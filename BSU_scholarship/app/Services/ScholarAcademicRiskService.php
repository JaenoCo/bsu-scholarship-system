<?php

namespace App\Services;

use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Support\Collection;

class ScholarAcademicRiskService
{
    public function evaluate(User $student, Collection $history, ?Scholarship $scholarship = null): array
    {
        $history = $history
            ->filter(fn ($record) => is_numeric(data_get($record, 'verified_gwa')) && (float) data_get($record, 'verified_gwa') > 0)
            ->sortBy(fn ($record) => sprintf('%s|%02d', data_get($record, 'school_year', ''), $this->semesterOrder(data_get($record, 'semester'))))
            ->values();

        $latest = $history->last();
        $latestGwa = $latest ? (float) data_get($latest, 'verified_gwa') : null;
        $previousGwa = $history->count() > 1
            ? (float) data_get($history->get($history->count() - 2), 'verified_gwa')
            : $latestGwa;
        $delta = $latestGwa !== null && $previousGwa !== null ? round($latestGwa - $previousGwa, 2) : null;

        $retention = $this->retentionRisk($latestGwa, $delta, $scholarship?->getGwaRequirement());
        $graduation = $this->graduationRisk($student, $history->count());

        return [
            'student_id' => $student->id,
            'latest_gwa' => $latestGwa,
            'previous_gwa' => $previousGwa,
            'gwa_delta' => $delta,
            'trend' => $delta === null ? 'unknown' : ($delta > 0 ? 'declining' : ($delta < 0 ? 'improving' : 'stable')),
            'graduation' => $graduation,
            'retention' => $retention,
            'status' => $this->highestStatus($graduation['status'], $retention['status']),
            'reasons' => array_values(array_merge($graduation['reasons'], $retention['reasons'])),
            'data_status' => $history->isEmpty() ? 'missing' : ($history->count() === 1 ? 'limited' : 'sufficient'),
        ];
    }

    private function retentionRisk(?float $gwa, ?float $delta, mixed $requirement): array
    {
        if ($gwa === null || !is_numeric($requirement) || (float) $requirement <= 0) {
            return ['status' => $gwa === null ? 'At-Risk' : 'On Track', 'score' => null, 'reasons' => $gwa === null ? ['Verified GWA is needed'] : []];
        }

        $requirement = (float) $requirement;
        $margin = $requirement - $gwa;
        $score = max(0, min(100, round((($gwa - max(1, $requirement - 0.5)) / 0.5) * 100)));
        $status = 'On Track';
        $reasons = [];

        if ($gwa > $requirement || ($delta !== null && $delta >= config('academic_risk.worsening_critical_delta') && $margin <= config('academic_risk.retention_warning_margin'))) {
            $status = 'Critical';
            $reasons[] = $gwa > $requirement ? 'Latest GWA is below the scholarship retention requirement' : 'GWA is declining sharply near the retention threshold';
        } elseif ($margin <= config('academic_risk.retention_warning_margin') || ($delta !== null && $delta >= config('academic_risk.worsening_warning_delta'))) {
            $status = 'At-Risk';
            $reasons[] = $margin <= config('academic_risk.retention_warning_margin') ? 'Latest GWA is close to the scholarship retention threshold' : 'GWA trend is declining';
        }

        return ['status' => $status, 'score' => $score, 'reasons' => $reasons];
    }

    private function graduationRisk(User $student, int $completedSemesters): array
    {
        $expected = (int) config('academic_risk.expected_semesters.' . strtolower((string) $student->education_level), config('academic_risk.expected_semesters.default', 8));
        $yearLevel = $this->yearNumber($student->year_level);
        $expectedCompleted = $yearLevel ? min($expected, $yearLevel * 2) : null;

        if ($yearLevel === null || $completedSemesters === 0) {
            return ['status' => 'At-Risk', 'score' => null, 'reasons' => ['Academic progress data is incomplete']];
        }

        $shortfall = max(0, $expectedCompleted - $completedSemesters);
        $status = $shortfall >= 2 ? 'Critical' : ($shortfall === 1 ? 'At-Risk' : 'On Track');
        $reasons = $shortfall > 0 ? [sprintf('%d expected semester record%s missing for the current year level', $shortfall, $shortfall === 1 ? '' : 's')] : [];

        return ['status' => $status, 'score' => min(100, $shortfall * 50), 'reasons' => $reasons];
    }

    private function highestStatus(string $first, string $second): string
    {
        $rank = ['On Track' => 0, 'At-Risk' => 1, 'Critical' => 2];
        return ($rank[$first] ?? 0) >= ($rank[$second] ?? 0) ? $first : $second;
    }

    private function semesterOrder(mixed $semester): int
    {
        return match (strtolower((string) $semester)) {
            '1st semester' => 1,
            '2nd semester' => 2,
            'summer' => 3,
            default => 0,
        };
    }

    private function yearNumber(mixed $yearLevel): ?int
    {
        if (preg_match('/([1-9])/', (string) $yearLevel, $matches)) {
            return (int) $matches[1];
        }

        return is_numeric($yearLevel) ? (int) $yearLevel : null;
    }
}
