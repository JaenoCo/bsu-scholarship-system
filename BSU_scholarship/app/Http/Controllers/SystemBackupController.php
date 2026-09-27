<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Campus;
use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SystemBackupController extends Controller
{
    /**
     * Tables owned by this application. Framework tables are deliberately left
     * out so a restore cannot overwrite jobs, sessions, or migration history.
     */
    private const BACKUP_TABLES = [
        'campuses', 'colleges', 'departments', 'programs', 'program_tracks',
        'users', 'campus_college', 'campus_department', 'scholarships',
        'campus_scholarship', 'scholarship_target_colleges',
        'scholarship_target_programs', 'scholarship_target_tracks',
        'scholarship_required_conditions', 'scholarship_required_documents',
        'application_forms', 'student_profiles', 'forms', 'applications',
        'scholars', 'scholar_renewal_snapshots', 'student_submitted_documents',
        'student_grades', 'submissions', 'submission_subjects', 'reports',
        'notifications', 'invitations', 'rejected_applicants', 'announcements',
        'document_evaluations', 'grade_submissions',
    ];

    public function __construct()
    {
        // Protected by route middleware; no-op here.
    }

    public static function buildExportFilename(string $role, string $category, ?int $year = null, ?string $campusLabel = null, string $format = 'xlsx'): string
    {
        $year = $year ?? now()->year;
        $safeFormat = strtolower($format) === 'csv' ? 'csv' : 'xlsx';

        if ($role === 'sfao') {
            $normalizedCampus = self::sanitizeFilenamePart($campusLabel ?? 'Campus');

            if ($category === 'registered_users') {
                return sprintf('Registered_%s_Users_%s.%s', $normalizedCampus, $year, $safeFormat);
            }

            $normalizedCategory = self::normalizeSfaoCategory($category);

            return sprintf('%s_%s_%s.%s', $normalizedCampus, $normalizedCategory, $year, $safeFormat);
        }

        $normalizedCategory = self::normalizeCentralCategory($category);

        return sprintf('%s.%s', $normalizedCategory, $safeFormat);
    }

    public function export(Request $request)
    {
        $role = $request->route('role') ?? session('role');
        $category = $request->input('category', 'registered_users');
        $year = (int) ($request->input('year', now()->year));
        $format = strtolower($request->input('format', 'xlsx')) === 'csv' ? 'csv' : 'xlsx';

        if (!in_array($role, ['sfao', 'central'], true)) {
            abort(403, 'Invalid role for backup export.');
        }

        if ($role === 'sfao' && !session()->has('user_id')) {
            abort(403, 'Please log in to export SFAO backups.');
        }

        if ($role === 'central' && (!session()->has('user_id') || session('role') !== 'central')) {
            abort(403, 'Central admin access is required for central backups.');
        }

        $campusName = $this->resolveCampusName($role);

        $fileName = self::buildExportFilename($role, $category, $year, $campusName, $format);
        $path = $this->createSpreadsheetExport($role, $category, $year, $campusName, $format);

        return response()->download($path, $fileName)->deleteFileAfterSend();
    }

    /** Download a self-contained, verified restoration bundle. */
    public function download(Request $request)
    {
        $role = $request->route('role') ?? session('role');
        $this->authorizeRole($role);

        if (! class_exists(\ZipArchive::class)) {
            return back()->withErrors(['backup_file' => 'ZIP support is not enabled on this server.']);
        }

        $scope = $role === 'sfao' ? $this->sfaoScope() : null;
        $data = $this->collectBackupData($role, $scope);
        $payload = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $fileName = sprintf('BSU_Scholarship_%s_Backup_%s.zip', strtoupper($role), now()->format('Ymd_His'));
        $temporaryPath = storage_path('app/tmp_exports/' . $fileName);

        if (! is_dir(dirname($temporaryPath))) {
            mkdir(dirname($temporaryPath), 0755, true);
        }

        $archive = new \ZipArchive();
        if ($archive->open($temporaryPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return back()->withErrors(['backup_file' => 'The backup archive could not be created.']);
        }

        $manifest = [
            'format' => 'bsu-scholarship-backup',
            'version' => 1,
            'role' => $role,
            'created_at' => now()->toIso8601String(),
            'scope' => $scope,
            'data_file' => 'database.json',
            'data_sha256' => hash('sha256', $payload),
        ];

        $archive->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        $archive->addFromString('database.json', $payload);
        $this->addPublicFiles($archive, $data);
        $archive->close();

        return response()->download($temporaryPath, $fileName, ['Content-Type' => 'application/zip'])->deleteFileAfterSend();
    }

    /** Restore only bundles produced by this application. */
    public function restore(Request $request)
    {
        $role = $request->route('role') ?? session('role');
        $this->authorizeRole($role);

        $request->validate([
            'backup_file' => ['required', 'file', 'mimes:zip', 'max:512000'],
        ]);

        if (! class_exists(\ZipArchive::class)) {
            return back()->withErrors(['backup_file' => 'ZIP support is not enabled on this server.']);
        }

        try {
            [$manifest, $data, $archive] = $this->openBackup($request->file('backup_file')->getRealPath(), $role);
            $this->validateBackupData($data);
            $this->validateBackupFilePaths($data);
            $this->restoreDatabase($data, $role);
            $this->restorePublicFiles($archive, $data, $role);
            $archive->close();
        } catch (\Throwable $exception) {
            if (isset($archive) && $archive instanceof \ZipArchive) {
                $archive->close();
            }

            report($exception);
            return back()->withErrors(['backup_file' => 'Restore was not applied: ' . $exception->getMessage()]);
        }

        $scopeMessage = $role === 'central'
            ? 'The system database and uploaded files were restored.'
            : 'The campus-scoped records and uploaded files were restored.';

        return back()->with('success', $scopeMessage);
    }

    protected function createSpreadsheetExport(string $role, string $category, int $year, ?string $campusLabel, string $format): string
    {
        $fileExtension = strtolower($format) === 'csv' ? 'csv' : 'xlsx';
        $tempDir = storage_path('app/tmp_exports');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $filePath = $tempDir . DIRECTORY_SEPARATOR . self::buildExportFilename($role, $category, $year, $campusLabel, $fileExtension);

        if ($fileExtension === 'csv') {
            $rows = $this->buildRowsForRole($role, $category, $year, $campusLabel);
            $handler = fopen($filePath, 'w');
            foreach ($rows as $row) {
                fputcsv($handler, $row);
            }
            fclose($handler);

            return $filePath;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $this->buildRowsForRole($role, $category, $year, $campusLabel);

        if (!empty($rows)) {
            $sheet->fromArray($rows, null, 'A1');
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }

    protected function buildRowsForRole(string $role, string $category, int $year, ?string $campusLabel): array
    {
        if ($role === 'sfao') {
            return $this->buildSfaoRows($category, $year, $campusLabel);
        }

        return $this->buildCentralRows($category);
    }

    protected function buildSfaoRows(string $category, int $year, ?string $campusLabel): array
    {
        $scope = $this->sfaoScope();
        $campusIds = $scope['campus_ids'];

        if ($category === 'scholar_summary') {
            $rows = [['Scholarship Name', 'Campus', 'Type', 'Status', 'Applications', 'Awarded Scholars', 'Year']];
            $scholarships = Scholarship::query()
                ->whereYear('created_at', '<=', $year)
                ->where(function ($query) use ($campusIds) {
                    $query->whereIn('campus_id', $campusIds)
                        ->orWhereHas('campuses', fn ($campuses) => $campuses->whereIn('campus_id', $campusIds));
                })
                ->withCount(['applications', 'scholars'])
                ->with('campus:id,name')
                ->orderBy('scholarship_name')
                ->get();

            foreach ($scholarships as $scholarship) {
                $rows[] = [$scholarship->scholarship_name, $scholarship->campus?->name, $scholarship->scholarship_type, $scholarship->is_active ? 'Active' : 'Inactive', $scholarship->applications_count, $scholarship->scholars_count, $year];
            }

            return $rows;
        }

        if ($category === 'applicants') {
            $rows = [['Applicant Name', 'Student Number', 'Email', 'Campus', 'Scholarship', 'Status', 'Applied At']];
            $applications = Application::query()
                ->whereYear('created_at', $year)
                ->whereHas('user', fn ($users) => $users->whereIn('campus_id', $campusIds))
                ->with(['user.campus:id,name', 'scholarship:id,scholarship_name'])
                ->latest()
                ->get();

            foreach ($applications as $application) {
                $rows[] = [$application->user?->name, $application->user?->sr_code, $application->user?->email, $application->user?->campus?->name, $application->scholarship?->scholarship_name, $application->status, optional($application->created_at)->toDateTimeString()];
            }

            return $rows;
        }

        $rows = [['User Name', 'Student Number', 'Email', 'Role', 'Campus', 'Registered At']];
        User::query()->whereIn('campus_id', $campusIds)->orderBy('name')->with('campus:id,name')->each(function (User $user) use (&$rows) {
            $rows[] = [$user->name, $user->sr_code, $user->email, $user->role, $user->campus?->name, optional($user->created_at)->toDateTimeString()];
        });

        return $rows;
    }

    protected function buildCentralRows(string $category): array
    {
        if ($category === 'sfao_users') {
            $rows = [['Name', 'Email', 'Campus', 'Status', 'Created At']];
            User::query()->where('role', 'sfao')->with('campus:id,name')->orderBy('name')->each(function (User $user) use (&$rows) {
                $rows[] = [$user->name, $user->email, $user->campus?->name, $user->email_verified_at ? 'Verified' : 'Pending verification', optional($user->created_at)->toDateTimeString()];
            });
            return $rows;
        }

        if ($category === 'campus') {
            $rows = [['Campus Name', 'Type', 'Parent Campus', 'SFAO Assigned', 'Registered Users']];
            Campus::query()->with(['parentCampus:id,name'])->withCount('users')->orderBy('name')->each(function (Campus $campus) use (&$rows) {
                $rows[] = [$campus->name, $campus->type, $campus->parentCampus?->name, $campus->has_sfao_admin ? 'Yes' : 'No', $campus->users_count];
            });
            return $rows;
        }

        $rows = [['Scholarship Name', 'Primary Campus', 'Available Campuses', 'Type', 'Status', 'Applications']];
        Scholarship::query()->with(['campus:id,name', 'campuses:id,name'])->withCount('applications')->orderBy('scholarship_name')->each(function (Scholarship $scholarship) use (&$rows) {
            $availableCampuses = $scholarship->campuses->pluck('name')->implode(', ') ?: $scholarship->campus?->name;
            $rows[] = [$scholarship->scholarship_name, $scholarship->campus?->name, $availableCampuses, $scholarship->scholarship_type, $scholarship->is_active ? 'Active' : 'Inactive', $scholarship->applications_count];
        });

        return $rows;
    }

    /** @return array{campus_ids: array<int, int>, campus_names: array<int, string>} */
    protected function sfaoScope(): array
    {
        $user = User::with('campus.extensionCampuses')->findOrFail(session('user_id'));
        abort_unless($user->campus, 403, 'Your SFAO account has no campus scope.');

        $campuses = $user->campus->getAllCampusesUnder();

        return [
            'campus_ids' => $campuses->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'campus_names' => $campuses->pluck('name')->values()->all(),
        ];
    }

    protected function authorizeRole(?string $role): void
    {
        if (! in_array($role, ['sfao', 'central'], true) || ! session()->has('user_id')) {
            abort(403, 'You must be signed in to manage backups.');
        }

        if ($role === 'central' && session('role') !== 'central') {
            abort(403, 'Central administrator access is required.');
        }

        if ($role === 'sfao' && session('role') !== 'sfao') {
            abort(403, 'SFAO access is required.');
        }
    }

    /** @return array<string, array<int, array<string, mixed>>> */
    protected function collectBackupData(string $role, ?array $scope): array
    {
        if ($role === 'central') {
            return collect(self::BACKUP_TABLES)
                ->filter(fn (string $table) => Schema::hasTable($table))
                ->mapWithKeys(fn (string $table) => [$table => $this->rows($table)])
                ->all();
        }

        $campusIds = $scope['campus_ids'];
        $users = $this->rows('users', fn ($query) => $query->whereIn('campus_id', $campusIds));
        $userIds = array_column($users, 'id');
        $campuses = $this->rows('campuses', fn ($query) => $query->whereIn('id', $campusIds));
        $scholarshipRows = $this->rows('scholarships', fn ($query) => $query->whereIn('campus_id', $campusIds));
        $scholarshipIds = array_column($scholarshipRows, 'id');
        $pivotRows = $this->rows('campus_scholarship', fn ($query) => $query->whereIn('campus_id', $campusIds)->whereIn('scholarship_id', $scholarshipIds));
        $scholarships = $this->rows('scholarships', fn ($query) => $query->whereIn('id', $scholarshipIds));
        $applications = $this->rows('applications', fn ($query) => $query->whereIn('user_id', $userIds));
        $applicationIds = array_column($applications, 'id');
        $scholars = $this->rows('scholars', fn ($query) => $query->whereIn('user_id', $userIds));
        $scholarIds = array_column($scholars, 'id');
        $submissions = $this->rows('submissions', fn ($query) => $query->whereIn('user_id', $userIds));
        $submissionIds = array_column($submissions, 'id');

        return $this->onlyExistingTables([
            'campuses' => $campuses,
            'users' => $users,
            'scholarships' => $scholarships,
            'campus_scholarship' => $pivotRows,
            'scholarship_required_conditions' => $this->rows('scholarship_required_conditions', fn ($query) => $query->whereIn('scholarship_id', $scholarshipIds)),
            'scholarship_required_documents' => $this->rows('scholarship_required_documents', fn ($query) => $query->whereIn('scholarship_id', $scholarshipIds)),
            'scholarship_target_colleges' => $this->rows('scholarship_target_colleges', fn ($query) => $query->whereIn('scholarship_id', $scholarshipIds)),
            'scholarship_target_programs' => $this->rows('scholarship_target_programs', fn ($query) => $query->whereIn('scholarship_id', $scholarshipIds)),
            'scholarship_target_tracks' => $this->rows('scholarship_target_tracks', fn ($query) => $query->whereIn('scholarship_id', $scholarshipIds)),
            'application_forms' => $this->rows('application_forms', fn ($query) => $query->whereIn('campus_id', $campusIds)),
            'student_profiles' => $this->rows('student_profiles', fn ($query) => $query->whereIn('user_id', $userIds)),
            'forms' => $this->rows('forms', fn ($query) => $query->whereIn('user_id', $userIds)),
            'applications' => $applications,
            'scholars' => $scholars,
            'scholar_renewal_snapshots' => $this->rows('scholar_renewal_snapshots', fn ($query) => $query->whereIn('scholar_id', $scholarIds)),
            'student_submitted_documents' => $this->rows('student_submitted_documents', fn ($query) => $query->whereIn('user_id', $userIds)),
            'student_grades' => $this->rows('student_grades', fn ($query) => $query->whereIn('user_id', $userIds)),
            'submissions' => $submissions,
            'submission_subjects' => $this->rows('submission_subjects', fn ($query) => $query->whereIn('submission_id', $submissionIds)),
            'reports' => $this->rows('reports', fn ($query) => $query->whereIn('campus_id', $campusIds)),
            'notifications' => $this->rows('notifications', fn ($query) => $query->whereIn('user_id', $userIds)),
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    protected function rows(string $table, ?callable $constraint = null): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        $query = DB::table($table)->orderBy('id');
        if ($constraint) {
            $constraint($query);
        }

        return $query->get()->map(fn ($row) => (array) $row)->all();
    }

    /** @param array<string, array<int, array<string, mixed>>> $tables */
    protected function onlyExistingTables(array $tables): array
    {
        return collect($tables)->filter(fn ($rows, string $table) => Schema::hasTable($table))->all();
    }

    protected function addPublicFiles(\ZipArchive $archive, array $data): void
    {
        foreach ($this->backupFilePaths($data) as $path => $disk) {
            $absolutePath = Storage::disk($disk)->path($path);
            if (is_file($absolutePath)) {
                $archive->addFile($absolutePath, 'files/' . str_replace('\\', '/', $path));
            }
        }
    }

    /** @return array{0: array<string, mixed>, 1: array<string, array>, 2: \ZipArchive} */
    protected function openBackup(string $path, string $role): array
    {
        $archive = new \ZipArchive();
        if ($archive->open($path, \ZipArchive::CHECKCONS) !== true) {
            throw new \RuntimeException('The selected file is not a valid backup archive.');
        }

        $manifestRaw = $archive->getFromName('manifest.json');
        $dataRaw = $archive->getFromName('database.json');
        if ($manifestRaw === false || $dataRaw === false) {
            throw new \RuntimeException('This archive does not contain a BSU restoration bundle.');
        }

        $manifest = json_decode($manifestRaw, true, 512, JSON_THROW_ON_ERROR);
        $data = json_decode($dataRaw, true, 512, JSON_THROW_ON_ERROR);
        if (($manifest['format'] ?? null) !== 'bsu-scholarship-backup' || ($manifest['version'] ?? null) !== 1 || ($manifest['role'] ?? null) !== $role) {
            throw new \RuntimeException('This bundle is not compatible with your restoration scope.');
        }
        if (! hash_equals((string) ($manifest['data_sha256'] ?? ''), hash('sha256', $dataRaw))) {
            throw new \RuntimeException('The backup data checksum does not match.');
        }
        if (! is_array($data) || array_diff(array_keys($data), self::BACKUP_TABLES)) {
            throw new \RuntimeException('The backup contains unsupported data tables.');
        }

        return [$manifest, $data, $archive];
    }

    protected function restoreDatabase(array $data, string $role): void
    {
        if ($role === 'sfao') {
            $this->validateSfaoPayload($data, $this->sfaoScope());
        }

        $connection = DB::connection();
        $isMySql = $connection->getDriverName() === 'mysql';
        if ($isMySql) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        try {
            DB::transaction(function () use ($data, $role) {
                $tables = array_values(array_filter(self::BACKUP_TABLES, fn ($table) => array_key_exists($table, $data) && Schema::hasTable($table)));
                if ($role === 'central') {
                    foreach (array_reverse($tables) as $table) {
                        DB::table($table)->delete();
                    }
                }

                foreach ($tables as $table) {
                    foreach (array_chunk($data[$table], 250) as $rows) {
                        if ($rows) {
                            if ($role === 'sfao' && array_key_exists('id', $rows[0])) {
                                DB::table($table)->upsert($rows, ['id']);
                            } elseif ($role === 'sfao') {
                                DB::table($table)->insertOrIgnore($rows);
                            } else {
                                DB::table($table)->insert($rows);
                            }
                        }
                    }
                }
            });
        } finally {
            if ($isMySql) {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        }
    }

    protected function validateSfaoPayload(array $data, array $scope): void
    {
        $allowedCampusIds = $scope['campus_ids'];
        foreach ($data['campuses'] ?? [] as $campus) {
            if (! in_array((int) ($campus['id'] ?? 0), $allowedCampusIds, true)) {
                throw new \RuntimeException('The backup contains a campus outside your SFAO scope.');
            }
        }
        foreach ($data['users'] ?? [] as $user) {
            if (! in_array((int) ($user['campus_id'] ?? 0), $allowedCampusIds, true)) {
                throw new \RuntimeException('The backup contains users outside your SFAO scope.');
            }
        }
        foreach ($data['scholarships'] ?? [] as $scholarship) {
            if (! in_array((int) ($scholarship['campus_id'] ?? 0), $allowedCampusIds, true)) {
                throw new \RuntimeException('The backup contains scholarships outside your SFAO scope.');
            }
        }
    }

    protected function restorePublicFiles(\ZipArchive $archive, array $data, string $role): void
    {
        foreach ($this->backupFilePaths($data) as $path => $disk) {
            if (str_contains($path, '..') || str_starts_with($path, '/')) {
                throw new \RuntimeException('The backup contains an invalid file path.');
            }
            $content = $archive->getFromName('files/' . str_replace('\\', '/', $path));
            if ($content !== false) {
                Storage::disk($disk)->put($path, $content);
            }
        }
    }

    /** @return array<string, 'local'|'public'> */
    protected function backupFilePaths(array $data): array
    {
        $paths = [];
        foreach (['student_submitted_documents', 'submissions', 'grade_submissions'] as $table) {
            foreach ($data[$table] ?? [] as $row) {
                if (! empty($row['file_path'])) {
                    $paths[ltrim((string) $row['file_path'], '/')] = 'public';
                }
            }
        }
        foreach ($data['student_grades'] ?? [] as $row) {
            if (! empty($row['document_path'])) {
                $paths[ltrim((string) $row['document_path'], '/')] = 'public';
            }
        }
        foreach ($data['application_forms'] ?? [] as $row) {
            if (! empty($row['file_path'])) {
                $paths[ltrim((string) $row['file_path'], '/')] = 'local';
            }
        }
        foreach ($data['users'] ?? [] as $row) {
            if (! empty($row['profile_picture'])) {
                $paths['profile_pictures/' . basename((string) $row['profile_picture'])] = 'public';
            }
        }

        return $paths;
    }

    protected function validateBackupFilePaths(array $data): void
    {
        foreach (array_keys($this->backupFilePaths($data)) as $path) {
            if ($path === '' || str_contains($path, '..') || str_starts_with($path, '/') || str_starts_with($path, '\\')) {
                throw new \RuntimeException('The backup contains an invalid file path.');
            }
        }
    }

    protected function validateBackupData(array $data): void
    {
        foreach ($data as $table => $rows) {
            if (! is_array($rows) || ! Schema::hasTable($table)) {
                throw new \RuntimeException('The backup data does not match this system version.');
            }
            $columns = Schema::getColumnListing($table);
            foreach ($rows as $row) {
                if (! is_array($row) || array_diff(array_keys($row), $columns)) {
                    throw new \RuntimeException("The backup contains invalid {$table} data.");
                }
            }
        }
    }

    protected function resolveCampusName(string $role): ?string
    {
        if ($role !== 'sfao') {
            return null;
        }

        $user = User::with('campus')->find(session('user_id'));

        return $user?->campus?->name ?: 'Campus';
    }

    protected static function normalizeSfaoCategory(string $category): string
    {
        return match ($category) {
            'scholar_summary' => 'Scholar_Summary',
            'applicants' => 'Applicants',
            'registered_users' => 'Registered_Users',
            default => self::sanitizeFilenamePart(str_replace('_', ' ', $category)),
        };
    }

    protected static function normalizeCentralCategory(string $category): string
    {
        return match ($category) {
            'sfao_users' => 'SFAO_Users',
            'campus' => 'Campus',
            'campus_scholarships' => 'Campus_scholarships',
            default => self::sanitizeFilenamePart(str_replace('_', ' ', $category)),
        };
    }

    protected static function sanitizeFilenamePart(?string $value): string
    {
        $value = (string) ($value ?? 'Export');
        $value = preg_replace('/[^A-Za-z0-9\-\s_]/', '', $value);
        $value = preg_replace('/\s+/', ' ', trim($value));
        $value = str_replace(' ', '_', $value);

        return trim($value, '_');
    }
}
