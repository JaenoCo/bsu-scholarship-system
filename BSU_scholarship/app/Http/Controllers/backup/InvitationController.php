<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class InvitationController extends Controller
{
    /**
     * Show the invitation acceptance form
     */
    public function show($token)
    {
        $invitation = Invitation::where('token', $token)->first();

        if (!$invitation) {
            return redirect('/login')->with('error', 'Invalid invitation link.');
        }

        if (!$invitation->isValid()) {
            return redirect('/login')->with('error', 'This invitation has expired or is no longer valid.');
        }

        return view('auth.accept-invitation', compact('invitation'));
    }

    /**
     * Accept the invitation and create user account
     */
    public function accept(Request $request, $token)
    {
        $invitation = Invitation::where('token', $token)->first();

        if (!$invitation) {
            return redirect('/login')->with('error', 'Invalid invitation link.');
        }

        if (!$invitation->isValid()) {
            return redirect('/login')->with('error', 'This invitation has expired or is no longer valid.');
        }

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Create the user account
            $user = User::create([
                'name' => $invitation->name,
                'email' => $invitation->email,
                'password' => Hash::make($request->password),
                'role' => 'sfao',
                'campus_id' => $invitation->campus_id,
                'email_verified_at' => now(),
            ]);

            // Mark invitation as accepted
            $invitation->accept();

            // Log the user in
            session([
                'user_id' => $user->id,
                'role' => 'sfao',
                'name' => $user->name,
                'campus_id' => $user->campus_id,
            ]);

            return redirect('/sfao/dashboard')->with('success', 'Welcome! Your account has been created successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create account. Please try again.');
        }
    }
}