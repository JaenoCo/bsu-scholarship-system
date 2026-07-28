<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationForm;
use App\Models\Campus;
use App\Models\College;
use App\Models\Form;
use App\Models\Notification;
use App\Models\Program;
use App\Models\ProgramTrack;
use App\Models\Report;
use App\Models\Scholar;
use App\Models\Scholarship;
use App\Models\StudentSubmittedDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SearchController extends Controller
{
    private const PER_GROUP_LIMIT = 6;
    private const MIN_TERM_LENGTH = 2;
    private const MAX_TERM_LENGTH = 80;

    public function suggest(Request $request)
    {
        $term = trim((string) $request->query('term', ''));
        $term = mb_substr(preg_replace('/\s+/', ' ', $term), 0, self::MAX_TERM_LENGTH);

        if (mb_strlen($term) < self::MIN_TERM_LENGTH) {
            return response()->json($this->emptyPayload());
        }

        $user = $this->resolveUser();

        if (! $user) {
            return response()->json($this->emptyPayload(), 401);
        }

        $campusIds = $this->resolveCampusScope($user);
        $payload = $this->emptyPayload();

        $searches = [
            'students' => fn () => $this->searchStudents($term, $user, $campusIds),
            'staff' => fn () => $this->searchStaff($term, $user),
            'scholarships' => fn () => $this->searchScholarships($term, $user, $campusIds),
            'applications' => fn () => $this->searchApplications($term, $user, $campusIds),
            'scholars' => fn () => $this->searchScholars($term, $user, $campusIds),
            'reports' => fn () => $this->searchReports($term, $user, $campusIds),
            'forms' => fn () => $this->searchForms($term, $user, $campusIds),
            'documents' => fn () => $this->searchDocuments($term, $user, $campusIds),
            'notifications' => fn () => $this->searchNotifications($term, $user),
            'programs' => fn () => $this->searchPrograms($term, $user, $campusIds),
            'colleges' => fn () => $this->searchColleges($term, $user, $campusIds),
            'campuses' => fn () => $this->searchCampuses($term, $user, $campusIds),
        ];

        foreach ($searches as $group => $callback) {
            $payload[$group] = $this->safeSearch($callback);
        }

        return response()->json($payload);
    }

    public function redirectToRecord(string $type, int $id)
    {
        $user = $this->resolveUser();

        if (! $user) {
            return redirect()->route('login');
        }

        return redirect($this->dashboardUrlFor($user, $type));
    }

    protected function searchStudents(string $term, User $viewer, ?array $campusIds = null): array
    {
        if (! Schema::hasTable('users') || $viewer->role === 'student') {
            return [];
        }

        $query = User::query()->where('role', 'student');
        $this->scopeUsersByCampuses($query, $campusIds);
        $this->whereLikeAny($query, 'users', [
            'name',
            'first_name',
            'middle_name',
            'last_name',
            'email',
            'sr_code',
            'contact_number',
            'college',
            'program',
            'track',
            'year_level',
            'education_level',
        ], $term);

        return $query->orderBy('name')
            ->limit(self::PER_GROUP_LIMIT)
            ->get()
            ->map(fn (User $student) => $this->result(
                'student',
                $student->id,
                $student->sr_code ? "{$student->name} ({$student->sr_code})" : $student->name,
                trim(collect([$student->campus?->name, $student->college, $student->program])->filter()->join(' / ')),
                'Student'
            ))
            ->values()
            ->all();
    }

    protected function searchStaff(string $term, User $viewer): array
    {
        if (! Schema::hasTable('users') || $viewer->role !== 'central') {
            return [];
        }

        $query = User::query()->whereIn('role', ['sfao', 'central']);
        $this->whereLikeAny($query, 'users', [
            'name',
            'first_name',
            'middle_name',
            'last_name',
            'email',
            'role',
            'contact_number',
        ], $term);

        return $query->orderBy('name')
            ->limit(self::PER_GROUP_LIMIT)
            ->get()
            ->map(fn (User $staff) => $this->result(
                'staff',
                $staff->id,
                $staff->name,
                trim(collect([strtoupper((string) $staff->role), $staff->email])->filter()->join(' / ')),
                'Staff'
            ))
            ->values()
            ->all();
    }

    protected function searchScholarships(string $term, User $viewer, ?array $campusIds = null): array
    {
        if (! Schema::hasTable('scholarships')) {
            return [];
        }

        $query = Scholarship::query();

        if ($viewer->role === 'student') {
            $this->whereColumnIfExists($query, 'scholarships', 'is_active', true);
        }

        $this->scopeScholarshipsByCampuses($query, $campusIds);
        $this->whereLikeAny($query, 'scholarships', [
            'scholarship_name',
            'scholarship_type',
            'description',
            'eligibility_notes',
            'announcement_title',
            'announcement_message',
            'grant_type',
        ], $term);

        return $query->orderBy('scholarship_name')
            ->limit(self::PER_GROUP_LIMIT)
            ->get()
            ->map(fn (Scholarship $scholarship) => $this->result(
                'scholarship',
                $scholarship->id,
                $scholarship->scholarship_name,
                trim(collect([$scholarship->scholarship_type, $scholarship->grant_amount ? 'Grant: ' . number_format((float) $scholarship->grant_amount, 2) : null])->filter()->join(' / ')),
                $scholarship->announcement_title ? 'Announcement' : 'Scholarship'
            ))
            ->values()
            ->all();
    }

    protected function searchApplications(string $term, User $viewer, ?array $campusIds = null): array
    {
        if (! Schema::hasTable('applications')) {
            return [];
        }

        $query = Application::query()->with(['user', 'scholarship']);
        $this->scopeRecordByViewer($query, $viewer, $campusIds, 'user');

        $query->where(function (Builder $scope) use ($term) {
            $this->whereLikeAny($scope, 'applications', ['status', 'remarks', 'grant_count'], $term, false);

            if (Schema::hasTable('users')) {
                $scope->orWhereHas('user', function (Builder $userQuery) use ($term) {
                    $this->whereLikeAny($userQuery, 'users', ['name', 'email', 'sr_code', 'college', 'program'], $term);
                });
            }

            if (Schema::hasTable('scholarships')) {
                $scope->orWhereHas('scholarship', function (Builder $scholarshipQuery) use ($term) {
                    $this->whereLikeAny($scholarshipQuery, 'scholarships', ['scholarship_name', 'scholarship_type'], $term);
                });
            }
        });

        return $query->latest()
            ->limit(self::PER_GROUP_LIMIT)
            ->get()
            ->map(fn (Application $application) => $this->result(
                'application',
                $application->id,
                $application->scholarship?->scholarship_name ?? 'Scholarship application',
                trim(collect([$application->user?->name, ucfirst(str_replace('_', ' ', (string) $application->status))])->filter()->join(' / ')),
                'Application'
            ))
            ->values()
            ->all();
    }

    protected function searchScholars(string $term, User $viewer, ?array $campusIds = null): array
    {
        if (! Schema::hasTable('scholars')) {
            return [];
        }

        $query = Scholar::query()->with(['user', 'scholarship']);
        $this->scopeRecordByViewer($query, $viewer, $campusIds, 'user');

        $query->where(function (Builder $scope) use ($term) {
            $this->whereLikeAny($scope, 'scholars', ['type', 'status', 'notes', 'grant_count', 'total_grant_received'], $term, false);

            if (Schema::hasTable('users')) {
                $scope->orWhereHas('user', function (Builder $userQuery) use ($term) {
                    $this->whereLikeAny($userQuery, 'users', ['name', 'email', 'sr_code', 'college', 'program'], $term);
                });
            }

            if (Schema::hasTable('scholarships')) {
                $scope->orWhereHas('scholarship', function (Builder $scholarshipQuery) use ($term) {
                    $this->whereLikeAny($scholarshipQuery, 'scholarships', ['scholarship_name', 'scholarship_type'], $term);
                });
            }
        });

        return $query->latest()
            ->limit(self::PER_GROUP_LIMIT)
            ->get()
            ->map(fn (Scholar $scholar) => $this->result(
                'scholar',
                $scholar->id,
                $scholar->user?->name ?? 'Scholar',
                trim(collect([$scholar->scholarship?->scholarship_name, ucfirst((string) $scholar->status)])->filter()->join(' / ')),
                'Scholar'
            ))
            ->values()
            ->all();
    }

    protected function searchReports(string $term, User $viewer, ?array $campusIds = null): array
    {
        if (! Schema::hasTable('reports') || $viewer->role === 'student') {
            return [];
        }

        $query = Report::query()->with('campus');

        if ($viewer->role === 'sfao') {
            if (Schema::hasColumn('reports', 'sfao_user_id')) {
                $query->where('sfao_user_id', $viewer->id);
            } elseif ($campusIds !== null) {
                $this->whereInIfColumnExists($query, 'reports', 'campus_id', $campusIds);
            }
        }

        $this->whereLikeAny($query, 'reports', [
            'title',
            'description',
            'report_type',
            'student_type',
            'academic_year',
            'status',
            'notes',
            'central_feedback',
        ], $term);

        return $query->latest()
            ->limit(self::PER_GROUP_LIMIT)
            ->get()
            ->map(fn (Report $report) => $this->result(
                'report',
                $report->id,
                $report->title,
                trim(collect([$report->campus?->name, ucfirst((string) $report->status)])->filter()->join(' / ')),
                'Report'
            ))
            ->values()
            ->all();
    }

    protected function searchForms(string $term, User $viewer, ?array $campusIds = null): array
    {
        $results = [];

        if (Schema::hasTable('application_forms')) {
            $query = ApplicationForm::query()->with(['campus', 'uploader']);

            if ($viewer->role === 'student') {
                $this->whereColumnIfExists($query, 'application_forms', 'campus_id', $viewer->campus_id);
            } elseif ($viewer->role === 'sfao') {
                $this->whereInIfColumnExists($query, 'application_forms', 'campus_id', $campusIds ?? []);
            }

            $this->whereLikeAny($query, 'application_forms', ['form_name', 'form_type', 'description', 'file_type'], $term);

            $results = $query->latest()
                ->limit(self::PER_GROUP_LIMIT)
                ->get()
                ->map(fn (ApplicationForm $form) => $this->result(
                    'application_form',
                    $form->id,
                    $form->form_name,
                    trim(collect([$form->form_type, $form->campus?->name])->filter()->join(' / ')),
                    'Form'
                ))
                ->values()
                ->all();
        }

        if (count($results) < self::PER_GROUP_LIMIT && Schema::hasTable('forms')) {
            $query = Form::query()->with('user');
            $this->scopeRecordByViewer($query, $viewer, $campusIds, 'user');
            $this->whereLikeAny($query, 'forms', [
                'civil_status',
                'birthplace',
                'town_city',
                'province',
                'citizenship',
                'disability',
                'tribe',
                'honors_received',
                'scholarship_applied',
                'semester',
                'academic_year',
                'existing_scholarship_details',
                'father_name',
                'mother_name',
                'reason_for_applying',
                'form_status',
                'reviewer_remarks',
            ], $term);

            $results = array_merge($results, $query->latest()
                ->limit(self::PER_GROUP_LIMIT - count($results))
                ->get()
                ->map(fn (Form $form) => $this->result(
                    'student_form',
                    $form->id,
                    $form->user?->name ? $form->user->name . ' application form' : 'Application form',
                    trim(collect([$form->academic_year, $form->form_status])->filter()->join(' / ')),
                    'Student Form'
                ))
                ->values()
                ->all());
        }

        return $results;
    }

    protected function searchDocuments(string $term, User $viewer, ?array $campusIds = null): array
    {
        if (! Schema::hasTable('student_submitted_documents')) {
            return [];
        }

        $query = StudentSubmittedDocument::query()->with(['user', 'scholarship']);
        $this->scopeRecordByViewer($query, $viewer, $campusIds, 'user');
        $this->whereLikeAny($query, 'student_submitted_documents', [
            'document_category',
            'document_name',
            'original_filename',
            'file_type',
            'description',
            'evaluation_status',
            'evaluation_notes',
        ], $term);

        return $query->latest()
            ->limit(self::PER_GROUP_LIMIT)
            ->get()
            ->map(fn (StudentSubmittedDocument $document) => $this->result(
                'document',
                $document->id,
                $document->document_name ?: $document->original_filename,
                trim(collect([$document->user?->name, $document->scholarship?->scholarship_name, ucfirst((string) $document->evaluation_status)])->filter()->join(' / ')),
                'Document'
            ))
            ->values()
            ->all();
    }

    protected function searchNotifications(string $term, User $viewer): array
    {
        if (! Schema::hasTable('notifications')) {
            return [];
        }

        $query = Notification::query()->where('user_id', $viewer->id);
        $this->whereLikeAny($query, 'notifications', ['type', 'title', 'message'], $term);

        return $query->latest()
            ->limit(self::PER_GROUP_LIMIT)
            ->get()
            ->map(fn (Notification $notification) => $this->result(
                'notification',
                $notification->id,
                $notification->title,
                $notification->message,
                'Notification'
            ))
            ->values()
            ->all();
    }

    protected function searchPrograms(string $term, User $viewer, ?array $campusIds = null): array
    {
        if (! Schema::hasTable('programs')) {
            return [];
        }

        $query = Program::query();
        $this->whereLikeAny($query, 'programs', ['name', 'short_name'], $term);

        if ($campusIds !== null && Schema::hasTable('campus_college')) {
            $query->whereHas('campusCollege', function (Builder $campusCollegeQuery) use ($campusIds) {
                $campusCollegeQuery->whereIn('campus_id', $campusIds);
            });
        }

        return $query->orderBy('name')
            ->limit(self::PER_GROUP_LIMIT)
            ->get()
            ->map(fn (Program $program) => $this->result(
                'program',
                $program->id,
                $program->name,
                $program->short_name,
                'Program'
            ))
            ->values()
            ->all();
    }

    protected function searchColleges(string $term, User $viewer, ?array $campusIds = null): array
    {
        if (! Schema::hasTable('colleges')) {
            return [];
        }

        $query = College::query();
        $this->whereLikeAny($query, 'colleges', ['name', 'short_name'], $term);

        if ($campusIds !== null && Schema::hasTable('campus_college')) {
            $query->whereHas('campuses', function (Builder $campusQuery) use ($campusIds) {
                $campusQuery->whereIn('campuses.id', $campusIds);
            });
        }

        return $query->orderBy('name')
            ->limit(self::PER_GROUP_LIMIT)
            ->get()
            ->map(fn (College $college) => $this->result('college', $college->id, $college->name, $college->short_name ?? null, 'College'))
            ->values()
            ->all();
    }

    protected function searchCampuses(string $term, User $viewer, ?array $campusIds = null): array
    {
        if (! Schema::hasTable('campuses') || $viewer->role === 'student') {
            return [];
        }

        $query = Campus::query();
        $this->whereLikeAny($query, 'campuses', ['name', 'type'], $term);

        if ($campusIds !== null) {
            $query->whereIn('id', $campusIds);
        }

        return $query->orderBy('name')
            ->limit(self::PER_GROUP_LIMIT)
            ->get()
            ->map(fn (Campus $campus) => $this->result('campus', $campus->id, $campus->name, ucfirst((string) $campus->type), 'Campus'))
            ->values()
            ->all();
    }

    protected function emptyPayload(): array
    {
        return [
            'students' => [],
            'staff' => [],
            'scholarships' => [],
            'applications' => [],
            'scholars' => [],
            'reports' => [],
            'forms' => [],
            'documents' => [],
            'notifications' => [],
            'programs' => [],
            'colleges' => [],
            'campuses' => [],
        ];
    }

    protected function safeSearch(callable $callback): array
    {
        try {
            return $callback();
        } catch (QueryException $exception) {
            report($exception);
            return [];
        }
    }

    protected function result(string $type, int $id, ?string $text, ?string $detail = null, ?string $badge = null): array
    {
        return [
            'id' => "{$type}-{$id}",
            'record_id' => $id,
            'type' => $type,
            'text' => $text ?: 'Untitled record',
            'detail' => $detail ?: null,
            'badge' => $badge ?: ucfirst(str_replace('_', ' ', $type)),
            'url' => route('search.redirect', ['type' => $type, 'id' => $id]),
        ];
    }

    protected function resolveUser(): ?User
    {
        $userId = session('user_id');

        if (! $userId) {
            return null;
        }

        return User::with('campus.extensionCampuses')->find($userId);
    }

    protected function resolveCampusScope(?User $user): ?array
    {
        if (! $user) {
            return null;
        }

        if ($user->role === 'central') {
            return null;
        }

        if ($user->role === 'student') {
            return $user->campus_id ? [$user->campus_id] : [];
        }

        if ($user->role !== 'sfao' || ! $user->campus) {
            return [];
        }

        return $user->campus->getAllCampusesUnder()->pluck('id')->filter()->unique()->values()->all();
    }

    protected function whereLikeAny(Builder $query, string $table, array $columns, string $term, bool $wrap = true): void
    {
        $columns = array_values(array_filter($columns, fn (string $column) => Schema::hasColumn($table, $column)));

        if ($columns === []) {
            return;
        }

        $keyword = $wrap ? "%{$term}%" : "%{$term}%";

        $query->where(function (Builder $scope) use ($columns, $keyword) {
            foreach ($columns as $index => $column) {
                $method = $index === 0 ? 'where' : 'orWhere';
                $scope->{$method}($column, 'like', $keyword);
            }
        });
    }

    protected function whereColumnIfExists(Builder $query, string $table, string $column, mixed $value): void
    {
        if (Schema::hasColumn($table, $column)) {
            $query->where($column, $value);
        }
    }

    protected function whereInIfColumnExists(Builder $query, string $table, string $column, array $values): void
    {
        if (Schema::hasColumn($table, $column)) {
            $query->whereIn($column, $values);
        }
    }

    protected function scopeUsersByCampuses(Builder $query, ?array $campusIds): void
    {
        if ($campusIds !== null && Schema::hasColumn('users', 'campus_id')) {
            $query->whereIn('campus_id', $campusIds);
        }
    }

    protected function scopeScholarshipsByCampuses(Builder $query, ?array $campusIds): void
    {
        if ($campusIds === null) {
            return;
        }

        $query->where(function (Builder $scope) use ($campusIds) {
            if (Schema::hasColumn('scholarships', 'campus_id')) {
                $scope->whereIn('campus_id', $campusIds);
            }

            if (Schema::hasTable('campus_scholarship')) {
                $scope->orWhereHas('campuses', function (Builder $campusQuery) use ($campusIds) {
                    $campusQuery->whereIn('campuses.id', $campusIds);
                });
            }
        });
    }

    protected function scopeRecordByViewer(Builder $query, User $viewer, ?array $campusIds, string $userRelation): void
    {
        if ($viewer->role === 'central') {
            return;
        }

        if ($viewer->role === 'student') {
            $query->where('user_id', $viewer->id);
            return;
        }

        $query->whereHas($userRelation, function (Builder $userQuery) use ($campusIds) {
            $this->scopeUsersByCampuses($userQuery, $campusIds ?? []);
        });
    }

    protected function dashboardUrlFor(User $user, string $type): string
    {
        $tabs = [
            'central' => [
                'student' => 'endorsed_applicants',
                'application' => 'endorsed_applicants',
                'scholar' => 'all_scholars',
                'scholarship' => 'all_scholarships',
                'report' => 'sfao_reports',
                'staff' => 'staff',
                'campus' => 'all_statistics',
                'college' => 'all_statistics',
                'program' => 'all_statistics',
                'application_form' => 'all_scholarships',
                'student_form' => 'endorsed_applicants',
                'document' => 'endorsed_applicants',
                'notification' => 'all_statistics',
            ],
            'sfao' => [
                'student' => 'applicants',
                'application' => 'applicants',
                'scholar' => 'scholars',
                'scholarship' => 'scholarships',
                'report' => 'reports-student_summary',
                'campus' => 'analytics_scholarships',
                'college' => 'analytics_applications',
                'program' => 'analytics_applications',
                'application_form' => 'all-app-forms',
                'student_form' => 'applicants',
                'document' => 'applicants',
                'notification' => 'analytics_scholarships',
            ],
            'student' => [
                'scholarship' => 'all_scholarships',
                'application' => 'applied_scholarships',
                'scholar' => 'applied_scholarships',
                'application_form' => 'all-app-forms',
                'student_form' => 'sfao_form',
                'document' => 'applied_scholarships',
                'notification' => 'all_notifications',
                'program' => 'all_scholarships',
                'college' => 'all_scholarships',
            ],
        ];

        $roleTabs = $tabs[$user->role] ?? [];
        $tab = $roleTabs[$type] ?? null;

        return match ($user->role) {
            'central' => route('central.dashboard', $tab ? ['tabs' => $tab] : []),
            'sfao' => route('sfao.dashboard', $tab ? ['tabs' => $tab] : []),
            'student' => route('student.dashboard', $tab ? ['tab' => $tab] : []),
            default => route('login'),
        };
    }
}
