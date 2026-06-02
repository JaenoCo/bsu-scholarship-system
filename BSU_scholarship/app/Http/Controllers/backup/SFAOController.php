<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Application;
use App\Models\Scholarship;
use App\Models\SfaoRequirement;
use Illuminate\Support\Facades\DB;

class SFAOController extends Controller
{
    public function dashboard()
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $user = User::with('campus')->find(session('user_id'));
        
        // Get the SFAO admin's campus and all campuses under it
        $sfaoCampus = $user->campus;
        $campusIds = $sfaoCampus->getAllCampusesUnder()->pluck('id');

        // Get students only from the SFAO admin's campus and its extensions
        $students = User::where('role', 'student')
            ->whereIn('campus_id', $campusIds)
            ->with(['applications.scholarship', 'form', 'campus'])
            ->leftJoin('sfao_requirements', 'users.id', '=', 'sfao_requirements.user_id')
            ->select(
                'users.id as student_id',
                'users.name',
                'users.email',
                'users.created_at',
                'users.campus_id',
                DB::raw('MAX(sfao_requirements.updated_at) as last_uploaded'),
                DB::raw('COUNT(DISTINCT sfao_requirements.id) as documents_count')
            )
            ->groupBy('users.id', 'users.name', 'users.email', 'users.created_at', 'users.campus_id')
            ->get();

        // Add application status information to each student
        $students->each(function($student) {
            $student->has_applications = $student->applications->count() > 0;
            $student->has_documents = $student->documents_count > 0;
            $student->application_status = $student->applications->pluck('status')->unique()->toArray();
            $student->applied_scholarships = $student->applications->pluck('scholarship.scholarship_name')->toArray();
            $student->applications_with_types = $student->applications->map(function($app) {
                return [
                    'id' => $app->id,
                    'scholarship_name' => $app->scholarship->scholarship_name,
                    'status' => $app->status,
                    'type' => $app->type,
                    'type_display' => $app->getApplicantTypeDisplayName(),
                    'type_badge_color' => $app->getApplicantTypeBadgeColor(),
                    'grant_count' => $app->grant_count,
                    'grant_count_display' => $app->getGrantCountDisplay(),
                    'grant_count_badge_color' => $app->getGrantCountBadgeColor()
                ];
            });
        });

        // Get applications only from students under this SFAO admin's jurisdiction
        $applications = Application::with('user', 'scholarship')
            ->whereHas('user', function($query) use ($campusIds) {
                $query->whereIn('campus_id', $campusIds);
            })
            ->get();
            
        $scholarships = Scholarship::all();

        return view('sfao.dashboard', compact('user', 'students', 'applications', 'scholarships', 'sfaoCampus'));
    }

    public function applicants()
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $user = User::with('campus')->find(session('user_id'));
        
        // Get the SFAO admin's campus and all campuses under it
        $sfaoCampus = $user->campus;
        $campusIds = $sfaoCampus->getAllCampusesUnder()->pluck('id');

        // Get students who have uploaded at least one document, only from this SFAO admin's jurisdiction
        $students = DB::table('sfao_requirements')
            ->join('users', 'sfao_requirements.user_id', '=', 'users.id')
            ->whereIn('users.campus_id', $campusIds)
            ->select(
                'users.id as student_id',
                'users.name',
                'users.email',
                'users.campus_id',
                DB::raw('MAX(sfao_requirements.updated_at) as last_uploaded')
            )
            ->groupBy('users.id', 'users.name', 'users.email', 'users.campus_id')
            ->get();

        return view('sfao.partials.applicants', compact('students', 'sfaoCampus'));
    }

    /**
     * View student documents
     */
    public function viewDocuments($user_id)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $student = User::findOrFail($user_id);
        $documents = SfaoRequirement::where('user_id', $user_id)->get();

        return view('sfao.partials.view-documents', compact('student', 'documents'));
    }

    /**
     * Approve application
     */
    public function approveApplication($id)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
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
        if (!session()->has('user_id') || session('role') !== 'sfao') {
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
        if (!session()->has('user_id') || session('role') !== 'sfao') {
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

}
