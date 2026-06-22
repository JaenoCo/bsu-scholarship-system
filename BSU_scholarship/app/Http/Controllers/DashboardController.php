<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Application;
use App\Models\Scholarship;
use App\Models\Report;
use App\Models\Scholar;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Unified Dashboard Entry Point
     */
    public function index(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login')->with('session_expired', true);
        }

        $role = session('role');

        return match($role) {
            'sfao' => $this->sfaoDashboard($request),
            'central' => $this->centralDashboard($request),
            'student' => $this->studentDashboard($request),
            default => redirect('/login')
        };
    }
    
    /**
     * Settings Page Entry Point
     */
    public function settings(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $user = User::with('campus')->find(session('user_id'));
        $role = session('role');

        return match($role) {
            'sfao' => $this->sfaoSettings($request, $user),
            'central' => $this->centralSettings($request, $user),
            'student' => $this->studentSettings($request, $user),
            default => redirect('/login')
        };
    }

    private function sfaoSettings(Request $request, $user)
    {
        $sfaoCampus = $user->campus;
        return view('sfao.settings.index', ['user' => $user, 'sfaoCampus' => $sfaoCampus]);
    }

    private function centralSettings(Request $request, $user)
    {
         $campuses = \App\Models\Campus::all();
         return view('central.settings.index', ['user' => $user, 'campuses' => $campuses]);
    }

    private function studentSettings(Request $request, $user)
    {
        return view('student.settings.index', ['user' => $user]);
    }

    /**
     * SFAO Dashboard Logic
     */
    private function sfaoDashboard(Request $request)
    {

        $user = User::with('campus')->find(session('user_id'));
        $sfaoCampus = $user->campus;
        $monitoredCampuses = $sfaoCampus->getAllCampusesUnder();
        $campusIds = $monitoredCampuses->pluck('id');

        // --- DASHBOARD ANALYTICS ---
        $analytics = $this->getAnalytics($campusIds);
        
        // --- DATA LOADERS ---
        // These will eventually be moved to AJAX calls for better performance,
        // but for now we load them to maintain current functionality.
        
        // 1. Applicants Data (Delegated to ApplicantController logic if needed, but keeping here for dashboard view composition)
        $limit = 5; // Dashboard preview limit
        
        // 2. Scholarships Data
        $scholarshipsQuery = Scholarship::withCount([
            'applications' => function($query) use ($campusIds) {
                $query->whereHas('user', function($userQuery) use ($campusIds) {
                    $userQuery->whereIn('campus_id', $campusIds);
                });
            },
            'scholars' => function($query) use ($campusIds) {
                $query->whereHas('user', function($userQuery) use ($campusIds) {
                    $userQuery->whereIn('campus_id', $campusIds);
                });
            }
        ])->whereHas('campuses', function($q) use ($campusIds) {
            $q->whereIn('campus_id', $campusIds);
        });

        $scholarshipsAll = (clone $scholarshipsQuery)->orderBy('scholarship_name', 'asc')->get();
        // Sorting and Pagination logic for scholarships... (Simplified for initial migration)
        $scholarshipsAll = $this->paginate($scholarshipsAll, 5, $request->get('page_all', 1), 'page_all');
        $scholarshipsPrivate = $this->paginate((clone $scholarshipsQuery)->where('scholarship_type', 'private')->orderBy('scholarship_name', 'asc')->get(), 5, $request->get('page_private', 1), 'page_private');
        $scholarshipsGov = $this->paginate((clone $scholarshipsQuery)->where('scholarship_type', 'government')->orderBy('scholarship_name', 'asc')->get(), 5, $request->get('page_gov', 1), 'page_gov');
        
        // Scholarships List for Dropdowns (Unpaginated)
        $activeScholarshipsList = Scholarship::where('is_active', true)
            ->whereHas('campuses', function($q) use ($campusIds) {
                $q->whereIn('campus_id', $campusIds);
            })
            ->orderBy('scholarship_name', 'asc')
            ->get();

        // 3. Scholars Data
        $scholars = Scholar::with(['user', 'scholarship'])
            ->whereHas('user', function($query) use ($campusIds) {
                $query->whereIn('campus_id', $campusIds);
            })->get();

        // 4. Reports
        $reports = Report::where('sfao_user_id', session('user_id'))->latest()->paginate(5);

        // View Parameters
        $activeTab = $request->get('tabs', $request->get('tab', 'analytics'));
        $campusOptions = $monitoredCampuses->map(function($c) { return ['id' => $c->id, 'name' => $c->name]; })->values();

        // Pass empty collections for the "Detailed Lists" that are handled by AJAX or specific tabs
        // This prevents the view from crashing while we transition.
        $students = collect(); 
        $studentsAll = $this->paginate(collect(), 10, 1, 'page_applicants'); 
        
        // NOTE: We need to pull the full ApplicantController logic here TEMPORARILY 
        // because the dashboard view expects ALL variables to be present. 
        // In a full refactor, we would use View Composers or separate routes.
        // For now, I will use a helper method to keep this clean.
        $applicantData = $this->getApplicantData($request, $campusIds);

        // --- AJAX HANDLER ---
        if ($request->ajax()) {
            $view = '';
            $data = [];
            
            if ($activeTab === 'scholarships' || str_starts_with($activeTab, 'scholarships-')) {
                // Re-query with filters if needed (already handled by paginate params above if passed correctly)
                // For now, simple return of the list view with the updated data
                // Note: The $scholarshipsAll above is already filtered by page/type if params exist
                // We just need to determine WHICH list to return.
                
                // Simplified: Reuse the main collection logic. 
                // In a perfect refactor, we'd move this query logic to a service or dedicated method.
                
                // Recalculate based on specific request filters to be safe
                $query = Scholarship::withCount([
                    'applications' => function($query) use ($campusIds) {
                        $query->whereHas('user', function($userQuery) use ($campusIds) {
                            $userQuery->whereIn('campus_id', $campusIds);
                        });
                    },
                    'scholars' => function($query) use ($campusIds) {
                        $query->whereHas('user', function($userQuery) use ($campusIds) {
                            $userQuery->whereIn('campus_id', $campusIds);
                        });
                    }
                ])->whereHas('campuses', function($q) use ($campusIds) {
                    $q->whereIn('campus_id', $campusIds);
                });
                
                if ($request->get('campus_filter') && $request->get('campus_filter') !== 'all') {
                    $campusFilter = $request->get('campus_filter');
                    $query->whereHas('campuses', function($q) use ($campusFilter) {
                        $q->where('campus_id', $campusFilter);
                    });
                }
                 
                if ($request->get('type_filter') && $request->get('type_filter') !== 'all') {
                    $eligibility = $request->get('type_filter');
                    // Check if filter is Government/Private (Legacy/Fallback) or Condition
                    if (in_array($eligibility, ['private', 'government'])) {
                        $query->where('scholarship_type', $eligibility);
                    } else {
                        // Filter by eligibility condition
                        $query->whereHas('conditions', function($q) use ($eligibility) {
                            $q->where('name', $eligibility);
                        });
                    }
                }

                if ($request->get('search_query')) {
                    $search = $request->get('search_query');
                    $query->where('scholarship_name', 'LIKE', "%{$search}%");
                }
                
                $sort = $request->get('sort_by', 'name');
                if ($sort === 'name') $sort = 'scholarship_name'; // Fix mapped column name
                
                $order = $request->get('sort_order', 'asc');
                $query->orderBy($sort, $order);
                
                $scholarships = $this->paginate($query->get(), 5, $request->get('page_scholarships', 1), 'page_scholarships');
                
                $view = 'sfao.scholarships.list';
                $data = ['scholarships' => $scholarships];

                return response()->json([
                    'html' => view($view, $data)->render()
                ]);

            } elseif ($activeTab === 'applicants' || str_starts_with($activeTab, 'applicants-')) {
                 // DEPRECATED: Logic moved to ApplicationController::sfaoApplicantsList
                 // This block should ideally not be hit if frontend is updated, 
                 // but we can fallback or return empty to avoid errors.
                 return response()->json(['error' => 'Endpoint moved to sfao.applicants.list'], 404);

            } elseif ($activeTab === 'scholars' || str_starts_with($activeTab, 'scholars-')) {
                // Handle Scholar Filtering
                $query = Scholar::with(['user', 'scholarship'])
                    ->whereHas('user', function($q) use ($campusIds) {
                        $q->whereIn('campus_id', $campusIds);
                    });
                    
                if ($request->get('campus_filter') && $request->get('campus_filter') !== 'all') {
                    $query->whereHas('user', function($q) use ($request) {
                        $q->where('campus_id', $request->get('campus_filter'));
                    });
                }
                
                // College Filter
                $collegeFilter = $request->get('college_filter', 'all');
                if ($collegeFilter !== 'all') {
                     $variations = explode('|', $collegeFilter);
                     if (in_array('CABE', $variations) || in_array('CABEIHM', $variations)) {
                          $variations = array_merge($variations, [
                              'CABE', 'CABEIHM', 
                              'College of Accountancy, Business, Economics, International Hospitality Management'
                          ]);
                     }
                     $query->whereHas('user', function($q) use ($variations) {
                         $q->whereIn('college', array_unique($variations));
                     });
                }

                // Program Filter
                $programFilter = $request->get('program_filter', 'all');
                if ($programFilter !== 'all') {
                     $query->whereHas('user', function($q) use ($programFilter) {
                         $q->where('program', $programFilter);
                     });
                }

                // Track Filter
                $trackFilter = $request->get('track_filter', 'all');
                if ($trackFilter !== 'all') {
                     $query->whereHas('user', function($q) use ($trackFilter) {
                         $q->where('track', $trackFilter);
                     });
                }

                // Academic Year Filter
                $academicYearFilter = $request->get('academic_year_filter', 'all');
                if ($academicYearFilter !== 'all') {
                     $parts = explode('-', $academicYearFilter);
                     if (count($parts) === 2) {
                         $startYear = (int)$parts[0];
                         $endYear = (int)$parts[1];
                         $startDate = "$startYear-08-01";
                         $endDate = "$endYear-07-31";
                         $query->whereBetween('created_at', [$startDate, $endDate]);
                     }
                }
                
                if ($request->get('scholarship_filter') && $request->get('scholarship_filter') !== 'all') {
                    $query->where('scholarship_id', $request->get('scholarship_filter'));
                }
                
                if ($request->get('type_filter') && $request->get('type_filter') !== 'all') {
                    $query->where('type', $request->get('type_filter'));
                }

                $scholarsList = $query->get();
                $scholarsList = $this->paginate($scholarsList, 5, $request->get('page_scholarships', 1), 'page_scholarships');
                
                $view = 'sfao.scholars.list';
                $data = ['scholars' => $scholarsList];
                
                
                // Get filtered scholars for accurate counts
                $filteredScholars = $query->get();
                
                $counts = [
                    'total' => $filteredScholars->count(),
                    'active' => $filteredScholars->where('status', 'active')->count(),
                    'new' => $filteredScholars->where('type', 'new')->count(),
                    'old' => $filteredScholars->where('type', 'old')->count()
                ];

                return response()->json([
                    'html' => view($view, $data)->render(),
                    'counts' => $counts
                ]);
            }

            if ($view) {
                return response()->json(['html' => view($view, $data)->render()]);
            }
        }



        // Fetch Filter Options with Normalization
        $rawColleges = User::where('role', 'student')
            ->whereIn('campus_id', $campusIds)
            ->whereNotNull('college')
            ->distinct()
            ->pluck('college');

        $mergedColleges = [];
        foreach ($rawColleges as $c) {
            $label = $c;
            // Normalize CABEIHM variations
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

        // Get Programs and Tracks directly (assuming they are cleaner, or handle later)
        $filterOptions = User::where('role', 'student')
            ->whereIn('campus_id', $campusIds)
            ->select('program', 'track')
            ->distinct()
            ->get();

        $programs = $filterOptions->pluck('program')->filter()->unique()->values();
        $tracks = $filterOptions->pluck('track')->filter()->unique()->values();
        
        // Academic Years (from Applications) — PostgreSQL-compatible using EXTRACT
        $datePartsSelect = DB::connection()->getDriverName() === 'pgsql'
            ? 'DISTINCT EXTRACT(YEAR FROM created_at) as year, EXTRACT(MONTH FROM created_at) as month'
            : 'DISTINCT YEAR(created_at) as year, MONTH(created_at) as month';

        $academicYears = Application::whereHas('user', function($q) use ($campusIds) {
                $q->whereIn('campus_id', $campusIds);
            })
            ->selectRaw($datePartsSelect)
            ->get()
            ->map(function($app) {
                // Assumption: AY starts in August (Month 8)
                $startYear = $app->month >= 8 ? (int)$app->year : (int)$app->year - 1;
                return $startYear . '-' . ($startYear + 1);
            })
            ->unique()
            ->sortDesc()
            ->values();

        // Ensure Current AY is present
        $currentMonth = date('n');
        $currentYear = date('Y');
        $currentAYStart = $currentMonth >= 8 ? $currentYear : $currentYear - 1;
        $currentAY = $currentAYStart . '-' . ($currentAYStart + 1);
        
        if (!$academicYears->contains($currentAY)) {
            $academicYears->prepend($currentAY);
        }

        // 5. Application Forms
        $forms = \App\Models\ApplicationForm::with(['campus', 'uploader'])
            ->whereIn('campus_id', $campusIds)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('sfao.index', array_merge([
            'user' => $user,
            'sfaoCampus' => $sfaoCampus,
            'monitoredCampuses' => $monitoredCampuses,
            'managedCampuses' => $monitoredCampuses,
            'analytics' => $analytics,
            'scholarshipsAll' => $scholarshipsAll,
            'scholarshipsPrivate' => $scholarshipsPrivate,
            'scholarshipsGov' => $scholarshipsGov,
            'activeScholarshipsList' => $activeScholarshipsList,
            'scholars' => $scholars,
            'reports' => $reports,
            'forms' => $forms,
            'activeTab' => $activeTab,
            'campusOptions' => $campusOptions,
            'colleges' => $colleges,
            'programs' => $programs,
            'tracks' => $tracks,
            'academicYears' => $academicYears,
            'sortBy' => 'name', 'sortOrder' => 'asc', 'campusFilter' => 'all', 'statusFilter' => 'all',
            'scholarsSortBy' => 'name', 'scholarsSortOrder' => 'asc'
        ], $applicantData));
    }

    private function getAnalytics($campusIds)
    {
        $analytics = [];
        
        // 1. Basic Counts
        $totalStudents = User::where('role', 'student')
            ->whereIn('campus_id', $campusIds)
            ->count();
            
        $studentsWithApplications = Application::whereHas('user', function($query) use ($campusIds) {
                $query->whereIn('campus_id', $campusIds);
            })
            ->distinct('user_id')
            ->count('user_id');
            
        $pendingApplications = Application::whereHas('user', function($query) use ($campusIds) {
                $query->whereIn('campus_id', $campusIds);
            })
            ->where('status', 'pending')
            ->count();
            
        $approvedApplications = Application::whereHas('user', function($query) use ($campusIds) {
                $query->whereIn('campus_id', $campusIds);
            })
            ->where('status', 'approved')
            ->count();
            
        $rejectedApplications = Application::whereHas('user', function($query) use ($campusIds) {
                $query->whereIn('campus_id', $campusIds);
            })
            ->where('status', 'rejected')
            ->count();

        $analytics['total_students'] = $totalStudents;
        $analytics['students_with_applications'] = $studentsWithApplications;
        $analytics['pending_applications'] = $pendingApplications;
        $analytics['approved_applications'] = $approvedApplications;
        $analytics['rejected_applications'] = $rejectedApplications;
        $analytics['approval_rate'] = $studentsWithApplications > 0 ? round(($approvedApplications / $studentsWithApplications) * 100, 1) : 0;

        // 2. Department Statistics (Refactored to College)
        // Get all colleges
        // Get colleges associated with the monitored campuses
        $allColleges = \App\Models\College::whereHas('campuses', function($q) use ($campusIds) {
            $q->whereIn('campus_id', $campusIds);
        })->get();
        // Fetch available scholarships for filter
        $availableScholarships = Scholarship::where('is_active', true)
            ->select('id', 'scholarship_name', 'scholarship_type')
            ->orderBy('scholarship_name')
            ->get();
        $analytics['available_scholarships'] = $availableScholarships;
        
        $analytics['all_colleges'] = $allColleges;
        
        // Map campuses to departments
        $sfaoUser = User::find(session('user_id'));
        $sfaoCampus = $sfaoUser->campus;
        
        $campusColleges = [];
        foreach ($sfaoCampus->getAllCampusesUnder() as $camp) {
            $campusColleges[$camp->id] = $camp->colleges->pluck('short_name')->toArray();
        }
        $analytics['campus_colleges'] = $campusColleges;

        // Get Programs per Department (for filters)
        $dbProgramsData = User::where('role', 'student')
            ->whereIn('campus_id', $campusIds)
            ->whereNotNull('program')
            ->select('college', 'program')
            ->distinct()
            ->get()
            ->groupBy('college')
            ->map(function ($items) {
                return $items->pluck('program')->unique()->values()->all();
            })->toArray();
            
        // Merge with Standard Programs to ensure filters are populated
        $standardPrograms = $this->getStandardPrograms();
        $mergedPrograms = $standardPrograms;
        
        foreach ($dbProgramsData as $college => $programs) {
            if (isset($mergedPrograms[$college])) {
                $mergedPrograms[$college] = array_unique(array_merge($mergedPrograms[$college], $programs));
                sort($mergedPrograms[$college]);
            } else {
                $mergedPrograms[$college] = $programs;
            }
        }
        
        $analytics['college_programs'] = $mergedPrograms;

        // Generate Strict Campus-College-Program Map
        $rawPrograms = \App\Models\Program::with(['campusCollege.campus', 'campusCollege.college'])->get();
        $campusCollegePrograms = [];
        foreach ($rawPrograms as $prog) {
            if ($prog->campusCollege && $prog->campusCollege->campus && $prog->campusCollege->college) {
                $cId = $prog->campusCollege->campus_id;
                $colName = $prog->campusCollege->college->short_name;
                $pName = $prog->name;
                $campusCollegePrograms[$cId][$colName][] = $pName;
            }
        }
        // Ensure uniqueness and sort
        foreach ($campusCollegePrograms as $cId => $cols) {
            foreach ($cols as $colName => $progs) {
                $uniqueProgs = array_unique($progs);
                sort($uniqueProgs);
                $campusCollegePrograms[$cId][$colName] = $uniqueProgs;
            }
        }
        $analytics['campus_college_programs'] = $campusCollegePrograms;

        // Get Tracks for Programs (Grouped by Program Name)
        $programTracks = [];
        $programsWithTracks = \App\Models\Program::with('tracks')->get();
        foreach ($programsWithTracks as $p) {
             if ($p->tracks->isNotEmpty()) {
                 if (!isset($programTracks[$p->name])) {
                     $programTracks[$p->name] = [];
                 }
                 $programTracks[$p->name] = array_unique(array_merge($programTracks[$p->name], $p->tracks->pluck('name')->toArray()));
             }
        }
        foreach ($programTracks as $name => $tracks) {
             $programTracks[$name] = array_values($tracks);
             sort($programTracks[$name]);
        }
        $analytics['program_tracks'] = $programTracks;

        // Calculate stats per college
        $collegeStats = [];
        foreach ($allColleges as $college) {
            // Count students in this college
            $colStudentsCount = User::where('role', 'student')
                ->whereIn('campus_id', $campusIds)
                ->where('college', $college->short_name)
                ->count();
                
            // Count applications for students in this college
            $colApplicationsCount = Application::whereHas('user', function($query) use ($campusIds, $college) {
                    $query->whereIn('campus_id', $campusIds)
                           ->where('college', $college->short_name);
                })
                ->count();
                
            $colApprovedCount = Application::whereHas('user', function($query) use ($campusIds, $college) {
                    $query->whereIn('campus_id', $campusIds)
                           ->where('college', $college->short_name);
                })
                ->where('status', 'approved')
                ->count();

            $colPendingCount = Application::whereHas('user', function($query) use ($campusIds, $college) {
                    $query->whereIn('campus_id', $campusIds)
                           ->where('college', $college->short_name);
                })
                ->where('status', 'pending')
                ->count();

            $colRejectedCount = Application::whereHas('user', function($query) use ($campusIds, $college) {
                    $query->whereIn('campus_id', $campusIds)
                           ->where('college', $college->short_name);
                })
                ->where('status', 'rejected')
                ->count();

            if ($colStudentsCount > 0 || $colApplicationsCount > 0) {
                $collegeStats[] = [
                    'name' => $college->short_name,
                    'full_name' => $college->name,
                    'total_students' => $colStudentsCount,
                    'total_applications' => $colApplicationsCount,
                    'approved_applications' => $colApprovedCount,
                    'pending_applications' => $colPendingCount,
                    'rejected_applications' => $colRejectedCount,
                    'approval_rate' => $colApplicationsCount > 0 ? round(($colApprovedCount / $colApplicationsCount) * 100, 1) : 0
                ];
            }
        }
        $analytics['college_stats'] = $collegeStats;

        // 3. All Students Data for Client-side Filtering (Gender Chart)
        $allStudentsData = User::where('role', 'student')
            ->whereIn('campus_id', $campusIds)
            ->select('campus_id', 'college', 'sex', 'program')
            ->get();
            
        $analytics['all_students_data'] = $allStudentsData;

        // 4. All Applications Data for Client-side Filtering (Scholarship Type Chart & Stacked Bar)
        $allApplicationsData = Application::join('users', 'applications.user_id', '=', 'users.id')
            ->join('scholarships', 'applications.scholarship_id', '=', 'scholarships.id')
            ->leftJoin('scholars', function($join) {
                $join->on('users.id', '=', 'scholars.user_id')
                     ->on('scholarships.id', '=', 'scholars.scholarship_id');
            })
            ->where('users.role', 'student') // Only count students
            ->whereIn('users.campus_id', $campusIds)
            ->select(
                'users.id as user_id', 
                'users.sr_code',
                'users.name as student_name',
                'users.first_name',
                'users.middle_name',
                'users.last_name',
                'users.sex', 
                'users.campus_id', 
                'users.college', 
                'users.program', 
                'users.track', 
                'scholarships.scholarship_type', 
                'scholarships.scholarship_name as scholarship_name', 
                'applications.status', 
                'applications.created_at', 
                'scholars.id as scholar_id', 
                'scholars.status as scholar_status', 
                'scholars.type as scholar_type',
                DB::raw('(SELECT COUNT(*) FROM scholars as s WHERE s.user_id = users.id) as is_global_scholar')
            )
            ->distinct()
            ->get();
            
        $analytics['all_applications_data'] = $allApplicationsData;


        return $analytics;
    }

    private function getApplicantData($request, $campusIds)
    {
        // Base Query: Students in SFAO jurisdiction
        $baseQuery = User::where('role', 'student')
            ->whereIn('campus_id', $campusIds)
            ->with(['campus', 'applications.scholarship']);

        // Helper to clone and paginate
        $paginate = function($query, $pageName) use ($request) {
            return $query->paginate(10, ['*'], $pageName)->withQueryString();
        };

        // 1. All Students (Dictionary)
        $studentsAll = clone $baseQuery;

        // 2. Not Applied
        $studentsNotApplied = (clone $baseQuery)->whereDoesntHave('applications');

        // 3. In Progress
        $studentsInProgress = (clone $baseQuery)->whereHas('applications', function($q) {
            $q->where('status', 'in_progress');
        });

        // 4. Pending
        $studentsPending = (clone $baseQuery)->whereHas('applications', function($q) {
            $q->where('status', 'pending');
        });

        // 5. Approved
        $studentsApproved = (clone $baseQuery)->whereHas('applications', function($q) {
            $q->where('status', 'approved');
        });

        // 6. Rejected
        $studentsRejected = (clone $baseQuery)->whereHas('applications', function($q) {
            $q->where('status', 'rejected');
        });
        
        // Applications for counters/references if needed
        $applications = Application::whereIn('user_id', (clone $baseQuery)->select('id'))
            ->get();

        return [
            'students' => $paginate(clone $baseQuery, 'page_applicants'), // Default list
            'studentsAll' => $paginate($studentsAll, 'page_applicants'),
            'studentsNotApplied' => $paginate($studentsNotApplied, 'page_not_applied'),
            'studentsInProgress' => $paginate($studentsInProgress, 'page_in_progress'),
            'studentsPending' => $paginate($studentsPending, 'page_pending'),
            'studentsApproved' => $paginate($studentsApproved, 'page_approved'),
            'studentsRejected' => $paginate($studentsRejected, 'page_rejected'),
            'applications' => $applications,
        ];
    }

    private function paginate($items, $perPage, $page, $pageName)
    {
        $items = $items instanceof \Illuminate\Support\Collection ? $items : collect($items);
        $paginator = new LengthAwarePaginator(
            $items->forPage($page, $perPage),
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'pageName' => $pageName]
        );
        return $paginator->appends(request()->query());
    }

    /**
     * Central Dashboard Logic
     */
    private function centralDashboard(Request $request)
    {
        if (!session()->has('user_id') || session('role') !== 'central') {
            return redirect('/login')->with('session_expired', true);
        }

        // Create user object
        $user = \App\Models\User::find(session('user_id'));

        // Get all campuses for filter and resolving tab
        $campuses = \App\Models\Campus::all();

        // Get hierarchical data for cascading filters
        $campusColleges = \App\Models\CampusCollege::with(['college', 'programs.tracks'])->get()->values();

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
        
        // Apply sorting
        $queryAll = clone $scholarshipsQuery;
        $queryPrivate = clone $scholarshipsQuery;
        $queryGov = clone $scholarshipsQuery;
        
        // Apply type/eligibility filter to all lists if present
        if ($request->filled('type') && $request->type !== 'all') {
            $type = $request->type;

            if (in_array($type, ['private', 'government'])) {
                $queryAll->where('scholarship_type', $type);
                
                if ($type === 'private') $queryGov->where('id', -1); // Force empty
                if ($type === 'government') $queryPrivate->where('id', -1); // Force empty
            } else {
                 // It's a condition (GWA, etc). Apply to ALL queries.
                 $queryAll->whereHas('conditions', function($sq) use ($type) { $sq->where('name', $type); });
                 $queryPrivate->whereHas('conditions', function($sq) use ($type) { $sq->where('name', $type); });
                 $queryGov->whereHas('conditions', function($sq) use ($type) { $sq->where('name', $type); });
            }
        }

        // Apply sorting (and type restrictions for tabs)
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
            $years = explode('-', $academicYearFilter);
            if (count($years) === 2) {
                $startYear = (int)$years[0];
                $endYear = (int)$years[1];
                $startDate = \Carbon\Carbon::createFromDate($startYear, 8, 1)->startOfDay();
                $endDate = \Carbon\Carbon::createFromDate($endYear, 7, 31)->endOfDay();
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
        $reportsParams = ['status', 'type', 'campus', 'sort', 'order', 'academic_year'];
        
        $reportsSubmitted = $querySubmitted->where('status', 'submitted')->paginate(10, ['*'], 'page_submitted')->appends($request->only($reportsParams));
        $reportsReviewed = $queryReviewed->where('status', 'reviewed')->paginate(10, ['*'], 'page_reviewed')->appends($request->only($reportsParams));
        $reportsApproved = $queryApproved->where('status', 'approved')->paginate(10, ['*'], 'page_approved')->appends($request->only($reportsParams));
        $reportsRejected = $queryRejected->where('status', 'rejected')->paginate(10, ['*'], 'page_rejected')->appends($request->only($reportsParams));

        // Generate Academic Year Options
        $oldestReport = \App\Models\Report::orderBy('report_period_start', 'asc')->first();
        $startYear = $oldestReport && $oldestReport->report_period_start ? $oldestReport->report_period_start->year : now()->year;
        
        $currentYear = now()->year;
        $academicYearOptions = [];
        for ($y = $currentYear + 1; $y >= $startYear; $y--) {
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

        // START: Enrich Analytics for SFAO-style Charts
        $allCampusIds = \App\Models\Campus::pluck('id')->toArray();
        
        // Campus Colleges Map
        $campusCollegesMap = [];
        foreach ($campuses as $camp) {
            $campusCollegesMap[$camp->id] = $camp->colleges->pluck('short_name')->toArray();
        }
        $analytics['campus_colleges'] = $campusCollegesMap;
        
        // All Colleges
        $analytics['all_colleges'] = \App\Models\College::select('id', 'name', 'short_name')->get()->toArray();

        // Programs Logic
        $dbProgramsData = \App\Models\User::where('role', 'student')
            ->whereNotNull('program')
            ->select('college', 'program')
            ->distinct()
            ->get()
            ->groupBy('college')
            ->map(function ($items) {
                return $items->pluck('program')->unique()->values()->all();
            })->toArray();
            
        $standardPrograms = $this->getStandardPrograms();
        $mergedPrograms = $standardPrograms;
        foreach ($dbProgramsData as $college => $programs) {
            if (isset($mergedPrograms[$college])) {
                $mergedPrograms[$college] = array_unique(array_merge($mergedPrograms[$college], $programs));
                sort($mergedPrograms[$college]);
            } else {
                $mergedPrograms[$college] = $programs;
            }
        }
        $analytics['college_programs'] = $mergedPrograms;

        // Campus College Programs
        $rawPrograms = \App\Models\Program::with(['campusCollege.campus', 'campusCollege.college'])->get();
        $campusCollegePrograms = [];
        foreach ($rawPrograms as $prog) {
            if ($prog->campusCollege && $prog->campusCollege->campus && $prog->campusCollege->college) {
                $cId = $prog->campusCollege->campus_id;
                $colName = $prog->campusCollege->college->short_name;
                $pName = $prog->name;
                $campusCollegePrograms[$cId][$colName][] = $pName;
            }
        }
        foreach ($campusCollegePrograms as $cId => $cols) {
            foreach ($cols as $colName => $progs) {
                $uniqueProgs = array_unique($progs);
                sort($uniqueProgs);
                $campusCollegePrograms[$cId][$colName] = $uniqueProgs;
            }
        }
        $analytics['campus_college_programs'] = $campusCollegePrograms;

        // Program Tracks
        $programTracks = [];
        $programsWithTracks = \App\Models\Program::with('tracks')->get();
        foreach ($programsWithTracks as $p) {
             if ($p->tracks->isNotEmpty()) {
                 if (!isset($programTracks[$p->name])) {
                     $programTracks[$p->name] = [];
                 }
                 $programTracks[$p->name] = array_unique(array_merge($programTracks[$p->name], $p->tracks->pluck('name')->toArray()));
             }
        }
        foreach ($programTracks as $name => $tracks) {
             $programTracks[$name] = array_values($tracks);
             sort($programTracks[$name]);
        }
        $analytics['program_tracks'] = $programTracks;

        // Available Scholarships
        $analytics['available_scholarships'] = \App\Models\Scholarship::select('id', 'scholarship_name')->get()->toArray();

        // All Applications Data (Heavy Query - strictly needed for JS filtering)
        $allApplicationsData = \App\Models\Application::join('users', 'applications.user_id', '=', 'users.id')
            ->join('scholarships', 'applications.scholarship_id', '=', 'scholarships.id')
            ->leftJoin('scholars', function($join) {
                $join->on('users.id', '=', 'scholars.user_id')
                     ->on('scholarships.id', '=', 'scholars.scholarship_id');
            })
            ->where('users.role', 'student')
            ->select(
                'users.id as user_id', 
                'users.sr_code',
                'users.name as student_name',
                'users.first_name',
                'users.middle_name',
                'users.last_name',
                'users.sex', 
                'users.campus_id', 
                'users.college', 
                'users.program', 
                'users.track', 
                'scholarships.scholarship_type', 
                'scholarships.scholarship_name as scholarship_name', 
                'applications.status', 
                'applications.created_at', 
                'scholars.id as scholar_id', 
                'scholars.status as scholar_status', 
                'scholars.type as scholar_type',
                \Illuminate\Support\Facades\DB::raw('(SELECT COUNT(*) FROM scholars as s WHERE s.user_id = users.id) as is_global_scholar')
            )
            ->distinct()
            ->get();
            
        $analytics['all_applications_data'] = $allApplicationsData;
        // END: Enrich Analytics

        // Get filter options for applications
        $campusOptions = $campuses->map(function($campus) {
            return [
                'id' => $campus->id,
                'name' => $campus->name
            ];
        })->toArray();
        
        $scholarshipOptions = \App\Models\Scholarship::all()->map(function($scholarship) {
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
        $scholarsQuery = \App\Models\Scholar::with(['user', 'scholarship', 'user.campus']);

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
        if ($statusFilter !== 'all') {
             $queryScholarsAll->where('status', $statusFilter);
             $queryScholarsNew->where('status', $statusFilter);
             $queryScholarsOld->where('status', $statusFilter);
        }

        $queryScholarsNew->where('type', 'new');
        $queryScholarsOld->where('type', 'old');

        // Paginate
        $scholarsAll = $queryScholarsAll->paginate(10, ['*'], 'page_scholars_all')->withQueryString();
        $scholarsNew = $queryScholarsNew->paginate(10, ['*'], 'page_scholars_new')->withQueryString();
        $scholarsOld = $queryScholarsOld->paginate(10, ['*'], 'page_scholars_old')->withQueryString();

        $scholars = $scholarsAll;

        // Get qualified applicants (approved by SFAO but not yet selected as scholars)
        $qualifiedApplicantsQuery = User::with(['applications.scholarship', 'campus'])
            ->where('role', 'student')
            ->whereHas('applications', function($query) {
                $query->where('status', 'approved');
            })
            ->whereDoesntHave('scholars');

        if ($campusFilter !== 'all') {
            $qualifiedApplicantsQuery->where('campus_id', $campusFilter);
        }

        if ($scholarshipFilter !== 'all') {
            $qualifiedApplicantsQuery->whereHas('applications', function($query) use ($scholarshipFilter) {
                $query->where('scholarship_id', $scholarshipFilter);
            });
        }

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

        $qualifiedApplicants = $qualifiedApplicantsQuery->get() ?? collect();

        // Get endorsed applicants (approved by SFAO and ready for scholar selection)
        $endorsedApplicantsQuery = Application::with(['user', 'scholarship', 'user.campus'])
            ->where('status', 'approved')
            ->whereDoesntHave('user.scholars');

        if ($campusFilter !== 'all') {
            $endorsedApplicantsQuery->whereHas('user', function($query) use ($campusFilter) {
                $query->where('campus_id', $campusFilter);
            });
        }

        if ($scholarshipFilter !== 'all') {
            $endorsedApplicantsQuery->where('scholarship_id', $scholarshipFilter);
        }

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

        $endorsedApplicants = $endorsedApplicantsQuery->get() ?? collect();

        // Get all rejected applicants (rejected by Central Admin)
        $rejectedApplicants = \App\Models\RejectedApplicant::with(['user', 'scholarship', 'rejectedByUser'])
            ->where('rejected_by', 'central')
            ->orderBy('rejected_at', 'desc')
            ->get();

        // Data for Client-Side Filtering (SFAO Reports Tab)
        $allReportsForReportsTab = \App\Models\Report::with(['campus', 'college', 'program', 'track'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($report) {
                $report->report_type_display = $report->getReportTypeDisplayName();
                $report->campus_name = $report->campus ? $report->campus->name : 'Unknown Campus';
                $report->student_type_val = $report->student_type;
                $report->college_name = $report->college ? $report->college->short_name : null;
                $report->program_name = $report->program ? $report->program->name : null;
                $report->track_name = $report->track ? $report->track->name : null;

                $report->display_submitted_at = $report->submitted_at ? $report->submitted_at->format('M d, Y') : $report->created_at->format('M d, Y');
                $report->display_reviewed_at = $report->reviewed_at ? $report->reviewed_at->format('M d, Y') : 'Recently';
                
                if ($report->academic_year) {
                    $report->academic_year_display = $report->academic_year;
                } else {
                    $date = $report->report_period_start ?? $report->created_at;
                    $year = $date->year;
                    $month = $date->month;
                    if ($month >= 8) {
                        $report->academic_year_display = $year . '-' . ($year + 1);
                     } else {
                        $report->academic_year_display = ($year - 1) . '-' . $year;
                    }
                }
                $report->academic_year = $report->academic_year_display;
                
                return $report;
            });

        return view('central.analytics.index', compact(
            'user', 'applications', 'scholarshipsAll', 'scholarshipsPrivate', 'scholarshipsGov',
            'reportStats', 'analytics', 'reportsSubmitted', 'reportsReviewed', 'reportsApproved',
            'reportsRejected', 'campuses', 'campusOptions', 'scholarshipOptions', 'statusOptions',
            'sortBy', 'sortOrder', 'statusFilter', 'campusFilter', 'scholarshipFilter', 'scholars',
            'scholarsAll', 'scholarsNew', 'scholarsOld', 'qualifiedApplicants', 'endorsedApplicants',
            'rejectedApplicants', 'totalReports', 'academicYearOptions', 'academicYearFilter',
            'campusColleges', 'allReportsForReportsTab'
        ));
    }

    private function sortScholarships($scholarships, $sortBy, $sortOrder)
    {
        return $scholarships->sortBy(function($scholarship) use ($sortBy) {
            switch ($sortBy) {
                case 'name':
                    return $scholarship->scholarship_name;
                case 'type':
                    return $scholarship->scholarship_type;
                case 'amount':
                    return $scholarship->grant_amount;
                case 'deadline':
                    return $scholarship->deadline;
                case 'active':
                    return $scholarship->is_active;
                default:
                    return $scholarship->created_at;
            }
        }, SORT_REGULAR, $sortOrder === 'desc');
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
        $applicationQuery = \App\Models\Application::query();
        $userQuery = \App\Models\User::query();
        $reportQuery = \App\Models\Report::query();
        $scholarQuery = \App\Models\Scholar::query();
        
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
        $totalScholarships = \App\Models\Scholarship::count();
        $activeScholarships = \App\Models\Scholarship::where('is_active', true)->count();
        $acceptingApplicationsScholarships = \App\Models\Scholarship::acceptingApplications()->count();
        $oneTimeScholarships = \App\Models\Scholarship::where('grant_type', 'one_time')->count();
        $recurringScholarships = \App\Models\Scholarship::where('grant_type', 'recurring')->count();
        
        // Get scholar statistics
        $totalScholars = $scholarQuery->count();
        $activeScholars = (clone $scholarQuery)->where('status', 'active')->count();
        $completedScholars = (clone $scholarQuery)->where('status', 'completed')->count();
        $inactiveScholars = (clone $scholarQuery)->where('status', 'inactive')->count();
        $suspendedScholars = (clone $scholarQuery)->where('status', 'suspended')->count();
        $uniqueScholars = (clone $scholarQuery)->whereHas('user', function($q){ $q->where('role', 'student'); })->distinct('user_id')->count('user_id');

        // Get user statistics
        $totalStudents = $userQuery->where('role', 'student')->count();
        $registeredToday = (clone $userQuery)->whereDate('created_at', today())->count();

        // Unique Applicants
        $uniqueApplicants = (clone $applicationQuery)->distinct('user_id')->count('user_id');

        // Campus Distribution
        $campusDistribution = \App\Models\Campus::withCount(['users' => function($query) use ($timePeriod) {
            $query->where('role', 'student');
            if ($timePeriod !== 'all') {
                $dateCondition = $this->getDateCondition($timePeriod);
                if ($dateCondition) $query->whereBetween('created_at', $dateCondition);
            }
        }])->get()->map(function($campus) {
             return [
                 'name' => $campus->name,
                 'count' => $campus->users_count
             ];
        });

        // Application Status Trends (Monthly for current year) — PostgreSQL-compatible
        $monthExpression = DB::connection()->getDriverName() === 'pgsql'
            ? 'EXTRACT(MONTH FROM created_at)::integer'
            : 'MONTH(created_at)';

        $monthlyApplications = \App\Models\Application::selectRaw("{$monthExpression} as month, COUNT(*) as count")
            ->whereYear('created_at', date('Y'))
            ->groupByRaw($monthExpression)
            ->orderByRaw($monthExpression)
            ->pluck('count', 'month')
            ->toArray();
            
        $months = [];
        $applicationTrends = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = date('M', mktime(0, 0, 0, $i, 1));
            $applicationTrends[] = $monthlyApplications[$i] ?? 0;
        }

        // Calculate Detailed Campus Stats
        $campusStats = \App\Models\Campus::all()->map(function($campus) use ($timePeriod) {
            $dateCondition = $timePeriod !== 'all' ? $this->getDateCondition($timePeriod) : null;
            
            $studentQuery = $campus->users()->where('role', 'student');
            if ($dateCondition) $studentQuery->whereBetween('created_at', $dateCondition);
            $totalStudents = $studentQuery->count();

            $maleStudents = (clone $studentQuery)->where('sex', 'Male')->count();
            $femaleStudents = (clone $studentQuery)->where('sex', 'Female')->count();

            $scholarQuery = \App\Models\Scholar::whereHas('user', function($q) use ($campus) {
                $q->where('campus_id', $campus->id);
            });
            if ($dateCondition) $scholarQuery->whereBetween('created_at', $dateCondition);
            
            $newScholars = (clone $scholarQuery)->where('type', 'new')->count();
            $oldScholars = (clone $scholarQuery)->where('type', 'old')->count();
            
            $scholarshipScholarStats = $campus->scholars()
                ->with('scholarship')
                ->get()
                ->groupBy('scholarship_id')
                ->map(function ($scholars) {
                    $scholarship = $scholars->first()->scholarship;
                    return [
                        'name' => $scholarship->scholarship_name ?? 'Unknown',
                        'total' => $scholars->count(),
                        'new' => $scholars->where('type', 'new')->count(),
                        'old' => $scholars->where('type', 'old')->count()
                    ];
                })->values();

            return [
                'campus_id' => $campus->id,
                'campus_name' => $campus->name,
                'total_students' => $totalStudents,
                'male_students' => $maleStudents,
                'female_students' => $femaleStudents,
                'new_scholars' => $newScholars,
                'old_scholars' => $oldScholars,
                'scholarship_scholar_stats' => $scholarshipScholarStats
            ];
        })->values();

        // Aggregate Global Stats from Campus Stats
        $globalMaleStudents = $campusStats->sum('male_students');
        $globalFemaleStudents = $campusStats->sum('female_students');
        $globalNewScholars = $campusStats->sum('new_scholars');
        $globalOldScholars = $campusStats->sum('old_scholars');
        
        // Aggregate Scholarship Stats across all campuses
        $globalScholarshipStats = collect();
        foreach ($campusStats as $stat) {
            foreach ($stat['scholarship_scholar_stats'] as $sStat) {
                if (!$globalScholarshipStats->has($sStat['name'])) {
                    $globalScholarshipStats->put($sStat['name'], [
                        'name' => $sStat['name'], 
                        'total' => 0, 
                        'new' => 0, 
                        'old' => 0
                    ]);
                }
                $current = $globalScholarshipStats->get($sStat['name']);
                $current['total'] += $sStat['total'];
                $current['new'] += $sStat['new'];
                $current['old'] += $sStat['old'];
                $globalScholarshipStats->put($sStat['name'], $current);
            }
        }

        return [
            'reports' => [
                'total' => $totalReports,
                'submitted' => $submittedReports,
                'approved' => $approvedReports,
                'rejected' => $rejectedReports,
                'draft' => $draftReports,
                'pending_review' => $pendingReviews
            ],
            'applications' => [
                'total' => $totalApplications,
                'approved' => $approvedApplications,
                'rejected' => $rejectedApplications,
                'pending' => $pendingApplications,
                'claimed' => $claimedApplications,
                'monthly_trends' => [
                    'labels' => $months,
                    'data' => $applicationTrends
                ]
            ],
            'scholarships' => [
                'total' => $totalScholarships,
                'active' => $activeScholarships,
                'accepting' => $acceptingApplicationsScholarships,
                'one_time' => $oneTimeScholarships,
                'recurring' => $recurringScholarships
            ],
            'scholars' => [
                'total' => $totalScholars,
                'active' => $activeScholars,
                'completed' => $completedScholars,
                'inactive' => $inactiveScholars,
                'suspended' => $suspendedScholars,
                'graduated' => $completedScholars,
                'dropped' => $inactiveScholars + $suspendedScholars
            ],
            'users' => [
                'total_students' => $totalStudents,
                'registered_today' => $registeredToday,
                'campus_distribution' => $campusDistribution,
                'unique_applicants' => $uniqueApplicants,
                'unique_scholars' => $uniqueScholars
            ],
            'campus_names' => $campusStats->pluck('campus_name')->toArray(),
            'campus_application_stats' => $campusStats->toArray(),
            
            /* Global Flattened Stats for "All" View */
            'total_students' => $totalStudents,
            'male_students' => $globalMaleStudents,
            'female_students' => $globalFemaleStudents,
            'new_scholars' => $globalNewScholars,
            'old_scholars' => $globalOldScholars,
            'scholarship_scholar_stats' => $globalScholarshipStats->values()->toArray(),
        ];
    }

    private function getDateCondition($period)
    {
        switch ($period) {
            case 'today':
                return [now()->startOfDay(), now()->endOfDay()];
            case 'week':
                return [now()->startOfWeek(), now()->endOfWeek()];
            case 'month':
                return [now()->startOfMonth(), now()->endOfMonth()];
            case 'year':
                return [now()->startOfYear(), now()->endOfYear()];
            case 'last_7_days':
                return [now()->subDays(7)->startOfDay(), now()->endOfDay()];
            case 'last_30_days':
                return [now()->subDays(30)->startOfDay(), now()->endOfDay()];
            default:
                return null;
        }
    }

    /**
     * Student Dashboard Logic
     */
    private function studentDashboard(Request $request)
    {
        $user = \App\Models\User::find(session('user_id'));
        
        // 1. Fetch Application Forms
        $forms = \App\Models\ApplicationForm::where('campus_id', $user->campus_id)
            ->with('uploader')
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Fetch Scholarships
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $type = $request->get('type', 'all');

        $scholarshipsQuery = \App\Models\Scholarship::where('is_active', true)
            ->whereHas('campuses', function($query) use ($user) {
                $query->where('campus_id', $user->campus_id);
            })
            ->withCount('applications');

        if ($type !== 'all') {
            if (in_array($type, ['private', 'government'])) {
                $scholarshipsQuery->where('scholarship_type', $type);
            } else {
                 $scholarshipsQuery->whereHas('conditions', function($q) use ($type) {
                    $q->where('name', $type);
                });
            }
        }

        // Apply Sorting
        switch ($sortBy) {
            case 'name':
                $scholarshipsQuery->orderBy('scholarship_name', $sortOrder);
                break;
            case 'submission_deadline':
                $scholarshipsQuery->orderBy('submission_deadline', $sortOrder);
                break;
            case 'grant_amount':
                $scholarshipsQuery->orderBy('grant_amount', $sortOrder);
                break;
            case 'slots_available':
                $scholarshipsQuery->orderBy('slots_available', $sortOrder);
                break;
            case 'applications_count':
                $scholarshipsQuery->orderBy('applications_count', $sortOrder);
                break;
            default:
                $scholarshipsQuery->orderBy('created_at', $sortOrder);
                break;
        }

        $scholarships = $scholarshipsQuery->paginate(12)->withQueryString();
            
        // Append user-specific status to scholarships
        foreach ($scholarships as $scholarship) {
             $application = \App\Models\Application::where('user_id', $user->id)
                 ->where('scholarship_id', $scholarship->id)
                 ->first();
             
             $scholarship->applied = $application ? true : false;
             $scholarship->application_status = $application ? $application->status : null;
             
             $scholar = \App\Models\Scholar::where('user_id', $user->id)
                  ->where('scholarship_id', $scholarship->id)
                  ->exists();
             $scholarship->is_scholar = $scholar;
        }

        // 3. Check for any pending application
        $hasPendingApplication = \App\Models\Application::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        // 4. Notification Counts
        $unreadCount = \App\Models\Notification::where('user_id', $user->id)->where('is_read', false)->count();
        $unreadCountScholarships = \App\Models\Notification::where('user_id', $user->id)->where('is_read', false)->where('type', 'scholarship_created')->count();
        $unreadCountStatus = \App\Models\Notification::where('user_id', $user->id)->where('is_read', false)->where('type', 'application_status')->count();
        $unreadCountComments = \App\Models\Notification::where('user_id', $user->id)->where('is_read', false)->where('type', 'sfao_comment')->count();

        // 5. Scholarship Counts for Empty States
        $privateScholarshipsCount = \App\Models\Scholarship::where('is_active', true)->where('scholarship_type', 'Private')->count();
        $governmentScholarshipsCount = \App\Models\Scholarship::where('is_active', true)->where('scholarship_type', 'Government')->count();

        // 6. User's Filled Application Form (for SFAO/TDP tabs)
        $form = \App\Models\Form::where('user_id', $user->id)->first();

        // 7. Student Applications
        $applications = \App\Models\Application::where('user_id', $user->id)
            ->with('scholarship')
            ->orderBy('created_at', 'desc')
            ->get();

        // 8. Notifications
        $notifications = \App\Models\Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // 9. My Scholarships
        $scholarshipIds = \App\Models\Scholar::where('user_id', $user->id)->pluck('scholarship_id');
        $myScholarships = \App\Models\Scholarship::whereIn('id', $scholarshipIds)->get();

        return view('student.index', compact(
            'forms', 
            'user', 
            'scholarships', 
            'applications',
            'notifications',
            'myScholarships',
            'hasPendingApplication', 
            'unreadCount', 
            'unreadCountScholarships', 
            'unreadCountStatus', 
            'unreadCountComments',
            'privateScholarshipsCount',
            'governmentScholarshipsCount',
            'form'
        ));
    }

    private function getStandardPrograms()
    {
        // Fetch from Programs Table (Grouped by College)
        $programs = \App\Models\Program::with('campusCollege.college')->get();
        $grouped = [];
        
        foreach ($programs as $prog) {
            if ($prog->campusCollege && $prog->campusCollege->college) {
                $grouped[$prog->campusCollege->college->short_name][] = $prog->name;
            }
        }
        
        // Remove duplicates
        foreach ($grouped as $college => $names) {
            $grouped[$college] = array_values(array_unique($names));
        }

        return $grouped;
    }
}
