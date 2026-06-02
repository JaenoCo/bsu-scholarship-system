<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApplicationRequest;
use App\Services\ApplicationService;
use Illuminate\Http\Request;

class FormController extends Controller
{
    protected $applicationService;

    public function __construct(ApplicationService $applicationService)
    {
        $this->applicationService = $applicationService;
    }

    public function submit(StoreApplicationRequest $request)
    {
        $userId = session('user_id');
        $validated = $request->validated();

        // Process application via service
        $this->applicationService->processApplication($validated, $userId);

        // Handle Redirection Logic
        
        // 1. Save and Navigate
        if ($request->has('save_and_navigate') && $request->save_and_navigate) {
            return redirect($request->save_and_navigate)->with('success', 'Application saved successfully.');
        }

        // 2. Print after Save
        if ($request->has('print_after_save') && $request->print_after_save) {
            if ($request->filled('scholarship_id')) {
                return redirect()->route('student.print-application.scholarship', ['scholarship_id' => $request->scholarship_id])
                    ->with('success', 'Application saved successfully. Preparing your document...');
            } else {
                return redirect()->route('student.print-application')
                    ->with('success', 'Application saved successfully. Preparing your document...');
            }
        }

        // 3. Default Redirect
        return redirect('/student')->with('success', 'Application saved successfully.');
    }
}

