<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;

class ApplicantsController extends Controller
{
    public function viewApplicants()
    {
        // Retrieve applications with related user, their form, and scholarship info
        $applications = Application::with(['user.form', 'scholarship'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Pass the applications to the view
        return view('central.applicants', compact('applications'));
    }
}
