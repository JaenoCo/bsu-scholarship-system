<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Application;
use App\Models\Scholarship;

class ApplicationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $applications = $user->appliedScholarships; // assuming proper relationship
        return view('applications', compact('applications'));
    }

    public function apply(Request $request)
    {
        $request->validate([
            'scholarship_id' => 'required|exists:scholarships,id',
        ]);

        $userId = Auth::id();

        $existing = Application::where('user_id', $userId)
            ->where('scholarship_id', $request->scholarship_id)
            ->first();

        if ($existing) {
            return back()->with('error', 'You have already applied for this scholarship.');
        }

        Application::create([
            'user_id' => $userId,
            'scholarship_id' => $request->scholarship_id,
        ]);

        return back()->with('success', 'You have successfully applied for the scholarship.');
    }

    public function unapply(Request $request)
    {
        $request->validate([
            'scholarship_id' => 'required|exists:scholarships,id',
        ]);

        $userId = Auth::id();

        $application = Application::where('user_id', $userId)
            ->where('scholarship_id', $request->scholarship_id)
            ->first();

        if ($application) {
            $application->delete();
            return back()->with('success', 'You have unapplied from the scholarship.');
        }

        return back()->with('error', 'Application not found.');
    }
}
