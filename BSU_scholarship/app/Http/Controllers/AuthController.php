<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Invitation;
use App\Models\Scholar;

/**
 * Authentication Controller
 * 
 * Handles user authentication operations:
 * - Login
 * - Registration
 * - Email verification
 * - Logout
 */
class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        if (session()->has('user_id')) {
            // Redirect central admin to their login page if they try to access regular login
            if (session('role') === 'central') {
                return redirect('/central/login');
            }
            return redirect(match (session('role')) {
                'student' => route('student.dashboard'),
                'sfao'    => '/sfao?tabs=overview',
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

        // Prevent central admin from using regular login
        if ($user->role === 'central') {
            return back()->withErrors(['Central admin users must use the central admin login page.']);
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
            'sfao'    => '/sfao?tabs=overview',
            default   => '/'
        });
    }

    /**
     * Show central admin login form
     * Only accessible via direct URL - no navigation links should point here
     */
    public function showCentralLogin(Request $request)
    {
        // Prevent access if coming from internal navigation (referrer check)
        // This ensures the page can only be accessed by typing the URL directly
        $referrer = $request->headers->get('referer');
        if ($referrer) {
            $referrerUrl = parse_url($referrer);
            $referrerHost = $referrerUrl['host'] ?? null;
            $referrerPath = $referrerUrl['path'] ?? null;
            $currentHost = $request->getHost();
            
            // Allow if referrer is from different site (external)
            // Allow if coming from /login (programmatic redirect for central admin)
            // Block if referrer is from same site and not /login (internal navigation via links/buttons)
            if ($referrerHost === $currentHost && $referrerPath !== '/login') {
                return redirect('/')->withErrors(['Access denied.']);
            }
        }

        if (session()->has('user_id')) {
            if (session('role') === 'central') {
                return redirect()->route('central.dashboard', ['tabs' => 'overview']);
            }
            return redirect(match (session('role')) {
                'student' => route('student.dashboard'),
                'sfao'    => '/sfao?tabs=overview',
                default   => '/'
            });
        }
        return view('auth.centrallogin');
    }

    /**
     * Handle central admin login request
     */
    public function centralLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['Invalid credentials']);
        }

        // Only allow central admin role
        if ($user->role !== 'central') {
            return back()->withErrors(['This login page is only for central admin users.']);
        }

        if (!$user->hasVerifiedEmail()) {
            return back()->withErrors(['Your email is not verified. Please check your inbox.']);
        }

        session([
            'user_id' => $user->id,
            'role' => $user->role,
        ]);

        return redirect()->route('central.dashboard', ['tabs' => 'overview']);
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
        if (session()->has('user_id')) {
            return redirect(match (session('role')) {
                'student' => route('student.dashboard'),
                'sfao'    => route('sfao.dashboard'),
                'central' => route('central.dashboard'),
                default   => '/'
            });
        }

        $campuses = \App\Models\Campus::with('colleges')->get();
        $scholarships = \App\Models\Scholarship::all();
        return view('auth.register', compact('campuses', 'scholarships'));
    }

    /**
     * Handle registration
     */
    public function register(Request $request)
    {
        $request->validate([
            'first_name'    => 'required|string|max:255',
            'middle_name'   => 'nullable|string|max:255',
            'last_name'     => 'required|string|max:255',
            'birthdate'     => 'required|date|before:-18 years',
            'sex'           => 'required|in:Male,Female',
            'email'         => ['required','email','unique:users,email','regex:/^[a-zA-Z0-9._%+-]+@g\.batstate-u\.edu\.ph$/'],
            'contact_number'=> 'required|string|max:20',
            'sr_code'       => 'required|string|unique:users,sr_code',
            'education_level'=> 'required|string',
            'college'       => 'required|string',
            'program'       => 'required|string',
            'year_level'    => 'required|string',
            'password'      => 'required|string|confirmed|min:6',
            'role'          => 'required|string',
            'campus_id'     => 'required|exists:campuses,id',
            'selected_scholarships' => 'nullable|array',
            'selected_scholarships.*' => 'exists:scholarships,id',
        ]);

        $fullName = $request->first_name . ' ' . ($request->middle_name ? $request->middle_name . ' ' : '') . $request->last_name;

        $user = User::create([
            'name'          => $fullName,
            'first_name'    => $request->first_name,
            'middle_name'   => $request->middle_name,
            'last_name'     => $request->last_name,
            'birthdate'     => $request->birthdate,
            'sex'           => $request->sex,
            'email'         => $request->email,
            'contact_number'=> $request->contact_number,
            'sr_code'       => $request->sr_code,
            'education_level'=> $request->education_level,
            'college'       => $request->college,
            'program'       => $request->program,
            'year_level'    => $request->year_level,
            'password'      => Hash::make($request->password),
            'role'          => $request->role,
            'campus_id'     => $request->campus_id,
        ]);

        // Handle existing scholarships
        $existingScholarshipNames = [];
        if ($request->has('selected_scholarships') && is_array($request->selected_scholarships)) {
            foreach ($request->selected_scholarships as $scholarshipId) {
                Scholar::create([
                    'user_id' => $user->id,
                    'scholarship_id' => $scholarshipId,
                    'scholarship_start_date' => now(),
                    'status' => 'active',
                    'type' => 'new',
                    'grant_count' => 0,
                    'total_grant_received' => 0
                ]);
            }
            $existingScholarshipNames = \App\Models\Scholarship::whereIn('id', $request->selected_scholarships)->pluck('scholarship_name')->toArray();
        }

        // Create SFAO Application Form (Auto-populate)
        \App\Models\Form::create([
            'user_id' => $user->id,
            'age' => \Carbon\Carbon::parse($request->birthdate)->age,
            'has_existing_scholarship' => $request->has_scholarship === 'yes',
            'existing_scholarship_details' => !empty($existingScholarshipNames) ? implode(', ', $existingScholarshipNames) : null,
            'form_status' => 'draft'
        ]);

        // Automatically mark student/applicant email as verified so they can log in immediately
        $user->email_verified_at = now();
        $user->save();

        return redirect('/login')->with('success', 'Account created successfully. You may now log in.');
    }

    /**
     * Verify user email
     */
    public function verifyEmail($id, $hash, Request $request)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect('/login')->with('status', 'Email already verified.');
        }

        if ($user->markEmailAsVerified()) {
            event(new \Illuminate\Auth\Events\Verified($user));
        }

        // Check if this is an SFAO user with an invitation
        if ($user->role === 'sfao' && $user->invitation) {
            return redirect()->route('sfao.password.setup')->with('status', 'Email verified! Please set your password.');
        }

        return redirect('/login')->with('status', 'Email verified successfully! You can now login.');
    }

    /**
     * Resend email verification notification
     */
    public function resendVerification(Request $request)
    {
        if (session()->has('user_id')) {
            $user = User::find(session('user_id'));
            if ($user && !$user->hasVerifiedEmail()) {
                $user->sendEmailVerificationNotification();
                return back()->with('status', 'Verification link sent!');
            }
        }
        return back()->withErrors(['User not found or already verified.']);
    }

    /**
     * Resend verification email by email address (for unauthenticated users)
     */
    public function resendVerificationByEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && !$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
            return back()->with('status', 'Verification link sent! Please check your email.');
        }

        // If user not found or already verified, we still show success to prevent email enumeration
        // or we can be specific if security is less of a concern for this internal-ish app.
        // Given the context, being helpful is likely preferred over strict enumeration protection.
        if (!$user) {
             return back()->withErrors(['We could not find a user with that email address.']);
        }

        if ($user->hasVerifiedEmail()) {
            return back()->with('status', 'Your email is already verified. Please login.');
        }

        return back()->with('status', 'Verification link sent!');
    }

    
    /**
     * Show the waiting for verification page
     */
    public function showWaitingPage()
    {
        if (!session()->has('email')) {
            return redirect('/login');
        }
        return view('auth.waiting-for-verification');
    }

    /**
     * Check if the user in the waiting session has verified their email
     */
    public function checkVerificationStatus()
    {
        if (!session()->has('email')) {
            return response()->json(['verified' => false]);
        }
        
        $user = User::where('email', session('email'))->first();
        
        // If user is not found, or not verified, return false
        if (!$user || !$user->hasVerifiedEmail()) {
            return response()->json(['verified' => false]);
        }
        
        return response()->json(['verified' => true]);
    }

    /**
     * Resend verification email with a limit of 3 attempts
     */
    public function resendRegistrationVerification(Request $request)
    {
        if (!session()->has('email')) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please login manually.']);
        }

        $count = session('resend_count', 0);
        
        if ($count >= 3) {
            return response()->json([
                'success' => false, 
                'message' => 'Maximum resend attempts reached.', 
                'resend_count' => $count
            ]);
        }

        $user = User::where('email', session('email'))->first();

        if ($user && !$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
            
            // Increment count
            $newCount = $count + 1;
            session(['resend_count' => $newCount]);
            
            return response()->json([
                'success' => true, 
                'resend_count' => $newCount
            ]);
        }

        return response()->json(['success' => false, 'message' => 'User not found or already verified.']);
    }
}

