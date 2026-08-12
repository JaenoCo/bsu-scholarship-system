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
 * ANNOTATED COPY — every method has an explanation block above it.
 * The goal of these annotations is to help you (Jaeno) quickly find
 * where to apply your per-student status-priority dedup rule
 * (approved > in_progress/pending > rejected) across the codebase,
 * and to flag exactly which data structures are "one row per
 * application" (safe to double count) vs "one row per student"
 * (already deduped).
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
     * ANNOTATION: This is the canonical priority map. Anywhere you need
     * to collapse a student's multiple applications down to ONE
     * representative status, you should be calling this helper (or the
     * literal array ['approved','in_progress','pending','rejected'] in
     * the same order) rather than re-inventing the order inline.
     * Lower number = higher priority = wins.
     *
     * Currently used by: sfaoDashboard() for the sort-by-status column.
     * NOT currently used by: sfaoApplicantsList() (uses an equivalent
     * literal array instead — functionally fine, but a duplicate source
     * of truth), and NOT used at all by the analytics/chart-building
     * data (generateAnalyticsData(), $analytics['all_applications_data']
     * in sfaoDashboard()) — THIS is where your donut-chart bug lives,
     * because those datasets are flat "one row per application" and
     * nothing downstream collapses them per student before charting.
     *
     * @return array<string,int>
     */
    private function statusSortPriority(): array
    {
        return [
            'approved' => 1,
            'in_progress' => 2,
            'pending' => 3,
            'rejected' => 4,
        ];
    }

    /**
     * ANNOTATION: Generic single-application status mutator, shared by
     * the simple SFAO/Central approve & reject action endpoints further
     * down (sfaoApproveApplication, sfaoRejectApplication,
     * centralApproveApplication, centralRejectApplication). Operates on
     * exactly one Application row — no multi-application dedup concerns
     * here since it's a single record mutation, not a read/aggregate.
     *
     * Flow: auth check -> load application -> set status -> save ->
     * notify student -> flash a human-readable success message.
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

        $statusLabel = match ($status) {
            'in_progress' => 'forwarded for admin review',
            'approved' => 'approved',
            'rejected' => 'rejected',
            'pending' => 'set to pending',
            'claimed' => 'claimed',
            default => $status,
        };

        return back()->with('success', "Application {$statusLabel} successfully.");
    }

    /**
     * ANNOTATION: Student-facing "my applications" list. Scoped to the
     * logged-in student only, so there's no cross-student aggregation
     * or dedup to worry about — a student naturally only sees their own
     * (possibly multiple) applications, each shown individually.
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
     * ANNOTATION: Student applies (or re-applies) to a single
     * scholarship. Note the upsert-like pattern: if an Application row
     * already exists for this (user_id, scholarship_id) pair it's
     * reset to 'pending' rather than duplicated. This is actually part
     * of why a student can never have TWO applications to the SAME
     * scholarship — but they CAN have applications to DIFFERENT
     * scholarships in different statuses simultaneously, which is
     * exactly the scenario your dedup rule needs to handle downstream.
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
                'user_id' => session('user_id'),
                'scholarship_id' => $request->scholarship_id,
                'status' => 'pending',
            ]);

            $message = 'You have successfully applied for the scholarship.';

            return back()->with('success', $message);
        }
    }

    /**
     * ANNOTATION: Student withdraws from a single scholarship — deletes
     * the Application row and any submitted documents/files tied to
     * that (user_id, scholarship_id) pair. Single-record operation, no
     * dedup concerns.
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
     * ANNOTATION: Central Admin's queue of SFAO-endorsed (in_progress)
     * applications. This intentionally shows one row PER APPLICATION,
     * not per student — it's a work queue, not a headcount metric, so
     * a student with two in_progress applications SHOULD legitimately
     * appear twice here (once per scholarship they're being reviewed
     * for). No dedup needed/wanted in this method.
     */
    public function viewApplicants()
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        // Retrieve only SFAO-approved applications (now marked as 'in_progress') with related user and scholarship info
        $applications = Application::with(['user', 'scholarship'])
            ->where('status', 'in_progress') // Only show SFAO-approved applications (in_progress status)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('central.partials.tabs.applicants', compact('applications'));
    }

    /**
     * SFAO Applicants List (AJAX)
     * Handles fetching and filtering of applicant data for SFAO Dashboard
     *
     * ANNOTATION: This is the AJAX-backed table behind the SFAO
     * dashboard's "Applicants" tab (separate from sfaoDashboard()'s own
     * applicants tabs — this looks like a newer/alternate implementation
     * of roughly the same feature, worth confirming with your routes
     * file which one is actually wired to the current UI).
     *
     * Structure:
     *   1. Auth check
     *   2. Resolve the SFAO admin's campus + all campuses under it
     *      (multi-campus jurisdiction support)
     *   3. Read filter/sort/tab params from the request
     *   4. Build base User query with eager-loaded `applications`
     *      (filtered by status/scholarship inside the `with()` closure —
     *      careful, this only filters which applications are LOADED,
     *      not which students are RETURNED)
     *   5-6. Apply the various dropdown filters + sorting
     *   7. Paginate — one row per STUDENT here (good, this is a
     *      genuinely per-student list)
     *   8. Post-process each paginated student to compute a single
     *      `display_status` — THIS is where your priority rule is
     *      already correctly applied (see below)
     *   9. Compute tab badge counts using whereHas() per status, scoped
     *      to unique students
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
        if (!$user->campus) {
            return response()->json(['error' => 'User campus not assigned'], 400);
        }
        $campusIds = $user->campus->getAllCampusesUnder()->pluck('id');

        $tab = str_replace('_', '-', $request->get('tab', 'applicants'));
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $campusFilter = $request->get('campus_filter', 'all');
        $scholarshipFilter = $request->get('scholarship_filter', 'all');
        $collegeFilter = $request->get('college_filter', 'all');
        $programFilter = $request->get('program_filter', 'all');
        $trackFilter = $request->get('track_filter', 'all');
        $academicYearFilter = $request->get('academic_year_filter', 'all');
        $statusFilter = str_replace('-', '_', $request->get('status_filter', 'all'));

        // Resolve the effective application-status filter: an explicit
        // status_filter query param wins; otherwise fall back to
        // inferring it from a status-specific tab name (e.g.
        // "applicants-pending" -> pending).
        $applicationStatusFilter = $statusFilter !== 'all' ? $statusFilter : null;
        if ($applicationStatusFilter === null && str_starts_with($tab, 'applicants-')) {
            $tabStatus = str_replace('applicants-', '', $tab);
            if ($tabStatus === 'in-progress') {
                $tabStatus = 'in_progress';
            }
            if (in_array($tabStatus, ['in_progress', 'pending', 'approved', 'rejected'])) {
                $applicationStatusFilter = $tabStatus;
            }
        }

        $applyApplicationFilters = function ($q, string $status = '__current__') use ($applicationStatusFilter, $scholarshipFilter, $academicYearFilter) {
            $effectiveStatus = $status === '__current__' ? $applicationStatusFilter : $status;

            if ($effectiveStatus && $effectiveStatus !== 'all') {
                $q->where('status', $effectiveStatus);
            }

            if ($scholarshipFilter !== 'all') {
                $q->where('scholarship_id', $scholarshipFilter);
            }

            if ($academicYearFilter !== 'all') {
                $parts = explode('-', $academicYearFilter);
                if (count($parts) === 2) {
                    $q->whereBetween('created_at', [$parts[0] . '-08-01', $parts[1] . '-07-31']);
                }
            }
        };

        // 3. Base Query - flat string columns (college/program/track live on users, no join tables)
        // NOTE: college/program/track are plain string columns on `users`,
        // NOT foreign keys into separate lookup tables — confirmed
        // elsewhere in your project notes, so no join tables needed here.
        $query = User::where('role', 'student')
            ->whereIn('campus_id', $campusIds)
            ->with([
                'applications' => function ($q) use ($applyApplicationFilters) {
                    // NOTE: this only constrains which application rows
                    // get eager-loaded onto each student, it does NOT
                    // filter which students are returned by the outer
                    // query — that's handled separately below via
                    // whereHas().
                    $applyApplicationFilters($q);
                },
                'applications.scholarship',
                'documents',
                'form',
                'campus'
            ]);

        // 4. Apply Filters
        if ($campusFilter !== 'all') {
            $query->where('campus_id', $campusFilter);
        }

        $query->whereHas('applications', $applyApplicationFilters);

        if ($collegeFilter !== 'all') {
            // collegeFilter can be a pipe-delimited set of variant labels
            // (see the CABEIHM merging logic further down in
            // sfaoDashboard()) — whereIn handles all variants at once.
            $variations = explode('|', $collegeFilter);
            $query->whereIn('college', $variations);
        }

        if ($programFilter !== 'all') {
            $query->where('program', $programFilter);
        }

        if ($trackFilter !== 'all') {
            $query->where('track', $trackFilter);
        }

        if ($academicYearFilter !== 'all') {
            // Academic year assumed to start August 1 and end July 31
            // of the following year — same assumption used elsewhere
            // (see sfaoDashboard()'s $academicYears computation).
            // Handled by the application-scoped filter above.
        }

        // 5. Tab-based status is resolved into $applicationStatusFilter above.

        // 6. Sorting
        $orderCol = match ($sortBy) {
            'email' => 'email',
            'date_joined' => 'created_at',
            default => 'name',
        };
        $query->orderBy($orderCol, $sortOrder);

        // 7. Paginate
        // ANNOTATION: paginated at the STUDENT level (one row per user),
        // which is correct/safe — a student won't appear twice in this
        // list purely because they have multiple applications.
        $paginatedStudents = $query->paginate(10, ['*'], 'page_applicants');

        // 8. Post-process for Blade template
        $paginatedStudents->getCollection()->transform(function ($student) use ($scholarshipFilter) {
            $statuses = $student->applications->pluck('status')->filter()->unique()->toArray();

            if (empty($statuses)) {
                $student->display_status = 'not_applied';
            } else {
                // ANNOTATION: *** THIS is your priority-rule dedup logic,
                // already correctly implemented for this table. ***
                // It walks the priority list (approved -> in_progress ->
                // pending -> rejected) and picks the first one the
                // student actually has, guaranteeing exactly one
                // display_status per student regardless of how many
                // applications they have.
                //
                // Minor suggestion: this literal array duplicates
                // statusSortPriority()'s ordering. Consider replacing
                // with:
                //   foreach (array_keys($this->statusSortPriority()) as $status) { ... }
                // so there's a single source of truth for the priority
                // order going forward.
                foreach (['approved', 'in_progress', 'pending', 'rejected'] as $status) {
                    if (in_array($status, $statuses)) {
                        $student->display_status = $status;
                        break;
                    }
                }
            }

            $student->has_applications = $student->applications->count() > 0;
            $student->has_documents = $student->documents->count() > 0;
            $student->documents_count = $student->documents->count();
            $student->applied_scholarships = $student->applications
                ->filter(function ($a) use ($scholarshipFilter) {
                    return $scholarshipFilter === 'all' || $a->scholarship_id == $scholarshipFilter;
                })
                ->pluck('scholarship.scholarship_name')
                ->filter()->unique()->values()->toArray();

            // Per-application detail array for the modal drill-down —
            // note this stays as one entry PER APPLICATION (correct,
            // since the modal needs to show each application
            // individually, not a collapsed status).
            $student->applications_with_types = $student->applications->map(function ($app) use ($student) {
                $applicationDocuments = $student->documents->where('scholarship_id', $app->scholarship_id);
                $app->documents_count = $applicationDocuments->count();
                $app->last_uploaded = $applicationDocuments->max('updated_at');

                return [
                    'id' => $app->id,
                    'scholarship_name' => $app->scholarship?->scholarship_name ?? 'Unknown',
                    'status' => $app->status,
                    'grant_count' => $app->grant_count,
                    'grant_count_display' => method_exists($app, 'getGrantCountDisplay') ? $app->getGrantCountDisplay() : $app->grant_count,
                    'grant_count_badge_color' => method_exists($app, 'getGrantCountBadgeColor') ? $app->getGrantCountBadgeColor() : 'gray'
                ];
            });

            return $student;
        });

        // 9. Counts - apply the SAME non-tab filters used for the list
        // ANNOTATION: each of these counts is independently scoped via
        // whereHas() to STUDENTS (not applications), so no student is
        // double-counted WITHIN a single bucket. However, since each
        // bucket is computed independently, a student with both a
        // pending AND an approved application will legitimately
        // contribute to BOTH the 'pending' and 'approved' counts here.
        // That's fine for "how many students currently have a pending
        // application" style badges, but these counts will NOT sum to
        // `total` — don't be surprised if pending+in_progress+approved+
        // rejected > total for that reason. If you want mutually
        // exclusive counts (i.e. one bucket per student using the
        // display_status priority above), you'd need to compute them
        // client-side from display_status, or restructure this to
        // group by a computed representative status server-side.
        $countsBase = User::where('role', 'student')->whereIn('campus_id', $campusIds);

        if ($campusFilter !== 'all') {
            $countsBase->where('campus_id', $campusFilter);
        }
        if ($scholarshipFilter !== 'all') {
            $countsBase->whereHas('applications', fn($q) => $q->where('scholarship_id', $scholarshipFilter));
        }
        if ($collegeFilter !== 'all') {
            $countsBase->whereIn('college', explode('|', $collegeFilter));
        }
        if ($programFilter !== 'all') {
            $countsBase->where('program', $programFilter);
        }
        if ($trackFilter !== 'all') {
            $countsBase->where('track', $trackFilter);
        }
        if ($academicYearFilter !== 'all') {
            // Applied in the application-scoped count filters below.
        }

        $counts = [
            'total' => (clone $countsBase)->whereHas('applications', fn($q) => $applyApplicationFilters($q, 'all'))->count(),
            'in_progress' => (clone $countsBase)->whereHas('applications', fn($q) => $applyApplicationFilters($q, 'in_progress'))->count(),
            'pending' => (clone $countsBase)->whereHas('applications', fn($q) => $applyApplicationFilters($q, 'pending'))->count(),
            'approved' => (clone $countsBase)->whereHas('applications', fn($q) => $applyApplicationFilters($q, 'approved'))->count(),
            'rejected' => (clone $countsBase)->whereHas('applications', fn($q) => $applyApplicationFilters($q, 'rejected'))->count(),
        ];

        return response()->json([
            'html' => view('sfao.applicants.list', ['students' => $paginatedStudents])->render(),
            'counts' => $counts
        ]);
    }

    /**
     * SFAO Dashboard - Only shows applicants (students with applications), not scholars
     *
     * ANNOTATION: This is the big one — it builds essentially every
     * section of the SFAO dashboard in a single request: the per-status
     * applicant tabs, the scholarships tabs, the scholars tabs, reports,
     * filter dropdown option lists, AND the analytics payload that
     * feeds your Chart.js charts (including the donut chart you're
     * debugging). Read the inline notes below closely — the "one row
     * per application" vs "one row per student" distinction is called
     * out at each relevant block.
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
            if (in_array($statusFromTab, ['in_progress', 'pending', 'rejected'])) {
                $statusFilter = $statusFromTab;
            }
            // 'approved' tab is handled separately via has_approved_documents property
        }

        // Build the query - SFAO sees all students in their domain
        // Exclude students who are already scholars (they will be shown in Scholars tab)
        // Build the query - SFAO sees all students in their domain
        //
        // ANNOTATION: base query joins in student_submitted_documents
        // (left join, filtered to sfao_required docs) so downstream
        // aggregates (MAX(updated_at), COUNT(DISTINCT id)) can be
        // computed per student/application. The commented-out
        // whereDoesntHave('scholars') shows a past design decision —
        // existing scholars ARE allowed to show up here if they have a
        // new/separate application.
        $query = User::where('role', 'student')
            ->whereIn('campus_id', $campusIds)
            // Removed: whereDoesntHave('scholars') to allow existing scholars to appear if they have new applications
            ->with(['applications.scholarship', 'form', 'campus'])
            ->leftJoin('student_submitted_documents', function ($join) {
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

        //$queryNotApplied = clone $query;
        $queryInProgress = clone $query;
        $queryPending = clone $query;
        $queryApproved = clone $query;
        $queryRejected = clone $query;

        // Apply filters to clones
        //$queryNotApplied->doesntHave('applications');

        $queryInProgress->whereHas('applications', function ($q) {
            $q->where('status', 'in_progress');
        });

        $queryPending->whereHas('applications', function ($q) {
            $q->where('status', 'pending');
        });

        $queryRejected->whereHas('applications', function ($q) {
            $q->where('status', 'rejected');
        });

        $queryApproved->whereHas('applications', function ($q) {
            $q->where('status', 'approved');
        });

        // Precompute a representative application status per student, used only
        // for sorting the "status" column below. Previously the applications
        // table was never pulled into this part of the dashboard at all, so a
        // status-based sort request had nothing to sort by and silently fell
        // back to sorting by name. Priority mirrors the "highest status wins"
        // rule used for badge counts elsewhere: approved > in_progress > pending > rejected.
        //
        // ANNOTATION: this IS your dedup/priority rule, correctly applied
        // — but scoped ONLY to sorting. It has no effect on counting or
        // charting; it just determines row order when sortBy=status.
        $statusPriority = $this->statusSortPriority();
        $sortStatusMap = Application::whereHas('user', function ($q) use ($campusIds) {
            $q->whereIn('campus_id', $campusIds);
        })
            ->get(['user_id', 'status'])
            ->groupBy('user_id')
            ->map(function ($apps) use ($statusPriority) {
                return $apps->pluck('status')
                    ->sortBy(fn($status) => $statusPriority[$status] ?? 999)
                    ->first();
            });

        // Helper to process (fetch, sort, paginate)
        //
        // ANNOTATION: $processStudents builds ONE OF THE FIVE tabs
        // (All/InProgress/Pending/Approved/Rejected). Each row here is
        // really an APPLICATION joined with its student — grouped by
        // (user, application) so a student legitimately appears once
        // per matching application within a given tab. That's correct
        // for "All" (which should show every application) but note
        // that the per-tab queries ($queryInProgress etc.) already
        // whereHas-filtered to the relevant status before this runs,
        // so within e.g. the Pending tab a student only shows once per
        // pending application they have (usually just one, since
        // apply() prevents duplicate applications to the same
        // scholarship).
        $processStudents = function ($query, $pageName, $statusFilter = null) use ($sortBy, $sortOrder, $request, $sortStatusMap, $statusPriority) {
            $students = $query->select(
                'users.id as student_id',
                'users.name',
                'users.email',
                'users.created_at',
                'users.campus_id',
                'applications.id as application_id',
                'applications.scholarship_id',
                'applications.status',
                DB::raw('MAX(student_submitted_documents.updated_at) as last_uploaded'),
                DB::raw('COUNT(DISTINCT student_submitted_documents.id) as documents_count')
            )
                ->join('applications', function ($join) use ($statusFilter) {
                    $join->on('users.id', '=', 'applications.user_id');

                    if ($statusFilter !== null) {
                        $join->where('applications.status', $statusFilter);
                    }
                })
                ->leftJoin('student_submitted_documents', function ($join) {
                    $join->on('applications.user_id', '=', 'student_submitted_documents.user_id')
                        ->on('applications.scholarship_id', '=', 'student_submitted_documents.scholarship_id');
                })
                ->groupBy(
                    'users.id',
                    'users.name',
                    'users.email',
                    'users.created_at',
                    'users.campus_id',
                    'applications.id',
                    'applications.scholarship_id',
                    'applications.status'
                )
                ->get();

            $students = $students->sortBy(function ($student) use ($sortBy, $sortStatusMap, $statusPriority) {
                switch ($sortBy) {
                    case 'name':
                        return $student->name;
                    case 'email':
                        return $student->email;
                    case 'date_joined':
                        return $student->created_at;
                    case 'last_uploaded':
                        return $student->last_uploaded;
                    case 'documents_count':
                        return $student->documents_count;
                    case 'status':
                        // Uses the priority map for ordering only — a
                        // student's SORT POSITION reflects their best
                        // status, even though the row itself may
                        // represent a lower-priority application.
                        $status = $sortStatusMap->get($student->student_id);
                        return $statusPriority[$status] ?? 999;
                    default:
                        return $student->name;
                }
            });

            if ($sortOrder === 'desc') {
                $students = $students->reverse();
            }

            $perPage = 10;
            $page = $request->ajax() ? $request->get('page_applicants', 1) : $request->get($pageName, 1);

            return new LengthAwarePaginator(
                $students->forPage($page, $perPage),
                $students->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query(), 'pageName' => $request->ajax() ? 'page_applicants' : $pageName]
            );
        };

        $studentsAll = $processStudents($queryAll, 'page_applicants'); // no status filter — "All" legitimately shows every application
        $studentsInProgress = $processStudents($queryInProgress, 'page_applicants_in_progress', 'in_progress');
        $studentsPending = $processStudents($queryPending, 'page_applicants_pending', 'pending');
        $studentsApproved = $processStudents($queryApproved, 'page_applicants_approved', 'approved');
        $studentsRejected = $processStudents($queryRejected, 'page_applicants_rejected', 'rejected');

        // Placeholder paginator for "Not Applied" tab to avoid undefined variable in views
        $perPage = 10;
        $studentsNotApplied = new LengthAwarePaginator(
            collect(),
            0,
            $perPage,
            1,
            ['path' => $request->url(), 'query' => $request->query(), 'pageName' => 'page_applicants_not_applied']
        );

        // Collect IDs for data loading from ALL collections
        //
        // ANNOTATION: gathers every student_id appearing in ANY of the
        // five paginated tabs (deduped via ->unique()) so the next
        // block can batch-load full application/document data once
        // instead of N+1 querying per student.
        $allCollections = [$studentsAll, $studentsInProgress, $studentsPending, $studentsApproved, $studentsRejected];
        $studentIds = collect();
        foreach ($allCollections as $c)
            $studentIds = $studentIds->merge($c->getCollection()->pluck('student_id'));
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

        // Load document summaries by student and scholarship so counts are application-specific
        $documentSummaries = StudentSubmittedDocument::select(
            'user_id',
            'scholarship_id',
            DB::raw('COUNT(*) as documents_count'),
            DB::raw('MAX(updated_at) as last_uploaded')
        )
            ->whereIn('user_id', $studentIds)
            ->groupBy('user_id', 'scholarship_id')
            ->get()
            ->keyBy(function ($item) {
                return $item->user_id . '-' . $item->scholarship_id;
            });


        // Add application status information to each student in ALL collections
        //
        // ANNOTATION: for every already-paginated row (which, remember,
        // represents one student+one application), this attaches the
        // FULL set of that student's applications scoped down to the
        // SAME scholarship_id as the row's own application — i.e. it's
        // enriching each row with document/status info for that
        // specific application, not aggregating across all the
        // student's applications. `application_status` here ends up
        // being an array of ONE status (since it's filtered to a
        // single scholarship_id), which is a bit misleadingly named as
        // if it could hold multiple statuses.
        foreach ($allCollections as $collection) {
            $collection->each(function ($student) use ($applicationsData, $documentsData, $documentSummaries) {
                $studentApplications = $applicationsData
                    ->get($student->student_id, collect())
                    ->where('scholarship_id', $student->scholarship_id);
                $studentDocuments = $documentsData->get($student->student_id, collect());

                $student->applications = $studentApplications;
                $student->has_applications = $studentApplications->count() > 0;
                $student->has_documents = $student->documents_count > 0;
                $student->application_status = $studentApplications->pluck('status')->unique()->toArray();
                $student->applied_scholarships = $studentApplications->pluck('scholarship.scholarship_name')->toArray();

                $studentApplications->each(function ($application) use ($student, $documentSummaries) {
                    $documentSummaryKey = $student->student_id . '-' . $application->scholarship_id;
                    $summary = $documentSummaries->get($documentSummaryKey);
                    $application->documents_count = $summary->documents_count ?? 0;
                    $application->last_uploaded = $summary?->last_uploaded;
                });

                // Check if student has approved documents
                $student->has_approved_documents = $studentDocuments->where('evaluation_status', 'approved')->count() > 0;

                $student->applications_with_types = $studentApplications->map(function ($app) {
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
        //
        // ANNOTATION: raw flat list of Application rows (one per
        // application, NOT deduped per student) — used for whatever the
        // Blade view does with `$applications` directly. If this feeds
        // any headcount-style summary in the view/JS, it has the same
        // double-count risk as the analytics data further below.
        $applications = Application::with('user', 'scholarship')
            ->whereHas('user', function ($query) use ($campusIds) {
                $query->whereIn('campus_id', $campusIds);
            })
            ->get();

        // Get scholarship type filter from tab parameter
        $scholarshipTypeFilter = $request->get('tab', 'scholarships');

        // Build scholarships query
        $scholarshipsQuery = Scholarship::withCount([
            'applications' => function ($query) use ($campusIds) {
                $query->whereHas('user', function ($userQuery) use ($campusIds) {
                    $userQuery->whereIn('campus_id', $campusIds);
                });
            }
        ]);

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
            ->merge($sfaoCampus->getAllCampusesUnder()->map(function ($campus) {
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
        //
        // ANNOTATION: `Scholar` is its own table with one row per
        // scholar record (created in acceptEndorsed() below), so this
        // is INHERENTLY safe from the "multiple applications" double
        // count problem — a student either has a Scholar row or not,
        // regardless of how many Application rows they have.
        $scholarsQuery = Scholar::with(['user', 'scholarship', 'user.campus'])
            ->whereHas('user', function ($query) use ($campusIds) {
                $query->whereIn('campus_id', $campusIds);
            });

        // Apply campus filter for scholars
        if ($campusFilter !== 'all') {
            $scholarsQuery->whereHas('user', function ($query) use ($campusFilter) {
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
        //
        // ANNOTATION: $studentsWithApplications correctly uses
        // ->distinct('user_id')->count('user_id') to count UNIQUE
        // students, not applications — good pattern to replicate
        // elsewhere. But $pendingApplications / $approvedApplications /
        // $rejectedApplications below are raw ->count() on Application
        // rows — i.e. these are "number of applications in status X",
        // NOT "number of students with status X". A student with both
        // a pending and approved application contributes to BOTH counts.
        // Fine if the UI labels them as application counts; misleading
        // if displayed as "students".
        $countQuery = User::where('role', 'student')
            ->whereIn('campus_id', $campusIds);

        if ($campusFilter !== 'all') {
            $countQuery->where('campus_id', $campusFilter);
        }

        $totalStudents = (clone $countQuery)->count();

        $studentsWithApplications = Application::whereHas('user', function ($query) use ($campusIds, $campusFilter) {
            $query->whereIn('campus_id', $campusIds);
            if ($campusFilter !== 'all') {
                $query->where('campus_id', $campusFilter);
            }
        })
            ->distinct('user_id')
            ->count('user_id');

        $pendingApplications = Application::whereHas('user', function ($query) use ($campusIds, $campusFilter) {
            $query->whereIn('campus_id', $campusIds);
            if ($campusFilter !== 'all') {
                $query->where('campus_id', $campusFilter);
            }
        })
            ->where('status', 'pending')
            ->count();

        $approvedApplications = Application::whereHas('user', function ($query) use ($campusIds, $campusFilter) {
            $query->whereIn('campus_id', $campusIds);
            if ($campusFilter !== 'all') {
                $query->where('campus_id', $campusFilter);
            }
        })
            ->where('status', 'approved')
            ->count();

        $rejectedApplications = Application::whereHas('user', function ($query) use ($campusIds, $campusFilter) {
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
        //
        // ANNOTATION: same pattern as above, all counts here are raw
        // Application::where(...)->count() — application-level, not
        // student-level. $deptApprovedCount + $deptPendingCount +
        // $deptRejectedCount can legitimately exceed $deptStudentsCount
        // if students have multiple applications in different statuses.
        $departmentStats = [];
        foreach ($allDepartments as $dept) {
            // Count students in this department (assuming users.college stores short_name)
            $deptStudentsCount = User::where('role', 'student')
                ->whereIn('campus_id', $campusIds)
                ->where('college', $dept->short_name)
                ->count();

            // Count applications for students in this department
            $deptApplicationsCount = Application::whereHas('user', function ($query) use ($campusIds, $dept) {
                $query->whereIn('campus_id', $campusIds)
                    ->where('college', $dept->short_name);
            })
                ->count();

            $deptApprovedCount = Application::whereHas('user', function ($query) use ($campusIds, $dept) {
                $query->whereIn('campus_id', $campusIds)
                    ->where('college', $dept->short_name);
            })
                ->where('status', 'approved')
                ->count();

            $deptPendingCount = Application::whereHas('user', function ($query) use ($campusIds, $dept) {
                $query->whereIn('campus_id', $campusIds)
                    ->where('college', $dept->short_name);
            })
                ->where('status', 'pending')
                ->count();

            $deptRejectedCount = Application::whereHas('user', function ($query) use ($campusIds, $dept) {
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
        //
        // ANNOTATION: one row per STUDENT (safe, no application-level
        // fan-out) — used for the gender breakdown chart client-side.
        $allStudentsData = User::where('role', 'student')
            ->whereIn('campus_id', $campusIds)
            ->select('campus_id', 'college', 'sex')
            ->get();

        $analytics['all_students_data'] = $allStudentsData;

        // 4. All Applications Data for Client-side Filtering (Scholarship Type Chart & Stacked Bar)
        //
        // *** ANNOTATION: THIS IS LIKELY WHERE YOUR DONUT-CHART BUG
        // COMES FROM. ***
        // This is a flat SQL JOIN of applications x users x scholarships
        // — i.e. ONE ROW PER APPLICATION, not per student. It is NOT
        // deduped by user_id, and it does NOT apply the
        // approved>in_progress/pending>rejected priority rule anywhere.
        // If createCollegeChart() / createGranularDonutChart() in
        // sfao-script.js count "scholars" or "students" by iterating
        // this array and tallying by status, a student with a pending
        // application to Scholarship A and an approved application to
        // Scholarship B will produce TWO rows here and get counted
        // TWICE in the chart. This is your bug.
        //
        // Two ways to fix, pick one:
        //   (a) Backend: dedupe this collection by user_id here,
        //       keeping only the highest-priority row per student
        //       (using $this->statusSortPriority()), before assigning
        //       it to $analytics['all_applications_data'].
        //   (b) Frontend: in sfao-script.js, group these rows by a
        //       student identifier (note: this select does NOT include
        //       user_id currently — you'd need to add
        //       'users.id as user_id' to the select list below to do
        //       this reliably) and pick one row per student using the
        //       same priority order before tallying into the donut
        //       chart.
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
                    //case 'not_applied':
                    // $studentsList = $studentsNotApplied;
                    // break;
                    case 'in_progress':
                        $studentsList = $studentsInProgress;
                        break;
                    case 'pending':
                        $studentsList = $studentsPending;
                        break;
                    case 'approved':
                        $studentsList = $studentsApproved;
                        break;
                    case 'rejected':
                        $studentsList = $studentsRejected;
                        break;
                }

                return response()->json([
                    'html' => view('sfao.partials.tabs.applicants_list', ['students' => $studentsList])->render(),
                    'counts' => [
                        'total' => $studentsAll->total(),
                        'pending' => $studentsPending->total(),
                        'in_progress' => $studentsInProgress->total(),
                        'rejected' => $studentsRejected->total(),
                        //'not_applied' => $studentsNotApplied->total(),
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
                    $filteredScholarships = $filteredScholarships->filter(function ($s) use ($typeFilter) {
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

                $paginatedScholarships->getCollection()->each(function ($scholarship) {
                    if ($scholarship->slots_available && $scholarship->slots_available > 0) {
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
        //
        // ANNOTATION: merges known "alias" labels for the same college
        // (e.g. the old long name, 'CABE', 'CABEIHM') into a single
        // filter option whose `value` is a pipe-delimited list of all
        // the raw variants — this is what $collegeFilter's explode('|')
        // handling elsewhere in the controller is designed to consume.
        $rawColleges = User::where('role', 'student')
            ->whereIn('campus_id', $campusIds)
            ->whereNotNull('college')
            ->distinct()
            ->pluck('college');

        $mergedColleges = [];
        foreach ($rawColleges as $c) {
            $label = $c;
            if (
                $c === 'College of Accountancy, Business, Economics, International Hospitality Management' ||
                $c === 'CABE' ||
                $c === 'CABEIHM'
            ) {
                $label = 'CABEIHM';
            }
            if (!isset($mergedColleges[$label])) {
                $mergedColleges[$label] = [];
            }
            $mergedColleges[$label][] = $c;
        }

        $colleges = collect($mergedColleges)->map(function ($values, $label) {
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
        //
        // ANNOTATION: driver-aware SQL (Postgres vs MySQL syntax) to
        // extract year/month from created_at, then maps each
        // application's created_at into an "AY YYYY-YYYY" label
        // assuming the academic year starts in August. Same August-start
        // assumption used in the academicYearFilter handling above.
        $yearExpression = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql'
            ? 'EXTRACT(YEAR FROM created_at)::integer'
            : 'YEAR(created_at)';
        $monthExpression = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql'
            ? 'EXTRACT(MONTH FROM created_at)::integer'
            : 'MONTH(created_at)';
        $academicYears = Application::whereHas('user', function ($q) use ($campusIds) {
            $q->whereIn('campus_id', $campusIds);
        })
            ->selectRaw("{$yearExpression} as year, {$monthExpression} as month")
            ->distinct()
            ->get()
            ->map(function ($app) {
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
     *
     * ANNOTATION: an older/simpler alternative applicants view, keyed
     * off `student_submitted_documents` rather than `applications` —
     * lists students who've uploaded at least one sfao_required
     * document, one row per student (grouped by user id/name/email/
     * campus_id). No application-status dedup concerns since this
     * isn't status-based at all.
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

        return view('sfao.partials.tabs.applicants', compact('students', 'sfaoCampus'));
    }

    /**
     * View student documents (SFAO)
     *
     * ANNOTATION: simple lookup, one student's sfao_required documents.
     * No dedup concerns.
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
     * Approve application (SFAO) - Sets to in_progress for admin final review
     *
     * ANNOTATION: SFAO "approving" doesn't actually set status to
     * 'approved' — it moves it to 'in_progress' so Central can do the
     * final approval (see acceptEndorsed() below, which is what
     * actually sets 'approved'). Important distinction if you're ever
     * tracing "why isn't this application showing as approved yet."
     */
    public function sfaoApproveApplication($id)
    {
        return $this->updateApplicationStatus($id, 'in_progress', 'sfao');
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
     *
     * ANNOTATION: only allowed from 'approved' status. grant_count is
     * computed via Application::getNextGrantCount() (defined on the
     * model, not shown here) — presumably counts prior claimed grants
     * for this (user, scholarship) pair and increments.
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
     *
     * ANNOTATION: unlike SFAO's version, Central approval DOES set
     * status to 'approved' directly. (Though note acceptEndorsed()
     * below is the more fully-featured accept flow that also creates a
     * Scholar record — this simple method looks like it may be a
     * legacy/alternate path that bypasses Scholar creation. Worth
     * checking your routes to see if this is still wired to anything.)
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
     *
     * ANNOTATION: simple helper, all of a single student's applications
     * in reverse chronological order. No dedup needed — the student
     * legitimately wants to see every application they've made.
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
     *
     * ANNOTATION: unused-looking utility method (sfaoDashboard() builds
     * its own $applications inline rather than calling this) — flat,
     * one row per application, scoped to campus jurisdiction.
     */
    public function getSfaoApplications($campusIds)
    {
        return Application::with('user', 'scholarship')
            ->whereHas('user', function ($query) use ($campusIds) {
                $query->whereIn('campus_id', $campusIds);
            })
            ->get();
    }

    /**
     * Central Dashboard - Only shows scholars (selected students), not applicants
     *
     * ANNOTATION: Central Admin's equivalent of sfaoDashboard(). Builds
     * the applications table, scholarships tabs, reports tabs, scholars
     * tabs (scholarsAll/scholarsNew/scholarsOld — Scholar-table-backed,
     * inherently deduped), qualifiedApplicants, endorsedApplicants, and
     * rejectedApplicants sections, then calls generateAnalyticsData()
     * for the big chart/stat payload (see that method for the other
     * half of your donut-chart bug).
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
        //
        // ANNOTATION: lets a URL like ?tabs=main_campus_statistics
        // implicitly scope everything to that one campus by matching
        // the slugified campus name against the tab name.
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
        //
        // ANNOTATION: flat Application list (one row per application),
        // filtered by status/campus/scholarship and sorted — feeds
        // whatever the Central "Applications" tab table shows. Since
        // this is meant to be a literal applications table (not a
        // student headcount), one row per application is correct here.
        $applicationsQuery = Application::with(['user', 'scholarship', 'user.campus'])
            ->whereHas('user', function ($query) {
                $query->where('role', 'student');
            });

        // Apply status filter
        if ($statusFilter !== 'all') {
            $applicationsQuery->where('status', $statusFilter);
        }
        // If 'all' is selected, don't apply any status filter to show all statuses

        // Apply campus filter
        if ($campusFilter !== 'all') {
            $applicationsQuery->whereHas('user', function ($query) use ($campusFilter) {
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
                $startYear = (int) $years[0];
                $endYear = (int) $years[1];
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
        //
        // ANNOTATION: delegates to generateAnalyticsData() below — see
        // that method's inline notes for the other half of your
        // donut-chart bug (its own $allApplicationsData is likewise
        // application-level, not deduped per student).
        $analytics = $this->generateAnalyticsData(['campus' => $campusFilter]);

        // Get all campuses for filter (Moved to top)
        // $campuses = \App\Models\Campus::all();

        // Get filter options for applications
        $campusOptions = $campuses->map(function ($campus) {
            return [
                'id' => $campus->id,
                'name' => $campus->name
            ];
        })->toArray();

        $scholarshipOptions = Scholarship::all()->map(function ($scholarship) {
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
        //
        // ANNOTATION: again, Scholar-table-backed — inherently one row
        // per scholar, safe from the multi-application dedup issue.
        $scholarsQuery = Scholar::with(['user', 'scholarship', 'user.campus']);

        // Apply filters (shared filters like campus, status, etc.)
        // Apply campus filter for scholars
        if ($campusFilter !== 'all') {
            $scholarsQuery->whereHas('user', function ($query) use ($campusFilter) {
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
        //
        // ANNOTATION: "qualified" = has at least one approved
        // application AND is not already a Scholar. This is a
        // per-STUDENT list (whereHas/whereDoesntHave on User), so a
        // student won't appear twice here just for having multiple
        // approved applications.
        $qualifiedApplicantsQuery = User::with(['applications.scholarship', 'campus'])
            ->where('role', 'student')
            ->whereHas('applications', function ($query) {
                $query->where('status', 'approved');
            })
            ->whereDoesntHave('scholars'); // Not already a scholar

        // Apply campus filter for qualified applicants
        if ($campusFilter !== 'all') {
            $qualifiedApplicantsQuery->where('campus_id', $campusFilter);
        }

        // Apply scholarship filter for qualified applicants
        if ($scholarshipFilter !== 'all') {
            $qualifiedApplicantsQuery->whereHas('applications', function ($query) use ($scholarshipFilter) {
                $query->where('scholarship_id', $scholarshipFilter);
            });
        }

        // Apply sorting for qualified applicants
        switch ($sortBy) {
            case 'name':
                $qualifiedApplicantsQuery->orderBy('users.name', $sortOrder);
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
        //
        // ANNOTATION: "endorsed" = Application-level (status in_progress,
        // no linked Scholar yet), one row per APPLICATION rather than
        // per student — since this is Central's action queue for
        // deciding accept/reject per application, one row per
        // application is the correct shape here (mirrors viewApplicants()
        // above).
        $endorsedApplicantsQuery = Application::with(['user', 'scholarship', 'user.campus'])
            ->where('status', 'in_progress')
            ->whereDoesntHave('scholar'); // Not already converted into a scholar record

        // Apply campus filter for endorsed applicants
        if ($campusFilter !== 'all') {
            $endorsedApplicantsQuery->whereHas('user', function ($query) use ($campusFilter) {
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
        //
        // ANNOTATION: RejectedApplicant is its own audit-trail table
        // (populated by rejectEndorsed() below) — one row per rejection
        // event, not derived from Application.status directly.
        $rejectedApplicants = \App\Models\RejectedApplicant::with(['user', 'scholarship', 'rejectedByUser'])
            ->where('rejected_by', 'central')
            ->orderBy('rejected_at', 'desc')
            ->get();

        return view('central.analytics.index', compact('user', 'applications', 'scholarshipsAll', 'scholarshipsPrivate', 'scholarshipsGov', 'reportStats', 'analytics', 'reportsSubmitted', 'reportsReviewed', 'reportsApproved', 'reportsRejected', 'campuses', 'campusOptions', 'scholarshipOptions', 'statusOptions', 'sortBy', 'sortOrder', 'statusFilter', 'campusFilter', 'scholarshipFilter', 'scholars', 'scholarsAll', 'scholarsNew', 'scholarsOld', 'qualifiedApplicants', 'endorsedApplicants', 'rejectedApplicants', 'totalReports', 'academicYearOptions', 'academicYearFilter'));
    }

    /**
     * ANNOTATION: AJAX endpoint used when the Central analytics filters
     * (campus/college/program/track/scholarship/time period) change
     * without a full page reload — just re-runs generateAnalyticsData()
     * with the new filters and returns JSON. Any dedup fix you make
     * inside generateAnalyticsData() automatically applies here too.
     */
    public function getFilteredAnalytics(Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $filters = $request->input('filters', []);
        $analytics = $this->generateAnalyticsData($filters);

        return response()->json([
            'success' => true,
            'analytics' => $analytics,
            'counts' => $analytics['counts'] ?? null,
        ]);
    }

    /**
     * Generate comprehensive analytics data for the statistics dashboard
     *
     * ANNOTATION: shared by centralDashboard() (server-rendered) and
     * getFilteredAnalytics() (AJAX). This is the single largest method
     * in the file. General pattern to watch for as you read through:
     *   - Anything built from `Scholar::` / `$scholarQuery` is
     *     inherently per-scholar (one row per scholar record) → safe.
     *   - Anything built from `Application::` counts/collections is
     *     per-APPLICATION → a student with N applications in different
     *     statuses contributes to N different status buckets. Fine for
     *     "how many applications" metrics, NOT fine if displayed/used
     *     as "how many students."
     *   - The one exception worth flagging in bold: $allApplicationsData
     *     near the bottom, which is the flat join used for client-side
     *     chart building — same issue as sfaoDashboard()'s
     *     $analytics['all_applications_data'].
     */
    private function generateAnalyticsData($filters = [])
    {
        // Extract filter values
        $timePeriod = $filters['timePeriod'] ?? $filters['academicYear'] ?? 'all';
        $campusId = $filters['campus'] ?? 'all';
        $college = $filters['college'] ?? 'all';
        $program = $filters['program'] ?? 'all';
        $track = $filters['track'] ?? 'all';
        $scholarshipId = $filters['scholarship'] ?? 'all';

        // Build base query conditions
        $applicationQuery = Application::query();
        $userQuery = User::query();
        $reportQuery = \App\Models\Report::query();
        $scholarQuery = Scholar::query();

        // Apply time period filter
        //
        // ANNOTATION: getDateCondition() below only recognizes
        // 'this_month' / 'last_3_months' / 'this_year' — any other
        // value (including academic-year-style strings like
        // "2023-2024") falls through to `default => null`, silently
        // skipping the date filter. Worth confirming the frontend never
        // actually sends an academic-year string here expecting it to
        // work, since $filters['academicYear'] is explicitly read above
        // as a possible source for $timePeriod.
        $dateCondition = null;
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
            $applicationQuery->whereHas('user', function ($query) use ($campusId) {
                $query->where('campus_id', $campusId);
            });
            $userQuery->where('campus_id', $campusId);
            $reportQuery->where('campus_id', $campusId);
            $scholarQuery->whereHas('user', function ($query) use ($campusId) {
                $query->where('campus_id', $campusId);
            });
        }

        if ($college !== 'all') {
            $applicationQuery->whereHas('user', function ($query) use ($college) {
                $query->where('college', $college);
            });
        }

        if ($program !== 'all') {
            $applicationQuery->whereHas('user', function ($query) use ($program) {
                $query->where('program', $program);
            });
        }

        if ($track !== 'all') {
            $applicationQuery->whereHas('user', function ($query) use ($track) {
                $query->where('track', $track);
            });
        }

        if ($scholarshipId !== 'all') {
            $applicationQuery->where('scholarship_id', $scholarshipId);
        }

        // Get basic report statistics
        $totalReports = $reportQuery->count();
        $submittedReports = $reportQuery->where('status', 'submitted')->count();
        $approvedReports = $reportQuery->where('status', 'approved')->count();
        $rejectedReports = $reportQuery->where('status', 'rejected')->count();
        $draftReports = $reportQuery->where('status', 'draft')->count();
        $pendingReviews = $reportQuery->where('status', 'submitted')->count();

        // Get comprehensive application statistics
        //
        // ANNOTATION: these are raw APPLICATION counts (not deduped per
        // student) — same caveat as everywhere else: sum of
        // approved+rejected+pending+in_progress+claimed can exceed the
        // number of unique applicants.
        $totalApplications = $applicationQuery->count();
        $approvedApplications = (clone $applicationQuery)->where('status', 'approved')->count();
        $rejectedApplications = (clone $applicationQuery)->where('status', 'rejected')->count();
        $pendingApplications = (clone $applicationQuery)->where('status', 'pending')->count();
        $inProgressApplications = (clone $applicationQuery)->where('status', 'in_progress')->count();
        $claimedApplications = (clone $applicationQuery)->where('status', 'claimed')->count();
        $activeApplications = $pendingApplications + $inProgressApplications;

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
        //
        // ANNOTATION: Scholar-table-backed — safe, one row per scholar.
        $newScholars = (clone $scholarQuery)->where('type', 'new')->count();
        $oldScholars = (clone $scholarQuery)->where('type', 'old')->count();

        // Get demographic statistics from scholars
        //
        // ANNOTATION: also Scholar-table-backed — safe. Note these are
        // "scholar" counts, i.e. only students who've been formally
        // accepted as scholars, not all applicants.
        $maleStudents = (clone $scholarQuery)->whereHas('user', function ($q) {
            $q->where('sex', 'male');
        })->count();
        $femaleStudents = (clone $scholarQuery)->whereHas('user', function ($q) {
            $q->where('sex', 'female');
        })->count();
        $studentsWithApplications = User::where('role', 'student')
            ->whereHas('applications')
            ->count();
        $studentsWithoutApplications = $totalStudents - $studentsWithApplications;

        // Get application status by gender (using users table)
        //
        // ANNOTATION: back to raw Application counts — same
        // per-application (not per-student) caveat applies to all of
        // the male/female application counts below.
        $maleApplications = Application::whereHas('user', function ($query) {
            $query->where('sex', 'male');
        })->count();
        $femaleApplications = Application::whereHas('user', function ($query) {
            $query->where('sex', 'female');
        })->count();

        $maleApprovedApplications = Application::whereHas('user', function ($query) {
            $query->where('sex', 'male');
        })->where('status', 'approved')->count();
        $femaleApprovedApplications = Application::whereHas('user', function ($query) {
            $query->where('sex', 'female');
        })->where('status', 'approved')->count();

        $maleRejectedApplications = Application::whereHas('user', function ($query) {
            $query->where('sex', 'male');
        })->where('status', 'rejected')->count();
        $femaleRejectedApplications = Application::whereHas('user', function ($query) {
            $query->where('sex', 'female');
        })->where('status', 'rejected')->count();

        $malePendingApplications = Application::whereHas('user', function ($query) {
            $query->where('sex', 'male');
        })->where('status', 'pending')->count();
        $femalePendingApplications = Application::whereHas('user', function ($query) {
            $query->where('sex', 'female');
        })->where('status', 'pending')->count();

        // Get year level distribution from scholars
        //
        // ANNOTATION: Scholar-backed via whereHas('scholars') on User —
        // safe, one row per student-with-a-scholar-record.
        $scholarUserQuery = User::whereHas('scholars');

        // Apply filters to scholar user query
        if ($timePeriod !== 'all' && $dateCondition) {
            $scholarUserQuery->whereHas('scholars', function ($q) use ($dateCondition) {
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
        //
        // ANNOTATION: same normalization pattern repeated later inside
        // the per-campus loop ($campusYearLevelMapping) — collapses
        // inconsistent historical data entry ("1st", "First Year",
        // "1st Year") into one canonical label per year level.
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
        //
        // ANNOTATION: Scholar-backed, top 10 by count — safe.
        $programStats = (clone $scholarUserQuery)
            ->selectRaw('program, COUNT(*) as count')
            ->groupBy('program')
            ->orderBy('count', 'desc')
            ->limit(10) // Top 10 programs
            ->get();

        $programLabels = $programStats->pluck('program')->toArray();
        $programCounts = $programStats->pluck('count')->toArray();

        // Get application status by year level (using forms table)
        //
        // ANNOTATION: for each standardized year level, pulls every
        // Application whose user's `year_level` matches ANY of the raw
        // variant labels that normalize to that standard level, then
        // tallies statuses. Again, per-APPLICATION not per-student —
        // approved+rejected+pending+claimed can exceed total unique
        // students in that year level if some have multiple
        // applications.
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
            $yearLevelApplications = Application::whereHas('user', function ($query) use ($yearLevelVariations) {
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
        //
        // ANNOTATION: builds 6 parallel arrays (one entry per of the
        // last 6 months) for reports/applications/approved/rejected —
        // this is the data behind whatever monthly trend line chart
        // exists in the Central analytics view. All Application-based
        // counts here, so again per-application granularity, but a
        // monthly trend of "applications submitted" is arguably the
        // CORRECT metric to show as application-level (you generally do
        // want to count every application submission event, not
        // collapse them per student) — flagging this one as probably
        // fine as-is, unlike the donut/pie chart use case.
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
        $campuses = Campus::withCount([
            'reports',
            'users' => function ($query) {
                $query->where('role', 'student');
            }
        ])->get();

        $campusNames = $campuses->pluck('name')->toArray();
        $campusReports = $campuses->pluck('reports_count')->toArray();
        $campusStudents = $campuses->pluck('users_count')->toArray();

        // Get campus application statistics
        //
        // ANNOTATION: big per-campus loop, computing an entire nested
        // analytics block (applications, scholars, gender, year level,
        // program, monthly trends, per-scholarship new/old scholar
        // counts) for EACH campus. Watch for the note further down
        // about $campusStudentsCount — this is the variable that used
        // to be named $campusStudents and shadowed the outer
        // $campusStudents array (already fixed, per your project
        // history — the comment below documents that fix).
        $campusApplicationStats = [];
        foreach ($campuses as $campus) {
            // ANNOTATION: $campusApplications is a flat, per-application
            // collection (not deduped per student) — every count
            // derived from it below (approved/rejected/pending/claimed)
            // is an application count, not a student count.
            $campusApplications = Application::whereHas('user', function ($query) use ($campus) {
                $query->where('campus_id', $campus->id);
            })->get();

            // Get scholar stats for campus
            //
            // ANNOTATION: Scholar-backed — safe.
            $campusScholarsQuery = Scholar::whereHas('user', function ($query) use ($campus) {
                $query->where('campus_id', $campus->id);
            });
            $campusNewScholars = (clone $campusScholarsQuery)->where('type', 'new')->count();
            $campusOldScholars = (clone $campusScholarsQuery)->where('type', 'old')->count();

            // Get total students for this campus (uses a locally scoped variable so it
            // no longer overwrites the outer $campusStudents array used for the
            // overall campus chart above).
            //
            // ANNOTATION: this comment documents the fix for the
            // variable-shadowing bug mentioned in your project history
            // — $campusStudentsCount is now local to this loop
            // iteration and no longer clobbers the outer $campusStudents
            // array (plural, built above from ->pluck('users_count')).
            $campusStudentsCount = User::where('role', 'student')
                ->where('campus_id', $campus->id)
                ->count();

            // Get students with applications for this campus
            $campusStudentsWithApplications = User::where('role', 'student')
                ->where('campus_id', $campus->id)
                ->whereHas('applications')
                ->count();

            // Get gender statistics for this campus
            // Get gender statistics for this campus (Scholars only)
            $campusMaleStudents = (clone $campusScholarsQuery)->whereHas('user', function ($q) {
                $q->where('sex', 'male');
            })->count();

            $campusFemaleStudents = (clone $campusScholarsQuery)->whereHas('user', function ($q) {
                $q->where('sex', 'female');
            })->count();

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

                $campusMonthlyApplications = Application::whereHas('user', function ($query) use ($campus) {
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
                'total_students' => $campusStudentsCount,
                'total_applications' => $campusApplications->count(),
                'approved_applications' => $campusApplications->where('status', 'approved')->count(),
                'rejected_applications' => $campusApplications->where('status', 'rejected')->count(),
                'pending_applications' => $campusApplications->where('status', 'pending')->count(),
                'claimed_applications' => $campusApplications->where('status', 'claimed')->count(),
                'students_with_applications' => $campusStudentsWithApplications,
                'students_without_applications' => $campusStudentsCount - $campusStudentsWithApplications,
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
                    'scholars as new_scholars_count' => function ($q) use ($campus) {
                        $q->where('type', 'new')->whereHas('user', fn($u) => $u->where('campus_id', $campus->id));
                    },
                    'scholars as old_scholars_count' => function ($q) use ($campus) {
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
        //
        // ANNOTATION: per-scholarship rollup using withCount() — this is
        // scoped by scholarship, so "applications_count" here means
        // "how many applications exist for this scholarship", which is
        // a legitimate application-level metric (a scholarship's fill
        // rate naturally cares about every application to it, not
        // unique students across ALL scholarships).
        $scholarshipPerformance = Scholarship::withCount([
            'applications',
            'applications as approved_applications_count' => function ($query) {
                $query->where('status', 'approved');
            }
        ])->get()->map(function ($scholarship) {
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
        //
        // ANNOTATION: this is the raw application-level status
        // breakdown used to feed whatever "overall status" pie/donut
        // chart exists in the Central analytics view — same caveat as
        // sfaoDashboard()'s equivalent: these four numbers ($approved +
        // $rejected + $pending + $claimed) don't collapse per student,
        // so if this specific object is what feeds a "students by
        // status" style chart (as opposed to an "applications by
        // status" chart), it has the same double-count risk.
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
        $overallApprovalRate = $totalApplications > 0 ? round(($approvedApplications / $totalApplications) * 100, 1) : 0;
        $overallRejectionRate = $totalApplications > 0 ? round(($rejectedApplications / $totalApplications) * 100, 2) : 0;
        $approvalRateDisplay = number_format($overallApprovalRate, 1, '.', '');

        $counts = [
            'total' => $totalApplications,
            'approved' => $approvedApplications,
            'rejected' => $rejectedApplications,
            'active' => $activeApplications,
            'approvalRate' => $approvalRateDisplay,
        ];

        // Data required by the Alpine.js frontend for dropdowns, charts, and
        // client-side fallback counting.
        //
        // ANNOTATION: $allStudentsData below is one row per student —
        // safe.
        $rawStudentQuery = User::where('role', 'student');
        if ($campusId !== 'all') {
            $rawStudentQuery->where('campus_id', $campusId);
        }

        $allStudentsData = (clone $rawStudentQuery)
            ->select('campus_id', 'college', 'sex')
            ->get();

        // *** ANNOTATION: THE OTHER HALF OF YOUR DONUT-CHART BUG. ***
        // Same shape as sfaoDashboard()'s $allApplicationsData — a flat
        // join of applications x users x scholarships (plus a left join
        // to scholars here), ONE ROW PER APPLICATION, not deduped by
        // user. This version DOES include 'users.id as user_id' in its
        // select list (good — that makes a frontend dedup-by-user_id
        // fix straightforward here, unlike the SFAO version which is
        // missing user_id). If createCollegeChart() /
        // createGranularDonutChart() consume this array (via
        // all_applications_data below) for the Central dashboard's
        // donut chart, the same "student with pending + approved
        // applications counted twice" bug applies here too.
        //
        // Recommended fix: before returning this array, group by
        // user_id and keep only the row whose `status` has the highest
        // priority per $this->statusSortPriority(), e.g.:
        //   $priority = $this->statusSortPriority();
        //   $allApplicationsData = $allApplicationsData
        //       ->groupBy('user_id')
        //       ->map(fn($rows) => $rows->sortBy(fn($r) => $priority[$r->status] ?? 999)->first())
        //       ->values();
        // (Do this AFTER the query, since a student's applications can
        // span different scholarships/colleges which are also columns
        // in the same select — decide whether you want the winning
        // row's scholarship/college context, which this approach
        // preserves correctly.)
        $allApplicationsData = Application::join('users', 'applications.user_id', '=', 'users.id')
            ->join('scholarships', 'applications.scholarship_id', '=', 'scholarships.id')
            ->leftJoin('scholars', function ($join) {
                $join->on('scholars.user_id', '=', 'users.id')
                    ->on('scholars.scholarship_id', '=', 'applications.scholarship_id');
            })
            ->when($campusId !== 'all', fn($q) => $q->where('users.campus_id', $campusId))
            ->select(
                'users.id as user_id',
                'users.campus_id',
                'users.college',
                'users.program',
                'users.track',
                'users.first_name',
                'users.middle_name',
                'users.last_name',
                'users.sr_code',
                'scholarships.scholarship_type',
                'scholarships.scholarship_name',
                'applications.status',
                'applications.created_at',
                'scholars.id as scholar_id',
                'scholars.type as scholar_type',
                'scholars.status as scholar_status'
            )
            ->get();

        $availableScholarships = Scholarship::select('id', 'scholarship_name')->get();
        $allColleges = \App\Models\Department::select('id', 'short_name')->get();

        // ANNOTATION: builds two lookup structures for cascading
        // dropdowns in the frontend filters: which colleges exist per
        // campus, and which programs exist per (campus, college) pair.
        $campusColleges = [];
        $campusCollegePrograms = [];
        foreach (Campus::with('departments')->get() as $camp) {
            $shortNames = $camp->departments->pluck('short_name')->toArray();
            $campusColleges[$camp->id] = $shortNames;

            $programsByCollege = [];
            foreach ($shortNames as $shortName) {
                $programsByCollege[$shortName] = User::where('role', 'student')
                    ->where('campus_id', $camp->id)
                    ->where('college', $shortName)
                    ->whereNotNull('program')
                    ->distinct()
                    ->pluck('program')
                    ->values()
                    ->toArray();
            }
            $campusCollegePrograms[$camp->id] = $programsByCollege;
        }

        // ANNOTATION: builds a program -> [tracks] lookup for another
        // cascading dropdown (program picked -> track options narrow).
        $programTracks = [];
        foreach (
            User::where('role', 'student')
                ->whereNotNull('program')->whereNotNull('track')
                ->select('program', 'track')->distinct()->get() as $row
        ) {
            $programTracks[$row->program][] = $row->track;
        }
        foreach ($programTracks as $program => $tracks) {
            $programTracks[$program] = array_values(array_unique($tracks));
        }

        // ANNOTATION: final assembled payload returned to both
        // centralDashboard() (as $analytics, passed to the Blade view)
        // and getFilteredAnalytics() (as JSON for AJAX filter updates).
        // If you add a dedup step for all_applications_data per the
        // note above, just reassign $allApplicationsData before this
        // return array is built — no other changes needed here.
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
                'scholars as new_scholars_count' => function ($q) use ($campusId) {
                    $q->where('type', 'new');
                    if ($campusId !== 'all')
                        $q->whereHas('user', fn($u) => $u->where('campus_id', $campusId));
                },
                'scholars as old_scholars_count' => function ($q) use ($campusId) {
                    $q->where('type', 'old');
                    if ($campusId !== 'all')
                        $q->whereHas('user', fn($u) => $u->where('campus_id', $campusId));
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
            'grant_type_data' => $grantTypeData,

            // Summary counts for the stats cards
            'counts' => $counts,

            'all_applications_data' => $allApplicationsData,
            'all_students_data' => $allStudentsData,
            'available_scholarships' => $availableScholarships,
            'all_colleges' => $allColleges,
            'campus_colleges' => $campusColleges,
            'campus_college_programs' => $campusCollegePrograms,
            'program_tracks' => $programTracks,
        ];
    }

    /**
     * Sort scholarships based on various criteria
     *
     * ANNOTATION: generic in-memory sort helper used by both
     * sfaoDashboard() and centralDashboard() for their scholarships
     * tabs. Operates on an already-fetched Collection (not a query
     * builder), sorting by whichever field name is passed in. Unrelated
     * to application/student dedup.
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
     *
     * ANNOTATION: unused-looking utility (centralDashboard() builds its
     * own $applications inline) — flat list, all applications globally,
     * no campus scoping (unlike getSfaoApplications() above).
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
     *
     * ANNOTATION: only handles 3 named time periods; anything else
     * (including 'all', or an unrecognized string) returns null, which
     * generateAnalyticsData() treats as "no date filter applied."
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
     *
     * ANNOTATION: first step of the 4-stage SFAO document evaluation
     * workflow. Loads one student + all their applications so the SFAO
     * user can pick which application (scholarship) to evaluate next.
     * Includes a jurisdiction check (student's campus must be within
     * this SFAO admin's monitored campuses).
     */
    public function showEvaluation($userId)
    {
        if (!session()->has('user_id') || session('role') !== 'sfao') {
            return redirect('/login')->with('session_expired', true);
        }

        $student = User::with(['applications.scholarship', 'campus'])->findOrFail($userId);

        // Get SFAO admin's campus to verify jurisdiction
        $sfaoAdmin = User::with('campus')->find(session('user_id'));
        if (!$sfaoAdmin || !$sfaoAdmin->campus) {
            return redirect('/login')->with('error', 'User campus not assigned.');
        }
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
     *
     * ANNOTATION: loads only the 'sfao_required' category documents for
     * a specific (student, scholarship) pair — scoped to a single
     * application, not aggregated across the student's other
     * applications.
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
        if (!$sfaoAdmin || !$sfaoAdmin->campus) {
            return redirect('/login')->with('error', 'User campus not assigned.');
        }
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
     *
     * ANNOTATION: bulk-updates evaluation_status/evaluated_by/
     * evaluated_at for a batch of sfao_required documents belonging to
     * one (student, scholarship) pair, then redirects to Stage 3.
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
     *
     * ANNOTATION: same as Stage 2 but for the 'scholarship_required'
     * document category instead of 'sfao_required'.
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
        if (!$sfaoAdmin || !$sfaoAdmin->campus) {
            return redirect('/login')->with('error', 'User campus not assigned.');
        }
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
     *
     * ANNOTATION: IMPORTANT — this is a DIFFERENT priority rule than
     * statusSortPriority() above. This one is about DOCUMENT evaluation
     * statuses within a single application (reject beats pending beats
     * approve, i.e. "any bad document sinks the whole application"),
     * whereas statusSortPriority() is about APPLICATION statuses across
     * a student's multiple applications (approved beats in_progress
     * beats pending beats rejected, i.e. "best outcome wins for
     * display"). Don't conflate the two when refactoring — they're
     * intentionally inverted in spirit (one is "worst wins", the other
     * is "best wins") because they answer different questions.
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
     * Map the SFAO review action to the application status shown in the workflow.
     * SFAO moves an application to in progress; only the admin can mark it approved.
     *
     * ANNOTATION: this helper doesn't appear to be called anywhere else
     * in this file — submitFinalEvaluation() below inlines its own
     * equivalent match() expression instead of calling this. Possibly
     * dead code, or called from a route/view not shown here — worth
     * grepping your codebase for resolveSfaoApplicationStatus( to
     * confirm before removing it.
     */
    private function resolveSfaoApplicationStatus(string $action, ?string $fallbackStatus = null): string
    {
        return match ($action) {
            'reject' => 'rejected',
            'approve', 'pending' => 'in_progress',
            default => $fallbackStatus ?? 'in_progress',
        };
    }

    /**
     * Show final review - Stage 4
     *
     * ANNOTATION: loads ALL documents (both categories) for a
     * (student, scholarship) pair, splits them into sfaoDocuments /
     * scholarshipDocuments collections for display, and computes the
     * suggested autoDecision using determineAutoDecision() so the SFAO
     * reviewer sees a pre-filled recommendation before submitting.
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
        if (!$sfaoAdmin || !$sfaoAdmin->campus) {
            return redirect('/login')->with('error', 'User campus not assigned.');
        }
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
     *
     * ANNOTATION: the actual state-changing step of the 4-stage
     * workflow. Re-runs determineAutoDecision() (document-level
     * priority: reject > pending > approve) to decide the application's
     * new status, updates the Application row, then builds and sends a
     * student-facing Notification with a decision-specific title and
     * message (including a rundown of any pending/rejected document
     * names when the outcome is 'pending'). This is where an
     * application transitions from SFAO's queue into either 'rejected',
     * 'pending', or 'in_progress' (forwarded to Central).
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

        // Update application with remarks and status (SFAO approval sets to in_progress for Central review)
        $newStatus = match ($action) {
            'approve' => 'in_progress',
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

        // Ensure scholarship relationship is loaded for notification text
        $application->load('scholarship');
        $scholarshipName = $application->scholarship->scholarship_name ?? 'the scholarship';

        // Create notification for student
        $notificationTitle = match ($action) {
            'approve' => 'Application Forwarded for Central Review',
            'reject' => 'Application Rejected',
            'pending' => 'Application Status Updated',
            default => 'Application Status Updated'
        };

        $notificationMessage = match ($action) {
            'approve' => 'Your application for ' . $scholarshipName . ' has been approved by SFAO. It is now forwarded to Central Administration for final review.',
            'reject' => 'Your application for ' . $scholarshipName . ' has been rejected based on document evaluation.',
            'pending' => 'Your application for ' . $scholarshipName . ' is now in progress after SFAO evaluation.',
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
                'scholarship_name' => $scholarshipName,
                'status' => $newStatus,
                'remarks' => $request->remarks,
                'document_status' => $documentStatus,
                'pending_documents' => $pendingDocuments,
                'rejected_documents' => $rejectedDocuments,
            ]
        ]);

        $message = match ($action) {
            'approve' => 'Application approved and forwarded to Central Administration for final review.',
            'reject' => 'Application rejected successfully based on document evaluation.',
            'pending' => 'Application moved to in progress successfully after SFAO evaluation.',
            default => 'Application status updated successfully.'
        };

        return redirect()->route('sfao.dashboard')
            ->with('success', $message);
    }


    /**
     * Show validation page for an endorsed (approved) application for Central admin.
     *
     * ANNOTATION: loads a single Application plus its student, scholarship,
     * and submitted documents so Central can review before accepting or
     * rejecting it (see acceptEndorsed / rejectEndorsed below). Uses
     * Laravel route-model binding (Application $application) instead of
     * a raw $id, unlike most other methods in this file.
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

        return view('central.endorsed.validate', compact('application', 'user', 'scholarship', 'submittedDocuments'));
    }

    /**
     * Accept an endorsed application (Central)
     * @param Application $application
     *
     * ANNOTATION: THIS is the "real" Central approval flow (contrast
     * with the thin centralApproveApplication() wrapper above). Only
     * works on 'in_progress' applications (i.e. ones SFAO already
     * endorsed). Guards against creating a duplicate Scholar record for
     * the same (user, scholarship) pair. Sets application status to
     * 'approved', THEN creates a new Scholar record with type='new' and
     * computed start/end dates (1 year if renewal_allowed, else 6
     * months). This is the single place in the whole controller where
     * a Scholar row gets created from the applicant pipeline (the other
     * creation path would be direct seeding/admin tools not shown
     * here).
     */
    public function acceptEndorsed(Application $application)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        // Ensure the application is in in_progress status (endorsed/approved by SFAO)
        if ($application->status !== 'in_progress') {
            return back()->with('error', 'Only SFAO-approved (in-progress) applications can be accepted.');
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

        // Mark the application as approved before creating the scholar record.
        $application->status = 'approved';
        $application->save();

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

        // Create notification for student
        NotificationService::notifyApplicationStatusChange($application, 'approved');

        return redirect()->route('central.dashboard', ['tabs' => 'endorsed_applicants'])
            ->with('success', 'Application has been accepted successfully. Scholar record has been created.');
    }

    /**
     * Reject an endorsed application (Central)
     * @param Request $request
     * @param Application $application
     *
     * ANNOTATION: counterpart to acceptEndorsed(). Requires a
     * rejection_reason. Sets application status to 'rejected' AND
     * writes a permanent audit record into RejectedApplicant (with the
     * reason, optional remarks, who rejected it, and when) — this table
     * is what prevents the student from re-applying to the same
     * scholarship in the future (referenced by name in the success
     * message).
     */
    public function rejectEndorsed(Request $request, Application $application)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        // Ensure the application is in in_progress status (endorsed/approved by SFAO)
        if ($application->status !== 'in_progress') {
            return back()->with('error', 'Only SFAO-approved (in-progress) applications can be rejected.');
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

        return redirect()->route('central.dashboard', ['tabs' => 'rejected_applicants'])
            ->with('success', 'Application has been rejected. The student will not be able to apply to this scholarship again.');
    }

    /**
     * View rejected applicants list (Central)
     *
     * ANNOTATION: simple listing of the RejectedApplicant audit table,
     * scoped to rejections made by Central (as opposed to any other
     * rejection source, though this controller only ever writes
     * 'central' as the rejected_by value).
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
     *
     * ANNOTATION: bulk-updates evaluation_status for a batch of
     * documents in the (student, scholarship) pair — notably this one
     * does NOT filter by document_category (unlike submitSfaoEvaluation()
     * which explicitly scopes to 'sfao_required'), so it will update
     * ANY matching document_id regardless of category. Given the method
     * name and its place in the Stage 3 flow, it's presumably only ever
     * called with scholarship_required document IDs from the frontend,
     * but the query itself doesn't enforce that — worth double-checking
     * if you ever see cross-category evaluation status bleed.
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
