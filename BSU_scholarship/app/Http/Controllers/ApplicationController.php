<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Application;
use App\Models\Scholarship;
use App\Models\StudentSubmittedDocument;
use App\Models\Campus;
use App\Models\Notification;
use App\Models\RejectedApplicant;
use App\Models\Scholar;
use App\Services\NotificationService;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * =====================================================
 * APPLICATION MANAGEMENT CONTROLLER
 * =====================================================
 * 
 * This controller handles all application-related functionality
 * including student applications, applicant management, and
 * application processing for both SFAO and Central roles.
 * 
 * Combined functionality from:
 * - ApplicationController
 * - ApplicantsController  
 * - StudentController (application methods)
 * - SFAOController (application management)
 * - CentralController (application management)
 */
class ApplicationController extends Controller
{
    /**
     * Helper method to handle application status updates for both SFAO and Central roles.
     */
    private function updateApplicationStatus($id, $status, $role)
    {
        if (!session()->has('user_id') || session('role') !== $role) {
            return redirect('/login')->with('session_expired', true);
        }

        $application = Application::findOrFail($id);
        $application->status = $status;
        $application->save();

        // Create notification for student
        NotificationService::notifyApplicationStatusChange($application, $status);

        return back()->with('success', "Application " . ($status === 'approved' ? 'approved' : 'rejected') . " successfully.");
    }

    /**
     * Show student's applications
     */
    public function studentApplications()
    {
        if (!session()->has('user_id') || session('role') !== 'student') {
            return redirect('/login')->with('session_expired', true);
        }

        $user = User::find(session('user_id'));
        if (!$user) {
            return redirect('/login')->with('error', 'User not found.');
        }

        $applications = $user->appliedScholarships;

        return view('student.applications.index', compact('applications'));
    }

    /**
     * Apply for a scholarship (Student)
     */
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
            // Create new application
            Application::create([
                'user_id'        => session('user_id'),
                'scholarship_id' => $request->scholarship_id,
                'status'         => 'pending',
            ]);
            
            $message = 'You have successfully applied for the scholarship.';
                
            return back()->with('success', $message);
        }
    }

    /**
     * Withdraw from a scholarship (Student)
     */
    public function withdraw(Request $request)
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

        // Find all submitted documents for this user and scholarship
        $submittedDocuments = StudentSubmittedDocument::where('user_id', $userId)
            ->where('scholarship_id', $scholarshipId)
            ->get();

        // Delete files from storage and then delete the database records
        foreach ($submittedDocuments as $document) {
            if (!empty($document->file_path) && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            $document->delete();
        }

        return back()->with('success', 'You have successfully withdrawn, and your documents were removed.');
    }

    // =====================================================
    // APPLICANT MANAGEMENT METHODS
    // =====================================================

    /**
     * View all applicants (Central Admin) - Only SFAO-approved applications
     */
    public function viewApplicants()
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        // Retrieve only SFAO-approved applications with related user and scholarship info
        $applications = Application::with(['user', 'scholarship'])
            ->where('status', 'approved') // Only show SFAO-approved applications
            ->orderBy('created_at', 'desc')
            ->get();

        return view('central.partials.tabs.applicants', compact('applications'));
    }

    /**
     * SFAO Applicants List (AJAX)
     * Handles fetching and filtering of applicant data for SFAO Dashboard
     */
    public function sfaoApplicantsList(Request $request)
    {
        // 1. Authorization
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // 2. Setup Context
        $user = User::with('campus')->find(session('user_id'));
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $campusIds = $user->campus->getAllCampusesUnder()->pluck('id');

        $activeTab = str_replace('_', '-', strtolower($request->get('tab', 'applicants')));
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $campusFilter = $request->get('campus_filter', 'all');
        $statusFilter = str_replace('-', '_', strtolower($request->get('status_filter', 'all')));
        $isModalRequest = $request->boolean('fetch_modal_data');

        // 3. Base Query
        $query = User::where('role', 'student')
            ->whereIn('campus_id', $campusIds)
            ->with(['applications.scholarship', 'form', 'campus']);

        // 4. Apply Filters
        if ($campusFilter !== 'all') {
            $query->where('campus_id', $campusFilter);
        }

        $scholarshipFilter = $request->get('scholarship_filter', 'all');
        if ($scholarshipFilter !== 'all') {
             $query->whereHas('applications', function($q) use ($scholarshipFilter) {
                 $q->where('scholarship_id', $scholarshipFilter);
             });
        }

        $collegeFilter = $request->get('college_filter', 'all');
        if ($collegeFilter !== 'all') {
             $variations = explode('|', $collegeFilter);
             $query->whereIn('college', $variations);
        }

        $programFilter = $request->get('program_filter', 'all');
        if ($programFilter !== 'all') {
             $query->where('program', $programFilter);
        }

        $trackFilter = $request->get('track_filter', 'all');
        if ($trackFilter !== 'all') {
             $query->where('track', $trackFilter);
        }

        $academicYearFilter = $request->get('academic_year_filter', 'all');
        if ($academicYearFilter !== 'all') {
             $parts = explode('-', $academicYearFilter);
             if (count($parts) === 2) {
                 $startDate = $parts[0] . "-08-01";
                 $endDate = $parts[1] . "-07-31";
                 $query->whereHas('applications', function($q) use ($startDate, $endDate) {
                     $q->whereBetween('created_at', [$startDate, $endDate]);
                 });
             }
        }

        // 5. Status/Tab Filter
        $countsQuery = clone $query;
        $effectiveStatus = 'all';

        if (str_starts_with($activeTab, 'applicants-')) {
            $effectiveStatus = str_replace('applicants-', '', $activeTab);
        }

        if ($effectiveStatus === 'all' && $isModalRequest && $statusFilter !== 'all') {
            $effectiveStatus = $statusFilter;
        }

        if ($effectiveStatus === 'not_applied') {
            $query->doesntHave('applications');
        } elseif ($effectiveStatus === 'all') {
            // No status filter - include all students regardless of application presence
        } elseif (in_array($effectiveStatus, ['in_progress', 'pending', 'approved', 'rejected'])) {
            $query->whereHas('applications', function($q) use ($effectiveStatus) {
                $q->where('status', $effectiveStatus);
            });
        }

        $resolveDisplayStatus = function($applications) {
            $statuses = $applications->pluck('status')->filter()->values()->all();

            if (empty($statuses)) {
                return 'not_applied';
            }

            foreach (['approved', 'in_progress', 'pending', 'rejected'] as $status) {
                if (in_array($status, $statuses, true)) {
                    return $status;
                }
            }

            return $statuses[0];
        };

        // 6. Aggregates & Sorting (Optimized)
        $query->leftJoin('student_submitted_documents', function($join) {
            $join->on('users.id', '=', 'student_submitted_documents.user_id')
                 ->where('student_submitted_documents.document_category', '=', 'sfao_required');
        });

        $query->select(
            'users.id as student_id',
            'users.name',
            'users.email',
            'users.created_at',
            'users.campus_id',
            'users.sex',
            'users.contact_number',
            'users.birthdate',
            'users.sr_code',
            'users.program',
            'users.year_level',
            'users.college',
            'users.profile_picture',
            DB::raw('MAX(student_submitted_documents.updated_at) as last_uploaded'),
            DB::raw('COUNT(DISTINCT student_submitted_documents.id) as documents_count')
        )
        ->groupBy('users.id');

        // Database-level sorting
        $orderCol = match($sortBy) {
            'email' => 'users.email',
            'date_joined' => 'users.created_at',
            'last_uploaded' => 'last_uploaded',
            'documents_count' => 'documents_count',
            default => 'users.name',
        };
        $query->orderBy($orderCol, $sortOrder);

        // 7. Paginate at Database Level
        $perPage = 10;
        $page = $request->get('page_applicants', 1);
        $paginatedStudents = $query->paginate($perPage, ['*'], 'page_applicants');

        // 8. Post-Processing
        $studentCollection = collect($paginatedStudents->items());
        $studentIds = $studentCollection->pluck('student_id');
        
        $applicationsData = Application::with('scholarship')
            ->whereIn('user_id', $studentIds)
            ->get()
            ->groupBy('user_id');

        $documentsData = StudentSubmittedDocument::whereIn('user_id', $studentIds)
            ->get()
            ->groupBy('user_id');

        $studentCollection = $studentCollection->map(function($student) use ($applicationsData, $documentsData, $resolveDisplayStatus, $effectiveStatus) {
            $studentApplications = $applicationsData->get($student->student_id, collect());
            $studentDocuments = $documentsData->get($student->student_id, collect());
            $bucketStatus = $effectiveStatus !== 'all' ? $effectiveStatus : $resolveDisplayStatus($studentApplications);
            
            $student->applications = $studentApplications;
            $student->has_applications = $studentApplications->count() > 0;
            $student->has_documents = $student->documents_count > 0;
            $student->application_status = $studentApplications->pluck('status')->unique()->toArray();
            $student->display_status = $bucketStatus;
            
            $student->applications_with_types = $studentApplications->map(function($app) {
                return [
                    'id' => $app->id,
                    'scholarship_name' => $app->scholarship->scholarship_name ?? 'Unknown',
                    'status' => $app->status,
                    'grant_count' => $app->grant_count,
                    'grant_count_display' => method_exists($app, 'getGrantCountDisplay') ? $app->getGrantCountDisplay() : $app->grant_count,
                    'grant_count_badge_color' => method_exists($app, 'getGrantCountBadgeColor') ? $app->getGrantCountBadgeColor() : 'gray'
                ];
            });
            
            return $student;
        });

        $paginatedStudents = new LengthAwarePaginator(
            $studentCollection,
            $paginatedStudents->total(),
            $paginatedStudents->perPage(),
            $paginatedStudents->currentPage(),
            [
                'path' => $request->url(),
                'pageName' => 'page_applicants'
            ]
        );

        $paginatedStudents->appends($request->query());

        // 9. Counts
        $counts = [
            // 'total' now reflects all students under this SFAO admin (not only students with applications)
            'total' => (clone $countsQuery)->count(),
            'not_applied' => (clone $countsQuery)->doesntHave('applications')->count(),
            'in_progress' => (clone $countsQuery)->whereHas('applications', fn($q) => $q->where('status', 'in_progress'))->count(),
            'pending' => (clone $countsQuery)->whereHas('applications', fn($q) => $q->where('status', 'pending'))->count(),
            'approved' => (clone $countsQuery)->whereHas('applications', fn($q) => $q->where('status', 'approved'))->count(),
            'rejected' => (clone $countsQuery)->whereHas('applications', fn($q) => $q->where('status', 'rejected'))->count(),
        ];

        if ($request->get('fetch_modal_data')) {
            $applicants = $studentCollection->map(function($student) {
                return [
                    'id' => $student->student_id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'application_id' => $student->applications->first()?->id,
                    'status' => $student->display_status ?? $student->applications->first()?->status,
                ];
            })->values();
            
            return response()->json(['applicants' => $applicants, 'counts' => $counts]);
        }

        return response()->json([
            'html' => view('sfao.applicants.list', ['students' => $paginatedStudents])->render(),
            'counts' => $counts
        ]);
    }

    /**
     * SFAO Dashboard - Only shows applicants (students with applications), not scholars
     */
    public function sfaoDashboard(Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $user = User::with('campus')->find(session('user_id'));
        if (!$user) {
            return redirect('/login')->with('error', 'User not found.');
        }
        
        // Get the SFAO admin's campus and all campuses under it
        $sfaoCampus = $user->campus;
        if (!$sfaoCampus) {
             return redirect('/login')->with('error', 'User campus not assigned.');
        }
        $monitoredCampuses = $sfaoCampus->getAllCampusesUnder();
        $campusIds = $monitoredCampuses->pluck('id');

        // Get sorting parameters
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $campusFilter = $request->get('campus_filter', 'all');
        $statusFilter = $request->get('status_filter', 'all');
        
        // Get active tab to determine status filter if tab-based filtering is used
        $activeTab = $request->get('tab', 'scholarships');
        
        // If tab is a status-specific applicants tab, override status filter
        // Note: 'approved' tab shows applicants with approved documents, not application status
        if (str_starts_with($activeTab, 'applicants-')) {
            $statusFromTab = str_replace('applicants-', '', $activeTab);
            if (in_array($statusFromTab, ['not_applied', 'in_progress', 'pending', 'rejected'])) {
                $statusFilter = $statusFromTab;
            }
            // 'approved' tab is handled separately via has_approved_documents property
        }

        // Build the query - SFAO sees all students in their domain
        // Exclude students who are already scholars (they will be shown in Scholars tab)
        // Build the query - SFAO sees all students in their domain
        $query = User::where('role', 'student')
            ->whereIn('campus_id', $campusIds)
            // Removed: whereDoesntHave('scholars') to allow existing scholars to appear if they have new applications
            ->with(['applications.scholarship', 'form', 'campus'])
            ->leftJoin('student_submitted_documents', function($join) {
                $join->on('users.id', '=', 'student_submitted_documents.user_id')
                     ->where('student_submitted_documents.document_category', '=', 'sfao_required');
            });

        // Apply campus filter
        if ($campusFilter !== 'all') {
            $query->where('users.campus_id', $campusFilter);
        }

        // Clone query for tabs
        // Clone query for tabs
        $queryAll = (clone $query)->whereHas('applications'); // Exclude students who haven't applied

        $queryNotApplied = clone $query;
        $queryInProgress = clone $query;
        $queryPending = clone $query;
        $queryApproved = clone $query;
        $queryRejected = clone $query;

        // Apply filters to clones
        $queryNotApplied->doesntHave('applications');
        
        $queryInProgress->whereHas('applications', function($q) {
             $q->where('status', 'in_progress');
        });

        $queryPending->whereHas('applications', function($q) {
             $q->where('status', 'pending');
        });

        $queryRejected->whereHas('applications', function($q) {
             $q->where('status', 'rejected');
        });

        $queryApproved->whereHas('applications', function($q) {
             $q->where('status', 'approved');
        });

        // Helper to process (fetch, sort, paginate)
        $processStudents = function($query, $pageName) use ($sortBy, $sortOrder, $request) {
            $students = $query->select(
                'users.id as student_id',
                'users.name',
                'users.email',
                'users.created_at',
                'users.campus_id',
                DB::raw('MAX(student_submitted_documents.updated_at) as last_uploaded'),
                DB::raw('COUNT(DISTINCT student_submitted_documents.id) as documents_count')
            )
            ->groupBy('users.id', 'users.name', 'users.email', 'users.created_at', 'users.campus_id')
            ->get();

            $students = $students->sortBy(function($student) use ($sortBy) {
                switch ($sortBy) {
                    case 'name': return $student->name;
                    case 'email': return $student->email;
                    case 'date_joined': return $student->created_at;
                    case 'last_uploaded': return $student->last_uploaded;
                    case 'documents_count': return $student->documents_count;
                    default: return $student->name;
                }
            });

            if ($sortOrder === 'desc') {
                $students = $students->reverse();
            }

            $perPage = 10;
            // Use generic page param for AJAX, specific for normal load
            $page = $request->ajax() ? $request->get('page_applicants', 1) : $request->get($pageName, 1);
            
            return new LengthAwarePaginator(
                $students->forPage($page, $perPage),
                $students->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query(), 'pageName' => $request->ajax() ? 'page_applicants' : $pageName]
            );
        };

        $studentsAll = $processStudents($queryAll, 'page_applicants');
        $studentsNotApplied = $processStudents($queryNotApplied, 'page_applicants_not_applied');
        $studentsInProgress = $processStudents($queryInProgress, 'page_applicants_in_progress');
        $studentsPending = $processStudents($queryPending, 'page_applicants_pending');
        $studentsApproved = $processStudents($queryApproved, 'page_applicants_approved');
        $studentsRejected = $processStudents($queryRejected, 'page_applicants_rejected');

        // Collect IDs for data loading from ALL collections
        $allCollections = [$studentsAll, $studentsNotApplied, $studentsInProgress, $studentsPending, $studentsApproved, $studentsRejected];
        $studentIds = collect();
        foreach ($allCollections as $c) $studentIds = $studentIds->merge($c->getCollection()->pluck('student_id'));
        $studentIds = $studentIds->unique();

        // Load applications for each student separately to ensure relationships are loaded
        $applicationsData = Application::with('scholarship')
            ->whereIn('user_id', $studentIds)
            ->get()
            ->groupBy('user_id');

        // Load documents for each student to check for approved documents
        $documentsData = StudentSubmittedDocument::whereIn('user_id', $studentIds)
            ->get()
            ->groupBy('user_id');

        // Add application status information to each student in ALL collections
        foreach ($allCollections as $collection) {
            $collection->each(function($student) use ($applicationsData, $documentsData) {
                $studentApplications = $applicationsData->get($student->student_id, collect());
                $studentDocuments = $documentsData->get($student->student_id, collect());
                
                $student->applications = $studentApplications;
                $student->has_applications = $studentApplications->count() > 0;
                $student->has_documents = $student->documents_count > 0;
                $student->application_status = $studentApplications->pluck('status')->unique()->toArray();
                $student->applied_scholarships = $studentApplications->pluck('scholarship.scholarship_name')->toArray();
                
                // Check if student has approved documents
                $student->has_approved_documents = $studentDocuments->where('evaluation_status', 'approved')->count() > 0;
                
                $student->applications_with_types = $studentApplications->map(function($app) {
                    return [
                        'id' => $app->id,
                        'scholarship_name' => $app->scholarship->scholarship_name,
                        'status' => $app->status,
                        'grant_count' => $app->grant_count,
                        'grant_count_display' => $app->getGrantCountDisplay(),
                        'grant_count_badge_color' => $app->getGrantCountBadgeColor()
                    ];
                });
            });
        }
        
        // For backward compatibility with view, use All as default
        $students = $studentsAll;

        // Get applications only from students under this SFAO admin's jurisdiction
        $applications = Application::with('user', 'scholarship')
            ->whereHas('user', function($query) use ($campusIds) {
                $query->whereIn('campus_id', $campusIds);
            })
            ->get();
            
        // Get scholarship type filter from tab parameter
        $scholarshipTypeFilter = $request->get('tab', 'scholarships');
        
        // Build scholarships query
        $scholarshipsQuery = Scholarship::withCount(['applications' => function($query) use ($campusIds) {
            $query->whereHas('user', function($userQuery) use ($campusIds) {
                $userQuery->whereIn('campus_id', $campusIds);
            });
        }]);
        
        // Clone query for different tabs
        $queryAll = clone $scholarshipsQuery;
        $queryPrivate = clone $scholarshipsQuery;
        $queryGov = clone $scholarshipsQuery;
        
        // Apply filters
        $queryPrivate->where('scholarship_type', 'private');
        $queryGov->where('scholarship_type', 'government');
        
        // Apply sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        
        $scholarshipsAll = $this->sortScholarships($queryAll->get(), $sortBy, $sortOrder);
        $scholarshipsPrivate = $this->sortScholarships($queryPrivate->get(), $sortBy, $sortOrder);
        $scholarshipsGov = $this->sortScholarships($queryGov->get(), $sortBy, $sortOrder);

        // Pagination Logic for Scholarships
        $perPage = 5;

        // All Scholarships Paginator
        $pageAll = $request->get('page_all', 1);
        $scholarshipsAll = new LengthAwarePaginator(
            $scholarshipsAll->forPage($pageAll, $perPage),
            $scholarshipsAll->count(),
            $perPage,
            $pageAll,
            ['path' => $request->url(), 'query' => $request->query(), 'pageName' => 'page_all']
        );

        // Private Scholarships Paginator
        $pagePrivate = $request->get('page_private', 1);
        $scholarshipsPrivate = new LengthAwarePaginator(
            $scholarshipsPrivate->forPage($pagePrivate, $perPage),
            $scholarshipsPrivate->count(),
            $perPage,
            $pagePrivate,
            ['path' => $request->url(), 'query' => $request->query(), 'pageName' => 'page_private']
        );

        // Government Scholarships Paginator
        $pageGov = $request->get('page_gov', 1);
        $scholarshipsGov = new LengthAwarePaginator(
            $scholarshipsGov->forPage($pageGov, $perPage),
            $scholarshipsGov->count(),
            $perPage,
            $pageGov,
            ['path' => $request->url(), 'query' => $request->query(), 'pageName' => 'page_gov']
        );

        // Get campus options for filtering
        $campusOptions = collect([['id' => 'all', 'name' => 'All Campuses']])
            ->merge($sfaoCampus->getAllCampusesUnder()->map(function($campus) {
                return ['id' => $campus->id, 'name' => $campus->name];
            }));

        // Get reports for the SFAO admin with filtering
        $reportsQuery = \App\Models\Report::where('sfao_user_id', session('user_id'))
            ->with(['campus', 'reviewer']);

        // Apply status filter
        if ($request->has('status') && $request->status !== 'all') {
            $reportsQuery->where('status', $request->status);
        }

        // Apply type filter
        if ($request->has('type') && $request->type !== 'all') {
            $reportsQuery->where('report_type', $request->type);
        }

        $reports = $reportsQuery->orderBy('created_at', 'desc')->paginate(10);

        // Get scholars data for the scholars tab (students who have scholar records)
        $scholarsQuery = Scholar::with(['user', 'scholarship', 'user.campus'])
            ->whereHas('user', function($query) use ($campusIds) {
                $query->whereIn('campus_id', $campusIds);
            });

        // Apply campus filter for scholars
        if ($campusFilter !== 'all') {
            $scholarsQuery->whereHas('user', function($query) use ($campusFilter) {
                $query->where('campus_id', $campusFilter);
            });
        }

        // Get counts BEFORE applying status/type filters for the list
        // We need to clone the query because get() or count() might modify it or we need to reuse it
        $countQuery = clone $scholarsQuery;
        $scholarsTotalCount = $countQuery->count();
        $scholarsActiveCount = (clone $countQuery)->where('status', 'active')->count();
        $scholarsNewCount = (clone $countQuery)->where('type', 'new')->count();
        $scholarsOldCount = (clone $countQuery)->where('type', 'old')->count();

        // Apply status filter for scholars
        if ($statusFilter !== 'all' && $statusFilter !== 'not_applied' && !str_starts_with($activeTab, 'scholars-')) {
            $scholarsQuery->where('status', $statusFilter);
        }

        // Apply type filter (from request param or tab)
        $typeFilter = $request->get('type_filter', 'all');
        if ($typeFilter !== 'all') {
             $scholarsQuery->where('type', $typeFilter);
        } elseif (str_starts_with($activeTab, 'scholars-')) {
            // Fallback to tab-based filtering if no explicit type filter
            $typeFromTab = str_replace('scholars-', '', $activeTab);
            if (in_array($typeFromTab, ['new', 'old'])) {
                $scholarsQuery->where('type', $typeFromTab);
            }
        }

        // Apply sorting for scholars
        $scholarsSortBy = $request->get('scholars_sort_by', 'name');
        $scholarsSortOrder = $request->get('scholars_sort_order', 'asc');
        
        switch ($scholarsSortBy) {
            case 'name':
                $scholarsQuery->leftJoin('users as sort_users', 'scholars.user_id', '=', 'sort_users.id')
                    ->orderBy('sort_users.name', $scholarsSortOrder)
                    ->select('scholars.*');
                break;
            case 'email':
                $scholarsQuery->leftJoin('users as sort_users', 'scholars.user_id', '=', 'sort_users.id')
                    ->orderBy('sort_users.email', $scholarsSortOrder)
                    ->select('scholars.*');
                break;
            case 'scholarship':
                $scholarsQuery->leftJoin('scholarships as sort_scholarships', 'scholars.scholarship_id', '=', 'sort_scholarships.id')
                    ->orderBy('sort_scholarships.scholarship_name', $scholarsSortOrder)
                    ->select('scholars.*');
                break;
            case 'status':
                $scholarsQuery->orderBy('scholars.status', $scholarsSortOrder);
                break;
            case 'type':
                $scholarsQuery->orderBy('scholars.type', $scholarsSortOrder);
                break;
            default:
                $scholarsQuery->orderBy('scholars.created_at', $scholarsSortOrder);
        }

        $scholars = $scholarsQuery->get();

        // Use the activeTab we already determined earlier (or get from request if not set)
        if (!isset($activeTab)) {
            $activeTab = session('active_tab', $request->get('tab', 'scholarships'));
        }

        // Debug: Log the filtering parameters
        \Illuminate\Support\Facades\Log::info('SFAO Dashboard Filtering', [
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
            'campus_filter' => $campusFilter,
            'status_filter' => $statusFilter,
            'students_count' => $students->count(),
            'scholars_count' => $scholars->count(),
            'active_tab' => $activeTab
        ]);

        // Calculate Analytics Data for SFAO Dashboard
        $analytics = [];
        
        // 1. Basic Counts
        $countQuery = User::where('role', 'student')
            ->whereIn('campus_id', $campusIds);

        if ($campusFilter !== 'all') {
            $countQuery->where('campus_id', $campusFilter);
        }

        $totalStudents = (clone $countQuery)->count();
            
        $studentsWithApplications = Application::whereHas('user', function($query) use ($campusIds, $campusFilter) {
                $query->whereIn('campus_id', $campusIds);
                if ($campusFilter !== 'all') {
                    $query->where('campus_id', $campusFilter);
                }
            })
            ->distinct('user_id')
            ->count('user_id');
            
        $pendingApplications = Application::whereHas('user', function($query) use ($campusIds, $campusFilter) {
                $query->whereIn('campus_id', $campusIds);
                if ($campusFilter !== 'all') {
                    $query->where('campus_id', $campusFilter);
                }
            })
            ->where('status', 'pending')
            ->count();
            
        $approvedApplications = Application::whereHas('user', function($query) use ($campusIds, $campusFilter) {
                $query->whereIn('campus_id', $campusIds);
                if ($campusFilter !== 'all') {
                    $query->where('campus_id', $campusFilter);
                }
            })
            ->where('status', 'approved')
            ->count();
            
        $rejectedApplications = Application::whereHas('user', function($query) use ($campusIds, $campusFilter) {
                $query->whereIn('campus_id', $campusIds);
                if ($campusFilter !== 'all') {
                    $query->where('campus_id', $campusFilter);
                }
            })
            ->where('status', 'rejected')
            ->count();

        $analytics['total_students'] = $totalStudents;
        $analytics['students_with_applications'] = $studentsWithApplications;
        $analytics['pending_applications'] = $pendingApplications;
        $analytics['approved_applications'] = $approvedApplications;
        $analytics['rejected_applications'] = $rejectedApplications;
        $analytics['approval_rate'] = $studentsWithApplications > 0 ? round(($approvedApplications / $studentsWithApplications) * 100, 1) : 0;

        // 2. Department Statistics
        // Get all departments
        $allDepartments = \App\Models\Department::all();
        $analytics['all_departments'] = $allDepartments;
        
        // Map campuses to departments
        $campusDepartments = [];
        foreach ($sfaoCampus->getAllCampusesUnder() as $camp) {
            $campusDepartments[$camp->id] = $camp->departments->pluck('short_name')->toArray();
        }
        $analytics['campus_departments'] = $campusDepartments;

        // Calculate stats per department
        $departmentStats = [];
        foreach ($allDepartments as $dept) {
            // Count students in this department (assuming users.college stores short_name)
            $deptStudentsCount = User::where('role', 'student')
                ->whereIn('campus_id', $campusIds)
                ->where('college', $dept->short_name)
                ->count();
                
            // Count applications for students in this department
            $deptApplicationsCount = Application::whereHas('user', function($query) use ($campusIds, $dept) {
                    $query->whereIn('campus_id', $campusIds)
                          ->where('college', $dept->short_name);
                })
                ->count();
                
            $deptApprovedCount = Application::whereHas('user', function($query) use ($campusIds, $dept) {
                    $query->whereIn('campus_id', $campusIds)
                          ->where('college', $dept->short_name);
                })
                ->where('status', 'approved')
                ->count();

            $deptPendingCount = Application::whereHas('user', function($query) use ($campusIds, $dept) {
                    $query->whereIn('campus_id', $campusIds)
                          ->where('college', $dept->short_name);
                })
                ->where('status', 'pending')
                ->count();

            $deptRejectedCount = Application::whereHas('user', function($query) use ($campusIds, $dept) {
                    $query->whereIn('campus_id', $campusIds)
                          ->where('college', $dept->short_name);
                })
                ->where('status', 'rejected')
                ->count();

            if ($deptStudentsCount > 0 || $deptApplicationsCount > 0) {
                $departmentStats[] = [
                    'name' => $dept->short_name,
                    'full_name' => $dept->name,
                    'total_students' => $deptStudentsCount,
                    'total_applications' => $deptApplicationsCount,
                    'approved_applications' => $deptApprovedCount,
                    'pending_applications' => $deptPendingCount,
                    'rejected_applications' => $deptRejectedCount,
                    'approval_rate' => $deptApplicationsCount > 0 ? round(($deptApprovedCount / $deptApplicationsCount) * 100, 1) : 0
                ];
            }
        }
        $analytics['department_stats'] = $departmentStats;

        // 3. All Students Data for Client-side Filtering (Gender Chart)
        $allStudentsData = User::where('role', 'student')
            ->whereIn('campus_id', $campusIds)
            ->select('campus_id', 'college', 'sex')
            ->get();
            
        $analytics['all_students_data'] = $allStudentsData;

        // 4. All Applications Data for Client-side Filtering (Scholarship Type Chart & Stacked Bar)
        $allApplicationsData = Application::join('users', 'applications.user_id', '=', 'users.id')
            ->join('scholarships', 'applications.scholarship_id', '=', 'scholarships.id')
            ->whereIn('users.campus_id', $campusIds)
            ->select('users.campus_id', 'users.college', 'scholarships.scholarship_type', 'scholarships.scholarship_name as scholarship_name', 'applications.status', 'applications.created_at')
            ->get();
            
        $analytics['all_applications_data'] = $allApplicationsData;

        // Handle AJAX Requests
        if ($request->ajax()) {
            if ($activeTab === 'applicants') {
                // Determine which list to return based on status_filter
                $studentsList = $studentsAll; // Default
                
                switch ($statusFilter) {
                    case 'not_applied': $studentsList = $studentsNotApplied; break;
                    case 'in_progress': $studentsList = $studentsInProgress; break;
                    case 'pending': $studentsList = $studentsPending; break;
                    case 'approved': $studentsList = $studentsApproved; break;
                    case 'rejected': $studentsList = $studentsRejected; break;
                }

                return response()->json([
                    'html' => view('sfao.partials.tabs.applicants_list', ['students' => $studentsList])->render(),
                    'counts' => [
                        'total' => $studentsAll->total(),
                        'pending' => $studentsPending->total(),
                        'in_progress' => $studentsInProgress->total(),
                        'rejected' => $studentsRejected->total(),
                        'not_applied' => $studentsNotApplied->total(),
                        'approved' => $studentsApproved->total()
                    ]
                ]);
            } elseif ($activeTab === 'scholars') {
                return response()->json([
                    'html' => view('sfao.partials.tabs.scholars_list', compact('scholars'))->render(),
                    'counts' => [
                        'total' => $scholarsTotalCount,
                        'active' => $scholarsActiveCount,
                        'new' => $scholarsNewCount,
                        'old' => $scholarsOldCount
                    ]
                ]);
            } elseif ($activeTab === 'scholarships') {
                $filteredScholarships = $scholarshipsAll;
                
                $typeFilter = $request->get('type_filter', 'all');
                if ($typeFilter !== 'all') {
                    $filteredScholarships = $filteredScholarships->filter(function($s) use ($typeFilter) {
                         return $s->scholarship_type === $typeFilter;
                    });
                }
                
                // Pagination for AJAX
                $page = $request->get('page_scholarships', 1);
                $perPage = 5;
                $paginatedScholarships = new LengthAwarePaginator(
                    $filteredScholarships->forPage($page, $perPage),
                    $filteredScholarships->count(),
                    $perPage,
                    $page,
                    ['path' => $request->url(), 'query' => $request->query(), 'pageName' => 'page_scholarships']
                );
                
                $paginatedScholarships->getCollection()->each(function($scholarship) {
                    if($scholarship->slots_available && $scholarship->slots_available > 0) {
                        $scholarship->fill_percentage = min((($scholarship->scholars_count ?? 0) / $scholarship->slots_available) * 100, 100);
                    } else {
                        $scholarship->fill_percentage = 0;
                    }
                });

                return response()->json([
                    'html' => view('sfao.partials.tabs.scholarships_list', ['scholarships' => $paginatedScholarships])->render()
                ]);
            }
        }




        // Fetch Filter Options for Applicants Tab
        $rawColleges = User::where('role', 'student')
            ->whereIn('campus_id', $campusIds)
            ->whereNotNull('college')
            ->distinct()
            ->pluck('college');

        $mergedColleges = [];
        foreach ($rawColleges as $c) {
            $label = $c;
            if ($c === 'College of Accountancy, Business, Economics, International Hospitality Management' || 
                $c === 'CABE' || 
                $c === 'CABEIHM') {
                $label = 'CABEIHM';
            }
            if (!isset($mergedColleges[$label])) {
                $mergedColleges[$label] = [];
            }
            $mergedColleges[$label][] = $c;
        }

        $colleges = collect($mergedColleges)->map(function($values, $label) {
            return ['name' => $label, 'value' => implode('|', array_unique($values))];
        })->sortBy('name')->values();

        $filterOptions = User::where('role', 'student')
            ->whereIn('campus_id', $campusIds)
            ->select('program', 'track')
            ->distinct()
            ->get();
            
        $programs = $filterOptions->pluck('program')->filter()->unique()->values();
        $tracks = $filterOptions->pluck('track')->filter()->unique()->values();
        
        // Academic Years (from Applications)
        $yearExpression = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql'
            ? 'EXTRACT(YEAR FROM created_at)::integer'
            : 'YEAR(created_at)';
        $monthExpression = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql'
            ? 'EXTRACT(MONTH FROM created_at)::integer'
            : 'MONTH(created_at)';
        $academicYears = Application::whereHas('user', function($q) use ($campusIds) {
                $q->whereIn('campus_id', $campusIds);
            })
            ->selectRaw("{$yearExpression} as year, {$monthExpression} as month")
            ->distinct()
            ->get()
            ->map(function($app) {
                // Assumption: AY starts in August
                $startYear = $app->month >= 8 ? $app->year : $app->year - 1;
                return $startYear . '-' . ($startYear + 1);
            })
            ->unique()
            ->sortDesc()
            ->values();

        return view('sfao.dashboard', compact(
            'user', 
            'students', 
            'studentsAll',
            'studentsNotApplied',
            'studentsInProgress',
            'studentsPending',
            'studentsApproved',
            'studentsRejected',
            'applications', 
            'scholarshipsAll', 
            'scholarshipsPrivate', 
            'scholarshipsGov', 
            'sfaoCampus', 
            'monitoredCampuses',
            'campusOptions', 
            'sortBy', 
            'sortOrder', 
            'campusFilter', 
            'statusFilter', 
            'reports', 
            'activeTab', 
            'scholars', 
            'scholarsSortBy', 
            'scholarsSortOrder', 
            'analytics',
            'colleges',
            'programs',
            'tracks',
            'academicYears'
        ));
    }

    /**
     * View applicants for SFAO (Campus-specific)
     */
    public function sfaoApplicants()
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $user = User::with('campus')->find(session('user_id'));
        $sfaoCampus = $user->campus;
        $campusIds = $sfaoCampus->getAllCampusesUnder()->pluck('id');

        // Get students who have uploaded at least one document, only from this SFAO admin's jurisdiction
        $students = DB::table('student_submitted_documents')
            ->join('users', 'student_submitted_documents.user_id', '=', 'users.id')
            ->whereIn('users.campus_id', $campusIds)
            ->where('student_submitted_documents.document_category', 'sfao_required')
            ->select(
                'users.id as student_id',
                'users.name',
                'users.email',
                'users.campus_id',
                DB::raw('MAX(student_submitted_documents.updated_at) as last_uploaded')
            )
            ->groupBy('users.id', 'users.name', 'users.email', 'users.campus_id')
            ->get();

        return view('sfao.partials.tabs.applicants', compact(
            'students', 
            'studentsAll',
            'studentsNotApplied',
            'studentsInProgress',
            'studentsPending',
            'studentsApproved',
            'studentsRejected',
            'sfaoCampus'
        ));
    }

    /**
     * View student documents (SFAO)
     */
    public function viewDocuments($user_id)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $student = User::findOrFail($user_id);
        $documents = StudentSubmittedDocument::where('user_id', $user_id)
            ->where('document_category', 'sfao_required')
            ->get();

        return view('sfao.applicants.view-documents', compact('student', 'documents'));
    }

    // =====================================================
    // APPLICATION PROCESSING METHODS
    // =====================================================

    /**
     * Approve application (SFAO)
     */
    public function sfaoApproveApplication($id)
    {
        return $this->updateApplicationStatus($id, 'approved', 'sfao');
    }

    /**
     * Reject application (SFAO)
     */
    public function sfaoRejectApplication($id)
    {
        return $this->updateApplicationStatus($id, 'rejected', 'sfao');
    }

    /**
     * Mark application as claimed (SFAO)
     */
    public function sfaoClaimGrant($id)
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

    /**
     * Approve application (Central)
     */
    public function centralApproveApplication($id)
    {
        return $this->updateApplicationStatus($id, 'approved', 'central');
    }

    /**
     * Reject application (Central)
     */
    public function centralRejectApplication($id)
    {
        return $this->updateApplicationStatus($id, 'rejected', 'central');
    }

    /**
     * Mark application as claimed (Central)
     */
    public function centralClaimGrant($id)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
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

    // =====================================================
    // APPLICATION TRACKING METHODS
    // =====================================================

    /**
     * Get application tracking data for student
     */
    public function getApplicationTracking($userId)
    {
        return Application::where('user_id', $userId)
            ->with('scholarship')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get applications for SFAO dashboard
     */
    public function getSfaoApplications($campusIds)
    {
        return Application::with('user', 'scholarship')
            ->whereHas('user', function($query) use ($campusIds) {
                $query->whereIn('campus_id', $campusIds);
            })
            ->get();
    }

    /**
     * Central Dashboard - Only shows scholars (selected students), not applicants
     */
    public function centralDashboard(Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        // Create user object
        $user = User::find(session('user_id'));

        // Get all campuses for filter and resolving tab
        $campuses = Campus::all();

        // Get filtering parameters
        $tab = $request->get('tabs', $request->get('tab', 'all_scholarships'));
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $statusFilter = $request->get('status_filter', 'all');
        
        // Resolve campus filter
        // Check both 'campus_filter' (Applications/Scholars) and 'campus' (Statistics)
        $campusFilter = $request->get('campus_filter', $request->get('campus', 'all'));
        
        // Override campus filter if tab implies a specific campus statistics page
        if (str_ends_with($tab, '_statistics') && $tab !== 'all_statistics') {
            $campusSlug = str_replace('_statistics', '', $tab);
            foreach ($campuses as $campus) {
                if (strtolower(str_replace(' ', '_', $campus->name)) === $campusSlug) {
                    $campusFilter = $campus->id;
                    break;
                }
            }
        }

        $scholarshipFilter = $request->get('scholarship_filter', 'all');

        // Build applications query with filtering
        $applicationsQuery = Application::with(['user', 'scholarship', 'user.campus'])
            ->whereHas('user', function($query) {
                $query->where('role', 'student');
            });

        // Apply status filter
        if ($statusFilter !== 'all') {
            $applicationsQuery->where('status', $statusFilter);
        }
        // If 'all' is selected, don't apply any status filter to show all statuses

        // Apply campus filter
        if ($campusFilter !== 'all') {
            $applicationsQuery->whereHas('user', function($query) use ($campusFilter) {
                $query->where('campus_id', $campusFilter);
            });
        }

        // Apply scholarship filter
        if ($scholarshipFilter !== 'all') {
            $applicationsQuery->where('scholarship_id', $scholarshipFilter);
        }


        // Apply sorting
        switch ($sortBy) {
            case 'name':
                $applicationsQuery->join('users', 'applications.user_id', '=', 'users.id')
                    ->orderBy('users.name', $sortOrder);
                break;
            case 'email':
                $applicationsQuery->join('users', 'applications.user_id', '=', 'users.id')
                    ->orderBy('users.email', $sortOrder);
                break;
            case 'scholarship':
                $applicationsQuery->join('scholarships', 'applications.scholarship_id', '=', 'scholarships.id')
                    ->orderBy('scholarships.scholarship_name', $sortOrder);
                break;
            case 'status':
                $applicationsQuery->orderBy('applications.status', $sortOrder);
                break;
            default:
                $applicationsQuery->orderBy('applications.created_at', $sortOrder);
        }

        $applications = $applicationsQuery->get();

        // Build scholarships query
        $scholarshipsQuery = Scholarship::with(['conditions', 'requiredDocuments']);
        
        // Apply sorting to base query
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        
        // Clone query for different tabs
        $queryAll = clone $scholarshipsQuery;
        $queryPrivate = clone $scholarshipsQuery;
        $queryGov = clone $scholarshipsQuery;

        // Apply sorting
        $queryAll = $this->sortScholarships($queryAll->get(), $sortBy, $sortOrder);
        $queryPrivate = $this->sortScholarships($queryPrivate->where('scholarship_type', 'private')->get(), $sortBy, $sortOrder);
        $queryGov = $this->sortScholarships($queryGov->where('scholarship_type', 'government')->get(), $sortBy, $sortOrder);

        // Pagination Logic for Scholarships
        $perPage = 5;

        // All Scholarships Paginator
        $pageAll = $request->get('page_all', 1);
        $scholarshipsAll = new LengthAwarePaginator(
            $queryAll->forPage($pageAll, $perPage),
            $queryAll->count(),
            $perPage,
            $pageAll,
            ['path' => $request->url(), 'query' => $request->query(), 'pageName' => 'page_all']
        );

        // Private Scholarships Paginator
        $pagePrivate = $request->get('page_private', 1);
        $scholarshipsPrivate = new LengthAwarePaginator(
            $queryPrivate->forPage($pagePrivate, $perPage),
            $queryPrivate->count(),
            $perPage,
            $pagePrivate,
            ['path' => $request->url(), 'query' => $request->query(), 'pageName' => 'page_private']
        );

        // Government Scholarships Paginator
        $pageGov = $request->get('page_gov', 1);
        $scholarshipsGov = new LengthAwarePaginator(
            $queryGov->forPage($pageGov, $perPage),
            $queryGov->count(),
            $perPage,
            $pageGov,
            ['path' => $request->url(), 'query' => $request->query(), 'pageName' => 'page_gov']
        );
        
        // Get all reports with relationships for full functionality
        $query = \App\Models\Report::with(['sfaoUser', 'campus', 'reviewer']);

        // Apply filters if provided


        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('report_type', $request->type);
        }

        // Apply campus filter
        if ($request->filled('campus') && $request->campus !== 'all') {
            $query->where('campus_id', $request->campus);
        }

        // Apply Academic Year Filter
        $academicYearFilter = $request->get('academic_year', 'all');
        if ($academicYearFilter !== 'all') {
            // Parse "2023-2024" -> Start: 2023-08-01, End: 2024-07-31
            $years = explode('-', $academicYearFilter);
            if (count($years) === 2) {
                $startYear = (int)$years[0];
                $endYear = (int)$years[1];
                $startDate = \Carbon\Carbon::createFromDate($startYear, 8, 1)->startOfDay();
                $endDate = \Carbon\Carbon::createFromDate($endYear, 7, 31)->endOfDay();
                
                // Filter by report_period_start if possible, or created_at fallback
                // Assuming report_period_start is the most accurate reflection of the report's coverage
                $query->whereBetween('report_period_start', [$startDate, $endDate]);
            }
        }

        // Apply sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');

        switch ($sortBy) {
            case 'submitted_at':
                $query->orderBy('submitted_at', $sortOrder);
                break;
            case 'title':
                $query->orderBy('title', $sortOrder);
                break;
            case 'campus':
                $query->join('campuses', 'reports.campus_id', '=', 'campuses.id')
                     ->orderBy('campuses.name', $sortOrder);
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
        }

        // Clone query for different tabs (filters remain applied to base query)
        $querySubmitted = clone $query;
        $queryReviewed = clone $query;
        $queryApproved = clone $query;
        $queryRejected = clone $query;

        // Paginate results (10 per page)
        $reportsParams = ['status', 'type', 'campus', 'sort', 'order', 'academic_year']; // Params to append
        
        $reportsSubmitted = $querySubmitted->where('status', 'submitted')->paginate(10, ['*'], 'page_submitted')->appends($request->only($reportsParams));
        $reportsReviewed = $queryReviewed->where('status', 'reviewed')->paginate(10, ['*'], 'page_reviewed')->appends($request->only($reportsParams));
        $reportsApproved = $queryApproved->where('status', 'approved')->paginate(10, ['*'], 'page_approved')->appends($request->only($reportsParams));
        $reportsRejected = $queryRejected->where('status', 'rejected')->paginate(10, ['*'], 'page_rejected')->appends($request->only($reportsParams));

        // ...

        // Generate Academic Year Options
        // Find the oldest report to determine start range
        $oldestReport = \App\Models\Report::orderBy('report_period_start', 'asc')->first();
        $startYear = $oldestReport && $oldestReport->report_period_start ? $oldestReport->report_period_start->year : now()->year;
        // If the report is from say Jan 2024, that falls in 2023-2024. If Aug 2024, 2024-2025.
        // Simplified: Start from the year of the oldest report.
        
        $currentYear = now()->year;
        $academicYearOptions = [];
        // Generate range from startYear down to currentYear+1
        // We go up to currentYear + 1 to cover the "next" academic year if we are in Aug-Dec
        for ($y = $currentYear + 1; $y >= $startYear; $y--) {
            // Academic Year: Y-1 to Y
            $prev = $y - 1;
            $label = "{$prev}-{$y}";
            $academicYearOptions[] = $label;
        }
        $academicYearOptions = array_unique($academicYearOptions);

        $totalReports = \App\Models\Report::count();

        // Get report statistics for dashboard counts
        $reportStats = [
            'total_reports' => $totalReports,
            'submitted_reports' => \App\Models\Report::where('status', 'submitted')->count(),
            'reviewed_reports' => \App\Models\Report::where('status', 'reviewed')->count(),
            'approved_reports' => \App\Models\Report::where('status', 'approved')->count(),
            'pending_reports' => \App\Models\Report::where('status', 'submitted')->count(),
        ];

        // Generate comprehensive analytics data
        $analytics = $this->generateAnalyticsData(['campus' => $campusFilter]);

        // Get all campuses for filter (Moved to top)
        // $campuses = \App\Models\Campus::all();
        
        // Get filter options for applications
        $campusOptions = $campuses->map(function($campus) {
            return [
                'id' => $campus->id,
                'name' => $campus->name
            ];
        })->toArray();
        
        $scholarshipOptions = Scholarship::all()->map(function($scholarship) {
            return [
                'id' => $scholarship->id,
                'name' => $scholarship->scholarship_name
            ];
        })->toArray();
        
        $statusOptions = [
            ['value' => 'in_progress', 'label' => 'In Progress'],
            ['value' => 'approved', 'label' => 'Approved'],
            ['value' => 'rejected', 'label' => 'Rejected'],
            ['value' => 'pending', 'label' => 'Pending'],
            ['value' => 'claimed', 'label' => 'Claimed'],
            ['value' => 'all', 'label' => 'All Status']
        ];
        


        // Scholars Query - Base
        // Get scholars data for the scholars tab (students who have scholar records)
        $scholarsQuery = Scholar::with(['user', 'scholarship', 'user.campus']);

        // Apply filters (shared filters like campus, status, etc.)
        // Apply campus filter for scholars
        if ($campusFilter !== 'all') {
            $scholarsQuery->whereHas('user', function($query) use ($campusFilter) {
                $query->where('campus_id', $campusFilter);
            });
        }

        // Apply scholarship filter for scholars
        if ($scholarshipFilter !== 'all') {
            $scholarsQuery->where('scholarship_id', $scholarshipFilter);
        }

        // Apply sorting
        switch ($sortBy) {
            // ... (sorting logic is shared, so we can keep it on the base query assuming we clone it properly OR apply it to each clone)
            // Actually, we should clone BEFORE sorting if sorting might differ, but here sorting is global for the page.
            // Let's apply sorting to the base query.
            case 'name':
                $scholarsQuery->join('users', 'scholars.user_id', '=', 'users.id')
                    ->orderBy('users.name', $sortOrder);
                break;
            case 'email':
                $scholarsQuery->join('users', 'scholars.user_id', '=', 'users.id')
                    ->orderBy('users.email', $sortOrder);
                break;
            case 'scholarship':
                $scholarsQuery->join('scholarships', 'scholars.scholarship_id', '=', 'scholarships.id')
                    ->orderBy('scholarships.scholarship_name', $sortOrder);
                break;
            case 'status':
                $scholarsQuery->orderBy('scholars.status', $sortOrder);
                break;
            case 'type':
                $scholarsQuery->orderBy('scholars.type', $sortOrder);
                break;
            default:
                $scholarsQuery->orderBy('scholars.created_at', $sortOrder);
        }

        // Prepare Queries for Tabs
        $queryScholarsAll = clone $scholarsQuery;
        $queryScholarsNew = clone $scholarsQuery;
        $queryScholarsOld = clone $scholarsQuery;

        // Apply specific filters
        // All Scholars tab might still respect the global status filter if set
        if ($statusFilter !== 'all') {
             $queryScholarsAll->where('status', $statusFilter);
             $queryScholarsNew->where('status', $statusFilter);
             $queryScholarsOld->where('status', $statusFilter);
        }

        $queryScholarsNew->where('type', 'new');
        $queryScholarsOld->where('type', 'old');

        // Paginate and preserve current query string parameters
        $scholarsAll = $queryScholarsAll->paginate(10, ['*'], 'page_scholars_all')->appends(request()->query());
        $scholarsNew = $queryScholarsNew->paginate(10, ['*'], 'page_scholars_new')->appends(request()->query());
        $scholarsOld = $queryScholarsOld->paginate(10, ['*'], 'page_scholars_old')->appends(request()->query());

        // Deprecate single $scholars
        $scholars = $scholarsAll;

        // Get qualified applicants (approved by SFAO but not yet selected as scholars)
        $qualifiedApplicantsQuery = User::with(['applications.scholarship', 'campus'])
            ->where('role', 'student')
            ->whereHas('applications', function($query) {
                $query->where('status', 'approved');
            })
            ->whereDoesntHave('scholars'); // Not already a scholar

        // Apply campus filter for qualified applicants
        if ($campusFilter !== 'all') {
            $qualifiedApplicantsQuery->where('campus_id', $campusFilter);
        }

        // Apply scholarship filter for qualified applicants
        if ($scholarshipFilter !== 'all') {
            $qualifiedApplicantsQuery->whereHas('applications', function($query) use ($scholarshipFilter) {
                $query->where('scholarship_id', $scholarshipFilter);
            });
        }

        // Apply sorting for qualified applicants
        switch ($sortBy) {
            case 'name':
                $qualifiedApplicantsQuery->orderBy('first_name', $sortOrder);
                break;
            case 'campus':
                $qualifiedApplicantsQuery->join('campuses', 'users.campus_id', '=', 'campuses.id')
                    ->orderBy('campuses.name', $sortOrder);
                break;
            case 'scholarship':
                $qualifiedApplicantsQuery->join('applications', 'users.id', '=', 'applications.user_id')
                    ->join('scholarships', 'applications.scholarship_id', '=', 'scholarships.id')
                    ->orderBy('scholarships.scholarship_name', $sortOrder);
                break;
            case 'date_approved':
                $qualifiedApplicantsQuery->join('applications', 'users.id', '=', 'applications.user_id')
                    ->orderBy('applications.updated_at', $sortOrder);
                break;
            default:
                $qualifiedApplicantsQuery->orderBy('users.created_at', $sortOrder);
        }

        $qualifiedApplicants = $qualifiedApplicantsQuery->get();

        // Ensure qualifiedApplicants is always a collection
        if (!$qualifiedApplicants) {
            $qualifiedApplicants = collect();
        }

        // Get endorsed applicants (approved by SFAO and ready for scholar selection)
        $endorsedApplicantsQuery = Application::with(['user', 'scholarship', 'user.campus'])
            ->where('status', 'approved')
            ->whereDoesntHave('user.scholars'); // Not already a scholar

        // Apply campus filter for endorsed applicants
        if ($campusFilter !== 'all') {
            $endorsedApplicantsQuery->whereHas('user', function($query) use ($campusFilter) {
                $query->where('campus_id', $campusFilter);
            });
        }

        // Apply scholarship filter for endorsed applicants
        if ($scholarshipFilter !== 'all') {
            $endorsedApplicantsQuery->where('scholarship_id', $scholarshipFilter);
        }

        // Apply sorting for endorsed applicants
        switch ($sortBy) {
            case 'name':
                $endorsedApplicantsQuery->join('users', 'applications.user_id', '=', 'users.id')
                    ->orderBy('users.name', $sortOrder);
                break;
            case 'email':
                $endorsedApplicantsQuery->join('users', 'applications.user_id', '=', 'users.id')
                    ->orderBy('users.email', $sortOrder);
                break;
            case 'scholarship':
                $endorsedApplicantsQuery->join('scholarships', 'applications.scholarship_id', '=', 'scholarships.id')
                    ->orderBy('scholarships.scholarship_name', $sortOrder);
                break;
            case 'status':
                $endorsedApplicantsQuery->orderBy('applications.status', $sortOrder);
                break;
            default:
                $endorsedApplicantsQuery->orderBy('applications.created_at', $sortOrder);
        }

        $endorsedApplicants = $endorsedApplicantsQuery->get();

        // Ensure endorsedApplicants is always a collection
        if (!$endorsedApplicants) {
            $endorsedApplicants = collect();
        }

        // Get rejected applicants (rejected by Central Admin)
        $rejectedApplicants = \App\Models\RejectedApplicant::with(['user', 'scholarship', 'rejectedByUser'])
            ->where('rejected_by', 'central')
            ->orderBy('rejected_at', 'desc')
            ->get();

        return view('central.analytics.index', compact('user', 'applications', 'scholarshipsAll', 'scholarshipsPrivate', 'scholarshipsGov', 'reportStats', 'analytics', 'reportsSubmitted', 'reportsReviewed', 'reportsApproved', 'reportsRejected', 'campuses', 'campusOptions', 'scholarshipOptions', 'statusOptions', 'sortBy', 'sortOrder', 'statusFilter', 'campusFilter', 'scholarshipFilter', 'scholars', 'scholarsAll', 'scholarsNew', 'scholarsOld', 'qualifiedApplicants', 'endorsedApplicants', 'rejectedApplicants', 'totalReports', 'academicYearOptions', 'academicYearFilter'));
    }

    public function getFilteredAnalytics(Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $filters = $request->input('filters', []);
        $analytics = $this->generateAnalyticsData($filters);
        
        return response()->json([
            'success' => true,
            'analytics' => $analytics
        ]);
    }

    /**
     * Generate comprehensive analytics data for the statistics dashboard
     */
    private function generateAnalyticsData($filters = [])
    {
        // Extract filter values
        $timePeriod = $filters['timePeriod'] ?? 'all';
        $campusId = $filters['campus'] ?? 'all';
        
        // Build base query conditions
        $applicationQuery = Application::query();
        $userQuery = User::query();
        $reportQuery = \App\Models\Report::query();
        $scholarQuery = Scholar::query();
        
        // Apply time period filter
        if ($timePeriod !== 'all') {
            $dateCondition = $this->getDateCondition($timePeriod);
            if ($dateCondition) {
                $applicationQuery->whereBetween('created_at', $dateCondition);
                $userQuery->whereBetween('created_at', $dateCondition);
                $reportQuery->whereBetween('created_at', $dateCondition);
                $scholarQuery->whereBetween('created_at', $dateCondition);
            }
        }
        
        
        // Apply campus filter
        if ($campusId !== 'all') {
            $applicationQuery->whereHas('user', function($query) use ($campusId) {
                $query->where('campus_id', $campusId);
            });
            $userQuery->where('campus_id', $campusId);
            $reportQuery->where('campus_id', $campusId);
            $scholarQuery->whereHas('user', function($query) use ($campusId) {
                $query->where('campus_id', $campusId);
            });
        }
        
        // Get basic report statistics
        $totalReports = $reportQuery->count();
        $submittedReports = $reportQuery->where('status', 'submitted')->count();
        $approvedReports = $reportQuery->where('status', 'approved')->count();
        $rejectedReports = $reportQuery->where('status', 'rejected')->count();
        $draftReports = $reportQuery->where('status', 'draft')->count();
        $pendingReviews = $reportQuery->where('status', 'submitted')->count();

        // Get comprehensive application statistics
        $totalApplications = $applicationQuery->count();
        $approvedApplications = (clone $applicationQuery)->where('status', 'approved')->count();
        $rejectedApplications = (clone $applicationQuery)->where('status', 'rejected')->count();
        $pendingApplications = (clone $applicationQuery)->where('status', 'pending')->count();
        $claimedApplications = (clone $applicationQuery)->where('status', 'claimed')->count();

        // Get scholarship statistics
        $totalScholarships = Scholarship::count();
        $activeScholarships = Scholarship::where('is_active', true)->count();
        $acceptingApplicationsScholarships = Scholarship::acceptingApplications()->count();
        $oneTimeScholarships = Scholarship::where('grant_type', 'one_time')->count();
        $recurringScholarships = Scholarship::where('grant_type', 'recurring')->count();
        $discontinuedScholarships = Scholarship::where('grant_type', 'discontinued')->count();

        // Get user statistics
        $totalUsers = User::count();
        $totalStudents = User::where('role', 'student')->count();
        $totalSfaoUsers = User::where('role', 'sfao')->count();
        $totalCentralUsers = User::where('role', 'central')->count();

        // Get scholar statistics (New vs Old)
        $newScholars = (clone $scholarQuery)->where('type', 'new')->count();
        $oldScholars = (clone $scholarQuery)->where('type', 'old')->count();

        // Get demographic statistics from scholars
        $maleStudents = (clone $scholarQuery)->whereHas('user', function($q) {
            $q->where('sex', 'male');
        })->count();
        $femaleStudents = (clone $scholarQuery)->whereHas('user', function($q) {
            $q->where('sex', 'female');
        })->count();
        $studentsWithApplications = User::where('role', 'student')
            ->whereHas('applications')
            ->count();
        $studentsWithoutApplications = $totalStudents - $studentsWithApplications;

        // Get application status by gender (using users table)
        $maleApplications = Application::whereHas('user', function($query) {
            $query->where('sex', 'male');
        })->count();
        $femaleApplications = Application::whereHas('user', function($query) {
            $query->where('sex', 'female');
        })->count();

        $maleApprovedApplications = Application::whereHas('user', function($query) {
            $query->where('sex', 'male');
        })->where('status', 'approved')->count();
        $femaleApprovedApplications = Application::whereHas('user', function($query) {
            $query->where('sex', 'female');
        })->where('status', 'approved')->count();

        $maleRejectedApplications = Application::whereHas('user', function($query) {
            $query->where('sex', 'male');
        })->where('status', 'rejected')->count();
        $femaleRejectedApplications = Application::whereHas('user', function($query) {
            $query->where('sex', 'female');
        })->where('status', 'rejected')->count();

        $malePendingApplications = Application::whereHas('user', function($query) {
            $query->where('sex', 'male');
        })->where('status', 'pending')->count();
        $femalePendingApplications = Application::whereHas('user', function($query) {
            $query->where('sex', 'female');
        })->where('status', 'pending')->count();

        // Get year level distribution from scholars
        $scholarUserQuery = User::whereHas('scholars');
        
        // Apply filters to scholar user query
        if ($timePeriod !== 'all' && $dateCondition) {
             $scholarUserQuery->whereHas('scholars', function($q) use ($dateCondition) {
                 $q->whereBetween('created_at', $dateCondition);
             });
        }
        if ($campusId !== 'all') {
            $scholarUserQuery->where('campus_id', $campusId);
        }

        $yearLevelStats = (clone $scholarUserQuery)
            ->selectRaw('year_level, COUNT(*) as count')
            ->groupBy('year_level')
            ->get();

        // Normalize year level labels to standard format
        $yearLevelMapping = [
            '1st Year' => '1st Year',
            'First Year' => '1st Year',
            '1st' => '1st Year',
            '2nd Year' => '2nd Year', 
            'Second Year' => '2nd Year',
            '2nd' => '2nd Year',
            '3rd Year' => '3rd Year',
            'Third Year' => '3rd Year', 
            '3rd' => '3rd Year',
            '4th Year' => '4th Year',
            'Fourth Year' => '4th Year',
            '4th' => '4th Year',
        ];

        // Group and normalize the data
        $normalizedYearLevels = [];
        foreach ($yearLevelStats as $stat) {
            $normalizedLabel = $yearLevelMapping[$stat->year_level] ?? $stat->year_level;
            if (!isset($normalizedYearLevels[$normalizedLabel])) {
                $normalizedYearLevels[$normalizedLabel] = 0;
            }
            $normalizedYearLevels[$normalizedLabel] += $stat->count;
        }

        // Sort by year level order
        $yearLevelOrder = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
        $sortedYearLevels = [];
        foreach ($yearLevelOrder as $level) {
            if (isset($normalizedYearLevels[$level])) {
                $sortedYearLevels[$level] = $normalizedYearLevels[$level];
            }
        }

        $yearLevelLabels = array_keys($sortedYearLevels);
        $yearLevelCounts = array_values($sortedYearLevels);

        // Get program distribution from scholars
        $programStats = (clone $scholarUserQuery)
            ->selectRaw('program, COUNT(*) as count')
            ->groupBy('program')
            ->orderBy('count', 'desc')
            ->limit(10) // Top 10 programs
            ->get();

        $programLabels = $programStats->pluck('program')->toArray();
        $programCounts = $programStats->pluck('count')->toArray();

        // Get application status by year level (using forms table)
        $yearLevelApplicationStats = [];
        $standardYearLevels = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
        
        foreach ($standardYearLevels as $standardYearLevel) {
            // Get all possible variations for this year level
            $yearLevelVariations = [];
            foreach ($yearLevelMapping as $original => $normalized) {
                if ($normalized === $standardYearLevel) {
                    $yearLevelVariations[] = $original;
                }
            }
            
            // Get applications for all variations of this year level
            $yearLevelApplications = Application::whereHas('user', function($query) use ($yearLevelVariations) {
                $query->whereIn('year_level', $yearLevelVariations);
            })->get();

            $yearLevelApplicationStats[] = [
                'year_level' => $standardYearLevel,
                'total_applications' => $yearLevelApplications->count(),
                'approved_applications' => $yearLevelApplications->where('status', 'approved')->count(),
                'rejected_applications' => $yearLevelApplications->where('status', 'rejected')->count(),
                'pending_applications' => $yearLevelApplications->where('status', 'pending')->count(),
                'claimed_applications' => $yearLevelApplications->where('status', 'claimed')->count()
            ];
        }

        // Get monthly trends (last 6 months)
        $monthlyLabels = [];
        $monthlyReports = [];
        $monthlyApplications = [];
        $monthlyApprovedApplications = [];
        $monthlyRejectedApplications = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyLabels[] = $date->format('M');
            
            $monthlyReports[] = \App\Models\Report::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
                
            $monthlyApplications[] = Application::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $monthlyApprovedApplications[] = Application::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->where('status', 'approved')
                ->count();

            $monthlyRejectedApplications[] = Application::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->where('status', 'rejected')
                ->count();
        }

        // Get campus performance data with applications
        $campuses = Campus::withCount(['reports', 'users' => function($query) {
            $query->where('role', 'student');
        }])->get();
        
        $campusNames = $campuses->pluck('name')->toArray();
        $campusReports = $campuses->pluck('reports_count')->toArray();
        $campusStudents = $campuses->pluck('users_count')->toArray();

        // Get campus application statistics
        $campusApplicationStats = [];
        foreach ($campuses as $campus) {
            $campusApplications = Application::whereHas('user', function($query) use ($campus) {
                $query->where('campus_id', $campus->id);
            })->get();

            // Get scholar stats for campus
            $campusScholarsQuery = Scholar::whereHas('user', function($query) use ($campus) {
                $query->where('campus_id', $campus->id);
            });
            $campusNewScholars = (clone $campusScholarsQuery)->where('type', 'new')->count();
            $campusOldScholars = (clone $campusScholarsQuery)->where('type', 'old')->count();

            // Get total students for this campus
            $campusStudents = User::where('role', 'student')
                ->where('campus_id', $campus->id)
                ->count();

            // Get students with applications for this campus
            $campusStudentsWithApplications = User::where('role', 'student')
                ->where('campus_id', $campus->id)
                ->whereHas('applications')
                ->count();

            // Get gender statistics for this campus
            // Get gender statistics for this campus (Scholars only)
            $campusMaleStudents = (clone $campusScholarsQuery)->whereHas('user', function($q) {
                $q->where('sex', 'male');
            })->count();
            
            $campusFemaleStudents = (clone $campusScholarsQuery)->whereHas('user', function($q) {
                $q->where('sex', 'female');
            })->count();

            // Get year level statistics for this campus (Scholars)
            $campusYearLevelStats = (clone $campusScholarsQuery)->whereHas('user', function($q) use ($campus) {
                   $q->where('campus_id', $campus->id); // Redundant if query has it, but safe
                })->first(); // Wait, I need to join to get year_level or use whereHas logic.
            
            // Better approach for Campus Loop: Query Users who are Scholars in this Campus
            $campusScholarUsers = User::where('campus_id', $campus->id)
                ->whereHas('scholars');

            $campusYearLevelStats = (clone $campusScholarUsers)
                ->selectRaw('year_level, COUNT(*) as count')
                ->groupBy('year_level')
                ->get();

            // Get program statistics for this campus (Scholars)
            $campusProgramStats = (clone $campusScholarUsers)
                ->selectRaw('program, COUNT(*) as count')
                ->groupBy('program')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get();

            // Normalize year level data for this campus
            $campusYearLevelMapping = [
                '1st Year' => '1st Year',
                'First Year' => '1st Year',
                '1st' => '1st Year',
                '2nd Year' => '2nd Year', 
                'Second Year' => '2nd Year',
                '2nd' => '2nd Year',
                '3rd Year' => '3rd Year',
                'Third Year' => '3rd Year', 
                '3rd' => '3rd Year',
                '4th Year' => '4th Year',
                'Fourth Year' => '4th Year',
                '4th' => '4th Year',
            ];

            $campusNormalizedYearLevels = [];
            foreach ($campusYearLevelStats as $stat) {
                $normalizedLabel = $campusYearLevelMapping[$stat->year_level] ?? $stat->year_level;
                if (!isset($campusNormalizedYearLevels[$normalizedLabel])) {
                    $campusNormalizedYearLevels[$normalizedLabel] = 0;
                }
                $campusNormalizedYearLevels[$normalizedLabel] += $stat->count;
            }

            // Sort by year level order
            $campusYearLevelOrder = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
            $campusSortedYearLevels = [];
            foreach ($campusYearLevelOrder as $level) {
                if (isset($campusNormalizedYearLevels[$level])) {
                    $campusSortedYearLevels[$level] = $campusNormalizedYearLevels[$level];
                }
            }

            // Generate campus-specific monthly trends
            $campusMonthlyTrends = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $monthKey = $date->format('M');
                
                $campusMonthlyApplications = Application::whereHas('user', function($query) use ($campus) {
                    $query->where('campus_id', $campus->id);
                })->whereYear('created_at', $date->year)
                  ->whereMonth('created_at', $date->month)
                  ->get();
                
                $campusMonthlyTrends[strtolower($monthKey)] = [
                    'total_applications' => $campusMonthlyApplications->count(),
                    'approved_applications' => $campusMonthlyApplications->where('status', 'approved')->count(),
                    'rejected_applications' => $campusMonthlyApplications->where('status', 'rejected')->count(),
                    'pending_applications' => $campusMonthlyApplications->where('status', 'pending')->count(),
                ];
            }

            $campusApplicationStats[] = [
                'campus_id' => $campus->id,
                'campus_name' => $campus->name,
                'total_students' => $campusStudents,
                'total_applications' => $campusApplications->count(),
                'approved_applications' => $campusApplications->where('status', 'approved')->count(),
                'rejected_applications' => $campusApplications->where('status', 'rejected')->count(),
                'pending_applications' => $campusApplications->where('status', 'pending')->count(),
                'claimed_applications' => $campusApplications->where('status', 'claimed')->count(),
                'students_with_applications' => $campusStudentsWithApplications,
                'students_without_applications' => $campusStudents - $campusStudentsWithApplications,
                'approval_rate' => $campusApplications->count() > 0 ? 
                    round(($campusApplications->where('status', 'approved')->count() / $campusApplications->count()) * 100, 2) : 0,
                // Scholar Stats
                'new_scholars' => $campusNewScholars,
                'old_scholars' => $campusOldScholars,
                // Add gender statistics
                'male_students' => $campusMaleStudents,
                'female_students' => $campusFemaleStudents,
                // Add year level statistics
                'year_level_labels' => array_keys($campusSortedYearLevels),
                'year_level_counts' => array_values($campusSortedYearLevels),
                // Add program statistics
                'program_labels' => $campusProgramStats->pluck('program')->toArray(),
                'program_counts' => $campusProgramStats->pluck('count')->toArray(),
                // Add campus-specific monthly trends
                'monthly_trends' => $campusMonthlyTrends,
                // Scholarship Scholar Stats (New vs Old)
                'scholarship_scholar_stats' => Scholarship::withCount([
                    'scholars as new_scholars_count' => function($q) use ($campus) {
                        $q->where('type', 'new')->whereHas('user', fn($u) => $u->where('campus_id', $campus->id));
                    },
                    'scholars as old_scholars_count' => function($q) use ($campus) {
                        $q->where('type', 'old')->whereHas('user', fn($u) => $u->where('campus_id', $campus->id));
                    }
                ])->get()->filter(fn($s) => $s->new_scholars_count > 0 || $s->old_scholars_count > 0)
                  ->map(fn($s) => [
                      'name' => $s->scholarship_name, 
                      'new' => $s->new_scholars_count, 
                      'old' => $s->old_scholars_count
                  ])->values()
            ];
        }

        // Get scholarship distribution by type
        $scholarshipTypes = Scholarship::selectRaw('scholarship_type, COUNT(*) as count')
            ->groupBy('scholarship_type')
            ->get();
        
        $scholarshipTypeNames = $scholarshipTypes->pluck('scholarship_type')->toArray();
        $scholarshipTypeCounts = $scholarshipTypes->pluck('count')->toArray();

        // Get scholarship performance data
        $scholarshipPerformance = Scholarship::withCount(['applications', 'applications as approved_applications_count' => function($query) {
            $query->where('status', 'approved');
        }])->get()->map(function($scholarship) {
        return [
                'name' => $scholarship->scholarship_name,
                'type' => $scholarship->scholarship_type,
                'total_applications' => $scholarship->applications_count,
                'approved_applications' => $scholarship->approved_applications_count,
                'slots_available' => $scholarship->slots_available,
                'grant_amount' => $scholarship->grant_amount,
                'fill_percentage' => $scholarship->slots_available > 0 ? 
                    min(($scholarship->approved_applications_count / $scholarship->slots_available) * 100, 100) : 0,
                'approval_rate' => $scholarship->applications_count > 0 ? 
                    round(($scholarship->approved_applications_count / $scholarship->applications_count) * 100, 2) : 0
            ];
        });

        // Get application status distribution
        $applicationStatusData = [
            'approved' => $approvedApplications,
            'rejected' => $rejectedApplications,
            'pending' => $pendingApplications,
            'claimed' => $claimedApplications
        ];


        // Get scholarship grant type distribution
        $grantTypeData = [
            'one_time' => $oneTimeScholarships,
            'recurring' => $recurringScholarships,
            'discontinued' => $discontinuedScholarships
        ];

        // Calculate approval rates
        $overallApprovalRate = $totalApplications > 0 ? round(($approvedApplications / $totalApplications) * 100, 2) : 0;
        $overallRejectionRate = $totalApplications > 0 ? round(($rejectedApplications / $totalApplications) * 100, 2) : 0;

        return [
            // Report Statistics
            'total_reports' => $totalReports,
            'submitted_reports' => $submittedReports,
            'approved_reports' => $approvedReports,
            'rejected_reports' => $rejectedReports,
            'draft_reports' => $draftReports,
            'pending_reviews' => $pendingReviews,
            
            // Application Statistics
            'total_applications' => $totalApplications,
            'approved_applications' => $approvedApplications,
            'rejected_applications' => $rejectedApplications,
            'pending_applications' => $pendingApplications,
            'claimed_applications' => $claimedApplications,
            'overall_approval_rate' => $overallApprovalRate,
            'overall_rejection_rate' => $overallRejectionRate,
            
            // Scholarship Statistics
            'total_scholarships' => $totalScholarships,
            'active_scholarships' => $activeScholarships,
            'accepting_applications_scholarships' => $acceptingApplicationsScholarships,
            'one_time_scholarships' => $oneTimeScholarships,
            'recurring_scholarships' => $recurringScholarships,
            'discontinued_scholarships' => $discontinuedScholarships,
            
            // User Statistics
            'total_users' => $totalUsers,
            'total_students' => $totalStudents,
            'total_sfao_users' => $totalSfaoUsers,
            'total_central_users' => $totalCentralUsers,
            
            // Scholar Status
            'new_scholars' => $newScholars,
            'old_scholars' => $oldScholars,
            'scholarship_scholar_stats' => \App\Models\Scholarship::withCount([
                'scholars as new_scholars_count' => function($q) use ($campusId) {
                    $q->where('type', 'new');
                    if ($campusId !== 'all') $q->whereHas('user', fn($u) => $u->where('campus_id', $campusId));
                },
                'scholars as old_scholars_count' => function($q) use ($campusId) {
                    $q->where('type', 'old');
                    if ($campusId !== 'all') $q->whereHas('user', fn($u) => $u->where('campus_id', $campusId));
                }
            ])->get()->filter(fn($s) => $s->new_scholars_count > 0 || $s->old_scholars_count > 0)
              ->map(fn($s) => [
                  'name' => $s->scholarship_name, 
                  'new' => $s->new_scholars_count, 
                  'old' => $s->old_scholars_count
              ])->values(),
            
            // Demographic Statistics
            'male_students' => $maleStudents,
            'female_students' => $femaleStudents,
            'students_with_applications' => $studentsWithApplications,
            'students_without_applications' => $studentsWithoutApplications,
            
            // Gender-based Application Statistics
            'male_applications' => $maleApplications,
            'female_applications' => $femaleApplications,
            'male_approved_applications' => $maleApprovedApplications,
            'female_approved_applications' => $femaleApprovedApplications,
            'male_rejected_applications' => $maleRejectedApplications,
            'female_rejected_applications' => $femaleRejectedApplications,
            'male_pending_applications' => $malePendingApplications,
            'female_pending_applications' => $femalePendingApplications,
            
            // Year Level and Program Data
            'year_level_labels' => $yearLevelLabels,
            'year_level_counts' => $yearLevelCounts,
            'program_labels' => $programLabels,
            'program_counts' => $programCounts,
            'year_level_application_stats' => $yearLevelApplicationStats,
            
            // Monthly Trends
            'monthly_labels' => $monthlyLabels,
            'monthly_reports' => $monthlyReports,
            'monthly_applications' => $monthlyApplications,
            'monthly_approved_applications' => $monthlyApprovedApplications,
            'monthly_rejected_applications' => $monthlyRejectedApplications,
            
            // Campus Data
            'campus_names' => $campusNames,
            'campus_reports' => $campusReports,
            'campus_students' => $campusStudents,
            'campus_application_stats' => $campusApplicationStats,
            
            // Scholarship Data
            'scholarship_types' => $scholarshipTypeNames,
            'scholarship_counts' => $scholarshipTypeCounts,
            'scholarship_performance' => $scholarshipPerformance,
            
            // Distribution Data
            'application_status_data' => $applicationStatusData,
            'grant_type_data' => $grantTypeData
        ];
    }

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
                case 'applications_count':
                    return $scholarship->applications_count ?? 0;
                default:
                    return $scholarship->scholarship_name;
            }
        }, SORT_REGULAR, $sortOrder === 'desc');
    }

    /**
     * Get applications for Central dashboard
     */
    public function getCentralApplications()
    {
        return Application::with(['user', 'scholarship'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // =====================================================
    // DOCUMENT EVALUATION SYSTEM
    // =====================================================

    /**
     * Get date condition for time period filter
     */
    private function getDateCondition($timePeriod)
    {
        $now = now();
        
        switch ($timePeriod) {
            case 'this_month':
                return [
                    $now->copy()->startOfMonth(),
                    $now->copy()->endOfMonth()
                ];
            case 'last_3_months':
                return [
                    $now->copy()->subMonths(3)->startOfMonth(),
                    $now->copy()->endOfMonth()
                ];
            case 'this_year':
                return [
                    $now->copy()->startOfYear(),
                    $now->copy()->endOfYear()
                ];
            default:
                return null;
        }
    }
    /**
     * Show evaluation - Stage 1: Select student and scholarship
     */
    public function showEvaluation($userId)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $student = User::with(['applications.scholarship', 'campus'])->findOrFail($userId);
        
        // Get SFAO admin's campus to verify jurisdiction
        $sfaoAdmin = User::with('campus')->find(session('user_id'));
        $campusIds = $sfaoAdmin->campus->getAllCampusesUnder()->pluck('id')->toArray();
        
        if (!in_array($student->campus_id, $campusIds)) {
            return redirect()->route('sfao.dashboard')->with('error', 'You do not have permission to evaluate this student.');
        }

        // Get applications with scholarship data
        $applications = $student->applications()->with('scholarship')->get();
        
        return view('sfao.applicants.evaluation.stage1-scholarship-selection', compact('student', 'applications'));
    }

    /**
     * Show SFAO documents evaluation - Stage 2
     */
    public function evaluateSfaoDocuments($userId, $scholarshipId)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $student = User::with(['campus'])->findOrFail($userId);
        $scholarship = Scholarship::with(['conditions', 'requiredDocuments'])->findOrFail($scholarshipId);
        
        // Verify SFAO has jurisdiction
        $sfaoAdmin = User::with('campus')->find(session('user_id'));
        $campusIds = $sfaoAdmin->campus->getAllCampusesUnder()->pluck('id')->toArray();
        
        if (!in_array($student->campus_id, $campusIds)) {
            return redirect()->route('sfao.dashboard')->with('error', 'You do not have permission to evaluate this student.');
        }

        // Get only SFAO required documents for this scholarship
        $sfaoDocuments = StudentSubmittedDocument::where('user_id', $userId)
            ->where('scholarship_id', $scholarshipId)
            ->where('document_category', 'sfao_required')
            ->with('evaluator')
            ->get();

        return view('sfao.applicants.evaluation.stage2-sfao-documents', compact(
            'student', 
            'scholarship', 
            'sfaoDocuments'
        ));
    }

    /**
     * Submit SFAO documents evaluation
     */
    public function submitSfaoEvaluation(Request $request, $userId, $scholarshipId)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $request->validate([
            'evaluations' => 'required|array',
            'evaluations.*.document_id' => 'required|exists:student_submitted_documents,id',
            'evaluations.*.status' => 'required|in:approved,pending,rejected',
        ]);

        $evaluatorId = session('user_id');
        $evaluatedAt = now();

        foreach ($request->evaluations as $evaluation) {
            StudentSubmittedDocument::where('id', $evaluation['document_id'])
                ->where('user_id', $userId)
                ->where('scholarship_id', $scholarshipId)
                ->where('document_category', 'sfao_required')
                ->update([
                    'evaluation_status' => $evaluation['status'],
                    'evaluated_by' => $evaluatorId,
                    'evaluated_at' => $evaluatedAt,
                ]);
        }

        return redirect()->route('sfao.evaluation.scholarship-documents', ['user_id' => $userId, 'scholarship_id' => $scholarshipId])
            ->with('success', 'SFAO documents evaluation completed. Proceeding to scholarship documents.');
    }

    /**
     * Show scholarship documents evaluation - Stage 3
     */
    public function evaluateScholarshipDocuments($userId, $scholarshipId)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $student = User::with(['campus'])->findOrFail($userId);
        $scholarship = Scholarship::with(['conditions', 'requiredDocuments'])->findOrFail($scholarshipId);
        
        // Verify SFAO has jurisdiction
        $sfaoAdmin = User::with('campus')->find(session('user_id'));
        $campusIds = $sfaoAdmin->campus->getAllCampusesUnder()->pluck('id')->toArray();
        
        if (!in_array($student->campus_id, $campusIds)) {
            return redirect()->route('sfao.dashboard')->with('error', 'You do not have permission to evaluate this student.');
        }

        // Get only scholarship required documents for this scholarship
        $scholarshipDocuments = StudentSubmittedDocument::where('user_id', $userId)
            ->where('scholarship_id', $scholarshipId)
            ->where('document_category', 'scholarship_required')
            ->with('evaluator')
            ->get();

        return view('sfao.applicants.evaluation.stage3-scholarship-documents', compact(
            'student', 
            'scholarship', 
            'scholarshipDocuments'
        ));
    }

    /**
     * Determine automatic decision based on document evaluation statuses
     * Priority: Reject > Pending > Approve
     */
    private function determineAutoDecision($documents)
    {
        if ($documents->isEmpty()) {
            return 'pending'; // Default to pending if no documents
        }

        // Check if any document is rejected (highest priority)
        if ($documents->contains('evaluation_status', 'rejected')) {
            return 'reject';
        }

        // Check if any document is pending (second priority)
        if ($documents->contains('evaluation_status', 'pending')) {
            return 'pending';
        }

        // All documents are approved
        return 'approve';
    }

    /**
     * Show final review - Stage 4
     */
    public function finalEvaluation($userId, $scholarshipId)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $student = User::with(['campus'])->findOrFail($userId);
        $scholarship = Scholarship::with(['conditions', 'requiredDocuments'])->findOrFail($scholarshipId);
        
        // Verify SFAO has jurisdiction
        $sfaoAdmin = User::with('campus')->find(session('user_id'));
        $campusIds = $sfaoAdmin->campus->getAllCampusesUnder()->pluck('id')->toArray();
        
        if (!in_array($student->campus_id, $campusIds)) {
            return redirect()->route('sfao.dashboard')->with('error', 'You do not have permission to evaluate this student.');
        }

        // Get all submitted documents for this scholarship
        $allDocuments = StudentSubmittedDocument::where('user_id', $userId)
            ->where('scholarship_id', $scholarshipId)
            ->with('evaluator')
            ->get();

        // Separate SFAO and scholarship documents
        $sfaoDocuments = $allDocuments->where('document_category', 'sfao_required');
        $scholarshipDocuments = $allDocuments->where('document_category', 'scholarship_required');

        // Get application
        $application = Application::where('user_id', $userId)
            ->where('scholarship_id', $scholarshipId)
            ->first();

        // Determine auto-decision based on document statuses
        $autoDecision = $this->determineAutoDecision($allDocuments);

        return view('sfao.applicants.evaluation.stage4-final-review', compact(
            'student',
            'scholarship',
            'sfaoDocuments',
            'scholarshipDocuments',
            'allDocuments',
            'application',
            'autoDecision'
        ))->with('evaluatedDocuments', $allDocuments);
    }

    /**
     * Submit final evaluation with remarks
     * Now automatically determines decision based on document statuses
     */
    public function submitFinalEvaluation(Request $request, $userId, $scholarshipId)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $request->validate([
            'remarks' => 'nullable|string|max:1000',
        ]);

        // Get the application
        $application = Application::where('user_id', $userId)
            ->where('scholarship_id', $scholarshipId)
            ->first();

        if (!$application) {
            return redirect()->back()->with('error', 'Application not found.');
        }

        // Get all documents to determine auto-decision
        $documents = StudentSubmittedDocument::where('user_id', $userId)
            ->where('scholarship_id', $scholarshipId)
            ->get();

        // Automatically determine the decision
        $action = $this->determineAutoDecision($documents);

        // Update application with remarks and status (including pending)
        $newStatus = match($action) {
            'approve' => 'approved',
            'reject' => 'rejected',
            'pending' => 'pending',
            default => $application->status,
        };

        $application->update([
            'status' => $newStatus,
            'remarks' => $request->remarks,
        ]);

        // Get document evaluation status for this application
        $documentStatus = [
            'pending' => $documents->where('evaluation_status', 'pending')->count(),
            'rejected' => $documents->where('evaluation_status', 'rejected')->count(),
            'approved' => $documents->where('evaluation_status', 'approved')->count(),
        ];

        $pendingDocuments = $documents->where('evaluation_status', 'pending')->pluck('document_name')->toArray();
        $rejectedDocuments = $documents->where('evaluation_status', 'rejected')->pluck('document_name')->toArray();

        // Create notification for student
        $notificationTitle = match($action) {
            'approve' => 'Application Approved',
            'reject' => 'Application Rejected', 
            'pending' => 'Application Status Updated',
            default => 'Application Status Updated'
        };

        $notificationMessage = match($action) {
            'approve' => 'Your application for ' . $application->scholarship->scholarship_name . ' has been approved based on document evaluation.',
            'reject' => 'Your application for ' . $application->scholarship->scholarship_name . ' has been rejected based on document evaluation.',
            'pending' => 'Your application for ' . $application->scholarship->scholarship_name . ' has been set to pending for further review based on document evaluation.',
            default => 'Your application status has been updated.'
        };

        // Add document information to message if there are pending or rejected documents
        if ($action === 'pending' && (count($pendingDocuments) > 0 || count($rejectedDocuments) > 0)) {
            $documentInfo = [];
            if (count($pendingDocuments) > 0) {
                $documentInfo[] = 'Pending documents: ' . implode(', ', $pendingDocuments);
            }
            if (count($rejectedDocuments) > 0) {
                $documentInfo[] = 'Rejected documents: ' . implode(', ', $rejectedDocuments);
            }
            $notificationMessage .= ' ' . implode('. ', $documentInfo) . '.';
        }

        Notification::create([
            'user_id' => $userId,
            'type' => 'application_status',
            'title' => $notificationTitle,
            'message' => $notificationMessage,
            'data' => [
                'application_id' => $application->id,
                'scholarship_id' => $scholarshipId,
                'scholarship_name' => $application->scholarship->scholarship_name,
                'status' => $newStatus,
                'remarks' => $request->remarks,
                'document_status' => $documentStatus,
                'pending_documents' => $pendingDocuments,
                'rejected_documents' => $rejectedDocuments,
            ]
        ]);

        $message = match($action) {
            'approve' => 'Application approved successfully based on document evaluation.',
            'reject' => 'Application rejected successfully based on document evaluation.',
            'pending' => 'Application set to pending successfully based on document evaluation.',
            default => 'Application status updated successfully.'
        };

        return redirect()->route('sfao.dashboard')
            ->with('success', $message);
    }


    /**
     * Show validation page for an endorsed (approved) application for Central admin.
     */
    public function showEndorsedValidation(Request $request, Application $application)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        // Load necessary relationships
        $application->load(['user', 'scholarship', 'user.campus', 'user.form']);
        
        // Get the user and scholarship for easier access in view
        $user = $application->user;
        $scholarship = $application->scholarship;
        
        // Load submitted documents for this application
        $submittedDocuments = StudentSubmittedDocument::where('user_id', $user->id)
            ->where('scholarship_id', $scholarship->id)
            ->get();


        // DEBUG: Verify the user being passed
        // dd('DEBUG: checking user', $user->toArray(), 'Is this the admin?', $user->name);
        
        return view('central.endorsed.validate', compact('application', 'user', 'scholarship', 'submittedDocuments'));
    }

    /**
     * Accept an endorsed application (Central)
     * @param Application $application
     */
    public function acceptEndorsed(Application $application)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        // Ensure the application is in approved status (endorsed by SFAO)
        if ($application->status !== 'approved') {
            return back()->with('error', 'Only SFAO-approved applications can be accepted.');
        }

        // Load necessary relationships
        $application->load(['user', 'scholarship']);

        // Check if scholar already exists for this user and scholarship
        $existingScholar = Scholar::where('user_id', $application->user_id)
            ->where('scholarship_id', $application->scholarship_id)
            ->first();

        if ($existingScholar) {
            return back()->with('error', 'A scholar record already exists for this application.');
        }

        // Calculate scholarship dates
        $startDate = now()->startOfMonth();
        $endDate = $application->scholarship->renewal_allowed 
            ? $startDate->copy()->addYear() 
            : $startDate->copy()->addMonths(6);

        // Create scholar record as 'new' scholar
        Scholar::create([
            'user_id' => $application->user_id,
            'scholarship_id' => $application->scholarship_id,
            'application_id' => $application->id,
            'type' => 'new', // Always new when accepted from endorsed applicants
            'grant_count' => 0, // No grants received yet
            'total_grant_received' => 0.00,
            'scholarship_start_date' => $startDate,
            'scholarship_end_date' => $endDate,
            'status' => 'active',
            'notes' => 'Created from accepted endorsed application',
        ]);

        // Application remains in 'approved' status - it's now validated by Central
        
        // Create notification for student
        NotificationService::notifyApplicationStatusChange($application, 'approved');

        return redirect()->route('central.dashboard', ['tab' => 'endorsed_applicants'])
            ->with('success', 'Application has been accepted successfully. Scholar record has been created.');
    }

    /**
     * Reject an endorsed application (Central)
     * @param Request $request
     * @param Application $application
     */
    public function rejectEndorsed(Request $request, Application $application)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        // Ensure the application is in approved status (endorsed by SFAO)
        if ($application->status !== 'approved') {
            return back()->with('error', 'Only SFAO-approved applications can be rejected.');
        }

        // Update application status to rejected
        $application->status = 'rejected';
        $application->save();

        // Store in rejected_applicants table to prevent re-application
        RejectedApplicant::create([
            'user_id' => $application->user_id,
            'scholarship_id' => $application->scholarship_id,
            'application_id' => $application->id,
            'rejected_by' => 'central',
            'rejected_by_user_id' => session('user_id'),
            'rejection_reason' => $request->rejection_reason,
            'remarks' => $request->remarks ?? null,
            'rejected_at' => now(),
        ]);

        // Create notification for student
        NotificationService::notifyApplicationStatusChange($application, 'rejected');

        return redirect()->route('central.dashboard', ['tab' => 'rejected_applicants'])
            ->with('success', 'Application has been rejected. The student will not be able to apply to this scholarship again.');
    }

    /**
     * View rejected applicants list (Central)
     */
    public function viewRejectedApplicants()
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        $rejectedApplicants = RejectedApplicant::with(['user', 'scholarship', 'rejectedByUser'])
            ->where('rejected_by', 'central')
            ->orderBy('rejected_at', 'desc')
            ->get();

        return view('central.partials.tabs.rejected-applicants', compact('rejectedApplicants'));
    }
    /**
     * Submit Scholarship Specific documents evaluation - Stage 3
     */
    public function submitScholarshipEvaluation(Request $request, $userId, $scholarshipId)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $request->validate([
            'evaluations' => 'required|array',
            'evaluations.*.document_id' => 'required|exists:student_submitted_documents,id',
            'evaluations.*.status' => 'required|in:approved,pending,rejected',
        ]);

        $evaluatorId = session('user_id');
        $evaluatedAt = now();

        foreach ($request->evaluations as $evaluation) {
            StudentSubmittedDocument::where('id', $evaluation['document_id'])
                ->where('user_id', $userId)
                ->where('scholarship_id', $scholarshipId)
                ->update([
                    'evaluation_status' => $evaluation['status'],
                    'evaluated_by' => $evaluatorId,
                    'evaluated_at' => $evaluatedAt,
                ]);
        }

        return redirect()->route('sfao.evaluation.final', ['user_id' => $userId, 'scholarship_id' => $scholarshipId])
            ->with('success', 'Scholarship documents evaluation completed. Proceeding to final review.');
    }

}
