<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'sfao_user_id',
        'campus_id',
        'original_campus_selection',
        'report_type',
        'student_type', // New
        'college_id',   // New
        'program_id',   // New
        'track_id',     // New
        'academic_year', // New
        'title',
        'description',
        'report_period_start',
        'report_period_end',
        'report_data',
        'status',
        'notes',
        'central_feedback',
        'submitted_at',
        'reviewed_at',
        'reviewed_by'
    ];

    protected $casts = [
        'report_data' => 'array',
        'report_period_start' => 'date',
        'report_period_end' => 'date',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime'
    ];

    // Relationships
    public function sfaoUser()
    {
        return $this->belongsTo(User::class, 'sfao_user_id');
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }
    
    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function track()
    {
        return $this->belongsTo(ProgramTrack::class, 'track_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('report_type', $type);
    }

    public function scopeByCampus($query, $campusId)
    {
        return $query->where('campus_id', $campusId);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeReviewed($query)
    {
        return $query->where('status', 'reviewed');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Helper methods
    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isSubmitted()
    {
        return $this->status === 'submitted';
    }

    public function isReviewed()
    {
        return $this->status === 'reviewed';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function getStatusBadgeColor()
    {
        return match($this->status) {
            'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            'submitted' => 'bg-bsu-red/10 text-bsu-red border border-bsu-red/20',
            'reviewed' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            'approved' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
        };
    }

    public function getReportTypeDisplayName()
    {
        return match($this->report_type) {
            'monthly' => 'Monthly Report',
            'quarterly' => 'Quarterly Report',
            'annual' => 'Annual Report',
            'custom' => 'Custom Report',
            default => ucfirst($this->report_type)
        };
    }

    public function getPeriodDisplayName()
    {
        if ($this->report_period_start && $this->report_period_end) {
            $start = Carbon::parse($this->report_period_start)->format('M d, Y');
            $end = Carbon::parse($this->report_period_end)->format('M d, Y');
            return "{$start} - {$end}";
        }
        return 'No period specified';
    }

    public function getDaysSinceSubmission()
    {
        if (!$this->submitted_at) {
            return null;
        }
        return Carbon::parse($this->submitted_at)->diffInDays(now());
    }

    public function getDaysSinceReview()
    {
        if (!$this->reviewed_at) {
            return null;
        }
        return Carbon::parse($this->reviewed_at)->diffInDays(now());
    }

    // Generate report data
    public static function generateReportData($campusId, $startDate, $endDate)
    {
        $campus = null;
        
        // Handle special case for "constituent_with_extensions"
        if ($campusId === 'constituent_with_extensions') {
            // Get the user's campus (which should be a constituent)
            $user = User::with('campus')->find(session('user_id'));
            if (!$user || !$user->campus) {
                throw new \Exception('User or campus not found');
            }
            $campus = $user->campus;
            $campusIds = $campus->getAllCampusesUnder()->pluck('id');
        } else {
            // Regular campus selection - only the selected campus
            $campus = Campus::find($campusId);
            if (!$campus) {
                throw new \Exception('Campus not found');
            }
            $campusIds = collect([$campusId]);
        }

        // Get applications in the period
        $applications = Application::whereHas('user', function($query) use ($campusIds) {
            $query->whereIn('campus_id', $campusIds);
        })
        ->whereBetween('created_at', [$startDate, $endDate])
        ->with(['user', 'scholarship'])
        ->get();

        // Get scholarships
        $scholarships = Scholarship::where('is_active', true)->get();

        // Calculate statistics
        $totalApplications = $applications->count();
        $approvedApplications = $applications->where('status', 'approved')->count();
        $rejectedApplications = $applications->where('status', 'rejected')->count();
        $pendingApplications = $applications->where('status', 'pending')->count();
        $claimedApplications = $applications->where('status', 'claimed')->count();


        // Applications by scholarship with detailed analysis
        $applicationsByScholarship = $applications->groupBy('scholarship_id')->map(function($group) {
            $scholarship = $group->first()->scholarship;
            $total = $group->count();
            $approved = $group->where('status', 'approved')->count();
            $rejected = $group->where('status', 'rejected')->count();
            $pending = $group->where('status', 'pending')->count();
            $claimed = $group->where('status', 'claimed')->count();
            
            return [
                'scholarship_name' => $scholarship->scholarship_name ?? 'Unknown',
                'scholarship_id' => $scholarship->id ?? null,
                'total' => $total,
                'approved' => $approved,
                'rejected' => $rejected,
                'pending' => $pending,
                'claimed' => $claimed,
                'approval_rate' => $total > 0 ? round(($approved / $total) * 100, 2) : 0,
                'rejection_rate' => $total > 0 ? round(($rejected / $total) * 100, 2) : 0,
                'slots_available' => $scholarship->slots_available ?? 0,
                'fill_percentage' => $scholarship->slots_available > 0 ? round(($total / $scholarship->slots_available) * 100, 2) : 0,
                'grant_amount' => $scholarship->grant_amount ?? 0
            ];
        });

        // Applications by month
        $applicationsByMonth = $applications->groupBy(function($app) {
            return Carbon::parse($app->created_at)->format('Y-m');
        })->map(function($group) {
            return $group->count();
        });

        // Student demographics
        $students = User::whereIn('campus_id', $campusIds)
            ->where('role', 'student')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalStudents = $students->count();
        $studentsWithApplications = $students->filter(function($student) use ($applications) {
            return $applications->where('user_id', $student->id)->count() > 0;
        })->count();

        // Campus-specific analysis
        $campusAnalysis = self::generateCampusAnalysis($campusIds, $startDate, $endDate);
        
        // Debug: Log campus analysis
        \Illuminate\Support\Facades\Log::info('Campus Analysis Generated:', [
            'campusIds' => $campusIds->toArray(),
            'campusAnalysis' => $campusAnalysis,
            'campusCount' => count($campusAnalysis)
        ]);

        // Scholarship distribution analysis
        $scholarshipDistribution = self::generateScholarshipDistributionAnalysis($applicationsByScholarship, $scholarships);

        // Performance insights
        $performanceInsights = self::generatePerformanceInsights($applications, $campusAnalysis, $scholarshipDistribution);

        // ML-powered insights
        $mlInsights = self::generateMLInsights($campusIds->first() ?? $campusId, $startDate, $endDate);

        return [
            'summary' => [
                'total_applications' => $totalApplications,
                'approved_applications' => $approvedApplications,
                'rejected_applications' => $rejectedApplications,
                'pending_applications' => $pendingApplications,
                'claimed_applications' => $claimedApplications,
                'approval_rate' => $totalApplications > 0 ? round(($approvedApplications / $totalApplications) * 100, 2) : 0,
                'rejection_rate' => $totalApplications > 0 ? round(($rejectedApplications / $totalApplications) * 100, 2) : 0
            ],
            'by_scholarship' => $applicationsByScholarship,
            'by_month' => $applicationsByMonth,
            'student_stats' => [
                'total_students' => $totalStudents,
                'students_with_applications' => $studentsWithApplications,
                'application_rate' => $totalStudents > 0 ? round(($studentsWithApplications / $totalStudents) * 100, 2) : 0
            ],
            'campus_analysis' => $campusAnalysis,
            'scholarship_distribution' => $scholarshipDistribution,
            'performance_insights' => $performanceInsights,
            'ml_insights' => $mlInsights,
            'campus_info' => [
                'campus_name' => $campus->name,
                'campus_type' => $campus->type,
                'extension_campuses' => $campus->extensionCampuses->pluck('name')->toArray()
            ],
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'duration_days' => Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate))
            ]
        ];
    }

    // Generate campus-specific analysis
    private static function generateCampusAnalysis($campusIds, $startDate, $endDate)
    {
        $campuses = Campus::whereIn('id', $campusIds)->get();
        $analysis = [];

        foreach ($campuses as $campus) {
            $campusApplications = Application::whereHas('user', function($query) use ($campus) {
                $query->where('campus_id', $campus->id);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

            $total = $campusApplications->count();
            $approved = $campusApplications->where('status', 'approved')->count();
            $rejected = $campusApplications->where('status', 'rejected')->count();
            $pending = $campusApplications->where('status', 'pending')->count();

            // Always include campus in analysis, even if no applications
            $analysis[] = [
                'campus_name' => $campus->name,
                'campus_type' => $campus->type,
                'total_applications' => $total,
                'approved_applications' => $approved,
                'rejected_applications' => $rejected,
                'pending_applications' => $pending,
                'approval_rate' => $total > 0 ? round(($approved / $total) * 100, 2) : 0,
                'rejection_rate' => $total > 0 ? round(($rejected / $total) * 100, 2) : 0
            ];
        }

        return $analysis;
    }

    // Generate scholarship distribution analysis
    private static function generateScholarshipDistributionAnalysis($applicationsByScholarship, $scholarships)
    {
        $distribution = [];
        $totalSlots = 0;
        $totalApplications = 0;
        $underutilizedScholarships = [];
        $overutilizedScholarships = [];

        foreach ($applicationsByScholarship as $scholarshipData) {
            $totalSlots += $scholarshipData['slots_available'];
            $totalApplications += $scholarshipData['total'];
            
            $fillRate = $scholarshipData['fill_percentage'];
            
            if ($fillRate < 50) {
                $underutilizedScholarships[] = [
                    'name' => $scholarshipData['scholarship_name'],
                    'fill_rate' => $fillRate,
                    'slots_available' => $scholarshipData['slots_available'],
                    'applications' => $scholarshipData['total']
                ];
            } elseif ($fillRate > 100) {
                $overutilizedScholarships[] = [
                    'name' => $scholarshipData['scholarship_name'],
                    'fill_rate' => $fillRate,
                    'slots_available' => $scholarshipData['slots_available'],
                    'applications' => $scholarshipData['total']
                ];
            }
        }

        return [
            'total_slots_available' => $totalSlots,
            'total_applications' => $totalApplications,
            'overall_fill_rate' => $totalSlots > 0 ? round(($totalApplications / $totalSlots) * 100, 2) : 0,
            'underutilized_scholarships' => $underutilizedScholarships,
            'overutilized_scholarships' => $overutilizedScholarships,
            'distribution_efficiency' => count($underutilizedScholarships) === 0 ? 'High' : (count($underutilizedScholarships) <= 2 ? 'Medium' : 'Low')
        ];
    }

    // Generate performance insights
    private static function generatePerformanceInsights($applications, $campusAnalysis, $scholarshipDistribution)
    {
        $insights = [];
        $warnings = [];
        $recommendations = [];

        // Campus approval rate analysis
        $approvalRates = collect($campusAnalysis)->pluck('approval_rate');
        $maxApprovalRate = $approvalRates->max();
        $minApprovalRate = $approvalRates->min();
        $approvalRateDifference = $maxApprovalRate - $minApprovalRate;

        if ($approvalRateDifference > 30) {
            $warnings[] = "Significant approval rate variation detected between campuses ({$approvalRateDifference}% difference)";
            $recommendations[] = "Review evaluation criteria consistency across campuses";
        }

        // Scholarship utilization analysis
        if (count($scholarshipDistribution['underutilized_scholarships']) > 0) {
            $underutilized = $scholarshipDistribution['underutilized_scholarships'];
            $warnings[] = count($underutilized) . " scholarship(s) are underutilized (less than 50% filled)";
            $recommendations[] = "Consider increasing awareness campaigns for underutilized scholarships";
        }

        if (count($scholarshipDistribution['overutilized_scholarships']) > 0) {
            $overutilized = $scholarshipDistribution['overutilized_scholarships'];
            $warnings[] = count($overutilized) . " scholarship(s) are overutilized (more than 100% filled)";
            $recommendations[] = "Consider increasing slot allocation for popular scholarships";
        }

        // Overall performance metrics
        $totalApplications = $applications->count();
        $approvedApplications = $applications->where('status', 'approved')->count();
        $overallApprovalRate = $totalApplications > 0 ? round(($approvedApplications / $totalApplications) * 100, 2) : 0;

        if ($overallApprovalRate < 50) {
            $warnings[] = "Low overall approval rate ({$overallApprovalRate}%)";
            $recommendations[] = "Review application requirements and evaluation process";
        } elseif ($overallApprovalRate > 90) {
            $warnings[] = "Very high approval rate ({$overallApprovalRate}%) - may indicate lenient evaluation";
            $recommendations[] = "Ensure evaluation standards are appropriately rigorous";
        }

        return [
            'overall_approval_rate' => $overallApprovalRate,
            'campus_consistency' => $approvalRateDifference < 20 ? 'Good' : ($approvalRateDifference < 40 ? 'Fair' : 'Poor'),
            'scholarship_utilization' => $scholarshipDistribution['distribution_efficiency'],
            'warnings' => $warnings,
            'recommendations' => $recommendations,
            'performance_score' => self::calculatePerformanceScore($overallApprovalRate, $approvalRateDifference, $scholarshipDistribution)
        ];
    }

    // Calculate overall performance score
    private static function calculatePerformanceScore($approvalRate, $approvalRateDifference, $scholarshipDistribution)
    {
        $score = 0;
        
        // Approval rate score (40% weight)
        if ($approvalRate >= 70 && $approvalRate <= 85) {
            $score += 40;
        } elseif ($approvalRate >= 60 && $approvalRate < 70) {
            $score += 30;
        } elseif ($approvalRate >= 50 && $approvalRate < 60) {
            $score += 20;
        } else {
            $score += 10;
        }

        // Consistency score (30% weight)
        if ($approvalRateDifference < 20) {
            $score += 30;
        } elseif ($approvalRateDifference < 40) {
            $score += 20;
        } else {
            $score += 10;
        }

        // Utilization score (30% weight)
        if ($scholarshipDistribution['distribution_efficiency'] === 'High') {
            $score += 30;
        } elseif ($scholarshipDistribution['distribution_efficiency'] === 'Medium') {
            $score += 20;
        } else {
            $score += 10;
        }

        return $score;
    }

    // Generate ML-powered insights
    private static function generateMLInsights($campusId, $startDate, $endDate)
    {
        try {
            $mlService = new \App\Services\MLAnalyticsService();
            return $mlService->generateMLInsights($campusId, $startDate, $endDate);
        } catch (\Exception $e) {
            \Log::error('ML Insights Error: ' . $e->getMessage());
            return [
                'ml_models' => [],
                'predictions' => [],
                'recommendations' => ['ML Analysis: Unable to generate AI insights at this time.'],
                'risk_factors' => [],
                'opportunities' => [],
                'trends' => [],
                'performance_metrics' => [],
                'error' => 'ML analysis temporarily unavailable'
            ];
        }
    }


}
