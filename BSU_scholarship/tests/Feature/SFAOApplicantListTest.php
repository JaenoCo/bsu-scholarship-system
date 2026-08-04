<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Campus;
use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SFAOApplicantListTest extends TestCase
{
    use RefreshDatabase;

    public function test_applicant_counts_are_distinct_students_when_multiple_applications_exist(): void
    {
        $campus = Campus::create([
            'name' => 'Test Campus',
            'type' => 'constituent',
        ]);

        $sfaoUser = User::factory()->create([
            'role' => 'sfao',
            'campus_id' => $campus->id,
            'first_name' => 'SFAO',
            'last_name' => 'User',
        ]);

        $studentOne = User::factory()->create([
            'role' => 'student',
            'campus_id' => $campus->id,
            'name' => 'First Student',
            'first_name' => 'First',
            'last_name' => 'Student',
        ]);

        $studentTwo = User::factory()->create([
            'role' => 'student',
            'campus_id' => $campus->id,
            'name' => 'Second Student',
            'first_name' => 'Second',
            'last_name' => 'Student',
        ]);

        $scholarship = Scholarship::create([
            'scholarship_name' => 'Test Scholarship',
            'description' => 'Test Description',
            'submission_deadline' => now()->addMonth(),
            'application_start_date' => now(),
            'created_by' => $sfaoUser->id,
        ]);

        Application::create([
            'user_id' => $studentOne->id,
            'scholarship_id' => $scholarship->id,
            'status' => 'pending',
        ]);
        Application::create([
            'user_id' => $studentOne->id,
            'scholarship_id' => $scholarship->id,
            'status' => 'approved',
        ]);
        Application::create([
            'user_id' => $studentTwo->id,
            'scholarship_id' => $scholarship->id,
            'status' => 'pending',
        ]);

        session(['user_id' => $sfaoUser->id, 'role' => 'sfao']);

        $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('sfao.applicants.list', ['tab' => 'applicants']));

        $response->assertStatus(200);
        $response->assertJsonPath('counts.total', 2);
        $response->assertJsonPath('counts.pending', 2);
        $response->assertJsonPath('counts.approved', 1);
        $response->assertJsonPath('counts.rejected', 0);
    }

    public function test_pending_tab_returns_only_students_with_pending_applications(): void
    {
        $campus = Campus::create([
            'name' => 'Test Campus',
            'type' => 'constituent',
        ]);

        $sfaoUser = User::factory()->create([
            'role' => 'sfao',
            'campus_id' => $campus->id,
            'first_name' => 'SFAO',
            'last_name' => 'User',
        ]);

        $pendingStudent = User::factory()->create([
            'role' => 'student',
            'campus_id' => $campus->id,
            'name' => 'Pending Student',
            'first_name' => 'Pending',
            'last_name' => 'Student',
        ]);

        $approvedStudent = User::factory()->create([
            'role' => 'student',
            'campus_id' => $campus->id,
            'name' => 'Approved Student',
            'first_name' => 'Approved',
            'last_name' => 'Student',
        ]);

        $scholarship = Scholarship::create([
            'scholarship_name' => 'Test Scholarship',
            'description' => 'Test Description',
            'submission_deadline' => now()->addMonth(),
            'application_start_date' => now(),
            'created_by' => $sfaoUser->id,
        ]);

        Application::create([
            'user_id' => $pendingStudent->id,
            'scholarship_id' => $scholarship->id,
            'status' => 'pending',
        ]);

        Application::create([
            'user_id' => $approvedStudent->id,
            'scholarship_id' => $scholarship->id,
            'status' => 'approved',
        ]);

        session(['user_id' => $sfaoUser->id, 'role' => 'sfao']);

        $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('sfao.applicants.list', ['tab' => 'applicants-pending']));

        $response->assertStatus(200);
        $response->assertJsonPath('counts.total', 2);
        $response->assertJsonPath('counts.pending', 1);

        $html = $response->json('html');
        $this->assertStringContainsString('Pending Student', $html);
        $this->assertStringNotContainsString('Approved Student', $html);
    }
}
