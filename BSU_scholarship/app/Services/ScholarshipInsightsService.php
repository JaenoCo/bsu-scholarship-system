<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Scholar;
use App\Models\Scholarship;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/** Builds the SFAO Scholarship Insights dataset from one consistently scoped query. */
class ScholarshipInsightsService
{
    private const APPLICATION_STATUSES = ['pending', 'in_progress', 'approved', 'rejected', 'claimed'];
    private const SCHOLAR_STATUSES = ['active', 'inactive', 'suspended', 'completed'];

    public function build(Collection $campusIds, array $filters = []): array
    {
        $view = $filters['view'] ?? 'scholarships';
        $report = match ($view) {
            'applicants' => ['title' => 'Applicant Insights Report', 'focus' => 'Applicant volume and application decisions'],
            'scholars' => ['title' => 'Scholar Insights Report', 'focus' => 'Approved and active scholar outcomes'],
            'gwa' => ['title' => 'GWA Prediction Report', 'focus' => 'Scholarship qualification and academic-risk scope'],
            default => ['title' => 'Scholarship Insights Report', 'focus' => 'Scholarship applications and program performance'],
        };
        $applications = $this->query($campusIds, $filters)
            ->with(['user:id,campus_id,college,program,track', 'user.campus:id,name', 'scholarship:id,scholarship_name,is_active'])
            ->get();

        // Scholarship Insights is program-centric. Applications alone cannot tell us
        // how many students were actually enrolled as scholars, so build the matching
        // scholar scope separately and merge it by scholarship program below.
        $scholarQuery = Scholar::query()
            ->whereHas('user', fn (Builder $users) => $users->whereIn('campus_id', $campusIds));
        $this->applyUserFilters($scholarQuery, $filters);
        if (($filters['scholarship_id'] ?? 'all') !== 'all' && ($filters['scholarship_id'] ?? '') !== '') {
            $scholarQuery->where('scholarship_id', $filters['scholarship_id']);
        }
        if (($filters['scholarship'] ?? '') !== '') {
            $scholarQuery->whereHas('scholarship', fn (Builder $scholarship) => $scholarship->where('scholarship_name', $filters['scholarship']));
        }
        $this->applyScholarDateFilters($scholarQuery, $filters);
        $scholars = $scholarQuery
            ->with(['user:id,campus_id,college,program,track', 'user.campus:id,name', 'scholarship:id,scholarship_name,is_active'])
            ->get();

        $total = $applications->count();
        $approved = $applications->whereIn('status', ['approved', 'claimed'])->count();
        $pending = $applications->where('status', 'pending')->count();
        // Keep the database value for compatibility; users see this as Under Review.
        $underReview = $applications->where('status', 'in_progress')->count();
        $rejected = $applications->where('status', 'rejected')->count();

        $applicationPrograms = $applications->groupBy('scholarship_id');
        $scholarPrograms = $scholars->groupBy('scholarship_id');
        $programIds = $applicationPrograms->keys()->merge($scholarPrograms->keys())->unique();
        $programs = $programIds->map(function ($programId) use ($applicationPrograms, $scholarPrograms) {
            $applicationRows = $applicationPrograms->get($programId, collect());
            $scholarRows = $scholarPrograms->get($programId, collect());
            $applicationRecord = $applicationRows->first();
            $scholarRecord = $scholarRows->first();
            $program = optional($applicationRecord?->scholarship ?? $scholarRecord?->scholarship);

            return [
                'name' => $program->scholarship_name ?? 'Unknown program',
                'total' => $applicationRows->count(),
                'applicants' => $applicationRows->pluck('user_id')->unique()->count(),
                'scholars' => $scholarRows->pluck('user_id')->unique()->count(),
                'active_scholars' => $scholarRows->where('status', 'active')->pluck('user_id')->unique()->count(),
                'approved' => $applicationRows->whereIn('status', ['approved', 'claimed'])->count(),
                'pending' => $applicationRows->where('status', 'pending')->count(),
                'under_review' => $applicationRows->where('status', 'in_progress')->count(),
                'rejected' => $applicationRows->where('status', 'rejected')->count(),
            ];
        })->sortByDesc('total')->values();

        $by = function (string $field) use ($applications) {
            return $applications->groupBy(fn ($app) => data_get($app, "user.$field") ?: 'Unassigned')
                ->map(fn ($rows, $name) => [
                    'name' => $name,
                    'applications' => $rows->count(),
                    'approved' => $rows->whereIn('status', ['approved', 'claimed'])->count(),
                    'pending' => $rows->where('status', 'pending')->count(),
                    'under_review' => $rows->where('status', 'in_progress')->count(),
                    'rejected' => $rows->where('status', 'rejected')->count(),
                ])->sortByDesc('applications')->values();
        };

        $applicationTrend = $applications->groupBy(fn ($app) => Carbon::parse($app->created_at)->format('Y-m'));
        $scholarTrend = $scholars->filter(fn ($scholar) => $scholar->scholarship_start_date)
            ->groupBy(fn ($scholar) => Carbon::parse($scholar->scholarship_start_date)->format('Y-m'));
        $trend = $applicationTrend->keys()->merge($scholarTrend->keys())->unique()->sort()->map(function ($month) use ($applicationTrend, $scholarTrend) {
            $applicationRows = $applicationTrend->get($month, collect());
            $scholarRows = $scholarTrend->get($month, collect());
            return [
                'period' => $month,
                'applications' => $applicationRows->count(),
                'approved' => $applicationRows->whereIn('status', ['approved', 'claimed'])->count(),
                'rejected' => $applicationRows->where('status', 'rejected')->count(),
                'scholars' => $scholarRows->pluck('user_id')->unique()->count(),
            ];
        })->values();

        $activePrograms = Scholarship::where('is_active', true)
            ->where(function (Builder $query) use ($campusIds) {
                $query->whereHas('campuses', fn (Builder $campuses) => $campuses->whereIn('campus_id', $campusIds))
                    ->orDoesntHave('campuses');
            })->count();

        $approvalRate = $total ? round(($approved / $total) * 100, 1) : 0.0;
        $totalApplicants = $applications->pluck('user_id')->unique()->count();
        $totalScholars = $scholars->pluck('user_id')->unique()->count();
        $activeScholars = $scholars->where('status', 'active')->pluck('user_id')->unique()->count();

        return [
            'filters' => $filters,
            'report' => $report,
            'summary' => compact('total', 'totalApplicants', 'totalScholars', 'activeScholars', 'approved', 'pending', 'underReview', 'rejected', 'activePrograms', 'approvalRate'),
            'programs' => $programs,
            'colleges' => $by('college'),
            'campuses' => $by('campus.name'),
            'status' => $this->statusBreakdown($applications),
            'trend' => $trend,
            'insights' => $this->insights($programs, $by('college'), $total, $approved, $approvalRate, $trend),
        ];
    }

    public function buildApplicants(Collection $campusIds, array $filters = []): array
    {
        $applications = $this->query($campusIds, $filters)
            ->with(['user:id,campus_id,college,program,sex', 'user.campus:id,name', 'scholarship:id,scholarship_name'])
            ->get();
        $groups = $applications->groupBy('user_id');
        $approved = $applications->whereIn('status', ['approved', 'claimed']);
        $total = $groups->count();

        return [
            'filters' => $filters,
            'summary' => [
                'totalApplicants' => $total,
                'completed' => $groups->filter(fn ($rows) => $rows->contains('status', 'approved') || $rows->contains('status', 'claimed'))->count(),
                // A draft table does not exist in the current schema.  Treat an applicant
                // with a pending or under-review application as incomplete for this view.
                'incomplete' => $groups->filter(fn ($rows) => $rows->contains(fn ($row) => in_array($row->status, ['pending', 'in_progress'], true)))->count(),
                'withdrawn' => 0,
                'newApplicants' => $groups->filter(fn ($rows) => $rows->min('created_at')?->gte(now()->subMonths(1)))->count(),
                'approvalRate' => $total ? round(($approved->pluck('user_id')->unique()->count() / $total) * 100, 1) : 0.0,
            ],
            'gender' => $this->groupUsers($applications, 'sex'),
            'campuses' => $this->groupUsers($applications, 'campus.name'),
            'colleges' => $this->groupUsers($applications, 'college'),
            'programs' => $applications->groupBy('scholarship_id')->map(fn ($rows) => [
                'name' => optional($rows->first()->scholarship)->scholarship_name ?? 'Unknown program',
                'applicants' => $rows->pluck('user_id')->unique()->count(),
                'approved' => $rows->whereIn('status', ['approved', 'claimed'])->pluck('user_id')->unique()->count(),
            ])->sortByDesc('applicants')->values(),
            'trend' => $applications->groupBy(fn ($app) => Carbon::parse($app->created_at)->format('Y-m'))->sortKeys()->map(fn ($rows, $period) => [
                'period' => $period,
                'applicants' => $rows->pluck('user_id')->unique()->count(),
                'approved' => $rows->whereIn('status', ['approved', 'claimed'])->pluck('user_id')->unique()->count(),
            ])->values(),
            'status' => $this->statusBreakdown($applications),
            'insights' => $this->insights($applications->groupBy('scholarship_id')->map(fn ($rows) => ['name' => optional($rows->first()->scholarship)->scholarship_name ?? 'Unknown program', 'total' => $rows->pluck('user_id')->unique()->count(), 'approved' => $rows->whereIn('status', ['approved', 'claimed'])->pluck('user_id')->unique()->count()]), collect(), $total, $approved->pluck('user_id')->unique()->count(), $total ? round(($approved->pluck('user_id')->unique()->count() / $total) * 100, 1) : 0.0, collect()),
        ];
    }

    public function buildScholars(Collection $campusIds, array $filters = []): array
    {
        $query = Scholar::query()->whereHas('user', fn (Builder $users) => $users->whereIn('campus_id', $campusIds));
        $this->applyUserFilters($query, $filters);
        if (($filters['scholarship_id'] ?? 'all') !== 'all' && ($filters['scholarship_id'] ?? '') !== '') $query->where('scholarship_id', $filters['scholarship_id']);
        if (($filters['scholarship'] ?? '') !== '') $query->whereHas('scholarship', fn (Builder $scholarship) => $scholarship->where('scholarship_name', $filters['scholarship']));
        if (($filters['status'] ?? 'all') !== 'all' && ($filters['status'] ?? '') !== '') $query->where('status', $filters['status']);
        $this->applyScholarDateFilters($query, $filters);
        $scholars = $query->with(['user:id,campus_id,college,program,sex', 'user.campus:id,name', 'scholarship:id,scholarship_name'])->get();
        $active = $scholars->where('status', 'active');
        $retained = $scholars->whereIn('status', ['active', 'completed'])->count();
        return [
            'filters' => $filters,
            'summary' => ['totalScholars' => $scholars->pluck('user_id')->unique()->count(), 'active' => $active->pluck('user_id')->unique()->count(), 'completed' => $scholars->where('status', 'completed')->pluck('user_id')->unique()->count(), 'ongoing' => $active->count(), 'atRisk' => $scholars->whereIn('status', ['suspended', 'inactive'])->pluck('user_id')->unique()->count(), 'retentionRate' => $scholars->count() ? round(($retained / $scholars->count()) * 100, 1) : 0.0],
            'status' => $this->statusBreakdown($scholars),
            'gender' => $this->groupUsers($scholars, 'sex'),
            'colleges' => $this->groupUsers($scholars, 'college'),
            'campuses' => $this->groupUsers($scholars, 'campus.name'),
            'programs' => $scholars->groupBy('scholarship_id')->map(fn ($rows) => ['name' => optional($rows->first()->scholarship)->scholarship_name ?? 'Unknown program', 'scholars' => $rows->pluck('user_id')->unique()->count()])->sortByDesc('scholars')->values(),
            'trend' => $scholars->groupBy(fn ($scholar) => Carbon::parse($scholar->scholarship_start_date)->format('Y'))->sortKeys()->map(fn ($rows, $period) => ['period' => $period, 'scholars' => $rows->pluck('user_id')->unique()->count(), 'new' => $rows->where('type', 'new')->pluck('user_id')->unique()->count()])->values(),
            'insights' => $this->scholarInsights($scholars),
        ];
    }

    private function query(Collection $campusIds, array $filters): Builder
    {
        $query = Application::query()->whereHas('user', fn (Builder $users) => $users->whereIn('campus_id', $campusIds));
        $this->applyUserFilters($query, $filters);
        if (($filters['scholarship_id'] ?? 'all') !== 'all' && ($filters['scholarship_id'] ?? '') !== '') $query->where('scholarship_id', $filters['scholarship_id']);
        if (($filters['scholarship'] ?? '') !== '') $query->whereHas('scholarship', fn (Builder $scholarship) => $scholarship->where('scholarship_name', $filters['scholarship']));
        if (in_array($filters['status'] ?? '', self::APPLICATION_STATUSES, true) || ($filters['status'] ?? '') === 'under_review') $query->where('status', $filters['status'] === 'under_review' ? 'in_progress' : $filters['status']);
        $this->applyDateFilters($query, $filters);
        return $query;
    }

    private function applyUserFilters(Builder $query, array $filters): void
    {
        foreach (['campus_id' => 'campus', 'college' => 'college', 'program' => 'program', 'track' => 'track'] as $column => $key) {
            if (($filters[$key] ?? 'all') !== 'all' && ($filters[$key] ?? '') !== '') {
                $value = $filters[$key];
                $query->whereHas('user', fn (Builder $users) => $users->where($column, $value));
            }
        }
    }

    private function applyDateFilters(Builder $query, array $filters): void
    {
        if (($filters['academic_year'] ?? 'all') !== 'all' && preg_match('/^(\d{4})-(\d{4})$/', $filters['academic_year'], $years)) {
            $query->whereBetween('created_at', [Carbon::create($years[1], 8, 1)->startOfDay(), Carbon::create($years[2], 7, 31)->endOfDay()]);
        }
        if (($filters['semester'] ?? 'all') === 'first') $query->whereMonth('created_at', '>=', 8);
        if (($filters['semester'] ?? 'all') === 'second') $query->whereMonth('created_at', '<=', 7);
    }

    private function applyScholarDateFilters(Builder $query, array $filters): void
    {
        if (($filters['academic_year'] ?? 'all') !== 'all' && preg_match('/^(\d{4})-(\d{4})$/', $filters['academic_year'], $years)) {
            $query->whereBetween('scholarship_start_date', [Carbon::create($years[1], 8, 1)->startOfDay(), Carbon::create($years[2], 7, 31)->endOfDay()]);
        }
        if (($filters['semester'] ?? 'all') === 'first') $query->whereMonth('scholarship_start_date', '>=', 8);
        if (($filters['semester'] ?? 'all') === 'second') $query->whereMonth('scholarship_start_date', '<=', 7);
    }

    private function groupUsers(Collection $rows, string $field): Collection
    {
        return $rows->groupBy(fn ($row) => data_get($row, "user.$field") ?: 'Unassigned')->map(fn ($items, $name) => ['name' => $name, 'total' => $items->pluck('user_id')->unique()->count()])->sortByDesc('total')->values();
    }

    private function statusBreakdown(Collection $rows): Collection
    {
        return $rows->groupBy('status')->map(fn (Collection $items, string $status) => [
            'name' => $status,
            'total' => $items->count(),
        ])->values();
    }

    private function scholarInsights(Collection $scholars): array
    {
        if ($scholars->isEmpty()) return [];
        $top = $scholars->groupBy('scholarship_id')->sortByDesc(fn ($rows) => $rows->count())->first();
        return ['Scholar records are distributed across '.$scholars->pluck('user_id')->unique()->count().' distinct students.', 'The largest scholar group contains '.$top->count().' records.'];
    }

    private function insights(Collection $programs, Collection $colleges, int $total, int $approved, float $rate, Collection $trend): array
    {
        $insights = [];
        if ($total) $insights[] = "Overall approval rate is {$rate}% ({$approved} of {$total} submitted applications).";
        if ($programs->isNotEmpty()) $insights[] = $programs->first()['name'].' has the highest application volume.';
        if ($colleges->isNotEmpty()) $insights[] = $colleges->first()['name'].' has the highest number of scholarship applications.';
        $eligiblePrograms = $programs->filter(fn ($program) => $program['total'] >= 5)
            ->sortByDesc(fn ($program) => $program['total'] ? $program['approved'] / $program['total'] : 0);
        if ($eligiblePrograms->isNotEmpty()) {
            $program = $eligiblePrograms->first();
            $programRate = round(($program['approved'] / $program['total']) * 100, 1);
            $insights[] = "{$program['name']} has the highest approval rate ({$programRate}%) among programs with at least five applications.";
        }
        if ($trend->count() >= 2) {
            $current = $trend->last()['applications']; $previous = $trend->slice(-2, 1)->first()['applications'];
            if ($previous > 0) {
                $change = round((($current - $previous) / $previous) * 100, 1);
                $direction = $change >= 0 ? 'increased' : 'decreased';
                $insights[] = "Application volume {$direction} by ".abs($change).'% compared with the previous reporting month.';
            }
        }
        return $insights;
    }
}
