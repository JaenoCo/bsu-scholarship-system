<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scholar;
use App\Models\User;
use App\Models\Scholarship;
use App\Models\Application;
use Illuminate\Support\Facades\DB;

class ScholarController extends Controller
{
    /**
     * Display a listing of scholars
     */
    public function index(Request $request)
    {
        $query = Scholar::with(['user', 'scholarship', 'application']);

        // Filter by type (new/old)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by campus
        if ($request->filled('campus_id')) {
            $query->byCampus($request->campus_id);
        }

        // Filter by scholarship
        if ($request->filled('scholarship_id')) {
            $query->byScholarship($request->scholarship_id);
        }

        // Search by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $scholars = $query->paginate(20);

        // Get filter options
        $campuses = \App\Models\Campus::all();
        $scholarships = Scholarship::all();

        return view('scholars.index', compact('scholars', 'campuses', 'scholarships'));
    }

    /**
     * Show the form for creating a new scholar
     */
    public function create()
    {
        $users = User::where('role', 'student')->get();
        $scholarships = Scholarship::where('is_active', true)->get();
        $applications = Application::where('status', 'approved')
            ->whereDoesntHave('scholar')
            ->with(['user', 'scholarship'])
            ->get();

        return view('scholars.create', compact('users', 'scholarships', 'applications'));
    }

    /**
     * Store a newly created scholar
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'scholarship_id' => 'required|exists:scholarships,id',
            'application_id' => 'nullable|exists:applications,id',
            'type' => 'required|in:new,old',
            'scholarship_start_date' => 'required|date',
            'scholarship_end_date' => 'nullable|date|after:scholarship_start_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check if scholar already exists for this user and scholarship
        $existingScholar = Scholar::where('user_id', $request->user_id)
            ->where('scholarship_id', $request->scholarship_id)
            ->first();

        if ($existingScholar) {
            return back()->withErrors(['error' => 'Scholar record already exists for this user and scholarship.']);
        }

        Scholar::create($request->all());

        return redirect()->route('scholars.index')
            ->with('success', 'Scholar record created successfully.');
    }

    /**
     * Display the specified scholar
     */
    public function show(Scholar $scholar)
    {
        $scholar->load(['user', 'scholarship', 'application']);
        return view('scholars.show', compact('scholar'));
    }

    /**
     * Show the form for editing the specified scholar
     */
    public function edit(Scholar $scholar)
    {
        $scholarships = Scholarship::where('is_active', true)->get();
        return view('scholars.edit', compact('scholar', 'scholarships'));
    }

    /**
     * Update the specified scholar
     */
    public function update(Request $request, Scholar $scholar)
    {
        $request->validate([
            'scholarship_id' => 'required|exists:scholarships,id',
            'type' => 'required|in:new,old',
            'status' => 'required|in:active,inactive,suspended,completed',
            'scholarship_start_date' => 'required|date',
            'scholarship_end_date' => 'nullable|date|after:scholarship_start_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $scholar->update($request->all());

        return redirect()->route('scholars.index')
            ->with('success', 'Scholar record updated successfully.');
    }

    /**
     * Remove the specified scholar
     */
    public function destroy(Scholar $scholar)
    {
        $scholar->delete();

        return redirect()->route('scholars.index')
            ->with('success', 'Scholar record deleted successfully.');
    }

    /**
     * Add a grant to a scholar
     */
    public function addGrant(Request $request, Scholar $scholar)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        $scholar->addGrant($request->amount, $request->description);

        return back()->with('success', 'Grant added successfully.');
    }

    /**
     * Get scholars statistics
     */
    public function statistics()
    {
        $stats = [
            'total_scholars' => Scholar::count(),
            'new_scholars' => Scholar::new()->count(),
            'old_scholars' => Scholar::old()->count(),
            'active_scholars' => Scholar::active()->count(),
            'inactive_scholars' => Scholar::where('status', 'inactive')->count(),
            'suspended_scholars' => Scholar::where('status', 'suspended')->count(),
            'completed_scholars' => Scholar::where('status', 'completed')->count(),
        ];

        // Scholars by campus
        $scholarsByCampus = Scholar::with('user.campus')
            ->get()
            ->groupBy('user.campus.name')
            ->map->count();

        // Scholars by scholarship
        $scholarsByScholarship = Scholar::with('scholarship')
            ->get()
            ->groupBy('scholarship.scholarship_name')
            ->map->count();

        // Total grants distributed
        $totalGrantsDistributed = Scholar::sum('total_grant_received');

        return view('scholars.statistics', compact('stats', 'scholarsByCampus', 'scholarsByScholarship', 'totalGrantsDistributed'));
    }

    /**
     * Create scholar from approved application
     */
    public function createFromApplication(Application $application)
    {
        if ($application->status !== 'approved') {
            return back()->withErrors(['error' => 'Application must be approved to create scholar record.']);
        }

        // Check if scholar already exists
        $existingScholar = Scholar::where('user_id', $application->user_id)
            ->where('scholarship_id', $application->scholarship_id)
            ->first();

        if ($existingScholar) {
            return back()->withErrors(['error' => 'Scholar record already exists for this application.']);
        }

        // Determine scholar type
        $scholarType = $application->grant_count > 0 ? 'old' : 'new';

        // Calculate dates
        $startDate = $application->created_at->startOfMonth();
        $endDate = $application->scholarship->renewal_allowed 
            ? $startDate->copy()->addYear() 
            : $startDate->copy()->addMonths(6);

        Scholar::create([
            'user_id' => $application->user_id,
            'scholarship_id' => $application->scholarship_id,
            'application_id' => $application->id,
            'type' => $scholarType,
            'grant_count' => $application->grant_count,
            'total_grant_received' => $application->grant_count * $application->scholarship->grant_amount,
            'scholarship_start_date' => $startDate,
            'scholarship_end_date' => $endDate,
            'status' => 'active',
            'notes' => 'Created from approved application',
        ]);

        return back()->with('success', 'Scholar record created successfully from application.');
    }
}
