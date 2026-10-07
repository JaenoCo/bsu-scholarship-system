<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\ScholarAcademicRiskService;
use Tests\TestCase;

class ScholarAcademicRiskServiceTest extends TestCase
{
    public function test_gwa_thresholds_set_the_gwa_and_overall_risk_status(): void
    {
        $service = new ScholarAcademicRiskService();
        $student = new User([
            'education_level' => 'undergraduate',
            'year_level' => '1st Year',
        ]);

        foreach ([
            [2.50, 'Critical'],
            [2.51, 'At-Risk'],
            [2.75, 'At-Risk'],
            [2.76, 'On Track'],
        ] as [$gwa, $expectedStatus]) {
            $risk = $service->evaluate($student, collect([
                ['verified_gwa' => $gwa, 'school_year' => '2025-2026', 'semester' => '1st semester'],
                ['verified_gwa' => $gwa, 'school_year' => '2025-2026', 'semester' => '2nd semester'],
            ]));

            $this->assertSame($expectedStatus, $risk['gwa_risk']['status']);
            $this->assertSame($expectedStatus, $risk['status']);
        }
    }
}
