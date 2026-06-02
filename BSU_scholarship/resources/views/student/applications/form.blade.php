@extends('layouts.focused')

@section('page-title', 'Apply for Scholarship')

{{-- Navbar Configuration --}}
@section('navbar-title', 'Scholarship Application')
@section('back-url', route('student.dashboard'))
@section('back-text', 'Back to Dashboard')

@section('content')
<div class="bg-gray-50 dark:bg-gray-900 min-h-screen py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="mb-10 text-center">
            <h1 class="text-3xl font-extrabold text-red-600 dark:text-red-400 mb-2">Apply for Scholarship</h1>
            <div class="inline-flex items-center px-4 py-2 rounded-full bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800">
                <span class="text-red-700 dark:text-red-300 font-medium text-sm">
                    {{ $scholarship->scholarship_name }}
                </span>
                <span class="mx-3 text-gray-300 dark:text-gray-600">|</span>
                <span class="text-gray-600 dark:text-gray-400 text-sm">
                    Grant: <span class="font-semibold text-red-600 dark:text-red-400">₱{{ number_format($scholarship->grant_amount, 2) }}</span>
                </span>
                <span class="mx-3 text-gray-300 dark:text-gray-600">|</span>
                <span class="text-gray-600 dark:text-gray-400 text-sm">
                    Deadline: <span class="font-semibold text-red-600 dark:text-red-400">{{ \Carbon\Carbon::parse($scholarship->submission_deadline)->format('M d, Y') }}</span>
                </span>
            </div>
        </div>

        <!-- Progress Stepper -->
        <div class="mb-12">
            <div class="relative after:absolute after:inset-x-0 after:top-1/2 after:block after:h-0.5 after:-translate-y-1/2 after:rounded-lg after:bg-gray-200 dark:after:bg-gray-700">
                <ol class="relative z-10 flex justify-between text-sm font-medium text-gray-500 dark:text-gray-400">
                    <li class="flex items-center gap-2 p-2 bg-gray-50 dark:bg-gray-900">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full {{ $currentStage >= 1 ? 'bg-red-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }} ring-4 ring-gray-50 dark:ring-gray-900">
                            @if($currentStage > 1)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            @else
                                1
                            @endif
                        </span>
                        <span class="{{ $currentStage >= 1 ? 'text-red-600 dark:text-red-400 font-bold' : '' }}">SFAO Requirements</span>
                    </li>
                    <li class="flex items-center gap-2 p-2 bg-gray-50 dark:bg-gray-900">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full {{ $currentStage >= 2 ? 'bg-red-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }} ring-4 ring-gray-50 dark:ring-gray-900">
                            @if($currentStage > 2)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            @else
                                2
                            @endif
                        </span>
                        <span class="{{ $currentStage >= 2 ? 'text-red-600 dark:text-red-400 font-bold' : '' }}">Scholarship Requirements</span>
                    </li>
                    <li class="flex items-center gap-2 p-2 bg-gray-50 dark:bg-gray-900">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full {{ $currentStage >= 3 ? 'bg-red-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }} ring-4 ring-gray-50 dark:ring-gray-900">
                            3
                        </span>
                        <span class="{{ $currentStage >= 3 ? 'text-red-600 dark:text-red-400 font-bold' : '' }}">Review & Submit</span>
                    </li>
                </ol>
            </div>
        </div>

        {{-- Messages removed - now shown in modals --}}

        <!-- Main Form Content -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden border border-gray-100 dark:border-gray-700">
            @if($currentStage == 1)
                <!-- Stage 1 Content -->
                <div class="p-8">
                    <div class="mb-8 border-b border-gray-100 dark:border-gray-700 pb-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">SFAO Required Documents</h2>
                        <p class="text-gray-500 dark:text-gray-400">Please verify and upload the standard documents required by the Scholarship Office.</p>
                    </div>

                    <form method="POST" action="{{ route('student.apply.sfao-documents', $scholarship->id) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            @php
                                $getDoc = function($name) use ($submittedDocuments) {
                                    return $submittedDocuments->where('document_category', 'sfao_required')
                                        ->filter(function($d) use ($name) { return str_contains($d->document_name, $name); })
                                        ->first();
                                };
                                
                                $docs = [
                                    'form_137' => ['name' => 'Form 137', 'required' => true, 'desc' => 'Your high school report card or transcript.'],
                                    'grades' => ['name' => 'Grades', 'required' => true, 'desc' => 'Recent copy of grades or certificate of grades.'],
                                    'certificate' => ['name' => 'Certificate', 'required' => false, 'desc' => 'Certificate of Good Moral Character (optional).'],
                                    'application_form' => ['name' => 'Application Form', 'required' => true, 'desc' => 'Duly accomplished scholarship application form.'],
                                ];
                            @endphp

                            @foreach($docs as $key => $config)
                                @php
                                    $doc = $getDoc($config['name']);
                                    $status = $doc ? $doc->evaluation_status : null;
                                    $isApproved = $status === 'approved';
                                    $isRejected = $status === 'rejected';
                                    $isPending = $status === 'pending';
                                @endphp
                                
                                <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-5 border border-gray-100 dark:border-gray-700 transition-all hover:shadow-md">
                                    <div class="flex justify-between items-start mb-3">
                                        <label class="block text-base font-bold text-gray-900 dark:text-white">
                                            {{ $config['name'] }}
                                            @if($config['required'] && !$isApproved)
                                                <span class="text-red-500 ml-1">*</span>
                                            @endif
                                        </label>
                                        
                                        @if($isApproved)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Approved
                                            </span>
                                        @elseif($isRejected)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                Rejected
                                            </span>
                                        @elseif($isPending)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                Pending
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-200">
                                                Not Uploaded
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">{{ $config['desc'] }}</p>

                                    @if($isApproved)
                                        <div class="flex items-center p-3 text-sm text-green-700 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-100 dark:border-green-800">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            <span class="truncate">{{ $doc->original_filename }}</span>
                                        </div>
                                    @else
                                        <div class="relative">
                                            <input type="file" name="{{ $key }}" id="{{ $key }}" 
                                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-red-600 file:text-white hover:file:bg-red-700 cursor-pointer"
                                                   accept=".pdf,.jpg,.jpeg,.png,.docx" {{ ($config['required'] && !$isApproved) ? 'required' : '' }}>
                                        </div>
                                        <p class="text-xs text-gray-400 mt-2">Max 10MB (PDF, JPG, PNG, DOCX)</p>
                                        @if($isRejected && $doc->remarks)
                                            <div class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 text-xs rounded-lg border border-red-100 dark:border-red-800">
                                                <strong>Correction Needed:</strong> {{ $doc->remarks }}
                                            </div>
                                        @endif
                                    @endif
                                    
                                    @error($key)
                                        <p class="text-xs text-red-500 mt-2 font-medium">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-8 flex justify-end pt-6 border-t border-gray-100 dark:border-gray-700">
                            <button type="submit" class="inline-flex items-center px-8 py-3 bg-white border-2 border-red-600 text-red-600 text-sm font-bold uppercase tracking-wide rounded-xl shadow-lg hover:bg-red-50 hover:shadow-xl hover:translate-y-[-1px] transition-all duration-200">
                                Upload & Continue
                                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </button>
                        </div>
                    </form>
                </div>

            @elseif($currentStage == 2)
                <!-- Stage 2 Content -->
                <div class="p-8">
                    <div class="mb-8 border-b border-gray-100 dark:border-gray-700 pb-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Scholarship Required Documents</h2>
                        <p class="text-gray-500 dark:text-gray-400">Additional requirements specific to <span class="font-semibold text-blue-600">{{ $scholarship->scholarship_name }}</span>.</p>
                    </div>

                    @if($scholarship->requiredDocuments->count() > 0)
                        <form method="POST" action="{{ route('student.apply.scholarship-documents', $scholarship->id) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                @foreach($scholarship->requiredDocuments as $doc)
                                    @php
                                        $submittedDoc = $submittedDocuments->where('document_category', 'scholarship_required')
                                            ->where('document_name', $doc->document_name)
                                            ->first();
                                            
                                        $status = $submittedDoc ? $submittedDoc->evaluation_status : null;
                                        $isApproved = $status === 'approved';
                                        $isRejected = $status === 'rejected';
                                        $isPending = $status === 'pending';
                                    @endphp

                                    <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-5 border border-gray-100 dark:border-gray-700 transition-all hover:shadow-md">
                                        <div class="flex justify-between items-start mb-3">
                                            <label class="block text-base font-bold text-gray-900 dark:text-white">
                                                {{ strip_tags($doc->document_name) }}
                                                @if($doc->is_mandatory && !$isApproved)
                                                    <span class="text-red-500 ml-1">*</span>
                                                @endif
                                            </label>
                                            
                                            @if($isApproved)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                                                    Approved
                                                </span>
                                            @elseif($isRejected)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300">
                                                    Rejected
                                                </span>
                                            @elseif($isPending)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300">
                                                    Pending
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-200">
                                                    Not Uploaded
                                                </span>
                                            @endif
                                        </div>
                                        
                                        @if($doc->description)
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">{{ $doc->description }}</p>
                                        @endif
                                        
                                        @if($isApproved)
                                            <div class="flex items-center p-3 text-sm text-green-700 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-100 dark:border-green-800">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                <span class="truncate">{{ $submittedDoc->original_filename }}</span>
                                            </div>
                                        @else
                                            <div class="relative">
                                                <input type="file" name="scholarship_doc_{{ $doc->id }}" 
                                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-red-600 file:text-white hover:file:bg-red-700 cursor-pointer"
                                                       accept=".pdf,.jpg,.jpeg,.png,.docx" {{ ($doc->is_mandatory && !$isApproved) ? 'required' : '' }}>
                                            </div>
                                            <p class="text-xs text-gray-400 mt-2">Max 10MB (PDF, JPG, PNG, DOCX)</p>
                                            @if($isRejected && $submittedDoc->remarks)
                                                <div class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 text-xs rounded-lg border border-red-100 dark:border-red-800">
                                                    <strong>Correction Needed:</strong> {{ $submittedDoc->remarks }}
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-8 flex justify-between items-center pt-6 border-t border-gray-100 dark:border-gray-700">
                                <a href="{{ route('student.apply', ['scholarship_id' => $scholarship->id, 'stage' => 1]) }}" 
                                   class="inline-flex items-center px-6 py-2.5 border border-gray-300 shadow-sm text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                    Previous Step
                                </a>
                                <button type="submit" class="inline-flex items-center px-8 py-3 bg-white border-2 border-red-600 text-red-600 text-sm font-bold uppercase tracking-wide rounded-xl shadow-lg hover:bg-red-50 hover:shadow-xl hover:translate-y-[-1px] transition-all duration-200">
                                    Continue
                                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="bg-red-50 dark:bg-red-900/30 border border-red-100 dark:border-red-800 rounded-xl p-8 text-center mb-8">
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 mb-4">
                                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h3 class="text-lg font-medium text-red-900 dark:text-red-300">All Set!</h3>
                            <p class="text-red-700 dark:text-red-400 mt-2">No additional documents are required for this scholarship.</p>
                        </div>
                        <div class="flex justify-between items-center pt-6 border-t border-gray-100 dark:border-gray-700">
                            <a href="{{ route('student.apply', ['scholarship_id' => $scholarship->id, 'stage' => 1]) }}" 
                               class="inline-flex items-center px-6 py-2.5 border border-gray-300 shadow-sm text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                Previous Step
                            </a>
                            <a href="{{ route('student.apply', $scholarship->id) }}" class="inline-flex items-center px-8 py-3 bg-white border-2 border-red-600 text-red-600 text-sm font-bold uppercase tracking-wide rounded-xl shadow-lg hover:bg-red-50 hover:shadow-xl hover:translate-y-[-1px] transition-all duration-200">
                                Continue to Review
                                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </a>
                        </div>
                    @endif
                </div>

            @elseif($currentStage == 3)
                <!-- Stage 3: Confirmation -->
                <div class="p-8">
                    <div class="mb-8 border-b border-gray-100 dark:border-gray-700 pb-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Review & Submit</h2>
                        <p class="text-gray-500 dark:text-gray-400">Please review your submitted documents before finalizing your application.</p>
                    </div>

                    <!-- Submitted Documents Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                        @foreach($submittedDocuments as $doc)
                            <div class="bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-lg bg-red-100 dark:bg-red-900/50 flex items-center justify-center text-red-600 dark:text-red-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ strip_tags($doc->document_name) }}</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ ucfirst($doc->document_category) }} Phase</p>
                                    </div>
                                </div>
                                <a href="{{ $doc->getViewUrl() }}" target="_blank" 
                                   class="text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors p-2"
                                   title="View Document">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                            </div>
                        @endforeach
                    </div>

                    @if($application)
                         <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl p-8 text-center mb-8">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 dark:bg-green-900 mb-4">
                                <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-green-900 dark:text-green-300">Application Submitted!</h3>
                            <p class="text-green-700 dark:text-green-400 mt-2">Your application is now under review. Good luck!</p>
                            <div class="mt-6">
                                <span class="px-4 py-2 rounded-lg bg-green-200 dark:bg-green-800 text-green-800 dark:text-green-200 font-semibold text-sm">
                                    Status: {{ ucfirst($application->status) }}
                                </span>
                            </div>
                         </div>
                    @else
                        <div class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-xl p-6 mb-8 flex items-start">
                            <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <div>
                                <h4 class="text-sm font-bold text-yellow-900 dark:text-yellow-300">Final Confirmation</h4>
                                <p class="text-sm text-yellow-800 dark:text-yellow-400 mt-1">By submitting this application, you certify that all information provided is true and correct. Once submitted, you cannot make changes to your attached documents.</p>
                            </div>
                        </div>

                        <div class="flex justify-between items-center pt-6 border-t border-gray-100 dark:border-gray-700">
                            <a href="{{ route('student.apply', ['scholarship_id' => $scholarship->id, 'stage' => 2]) }}" 
                               class="inline-flex items-center px-6 py-2.5 border border-gray-300 shadow-sm text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                Previous Step
                            </a>
                            <form method="POST" action="{{ route('student.apply.final-submission', $scholarship->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-8 py-3 bg-white border-2 border-green-600 text-green-600 text-sm font-bold uppercase tracking-wide rounded-xl shadow-lg hover:bg-green-50 hover:shadow-xl hover:translate-y-[-1px] transition-all duration-200">
                                    Submit Application
                                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Success Message Modal --}}
@if(session('success'))
<div x-data="{ showModal: true }" 
     x-show="showModal"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true">
    
    {{-- Background Overlay --}}
    <div x-show="showModal"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm"
         @click="showModal = false"></div>

    {{-- Modal Content --}}
    <div x-show="showModal"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-8 transform transition-all">
        
        {{-- Success Icon --}}
        <div class="flex justify-center mb-6">
            <div class="flex items-center justify-center h-20 w-20 rounded-full bg-green-100 dark:bg-green-900/30">
                <svg class="h-12 w-12 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>
        
        {{-- Title and Message --}}
        <div class="text-center mb-8">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3" id="modal-title">
                Success!
            </h3>
            <p class="text-base text-gray-600 dark:text-gray-400 leading-relaxed">
                {{ session('success') }}
            </p>
        </div>
        
        {{-- Action Button --}}
        <button type="button"
                @click="showModal = false"
                class="w-full inline-flex justify-center items-center px-6 py-3 bg-green-600 text-white text-base font-semibold rounded-xl shadow-lg hover:bg-green-700 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
            Continue
            <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
            </svg>
        </button>
    </div>
</div>
@endif

{{-- Error Message Modal --}}
@if(session('error'))
<div x-data="{ showModal: true }" 
     x-show="showModal"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true">
    
    {{-- Background Overlay --}}
    <div x-show="showModal"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm"
         @click="showModal = false"></div>

    {{-- Modal Content --}}
    <div x-show="showModal"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-8 transform transition-all">
        
        {{-- Error Icon --}}
        <div class="flex justify-center mb-6">
            <div class="flex items-center justify-center h-20 w-20 rounded-full bg-red-100 dark:bg-red-900/30">
                <svg class="h-12 w-12 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
        </div>
        
        {{-- Title and Message --}}
        <div class="text-center mb-8">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3" id="modal-title">
                Error
            </h3>
            <p class="text-base text-gray-600 dark:text-gray-400 leading-relaxed">
                {{ session('error') }}
            </p>
        </div>
        
        {{-- Action Button --}}
        <button type="button"
                @click="showModal = false"
                class="w-full inline-flex justify-center items-center px-6 py-3 bg-red-600 text-white text-base font-semibold rounded-xl shadow-lg hover:bg-red-700 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200">
            Close
        </button>
    </div>
</div>
@endif

@endsection