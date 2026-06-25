<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Campus;
use App\Models\SfaoRequirement;
use App\Models\Application;
use App\Models\Notification;
use App\Models\Form;
use App\Models\Scholarship;
use App\Models\Invitation;
use App\Models\StudentSubmittedDocument;
use App\Models\ScholarshipRequiredDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\TemplateProcessor;

/**
 * =====================================================
 * USER MANAGEMENT CONTROLLER
 * =====================================================
 * 
 * This controller handles all user-related functionality
 * including authentication, profile management, document
 * uploads, and user administration.
 * 
 * Combined functionality from:
 * - AuthController
 * - StudentController (user-specific methods)
 * - SFAODocumentController
 * - CentralController (staff management)
 * - InvitationController
 */
class UserController extends Controller
{
    // =====================================================
    // AUTHENTICATION METHODS
    // =====================================================

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

        if (!\Illuminate\Support\Facades\URL::hasValidSignature($request)) {
            abort(403);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        // For SFAO users, update invitation status and redirect to password setup
        if ($user->role === 'sfao') {
            // Update invitation status to active
            $invitation = Invitation::where('email', $user->email)->first();
            if ($invitation) {
                $invitation->accept(); // This sets status to 'active' and accepted_at to now
            }
            
            // Log the user in temporarily for password setup
            session([
                'user_id' => $user->id,
                'role' => 'sfao',
                'name' => $user->name,
                'campus_id' => $user->campus_id,
            ]);
            
            return redirect('/sfao/password-setup')->with('success', 'Email verified! Please set up your password to complete your account setup.');
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

    // =====================================================
    // STUDENT DASHBOARD METHODS
    /**
     * Student dashboard
     */
    /**
     * Student dashboard
     */
    public function studentDashboard(Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'student') {
            return redirect('/login')->with('session_expired', true);
        }

        $userId = session('user_id');
        $user = User::with('appliedScholarships')->find($userId);
        $form = Form::where('user_id', $userId)->first();

        $hasApplication = $form !== null;
        $gwa = $form ? floatval($form->previous_gwa) : null;
        
        // Check if form is complete (all required fields filled)
        $isFormComplete = $form ? $form->isComplete() : false;

        $scholarships = collect();
        if ($isFormComplete) {
            // Get all scholarships that allow new applications and filter by all conditions
            $allScholarships = Scholarship::where('is_active', true)
                ->with('conditions')
                ->get();

            // Filter scholarships based on grant type and all requirements
            $scholarships = $allScholarships->filter(function ($scholarship) use ($form) {
                // Check if scholarship allows new applications based on grant type
                if (!$scholarship->allowsNewApplications()) {
                    return false;
                }
                
                // Check if student meets all conditions
                return $scholarship->meetsAllConditions($form);
            });

            // Apply sorting
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');
            
            $scholarships = $this->sortScholarships($scholarships, $sortBy, $sortOrder);

            // Get rejected scholarship IDs for this user
            $rejectedScholarshipIds = \App\Models\RejectedApplicant::where('user_id', $userId)
                ->where('rejected_by', 'central')
                ->pluck('scholarship_id')
                ->toArray();

            // Filter out rejected scholarships
            $scholarships = $scholarships->reject(function ($scholarship) use ($rejectedScholarshipIds) {
                return in_array($scholarship->id, $rejectedScholarshipIds);
            });

            $appliedIds = $user->appliedScholarships->pluck('id')->toArray();
            $appliedStatuses = $user->appliedScholarships->mapWithKeys(function ($item) {
                return [$item->id => $item->pivot->status];
            })->toArray();
            
            // Get IDs of scholarships where user is an active scholar
            $scholarIds = \App\Models\Scholar::where('user_id', $userId)
                ->where('status', 'active')
                ->pluck('scholarship_id')
                ->toArray();

            foreach ($scholarships as $scholarship) {
                $scholarship->applied = in_array($scholarship->id, $appliedIds);
                $scholarship->status = $appliedStatuses[$scholarship->id] ?? null;
                $scholarship->is_scholar = in_array($scholarship->id, $scholarIds);
            }
        }

        // Check for any active/pending applications
        $hasPendingApplication = $user->appliedScholarships()
            ->whereIn('status', ['pending', 'in_progress', 'approved'])
            ->exists();

        // Count types before pagination
        $privateScholarshipsCount = $scholarships->where('scholarship_type', 'private')->count();
        $governmentScholarshipsCount = $scholarships->where('scholarship_type', 'government')->count();

        // Pagination Logic
        $page = $request->get('page', 1);
        $perPage = 5;
        $scholarships = new LengthAwarePaginator(
            $scholarships->forPage($page, $perPage),
            $scholarships->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $applications = Application::where('user_id', $userId)
            ->whereHas('scholarship')
            ->with('scholarship')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get detailed application tracking data with enhanced information
        $applicationTracking = Application::where('user_id', $userId)
            ->whereHas('scholarship')
            ->with(['scholarship' => function($query) {
                $query->with(['conditions', 'requiredDocuments']);
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        // Add enhanced tracking data to each application
        $applicationTracking->each(function($application) {
            // Basic application data
            $application->scholarship->status_badge = $this->getStatusBadge($application->status);
            $application->scholarship->days_remaining = $this->getDaysRemaining($application->scholarship->submission_deadline);
            $application->scholarship->grant_amount_formatted = $application->scholarship->grant_amount ? '₱' . number_format($application->scholarship->grant_amount, 2) : 'Not specified';
            
            // Document tracking data
            $documents = \App\Models\StudentSubmittedDocument::where('user_id', $application->user_id)
                ->where('scholarship_id', $application->scholarship_id)
                ->get();
            
            $application->has_documents = $documents->count() > 0;
            $application->documents_count = $documents->count();
            $application->approved_documents_count = $documents->where('evaluation_status', 'approved')->count();
            $application->pending_documents_count = $documents->where('evaluation_status', 'pending')->count();
            $application->rejected_documents_count = $documents->where('evaluation_status', 'rejected')->count();
            $application->last_document_upload = $documents->max('created_at');
            
            // SFAO evaluation stage tracking
            $application->evaluation_stage = $this->getEvaluationStage($application);
            
            // Scholar status tracking
            $scholar = \App\Models\Scholar::where('user_id', $application->user_id)
                ->where('scholarship_id', $application->scholarship_id)
                ->first();
            
            if ($scholar) {
                $application->scholar_status = 'selected';
                $application->scholar_type = $scholar->type;
                $application->scholar_grant_count = $scholar->grant_count;
                $application->scholar_selected_at = $scholar->created_at;
            } else {
                $application->scholar_status = 'not_selected';
                $application->scholar_type = null;
                $application->scholar_grant_count = 0;
                $application->scholar_selected_at = null;
            }
        });

        // Get notifications and unread counts
        $notificationData = $this->getStudentNotificationData($userId);
        $notifications = $notificationData['notifications'];
        $unreadCount = $notificationData['unreadCount'];
        $unreadCountScholarships = $notificationData['unreadCountScholarships'];
        $unreadCountStatus = $notificationData['unreadCountStatus'];
        $unreadCountComments = $notificationData['unreadCountComments'];

        // Get user's active scholarships (My Scholarships)
        $myScholarships = \App\Models\Scholar::where('user_id', $userId)
            ->where('status', 'active')
            ->with('scholarship')
            ->get()
            ->pluck('scholarship');
            
        // Mark them as scholar for the view
        $myScholarships->each(function($s) {
            $s->is_scholar = true;
            $s->applied = true; // Treat as applied for visual consistency, or handle separately
        });

        return view('student.index', compact('hasApplication', 'scholarships', 'myScholarships', 'gwa', 'applications', 'applicationTracking', 'form', 'notifications', 'unreadCount', 'unreadCountScholarships', 'unreadCountStatus', 'unreadCountComments', 'privateScholarshipsCount', 'governmentScholarshipsCount', 'hasPendingApplication'));
    }

    /**
     * Show scholarships for student
     */
    public function scholarships(Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'student') {
            return redirect('/login')->with('session_expired', true);
        }

        $userId = session('user_id');
        $user = User::with('appliedScholarships')->find($userId);
        $form = Form::where('user_id', $userId)->first();

        $hasApplication = $form !== null;
        $gwa = $form ? floatval($form->previous_gwa) : null;
        
        // Check if form is complete (all required fields filled)
        $isFormComplete = $form ? $form->isComplete() : false;

        $scholarships = collect();
        if ($isFormComplete) {
            // Get all scholarships that allow new applications and filter by all conditions
            $allScholarships = Scholarship::where('is_active', true)
                ->with('conditions')
                ->get();

            // Filter scholarships based on grant type and all requirements
            $scholarships = $allScholarships->filter(function ($scholarship) use ($form) {
                // Check if scholarship allows new applications based on grant type
                if (!$scholarship->allowsNewApplications()) {
                    return false;
                }
                
                // Check if student meets all conditions
                return $scholarship->meetsAllConditions($form);
            });

            // Apply sorting
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');
            
            $scholarships = $this->sortScholarships($scholarships, $sortBy, $sortOrder);

            // Get rejected scholarship IDs for this user
            $rejectedScholarshipIds = \App\Models\RejectedApplicant::where('user_id', $userId)
                ->where('rejected_by', 'central')
                ->pluck('scholarship_id')
                ->toArray();

            // Filter out rejected scholarships
            $scholarships = $scholarships->reject(function ($scholarship) use ($rejectedScholarshipIds) {
                return in_array($scholarship->id, $rejectedScholarshipIds);
            });

            $appliedIds = $user->appliedScholarships->pluck('id')->toArray();
            $appliedIds = $user->appliedScholarships->pluck('id')->toArray();
            $appliedStatuses = $user->appliedScholarships->mapWithKeys(function ($item) {
                return [$item->id => $item->pivot->status];
            })->toArray();
            
            // Get IDs of scholarships where user is an active scholar
            $scholarIds = \App\Models\Scholar::where('user_id', $userId)
                ->where('status', 'active')
                ->pluck('scholarship_id')
                ->toArray();

            foreach ($scholarships as $scholarship) {
                $scholarship->applied = in_array($scholarship->id, $appliedIds);
                $scholarship->status = $appliedStatuses[$scholarship->id] ?? null;
                $scholarship->is_scholar = in_array($scholarship->id, $scholarIds);
            }
        }

        $scholarshipType = $request->get('type', 'all');
        if ($scholarshipType !== 'all') {
            $scholarships = $scholarships->where('scholarship_type', $scholarshipType);
        }

        // Count types before pagination
        $privateScholarshipsCount = $scholarships->where('scholarship_type', 'private')->count();
        $governmentScholarshipsCount = $scholarships->where('scholarship_type', 'government')->count();

        // Pagination Logic
        $page = $request->get('page', 1);
        $perPage = 5;
        $scholarships = new LengthAwarePaginator(
            $scholarships->forPage($page, $perPage),
            $scholarships->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $applications = Application::where('user_id', $userId)
            ->whereHas('scholarship')
            ->with('scholarship')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get detailed application tracking data with enhanced information
        $applicationTracking = Application::where('user_id', $userId)
            ->whereHas('scholarship')
            ->with(['scholarship' => function($query) {
                $query->with(['conditions', 'requiredDocuments']);
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        // Add enhanced tracking data to each application
        $applicationTracking->each(function($application) {
            // Basic application data
            $application->scholarship->status_badge = $this->getStatusBadge($application->status);
            $application->scholarship->days_remaining = $this->getDaysRemaining($application->scholarship->submission_deadline);
            $application->scholarship->grant_amount_formatted = $application->scholarship->grant_amount ? '₱' . number_format($application->scholarship->grant_amount, 2) : 'Not specified';
            
            // Document tracking data
            $documents = \App\Models\StudentSubmittedDocument::where('user_id', $application->user_id)
                ->where('scholarship_id', $application->scholarship_id)
                ->get();
            
            $application->has_documents = $documents->count() > 0;
            $application->documents_count = $documents->count();
            $application->approved_documents_count = $documents->where('evaluation_status', 'approved')->count();
            $application->pending_documents_count = $documents->where('evaluation_status', 'pending')->count();
            $application->rejected_documents_count = $documents->where('evaluation_status', 'rejected')->count();
            $application->last_document_upload = $documents->max('created_at');
            
            // SFAO evaluation stage tracking
            $application->evaluation_stage = $this->getEvaluationStage($application);
            
            // Scholar status tracking
            $scholar = \App\Models\Scholar::where('user_id', $application->user_id)
                ->where('scholarship_id', $application->scholarship_id)
                ->first();
            
            if ($scholar) {
                $application->scholar_status = 'selected';
                $application->scholar_type = $scholar->type;
                $application->scholar_grant_count = $scholar->grant_count;
                $application->scholar_selected_at = $scholar->created_at;
            } else {
                $application->scholar_status = 'not_selected';
                $application->scholar_type = null;
                $application->scholar_grant_count = 0;
                $application->scholar_selected_at = null;
            }
        });

        // Get notifications and unread counts
        $notificationData = $this->getStudentNotificationData($userId);
        $notifications = $notificationData['notifications'];
        $unreadCount = $notificationData['unreadCount'];
        $unreadCountScholarships = $notificationData['unreadCountScholarships'];
        $unreadCountStatus = $notificationData['unreadCountStatus'];
        $unreadCountComments = $notificationData['unreadCountComments'];

        // Get user's active scholarships (My Scholarships)
        $myScholarships = \App\Models\Scholar::where('user_id', $userId)
            ->where('status', 'active')
            ->with('scholarship')
            ->get()
            ->pluck('scholarship');
            
        // Mark them as scholar for the view
        $myScholarships->each(function($s) {
            $s->is_scholar = true;
            $s->applied = true; // Treat as applied for visual consistency, or handle separately
        });

        return view('student.index', compact('hasApplication', 'scholarships', 'myScholarships', 'gwa', 'applications', 'applicationTracking', 'form', 'notifications', 'unreadCount', 'scholarshipType', 'unreadCountScholarships', 'unreadCountStatus', 'unreadCountComments', 'privateScholarshipsCount', 'governmentScholarshipsCount'));
    }

    /**
     * Get evaluation stage for application tracking
     */
    private function getEvaluationStage($application)
    {
        if ($application->status === 'rejected') {
            return 0; // Rejected applications
        }
        
        if ($application->status === 'approved') {
            return 4; // All stages completed
        }
        
        // Check document evaluation progress
        $documents = \App\Models\StudentSubmittedDocument::where('user_id', $application->user_id)
            ->where('scholarship_id', $application->scholarship_id)
            ->get();
        
        $approvedDocs = $documents->where('evaluation_status', 'approved')->count();
        $totalDocs = $documents->count();
        
        if ($totalDocs === 0) {
            return 0; // No documents uploaded
        }
        
        if ($approvedDocs === $totalDocs) {
            return 4; // All documents approved
        }
        
        if ($approvedDocs > 0) {
            return min(3, $approvedDocs); // Partial progress
        }
        
        return 1; // Documents uploaded, pending evaluation
    }

    /**
     * Show application form
     */
    public function showApplicationForm($scholarship_id = null)
    {
        if (!session()->has('user_id') || session('role') !== 'student') {
            return redirect('/login')->with('session_expired', true);
        }

        $userId = session('user_id');
        
    // Get Program Tracks for dropdown (Legacy support - can be removed if view is fully updated)
    // $programTracks = ... 
    // New Hierarchy Data
    $campusData = $this->getAcademicHierarchy();

    // Get the user's form (one form per user)
    $existingApplication = Form::where('user_id', $userId)
        ->latest('updated_at')
        ->first();
    
    $campuses = Campus::all();

    // If scholarship_id is provided, set scholarship_applied for display
    if ($scholarship_id) {
        $scholarship = Scholarship::findOrFail($scholarship_id);
        
        // If form exists, ensure scholarship_applied is set correctly
        if ($existingApplication) {
            $existingApplication->scholarship_applied = $scholarship->scholarship_name;
        } else {
            // Create a new empty form object with just the scholarship_applied pre-filled
            $existingApplication = new \App\Models\Form();
            $existingApplication->scholarship_applied = $scholarship->scholarship_name;
        }
        
        return view('student.forms.sfao-application-form', compact('existingApplication', 'scholarship', 'campuses', 'campusData'));
    }
    
    return view('student.forms.sfao-application-form', compact('existingApplication', 'campuses', 'campusData'));
    }

    private function getAcademicHierarchy()
    {
        // Fetch all CampusCollege entries with relations
        $campusColleges = \App\Models\CampusCollege::with(['campus', 'college', 'programs.tracks'])->get();
        
        // Structure: CampusID -> [Colleges] -> [Programs] -> [Tracks]
        $hierarchy = [];
        
        foreach ($campusColleges as $cc) {
            $campusId = $cc->campus_id;
            $collegeName = $cc->college->name; // Using name as value based on form
            
            if (!isset($hierarchy[$campusId])) {
                $hierarchy[$campusId] = [];
            }
            
            $programs = $cc->programs->map(function($program) {
                return [
                    'name' => $program->name,
                    'tracks' => $program->tracks->pluck('name')->toArray()
                ];
            })->toArray();
            
            $hierarchy[$campusId][$collegeName] = $programs;
        }
        
        return $hierarchy; 
    }
    
    /**
     * Show TDP application form
     */
    public function showTdpApplicationForm()
    {
        if (!session()->has('user_id') || session('role') !== 'student') {
            return redirect('/login')->with('session_expired', true);
        }

        $userId = session('user_id');
        
        // Get the user's form (one form per user)
        $existingApplication = Form::where('user_id', $userId)
            ->latest('updated_at')
            ->first();
        
        $campuses = Campus::all();
        // Get Program Tracks for dropdown
        $programTracks = \App\Models\Program::with('tracks')->get()->mapWithKeys(function ($p) {
            return [$p->name => $p->tracks->pluck('name')];
        });

        return view('student.forms.tdp-application-form', compact('existingApplication', 'campuses', 'programTracks'));
    }

    /**
     * Print application using PHPWord template
     */
    public function printApplication()
    {
        if (!session()->has('user_id') || session('role') !== 'student') {
            return redirect('/login')->with('session_expired', true);
        }

        $user = User::find(session('user_id'));
        $form = Form::where('user_id', $user->id)->first();

        if (!$form) {
            return redirect()->back()->with('error', 'Application form not found.');
        }

        // Path to your Word template
        // Try the actual template file first, then fallback to storage location
        $templatePath = resource_path('forms/BatStateU-FO-SFA-01_Application Form_Template_Final.docx');
        
        if (!file_exists($templatePath)) {
            // Fallback to storage location
            $templatePath = storage_path('app/templates/application_form_template.docx');
        }
        
        if (!file_exists($templatePath)) {
            return redirect()->back()->with('error', 'Template file not found. Please ensure the template exists at: resources/forms/BatStateU-FO-SFA-01_Application Form_Template_Final.docx');
        }

        // Load the template
        $templateProcessor = new TemplateProcessor($templatePath);

        // Map estimated_gross_annual_income to readable text
        $incomeLabels = [
            'not_over_250000' => 'Not over P 250,000.00',
            'over_250000_not_over_400000' => 'Over P 250,000 but not over P 400,000',
            'over_400000_not_over_800000' => 'Over P 400,000 but not over P 800,000',
            'over_800000_not_over_2000000' => 'Over P 800,000 but not over P 2,000,000',
            'over_2000000_not_over_8000000' => 'Over P 2,000,000 but not over P 8,000,000',
            'over_8000000' => 'Over P 8,000,000'
        ];

        // Format birthdate for display (mm/dd/yyyy)
        $birthdateFormatted = $form->birthdate ? $form->birthdate->format('m/d/Y') : '';
        $birthdateMonth = $form->birthdate ? $form->birthdate->format('m') : '';
        $birthdateDay = $form->birthdate ? $form->birthdate->format('d') : '';
        $birthdateYear = $form->birthdate ? $form->birthdate->format('Y') : '';

        // Format date_signed
        $dateSignedFormatted = $form->date_signed ? $form->date_signed->format('m/d/Y') : '';

        // Replace all placeholders with form data
        $replacements = [
            // Personal Data
            '{{last_name}}' => $form->last_name ?? '',
            '{{first_name}}' => $form->first_name ?? '',
            '{{middle_name}}' => $form->middle_name ?? '',
            '{{age}}' => $form->age ?? '',
            '{{sex}}' => ucfirst($form->sex ?? ''),
            '{{civil_status}}' => $form->civil_status ?? '',
            '{{birthdate}}' => $birthdateFormatted,
            '{{birthdate_month}}' => $birthdateMonth,
            '{{birthdate_day}}' => $birthdateDay,
            '{{birthdate_year}}' => $birthdateYear,
            '{{birth_mm}}' => $birthdateMonth,
            '{{birth_dd}}' => $birthdateDay,
            '{{birth_yyyy}}' => $birthdateYear,
            '{{birthplace}}' => $form->birthplace ?? '',
            '{{email}}' => $form->email ?? '',
            '{{contact_number}}' => $form->contact_number ?? '',
            '{{street_barangay}}' => $form->street_barangay ?? '',
            '{{town_city}}' => $form->town_city ?? '',
            '{{province}}' => $form->province ?? '',
            '{{zip_code}}' => $form->zip_code ?? '',
            '{{citizenship}}' => $form->citizenship ?? '',
            '{{disability}}' => $form->disability ?? '',
            '{{tribe}}' => $form->tribe ?? '',

            // Academic Data
            '{{sr_code}}' => $form->sr_code ?? '',
            '{{education_level}}' => $form->education_level ?? '',
            '{{program}}' => $form->program ?? '',
            '{{college_department}}' => $form->college_department ?? '',
            '{{year_level}}' => $form->year_level ?? '',
            '{{campus}}' => $form->campus ?? '',
            '{{previous_gwa}}' => $form->previous_gwa ?? '',
            '{{honors_received}}' => $form->honors_received ?? '',
            '{{units_enrolled}}' => $form->units_enrolled ?? '',
            '{{scholarship_applied}}' => $form->scholarship_applied ?? '',
            '{{semester}}' => $form->semester ?? '',
            '{{academic_year}}' => $form->academic_year ?? '',
            '{{has_existing_scholarship}}' => $form->has_existing_scholarship ? 'Yes' : 'No',
            '{{existing_scholarship_details}}' => $form->existing_scholarship_details ?? '',

            // Family Data
            '{{father_status}}' => ucfirst($form->father_status ?? ''),
            '{{father_name}}' => $form->father_name ?? '',
            '{{father_address}}' => $form->father_address ?? '',
            '{{father_contact}}' => $form->father_contact ?? '',
            '{{father_occupation}}' => $form->father_occupation ?? '',
            '{{mother_status}}' => ucfirst($form->mother_status ?? ''),
            '{{mother_name}}' => $form->mother_name ?? '',
            '{{mother_address}}' => $form->mother_address ?? '',
            '{{mother_contact}}' => $form->mother_contact ?? '',
            '{{mother_occupation}}' => $form->mother_occupation ?? '',
            '{{estimated_gross_annual_income}}' => $incomeLabels[$form->estimated_gross_annual_income] ?? ($form->estimated_gross_annual_income ?? ''),
            '{{siblings_count}}' => $form->siblings_count ?? '',

            // Essay / Question
            '{{reason_for_applying}}' => $form->reason_for_applying ?? '',

            // Certification
            '{{student_signature}}' => $form->student_signature ?? '',
            '{{date_signed}}' => $dateSignedFormatted,
        ];

        // Replace all placeholders in the template
        // PHPWord TemplateProcessor expects ${variable} format in the Word document
        // We extract the variable name from {{variable}} or [[variable]] format
        foreach ($replacements as $placeholder => $value) {
            // Extract variable name by removing {{, }}, [[, ]], or ${, }
            // Remove opening delimiters: {{, [[, ${
            $variableName = preg_replace('/^(?:\{\{|\[\[|\$\{)/', '', $placeholder);
            // Remove closing delimiters: }}, ]], }
            $variableName = preg_replace('/(?:\}\}|\]\]|})$/', '', $variableName);
            // PHPWord setValue expects just the variable name (without ${})
            $templateProcessor->setValue($variableName, $value ?? '');
        }

        // Generate filename
        $filename = 'application_form_' . $user->id . '_' . date('Y-m-d') . '.docx';
        
        // Save the processed document
        $outputPath = storage_path('app/temp/' . $filename);
        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }
        
        $templateProcessor->saveAs($outputPath);

        // Download the file
        return response()->download($outputPath, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Show scholarships for student
     */


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
                default:
                    return $scholarship->scholarship_name;
            }
        }, SORT_REGULAR, $sortOrder === 'desc');
    }

    // =====================================================
    // DOCUMENT MANAGEMENT METHODS
    // =====================================================

    /**
     * Show document upload form
     */
    public function showUploadForm($scholarship_id)
    {
        if (!session()->has('user_id') || session('role') !== 'student') {
            return redirect('/login')->with('session_expired', true);
        }

        $scholarship = Scholarship::findOrFail($scholarship_id);
        return view('student.applications.upload', compact('scholarship'));
    }

    /**
     * Handle document uploads
     */
    public function uploadDocuments(Request $request, $scholarship_id)
    {
        if (!session()->has('user_id') || session('role') !== 'student') {
            return redirect('/login')->with('session_expired', true);
        }

        $request->validate([
            'form_137'         => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,docx', 'max:10240'],
            'grades'           => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,docx', 'max:10240'],
            'certificate'      => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,docx', 'max:10240'],
            'application_form' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,docx', 'max:10240'],
        ], [
            'form_137.required' => 'Form 137 is required.',
            'form_137.mimes' => 'Form 137 must be a PDF, JPG, PNG, or DOCX file.',
            'form_137.max' => 'Form 137 must not exceed 10MB.',
            'grades.required' => 'Grades document is required.',
            'grades.mimes' => 'Grades must be a PDF, JPG, PNG, or DOCX file.',
            'grades.max' => 'Grades must not exceed 10MB.',
            'certificate.mimes' => 'Certificate must be a PDF, JPG, PNG, or DOCX file.',
            'certificate.max' => 'Certificate must not exceed 10MB.',
            'application_form.required' => 'Application Form is required.',
            'application_form.mimes' => 'Application Form must be a PDF, JPG, PNG, or DOCX file.',
            'application_form.max' => 'Application Form must not exceed 10MB.',
        ]);

        $userId = session('user_id');

        $files = [
            'form_137'         => $request->file('form_137'),
            'grades'           => $request->file('grades'),
            'certificate'      => $request->file('certificate'),
            'application_form' => $request->file('application_form'),
        ];

        $filePaths = [];
        foreach ($files as $key => $file) {
            if ($file) {
                $filePaths[$key] = $file->store("documents/{$userId}", 'public');
            }
        }

        // Ensure scholarship_id is stored
        $filePaths['scholarship_id'] = $scholarship_id;

        SfaoRequirement::updateOrCreate(
            ['user_id' => $userId, 'scholarship_id' => $scholarship_id],
            $filePaths
        );

        // Mark student as applied
        Application::updateOrCreate(
            [
                'user_id'        => $userId,
                'scholarship_id' => $scholarship_id,
            ],
            [
                'status' => 'pending',
            ]
        );

        return redirect()
            ->route('student.dashboard')
            ->with('success', 'Documents uploaded successfully and you are now applied to this scholarship.');
    }

    // =====================================================
    // MULTI-STAGE APPLICATION METHODS
    // =====================================================

    /**
     * Show multi-stage application form
     */
    public function showMultiStageApplication($scholarship_id, Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'student') {
            return redirect('/login')->with('session_expired', true);
        }

        $scholarship = Scholarship::with(['requiredDocuments'])->findOrFail($scholarship_id);
        $userId = session('user_id');
        
        // Get existing submitted documents
        $submittedDocuments = StudentSubmittedDocument::byUserAndScholarship($userId, $scholarship_id)->get();
        
        // Check application status
        $application = Application::where('user_id', $userId)
            ->where('scholarship_id', $scholarship_id)
            ->first();

        // Check if user wants to force a specific stage or is resubmitting
        $forceStage = $request->get('stage');
        $isResubmitting = $request->get('resubmit') || ($application && $application->status === 'pending');
        
        if ($forceStage && in_array($forceStage, [1, 2, 3])) {
            $currentStage = (int) $forceStage;
        } else {
            // Determine current stage based on submitted documents
            $currentStage = 1;
            $sfaoDocsCount = $submittedDocuments->where('document_category', 'sfao_required')->count();
            $scholarshipDocsCount = $submittedDocuments->where('document_category', 'scholarship_required')->count();
            
            if ($sfaoDocsCount >= 3) { // At least 3 mandatory SFAO docs
                $currentStage = 2;
            }
            
            if ($scholarship->requiredDocuments->where('is_mandatory', true)->count() == 0 || $scholarshipDocsCount >= $scholarship->requiredDocuments->where('is_mandatory', true)->count()) {
                if ($currentStage == 2) {
                    $currentStage = 3;
                }
            }
            
            if ($application && !$isResubmitting) {
                $currentStage = 3; // Application already submitted and not explicitly resubmitting
            } else if ($isResubmitting) {
                // If resubmitting, force start at stage 1 to allow reviewing/updating documents
                $currentStage = 1;
            }
        }

        return view('student.applications.form', compact('scholarship', 'submittedDocuments', 'application', 'currentStage', 'isResubmitting'));
    }

    /**
     * Handle Stage 1: SFAO Required Documents
     */
    public function submitSfaoDocuments(Request $request, $scholarship_id)
    {
        if (!session()->has('user_id') || session('role') !== 'student') {
            return redirect('/login')->with('session_expired', true);
        }

        $userId = session('user_id');
        
        // Fetch existing approved documents to conditionally relax validation
        $approvedDocs = StudentSubmittedDocument::where('user_id', $userId)
            ->where('scholarship_id', $scholarship_id)
            ->where('evaluation_status', 'approved')
            ->pluck('document_category')
            ->toArray();
            
        // Helper to determine if a field is required
        $isRequired = function($category) use ($approvedDocs) {
            // Mapping input names to categories if they differ, but here they seem to match:
            // form_137 -> form_137 (assumed category name matches or mapped)
            // grades -> grades
            // etc.
            
            // Actually, need to check how categories are stored. 
            // In uploadDocuments, keys are used. 
            // Let's assume keys 'form_137', 'grades', 'certificate', 'application_form' correspond to categories or filenames.
            // If the system uses specific category names like 'sfao_required' for all, we need to check specific document names?
            // The StudentSubmittedDocument model usually stores specific document type?
            // Looking at previous code, `uploadDocuments` stores them. 
            // But `submitSfaoDocuments` is the new method.
            
            // Let's check if we can verify strict category/name mapping. 
            // For now, let's look at `StudentSubmittedDocument` table or usage.
            // But safely: if we have an approved doc with the same name, we skip.
            // However, `StudentSubmittedDocument` might store them with generic names?
            // Let's assume we can match by checking if an approved doc exists for this user/scholarship with the specific input name.
            
            // Actually, checking the `uploadDocuments` implementation (lines 872):
            // SfaoRequirement::updateOrCreate(...)
            // It seems SFAO docs are stored in `sfao_requirements` table?
            // Wait, line 910: $submittedDocuments = StudentSubmittedDocument::byUserAndScholarship...
            // It seems we utilize `StudentSubmittedDocument` for tracking status.
            // We need to be sure SFAO docs act as `StudentSubmittedDocument`.
            
            // If they are in `sfao_requirements`, that's separate. 
            // BUT `showMultiStageApplication` uses `StudentSubmittedDocument` to check status (line 925).
            // So SFAO docs MUST be in `StudentSubmittedDocument` as well.
            
            // Assuming `document_name` or `document_type` identifies them.
            // Let's assume 'Form 137', 'Grades', 'Application Form'.
            // Simple approach: Check if input has file. If not, and not approved, fail.
            return !in_array($category, $approvedDocs);
        };
        
        // We actually need to query specifically by document name or type.
        // Let's fetch the actual document names/types for this user/scholarship that are approved.
        // To be safe, let's just use the `StudentSubmittedDocument` checks inside the validator logic if possible,
        // or pre-calculate rules.
        
        // Better approach:
        // 1. Get all approved docs for this user & scholarship.
        // 2. Identify which "inputs" ('form_137', 'grades'...) correspond to which approved doc.
        //    (This might be tricky if names aren't 1:1 in DB).
        //    However, usually we can rely on `document_name` or similar.
        
        // Alternative: Just check if the file is present in request. 
        // If NOT present, check if there is an approved document for it.
        // If neither, then fail.
        
        $hasApprovedDoc = function($name) use ($userId, $scholarship_id) {
             return StudentSubmittedDocument::where('user_id', $userId)
                ->where('scholarship_id', $scholarship_id)
                ->where('document_name', 'LIKE', '%' . $name . '%') // Loose match or precise if known
                ->where('evaluation_status', 'approved')
                ->exists();
        };
        
        // Validation rules
        $rules = [
            'form_137'         => [$hasApprovedDoc('Form 137') ? 'nullable' : 'required', 'file', 'max:10240'],
            'grades'           => [$hasApprovedDoc('Grades') ? 'nullable' : 'required', 'file', 'max:10240'],
            'certificate'      => ['nullable', 'file', 'max:10240'],
            'application_form' => [$hasApprovedDoc('Application Form') ? 'nullable' : 'required', 'file', 'max:10240'],
        ];

        // Custom validation with better DOCX support
        $validator = \Validator::make($request->all(), $rules, [
            'form_137.required' => 'Form 137 is required.',
            'form_137.max' => 'Form 137 must not exceed 10MB.',
            'grades.required' => 'Grades document is required.',
            'grades.max' => 'Grades must not exceed 10MB.',
            'certificate.max' => 'Certificate must not exceed 10MB.',
            'application_form.required' => 'Application Form is required.',
            'application_form.max' => 'Application Form must not exceed 10MB.',
        ]);

        // Custom validation for file types (checking both extension and MIME type)
        $validator->after(function ($validator) use ($request) {
            $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'docx'];
            $allowedMimes = [
                'application/pdf',
                'image/jpeg',
                'image/png',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/msword' // Fallback for older DOCX files
            ];

            $files = [
                'form_137' => $request->file('form_137'),
                'grades' => $request->file('grades'),
                'certificate' => $request->file('certificate'),
                'application_form' => $request->file('application_form'),
            ];

            foreach ($files as $field => $file) {
                if ($file) {
                    $extension = strtolower($file->getClientOriginalExtension());
                    $mimeType = $file->getMimeType();

                    // Accept if either extension OR MIME type is in allowed lists
                    if (!in_array($extension, $allowedExtensions) && !in_array($mimeType, $allowedMimes)) {
                        $fieldName = ucfirst(str_replace('_', ' ', $field));
                        $validator->errors()->add($field, $fieldName . ' must be a PDF, JPG, PNG, or DOCX file. (Got: ' . $extension . ' / ' . $mimeType . ')');
                    }
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $userId = session('user_id');
        
        // Define SFAO required documents
        $sfaoDocuments = [
            'form_137' => ['name' => 'Form 137', 'mandatory' => true],
            'grades' => ['name' => 'Grades', 'mandatory' => true],
            'certificate' => ['name' => 'Certificate', 'mandatory' => false],
            'application_form' => ['name' => 'Application Form', 'mandatory' => true],
        ];

        foreach ($sfaoDocuments as $field => $config) {
            if ($request->hasFile($field) && $request->file($field)) {
                $file = $request->file($field);
                $filePath = $file->store("documents/{$userId}/sfao", 'public');
                
                StudentSubmittedDocument::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'scholarship_id' => $scholarship_id,
                        'document_category' => 'sfao_required',
                        'document_name' => $config['name'],
                    ],
                    [
                        'file_path' => $filePath,
                        'original_filename' => $file->getClientOriginalName(),
                        'file_type' => $file->getClientOriginalExtension(),
                        'file_size' => $file->getSize(),
                        'is_mandatory' => $config['mandatory'],
                    ]
                );
            }
        }

        // Check if scholarship has stage 2 requirements to decide redirect
        $scholarship = Scholarship::with('requiredDocuments')->findOrFail($scholarship_id);
        $hasStage2 = $scholarship->requiredDocuments->count() > 0;
        $nextStage = $hasStage2 ? 2 : 3;

        return redirect()->route('student.apply', ['scholarship_id' => $scholarship_id, 'stage' => $nextStage])
            ->with('success', 'SFAO required documents uploaded successfully!');
    }

    /**
     * Handle Stage 2: Scholarship Required Documents
     */
    public function submitScholarshipDocuments(Request $request, $scholarship_id)
    {
        if (!session()->has('user_id') || session('role') !== 'student') {
            return redirect('/login')->with('session_expired', true);
        }

        $scholarship = Scholarship::with(['requiredDocuments'])->findOrFail($scholarship_id);
        $userId = session('user_id');
        
        // Get scholarship required documents
        $requiredDocs = $scholarship->requiredDocuments;
        
        if ($requiredDocs->isEmpty()) {
            return redirect()->route('student.apply', $scholarship_id)
                ->with('success', 'No additional documents required for this scholarship.');
        }

        // Fetch existing approved documents to conditionally relax validation
        $approvedDocs = StudentSubmittedDocument::where('user_id', $userId)
            ->where('scholarship_id', $scholarship_id)
            ->where('evaluation_status', 'approved')
            ->pluck('document_name')
            ->toArray();
            
        // Helper to check if doc is approved
        $isApproved = function($docName) use ($approvedDocs) {
            // Need to match exactly or close enough. Since we store $doc->document_name, exact match works.
            return in_array($docName, $approvedDocs);
        };

        // Validate files based on scholarship requirements
        $validationRules = [];
        $customMessages = [];
        foreach ($requiredDocs as $doc) {
            $fieldName = 'scholarship_doc_' . $doc->id;
            $approved = $isApproved($doc->document_name);
            
            $validationRules[$fieldName] = [
                ($doc->is_mandatory && !$approved) ? 'required' : 'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png,docx',
                'max:10240'
            ];
            
            if ($doc->is_mandatory) {
                $customMessages[$fieldName . '.required'] = strip_tags($doc->document_name) . ' is required.';
            }
            $customMessages[$fieldName . '.mimes'] = strip_tags($doc->document_name) . ' must be a PDF, JPG, PNG, or DOCX file.';
            $customMessages[$fieldName . '.max'] = strip_tags($doc->document_name) . ' must not exceed 10MB.';
        }

        $request->validate($validationRules, $customMessages);

        // Process each scholarship required document
        foreach ($requiredDocs as $doc) {
            $fieldName = 'scholarship_doc_' . $doc->id;
            
            if ($request->hasFile($fieldName) && $request->file($fieldName)) {
                $file = $request->file($fieldName);
                $filePath = $file->store("documents/{$userId}/scholarship", 'public');
                
                StudentSubmittedDocument::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'scholarship_id' => $scholarship_id,
                        'document_category' => 'scholarship_required',
                        'document_name' => $doc->document_name,
                    ],
                    [
                        'file_path' => $filePath,
                        'original_filename' => $file->getClientOriginalName(),
                        'file_type' => $file->getClientOriginalExtension(),
                        'file_size' => $file->getSize(),
                        'is_mandatory' => $doc->is_mandatory,
                        'description' => $doc->description,
                    ]
                );
            }
        }

        return redirect()->route('student.apply', ['scholarship_id' => $scholarship_id, 'stage' => 3])
            ->with('success', 'Scholarship required documents uploaded successfully!');
    }

    /**
     * Handle Stage 3: Final Confirmation and Application Submission
     */
    public function submitFinalApplication(Request $request, $scholarship_id)
    {
        if (!session()->has('user_id') || session('role') !== 'student') {
            return redirect('/login')->with('session_expired', true);
        }

        $userId = session('user_id');
        
        // Check if all required documents are submitted
        $sfaoDocs = StudentSubmittedDocument::byUserAndScholarship($userId, $scholarship_id)
            ->sfaoRequired()
            ->mandatory()
            ->count();
        
        $scholarship = Scholarship::with(['requiredDocuments'])->findOrFail($scholarship_id);
        $requiredScholarshipDocs = $scholarship->requiredDocuments->where('is_mandatory', true)->count();
        $submittedScholarshipDocs = StudentSubmittedDocument::byUserAndScholarship($userId, $scholarship_id)
            ->scholarshipRequired()
            ->mandatory()
            ->count();

        // Check if all mandatory documents are submitted
        if ($sfaoDocs < 3) { // form_137, grades, application_form are mandatory
            return redirect()->route('student.apply', $scholarship_id)
                ->with('error', 'Please submit all required SFAO documents first.');
        }

        if ($requiredScholarshipDocs > 0 && $submittedScholarshipDocs < $requiredScholarshipDocs) {
            return redirect()->route('student.apply', $scholarship_id)
                ->with('error', 'Please submit all required scholarship documents first.');
        }

        // Check if student was previously rejected from this scholarship by Central Admin
        $wasRejected = \App\Models\RejectedApplicant::where('user_id', $userId)
            ->where('scholarship_id', $scholarship_id)
            ->where('rejected_by', 'central')
            ->exists();

        if ($wasRejected) {
            return redirect()->route('student.apply', $scholarship_id)
                ->with('error', 'You cannot apply to this scholarship as your previous application was rejected by Central Administration.');
        }

        // Create or update application
        $hasClaimedGrant = Application::hasClaimedGrant($userId, $scholarship_id);
        Application::updateOrCreate(
            [
                'user_id' => $userId,
                'scholarship_id' => $scholarship_id,
            ],
            [
                'status' => 'in_progress',
            ]
        );

        return redirect()->route('student.dashboard')
            ->with('success', 'Application submitted successfully! Your application is now under review.');
    }

    /**
     * Get application progress for a specific scholarship
     */
    public function getApplicationProgress($scholarship_id)
    {
        if (!session()->has('user_id') || session('role') !== 'student') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = session('user_id');
        $scholarship = Scholarship::with(['requiredDocuments'])->findOrFail($scholarship_id);
        
        // Check SFAO documents progress
        $sfaoSubmitted = StudentSubmittedDocument::byUserAndScholarship($userId, $scholarship_id)
            ->sfaoRequired()
            ->count();
        $sfaoRequired = 4; // form_137, grades, certificate, application_form
        
        // Check scholarship documents progress
        $scholarshipRequired = $scholarship->requiredDocuments->where('is_mandatory', true)->count();
        $scholarshipSubmitted = StudentSubmittedDocument::byUserAndScholarship($userId, $scholarship_id)
            ->scholarshipRequired()
            ->mandatory()
            ->count();
        
        // Check if application is submitted
        $application = Application::where('user_id', $userId)
            ->where('scholarship_id', $scholarship_id)
            ->first();

        return response()->json([
            'stage1' => [
                'completed' => $sfaoSubmitted >= 3, // At least 3 mandatory SFAO docs
                'progress' => $sfaoSubmitted,
                'required' => 3
            ],
            'stage2' => [
                'completed' => $scholarshipRequired == 0 || $scholarshipSubmitted >= $scholarshipRequired,
                'progress' => $scholarshipSubmitted,
                'required' => $scholarshipRequired
            ],
            'stage3' => [
                'completed' => $application !== null,
                'progress' => $application ? 1 : 0,
                'required' => 1
            ]
        ]);
    }

    /**
     * Upload profile picture
     */
    public function uploadProfilePicture(Request $request, $role)
    {
        if (!session()->has('user_id') || session('role') !== $role) {
            return redirect('/login')->with('session_expired', true);
        }

        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = User::find(session('user_id'));

        // Check if old profile picture exists and delete it
        // We use 'profile_pictures/' prefix assuming root is the public container
        if ($user->profile_picture && file_exists(public_path('storage/profile_pictures/' . $user->profile_picture))) {
            unlink(public_path('storage/profile_pictures/' . $user->profile_picture));
        }

        $file = $request->file('profile_picture');
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        
        // DIRECT MOVE: Bypasses config/filesystems.php entirely
        // Uses the verified public_path() from AppServiceProvider
        $file->move(public_path('storage/profile_pictures'), $filename);

        $user->profile_picture = $filename;
        $user->save();

        return back()->with('success', 'Profile picture updated.');
    }

    // STAFF MANAGEMENT METHODS (CENTRAL)
    // =====================================================

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
            'email' => 'required|email|max:255|unique:users,email',
            'campus_id' => 'required|exists:campuses,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check if user already exists
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser) {
            return back()->with('error', 'A user with this email already exists.');
        }

        try {
            // Create invitation record first
            $invitation = Invitation::createInvitation(
                $request->email,
                $request->name,
                $request->campus_id,
                session('user_id')
            );

            // Create SFAO user account with unverified email
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make(\Illuminate\Support\Str::random(16)), // Temporary password
                'role' => 'sfao',
                'campus_id' => $request->campus_id,
                'email_verified_at' => null, // Not verified yet
            ]);

            // Send account created email with verification link
            try {
                Mail::to($user->email)->send(new \App\Mail\SFAOAccountCreatedMail($user));
                Log::info("SFAO account created and email sent to: {$user->email}");
            } catch (\Exception $mailException) {
                Log::error("Failed to send email to {$user->email}: " . $mailException->getMessage());
                // Continue even if email fails - user account is created
            }

            return back()->with('success', "SFAO account created for {$request->name} ({$request->email}). Verification email sent - they need to verify their email and set up their password.");
        } catch (\Exception $e) {
            Log::error("Failed to create SFAO account: " . $e->getMessage());
            return back()->with('error', 'Failed to create SFAO account. Please try again.');
        }
    }

    /**
     * Deactivate SFAO staff member (remove from users table)
     */
    public function deactivateStaff(Request $request, $id)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        try {
            $user = User::findOrFail($id);
            
            // Check if user is SFAO staff
            if ($user->role !== 'sfao') {
                return back()->with('error', 'Only SFAO staff can be deactivated.');
            }

            // Store user information for logging before deletion
            $userName = $user->name;
            $userEmail = $user->email;
            $userCampusId = $user->campus_id;

            // Delete related applications first (if any)
            $user->applications()->delete();
            
            // Delete the user's form (if any)
            $user->form()->delete();
            
            // Update invitation status to 'removed' before deleting user
            $invitation = Invitation::where('email', $userEmail)->first();
            if ($invitation) {
                $invitation->markAsRemoved();
                Log::info("Invitation status updated to 'removed' for: {$userName} ({$userEmail})");
            }
            
            // Delete the user record completely
            $user->delete();
            
            Log::info("SFAO staff completely removed: {$userName} ({$userEmail}) by user ID: " . session('user_id'));

            return back()->with('success', "SFAO staff member {$userName} has been completely removed from the system. Their account can no longer be used to login.");
        } catch (\Exception $e) {
            Log::error("Failed to remove SFAO staff: " . $e->getMessage());
            return back()->with('error', 'Failed to remove staff member. Please try again.');
        }
    }

    /**
     * Note: Reactivate functionality removed since deactivated users are now completely deleted.
     * To restore access, a new SFAO account must be created using the invite functionality.
     */

    // =====================================================
    // SFAO PASSWORD SETUP METHODS
    // =====================================================


    /**
     * Show SFAO password setup form after email verification
     */
    public function showSFAOPasswordSetup()
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $user = User::find(session('user_id'));
        
        // Check if user has verified email and needs to set up password
        if (!$user->hasVerifiedEmail()) {
            return redirect('/email/verify')->with('error', 'Please verify your email first.');
        }

        return view('auth.sfao-password-setup', compact('user'));
    }

    /**
     * Handle SFAO password setup after email verification
     */
    public function setupSFAOPassword(Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $user = User::find(session('user_id'));

        if (!$user->hasVerifiedEmail()) {
            return redirect('/email/verify')->with('error', 'Please verify your email first.');
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
        ]);

        try {
            // Update the user's password
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            return redirect('/sfao')->with('success', 'Account setup completed successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to set up password. Please try again.');
        }
    }

    // =====================================================
    // USER PROFILE METHODS
    // =====================================================

    /**
     * Get user profile information
     */
    public function getUserProfile($userId)
    {
        return User::with(['campus', 'form', 'applications.scholarship'])->find($userId);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login')->with('session_expired', true);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:20',
        ]);

        $user = User::findOrFail(session('user_id'));
        $user->name = $request->name;
        $user->contact_number = $request->contact_number;
        $user->save();

        return back()->with('status', 'profile-updated');
    }

    /**
     * Update user display name only
     */
    public function updateName(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login')->with('session_expired', true);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = User::find(session('user_id'));
        $user->update([
            'name' => $request->name,
        ]);

        return back()->with('success', 'Display name updated successfully.');
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login')->with('session_expired', true);
        }

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::find(session('user_id'));

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }

    /**
     * Get status badge for application
     */
    private function getStatusBadge($status)
    {
        return match($status) {
            'in_progress' => ['text' => 'In Progress', 'color' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'],
            'pending' => ['text' => 'Pending', 'color' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'],
            'approved' => ['text' => 'Approved', 'color' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'],
            'rejected' => ['text' => 'Rejected', 'color' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'],
            'claimed' => ['text' => 'Claimed', 'color' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'],
            default => ['text' => 'Unknown', 'color' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200']
        };
    }

    /**
     * Get type badge for application
     */
    private function getTypeBadge($type)
    {
        return match($type) {
            'new' => ['text' => 'New Applicant', 'color' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'],
            'continuing' => ['text' => 'Continuing', 'color' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'],
            default => ['text' => 'Unknown', 'color' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200']
        };
    }

    /**
     * Get days remaining until deadline
     */
    private function getDaysRemaining($deadline)
    {
        if (!$deadline) return null;
        
        $now = now();
        $deadlineDate = \Carbon\Carbon::parse($deadline);
        
        if ($now->gt($deadlineDate)) {
            return 0; // Deadline has passed
        }
        
        return $now->diffInDays($deadlineDate);
    }

    /**
     * View document (especially for DOCX files)
     */
    public function viewDocument($id)
    {
        $document = StudentSubmittedDocument::findOrFail($id);
        
        // Check if file exists
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'Document not found');
        }
        
        $filePath = Storage::disk('public')->path($document->file_path);
        $fileType = strtolower($document->file_type);
        
        // Use a stream URL to serve the file from the application directly
        $fileUrl = route('document.stream', ['id' => $document->id]);
        
        $viewers = [];
        $viewerType = 'fallback'; // default

        // Handle specific file types
        if ($fileType === 'docx') {
            $viewerType = 'office';
            $encodedUrl = urlencode($fileUrl);
            
            // Check if we're on localhost
            $isLocalhost = !app()->isProduction() && (
                in_array(request()->getHost(), ['localhost', '127.0.0.1', '::1']) || 
                str_contains(request()->getHost(), '.local')
            );
            
            if (!$isLocalhost) {
                // Option 1: Google Docs Viewer
                $viewers[] = [
                    'name' => 'Google Docs Viewer',
                    'url' => "https://docs.google.com/viewer?url=" . $encodedUrl . "&embedded=true"
                ];
                // Option 2: Microsoft Office Viewer
                $viewers[] = [
                    'name' => 'Microsoft Office Viewer',
                    'url' => "https://view.officeapps.live.com/op/view.aspx?src=" . $encodedUrl
                ];
            }
        } elseif ($fileType === 'pdf') {
            $viewerType = 'pdf';
            // PDF can be viewed directly in iframe (except on mobile sometimes, but usually fine)
             $viewers[] = [
                'name' => 'Native PDF Viewer',
                'url' => $fileUrl
            ];
            $isLocalhost = false; 
        } elseif (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $viewerType = 'image';
             $viewers[] = [
                'name' => 'Image Viewer',
                'url' => $fileUrl
            ];
            $isLocalhost = false;
        } else {
             // Other files or unknown types
             $isLocalhost = false;
        }

        return view('components.document-viewer', [
            'document' => $document,
            'viewers' => $viewers,
            'viewerType' => $viewerType,
            'fileUrl' => $fileUrl, // For direct access
            'downloadUrl' => route('document.download', ['id' => $document->id]),
            'isLocalhost' => $isLocalhost ?? false
        ]);
    }

    /**
     * Download student submitted document
     */
    public function streamDocument($id)
    {
        $document = StudentSubmittedDocument::findOrFail($id);

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'Document not found');
        }

        $path = Storage::disk('public')->path($document->file_path);
        $mimeType = match(strtolower($document->file_type)) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => Storage::disk('public')->mimeType($document->file_path),
        };

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $document->original_filename . '"',
        ]);
    }

    public function downloadDocument($id)
    {
        $document = StudentSubmittedDocument::findOrFail($id);

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'Document not found');
        }

        return Storage::disk('public')->download($document->file_path, $document->original_filename);
    }

    /**
     * Get student notification data
     */
    private function getStudentNotificationData($userId)
    {
        $notifications = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $unreadCounts = Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        return [
            'notifications' => $notifications,
            'unreadCount' => $unreadCounts->sum(),
            'unreadCountScholarships' => $unreadCounts['scholarship_created'] ?? 0,
            'unreadCountStatus' => $unreadCounts['application_status'] ?? 0,
            'unreadCountComments' => $unreadCounts['sfao_comment'] ?? 0,
        ];
    }


}
