<?php

namespace App\Http\Controllers;

use App\Models\StudentGrade;
use App\Models\GradeSubmission;
use App\Models\SubmissionSubject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

        $latestSubmission = GradeSubmission::with('subjects')
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        $submittedGrades = $latestSubmission?->subjects
            ?? StudentGrade::where('user_id', $user->id)->latest()->get();

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
            'latestSubmission' => $latestSubmission,
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
                'in:1st Semester,2nd Semester,Summer'
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

            'grades.*.units' => [
                'nullable',
                'numeric',
                'min:0',
                'max:99.99'
            ],

            'grades.*.grade' => [
                'required',
                'numeric',
                'in:1.00,1.25,1.50,1.75,2.00,2.25,2.50,2.75,3.00,5.00',
            ],

            'document' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120'
            ],
        ]);
        $this->ensureUniqueSubjectCodes($validated['grades']);

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

        $submission = DB::transaction(function () use ($user, $validated, $documentPath) {
            $submission = GradeSubmission::create([
                'user_id' => $user->id,
                'school_year' => $validated['school_year'],
                'semester' => $validated['semester'],
                'file_path' => $documentPath,
                'status' => 'pending',
            ]);

            foreach ($validated['grades'] as $gradeData) {
                $subject = SubmissionSubject::create([
                    'submission_id' => $submission->id,
                    'subject_code' => trim($gradeData['subject_code']),
                    'subject_name' => trim($gradeData['subject_name']),
                    'units' => (float) ($gradeData['units'] ?? 0),
                    'grade' => (float) $gradeData['grade'],
                ]);

                StudentGrade::create([
                    'user_id' => $user->id,
                    'school_year' => $validated['school_year'],
                    'semester' => $validated['semester'],
                    'subject_code' => $subject->subject_code,
                    'subject_name' => $subject->subject_name,
                    'grade' => $subject->grade,
                    'document_path' => $documentPath,
                    'status' => 'pending',
                ]);
            }

            return $submission;
        });

        return redirect()
            ->route('student.dashboard', ['tab' => 'grade_history'])
            ->with(
                'success',
                'Grades submitted successfully. Your uploaded record is now saved and ready for review.'
            )->with('grade_submission_id', $submission->id);
    }

    /**
     * Display a student's uploaded document without exposing arbitrary paths.
     */
    public function document(GradeSubmission $submission)
    {
        if ((int) $submission->user_id !== (int) session('user_id')) {
            abort(403);
        }

        $filePath = storage_path('app/public/' . ltrim((string) $submission->file_path, '/'));

        if (! $submission->file_path || ! is_file($filePath)) {
            abort(404);
        }

        return response()->file(
            $filePath,
            ['Content-Disposition' => 'inline; filename="' . basename($submission->file_path) . '"']
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

    public function editSubmission(GradeSubmission $submission)
    {
        $this->authorizeStudentSubmission($submission);
        $user = User::findOrFail(session('user_id'));
        $submission->load('subjects');

        return view('student.forms.upload-grades', [
            'user' => $user,
            'submittedGrades' => $submission->subjects,
            'latestSubmission' => $submission,
            'isReadOnly' => false,
            'isEditMode' => true,
        ]);
    }

    public function destroy(GradeSubmission $submission)
    {
        $this->authorizeStudentSubmission($submission);
        $submission->delete();

        return redirect()
            ->route('student.dashboard', ['tab' => 'grade_history'])
            ->with('success', 'The grade submission was deleted successfully.');
    }

    /**
     * Return the logged-in student's grade submissions for dashboard clients.
     */
    public function history(Request $request)
    {
        $userId = session('user_id');

        if (! $userId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $submissions = GradeSubmission::with('subjects')
            ->where('user_id', $userId)
            ->latest()
            ->get()
            ->map(function (GradeSubmission $submission) {
                return [
                    'id' => $submission->id,
                    'school_year' => $submission->school_year,
                    'semester' => $submission->semester,
                    'date_uploaded' => optional($submission->created_at)->format('M d, Y'),
                    'status' => $submission->status,
                    'file_url' => $submission->file_path
                        ? asset('storage/' . ltrim($submission->file_path, '/'))
                        : null,
                    'subjects' => $submission->subjects,
                ];
            });

        return response()->json(['submissions' => $submissions]);
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
            'submission_id' => ['nullable', 'integer', 'exists:submissions,id'],
            'school_year' => [
                'required',
                'string',
                'max:20'
            ],

            'semester' => [
                'required',
                'in:1st Semester,2nd Semester,Summer'
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

            'grades.*.units' => [
                'nullable',
                'numeric',
                'min:0',
                'max:99.99'
            ],

            'grades.*.grade' => [
                'required',
                'numeric',
                'in:1.00,1.25,1.50,1.75,2.00,2.25,2.50,2.75,3.00,5.00',
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
        $this->ensureUniqueSubjectCodes($validated['grades']);

        /*
        |--------------------------------------------------------------------------
        | Keep existing document by default
        |--------------------------------------------------------------------------
        */

        $submission = ! empty($validated['submission_id'])
            ? GradeSubmission::with('subjects')->findOrFail($validated['submission_id'])
            : GradeSubmission::with('subjects')
                ->where('user_id', $user->id)
                ->latest()
                ->first();
        if ($submission) {
            $this->authorizeStudentSubmission($submission);
        }
        $documentPath = $submission?->file_path
            ?? StudentGrade::where('user_id', $user->id)->latest()->value('document_path');

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

        DB::transaction(function () use ($user, $validated, $documentPath, $submission) {
            if (! $submission) {
                $submission = new GradeSubmission(['user_id' => $user->id]);
            }

            $submission->fill([
                'school_year' => $validated['school_year'],
                'semester' => $validated['semester'],
                'file_path' => $documentPath,
                'status' => 'pending',
            ])->save();
            $submission->subjects()->delete();
            StudentGrade::where('user_id', $user->id)->delete();

            foreach ($validated['grades'] as $gradeData) {
                $subject = $submission->subjects()->create([
                    'subject_code' => trim($gradeData['subject_code']),
                    'subject_name' => trim($gradeData['subject_name']),
                    'units' => (float) ($gradeData['units'] ?? 0),
                    'grade' => (float) $gradeData['grade'],
                ]);

                StudentGrade::create([
                    'user_id' => $user->id,
                    'school_year' => $validated['school_year'],
                    'semester' => $validated['semester'],
                    'subject_code' => $subject->subject_code,
                    'subject_name' => $subject->subject_name,
                    'grade' => $subject->grade,
                    'document_path' => $documentPath,
                    'status' => 'pending',
                ]);
            }
        });

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

        $grades = GradeSubmission::with('subjects')
            ->where('user_id', $student->id)
            ->latest()
            ->get()
            ->flatMap(function (GradeSubmission $submission) {
                return $submission->subjects->map(function (SubmissionSubject $subject) use ($submission) {
                    $subject->school_year = $submission->school_year;
                    $subject->semester = $submission->semester;
                    $subject->document_path = $submission->file_path;
                    $subject->status = $submission->status === 'approved' ? 'verified' : $submission->status;
                    return $subject;
                });
            });

        return view(
            'sfao.applicants.view-grades',
            compact('student', 'grades')
        );
    }

    private function ensureUniqueSubjectCodes(array $grades): void
    {
        $codes = collect($grades)
            ->pluck('subject_code')
            ->map(fn ($code) => strtolower(trim((string) $code)));

        if ($codes->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages([
                'grades' => 'Each subject code may only appear once in a submission.',
            ]);
        }
    }

    private function authorizeStudentSubmission(GradeSubmission $submission): void
    {
        if ((int) $submission->user_id !== (int) session('user_id')) {
            abort(403);
        }
    }
}