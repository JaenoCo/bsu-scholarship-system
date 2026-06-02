<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApplicationForm;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ApplicationFormController extends Controller
{
    /**
     * Show all application forms (SFAO)
     */
    public function index()
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $user = User::with('campus')->find(session('user_id'));
        $sfaoCampus = $user->campus;
        $campusIds = $sfaoCampus->getAllCampusesUnder()->pluck('id');

        $forms = ApplicationForm::with(['campus', 'uploader'])
            ->whereIn('campus_id', $campusIds)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('sfao.application-forms.index', compact('forms', 'user', 'sfaoCampus'));
    }

    /**
     * Show upload form (SFAO)
     */
    public function create()
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $user = User::with('campus')->find(session('user_id'));
        $sfaoCampus = $user->campus;
        $managedCampuses = $sfaoCampus->getAllCampusesUnder();
        $campusIds = $managedCampuses->pluck('id');
        
        $activeScholarshipsList = \App\Models\Scholarship::where('is_active', true)
            ->whereHas('campuses', function($q) use ($campusIds) {
                $q->whereIn('campus_id', $campusIds);
            })
            ->orderBy('scholarship_name', 'asc')
            ->get();

        return view('sfao.application-forms.upload', compact('user', 'sfaoCampus', 'managedCampuses', 'activeScholarshipsList'));
    }

    /**
     * Store uploaded form (SFAO)
     */
    public function store(Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $request->validate([
            'form_name' => 'required|string|max:255',
            'form_type' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:10240', // 10MB max
            'campus_id' => 'required|exists:campuses,id',
        ]);

        $user = User::with('campus')->find(session('user_id'));

        // Verify campus permission
        // Default to user's campus if not provided or valid
        $campusId = $user->campus_id;
        
        // Use request campus_id only if it's strictly managed (though UI hides this now)
        if ($request->has('campus_id')) {
             $allowedCampuses = $user->campus->getAllCampusesUnder()->pluck('id');
             if ($allowedCampuses->contains($request->campus_id)) {
                 $campusId = $request->campus_id;
             }
        }

        try {
            // Store file
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();

            // Block lock files
            if (str_starts_with($originalName, '.~lock') || str_starts_with($originalName, '~$')) {
                return back()->withInput()->with('error', 'Invalid file. Please do not upload temporary or lock files.');
            }

            $filename = time() . '_' . str_replace(' ', '_', $originalName);
            $path = $file->storeAs('application_forms', $filename);

            // Create record
            ApplicationForm::create([
                'form_name' => $request->form_name,
                'form_type' => $request->form_type,
                'description' => $request->description,
                'file_path' => $path,
                'file_type' => $file->getClientOriginalExtension(),
                'campus_id' => $request->campus_id,
                'uploaded_by' => $user->id,
            ]);


            return redirect()->route('sfao.dashboard', ['tabs' => 'all-app-forms'])
                ->with('success', 'Application form uploaded successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to upload application form: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to upload form. Please try again. Error: ' . $e->getMessage());
        }
    }

    /**
     * Delete form (SFAO)
     */
    public function destroy($id)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $user = User::with('campus')->find(session('user_id'));
        $campusIds = $user->campus->getAllCampusesUnder()->pluck('id');

        Log::info("SFAO User {$user->id} attempting to delete form {$id}");

        $form = ApplicationForm::findOrFail($id);


        // Verify SFAO has permission (Allow if uploaded by user OR matches campus)
        if ($form->uploaded_by !== $user->id && !$campusIds->contains($form->campus_id)) {
            return back()->with('error', 'You do not have permission to delete this form.');
        }

        try {
            // Attempt to delete file, but continue to delete record even if file missing/locked
            try {
                if ($form->file_path && Storage::exists($form->file_path)) {
                    Storage::delete($form->file_path);
                }
            } catch (\Exception $e) {
                Log::warning('Could not delete file for form ' . $form->id . ': ' . $e->getMessage());
            }

            // Delete record
            $form->delete();

            return back()->with('success', 'Application form deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to delete application form: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete form. Please try again.');
        }
    }

    /**
     * Show forms list for students
     */
    public function studentIndex()
    {
        if (!session()->has('user_id') || session('role') !== 'student') {
            return redirect('/login')->with('session_expired', true);
        }

        $user = User::with('campus')->find(session('user_id'));

        // Get scholarships available to this student
        $availableScholarships = \App\Models\Scholarship::where('is_active', true)
            ->whereHas('campuses', function($q) use ($user) {
                $q->where('campus_id', $user->campus_id);
            })->pluck('scholarship_name');

        // Get forms: Match Campus OR Match Scholarship Category
        $forms = ApplicationForm::with(['campus', 'uploader'])
            ->where(function($query) use ($user, $availableScholarships) {
                $query->where('campus_id', $user->campus_id)
                      ->orWhereIn('form_type', $availableScholarships);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student.application-forms.index', compact('forms', 'user'));
    }

    /**
     * Download form
     */
    public function download($id)
    {
        if (!session()->has('user_id')) {
            return redirect('/login')->with('session_expired', true);
        }

        $form = ApplicationForm::findOrFail($id);
        $user = User::with('campus')->find(session('user_id'));

        // Verify access
        if (session('role') === 'student') {
            $hasAccess = false;
            
            // 1. Check Campus Match
            if ($form->campus_id === $user->campus_id) {
                $hasAccess = true;
            }
            
            // 2. Check Scholarship Availability (if not matched by campus)
            if (!$hasAccess && $form->form_type) {
                $hasAccess = \App\Models\Scholarship::where('scholarship_name', $form->form_type)
                    ->whereHas('campuses', function($q) use ($user) {
                        $q->where('campus_id', $user->campus_id);
                    })->exists();
            }

            if (!$hasAccess) {
                return back()->with('error', 'You do not have permission to download this form.');
            }
        }

        if (session('role') === 'sfao') {
            $campusIds = $user->campus->getAllCampusesUnder()->pluck('id');
            if (!$campusIds->contains($form->campus_id)) {
                return back()->with('error', 'You do not have permission to download this form.');
            }
        }

        // Increment download count
        $form->incrementDownloads();

        // Download file
        if (Storage::exists($form->file_path)) {
            return Storage::download($form->file_path, $form->form_name . '.' . $form->file_type);
        }

        return back()->with('error', 'File not found.');
    }
}
