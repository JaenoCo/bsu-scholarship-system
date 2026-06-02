<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        if (session()->has('user_id')) {
            return redirect(match (session('role')) {
                'student' => route('student.dashboard'),
                'sfao'    => '/sfao',
                'central' => '/central',
                default   => '/'
            });
        }
        return view('auth.auth');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'campus_id' => 'required|exists:campuses,id',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['Invalid credentials']);
        }

        if (!$user->hasVerifiedEmail()) {
            return back()->withErrors(['Your email is not verified. Please check your inbox.']);
        }

        if ($request->campus_id != $user->campus_id) {
            return back()->withErrors(['The selected campus does not match your account.']);
        }

        session([
            'user_id' => $user->id,
            'role' => $user->role,
        ]);

        return redirect(match ($user->role) {
            'student' => '/student',
            'sfao'    => '/sfao',
            'central' => '/central',
            default   => '/'
        });
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        session()->flush();
        return redirect('/login')->with('logged_out', true);
    }

    /**
     * Show registration form
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle registration
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => ['required','email','unique:users,email','regex:/^[a-zA-Z0-9._%+-]+@g\.batstate-u\.edu\.ph$/'],
            'password'  => 'required|string|confirmed|min:6',
            'role'      => 'required|string',
            'campus_id' => 'required|exists:campuses,id',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'campus_id' => $request->campus_id,
        ]);

        $user->sendEmailVerificationNotification();

        return redirect('/login')->with('status', 'Account created! Please verify your email before logging in.');
    }

    /**
     * Show email verification notice
     */
    public function showVerificationNotice()
    {
        return view('auth.verify-email');
    }

    /**
     * Handle email verification
     */
    public function verifyEmail($id, $hash, Request $request)
    {
        $user = User::findOrFail($id);

        if (!URL::hasValidSignature($request)) {
            abort(403);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return redirect('/login')->with('verified', true);
    }

    /**
     * Resend verification email
     */
    public function resendVerification(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['Email not found.']);
        }

        if ($user->hasVerifiedEmail()) {
            return back()->with('message', 'Your email is already verified.');
        }

        $user->sendEmailVerificationNotification();
        return back()->with('message', 'Verification email sent!');
    }
}
