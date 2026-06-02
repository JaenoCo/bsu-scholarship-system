<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Scholarship;
use App\Models\Invitation;
use App\Models\Campus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class CentralController extends Controller
{
    public function dashboard()
    {
        $applications = Application::with(['user', 'scholarship'])
            ->orderBy('created_at', 'desc')
            ->get();

        $scholarships = Scholarship::orderBy('created_at', 'desc')->get(); // ← fetch scholarships

        return view('central.dashboard', compact('applications', 'scholarships')); // ← pass both
    }

    /**
     * Approve application
     */
    public function approveApplication($id)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        $application = Application::findOrFail($id);
        $application->status = 'approved';
        $application->save();

        return back()->with('success', 'Application approved.');
    }

    /**
     * Reject application
     */
    public function rejectApplication($id)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        $application = Application::findOrFail($id);
        $application->status = 'rejected';
        $application->save();

        return back()->with('success', 'Application rejected.');
    }

    /**
     * Mark application as claimed (grant received)
     */
    public function claimGrant($id)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        $application = Application::findOrFail($id);
        
        // Only allow claiming if application is approved
        if ($application->status !== 'approved') {
            return back()->with('error', 'Only approved applications can be marked as claimed.');
        }
        
        // Calculate the grant count for this specific scholarship
        $grantCount = Application::getNextGrantCount($application->user_id, $application->scholarship_id);
        
        $application->status = 'claimed';
        $application->grant_count = $grantCount;
        $application->save();

        return back()->with('success', "Grant has been marked as claimed ({$grantCount}th grant). Student is now eligible for renewals.");
    }

    /**
     * Send invitation to new SFAO admin
     */
    public function inviteStaff(Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email|unique:invitations,email',
            'campus_id' => 'required|exists:campuses,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check if user already exists
        $existingUser = \App\Models\User::where('email', $request->email)->first();
        if ($existingUser) {
            return back()->with('error', 'A user with this email already exists.');
        }

        // Check if there's already a pending invitation
        $existingInvitation = Invitation::where('email', $request->email)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if ($existingInvitation) {
            return back()->with('error', 'An invitation has already been sent to this email address.');
        }

        try {
            // Create invitation
            $invitation = Invitation::createInvitation(
                $request->email,
                $request->name,
                $request->campus_id,
                session('user_id')
            );

            // Send invitation email
            Mail::to($request->email)->send(new \App\Mail\SFAOInvitationMail($invitation));

            return back()->with('success', "Invitation sent successfully to {$request->name} ({$request->email}). They have 7 days to accept the invitation.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send invitation. Please try again.');
        }
    }

    /**
     * Cancel a pending invitation
     */
    public function cancelInvitation($id)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        $invitation = Invitation::findOrFail($id);
        
        if ($invitation->status !== 'pending') {
            return back()->with('error', 'Cannot cancel this invitation.');
        }

        $invitation->markAsExpired();
        
        return back()->with('success', 'Invitation cancelled successfully.');
    }
}
