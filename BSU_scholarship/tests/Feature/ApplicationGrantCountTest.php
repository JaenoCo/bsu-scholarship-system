<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Campus;
use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationGrantCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_next_grant_count_uses_the_highest_existing_grant_count(): void
    {
        $campus = Campus::create([
            'name' => 'Test Campus',
            'type' => 'constituent',
        ]);

        $user = User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'campus_id' => $campus->id,
        ]);
        $scholarship = Scholarship::create([
            'scholarship_name' => 'Test Scholarship',
            'description' => 'Test scholarship',
            'submission_deadline' => now()->addMonth(),
            'application_start_date' => now(),
            'created_by' => $user->id,
        ]);

        Application::create([
            'user_id' => $user->id,
            'scholarship_id' => $scholarship->id,
            'status' => 'claimed',
            'grant_count' => 1,
        ]);

        Application::create([
            'user_id' => $user->id,
            'scholarship_id' => $scholarship->id,
            'status' => 'claimed',
            'grant_count' => 2,
        ]);

        $this->assertSame(2, Application::getTotalGrantCount($user->id, $scholarship->id));
        $this->assertSame(3, Application::getNextGrantCount($user->id, $scholarship->id));
    }

    public function test_legacy_approved_applications_with_grant_count_are_treated_as_claimed(): void
    {
        $campus = Campus::create([
            'name' => 'Test Campus',
            'type' => 'constituent',
        ]);

        $user = User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'campus_id' => $campus->id,
        ]);
        $scholarship = Scholarship::create([
            'scholarship_name' => 'Test Scholarship',
            'description' => 'Test scholarship',
            'submission_deadline' => now()->addMonth(),
            'application_start_date' => now(),
            'created_by' => $user->id,
        ]);

        Application::create([
            'user_id' => $user->id,
            'scholarship_id' => $scholarship->id,
            'status' => 'approved',
            'grant_count' => 1,
        ]);

        $this->assertTrue(Application::hasClaimedGrant($user->id, $scholarship->id));
        $this->assertSame(1, Application::getTotalGrantCount($user->id, $scholarship->id));
        $this->assertSame(2, Application::getNextGrantCount($user->id, $scholarship->id));
    }

    public function test_central_acceptance_creates_scholar_and_marks_application_as_approved(): void
    {
        $campus = Campus::create([
            'name' => 'Test Campus',
            'type' => 'constituent',
        ]);

        $centralUser = User::factory()->create([
            'name' => 'Central Admin',
            'first_name' => 'Central',
            'last_name' => 'Admin',
            'role' => 'central',
            'campus_id' => $campus->id,
        ]);

        $studentUser = User::factory()->create([
            'name' => 'Student Applicant',
            'first_name' => 'Student',
            'last_name' => 'Applicant',
            'role' => 'student',
            'campus_id' => $campus->id,
        ]);

        $scholarship = Scholarship::create([
            'scholarship_name' => 'Test Scholarship',
            'description' => 'Test scholarship',
            'submission_deadline' => now()->addMonth(),
            'application_start_date' => now(),
            'created_by' => $centralUser->id,
        ]);

        $application = Application::create([
            'user_id' => $studentUser->id,
            'scholarship_id' => $scholarship->id,
            'status' => 'in_progress',
        ]);

        session(['user_id' => $centralUser->id, 'role' => 'central']);

        $response = $this->post(route('central.endorsed.accept', $application->id));

        $response->assertRedirectToRoute('central.dashboard', ['tab' => 'endorsed_applicants']);
        $this->assertSame('approved', $application->fresh()->status);
        $this->assertDatabaseHas('scholars', [
            'application_id' => $application->id,
            'user_id' => $studentUser->id,
            'scholarship_id' => $scholarship->id,
        ]);
    }

    public function test_filtered_analytics_returns_status_counts_for_selected_filters(): void
    {
        $campus = Campus::create([
            'name' => 'Test Campus',
            'type' => 'constituent',
        ]);

        $centralUser = User::factory()->create([
            'name' => 'Central Admin',
            'first_name' => 'Central',
            'last_name' => 'Admin',
            'role' => 'central',
            'campus_id' => $campus->id,
        ]);

        $studentUser = User::factory()->create([
            'name' => 'Student Applicant',
            'first_name' => 'Student',
            'last_name' => 'Applicant',
            'role' => 'student',
            'campus_id' => $campus->id,
        ]);

        $scholarship = Scholarship::create([
            'scholarship_name' => 'Filtered Scholarship',
            'description' => 'Test scholarship',
            'submission_deadline' => now()->addMonth(),
            'application_start_date' => now(),
            'created_by' => $centralUser->id,
        ]);

        Application::create([
            'user_id' => $studentUser->id,
            'scholarship_id' => $scholarship->id,
            'status' => 'approved',
        ]);
        Application::create([
            'user_id' => $studentUser->id,
            'scholarship_id' => $scholarship->id,
            'status' => 'rejected',
        ]);
        Application::create([
            'user_id' => $studentUser->id,
            'scholarship_id' => $scholarship->id,
            'status' => 'pending',
        ]);
        Application::create([
            'user_id' => $studentUser->id,
            'scholarship_id' => $scholarship->id,
            'status' => 'in_progress',
        ]);

        session(['user_id' => $centralUser->id, 'role' => 'central']);

        $response = $this->postJson(route('central.analytics.filtered'), [
            'filters' => [
                'campus' => 'all',
                'scholarship' => (string) $scholarship->id,
                'timePeriod' => 'all',
            ],
        ]);

        $response->assertOk();
        $response->assertJsonPath('analytics.counts.total', 4);
        $response->assertJsonPath('analytics.counts.approved', 1);
        $response->assertJsonPath('analytics.counts.rejected', 1);
        $response->assertJsonPath('analytics.counts.active', 2);
        $response->assertJsonPath('analytics.counts.approvalRate', '25.0');
    }
}
