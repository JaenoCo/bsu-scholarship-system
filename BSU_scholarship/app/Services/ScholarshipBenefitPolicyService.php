<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Scholarship;
use App\Models\User;
use Carbon\Carbon;

/**
 * Enforces the benefit-stacking rules after an application has been approved.
 * Students may submit every application for which they qualify; these rules
 * only determine whether an approved benefit can be claimed.
 */
class ScholarshipBenefitPolicyService
{
    public const PRIVATE_SCHOLARSHIP_LIMIT = 3;

    public function currentSemester(?Carbon $date = null): string
    {
        $date ??= now();
        $year = (int) $date->year;

        // First semester runs August–December; second runs January–July.
        if ($date->month >= 8) {
            return sprintf('%d-%d-1', $year, $year + 1);
        }

        return sprintf('%d-%d-2', $year - 1, $year);
    }

    public function currentSemesterStartsAt(?Carbon $date = null): Carbon
    {
        $date ??= now();

        return $date->month >= 8
            ? $date->copy()->setDate($date->year, 8, 1)->startOfDay()
            : $date->copy()->setDate($date->year, 1, 1)->startOfDay();
    }

    public function hasGovernmentClaimForCurrentSemester(int $userId, ?int $exceptScholarshipId = null): bool
    {
        $term = $this->currentSemester();
        $termStart = $this->currentSemesterStartsAt();

        return Application::query()
            ->where('user_id', $userId)
            ->whereHas('scholarship', fn ($query) => $query->where('scholarship_type', 'government'))
            ->when($exceptScholarshipId, fn ($query) => $query->where('scholarship_id', '!=', $exceptScholarshipId))
            ->where(function ($query) use ($term, $termStart) {
                $query->where('claim_term', $term)
                    // Records claimed before this feature have no term marker. Keep
                    // them effective when their claim happened in this semester.
                    ->orWhere(function ($legacyQuery) use ($termStart) {
                        $legacyQuery->whereNull('claim_term')
                            ->where('status', 'claimed')
                            ->where('updated_at', '>=', $termStart);
                    });
            })
            ->exists();
    }

    public function isGovernmentScholarshipLocked(User|int $student, Scholarship $scholarship): bool
    {
        if ($scholarship->scholarship_type !== 'government') {
            return false;
        }

        $userId = $student instanceof User ? $student->id : $student;

        return $this->hasGovernmentClaimForCurrentSemester($userId, $scholarship->id);
    }

    public function activePrivateScholarshipCount(int $userId, ?int $exceptScholarshipId = null): int
    {
        return Application::query()
            ->where('user_id', $userId)
            ->whereHas('scholarship', fn ($query) => $query->where('scholarship_type', 'private'))
            ->when($exceptScholarshipId, fn ($query) => $query->where('scholarship_id', '!=', $exceptScholarshipId))
            ->where(function ($query) {
                $query->where('status', 'claimed')
                    ->orWhere('grant_count', '>', 0);
            })
            // Some legacy claims have no Scholar row. Treat those as active so
            // they cannot bypass the cap; otherwise only active scholar records
            // count as simultaneously enjoyed private scholarships.
            ->where(function ($query) {
                $query->whereDoesntHave('scholar')
                    ->orWhereHas('scholar', fn ($scholarQuery) => $scholarQuery->where('status', 'active'));
            })
            ->distinct('scholarship_id')
            ->count('scholarship_id');
    }

    /** Returns a student-safe reason when the benefit may not be claimed. */
    public function claimBlockReason(Application $application): ?string
    {
        $application->loadMissing('scholarship');
        $scholarship = $application->scholarship;

        if (!$scholarship) {
            return 'This scholarship is no longer available.';
        }

        return $this->claimBlockReasonForScholarship($application->user_id, $scholarship);
    }

    /** Returns a student-safe reason before any grant is released. */
    public function claimBlockReasonForScholarship(int $userId, Scholarship $scholarship): ?string
    {
        if ($scholarship->scholarship_type === 'government'
            && $this->hasGovernmentClaimForCurrentSemester($userId, $scholarship->id)) {
            return 'The student has already claimed a government scholarship this semester. Other government grants become available again during the next semestral application period.';
        }

        if ($scholarship->scholarship_type === 'private'
            && $this->activePrivateScholarshipCount($userId, $scholarship->id) >= self::PRIVATE_SCHOLARSHIP_LIMIT) {
            return 'The student is already receiving the maximum of three private scholarships simultaneously.';
        }

        return null;
    }

    public function recordClaim(Application $application): void
    {
        $application->claimed_at = now();
        $application->claim_term = $this->currentSemester();
    }

    public function claimedNotificationMessage(Application $application): string
    {
        $application->loadMissing('scholarship');
        $name = $application->scholarship?->scholarship_name ?? 'this scholarship';

        if ($application->scholarship?->scholarship_type === 'government') {
            return "Your grant for {$name} has been released and marked as claimed. You may continue applying for every scholarship you qualify for, but other government scholarship benefits are unavailable until the next semestral application period.";
        }

        if ($application->scholarship?->scholarship_type === 'private'
            && $this->activePrivateScholarshipCount($application->user_id) >= self::PRIVATE_SCHOLARSHIP_LIMIT) {
            return "Your grant for {$name} has been released and marked as claimed. You are now receiving the maximum of three private scholarships simultaneously.";
        }

        return "Your grant for {$name} has been released and marked as claimed.";
    }
}
