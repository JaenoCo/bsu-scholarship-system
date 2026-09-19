<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Campus;
use App\Models\Scholar;
use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SfaoAnalyticsEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_sfao_roles_cannot_access_analytics_endpoints(): void
    {
        $campus = Campus::create(['name' => 'Test Campus', 'type' => 'constituent']);

        foreach (['student', 'central'] as $role) {
            $user = User::factory()->create(['role' => $role, 'campus_id' => $campus->id]);
            $response = $this->withSession(['user_id' => $user->id, 'role' => $role])
                ->getJson(route('sfao.analytics.applicants'));

            $response->assertStatus(403);
        }
    }

    public function test_applicant_analytics_is_distinct_and_scoped_to_sfao_campuses(): void
    {
        $inside = Campus::create(['name' => 'Inside Campus', 'type' => 'constituent']);
        $outside = Campus::create(['name' => 'Outside Campus', 'type' => 'constituent']);
        $sfao = User::factory()->create(['role' => 'sfao', 'campus_id' => $inside->id]);
        $insideStudent = User::factory()->create(['role' => 'student', 'campus_id' => $inside->id]);
        $outsideStudent = User::factory()->create(['role' => 'student', 'campus_id' => $outside->id]);
        $scholarship = Scholarship::create([
            'scholarship_name' => 'Scoped Scholarship',
            'description' => 'Test scholarship',
            'submission_deadline' => now()->addMonth(),
            'application_start_date' => now(),
            'created_by' => $sfao->id,
        ]);

        Application::create(['user_id' => $insideStudent->id, 'scholarship_id' => $scholarship->id, 'status' => 'pending']);
        Application::create(['user_id' => $insideStudent->id, 'scholarship_id' => $scholarship->id, 'status' => 'approved']);
        Application::create(['user_id' => $outsideStudent->id, 'scholarship_id' => $scholarship->id, 'status' => 'approved']);

        $response = $this->withSession(['user_id' => $sfao->id, 'role' => 'sfao'])
            ->getJson(route('sfao.analytics.applicants'));

        $response->assertOk()->assertJsonPath('summary.totalApplicants', 1);
        $response->assertJsonPath('summary.completed', 1);
    }

    public function test_empty_analytics_returns_zero_approval_rate(): void
    {
        $campus = Campus::create(['name' => 'Empty Campus', 'type' => 'constituent']);
        $sfao = User::factory()->create(['role' => 'sfao', 'campus_id' => $campus->id]);

        $response = $this->withSession(['user_id' => $sfao->id, 'role' => 'sfao'])
            ->getJson(route('sfao.analytics.scholarships'));

        $response->assertOk()->assertJsonPath('summary.approvalRate', 0);
    }

    public function test_scholar_analytics_returns_status_totals_for_the_sfao_scope(): void
    {
        $campus = Campus::create(['name' => 'Scholar Campus', 'type' => 'constituent']);
        $sfao = User::factory()->create(['role' => 'sfao', 'campus_id' => $campus->id]);
        $student = User::factory()->create(['role' => 'student', 'campus_id' => $campus->id]);
        $scholarship = Scholarship::create([
            'scholarship_name' => 'Scholar Test Program',
            'description' => 'Test scholarship',
            'submission_deadline' => now()->addMonth(),
            'application_start_date' => now(),
            'created_by' => $sfao->id,
        ]);

        Scholar::create([
            'user_id' => $student->id,
            'scholarship_id' => $scholarship->id,
            'type' => 'new',
            'status' => 'active',
            'scholarship_start_date' => now()->startOfMonth(),
        ]);

        $response = $this->withSession(['user_id' => $sfao->id, 'role' => 'sfao'])
            ->getJson(route('sfao.analytics.scholars'));

        $response->assertOk()
            ->assertJsonPath('summary.totalScholars', 1)
            ->assertJsonPath('summary.active', 1)
            ->assertJsonFragment(['name' => 'active', 'total' => 1]);
    }
}
