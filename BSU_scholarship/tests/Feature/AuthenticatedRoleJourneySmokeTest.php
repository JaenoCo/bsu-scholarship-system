<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\TestingQaAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Server-rendered smoke coverage for the role journeys. These requests run
 * against the same isolated database configured in phpunit.xml.
 */
class AuthenticatedRoleJourneySmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TestingQaAccountSeeder::class);
    }

    public function test_student_can_open_the_application_grade_and_tracking_journey_screens(): void
    {
        $student = User::where('email', 'qa.student@example.test')->firstOrFail();

        $this->withSession(['user_id' => $student->id, 'role' => 'student'])
            ->get(route('student.dashboard', ['tab' => 'announcements']))
            ->assertOk();
        $this->get(route('student.grades.upload'))->assertOk();
        $this->get(route('student.applications'))->assertRedirect();
    }

    public function test_sfao_can_open_management_release_and_report_journey_screens(): void
    {
        $sfao = User::where('email', 'qa.sfao@example.test')->firstOrFail();

        $this->withSession(['user_id' => $sfao->id, 'role' => 'sfao'])
            ->get(route('sfao.scholarships.index'))
            ->assertRedirect(route('sfao.dashboard', ['tabs' => 'all_scholarships']));
        $this->get(route('sfao.reports.grant-summary'))->assertOk();
        $this->get(route('sfao.analytics.scholarships'))->assertOk();
    }

    public function test_central_can_open_review_scholar_management_analytics_and_report_screens(): void
    {
        $central = User::where('email', 'qa.central@example.test')->firstOrFail();

        $this->withSession(['user_id' => $central->id, 'role' => 'central'])
            ->get(route('central.dashboard', ['tabs' => 'all_statistics']))
            ->assertOk();
        $this->get(route('central.scholars.index'))->assertRedirect();
        $this->get(route('central.reports.overall'))->assertOk();
    }
}
