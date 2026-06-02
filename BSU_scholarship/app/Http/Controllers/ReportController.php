<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Report;
use App\Models\User;
use App\Models\Campus;
use App\Models\Application;
use App\Models\Scholarship;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    // =====================================================
    // SFAO REPORT METHODS
    // =====================================================


    /**
     * Show create report form
     */
    public function createReport()
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $user = User::with('campus')->find(session('user_id'));
        $campus = $user->campus;
        
        // Get campuses that this SFAO can monitor (their constituent campus and its extensions)
        $monitoredCampuses = $campus->getAllCampusesUnder();
        
        // Add a special option for "Constituent + Extensions" if the user's campus is a constituent
        $campusOptions = collect();
        
        // Add individual campuses
        foreach ($monitoredCampuses as $campusOption) {
            $campusOptions->push($campusOption);
        }
        
        // Add "Constituent + Extensions" option if the user's campus is a constituent
        if ($campus->type === 'constituent' && $campus->extensionCampuses->count() > 0) {
            $constituentWithExtensions = new \stdClass();
            $constituentWithExtensions->id = 'constituent_with_extensions';
            $constituentWithExtensions->name = $campus->name . ' + Extensions';
            $constituentWithExtensions->type = 'constituent_with_extensions';
            $campusOptions->push($constituentWithExtensions);
        }
        
        return view('sfao.reports.create', compact('campus', 'monitoredCampuses', 'campusOptions'));
    }

    /**
     * Generate and preview report data
     */
    public function generateReportData(Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $request->validate([
            'report_type' => 'required|in:monthly,quarterly,annual,custom',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'report_period_start' => 'required|date',
            'report_period_end' => 'required|date|after_or_equal:report_period_start',
            'campus_id' => 'required'
        ]);

        $user = User::with('campus')->find(session('user_id'));
        $campus = $user->campus;

        // Generate report data
        $reportData = Report::generateReportData(
            $request->campus_id,
            $request->report_period_start,
            $request->report_period_end
        );

        return response()->json([
            'success' => true,
            'data' => $reportData
        ]);
    }

    /**
     * Store new report
     */
    public function storeReport(Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $request->validate([
            'report_type' => 'required|in:monthly,quarterly,annual,custom',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'report_period_start' => 'required|date',
            'report_period_end' => 'required|date|after_or_equal:report_period_start',
            'notes' => 'nullable|string',
            'submit_immediately' => 'boolean',
            'campus_id' => 'required'
        ]);

        $user = User::with('campus')->find(session('user_id'));
        $campus = $user->campus;
        
        // Get campuses that this SFAO can monitor
        $monitoredCampuses = $campus->getAllCampusesUnder();
        $monitoredCampusIds = $monitoredCampuses->pluck('id')->toArray();
        
        // Validate that the selected campus is within the SFAO's jurisdiction
        if ($request->campus_id !== 'constituent_with_extensions' && !in_array($request->campus_id, $monitoredCampusIds)) {
            return redirect()->back()->withErrors(['campus_id' => 'You can only create reports for campuses under your jurisdiction.'])->withInput();
        }

        // Generate report data with error handling
        try {
            $reportData = Report::generateReportData(
                $request->campus_id,
                $request->report_period_start,
                $request->report_period_end
            );
        } catch (\Exception $e) {
            // If report data generation fails, create a basic structure
            $reportData = [
                'summary' => [
                    'total_applications' => 0,
                    'approved_applications' => 0,
                    'rejected_applications' => 0,
                    'pending_applications' => 0,
                    'claimed_applications' => 0,
                    'approval_rate' => 0
                ]
            ];
        }

        // Create report
        // For "constituent_with_extensions", use the user's campus ID
        $reportCampusId = $request->campus_id === 'constituent_with_extensions' ? $campus->id : $request->campus_id;
        
        $report = Report::create([
            'sfao_user_id' => session('user_id'),
            'campus_id' => $reportCampusId,
            'original_campus_selection' => $request->campus_id, // Store original selection
            'report_type' => $request->report_type,
            'title' => $request->title,
            'description' => $request->description,
            'report_period_start' => $request->report_period_start,
            'report_period_end' => $request->report_period_end,
            'report_data' => $reportData,
            'notes' => $request->notes,
            'status' => $request->submit_immediately ? 'submitted' : 'draft',
            'submitted_at' => $request->submit_immediately ? now() : null
        ]);

        if ($request->submit_immediately) {
            // Send notification to central admin
            $this->notifyCentralAdmin($report);
        }

        $message = $request->submit_immediately 
            ? 'Report created and submitted successfully!' 
            : 'Report saved as draft successfully!';

        return redirect()->route('sfao.dashboard')
            ->with('success', $message)
            ->with('active_tab', 'reports');
    }

    /**
     * Show report details
     */
    public function showReport($id)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $report = Report::where('sfao_user_id', session('user_id'))
            ->with(['campus', 'reviewer'])
            ->findOrFail($id);

        // If report_data is null or empty, regenerate it
        if (empty($report->report_data)) {
            try {
                $reportData = Report::generateReportData(
                    $report->campus_id,
                    $report->report_period_start,
                    $report->report_period_end
                );
                $report->update(['report_data' => $reportData]);
                $report->refresh();
            } catch (\Exception $e) {
                // If regeneration fails, create a basic structure
                $report->report_data = [
                    'summary' => [
                        'total_applications' => 0,
                        'approved_applications' => 0,
                        'rejected_applications' => 0,
                        'pending_applications' => 0,
                        'claimed_applications' => 0,
                        'approval_rate' => 0
                    ]
                ];
            }
        }

        return view('sfao.reports.show', compact('report'));
    }

    /**
     * Edit report
     */
    public function editReport($id)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $report = Report::where('sfao_user_id', session('user_id'))
            ->where('status', 'draft')
            ->findOrFail($id);

        $user = User::with('campus')->find(session('user_id'));
        $campus = $user->campus;
        
        // Get campuses that this SFAO can monitor (their constituent campus and its extensions)
        $monitoredCampuses = $campus->getAllCampusesUnder();
        
        // Add a special option for "Constituent + Extensions" if the user's campus is a constituent
        $campusOptions = collect();
        
        // Add individual campuses
        foreach ($monitoredCampuses as $campusOption) {
            $campusOptions->push($campusOption);
        }
        
        // Add "Constituent + Extensions" option if the user's campus is a constituent
        if ($campus->type === 'constituent' && $campus->extensionCampuses->count() > 0) {
            $constituentWithExtensions = new \stdClass();
            $constituentWithExtensions->id = 'constituent_with_extensions';
            $constituentWithExtensions->name = $campus->name . ' + Extensions';
            $constituentWithExtensions->type = 'constituent_with_extensions';
            $campusOptions->push($constituentWithExtensions);
        }

        return view('sfao.reports.edit', compact('report', 'campus', 'monitoredCampuses', 'campusOptions'));
    }

    /**
     * Update report
     */
    public function updateReport(Request $request, $id)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $report = Report::where('sfao_user_id', session('user_id'))
            ->where('status', 'draft')
            ->findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'submit_immediately' => 'boolean',
            'campus_id' => 'required'
        ]);

        $user = User::with('campus')->find(session('user_id'));
        $campus = $user->campus;
        
        // Get campuses that this SFAO can monitor
        $monitoredCampuses = $campus->getAllCampusesUnder();
        $monitoredCampusIds = $monitoredCampuses->pluck('id')->toArray();
        
        // Validate that the selected campus is within the SFAO's jurisdiction
        if ($request->campus_id !== 'constituent_with_extensions' && !in_array($request->campus_id, $monitoredCampusIds)) {
            return redirect()->back()->withErrors(['campus_id' => 'You can only create reports for campuses under your jurisdiction.'])->withInput();
        }

        // For "constituent_with_extensions", use the user's campus ID
        $reportCampusId = $request->campus_id === 'constituent_with_extensions' ? $campus->id : $request->campus_id;
        
        $report->update([
            'title' => $request->title,
            'description' => $request->description,
            'notes' => $request->notes,
            'campus_id' => $reportCampusId,
            'original_campus_selection' => $request->campus_id, // Store original selection
            'status' => $request->submit_immediately ? 'submitted' : 'draft',
            'submitted_at' => $request->submit_immediately ? now() : null
        ]);

        if ($request->submit_immediately) {
            // Regenerate report data using original campus selection
            $reportData = Report::generateReportData(
                $request->campus_id,
                $report->report_period_start,
                $report->report_period_end
            );
            $report->update(['report_data' => $reportData]);

            // Send notification to central admin
            $this->notifyCentralAdmin($report);
        }

        $message = $request->submit_immediately 
            ? 'Report updated and submitted successfully!' 
            : 'Report updated successfully!';

        return redirect()->route('sfao.dashboard')
            ->with('success', $message)
            ->with('active_tab', 'reports');
    }

    /**
     * Submit draft report
     */
    public function submitReport($id)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $report = Report::where('sfao_user_id', session('user_id'))
            ->where('status', 'draft')
            ->findOrFail($id);

        // Regenerate report data using original campus selection
        $campusId = $report->original_campus_selection ?? $report->campus_id;
        $reportData = Report::generateReportData(
            $campusId,
            $report->report_period_start,
            $report->report_period_end
        );

        $report->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'report_data' => $reportData
        ]);

        // Send notification to central admin
        $this->notifyCentralAdmin($report);

        return redirect()->route('sfao.dashboard')
            ->with('success', 'Report submitted successfully!')
            ->with('active_tab', 'reports');
    }

    /**
     * Delete report
     */
    public function deleteReport($id)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $report = Report::where('sfao_user_id', session('user_id'))
            ->where('status', 'draft')
            ->findOrFail($id);

        $report->delete();

        return redirect()->route('sfao.dashboard')
            ->with('success', 'Report deleted successfully!')
            ->with('active_tab', 'reports');
    }

    /**
     * Applicant Summary Report Page
     */
    public function applicantSummary(Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $user = User::with('campus')->find(session('user_id'));
        $campus = $user->campus;
        $monitoredCampuses = $campus->getAllCampusesUnder();
        
        // Fetch all active scholarships for the filter
        $scholarships = Scholarship::where('is_active', true)->get();
        
        // Determine Selected Scholarship (Default to first if null/all)
        $scholarshipId = $request->scholarship_id;
        if (!$scholarshipId || $scholarshipId == 'all') {
             $firstScholarship = $scholarships->first();
             $scholarshipId = $firstScholarship ? $firstScholarship->id : null;
        }
        
        $selectedScholarship = $scholarshipId ? Scholarship::find($scholarshipId) : null;
        
        // If still no scholarship (e.g. none exist), handle gracefully
        if (!$selectedScholarship && $scholarships->isNotEmpty()) {
             $selectedScholarship = $scholarships->first();
        }

        // Filter by campus if requested
        if ($request->has('campus_id') && $request->campus_id != 'all') {
            $monitoredCampuses = $monitoredCampuses->where('id', $request->campus_id);
        }

        $reportData = [];
        $approvedStatuses = ['approved', 'accepted', 'claimed'];

        if ($selectedScholarship) {
            foreach ($monitoredCampuses as $camp) {
                $campusData = [
                    'campus' => $camp,
                    'students' => []
                ];
                
                // Fetch students for this campus
                $query = User::where('campus_id', $camp->id)
                    ->where('role', 'student')
                    ->with(['form', 'applications.scholarship']);

                // Filter users who have an APPROVED application for this specific scholarship
                $query->whereHas('applications', function($q) use ($selectedScholarship, $approvedStatuses) {
                        $q->where('scholarship_id', $selectedScholarship->id)
                        ->whereIn('status', $approvedStatuses);
                });

                $students = $query->get();

                $processedStudents = [];
                $seq = 1;

                foreach ($students as $student) {
                    $application = $student->applications
                        ->where('scholarship_id', $selectedScholarship->id)
                        ->whereIn('status', $approvedStatuses)
                        ->first();

                    if (!$application) continue;

                    $form = $student->form;
                    
                    $processedStudents[] = [
                        'seq' => $seq++,
                        'app_id' => $application->id,
                        'last_name' => $student->last_name,
                        'first_name' => $student->first_name,
                        'middle_name' => $student->middle_name,
                        'sex' => $student->sex,
                        'birthdate' => $student->birthdate ? $student->birthdate->format('Y-m-d') : 'N/A',
                        'course' => $student->program ?? $student->college,
                        'year_level' => $student->year_level,
                        'units' => $form ? $form->units_enrolled : 'N/A',
                        'municipality' => $form ? $form->town_city : 'N/A',
                        'province' => $form ? $form->province : 'N/A',
                        'pwd' => $form ? $form->disability : 'N/A',
                        'grant' => $application->scholarship ? $application->scholarship->grant_amount : 'N/A',
                        'status_remarks' => $application->remarks ?: ucfirst($application->status)
                    ];
                }

                $campusData['students'] = $processedStudents;
                $reportData[] = $campusData;
            }
        }


        if ($request->ajax()) {
            return view('sfao.reports.partials.applicant-summary-table', compact('reportData', 'selectedScholarship'));
        }

        return view('sfao.reports.applicant-summary', compact('user', 'monitoredCampuses', 'reportData', 'scholarships', 'selectedScholarship'));
    }

    /**
     * Scholar Summary Report Page
     */
    public function scholarSummary(Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $user = User::with('campus')->find(session('user_id'));
        $campus = $user->campus;
        $monitoredCampuses = $campus->getAllCampusesUnder();
        
        // Fetch all active scholarships
        $scholarships = Scholarship::where('is_active', true)->get();
        
        // Determine Selected Scholarship
        $scholarshipId = $request->scholarship_id;
        if (!$scholarshipId || $scholarshipId == 'all') {
             $firstScholarship = $scholarships->first();
             $scholarshipId = $firstScholarship ? $firstScholarship->id : null;
        }
        $selectedScholarship = $scholarshipId ? Scholarship::find($scholarshipId) : null;
        if (!$selectedScholarship && $scholarships->isNotEmpty()) {
             $selectedScholarship = $scholarships->first();
        }

        // Filter by campus if selected
        if ($request->has('campus_id') && $request->campus_id != 'all') {
            $monitoredCampuses = $monitoredCampuses->where('id', $request->campus_id);
        }

        $reportData = [];

        if ($selectedScholarship) {
            foreach ($monitoredCampuses as $camp) {
                $campusData = [
                    'campus' => $camp,
                    'scholars' => []
                ];

                // Fetch students who have this specific scholarship assigned as a Scholar record
                $students = User::where('campus_id', $camp->id)
                    ->where('role', 'student')
                    ->whereHas('scholars', function($q) use ($selectedScholarship) {
                        $q->where('scholarship_id', $selectedScholarship->id);
                        // Optional: Filter by 'active' status if required, but default to showing all records of this scholarship
                    })
                    ->with(['form', 'scholars' => function($q) use ($selectedScholarship) {
                        $q->where('scholarship_id', $selectedScholarship->id);
                    }])
                    ->get();

                $processedScholars = [];
                $seq = 1;

                foreach ($students as $student) {
                    $scholarRecord = $student->scholars->first(); // Eager loaded filtered relation
                    if (!$scholarRecord) continue;

                    $form = $student->form;

                    $processedScholars[] = [
                        'seq' => $seq++,
                        'app_id' => $scholarRecord->id, // Using Scholar ID? OR keep App ID if mapped. Used id for now.
                        'last_name' => $student->last_name,
                        'first_name' => $student->first_name,
                        'middle_name' => $student->middle_name,
                        'sex' => $student->sex,
                        'department' => $student->college,
                        'program' => $student->program,
                    ];
                }

                $campusData['scholars'] = $processedScholars;
                $reportData[] = $campusData;
            }
        }

        if ($request->ajax()) {
            return view('sfao.reports.partials.scholar-summary-table', compact('reportData', 'selectedScholarship'));
        }

        return view('sfao.reports.scholar-summary', compact('user', 'monitoredCampuses', 'reportData', 'scholarships', 'selectedScholarship'));
    }

    /**
     * Grant Summary Report Page
     */
    public function grantSummary(Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $user = User::with('campus')->find(session('user_id'));
        $campus = $user->campus;
        $monitoredCampuses = $campus->getAllCampusesUnder();
        $campusIds = $monitoredCampuses->pluck('id');

        // Base query for approved/claimed applications (Grants)
        $query = Application::whereIn('status', ['approved', 'claimed'])
            ->whereHas('user', function($q) use ($campusIds) {
                $q->whereIn('campus_id', $campusIds);
            });

        // Apply filters
        if ($request->has('campus_id') && $request->campus_id != 'all') {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('campus_id', $request->campus_id);
            });
        }

        $totalGrants = (clone $query)->count();

        // Claimed vs Unclaimed
        $statusStats = (clone $query)->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Grants by Scholarship Type
        $typeStats = (clone $query)
            ->join('scholarships', 'applications.scholarship_id', '=', 'scholarships.id')
            ->select('scholarships.scholarship_type', DB::raw('count(*) as total'))
            ->groupBy('scholarships.scholarship_type')
            ->pluck('total', 'scholarship_type')
            ->toArray();

        return view('sfao.reports.grant-summary', compact('user', 'monitoredCampuses', 'totalGrants', 'statusStats', 'typeStats'));
    }

    // =====================================================
    // CENTRAL ADMIN REPORT METHODS
    // =====================================================


    /**
     * Show report details for central admin
     */
    public function centralShowReport($id)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        $report = Report::with(['sfaoUser', 'campus', 'reviewer'])
            ->findOrFail($id);

        return view('central.reports.show', compact('report'));
    }

    /**
     * Review report
     */
    public function reviewReport(Request $request, $id)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        $request->validate([
            'status' => 'required|in:reviewed,approved',
            'feedback' => 'nullable|string|max:1000'
        ]);

        $report = Report::findOrFail($id);

        $report->update([
            'status' => $request->status,
            'central_feedback' => $request->feedback,
            'reviewed_at' => now(),
            'reviewed_by' => session('user_id')
        ]);

        // Send notification to SFAO admin
        $this->notifySfaoAdmin($report);

        $message = $request->status === 'approved' 
            ? 'Report approved successfully!' 
            : 'Report marked as reviewed successfully!';

        return redirect()->route('central.dashboard')
            ->with('success', $message);
    }

    // =====================================================
    // PRIVATE HELPER METHODS
    // =====================================================

    /**
     * Notify central admin about new report
     */
    private function notifyCentralAdmin(Report $report)
    {
        // Get all central admins
        $centralAdmins = User::where('role', 'central')->get();

        foreach ($centralAdmins as $admin) {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'type' => 'report_submitted',
                'title' => 'New SFAO Report Submitted',
                'message' => "A new {$report->getReportTypeDisplayName()} has been submitted by {$report->sfaoUser->name} from {$report->campus->name}.",
                'data' => [
                    'report_id' => $report->id,
                    'sfao_user_id' => $report->sfao_user_id,
                    'campus_id' => $report->campus_id,
                    'report_type' => $report->report_type,
                    'title' => $report->title
                ]
            ]);
        }
    }

    /**
     * Submit Summary Report from Tab
     */
    public function submitSummaryReport(Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $request->validate([
            'report_type' => 'required|in:student_summary,scholar_summary,grant_summary',
            'frequency' => 'required|in:monthly,quarterly,semi-annual,annual',
            'description' => 'nullable|string',
            'campus_id' => 'required'
        ]);

        $user = User::with('campus')->find(session('user_id'));
        $campus = $user->campus;

        // Determine Campus ID for the report
        $reportCampusId = $request->campus_id;
        if ($reportCampusId === 'all') {
            // If 'all' is selected, we might want to store the user's main campus ID 
            // but indicate it covers all monitored campuses in the report data or a separate field.
            // For now, let's use the user's campus ID as the primary owner.
            $reportCampusId = $campus->id;
        }

        // 1. Regenerate Data Snapshot
        $reportData = [];
        $title = '';

        if ($request->report_type === 'student_summary') {
            $scholarship = $request->scholarship_id ? Scholarship::find($request->scholarship_id) : null;
            $title = $scholarship ? $scholarship->scholarship_name . ' Applicant Summary Report' : 'Applicant Summary Report'; // Default fallback, but dynamic title overwrites
            $reportData = $this->generateStudentSummaryData(
                $request->campus_id, 
                $campus, 
                $request->scholarship_id,
                $request->student_type ?? 'applicants',
                $request->college,
                $request->program,
                $request->track,
                $request->academic_year
            );
        } 
        elseif ($request->report_type === 'scholar_summary') {
            $scholarship = $request->scholarship_id ? Scholarship::find($request->scholarship_id) : null;
            $title = $scholarship ? $scholarship->scholarship_name . ' Scholar Summary Report' : 'Scholar Summary Report';
            $reportData = $this->generateScholarSummaryData($request->campus_id, $campus, $request->scholarship_id);
        } 
        elseif ($request->report_type === 'grant_summary') {
            $title = 'Grant Summary Report';
            $reportData = $this->generateGrantSummaryData($request->campus_id, $campus);
        }

        // 2. Create Report Record
        $report = Report::create([
            'sfao_user_id' => session('user_id'),
            'campus_id' => $reportCampusId, 
            'original_campus_selection' => $request->campus_id,
            'report_type' => $request->report_type . '_' . $request->frequency,
            'student_type' => $request->student_type, // New
            'college_id' => ($request->college && $request->college != 'all') ? \App\Models\College::where('short_name', $request->college)->value('id') : null, // New
            'program_id' => ($request->program && $request->program != 'all') ? \App\Models\Program::where('name', $request->program)->value('id') : null, // New
            'track_id' => ($request->track && $request->track != 'all') ? \App\Models\ProgramTrack::where('name', $request->track)->value('id') : null, // New
            'academic_year' => ($request->academic_year && $request->academic_year != 'all') ? $request->academic_year : null, // New
            'title' => $request->dynamic_title ?? ($title . ' - ' . ucfirst($request->frequency)), // Use dynamic title if provided
            'description' => $request->description,
            'report_period_start' => now()->startOfMonth(),
            'report_period_end' => now()->endOfMonth(),
            'report_data' => $reportData,
            'status' => 'submitted',
            'submitted_at' => now()
        ]);

        // 3. Notify Central
        $this->notifyCentralAdmin($report);

        return redirect()->back()->with('success', 'Report submitted successfully to Central Office!');
    }

    // =====================================================
    // DATA GENERATION HELPERS
    // =====================================================

    private function generateStudentSummaryData($campusIdSelection, $userCampus, $scholarshipId, $studentType, $collegeFilter, $programFilter, $trackFilter, $academicYearFilter)
    {
        $monitoredCampuses = $userCampus->getAllCampusesUnder();
        if ($campusIdSelection && $campusIdSelection != 'all') {
            $monitoredCampuses = $monitoredCampuses->where('id', $campusIdSelection);
        }

        $reportData = [];
        
        // Prepare Program Map for normalization if needed
        $programMap = \App\Models\Program::pluck('short_name', 'name')->toArray();

        // Prepare Statuses
        $approvedStatuses = ['approved', 'accepted', 'claimed'];

        foreach ($monitoredCampuses as $camp) {
            $campusData = [
                'campus' => $camp,
                'students' => []
            ];

            // Base Query
            $query = User::where('campus_id', $camp->id)->where('role', 'student');

            // 1. College Filter
            if ($collegeFilter && $collegeFilter !== 'all') {
                $query->where(function($q) use ($collegeFilter) {
                    $q->where('college', $collegeFilter)
                      ->orWhere('college', \App\Models\College::where('short_name', $collegeFilter)->value('name') ?? $collegeFilter);
                });
            }

            // 2. Program Filter
            if ($programFilter && $programFilter !== 'all') {
                $query->where('program', $programFilter);
            }

            // 3. Track Filter
            if ($trackFilter && $trackFilter !== 'all') {
                $query->where('track', $trackFilter);
            }

            // 4. Student Type Logic & Eager Loading
            if ($studentType === 'applicants') {
                // Must have an application
                $query->whereHas('applications', function($q) use ($scholarshipId, $academicYearFilter, $approvedStatuses) {
                    if ($scholarshipId && $scholarshipId !== 'all') {
                        $q->where('scholarship_id', $scholarshipId);
                    }
                    
                    // Exclude approved/claimed applications (they belong in Scholar report)
                    $q->whereNotIn('status', $approvedStatuses);

                    // Academic Year Filter for Applicants
                    if ($academicYearFilter === 'custom') {
                         // Skip custom logic for now
                    } elseif ($academicYearFilter && $academicYearFilter !== 'all') {
                         $years = explode('-', $academicYearFilter);
                         if (count($years) === 2) {
                             $startDt = \Carbon\Carbon::createFromDate($years[0], 8, 1)->startOfDay();
                             $endDt = \Carbon\Carbon::createFromDate($years[1], 7, 31)->endOfDay();
                             $q->whereBetween('created_at', [$startDt, $endDt]);
                         }
                    }
                })->with(['applications' => function($q) use ($scholarshipId) {
                    if ($scholarshipId && $scholarshipId !== 'all') {
                        $q->where('scholarship_id', $scholarshipId);
                    }
                    $q->latest();
                }, 'form', 'applications.scholarship']);
            
            } else {
                // Scholars
                $query->whereHas('scholars', function($q) use ($scholarshipId, $academicYearFilter) {
                     if ($scholarshipId && $scholarshipId !== 'all') {
                         $q->where('scholarship_id', $scholarshipId);
                     }
                      // Academic Year Filter for Scholars
                      if ($academicYearFilter && $academicYearFilter !== 'all' && $academicYearFilter !== 'custom') {
                         $years = explode('-', $academicYearFilter);
                         if (count($years) === 2) {
                             $startDt = \Carbon\Carbon::createFromDate($years[0], 8, 1)->startOfDay();
                             $endDt = \Carbon\Carbon::createFromDate($years[1], 7, 31)->endOfDay();
                             $q->whereBetween('created_at', [$startDt, $endDt]);
                         }
                      }
                })->with(['scholars' => function($q) use ($scholarshipId) {
                     if ($scholarshipId && $scholarshipId !== 'all') {
                         $q->where('scholarship_id', $scholarshipId);
                     }
                }, 'scholars.scholarship']);
            }

            $students = $query->get();
            $processedStudents = [];
            $seq = 1;
            
            foreach ($students as $student) {
                if ($studentType === 'applicants') {
                    $app = $student->applications->first();
                    if (!$app) continue;

                    // Re-check AY logic if exact date match needed (since has/whereHas covers existence, specific app might differ? No, usually fine)
                    
                     $processedStudents[] = [
                        'seq' => $seq++,
                         'app_id' => $app->id,
                        'last_name' => $student->last_name,
                        'first_name' => $student->first_name,
                        'middle_name' => $student->middle_name,
                        'sex' => $student->sex,
                        'birthdate' => $student->birthdate ? $student->birthdate->format('Y-m-d') : '',
                        'program' => $student->program,
                        'track' => $student->track,
                        'year_level' => $student->year_level,
                        'units' => $student->form ? $student->form->units_enrolled : '',
                        'municipality' => $student->form ? $student->form->town_city : '',
                        'province' => $student->form ? $student->form->province : '',
                        'pwd' => $student->form ? ($student->form->disability ?: 'No') : 'No',
                        'scholarship' => $app->scholarship ? $app->scholarship->scholarship_name : 'N/A',
                        'grant' => $app->scholarship ? $app->scholarship->grant_amount : 'N/A',
                        'status_remarks' => $app->remarks ?: ucfirst($app->status)
                     ];

                } else {
                    // Scholars
                    foreach ($student->scholars as $scholar) {
                        // Apply Scholarship Filter (Explicit loop check if multiple scholars per user)
                        if ($scholarshipId && $scholarshipId !== 'all' && $scholar->scholarship_id != $scholarshipId) continue;
                        
                        // Apply AY Filter (Double check)
                        if ($academicYearFilter && $academicYearFilter !== 'all' && $academicYearFilter !== 'custom') {
                             $years = explode('-', $academicYearFilter);
                             if (count($years) === 2) {
                                  $startDt = \Carbon\Carbon::createFromDate($years[0], 8, 1)->startOfDay();
                                  $endDt = \Carbon\Carbon::createFromDate($years[1], 7, 31)->endOfDay();
                                  if (!\Carbon\Carbon::parse($scholar->created_at)->between($startDt, $endDt)) continue;
                             }
                        }

                        $processedStudents[] = [
                            'seq' => $seq++,
                            'app_id' => $scholar->id,
                            'last_name' => $student->last_name,
                            'first_name' => $student->first_name,
                            'middle_name' => $student->middle_name,
                            'sex' => $student->sex,
                             'college' => $student->college, // Needed for scholar view
                            'program' => $student->program,
                            'track' => $student->track,
                            'scholarship' => $scholar->scholarship ? $scholar->scholarship->scholarship_name : 'N/A'
                        ];
                    }
                }
            }

            $campusData['students'] = $processedStudents;
            $reportData[] = $campusData;
        }

        return [
            'type' => 'student_summary',
            'details' => $reportData,
            'scholarship_id' => $scholarshipId
        ];
    }

    private function generateScholarSummaryData($campusIdSelection, $userCampus, $scholarshipId = null)
    {
        $monitoredCampuses = $userCampus->getAllCampusesUnder();
        if ($campusIdSelection && $campusIdSelection != 'all') {
            $monitoredCampuses = $monitoredCampuses->where('id', $campusIdSelection);
        }

        $reportData = [];
        $summaryStats = ['old_scholars' => 0, 'new_scholars' => 0, 'non_scholars' => 0, 'total' => 0];

        foreach ($monitoredCampuses as $camp) {
            $campusData = ['campus_name' => $camp->name, 'scholars' => []]; // Removed non_scholars as per new targeted report style
            
            $query = User::where('campus_id', $camp->id)->where('role', 'student');

            // Apply scholarship filter if present
            if ($scholarshipId) {
                $query->whereHas('scholars', function($q) use ($scholarshipId) {
                    $q->where('scholarship_id', $scholarshipId);
                })->with(['scholars' => function($q) use ($scholarshipId) {
                    $q->where('scholarship_id', $scholarshipId);
                }]);
            } else {
                 $query->with(['scholars' => function($q) {
                    $q->where('status', 'active');
                }]);
            }

            $students = $query->get();
            $seq = 1;

            foreach ($students as $student) {
                $activeScholar = $student->scholars->first();
                if (!$activeScholar) continue; // In the new targeted report, we only care about the filtered scholars

                $sData = [
                    'seq' => $seq++,
                    'name' => $student->last_name . ', ' . $student->first_name,
                    'last_name' => $student->last_name,
                    'first_name' => $student->first_name,
                    'middle_name' => $student->middle_name,
                    'sex' => $student->sex,
                    'college' => $student->college ?? 'N/A',
                    'program' => $student->program ?? 'N/A',
                    'year_level' => $student->year_level ?? 'N/A',
                    'status' => ucfirst($activeScholar->type) . ' Scholar'
                ];
                
                $campusData['scholars'][] = $sData;

                if ($activeScholar->type === 'old') $summaryStats['old_scholars']++;
                else $summaryStats['new_scholars']++;
                
                $summaryStats['total']++;
            }
            $reportData[] = $campusData;
        }

        return [
            'type' => 'scholar_summary',
            'details' => $reportData,
            'stats' => $summaryStats,
            'scholarship_id' => $scholarshipId // Store which scholarship this was for
        ];
    }

    private function generateGrantSummaryData($campusIdSelection, $userCampus)
    {
        $monitoredCampuses = $userCampus->getAllCampusesUnder();
        
        $campusIds = $monitoredCampuses->pluck('id');
        if ($campusIdSelection && $campusIdSelection != 'all') {
            $campusIds = [$campusIdSelection];
        }

        $query = Application::whereIn('status', ['approved', 'claimed'])
            ->whereHas('user', function($q) use ($campusIds) {
                $q->whereIn('campus_id', $campusIds);
            });

        $totalGrants = (clone $query)->count();
        $statusStats = (clone $query)->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status')->toArray();
        $typeStats = (clone $query)
            ->join('scholarships', 'applications.scholarship_id', '=', 'scholarships.id')
            ->select('scholarships.scholarship_type', DB::raw('count(*) as total'))
            ->groupBy('scholarships.scholarship_type')
            ->pluck('total', 'scholarship_type')
            ->toArray();

        return [
            'type' => 'grant_summary',
            'total_grants' => $totalGrants,
            'status_stats' => $statusStats,
            'type_stats' => $typeStats
        ];
    }


    /**
     * Notify SFAO admin about report review
     */
    private function notifySfaoAdmin(Report $report)
    {
        \App\Models\Notification::create([
            'user_id' => $report->sfao_user_id,
            'type' => 'report_reviewed',
            'title' => 'Report Review Update',
            'message' => "Your report '{$report->title}' has been {$report->status} by Central Administration.",
            'data' => [
                'report_id' => $report->id,
                'status' => $report->status,
                'feedback' => $report->central_feedback,
                'reviewed_by' => $report->reviewer->name ?? 'Central Admin'
            ]
        ]);
    }

    /**
     * Student Summary Report Page (Unified Applicant/Scholar)
     */
    public function studentSummary(Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $user = User::with('campus')->find(session('user_id'));
        $campus = $user->campus;
        $monitoredCampuses = $campus->getAllCampusesUnder();
        $campusIds = $monitoredCampuses->pluck('id');

        // Filters
        $studentType = $request->get('student_type', 'applicants'); // applicants, scholars
        $campusId = $request->get('campus_id', 'all');
        
        // If specific campus selected
        if ($campusId !== 'all') {
            $monitoredCampuses = $monitoredCampuses->where('id', $campusId);
            $campusIds = [$campusId];
        }

        $collegeFilter = $request->get('college', 'all');
        $programFilter = $request->get('program', 'all');
        $trackFilter = $request->get('track', 'all');
        $academicYearFilter = $request->get('academic_year', 'all');
        $scholarshipFilter = $request->get('scholarship_id', 'all');

        // Fetch Filter Options (Departments, Programs, Academic Years, Scholarships)
        // Build Cascading Maps
        $allMonitoredIds = $campus->getAllCampusesUnder()->pluck('id');
        $mapData = User::whereIn('campus_id', $allMonitoredIds)
            ->where('role', 'student')
            ->select('campus_id', 'college', 'program', 'track')
            ->distinct()
            ->get();
            
        $campusCollegePrograms = [];
        $programTracks = [];
        
        // Fetch Colleges for normalization
        $collegesRef = \App\Models\College::all();
        $collegeNameMap = $collegesRef->pluck('short_name', 'name')->toArray();
        $collegeShortMap = $collegesRef->pluck('name', 'short_name')->toArray(); // Just in case needed

        foreach ($mapData as $row) {
            if (!$row->college) continue;
            
            // Normalize College Name to Short Name for the Map Key
            $rawCol = $row->college;
            $colName = $collegeNameMap[$rawCol] ?? $rawCol;
            
            // Should also check if $rawCol IS a short name already, basically we want consistent keys matching the dropdown values (which are short names)
            // The dropdown uses $college->short_name. So we ensure this key is the short name.
            
            $progName = $row->program;
            
            if ($progName) {
                // Initialize array structure
                if (!isset($campusCollegePrograms[$row->campus_id][$colName])) {
                    $campusCollegePrograms[$row->campus_id][$colName] = [];
                }
                if (!in_array($progName, $campusCollegePrograms[$row->campus_id][$colName])) {
                    $campusCollegePrograms[$row->campus_id][$colName][] = $progName;
                }
                
                if ($row->track) {
                    if (!isset($programTracks[$progName])) {
                        $programTracks[$progName] = [];
                    }
                    if (!in_array($row->track, $programTracks[$progName])) {
                        $programTracks[$progName][] = $row->track;
                    }
                }
            }
        }
        
        // Pass colleges list for initial view (normalized unique colleges)
        $colleges = \App\Models\College::select('name', 'short_name')->get();

        // Academic Years (Dynamic Context-Aware Logic)
        $campusAcademicYearMap = [
            'applicants' => [],
            'scholars' => []
        ];

        // Ensure we have 'all' keys initialized
        $campusAcademicYearMap['applicants']['all'] = [];
        $campusAcademicYearMap['scholars']['all'] = [];

        // Helper to determine AY from date
        $getAy = function($date) {
            $d = \Carbon\Carbon::parse($date);
            $y = $d->year;
            $m = $d->month;
            if ($m >= 8) {
                return $y . '-' . ($y + 1);
            } else {
                return ($y - 1) . '-' . $y;
            }
        };

        // 1. Fetch Applicants Dates per Campus (via User)
        $appRaw = \Illuminate\Support\Facades\DB::table('applications')
            ->join('users', 'applications.user_id', '=', 'users.id')
            ->whereIn('users.campus_id', $allMonitoredIds)
            ->selectRaw('users.campus_id, YEAR(applications.created_at) as y, MONTH(applications.created_at) as m')
            ->distinct()
            ->get();

        foreach ($appRaw as $row) {
             // Reconstruct a date to use helper or inline logic
             $ay = ($row->m >= 8) ? ($row->y . '-' . ($row->y + 1)) : (($row->y - 1) . '-' . $row->y);
             
             // Initialize array if not present
             if (!isset($campusAcademicYearMap['applicants'][$row->campus_id])) {
                 $campusAcademicYearMap['applicants'][$row->campus_id] = [];
             }
             
             $campusAcademicYearMap['applicants'][$row->campus_id][] = $ay;
             $campusAcademicYearMap['applicants']['all'][] = $ay;
        }

        // 2. Fetch Scholars Dates per Campus (via User)
        $scholarRaw = \Illuminate\Support\Facades\DB::table('scholars')
             ->join('users', 'scholars.user_id', '=', 'users.id')
             ->whereIn('users.campus_id', $allMonitoredIds)
             ->selectRaw('users.campus_id, YEAR(scholars.created_at) as y, MONTH(scholars.created_at) as m')
             ->distinct()
             ->get();

        foreach ($scholarRaw as $row) {
             $ay = ($row->m >= 8) ? ($row->y . '-' . ($row->y + 1)) : (($row->y - 1) . '-' . $row->y);
             
             if (!isset($campusAcademicYearMap['scholars'][$row->campus_id])) {
                 $campusAcademicYearMap['scholars'][$row->campus_id] = [];
             }
             
             $campusAcademicYearMap['scholars'][$row->campus_id][] = $ay;
             $campusAcademicYearMap['scholars']['all'][] = $ay;
        }

        // De-duplicate and sort
        foreach ($campusAcademicYearMap as $type => &$campuses) {
             foreach ($campuses as $cId => &$years) {
                 $years = array_unique($years);
                 rsort($years); // Latest first
             }
        }
        
        // Initial generic options (union of everything found)
        // We can just use the 'all' key of the current type for initial render, or pass the map.
        // We'll pass the map.
        $academicYearOptions = $campusAcademicYearMap['applicants']['all']; // Default fallback
        
        // Scholarships
        $scholarships = \App\Models\Scholarship::where('is_active', true)->select('id', 'scholarship_name')->orderBy('scholarship_name')->get();


        // Data Fetching
        $reportData = [];
        
        // Pre-fetch Program Short Names Map
        $programMap = \App\Models\Program::pluck('short_name', 'name')->toArray();

        foreach ($monitoredCampuses as $camp) {
            $campusData = [
                'campus' => $camp,
                'students' => []
            ];

            $query = User::where('campus_id', $camp->id)->where('role', 'student');

             // Apply College Filter
            if ($collegeFilter !== 'all') {
                $query->where(function($q) use ($collegeFilter) {
                    $q->where('college', $collegeFilter)
                      ->orWhere('college', \App\Models\College::where('short_name', $collegeFilter)->value('name') ?? $collegeFilter);
                });
            }
             // Apply Program Filter
            if ($programFilter !== 'all') {
                $query->where('program', $programFilter);
            }
            // Apply Track Filter
            if ($trackFilter !== 'all') {
                $query->where('track', $trackFilter);
            }
            
            // For Applicants vs Scholars
            if ($studentType === 'applicants') {
                 // Filter by Applicant logic (Students who have applications AND are NOT scholars)
                 $query->whereDoesntHave('scholars')
                       ->whereHas('applications', function($qApp) use ($academicYearFilter, $scholarshipFilter, $request) {
                     // Scholarship Filter
                     if ($scholarshipFilter !== 'all') {
                         $qApp->where('scholarship_id', $scholarshipFilter);
                     }
                     // Academic Year Filter for Applicants
                     if ($academicYearFilter === 'custom') {
                         if ($request->custom_start && $request->custom_end) {
                             $startDt = \Carbon\Carbon::parse($request->custom_start)->startOfDay();
                             $endDt = \Carbon\Carbon::parse($request->custom_end)->endOfDay();
                             $qApp->whereBetween('created_at', [$startDt, $endDt]);
                         }
                     } elseif ($academicYearFilter !== 'all') {
                         $years = explode('-', $academicYearFilter);
                         if (count($years) === 2) {
                             $startDt = \Carbon\Carbon::createFromDate($years[0], 8, 1)->startOfDay();
                             $endDt = \Carbon\Carbon::createFromDate($years[1], 7, 31)->endOfDay();
                             $qApp->whereBetween('created_at', [$startDt, $endDt]);
                         }
                     }
                 })->with(['form', 'applications.scholarship']);
            } else {
                // Scholars
                $query->whereHas('scholars', function($qScholar) use ($academicYearFilter, $scholarshipFilter, $request) {
                     // Scholarship Filter
                     if ($scholarshipFilter !== 'all') {
                         $qScholar->where('scholarship_id', $scholarshipFilter);
                     }
                     // Academic Year Filter for Scholars
                      // Academic Year Filter for Scholars
                      if ($academicYearFilter === 'custom') {
                         if ($request->custom_start && $request->custom_end) {
                             $startDt = \Carbon\Carbon::parse($request->custom_start)->startOfDay();
                             $endDt = \Carbon\Carbon::parse($request->custom_end)->endOfDay();
                             $qScholar->whereBetween('created_at', [$startDt, $endDt]);
                         }
                     } elseif ($academicYearFilter !== 'all') {
                         $years = explode('-', $academicYearFilter);
                         if (count($years) === 2) {
                             $startDt = \Carbon\Carbon::createFromDate($years[0], 8, 1)->startOfDay();
                             $endDt = \Carbon\Carbon::createFromDate($years[1], 7, 31)->endOfDay();
                             $qScholar->whereBetween('created_at', [$startDt, $endDt]);
                         }
                     }
                })->with(['form', 'scholars.scholarship']);
            }

            $students = $query->get();
            $processedStudents = [];
            $seq = 1;

            foreach ($students as $student) {
                if ($studentType === 'applicants') {
                    // List all relevant applications for this student
                    foreach ($student->applications as $app) {
                         // Apply Scholarship Filter
                         if ($scholarshipFilter !== 'all' && $app->scholarship_id != $scholarshipFilter) continue;
                        
                         // Apply AY Filter to the specific application if needed
                         if ($academicYearFilter === 'custom') {
                             if ($request->custom_start && $request->custom_end) {
                                 $startDt = \Carbon\Carbon::parse($request->custom_start)->startOfDay();
                                 $endDt = \Carbon\Carbon::parse($request->custom_end)->endOfDay();
                                 if (!\Carbon\Carbon::parse($app->created_at)->between($startDt, $endDt)) continue;
                             }
                         } elseif ($academicYearFilter !== 'all') {
                              $years = explode('-', $academicYearFilter);
                              if (count($years) === 2) {
                                  $startDt = \Carbon\Carbon::createFromDate($years[0], 8, 1)->startOfDay();
                                  $endDt = \Carbon\Carbon::createFromDate($years[1], 7, 31)->endOfDay();
                                  if (!\Carbon\Carbon::parse($app->created_at)->between($startDt, $endDt)) continue;
                              }
                         }
                        
                         $processedStudents[] = [
                            'seq' => $seq++,
                            'app_id' => $app->id,
                            'last_name' => $student->last_name,
                            'first_name' => $student->first_name,
                            'middle_name' => $student->middle_name,
                            'sex' => $student->sex,
                            'birthdate' => $student->birthdate ? $student->birthdate->format('Y-m-d') : 'N/A',
                            'course' => $programMap[$student->program] ?? ($student->program ?? $student->college),
                            'track' => $student->track, // Add Track
                            'year_level' => $student->year_level,
                            'units' => $student->form ? $student->form->units_enrolled : 'N/A',
                            'municipality' => $student->form ? $student->form->town_city : 'N/A',
                            'province' => $student->form ? $student->form->province : 'N/A',
                            'pwd' => $student->form ? $student->form->disability : 'N/A',
                            'scholarship' => $app->scholarship ? $app->scholarship->scholarship_name : 'N/A',
                            'grant' => $app->scholarship ? $app->scholarship->grant_amount : 'N/A',
                            'status_remarks' => $app->remarks ?: ucfirst($app->status)
                         ];
                    }
                } else {
                     // Scholars
                     foreach ($student->scholars as $scholar) {
                        // Apply Scholarship Filter
                        if ($scholarshipFilter !== 'all' && $scholar->scholarship_id != $scholarshipFilter) continue;
                         
                        // Apply AY filter Check
                        // Apply AY filter Check
                        if ($academicYearFilter === 'custom') {
                             if ($request->custom_start && $request->custom_end) {
                                 $startDt = \Carbon\Carbon::parse($request->custom_start)->startOfDay();
                                 $endDt = \Carbon\Carbon::parse($request->custom_end)->endOfDay();
                                 if (!\Carbon\Carbon::parse($scholar->created_at)->between($startDt, $endDt)) continue;
                             }
                        } elseif ($academicYearFilter !== 'all') {
                              $years = explode('-', $academicYearFilter);
                              if (count($years) === 2) {
                                  $startDt = \Carbon\Carbon::createFromDate($years[0], 8, 1)->startOfDay();
                                  $endDt = \Carbon\Carbon::createFromDate($years[1], 7, 31)->endOfDay();
                                  if (!\Carbon\Carbon::parse($scholar->created_at)->between($startDt, $endDt)) continue;
                              }
                        }

                        $processedStudents[] = [
                            'seq' => $seq++,
                            'app_id' => $scholar->id, // Scholar ID
                            'last_name' => $student->last_name,
                            'first_name' => $student->first_name,
                            'middle_name' => $student->middle_name,
                            'sex' => $student->sex,
                            'college' => $student->college,
                            'course' => $programMap[$student->program] ?? ($student->program ?? $student->college),
                            'track' => $student->track, // Add Track
                            'program' => $student->program, // Keep original for reference if needed, or rely on course
                            'scholarship' => $scholar->scholarship ? $scholar->scholarship->scholarship_name : 'N/A',
                            'status' => ucfirst($scholar->type) . ' Scholar'
                         ];
                     }
                }
            }
            $campusData['students'] = $processedStudents;
            $reportData[] = $campusData;
        }

        // Dynamic Title Generation
        $titleParts = [];
        // Campus
        if ($campusId !== 'all') {
            $c = $monitoredCampuses->where('id', $campusId)->first();
            $titleParts[] = $c ? $c->name : 'Campus';
        } else {
            $titleParts[] = 'All Campuses';
        }
        // College
        if ($collegeFilter !== 'all') $titleParts[] = $collegeFilter;
        // Program
        if ($programFilter !== 'all') $titleParts[] = $programMap[$programFilter] ?? $programFilter;
        // Track
        if ($trackFilter !== 'all') $titleParts[] = $trackFilter;
        // AY
        if ($academicYearFilter === 'custom' && $request->custom_start && $request->custom_end) {
            $titleParts[] = 'Custom Date (' . $request->custom_start . ' to ' . $request->custom_end . ')';
        } elseif ($academicYearFilter !== 'all') {
            $titleParts[] = 'AY ' . $academicYearFilter;
        }

        $dynamicTitle = implode(', ', $titleParts) . ' - ' . ucfirst($studentType) . ' Summary';

        // Export to Excel Logic
        if ($request->get('export') === 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set Headers
            $headers = ['Seq', 'Last Name', 'First Name', 'Middle Name', 'Sex', 'Birthdate', 'Course/Program', 'Track', 'Year Level', 'Units', 'Municipality', 'Province', 'PWD', 'Scholarship', 'Grant', 'Status/Remarks'];
            $sheet->fromArray($headers, NULL, 'A1');
            
            // Style Header
            $sheet->getStyle('A1:O1')->getFont()->setBold(true);

            $row = 2;
            foreach ($reportData as $data) {
                // Campus Header Row (Optional, but good for separation if multiple campuses)
                if (count($reportData) > 1) {
                    $sheet->setCellValue('A' . $row, $data['campus']->name);
                    $sheet->mergeCells("A{$row}:P{$row}");
                    $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setItalic(true);
                    $row++;
                }

                foreach ($data['students'] as $student) {
                    $sheet->setCellValue('A' . $row, $student['seq']);
                    $sheet->setCellValue('B' . $row, $student['last_name']);
                    $sheet->setCellValue('C' . $row, $student['first_name']);
                    $sheet->setCellValue('D' . $row, $student['middle_name']);
                    $sheet->setCellValue('E' . $row, $student['sex']);
                    // Check if keys exist (Applicant vs Scholar diffs)
                    $sheet->setCellValue('F' . $row, $student['birthdate'] ?? 'N/A');
                    $sheet->setCellValue('G' . $row, $student['course'] ?? ($student['program'] ?? 'N/A'));
                    $sheet->setCellValue('H' . $row, $student['track'] ?? 'N/A');
                    $sheet->setCellValue('I' . $row, $student['year_level'] ?? 'N/A');
                    $sheet->setCellValue('J' . $row, $student['units'] ?? 'N/A');
                    $sheet->setCellValue('K' . $row, $student['municipality'] ?? 'N/A');
                    $sheet->setCellValue('L' . $row, $student['province'] ?? 'N/A');
                    $sheet->setCellValue('M' . $row, $student['pwd'] ?? 'N/A');
                    $sheet->setCellValue('N' . $row, $student['scholarship']);
                    $sheet->setCellValue('O' . $row, $student['grant'] ?? 'N/A');
                    $sheet->setCellValue('P' . $row, $student['status_remarks'] ?? ($student['status'] ?? 'N/A'));
                    $row++;
                }
            }

            foreach(range('A','P') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $safeTitle = preg_replace('/[^A-Za-z0-9_\-]/', '_', $dynamicTitle);
            $fileName = "Student_Summary_" . $safeTitle . "_" . date('Y-m-d_His') . ".xlsx";

            return response()->streamDownload(function() use ($writer) {
                $writer->save('php://output');
            }, $fileName);
        }

        if ($request->ajax()) {
            return view('sfao.reports.partials.student-summary-table', compact('reportData', 'studentType'));
        }

        if ($request->ajax()) {
            return view('sfao.reports.partials.student-summary-table', compact('reportData', 'studentType', 'dynamicTitle'));
        }

        return view('sfao.reports.student-summary', 
            compact('user', 'monitoredCampuses', 'reportData', 'studentType', 'colleges', 'campusCollegePrograms', 'programTracks', 'academicYearOptions', 'scholarships', 'dynamicTitle', 'campusAcademicYearMap'));
    }
}
