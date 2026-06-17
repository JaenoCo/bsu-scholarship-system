<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ScholarshipImportController extends Controller
{
    public function store(Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xls,xlsx|max:10240',
            'update_existing' => 'nullable|boolean',
        ]);

        $sfaoUser = User::with('campus.extensionCampuses')->findOrFail(session('user_id'));
        $managedCampuses = $sfaoUser->campus->getAllCampusesUnder();
        $managedCampusIds = $managedCampuses->pluck('id')->map(fn ($id) => (int) $id);
        $updateExisting = $request->boolean('update_existing', true);

        $rows = $this->readRows($request->file('file')->getRealPath(), strtolower($request->file('file')->getClientOriginalExtension()));

        if (count($rows) < 2) {
            return back()->with('error', 'Import file must contain a header row and at least one scholarship row.');
        }

        $headers = $this->normalizeHeaders(array_shift($rows));
        $result = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                $line = $index + 2;
                $data = $this->mapRow($headers, $row);

                if ($this->isBlankRow($data)) {
                    continue;
                }

                $name = $this->nullableString($data['scholarship_name'] ?? $data['name'] ?? $data['title'] ?? null);
                $description = $this->nullableString($data['description'] ?? null);
                $deadline = $this->nullableDate($data['submission_deadline'] ?? $data['deadline'] ?? null);

                if (!$name) {
                    $this->addError($result, $line, 'Missing scholarship_name.');
                    continue;
                }

                if (!$description) {
                    $this->addError($result, $line, 'Missing description.');
                    continue;
                }

                if (!$deadline) {
                    $this->addError($result, $line, 'Missing or invalid submission_deadline.');
                    continue;
                }

                $campusIds = $this->resolveCampusIds($data, $managedCampuses, (int) $sfaoUser->campus_id);
                if ($campusIds->isEmpty()) {
                    $this->addError($result, $line, 'No valid campus in this SFAO scope.');
                    continue;
                }

                if ($campusIds->diff($managedCampusIds)->isNotEmpty()) {
                    $this->addError($result, $line, 'One or more campuses are outside this SFAO scope.');
                    continue;
                }

                $grantType = $this->normalizeChoice($data['grant_type'] ?? null, ['one_time', 'recurring', 'discontinued'], 'recurring');

                $payload = [
                    'scholarship_name' => $name,
                    'scholarship_type' => $this->normalizeChoice($data['scholarship_type'] ?? $data['type'] ?? null, ['private', 'government'], 'private'),
                    'description' => $description,
                    'submission_deadline' => $deadline,
                    'application_start_date' => $this->nullableDate($data['application_start_date'] ?? $data['start_date'] ?? null),
                    'slots_available' => $this->nullableInteger($data['slots_available'] ?? $data['slots'] ?? null),
                    'grant_amount' => $this->nullableDecimal($data['grant_amount'] ?? $data['amount'] ?? null),
                    'renewal_allowed' => $this->nullableBoolean($data['renewal_allowed'] ?? null) ?? $grantType === 'recurring',
                    'grant_type' => $grantType,
                    'is_active' => $this->nullableBoolean($data['is_active'] ?? $data['active'] ?? null) ?? true,
                    'allow_existing_scholarship' => $this->nullableBoolean($data['allow_existing_scholarship'] ?? null) ?? false,
                    'eligibility_notes' => $this->nullableString($data['eligibility_notes'] ?? $data['eligibility'] ?? null),
                    'created_by' => $sfaoUser->id,
                ];

                $existing = Scholarship::where('scholarship_name', $name)->first();

                if ($existing) {
                    $existingCampusIds = $existing->campuses()->pluck('campuses.id')->map(fn ($id) => (int) $id);
                    if ($existingCampusIds->isNotEmpty() && $existingCampusIds->diff($managedCampusIds)->isNotEmpty()) {
                        $this->addError($result, $line, 'Existing scholarship is attached to a campus outside this SFAO scope.');
                        continue;
                    }

                    if (!$updateExisting) {
                        $result['skipped']++;
                        continue;
                    }

                    $existing->update($payload);
                    $existing->campuses()->sync($campusIds->all());
                    $result['updated']++;
                    continue;
                }

                $scholarship = Scholarship::create($payload);
                $scholarship->campuses()->sync($campusIds->all());
                $result['created']++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Scholarship import failed: ' . $e->getMessage());

            return back()->with('error', 'Import failed. Please check the file and try again.');
        }

        return redirect()
            ->route('sfao.dashboard', ['tabs' => 'import-scholarships'])
            ->with('success', 'Scholarship import completed.')
            ->with('import_result', $result);
    }

    private function readRows(string $path, string $extension): array
    {
        if (in_array($extension, ['csv', 'txt'], true)) {
            $handle = fopen($path, 'r');
            $rows = [];

            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = $row;
            }

            fclose($handle);
            return $rows;
        }

        $spreadsheet = IOFactory::load($path);
        return $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
    }

    private function normalizeHeaders(array $headers): array
    {
        return array_map(function ($header) {
            return Str::of((string) $header)
                ->lower()
                ->replace([' ', '-', '.'], '_')
                ->replaceMatches('/_+/', '_')
                ->trim('_')
                ->toString();
        }, $headers);
    }

    private function mapRow(array $headers, array $row): array
    {
        $data = [];

        foreach ($headers as $index => $header) {
            if ($header === '') {
                continue;
            }

            $data[$header] = isset($row[$index]) ? trim((string) $row[$index]) : null;
        }

        return $data;
    }

    private function isBlankRow(array $data): bool
    {
        return collect($data)->filter(fn ($value) => trim((string) $value) !== '')->isEmpty();
    }

    private function resolveCampusIds(array $data, $managedCampuses, int $defaultCampusId)
    {
        $raw = $data['campus_ids'] ?? $data['campus_id'] ?? $data['campuses'] ?? $data['campus'] ?? $data['campus_name'] ?? null;
        $raw = $this->nullableString($raw);

        if (!$raw) {
            return collect([$defaultCampusId]);
        }

        if (Str::lower($raw) === 'all') {
            return $managedCampuses->pluck('id')->map(fn ($id) => (int) $id)->values();
        }

        $parts = collect(preg_split('/[,;|]/', $raw))
            ->map(fn ($value) => trim($value))
            ->filter();

        return $parts->map(function ($value) use ($managedCampuses) {
            if (is_numeric($value)) {
                return (int) $value;
            }

            $matched = $managedCampuses->first(function (Campus $campus) use ($value) {
                return Str::lower($campus->name) === Str::lower($value);
            });

            return $matched ? (int) $matched->id : null;
        })->filter()->unique()->values();
    }

    private function normalizeChoice(?string $value, array $allowed, string $default): string
    {
        $value = Str::of((string) $value)
            ->lower()
            ->replace([' ', '-'], '_')
            ->trim('_')
            ->toString();

        return in_array($value, $allowed, true) ? $value : $default;
    }

    private function nullableBoolean(?string $value): ?bool
    {
        $value = Str::lower(trim((string) $value));

        if ($value === '') {
            return null;
        }

        if (in_array($value, ['1', 'true', 'yes', 'y', 'active', 'allow'], true)) {
            return true;
        }

        if (in_array($value, ['0', 'false', 'no', 'n', 'inactive', 'deny'], true)) {
            return false;
        }

        return null;
    }

    private function nullableDate(?string $value): ?string
    {
        $value = $this->nullableString($value);
        if (!$value) {
            return null;
        }

        try {
            $timestamp = strtotime($value);
            return $timestamp ? date('Y-m-d', $timestamp) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function nullableInteger(?string $value): ?int
    {
        $value = $this->nullableString($value);
        return is_numeric($value) ? (int) $value : null;
    }

    private function nullableDecimal(?string $value): ?float
    {
        $value = $this->nullableString($value);
        if (!$value) {
            return null;
        }

        $value = str_replace([',', 'PHP', 'php', '₱'], '', $value);
        return is_numeric($value) ? (float) $value : null;
    }

    private function nullableString(?string $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function addError(array &$result, int $line, string $message): void
    {
        $result['skipped']++;

        if (count($result['errors']) < 20) {
            $result['errors'][] = "Row {$line}: {$message}";
        }
    }
}
