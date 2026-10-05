<?php

namespace Tests\Feature;

use App\Mail\GrantSlipMail;
use App\Models\Application;
use App\Models\Campus;
use App\Models\GrantRelease;
use App\Models\Notification;
use App\Models\Scholar;
use App\Models\Scholarship;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GrantReleaseWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Campus $campus;
    private User $sfao;
    private Scholarship $scholarship;

    protected function setUp(): void
    {
        parent::setUp();

        $this->campus = Campus::create([
            'name' => 'Test Campus',
            'type' => 'constituent',
        ]);
        $this->sfao = User::factory()->create([
            'role' => 'sfao',
            'campus_id' => $this->campus->id,
        ]);
        $this->scholarship = Scholarship::create([
            'scholarship_name' => 'Test Scholarship',
            'description' => 'Test scholarship grant',
            'submission_deadline' => now()->addMonth(),
            'application_start_date' => now(),
            'created_by' => $this->sfao->id,
            'grant_amount' => 5000,
            'grant_type' => 'recurring',
            'scholarship_type' => 'private',
        ]);
    }

    public function test_release_targets_only_active_approved_beneficiaries_and_is_idempotent(): void
    {
        Mail::fake();

        $beneficiary = $this->createBeneficiary('approved', 'active');
        $this->createBeneficiary('rejected', 'active');
        $this->createBeneficiary('approved', 'inactive');

        $otherCampus = Campus::create([
            'name' => 'Other Campus',
            'type' => 'constituent',
        ]);
        $outsideStudent = User::factory()->create([
            'role' => 'student',
            'campus_id' => $otherCampus->id,
        ]);
        $this->createBeneficiary('approved', 'active', $outsideStudent);

        session(['user_id' => $this->sfao->id, 'role' => 'sfao']);
        $route = route('sfao.scholarships.release-grant', $this->scholarship->id);

        $this->post($route)->assertSessionHas('success');
        $release = GrantRelease::sole();

        $this->assertSame($beneficiary->id, $release->user_id);
        $this->assertSame('released', $release->status);
        $this->assertSame('5000.00', $release->amount);
        $this->assertMatchesRegularExpression('/^GRANT-\d{4}-\d{6}$/', $release->tracking_number);
        $this->assertStringContainsString('<svg', $release->qr_code);
        $this->assertDatabaseHas('notifications', [
            'id' => $release->notification_id,
            'user_id' => $beneficiary->id,
            'type' => 'grant_released',
            'is_read' => false,
        ]);
        $this->assertSame(1, NotificationService::getUnreadCount($beneficiary->id, 'grant_released'));
        Mail::assertSent(GrantSlipMail::class, 1);

        $this->get(route('sfao.reports.grant-summary', ['campus_id' => (string) $this->campus->id]))
            ->assertOk()
            ->assertSee($release->tracking_number)
            ->assertSee('Grant Tracking Validation');

        Notification::create([
            'user_id' => $beneficiary->id,
            'type' => 'application_status',
            'title' => 'Application Status Update',
            'message' => 'An unrelated notification.',
        ]);
        $this->assertSame(1, NotificationService::getUnreadCount($beneficiary->id, 'grant_released'));
        $this->assertSame(1, NotificationService::getUnreadCount($beneficiary->id, 'application_status'));

        session(['user_id' => $beneficiary->id, 'role' => 'student']);
        $markReadRoute = route('notifications.mark-read', $release->notification_id);
        $this->postJson($markReadRoute)->assertJsonPath('changed', true);
        $this->assertSame(0, NotificationService::getUnreadCount($beneficiary->id, 'grant_released'));
        $this->postJson($markReadRoute)->assertJsonPath('changed', false);
        $this->assertSame(0, NotificationService::getUnreadCount($beneficiary->id, 'grant_released'));

        $this->postJson(route('notifications.mark-unread', $release->notification_id))
            ->assertJsonPath('changed', true);
        $this->assertSame(1, NotificationService::getUnreadCount($beneficiary->id, 'grant_released'));
        session(['user_id' => $this->sfao->id, 'role' => 'sfao']);

        $this->post($route)->assertSessionHas('error');

        $this->assertSame(1, GrantRelease::count());
        $this->assertSame(1, Notification::where('type', 'grant_released')->count());
        Mail::assertSent(GrantSlipMail::class, 1);
    }

    public function test_missing_email_keeps_the_release_and_notification_and_reports_delivery_failure(): void
    {
        Mail::fake();

        $beneficiary = $this->createBeneficiary('approved', 'active');
        $beneficiary->update(['email' => '']);
        session(['user_id' => $this->sfao->id, 'role' => 'sfao']);

        $this->post(route('sfao.scholarships.release-grant', $this->scholarship->id))
            ->assertSessionHas('email_warnings');

        $release = GrantRelease::sole();
        $this->assertNotNull($release->email_error);
        $this->assertSame($beneficiary->id, $release->user_id);
        $this->assertDatabaseHas('notifications', [
            'id' => $release->notification_id,
            'user_id' => $beneficiary->id,
            'type' => 'grant_released',
        ]);
        Mail::assertNothingSent();
    }

    public function test_legacy_active_scholar_with_approved_application_is_included_without_direct_application_link(): void
    {
        Mail::fake();

        $student = User::factory()->create([
            'role' => 'student',
            'campus_id' => $this->campus->id,
        ]);
        Application::create([
            'user_id' => $student->id,
            'scholarship_id' => $this->scholarship->id,
            'status' => 'approved',
        ]);
        Scholar::create([
            'user_id' => $student->id,
            'scholarship_id' => $this->scholarship->id,
            'type' => 'new',
            'grant_count' => 0,
            'total_grant_received' => 0,
            'scholarship_start_date' => now()->toDateString(),
            'status' => 'active',
        ]);
        session(['user_id' => $this->sfao->id, 'role' => 'sfao']);

        $this->post(route('sfao.scholarships.release-grant', $this->scholarship->id))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('grant_releases', [
            'user_id' => $student->id,
            'scholarship_id' => $this->scholarship->id,
        ]);
        Mail::assertSent(GrantSlipMail::class);
    }

    private function createBeneficiary(string $applicationStatus, string $scholarStatus, ?User $student = null): User
    {
        $student ??= User::factory()->create([
            'role' => 'student',
            'campus_id' => $this->campus->id,
        ]);

        $application = Application::create([
            'user_id' => $student->id,
            'scholarship_id' => $this->scholarship->id,
            'status' => $applicationStatus,
        ]);

        Scholar::create([
            'user_id' => $student->id,
            'scholarship_id' => $this->scholarship->id,
            'application_id' => $application->id,
            'type' => 'new',
            'grant_count' => 0,
            'total_grant_received' => 0,
            'scholarship_start_date' => now()->toDateString(),
            'status' => $scholarStatus,
        ]);

        return $student;
    }
}
