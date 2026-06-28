<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ScholarshipController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\FormPrintController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentApplicationController;
use App\Http\Controllers\CentralApplicationController;
use Illuminate\Http\Request;

// =================================================================
// 0. DEV TOOLS & SEEDING FIXES
// =================================================================

Route::get('/dev/fix-dates', function() {
    $now = now();
    $count = 0;
    
    // Fix Applications
    $apps = \App\Models\Application::where('created_at', '>', $now)->get();
    foreach ($apps as $app) {
        // Shift back 1 year
        $newDate = \Carbon\Carbon::parse($app->created_at)->subYear();
        $app->created_at = $newDate;
        $app->updated_at = $newDate;
        $app->save();
        $count++;
    }

    // Fix Scholars
    $scholars = \App\Models\Scholar::where('created_at', '>', $now)->get();
    foreach ($scholars as $s) {
        $newDate = \Carbon\Carbon::parse($s->created_at)->subYear();
        $s->created_at = $newDate;
        $s->updated_at = $newDate;
        $s->save();
    }
    
    return "Fixed $count applications with future dates.";
});

// TEMPORARY: Database Reset
Route::get('/dev/migrate-fresh-seed', function() {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh', [
            '--seed' => true,
            '--force' => true
        ]);
        return "Database Reset and Seeded Successfully!<br><pre>" . \Illuminate\Support\Facades\Artisan::output() . "</pre>";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

// SAFE MIGRATION (Update Structure Only)
Route::get('/dev/migrate', function() {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', [
            '--force' => true
        ]);
        return "Migration (Structure Update) Completed!<br><pre>" . \Illuminate\Support\Facades\Artisan::output() . "</pre>";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

// TEMPORARY: View Logs
Route::get('/dev/logs', function() {
    $path = storage_path('logs/laravel.log');
    if (!file_exists($path)) return "No log file found.";
    
    $content = file_get_contents($path);
    // Get last 20000 chars roughly
    if (strlen($content) > 20000) {
        $content = substr($content, -20000);
    }
    
    return "<h1>Last Log Entries</h1><pre>" . htmlspecialchars($content) . "</pre>";
});

// TEMPORARY: Scholar Debug
Route::get('/dev/scholar-debug', function() {
    if (!session()->has('user_id') || session('role') !== 'sfao') {
        return redirect('/login');
    }
    return view('dev.scholar-debug');
});

// --------------------------------------------------
// PUBLIC ROUTES
// --------------------------------------------------

Route::get('/', function () {
    if (session()->has('user_id')) {
        return redirect(match (session('role')) {
            'student' => route('student.dashboard'),
            'sfao'    => route('sfao.dashboard') . '?tabs=overview',
            'central' => route('central.dashboard', ['tabs' => 'overview']),
            default   => '/'
        });
    }
    return view('home');
});

// --------------------------------------------------
// AUTHENTICATION ROUTES
// --------------------------------------------------

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset Routes
Route::get('password/reset', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');


// Central Admin Login
Route::get('/central/login', [AuthController::class, 'showCentralLogin'])->name('central.login');
Route::post('/central/login', [AuthController::class, 'centralLogin']);

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);


// Shared Application Forms Download (accessible by students and SFAO)
Route::get('/application-forms/{id}/download', [App\Http\Controllers\ApplicationFormController::class, 'download'])->name('application-forms.download')->middleware(['web', 'checkUserExists']);

// Email Verification
Route::get('/email/verify', [AuthController::class, 'showVerificationNotice'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])->name('verification.send');
Route::post('/email/resend', [AuthController::class, 'resendVerificationByEmail'])->name('verification.resend');

// Registration Waiting Flow
Route::get('/register/wait', [AuthController::class, 'showWaitingPage'])->name('register.wait');
Route::get('/register/check-status', [AuthController::class, 'checkVerificationStatus'])->name('verification.check');
Route::post('/register/resend', [AuthController::class, 'resendRegistrationVerification'])->name('verification.resend.post');

// --------------------------------------------------
// SHARED ROUTES
// --------------------------------------------------

// Session Keep-Alive
Route::get('/ping', function() {
    return response()->noContent();
})->middleware('web');

// Profile Picture Upload
Route::post('/upload-profile-picture/{role}', [UserController::class, 'uploadProfilePicture'])
    ->whereIn('role', ['student', 'sfao', 'central']);

// Document Viewer (for DOCX files)
Route::get('/document/view/{id}', [UserController::class, 'viewDocument'])->name('document.view');

// --------------------------------------------------
// STUDENT ROUTES
// --------------------------------------------------

Route::middleware(['web', 'checkUserExists', 'role:student'])->prefix('student')->name('student.')->group(function () {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');
    
    // Scholarships
    Route::get('/scholarships', [UserController::class, 'scholarships'])->name('scholarships');
    
    // Application Form
    Route::get('/sfao-form', [UserController::class, 'showApplicationForm'])->name('forms.application_form');
    Route::get('/tdp-form', [UserController::class, 'showTdpApplicationForm'])->name('forms.tdp_application_form');
    Route::get('/form/{scholarship_id}', [UserController::class, 'showApplicationForm'])->name('forms.application_form.scholarship');
    Route::post('/submit-application', [FormController::class, 'submit'])->name('submit');
    
    // Applications
    Route::get('/applications', function() {
        return redirect()->route('student.dashboard', ['tab' => 'applied_scholarships']);
    })->name('applications');
    Route::post('/apply', [ApplicationController::class, 'apply'])->name('apply.post');
    Route::post('/withdraw', [ApplicationController::class, 'withdraw'])->name('withdraw');
    
    // Document Uploads (Legacy - Redirect to new multi-stage application)
    Route::get('/upload-documents/{scholarship_id}', function($scholarship_id) {
        return redirect()->route('student.apply', ['scholarship_id' => $scholarship_id]);
    })->name('upload-documents');
    Route::post('/upload-documents/{scholarship_id}', [UserController::class, 'uploadDocuments'])->name('upload-documents.submit');
    
    // Multi-Stage Application
    Route::get('/apply/{scholarship_id}', [UserController::class, 'showMultiStageApplication'])->name('apply');
    Route::post('/apply/{scholarship_id}/sfao-documents', [UserController::class, 'submitSfaoDocuments'])->name('apply.sfao-documents');
    Route::post('/apply/{scholarship_id}/scholarship-documents', [UserController::class, 'submitScholarshipDocuments'])->name('apply.scholarship-documents');
    Route::post('/apply/{scholarship_id}/final-submission', [UserController::class, 'submitFinalApplication'])->name('apply.final-submission');
    Route::get('/apply/{scholarship_id}/progress', [UserController::class, 'getApplicationProgress'])->name('apply.progress');
    
    // Print Application
    Route::get('/print-application', [FormPrintController::class, 'printApplication'])->name('print-application');
    Route::get('/print-application/{scholarship_id}', [FormPrintController::class, 'printApplication'])->name('print-application.scholarship');
    Route::get('/download-file', [FormPrintController::class, 'downloadFile'])->name('download-file');


    // Application Forms
    Route::get('/application-forms', [App\Http\Controllers\ApplicationFormController::class, 'studentIndex'])->name('application-forms.index');

    // Change Password
    Route::post('/change-password', [UserController::class, 'changePassword'])->name('change-password');
});

// --------------------------------------------------
// SFAO ROUTES
// --------------------------------------------------

Route::middleware(['web', 'checkUserExists:sfao', 'role:sfao'])->prefix('sfao')->name('sfao.')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');
    
    // Applicants
    Route::get('/applicants/{user_id}/documents', [ApplicationController::class, 'viewDocuments'])->name('viewDocuments');
    
    // Document Evaluation System
    Route::get('/evaluation/{user_id}', [ApplicationController::class, 'showEvaluation'])->name('evaluation.show');
    Route::get('/evaluation/{user_id}/scholarship/{scholarship_id}/sfao-documents', [ApplicationController::class, 'evaluateSfaoDocuments'])->name('evaluation.sfao-documents');
    Route::post('/evaluation/{user_id}/scholarship/{scholarship_id}/sfao-documents/evaluate', [ApplicationController::class, 'submitSfaoEvaluation'])->name('evaluation.sfao-submit');
    Route::get('/evaluation/{user_id}/scholarship/{scholarship_id}/scholarship-documents', [ApplicationController::class, 'evaluateScholarshipDocuments'])->name('evaluation.scholarship-documents');
    Route::post('/evaluation/{user_id}/scholarship/{scholarship_id}/scholarship-documents/evaluate', [ApplicationController::class, 'submitScholarshipEvaluation'])->name('evaluation.scholarship-submit');
    Route::get('/evaluation/{user_id}/scholarship/{scholarship_id}/final', [ApplicationController::class, 'finalEvaluation'])->name('evaluation.final');
    Route::post('/evaluation/{user_id}/scholarship/{scholarship_id}/final/submit', [ApplicationController::class, 'submitFinalEvaluation'])->name('evaluation.final-submit');
    
    // Application Management
    Route::post('/applications/{id}/approve', [ApplicationController::class, 'sfaoApproveApplication'])->name('applications.approve');
    Route::post('/applications/{id}/reject', [ApplicationController::class, 'sfaoRejectApplication'])->name('applications.reject');
    // Applicant Management
    Route::get('/applicants/list', [ApplicationController::class, 'sfaoApplicantsList'])->name('applicants.list');
    Route::get('/applicants', [ApplicationController::class, 'sfaoApplicants'])->name('applicants');
    Route::get('/applicant/{user_id}/documents', [ApplicationController::class, 'viewDocuments'])->name('applicant.documents');
    Route::post('/scholarships/import', [App\Http\Controllers\ScholarshipImportController::class, 'store'])->name('scholarships.import');

    Route::post('/applications/{id}/claim', [ApplicationController::class, 'sfaoClaimGrant'])->name('applications.claim');

    // Scholarships Management
    Route::post('/scholarships/store', [ScholarshipController::class, 'store'])->name('scholarships.store');
    Route::post('/scholarships/{id}/update', [ScholarshipController::class, 'update'])->name('scholarships.update');
    
    Route::post('/scholarships/{id}/release-grant', [ScholarshipController::class, 'releaseGrant'])->name('scholarships.release-grant');
    Route::get('/scholarships', [ScholarshipController::class, 'sfaoIndex'])->name('scholarships.index');
    Route::get('/scholarships/{id}', [ScholarshipController::class, 'show'])->name('scholarships.show');
    Route::post('/scholars/bulk-mark-claimed', [ScholarshipController::class, 'bulkMarkScholarAsClaimed'])->name('scholars.bulk-mark-claimed');
    Route::post('/scholars/{id}/mark-claimed', [ScholarshipController::class, 'markScholarAsClaimed'])->name('scholars.mark-claimed');
    
    // Reports Management (Keep original for now as it wasn't split yet)
    Route::get('/reports/create', [ReportController::class, 'createReport'])->name('reports.create');
    Route::post('/reports', [ReportController::class, 'storeReport'])->name('reports.store');
    Route::get('/reports/{id}', [ReportController::class, 'showReport'])->name('reports.show');
    Route::post('/reports/summary-submit', [ReportController::class, 'submitSummaryReport'])->name('reports.summary-submit');
    Route::get('/reports/{id}/edit', [ReportController::class, 'editReport'])->name('reports.edit');
    Route::put('/reports/{id}', [ReportController::class, 'updateReport'])->name('reports.update');
    Route::post('/reports/{id}/submit', [ReportController::class, 'submitReport'])->name('reports.submit');
    Route::delete('/reports/{id}', [ReportController::class, 'deleteReport'])->name('reports.delete');
    Route::post('/reports/generate-data', [ReportController::class, 'generateReportData'])->name('reports.generate-data');
    
    // Specific Report Summaries
    Route::get('/student-summary', [ReportController::class, 'studentSummary'])->name('reports.student-summary');
    Route::get('/scholar-summary', function() {
        return redirect()->route('sfao.reports.student-summary', ['student_type' => 'scholars']);
    })->name('reports.scholar-summary');
    Route::get('/grant-summary', [ReportController::class, 'grantSummary'])->name('reports.grant-summary');
    
    
    // Application Forms Management
    Route::get('/application-forms', [App\Http\Controllers\ApplicationFormController::class, 'index'])->name('application-forms.index');
    Route::get('/application-forms/create', [App\Http\Controllers\ApplicationFormController::class, 'create'])->name('application-forms.create');
    Route::post('/application-forms', [App\Http\Controllers\ApplicationFormController::class, 'store'])->name('application-forms.store');
    Route::delete('/application-forms/{id}', [App\Http\Controllers\ApplicationFormController::class, 'destroy'])->name('application-forms.destroy');
    
    // Change Password
    Route::post('/change-password', [UserController::class, 'changePassword'])->name('change-password');
    // Profile Update
    Route::put('/profile/update', [UserController::class, 'updateProfile'])->name('profile.update');
});

// --------------------------------------------------
// CENTRAL ROUTES
// --------------------------------------------------

Route::middleware(['web', 'checkUserExists:central', 'role:central'])
    ->prefix('central')
    ->name('central.')
    ->group(function () {

    // Dashboard (Unified)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');
        
        // Analytics
        Route::post('/analytics/filtered', [ApplicationController::class, 'getFilteredAnalytics'])->name('analytics.filtered');

        // Scholarships
        Route::prefix('scholarships')->name('scholarships.')->group(function () {
            Route::get('/create', [ScholarshipController::class, 'create'])->name('create');
            Route::post('/store', [ScholarshipController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [ScholarshipController::class, 'edit'])->name('edit');
            Route::put('/{id}', [ScholarshipController::class, 'update'])->name('update');
            Route::delete('/{id}', [ScholarshipController::class, 'destroy'])->name('destroy');
        });

        
        // Application Management
        Route::post('/applications/{id}/approve', [ApplicationController::class, 'centralApproveApplication'])->name('applications.approve');
        Route::post('/applications/{id}/reject', [ApplicationController::class, 'centralRejectApplication'])->name('applications.reject');
        Route::post('/applications/{id}/claim', [ApplicationController::class, 'centralClaimGrant'])->name('applications.claim');
        
        // Staff Management
        Route::post('/staff/invite', [UserController::class, 'inviteStaff'])->name('staff.invite');
        Route::post('/staff/{id}/deactivate', [UserController::class, 'deactivateStaff'])->name('staff.deactivate');
        
        // Account Settings
        Route::post('/update-name', [UserController::class, 'updateName'])->name('update-name');
        Route::post('/change-password', [UserController::class, 'changePassword'])->name('change-password');
        
        // Reports Management
        Route::get('/reports/{id}', [ReportController::class, 'centralShowReport'])->name('reports.show');
        Route::post('/reports/{id}/review', [ReportController::class, 'reviewReport'])->name('reports.review');
        
        // Scholars Management
        Route::prefix('scholars')->name('scholars.')->group(function () {
            Route::get('/', [App\Http\Controllers\ScholarController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\ScholarController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\ScholarController::class, 'store'])->name('store');
            Route::get('/{scholar}', [App\Http\Controllers\ScholarController::class, 'show'])->name('show');
            Route::get('/{scholar}/edit', [App\Http\Controllers\ScholarController::class, 'edit'])->name('edit');
            Route::put('/{scholar}', [App\Http\Controllers\ScholarController::class, 'update'])->name('update');
            Route::delete('/{scholar}', [App\Http\Controllers\ScholarController::class, 'destroy'])->name('destroy');
            Route::get('/statistics', [App\Http\Controllers\ScholarController::class, 'statistics'])->name('statistics');
            Route::post('/{scholar}/add-grant', [App\Http\Controllers\ScholarController::class, 'addGrant'])->name('add-grant');
        });

        // Endorsed Applicants Validation
        Route::get('/endorsed-applications/{application}/validate', [ApplicationController::class, 'showEndorsedValidation'])->name('endorsed.validate');
        Route::post('/endorsed-applications/{application}/accept', [ApplicationController::class, 'acceptEndorsed'])->name('endorsed.accept');
        Route::post('/endorsed-applications/{application}/reject', [ApplicationController::class, 'rejectEndorsed'])->name('endorsed.reject');
        
        // Rejected Applicants
        Route::get('/rejected-applicants', [ApplicationController::class, 'viewRejectedApplicants'])->name('rejected-applicants');
    });


// --------------------------------------------------
// SFAO PASSWORD SETUP ROUTES
// --------------------------------------------------

// SFAO password setup after email verification
Route::get('/sfao/password-setup', [UserController::class, 'showSFAOPasswordSetup'])->name('sfao.password.setup');
Route::post('/sfao/password-setup', [UserController::class, 'setupSFAOPassword'])->name('sfao.password.setup');

// =====================================================
// NOTIFICATION ROUTES
// =====================================================

Route::middleware(['web'])->group(function () {
    Route::post('/notifications/{id}/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/{id}/mark-unread', [App\Http\Controllers\NotificationController::class, 'markAsUnread'])->name('notifications.mark-unread');
    Route::post('/notifications/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
});
