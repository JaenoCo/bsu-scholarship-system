<?php

namespace App\Http\Controllers;

use App\Models\StudentGrade;
use App\Models\User;
use Illuminate\Http\Request;

class StudentGradesController extends Controller
{
    /**
     * Display Upload Grades.
     *
     * Same page is used for:
     * 1. New grade submission
     * 2. Read-only submitted grades
     * 3. Editing submitted grades
     *
     * Edit mode is controlled by:
     * ?edit=1
     */
    public function create(Request $request)
    {
        $userId = session('user_id');

        if (! $userId) {
            return redirect()
                ->route('login')
                ->with(
                    'session_expired',
                    'Your session has expired. Please log in again.'
                );
        }

        $user = User::findOrFail($userId);

        $submittedGrades = StudentGrade::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Determine page state
        |--------------------------------------------------------------------------
        |
        | No submitted grades:
        |     Editable form
        |
        | Submitted grades + ?edit=1:
        |     Editable form
        |
        | Submitted grades without ?edit=1:
        |     Read-only form
        |
        */

        $isEditMode = $request->boolean('edit', false);

        $isReadOnly =
            $submittedGrades->isNotEmpty()
            && ! $isEditMode;

        return view('student.forms.upload-grades', [
            'user' => $user,
            'submittedGrades' => $submittedGrades,
            'isReadOnly' => $isReadOnly,
            'isEditMode' => $isEditMode,
        ]);
    }


    /**
     * Submit new grades.
     */
    public function store(Request $request)
    {
        $userId = session('user_id');

        if (! $userId) {
            return redirect()
                ->route('login')
                ->with(
                    'session_expired',
                    'Your session has expired. Please log in again.'
                );
        }

        $user = User::findOrFail($userId);

        $validated = $request->validate([
            'school_year' => [
                'required',
                'string',
                'max:20'
            ],

            'semester' => [
                'required',
                'string',
                'max:50'
            ],

            'grades' => [
                'required',
                'array',
                'min:1'
            ],

            'grades.*.subject_code' => [
                'required',
                'string',
                'max:50'
            ],

            'grades.*.subject_name' => [
                'required',
                'string',
                'max:255'
            ],

            'grades.*.grade' => [
                'required',
                'numeric',
                'min:0',
                'max:100'
            ],

            'document' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120'
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Store document
        |--------------------------------------------------------------------------
        */

        $documentFile = $request->file('document');

        $documentPath = $documentFile->storeAs(
            'student_grades/' . $user->id,
            'grades_' . time() . '.' .
            $documentFile->getClientOriginalExtension(),
            'public'
        );

        /*
        |--------------------------------------------------------------------------
        | Remove previous records
        |--------------------------------------------------------------------------
        |
        | Your current design stores one StudentGrade row per subject.
        | Therefore, delete the previous set before creating the new set.
        |
        */

        StudentGrade::where('user_id', $user->id)->delete();

        /*
        |--------------------------------------------------------------------------
        | Save grades
        |--------------------------------------------------------------------------
        */

        foreach ($validated['grades'] as $gradeData) {
            StudentGrade::create([
                'user_id' => $user->id,
                'school_year' => $validated['school_year'],
                'semester' => $validated['semester'],
                'subject_code' => trim($gradeData['subject_code']),
                'subject_name' => trim($gradeData['subject_name']),
                'grade' => (float) $gradeData['grade'],
                'document_path' => $documentPath,
                'status' => 'pending',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT:
        | Redirect to the SAME Upload Grades route.
        |
        | Do NOT redirect to dashboard or another grades page.
        |
        | Since the submitted records now exist, create() will automatically
        | display the read-only version.
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('student.grades.upload')
            ->with(
                'success',
                'Grades submitted successfully. Your uploaded record is now saved and ready for review.'
            );
    }


    /**
     * Edit submitted grades.
     *
     * This method is kept only for compatibility if you already have an
     * existing route pointing to student.grades.edit.
     *
     * Instead of rendering another page, it redirects back to the SAME
     * Upload Grades page with ?edit=1.
     */
    public function edit()
    {
        $userId = session('user_id');

        if (! $userId) {
            return redirect()
                ->route('login')
                ->with(
                    'session_expired',
                    'Your session has expired. Please log in again.'
                );
        }

        return redirect()->route('student.grades.upload', [
            'edit' => 1
        ]);
    }


    /**
     * Update submitted grades.
     */
    public function update(Request $request)
    {
        $userId = session('user_id');

        if (! $userId) {
            return redirect()
                ->route('login')
                ->with(
                    'session_expired',
                    'Your session has expired. Please log in again.'
                );
        }

        $user = User::findOrFail($userId);

        $validated = $request->validate([
            'school_year' => [
                'required',
                'string',
                'max:20'
            ],

            'semester' => [
                'required',
                'string',
                'max:50'
            ],

            'grades' => [
                'required',
                'array',
                'min:1'
            ],

            'grades.*.subject_code' => [
                'required',
                'string',
                'max:50'
            ],

            'grades.*.subject_name' => [
                'required',
                'string',
                'max:255'
            ],

            'grades.*.grade' => [
                'required',
                'numeric',
                'min:0',
                'max:100'
            ],

            /*
            |--------------------------------------------------------------------------
            | Document is optional during editing.
            |
            | If the user doesn't upload a new document, keep the existing one.
            |--------------------------------------------------------------------------
            */

            'document' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120'
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Keep existing document by default
        |--------------------------------------------------------------------------
        */

        $documentPath = StudentGrade::where('user_id', $user->id)
            ->latest()
            ->value('document_path');

        /*
        |--------------------------------------------------------------------------
        | Replace document only if a new one was uploaded
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('document')) {

            $documentFile = $request->file('document');

            $documentPath = $documentFile->storeAs(
                'student_grades/' . $user->id,
                'grades_' . time() . '.' .
                $documentFile->getClientOriginalExtension(),
                'public'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Replace existing grade records
        |--------------------------------------------------------------------------
        */

        StudentGrade::where('user_id', $user->id)->delete();

        /*
        |--------------------------------------------------------------------------
        | Save updated grades
        |--------------------------------------------------------------------------
        */

        foreach ($validated['grades'] as $gradeData) {
            StudentGrade::create([
                'user_id' => $user->id,
                'school_year' => $validated['school_year'],
                'semester' => $validated['semester'],
                'subject_code' => trim($gradeData['subject_code']),
                'subject_name' => trim($gradeData['subject_name']),
                'grade' => (float) $gradeData['grade'],
                'document_path' => $documentPath,
                'status' => 'pending',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT:
        | Return to the SAME Upload Grades route.
        |
        | No separate edit page.
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('student.grades.upload')
            ->with(
                'success',
                'Your submitted grades were updated successfully.'
            );
    }


    /**
     * SFAO view of a student's grades.
     */
    public function viewForSfao($userId)
    {
        if (
            ! session('user_id') ||
            session('role') !== 'sfao'
        ) {
            return redirect('/login')
                ->with(
                    'session_expired',
                    'Your session has expired. Please log in again.'
                );
        }

        $student = User::findOrFail($userId);

        $grades = StudentGrade::where('user_id', $student->id)
            ->orderByDesc('created_at')
            ->get();

        return view(
            'sfao.applicants.view-grades',
            compact('student', 'grades')
        );
    }
}