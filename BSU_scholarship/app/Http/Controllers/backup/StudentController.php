<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Form;
use App\Models\Scholarship;
use App\Models\Application;
use App\Models\SfaoRequirement;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentController extends Controller
{
    // ----------------------------
    // Dashboard
    // ----------------------------
    public function dashboard()
    {
        if (!session()->has('user_id') || session('role') !== 'student') {
            return redirect('/login')->with('session_expired', true);
        }

        $userId = session('user_id');
        $user = User::with('appliedScholarships')->find($userId);
        $form = Form::where('user_id', $userId)->first();

        $hasApplication = $form !== null;
        $gwa = $form ? floatval($form->gwa) : null;

        $scholarships = collect();
        if ($hasApplication) {
            // Get all scholarships that allow new applications and filter by all conditions
            $allScholarships = Scholarship::where('is_active', true)
                ->with('conditions')
                ->orderBy('submission_deadline')
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

            $appliedIds = $user->appliedScholarships->pluck('id')->toArray();
            foreach ($scholarships as $scholarship) {
                $scholarship->applied = in_array($scholarship->id, $appliedIds);
            }
        }

        $applications = $user ? $user->appliedScholarships : collect();
        
        // Get detailed application tracking data
        $applicationTracking = Application::where('user_id', $userId)
            ->with('scholarship')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student.dashboard', compact('hasApplication', 'scholarships', 'gwa', 'applications', 'applicationTracking', 'form'));
    }

    // ----------------------------
    // Upload Documents
    // ----------------------------
    public function uploadDocuments(Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'student') {
            return redirect('/login')->with('session_expired', true);
        }

        $request->validate([
            'form_137' => 'required|file|mimes:pdf,jpg,png|max:10240',
            'grades' => 'required|file|mimes:pdf,jpg,png|max:10240',
            'certificate' => 'nullable|file|mimes:pdf,jpg,png|max:10240',
            'application_form' => 'required|file|mimes:pdf,jpg,png|max:10240',
        ]);

        $userId = session('user_id');
        $user = User::find($userId);

        $files = [
            'form_137' => $request->file('form_137'),
            'grades' => $request->file('grades'),
            'certificate' => $request->file('certificate'),
            'application_form' => $request->file('application_form'),
        ];

        foreach ($files as $key => $file) {
            if ($file) {
                $path = $file->store("documents/{$user->id}", 'public');
                // Optionally: save $path to a documents table in DB
            }
        }

        return redirect()->route('student.dashboard')->with('success', 'Documents uploaded successfully.');
    }

    // ----------------------------
    // Submit Application Form
    // ----------------------------
    public function submitApplication(Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'student') {
            return redirect('/login')->with('session_expired', true);
        }

        return app(FormController::class)->submit($request);
    }

    // ----------------------------
    // View Applied Scholarships
    // ----------------------------
    public function applications()
    {
        if (!session()->has('user_id') || session('role') !== 'student') {
            return redirect('/login')->with('session_expired', true);
        }

        $user = User::with('appliedScholarships')->find(session('user_id'));
        $applications = $user->appliedScholarships;

        return view('student.applications', compact('applications'));
    }

    // ----------------------------
    // Scholarships Tab
    // ----------------------------
    public function scholarships()
    {
        if (!session()->has('user_id') || session('role') !== 'student') {
            return redirect('/login')->with('session_expired', true);
        }

        $user = User::with('appliedScholarships')->find(session('user_id'));
        $form = Form::where('user_id', $user->id)->first();
        $gwa = $form?->gwa;

        $scholarships = collect();

        if ($form) {
            // Get all scholarships that allow new applications and filter by all conditions
            $allScholarships = Scholarship::where('is_active', true)
                ->with('conditions')
                ->orderBy('submission_deadline')
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
        }

        // Mark applied scholarships
        $appliedIds = $user->appliedScholarships->pluck('id')->toArray();
        foreach ($scholarships as $scholarship) {
            $scholarship->applied = in_array($scholarship->id, $appliedIds);
        }

        return view('student.partials.tabs.scholarships', compact('scholarships', 'gwa', 'form'));
    }

    // ----------------------------
    // Application Form
    // ----------------------------
    public function showApplicationForm()
    {
        if (!session()->has('user_id') || session('role') !== 'student') {
            return redirect('/login')->with('session_expired', true);
        }

        $existingApplication = Form::where('user_id', session('user_id'))->first();
        $existingLevels = $existingApplication ? json_decode($existingApplication->level, true) : [];

        return view('student.forms.application_form', compact('existingApplication', 'existingLevels'));
    }

    // ----------------------------
    // Apply for a Scholarship
    // ----------------------------
    public function apply(Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'student') {
            return redirect('/login')->with('session_expired', true);
        }

        $request->validate([
            'scholarship_id' => 'required|exists:scholarships,id',
        ]);

        $application = Application::where('user_id', session('user_id'))
            ->where('scholarship_id', $request->scholarship_id)
            ->first();

        if ($application) {
            // If it already exists, update status (if you want to allow re-applying)
            $application->update(['status' => 'pending']);
            return back()->with('success', 'Your application has been updated.');
        } else {
            // Determine if this is a new or continuing application
            $hasClaimedGrant = Application::hasClaimedGrant(session('user_id'), $request->scholarship_id);
            $applicationType = $hasClaimedGrant ? 'continuing' : 'new';
            
            // Create new application
            Application::create([
                'user_id'        => session('user_id'),
                'scholarship_id' => $request->scholarship_id,
                'type'           => $applicationType,
                'status'         => 'pending',
            ]);
            
            $message = $hasClaimedGrant 
                ? 'You have successfully applied for scholarship renewal.'
                : 'You have successfully applied for the scholarship.';
                
            return back()->with('success', $message);
        }
    }

    // ----------------------------
    // Unapply from a Scholarship
    // ----------------------------
    public function unapply(Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'student') {
            return redirect('/login')->with('session_expired', true);
        }

        $request->validate([
            'scholarship_id' => 'required|exists:scholarships,id',
        ]);

        $userId = session('user_id');
        $scholarshipId = $request->scholarship_id;

        // Delete application entry
        Application::where('user_id', $userId)
            ->where('scholarship_id', $scholarshipId)
            ->delete();

        // Find the student document row
        $studentDocument = SfaoRequirement::where('user_id', $userId)
            ->where('scholarship_id', $scholarshipId)
            ->first();

        if ($studentDocument) {
            // Delete files from storage if they exist
            foreach (['form_137', 'grades', 'certificate', 'application_form'] as $field) {
                if (!empty($studentDocument->$field) && Storage::disk('public')->exists($studentDocument->$field)) {
                    Storage::disk('public')->delete($studentDocument->$field);
                }
            }

            // Delete the DB row
            $studentDocument->delete();
        }

        return back()->with('success', 'You have successfully un-applied, and your documents were removed.');
    }

    // ----------------------------
    // Print Application
    // ----------------------------
    public function printApplication()
    {
        $user = User::find(session('user_id'));
        $application = Application::where('user_id', $user->id)->first();

        $pdf = Pdf::loadView('student.forms.application_form_pdf', compact('user', 'application'))
                  ->setPaper('A4', 'portrait');
        return $pdf->stream('application_form.pdf');
    }
}
