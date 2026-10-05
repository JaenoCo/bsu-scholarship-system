<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Scholarship;
use App\Models\GrantRelease;
use App\Models\Notification;
use App\Models\Application;
use App\Models\User;
use App\Models\ScholarshipRequiredCondition;
use App\Models\ScholarshipRequiredDocument;
use App\Services\NotificationService;
use App\Services\ScholarshipBenefitPolicyService;
use App\Models\Scholar;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\GrantSlipMail;

/**
 * =====================================================
 * SCHOLARSHIP MANAGEMENT CONTROLLER
 * =====================================================
 * 
 * This controller handles all scholarship-related functionality
 * including creation, management, viewing, and requirements
 * for both Central and SFAO roles.
 * 
 * Combined functionality from:
 * - ScholarshipController
 * - ScholarshipRequirementController
 * - ScholarshipConditionController
 */
class ScholarshipController extends Controller
{
    // =====================================================
    // CENTRAL SCHOLARSHIP MANAGEMENT
    // =====================================================

    /**
     * List all scholarships with their conditions & requirements (Central)
     */
    public function centralIndex(Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        return redirect()->route('central.dashboard', ['tabs' => 'all_scholarships']);
    }

    /**
     * Show create scholarship form (Central)
     */
    public function create()
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        $colleges = \App\Models\College::orderBy('short_name')->get(['id', 'name', 'short_name']);
        $campuses = \App\Models\Campus::orderBy('name')->get();
        $programs = \App\Models\Program::with('campusCollege.college')->orderBy('name')->get();
        $tracks = \App\Models\ProgramTrack::with('program')->orderBy('name')->get();
        $campusColleges = \App\Models\CampusCollege::all(['campus_id', 'college_id']);
        return view('central.scholarships.create', compact('colleges', 'campuses', 'programs', 'tracks', 'campusColleges'));
    }

    /**
     * Store new scholarship (Central)
     */
    public function store(Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        $request->validate([
            'scholarship_name' => 'required|string|max:255',
            'scholarship_type' => 'required|in:private,government',
            'description'      => 'required|string',
            'announcement_title' => 'nullable|string|max:255',
            'announcement_message' => 'nullable|string',
            'submission_deadline' => 'required|date|after:today',
            'application_start_date' => 'nullable|date|before:submission_deadline',
            'slots_available'  => 'nullable|integer|min:0',
            'grant_amount'     => 'nullable|numeric|min:0',
            // 'campus_id'        => 'nullable|integer', // Replaced by campuses array
            'campuses'         => 'nullable|array',
            'campuses.*'       => 'exists:campuses,id',
            'target_colleges' => 'nullable|array',
            'target_colleges.*' => 'exists:colleges,id',
            'target_programs' => 'nullable|array',
            'target_programs.*' => 'exists:programs,id',
            'target_tracks' => 'nullable|array',
            'target_tracks.*' => 'exists:program_tracks,id',
            'grant_type'       => 'required|in:one_time,recurring,discontinued',
            'eligibility_notes' => 'nullable|string',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Handle background image upload
            $backgroundImagePath = null;
            if ($request->hasFile('background_image')) {
                $file = $request->file('background_image');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('scholarship_images', $filename, 'public');
                $backgroundImagePath = $filename;
            }

            // Automatically set renewal_allowed based on grant_type
            $renewalAllowed = $request->grant_type === 'recurring';

            $scholarship = Scholarship::create([
                'scholarship_name' => $request->scholarship_name,
                'scholarship_type' => $request->scholarship_type,
                'description'      => $request->description,
                'submission_deadline' => $request->submission_deadline,
                'application_start_date' => $request->application_start_date,
                'slots_available'  => $request->slots_available,
                'grant_amount'     => $request->grant_amount,
                'campus_id'        => null, // Deprecated or Primary Campus logic, setting null for now
                'renewal_allowed'  => $renewalAllowed,
                'grant_type'       => $request->grant_type,
                'eligibility_notes' => $request->eligibility_notes,
            'announcement_title' => $request->announcement_title,
            'announcement_message' => $request->announcement_message,
                'allow_existing_scholarship' => $request->has('allow_existing_scholarship'),
                'created_by'       => session('user_id'),
            ]);
            
            // Sync Campuses
            if ($request->has('campuses')) {
                $scholarship->campuses()->sync($request->campuses);
            }
            $this->syncAudienceTargets($scholarship, $request);

            Log::info('Scholarship created successfully:', ['id' => $scholarship->id, 'name' => $scholarship->scholarship_name]);
        } catch (\Exception $e) {
            Log::error('Error creating scholarship:', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Failed to create scholarship: ' . $e->getMessage()]);
        }

        // Save conditions
        if ($request->has('conditions')) {
            foreach ($request->conditions as $cond) {
                $scholarship->conditions()->create([
                    'name' => $cond['type'],
                    'value' => $cond['value'],
                    'is_mandatory' => true, // Conditions are always mandatory
                ]);
            }
        }

        // Save document requirements
        if ($request->has('documents')) {
            foreach ($request->documents as $doc) {
                $scholarship->requiredDocuments()->create([
                    'document_name' => strip_tags($doc['name']),
                    'document_type' => $doc['type'] ?? 'pdf',
                    'is_mandatory' => $doc['mandatory'] ?? true,
                ]);
            }
        }

        // Create notifications for all students
        // Create notifications for all students
        $emailCount = NotificationService::notifyScholarshipCreated($scholarship);

        return redirect()
            ->route('central.dashboard')
            ->with('success', "Scholarship added successfully! Sent notifications to {$emailCount} eligible student(s).");
    }

    /**
     * Show edit scholarship form (Central)
     */
    public function edit($id)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        try {
            $scholarship = Scholarship::with(['conditions', 'requiredDocuments', 'campuses'])->findOrFail($id);
            
            Log::info('Accessing scholarship edit form:', [
                'id' => $scholarship->id,
                'name' => $scholarship->scholarship_name,
                'accessed_by' => session('user_id')
            ]);
            
        $colleges = \App\Models\College::orderBy('short_name')->get(['id', 'name', 'short_name']);
        $campuses = \App\Models\Campus::orderBy('name')->get();
        $programs = \App\Models\Program::with('campusCollege.college')->orderBy('name')->get();
        $tracks = \App\Models\ProgramTrack::with('program')->orderBy('name')->get();
        $campusColleges = \App\Models\CampusCollege::all(['campus_id', 'college_id']);
        return view('central.scholarships.create', compact('colleges', 'campuses', 'programs', 'tracks', 'campusColleges', 'scholarship'));
            
        } catch (\Exception $e) {
            Log::error('Error accessing scholarship edit form:', [
                'id' => $id,
                'error' => $e->getMessage(),
                'accessed_by' => session('user_id')
            ]);
            
            return redirect()
                ->route('central.dashboard')
                ->with('error', 'Scholarship not found or access denied.');
        }
    }

    /**
     * Update scholarship (Central)
     */
    public function update(Request $request, $id)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        $request->validate([
            'scholarship_name' => 'required|string|max:255',
            'scholarship_type' => 'required|in:private,government',
            'description'      => 'required|string',
            'announcement_title' => 'nullable|string|max:255',
            'announcement_message' => 'nullable|string',
            'submission_deadline' => 'required|date|after:today',
            'application_start_date' => 'nullable|date|before:submission_deadline',
            'slots_available'  => 'nullable|integer|min:0',
            'grant_amount'     => 'nullable|numeric|min:0',
            'campuses'         => 'nullable|array',
            'campuses.*'       => 'exists:campuses,id',
            'target_colleges' => 'nullable|array',
            'target_colleges.*' => 'exists:colleges,id',
            'target_programs' => 'nullable|array',
            'target_programs.*' => 'exists:programs,id',
            'target_tracks' => 'nullable|array',
            'target_tracks.*' => 'exists:program_tracks,id',
            'grant_type'       => 'required|in:one_time,recurring,discontinued',
            'eligibility_notes' => 'nullable|string',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $scholarship = Scholarship::findOrFail($id);

        // Handle background image upload
        $backgroundImagePath = $scholarship->background_image; // Keep existing image
        if ($request->hasFile('background_image')) {
            // Delete old image if exists
            if ($scholarship->background_image && Storage::disk('public')->exists('scholarship_images/' . $scholarship->background_image)) {
                Storage::disk('public')->delete('scholarship_images/' . $scholarship->background_image);
            }
            
            $file = $request->file('background_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('scholarship_images', $filename, 'public');
            $backgroundImagePath = $filename;
        }

        // Automatically set renewal_allowed based on grant_type
        $renewalAllowed = $request->grant_type === 'recurring';

        $scholarship->update([
            'scholarship_name' => $request->scholarship_name,
            'scholarship_type' => $request->scholarship_type,
            'description'      => $request->description,
            'announcement_title' => $request->announcement_title,
            'announcement_message' => $request->announcement_message,
            'submission_deadline' => $request->submission_deadline,
            'application_start_date' => $request->application_start_date,
            'campus_id'        => null,
            'slots_available'  => $request->slots_available,
            'grant_amount'     => $request->grant_amount,
            'renewal_allowed'  => $renewalAllowed,
            'grant_type'       => $request->grant_type,
            'eligibility_notes' => $request->eligibility_notes,
            'background_image' => $backgroundImagePath,
            'allow_existing_scholarship' => $request->has('allow_existing_scholarship'),
        ]);

        // Sync Campuses
        if ($request->has('campuses')) {
             $scholarship->campuses()->sync($request->campuses);
        } else {
             // If no campuses sent (and not 'all' logic handled by frontend sending empty array? No, HTML forms don't send unchecked boxes)
             // But if specific input 'campuses' IS missing, it might mean "All" if we have a separate "All" checkbox that prevents 'campuses' from being sent?
             // Actually, if 'campuses' is missing, it usually means none selected.
             // But if we want "All", we might need to handle it.
             // For now, let's assume the frontend sends an empty array if none, or we clear it.
             $scholarship->campuses()->detach();
        }
        $this->syncAudienceTargets($scholarship, $request);

        // Refresh conditions
        $scholarship->conditions()->delete();
        if ($request->has('conditions')) {
            foreach ($request->conditions as $cond) {
                $scholarship->conditions()->create([
                    'name' => $cond['type'],
                    'value' => $cond['value'],
                    'is_mandatory' => true, // Conditions are always mandatory
                ]);
            }
        }

        // Refresh document requirements
        $scholarship->requiredDocuments()->delete();
        if ($request->has('documents')) {
            foreach ($request->documents as $doc) {
                $scholarship->requiredDocuments()->create([
                    'document_name' => strip_tags($doc['name']),
                    'document_type' => $doc['type'] ?? 'pdf',
                    'is_mandatory' => $doc['mandatory'] ?? true,
                ]);
            }
        }

        return redirect()
            ->route('central.dashboard')
            ->with('success', 'Scholarship updated successfully.');
    }

    private function syncAudienceTargets(Scholarship $scholarship, Request $request): void
    {
        $scholarship->targetColleges()->sync($request->input('target_colleges', []));
        $scholarship->targetPrograms()->sync($request->input('target_programs', []));
        $scholarship->targetTracks()->sync($request->input('target_tracks', []));
    }

    /**
     * Archive scholarship (Central)
     */
    public function centralArchive($id)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        try {
            $scholarship = Scholarship::findOrFail($id);
            $scholarship->update(['is_active' => false]);

            return redirect()
                ->route('central.dashboard', ['tabs' => 'archived_scholarships'])
                ->with('success', 'Scholarship archived successfully.');
        } catch (\Exception $e) {
            Log::error('Error archiving scholarship:', ['id' => $id, 'error' => $e->getMessage()]);
            return redirect()
                ->route('central.dashboard')
                ->with('error', 'Failed to archive scholarship.');
        }
    }

    /**
     * Unarchive scholarship (Central)
     */
    public function centralUnarchive($id)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        try {
            $scholarship = Scholarship::findOrFail($id);
            $scholarship->update(['is_active' => true]);

            return redirect()
                ->route('central.dashboard', ['tabs' => 'scholarships'])
                ->with('success', 'Scholarship restored successfully.');
        } catch (\Exception $e) {
            Log::error('Error restoring scholarship:', ['id' => $id, 'error' => $e->getMessage()]);
            return redirect()
                ->route('central.dashboard')
                ->with('error', 'Failed to restore scholarship.');
        }
    }

    /**
     * Delete scholarship (Central)
     */
    public function destroy($id)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        try {
            $scholarship = Scholarship::findOrFail($id);
            
            // Log the deletion attempt
            Log::info('Attempting to delete scholarship:', [
                'id' => $scholarship->id,
                'name' => $scholarship->scholarship_name,
                'deleted_by' => session('user_id')
            ]);

            // Delete related data first
            $scholarship->conditions()->delete();
            $scholarship->requiredDocuments()->delete();
            
            // Delete the scholarship
            $scholarship->delete();

            Log::info('Scholarship deleted successfully:', [
                'id' => $id,
                'name' => $scholarship->scholarship_name
            ]);

            return redirect()
                ->route('central.dashboard')
                ->with('success', 'Scholarship "' . $scholarship->scholarship_name . '" removed successfully.');
                
        } catch (\Exception $e) {
            Log::error('Error deleting scholarship:', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()
                ->route('central.dashboard')
                ->with('error', 'Failed to delete scholarship: ' . $e->getMessage());
        }
    }

    // =====================================================
    // SFAO SCHOLARSHIP MANAGEMENT
    // =====================================================

    /**
     * Release grant for scholarship (SFAO)
     */
    public function releaseGrant(Request $request, $id)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $sfao = User::with('campus')->find(session('user_id'));
        if (! $sfao || ! $sfao->campus) {
            return back()->with('error', 'Your SFAO account is not linked to a campus.');
        }

        $campusIds = $sfao->campus->getAllCampusesUnder()->pluck('id');
        $scholarship = Scholarship::findOrFail($id);

        if ($scholarship->grant_type === 'discontinued' || (float) $scholarship->grant_amount <= 0) {
            return back()->with('error', 'This scholarship does not have a releasable grant amount.');
        }

        try {
            $result = DB::transaction(function () use ($id, $sfao, $campusIds) {
                $scholarship = Scholarship::whereKey($id)->lockForUpdate()->firstOrFail();
                if ($scholarship->grant_type === 'discontinued' || (float) $scholarship->grant_amount <= 0) {
                    return [
                        'error' => 'This scholarship does not have a releasable grant amount.',
                        'releases' => [],
                        'existing_count' => 0,
                    ];
                }

                $approvedApplication = function ($query) use ($scholarship) {
                    $query->where('scholarship_id', $scholarship->id)
                        ->whereIn('status', ['approved', 'claimed']);
                };

                $scholars = Scholar::query()
                    ->where('scholarship_id', $scholarship->id)
                    ->where('status', 'active')
                    ->whereHas('user', function ($query) use ($campusIds) {
                        $query->where('role', 'student')->whereIn('campus_id', $campusIds);
                    })
                    ->where(function ($query) use ($approvedApplication) {
                        $query->whereHas('application', $approvedApplication)
                            ->orWhere(function ($legacyQuery) use ($approvedApplication) {
                                $legacyQuery->whereNull('application_id')
                                    ->whereHas('user', function ($userQuery) use ($approvedApplication) {
                                        $userQuery->whereHas('applications', $approvedApplication);
                                    });
                            });
                    })
                    ->with(['user', 'application'])
                    ->lockForUpdate()
                    ->get();

                $createdReleases = [];
                $existingReleases = 0;
                $benefitPolicy = app(ScholarshipBenefitPolicyService::class);

                foreach ($scholars as $scholar) {
                    $application = $scholar->application ?? Application::query()
                        ->where('user_id', $scholar->user_id)
                        ->where($approvedApplication)
                        ->latest()
                        ->first();
                    if (! $application
                        || (int) $application->user_id !== (int) $scholar->user_id
                        || (int) $application->scholarship_id !== (int) $scholarship->id
                        || $benefitPolicy->claimBlockReason($application)) {
                        continue;
                    }

                    $grantNumber = max((int) $application->grant_count, (int) $scholar->grant_count) + 1;
                    if ($scholarship->grant_type === 'one_time'
                        && ($application->status === 'claimed' || $grantNumber > 1)) {
                        continue;
                    }

                    $existing = GrantRelease::where('application_id', $application->id)
                        ->where('grant_number', $grantNumber)
                        ->exists();
                    if ($existing) {
                        $existingReleases++;
                        continue;
                    }

                    $release = GrantRelease::create([
                        'application_id' => $application->id,
                        'scholar_id' => $scholar->id,
                        'user_id' => $scholar->user_id,
                        'scholarship_id' => $scholarship->id,
                        'released_by' => $sfao->id,
                        'grant_number' => $grantNumber,
                        'amount' => $scholarship->grant_amount,
                        'status' => 'released',
                        'released_at' => now(),
                    ]);

                    $release->tracking_number = sprintf(
                        'GRANT-%s-%06d',
                        $release->released_at->format('Y'),
                        $release->id
                    );
                    $release->qr_code = app(\App\Services\GrantQrCodeService::class)->generate(
                        route('sfao.grant-releases.verify', ['trackingNumber' => $release->tracking_number])
                    );
                    $release->save();

                    $notification = Notification::create([
                        'user_id' => $scholar->user_id,
                        'type' => 'grant_released',
                        'title' => 'Scholarship Grant Released',
                        'message' => sprintf(
                            'Your scholarship "%s" has released its grant of ₱%s. Please visit the SFAO office and present your QR code for verification. Tracking Number: %s',
                            $scholarship->scholarship_name,
                            number_format((float) $release->amount, 2),
                            $release->tracking_number
                        ),
                        'data' => [
                            'grant_release_id' => $release->id,
                            'tracking_number' => $release->tracking_number,
                            'scholarship_id' => $scholarship->id,
                            'scholarship_name' => $scholarship->scholarship_name,
                            'amount' => $release->amount,
                            'redirect_url' => route('student.dashboard', ['tab' => 'my_scholarships'], false),
                        ],
                    ]);

                    $release->notification_id = $notification->id;
                    $release->save();
                    $createdReleases[] = $release->load(['scholar', 'scholarship', 'student']);
                }

                return [
                    'releases' => $createdReleases,
                    'existing_count' => $existingReleases,
                ];
            });
        } catch (\Throwable $e) {
            Log::error('Grant release transaction failed.', [
                'scholarship_id' => $id,
                'sfao_user_id' => $sfao->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'The grant could not be released. No release or notification records were committed.');
        }

        if (! empty($result['error'])) {
            return back()->with('error', $result['error']);
        }

        if (empty($result['releases'])) {
            $message = $result['existing_count'] > 0
                ? 'This grant installment has already been released. No duplicate email or notification was sent.'
                : 'No eligible approved beneficiaries were found in the campuses you manage.';

            return back()->with('error', $message);
        }

        $emailSent = 0;
        $emailFailures = [];
        foreach ($result['releases'] as $release) {
            $email = $release->student->email;
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $reason = 'No valid registered email address.';
                $release->update(['email_error' => $reason]);
                Log::warning('Grant release email was not sent.', [
                    'grant_release_id' => $release->id,
                    'user_id' => $release->user_id,
                    'reason' => $reason,
                ]);
                $emailFailures[] = $release->student->name . ': ' . $reason;
                continue;
            }

            try {
                Mail::to($email)->send(new GrantSlipMail(
                    $release->scholar,
                    $release->scholarship,
                    $release
                ));
                $release->update(['email_sent_at' => now(), 'email_error' => null]);
                $emailSent++;
            } catch (\Throwable $e) {
                $release->update(['email_error' => $e->getMessage()]);
                Log::error('Failed to send grant release email.', [
                    'grant_release_id' => $release->id,
                    'user_id' => $release->user_id,
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
                $emailFailures[] = $release->student->name . ': email delivery failed.';
            }
        }

        $message = sprintf(
            'Grant released for %d eligible beneficiary(ies). %d email(s) sent.',
            count($result['releases']),
            $emailSent
        );
        $response = back()->with('success', $message);

        return $emailFailures
            ? $response->with('email_warnings', $emailFailures)
            : $response;
    }

    /**
     * Verify a grant release from the tracking link encoded in its QR code.
     */
    public function verifyGrantRelease(string $trackingNumber)
    {
        if (! session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $release = GrantRelease::with(['student', 'scholarship', 'application'])
            ->where('tracking_number', $trackingNumber)
            ->firstOrFail();
        $sfao = User::with('campus')->find(session('user_id'));

        abort_unless(
            $sfao && $sfao->campus
                && $sfao->campus->getAllCampusesUnder()->contains('id', $release->student->campus_id),
            403
        );

        return view('sfao.grant-release-verify', compact('release'));
    }

    /**
     * Mark scholar's grant as claimed (SFAO)
     */
    public function markScholarAsClaimed(Request $request, $id)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return $request->expectsJson() || $request->ajax()
                ? response()->json(['success' => false, 'message' => 'Unauthorized'], 401)
                : redirect('/login')->with('session_expired', true);
        }

        $scholar = Scholar::with(['user', 'scholarship', 'application'])->findOrFail($id);

        // Verify SFAO manages this scholar's campus
        $user = User::with('campus')->find(session('user_id'));
        $sfaoCampus = $user->campus;
        $campusIds = $sfaoCampus->getAllCampusesUnder()->pluck('id');

        if (!$campusIds->contains($scholar->user->campus_id)) {
            $message = 'You do not have permission to manage this scholar.';
            return $request->expectsJson() || $request->ajax()
                ? response()->json(['success' => false, 'message' => $message], 403)
                : back()->with('error', $message);
        }

        // Check for one-time grant restriction
        if ($scholar->scholarship->grant_type === 'one_time' && $scholar->grant_count > 0) {
            $message = 'This is a one-time grant scholarship and has already been claimed.';
            return $request->expectsJson() || $request->ajax()
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with('error', $message);
        }

        // Find the application to mark as claimed
        $query = Application::where('user_id', $scholar->user_id)
            ->where('scholarship_id', $scholar->scholarship_id)
            ->latest();

        if ($scholar->scholarship->grant_type === 'recurring') {
            $query->whereIn('status', ['approved', 'claimed']);
        } else {
            $query->where('status', 'approved');
        }

        $application = $query->first();

        if (!$application) {
            $message = 'No valid application found to claim for this scholar.';
            return $request->expectsJson() || $request->ajax()
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with('error', $message);
        }

        $benefitPolicy = app(ScholarshipBenefitPolicyService::class);
        if ($reason = $benefitPolicy->claimBlockReason($application)) {
            return $request->expectsJson() || $request->ajax()
                ? response()->json(['success' => false, 'message' => $reason], 422)
                : back()->with('error', $reason);
        }

        try {
            DB::beginTransaction();

            $grantAmount = (float) ($scholar->scholarship->grant_amount ?? 0);
            $newGrantCount = $scholar->grant_count + 1;
            $newTotalReceived = ((float) $scholar->total_grant_received) + $grantAmount;

            Log::info('Marking grant as claimed (SFAO):', [
                'scholar_id' => $scholar->id,
                'old_count' => $scholar->grant_count,
                'new_count' => $newGrantCount,
                'grant_amount' => $grantAmount,
                'new_total' => $newTotalReceived,
            ]);

            $application->status = 'claimed';
            $application->grant_count = $newGrantCount;
            $benefitPolicy->recordClaim($application);
            $application->save();

            $affected = DB::table('scholars')->where('id', $scholar->id)->update([
                'grant_count' => $newGrantCount,
                'total_grant_received' => $newTotalReceived,
                'type' => ($scholar->type === 'new' && $newGrantCount >= 1) ? 'old' : $scholar->type,
                'updated_at' => now(),
            ]);

            Log::info('Grant release update result:', ['affected_rows' => $affected]);

            DB::commit();

            NotificationService::notifyApplicationStatusChange(
                $application,
                'claimed',
                $benefitPolicy->claimedNotificationMessage($application)
            );

            $successMessage = "Grant marked as claimed. New Count: {$newGrantCount}, New Total: ₱" . number_format($newTotalReceived, 2);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'grant_count' => $newGrantCount,
                    'total_grant_received' => $newTotalReceived,
                ]);
            }

            return back()->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to mark grant claimed: ' . $e->getMessage());
            $message = 'Failed to mark grant as claimed: ' . $e->getMessage();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 500);
            }

            return back()->with('error', $message);
        }
    }

    /**
     * Bulk mark scholars' grants as claimed (SFAO)
     */
    public function bulkMarkScholarAsClaimed(Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'scholar_ids' => 'required|array|min:1',
            'scholar_ids.*' => 'exists:scholars,id'
        ]);

        // Get SFAO's managed campuses
        $user = User::with('campus')->find(session('user_id'));
        $sfaoCampus = $user->campus;
        $campusIds = $sfaoCampus->getAllCampusesUnder()->pluck('id');

        $successCount = 0;
        $skippedCount = 0;
        $errors = [];

        foreach ($request->scholar_ids as $scholarId) {
            try {
                $scholar = Scholar::with(['user', 'scholarship', 'application'])->find($scholarId);

                if (!$scholar) {
                    $skippedCount++;
                    continue;
                }

                // Verify SFAO manages this scholar's campus
                if (!$campusIds->contains($scholar->user->campus_id)) {
                    $skippedCount++;
                    continue;
                }

                // Check for one-time grant restriction
                if ($scholar->scholarship->grant_type === 'one_time' && $scholar->grant_count > 0) {
                    $skippedCount++;
                    continue;
                }

                // Find the application to mark as claimed
                $query = Application::where('user_id', $scholar->user_id)
                    ->where('scholarship_id', $scholar->scholarship_id)
                    ->latest();

                if ($scholar->scholarship->grant_type === 'recurring') {
                    $query->whereIn('status', ['approved', 'claimed']);
                } else {
                    $query->where('status', 'approved');
                }

                $application = $query->first();

                if (!$application) {
                    $skippedCount++;
                    continue;
                }

                $benefitPolicy = app(ScholarshipBenefitPolicyService::class);
                if ($reason = $benefitPolicy->claimBlockReason($application)) {
                    $skippedCount++;
                    $errors[] = "Scholar ID {$scholarId}: {$reason}";
                    continue;
                }

                DB::beginTransaction();

                // Calculate new values
                $grantAmount = (float) ($scholar->scholarship->grant_amount ?? 0);
                $newGrantCount = $scholar->grant_count + 1;
                $newTotalReceived = ((float) $scholar->total_grant_received) + $grantAmount;

                // Update Application
                $application->status = 'claimed';
                $application->grant_count = $newGrantCount;
                $benefitPolicy->recordClaim($application);
                $application->save();

                // Update Scholar
                DB::table('scholars')->where('id', $scholar->id)->update([
                    'grant_count' => $newGrantCount,
                    'total_grant_received' => $newTotalReceived,
                    'type' => ($scholar->type === 'new' && $newGrantCount >= 1) ? 'old' : $scholar->type,
                    'updated_at' => now(),
                ]);

                DB::commit();

                NotificationService::notifyApplicationStatusChange(
                    $application,
                    'claimed',
                    $benefitPolicy->claimedNotificationMessage($application)
                );

                $successCount++;

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to mark scholar ' . $scholarId . ' as claimed: ' . $e->getMessage());
                $errors[] = "Scholar ID {$scholarId}: " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'success_count' => $successCount,
            'skipped_count' => $skippedCount,
            'errors' => $errors,
            'message' => "Successfully marked {$successCount} scholar(s) as claimed." . 
                        ($skippedCount > 0 ? " {$skippedCount} scholar(s) were skipped." : "")
        ]);
    }

    /**
     * List all scholarships with applicant counts (SFAO)
     */
    public function sfaoIndex(Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $user = User::with('campus')->find(session('user_id'));
        $sfaoCampus = $user->campus;
        $campusIds = $sfaoCampus->getAllCampusesUnder()->pluck('id');

        // Get scholarships with application counts filtered by campus
        $scholarships = Scholarship::withCount(['applications' => function($query) use ($campusIds) {
            $query->whereHas('user', function($userQuery) use ($campusIds) {
                $userQuery->whereIn('campus_id', $campusIds);
            });
        }])->get();

        // Apply sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        
        return redirect()->route('sfao.dashboard', ['tabs' => 'all_scholarships']);
    }

    /**
     * Sort scholarships based on various criteria
     */
    private function sortScholarships($scholarships, $sortBy, $sortOrder)
    {
        return $scholarships->sortBy(function ($scholarship) use ($sortBy) {
            switch ($sortBy) {
                case 'name':
                    return $scholarship->scholarship_name;
                case 'created_at':
                    return $scholarship->created_at;
                case 'submission_deadline':
                    return $scholarship->submission_deadline;
                case 'grant_amount':
                    return $scholarship->grant_amount ?? 0;
                case 'scholarship_type':
                    return $scholarship->scholarship_type;
                case 'grant_type':
                    return $scholarship->grant_type;
                case 'slots_available':
                    return $scholarship->slots_available ?? 999999;
                case 'gwa_requirement':
                    return $scholarship->getGwaRequirement() ?? 999;
                case 'applications_count':
                    return $scholarship->applications_count ?? 0;
                default:
                    return $scholarship->scholarship_name;
            }
        }, SORT_REGULAR, $sortOrder === 'desc');
    }

    // =====================================================
    // STUDENT SCHOLARSHIP VIEWING
    // =====================================================

    /**
     * Get scholarships for student dashboard
     */
    public function getStudentScholarships($form)
    {
        // Get all scholarships that allow new applications and filter by all conditions
        $allScholarships = Scholarship::where('is_active', true)
            ->with(['conditions', 'campuses'])
            ->orderBy('submission_deadline')
            ->get();

        // Filter scholarships based on grant type and all requirements
        $scholarships = $allScholarships->filter(function ($scholarship) use ($form) {
            // Check if scholarship allows new applications based on grant type
            if (!$scholarship->allowsNewApplications()) {
                return false;
            }
            
            // Campus Visibility Restriction
            // Check if the scholarship is available for the student's campus
            if ($scholarship->campuses->isNotEmpty()) {
                 if (empty($form->campus)) {
                     return false; 
                 }
                 
                 // Check if student's campus is in the allowed list
                 $allowed = $scholarship->campuses->contains(function ($campus) use ($form) {
                     return strtolower($campus->name) === strtolower($form->campus);
                 });
                 
                 if (!$allowed) return false;
            } else {
                 // If no campuses are linked, the scholarship is not available to anyone
                 // (Since we migrated 'All' to include all campuses)
                 return false;
            }

            // Check if student meets all conditions
            return $scholarship->meetsAllConditions($form);
        });

        return $scholarships;
    }

    /**
     * Get scholarship details for student
     */
    public function getScholarshipDetails($id)
    {
        return Scholarship::with(['conditions', 'requiredDocuments', 'applications', 'campuses'])
            ->findOrFail($id);
    }

    // =====================================================
    // SCHOLARSHIP REQUIREMENTS MANAGEMENT
    // =====================================================

    /**
     * Add condition to scholarship
     */
    public function addCondition(Request $request, $scholarshipId)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        $request->validate([
            'name' => 'required|string',
            'value' => 'required|string',
        ]);

        ScholarshipRequiredCondition::create([
            'scholarship_id' => $scholarshipId,
            'name' => $request->name,
            'value' => $request->value,
            'is_mandatory' => true, // Conditions are always mandatory
        ]);

        return back()->with('success', 'Condition added successfully.');
    }

    /**
     * Add document requirement to scholarship
     */
    public function addDocumentRequirement(Request $request, $scholarshipId)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        $request->validate([
            'name' => 'required|string',
            'is_mandatory' => 'boolean',
        ]);

        ScholarshipRequiredDocument::create([
            'scholarship_id' => $scholarshipId,
            'document_name' => $request->name,
            'type' => 'pdf', // Default to PDF
            'is_mandatory' => $request->boolean('is_mandatory'),
        ]);

        return back()->with('success', 'Document requirement added successfully.');
    }

    /**
     * Remove condition from scholarship
     */
    public function removeCondition($conditionId)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        $condition = ScholarshipRequiredCondition::findOrFail($conditionId);
        $condition->delete();

        return back()->with('success', 'Condition removed successfully.');
    }

    /**
     * Remove document requirement from scholarship
     */
    public function removeDocument($documentId)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        $document = ScholarshipRequiredDocument::findOrFail($documentId);
        $document->delete();

        return back()->with('success', 'Document requirement removed successfully.');
    }

    // =====================================================
    // SCHOLARSHIP STATISTICS
    // =====================================================

    /**
     * Get scholarship statistics for dashboard
     */
    public function getScholarshipStats()
    {
        return [
            'total' => Scholarship::count(),
            'active' => Scholarship::where('is_active', true)->count(),
            'accepting_applications' => Scholarship::acceptingApplications()->count(),
            'high_priority' => Scholarship::highPriority()->count(),
        ];
    }

    /**
     * Get application statistics for scholarship
     */
    public function getApplicationStats($scholarshipId)
    {
        $scholarship = Scholarship::findOrFail($scholarshipId);
        
        return [
            'total_applications' => $scholarship->getApplicationCount(),
            'approved_applications' => $scholarship->getApprovedCount(),
            'fill_percentage' => $scholarship->getFillPercentage(),
            'is_full' => $scholarship->isFull(),
        ];
    }
}
