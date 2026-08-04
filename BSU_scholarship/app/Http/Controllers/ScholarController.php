<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scholar;
use App\Models\Application;

class ScholarController extends Controller
{
    /**
     * Display a listing of scholars
     */
    public function index(Request $request)
    {
        return $this->redirectToCentralScholarsTab($request->get('type'));
    }

    /**
     * Show the form for creating a new scholar
     */
    public function create()
    {
        return $this->redirectToCentralScholarsTab();
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

        return $this->redirectToCentralScholarsTab()
            ->with('success', 'Scholar record created successfully.');
    }

    /**
     * Display the specified scholar
     */
    public function show(Scholar $scholar)
    {
        $scholar->load(['user', 'scholarship', 'application']);

        $user = User::find(session('user_id'));
        $campuses = \App\Models\Campus::all();

        return view('central.scholars.show', compact('scholar', 'user', 'campuses'));
    }

    /**
     * Show the form for editing the specified scholar
     */
    public function edit(Scholar $scholar)
    {
        $scholar->load(['user', 'scholarship']);
        $scholarships = Scholarship::where('is_active', true)->get();
        $campuses = \App\Models\Campus::all();
        $user = \App\Models\User::find(session('user_id'));

        return view('central.scholars.edit', compact('scholar', 'scholarships', 'campuses', 'user'));
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

        return redirect()->route('central.scholars.show', $scholar)
            ->with('success', 'Scholar record updated successfully.');
    }

    /**
     * Remove the specified scholar
     */
    public function destroy(Scholar $scholar)
    {
        $scholar->delete();

        return $this->redirectToCentralScholarsTab()
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
        return redirect()->route('central.dashboard', ['tabs' => 'all_statistics']);
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

    private function redirectToCentralScholarsTab(?string $type = null)
    {
        $tab = match ($type) {
            'new' => 'new_scholars',
            'old' => 'old_scholars',
            default => 'all_scholars',
        };

        return redirect()->route('central.dashboard', ['tabs' => $tab]);
    }
}
