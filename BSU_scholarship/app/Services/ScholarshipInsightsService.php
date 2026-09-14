<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Scholarship;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/** Builds the SFAO Scholarship Insights dataset from one consistently scoped query. */
class ScholarshipInsightsService
{
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

        $total = $applications->count();
        $approved = $applications->whereIn('status', ['approved', 'claimed'])->count();
        $pending = $applications->where('status', 'pending')->count();
        // Keep the database value for compatibility; users see this as Under Review.
        $underReview = $applications->where('status', 'in_progress')->count();
        $rejected = $applications->where('status', 'rejected')->count();

        $programs = $applications->groupBy('scholarship_id')->map(function ($rows) {
            return [
                'name' => optional($rows->first()->scholarship)->scholarship_name ?? 'Unknown program',
                'total' => $rows->count(),
                'approved' => $rows->whereIn('status', ['approved', 'claimed'])->count(),
                'pending' => $rows->where('status', 'pending')->count(),
                'under_review' => $rows->where('status', 'in_progress')->count(),
                'rejected' => $rows->where('status', 'rejected')->count(),
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

        $trend = $applications->groupBy(fn ($app) => Carbon::parse($app->created_at)->format('Y-m'))
            ->sortKeys()->map(fn ($rows, $month) => [
                'period' => $month,
                'applications' => $rows->count(),
                'approved' => $rows->whereIn('status', ['approved', 'claimed'])->count(),
                'rejected' => $rows->where('status', 'rejected')->count(),
            ])->values();

        $activePrograms = Scholarship::where('is_active', true)
            ->where(function (Builder $query) use ($campusIds) {
                $query->whereHas('campuses', fn (Builder $campuses) => $campuses->whereIn('campus_id', $campusIds))
                    ->orDoesntHave('campuses');
            })->count();

        $approvalRate = $total ? round(($approved / $total) * 100, 1) : 0.0;
        return [
            'filters' => $filters,
            'report' => $report,
            'summary' => compact('total', 'approved', 'pending', 'underReview', 'rejected', 'activePrograms', 'approvalRate'),
            'programs' => $programs,
            'colleges' => $by('college'),
            'campuses' => $by('campus.name'),
            'trend' => $trend,
            'insights' => $this->insights($programs, $by('college'), $total, $approved, $approvalRate, $trend),
        ];
    }

    private function query(Collection $campusIds, array $filters): Builder
    {
        $query = Application::query()->whereHas('user', fn (Builder $users) => $users->whereIn('campus_id', $campusIds));
        foreach (['campus_id' => 'campus', 'college' => 'college', 'program' => 'program', 'track' => 'track'] as $column => $key) {
            if (($filters[$key] ?? 'all') !== 'all' && ($filters[$key] ?? '') !== '') {
                $value = $filters[$key];
                $query->whereHas('user', fn (Builder $users) => $users->where($column, $value));
            }
        }
        if (($filters['scholarship_id'] ?? 'all') !== 'all' && ($filters['scholarship_id'] ?? '') !== '') $query->where('scholarship_id', $filters['scholarship_id']);
        if (($filters['scholarship'] ?? '') !== '') $query->whereHas('scholarship', fn (Builder $scholarship) => $scholarship->where('scholarship_name', $filters['scholarship']));
        if (($filters['status'] ?? 'all') !== 'all' && ($filters['status'] ?? '') !== '') $query->where('status', $filters['status'] === 'under_review' ? 'in_progress' : $filters['status']);
        if (($filters['academic_year'] ?? 'all') !== 'all' && preg_match('/^(\d{4})-(\d{4})$/', $filters['academic_year'], $years)) {
            $query->whereBetween('created_at', [Carbon::create($years[1], 8, 1)->startOfDay(), Carbon::create($years[2], 7, 31)->endOfDay()]);
        }
        if (($filters['semester'] ?? 'all') === 'first') $query->whereMonth('created_at', '>=', 8);
        if (($filters['semester'] ?? 'all') === 'second') $query->whereMonth('created_at', '<=', 7);
        return $query;
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
