@extends('student.layouts.application')

@section('title', 'Apply for Scholarship')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            @include('student.partials.page-header', [
              'title' => 'Apply for Scholarship',
              'subtitle' => $scholarship->scholarship_name . ' - Grant Amount: ₱' . number_format($scholarship->grant_amount, 2) . ' | Deadline: ' . \Carbon\Carbon::parse($scholarship->submission_deadline)->format('M d, Y')
            ])
        </div>

        <!-- Progress Indicator -->
        <div class="mb-8">
            <div class="flex items-center justify-center space-x-4">
                <!-- Stage 1 -->
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-full border-4 {{ $currentStage >= 1 ? 'border-green-500 bg-green-50' : 'border-gray-300' }} flex items-center justify-center">
                        @if($currentStage > 1)
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        @else
                            <span class="text-lg font-semibold {{ $currentStage >= 1 ? 'text-green-600' : 'text-gray-600' }}">1</span>
                        @endif
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">SFAO Required Documents</p>
                        <p class="text-xs text-gray-500">Form 137, Grades, Application Form</p>
                    </div>
                </div>

                <!-- Connector -->
                <div class="flex-1 h-0.5 {{ $currentStage > 1 ? 'bg-green-500' : 'bg-gray-300' }}"></div>

                <!-- Stage 2 -->
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-full border-4 {{ $currentStage >= 2 ? 'border-green-500 bg-green-50' : 'border-gray-300' }} flex items-center justify-center">
                        @if($currentStage > 2)
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        @else
                            <span class="text-lg font-semibold {{ $currentStage >= 2 ? 'text-green-600' : 'text-gray-600' }}">2</span>
                        @endif
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Scholarship Required Documents</p>
                        <p class="text-xs text-gray-500">Additional documents</p>
                    </div>
                </div>

                <!-- Connector -->
                <div class="flex-1 h-0.5 {{ $currentStage > 2 ? 'bg-green-500' : 'bg-gray-300' }}"></div>

                <!-- Stage 3 -->
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-full border-4 {{ $currentStage >= 3 ? 'border-green-500 bg-green-50' : 'border-gray-300' }} flex items-center justify-center">
                        @if($currentStage > 3)
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        @else
                            <span class="text-lg font-semibold {{ $currentStage >= 3 ? 'text-green-600' : 'text-gray-600' }}">3</span>
                        @endif
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Confirmation & Submit</p>
                        <p class="text-xs text-gray-500">Review and submit application</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800 mb-2">Please fix the following errors:</p>
                        <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Main Content -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            @if($currentStage == 1)
                <!-- Stage 1: SFAO Required Documents -->
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Stage 1: SFAO Required Documents</h2>
                    <p class="text-gray-600">Please upload the following documents required by the SFAO office.</p>
                </div>

                <form method="POST" action="{{ route('student.apply.sfao-documents', $scholarship->id) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @php
                            $getDoc = function($name) use ($submittedDocuments) {
                                return $submittedDocuments->where('document_category', 'sfao_required')
                                    ->filter(function($d) use ($name) { return str_contains($d->document_name, $name); })
                                    ->first();
                            };
                            
                            $docs = [
                                'form_137' => ['name' => 'Form 137', 'required' => true],
                                'grades' => ['name' => 'Grades', 'required' => true],
                                'certificate' => ['name' => 'Certificate', 'required' => false],
                                'application_form' => ['name' => 'Application Form', 'required' => true],
                            ];
                        @endphp

                        @foreach($docs as $key => $config)
                            @php
                                $doc = $getDoc($config['name']);
                                $status = $doc ? $doc->evaluation_status : null;
                                $isApproved = $status === 'approved';
                                $isRejected = $status === 'rejected';
                            @endphp
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ $config['name'] }} @if($config['required'] && !$isApproved)<span class="text-red-500">*</span>@endif
                                    
                                    @if($isApproved)
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Approved
                                        </span>
                                    @elseif($isRejected)
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            Rejected
                                        </span>
                                    @elseif($status === 'pending')
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            Pending
                                        </span>
                                    @endif
                                </label>
                                
                                @if($isApproved)
                                    <div class="flex items-center p-3 text-sm text-gray-500 bg-gray-50 rounded-lg border border-gray-200">
                                        <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        {{ $doc->original_filename }}
                                    </div>
                                @else
                                    <input type="file" name="{{ $key }}" id="{{ $key }}" 
                                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error($key) border-red-500 @enderror"
                                           accept=".pdf,.jpg,.jpeg,.png,.docx" {{ ($config['required'] && !$isApproved) ? 'required' : '' }}>
                                    <p class="text-xs text-gray-500 mt-1">PDF, JPG, PNG, or DOCX (Max 10MB)</p>
                                    @if($isRejected && $doc->remarks)
                                        <p class="text-xs text-red-600 mt-1"><strong>Reason:</strong> {{ $doc->remarks }}</p>
                                    @endif
                                @endif
                                
                                @error($key)
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            Upload SFAO Documents
                        </button>
                    </div>
                </form>

            @elseif($currentStage == 2)
                <!-- Stage 2: Scholarship Required Documents -->
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Stage 2: Scholarship Required Documents</h2>
                    <p class="text-gray-600">Please upload the additional documents required for this specific scholarship.</p>
                </div>

                @if($scholarship->requiredDocuments->count() > 0)
                    <form method="POST" action="{{ route('student.apply.scholarship-documents', $scholarship->id) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="space-y-6">
                            @foreach($scholarship->requiredDocuments as $doc)
                                @php
                                    // Use check helper if it was defined in scope or redefine local logic
                                    // We need to match by document_name as stored in database
                                    $submittedDoc = $submittedDocuments->where('document_category', 'scholarship_required')
                                        ->where('document_name', $doc->document_name)
                                        ->first();
                                        
                                    $status = $submittedDoc ? $submittedDoc->evaluation_status : null;
                                    $isApproved = $status === 'approved';
                                    $isRejected = $status === 'rejected';
                                @endphp

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ strip_tags($doc->document_name) }}@if($doc->is_mandatory && !$isApproved)<span class="text-red-500">*</span>@endif

                                        @if($isApproved)
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Approved
                                            </span>
                                        @elseif($isRejected)
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                Rejected
                                            </span>
                                        @elseif($status === 'pending')
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                Pending
                                            </span>
                                        @endif
                                    </label>
                                    
                                    @if($isApproved)
                                        <div class="flex items-center p-3 text-sm text-gray-500 bg-gray-50 rounded-lg border border-gray-200">
                                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            {{ $submittedDoc->original_filename }}
                                        </div>
                                    @else
                                        <input type="file" name="scholarship_doc_{{ $doc->id }}" 
                                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                               accept=".pdf,.jpg,.jpeg,.png,.docx" {{ ($doc->is_mandatory && !$isApproved) ? 'required' : '' }}>
                                        <p class="text-xs text-gray-500 mt-1">PDF, JPG, PNG, or DOCX (Max 10MB)</p>
                                        @if($doc->description)
                                            <p class="text-xs text-gray-600 mt-1">{{ $doc->description }}</p>
                                        @endif
                                        @if($isRejected && $submittedDoc->remarks)
                                            <p class="text-xs text-red-600 mt-1"><strong>Reason:</strong> {{ $submittedDoc->remarks }}</p>
                                        @endif
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6 flex justify-between">
                            <a href="{{ route('student.apply', ['scholarship_id' => $scholarship->id, 'stage' => 1]) }}" 
                               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Back to Previous
                            </a>
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                Upload Scholarship Documents
                            </button>
                        </div>
                    </form>
                @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <p class="text-yellow-800">No additional documents required for this scholarship.</p>
                    </div>
                    <div class="flex justify-between">
                        <a href="{{ route('student.apply', ['scholarship_id' => $scholarship->id, 'stage' => 1]) }}" 
                           class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Previous
                        </a>
                        <a href="{{ route('student.apply', $scholarship->id) }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            Continue to Confirmation
                        </a>
                    </div>
                @endif

            @elseif($currentStage == 3)
                <!-- Stage 3: Confirmation -->
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Stage 3: Confirmation & Submit</h2>
                    <p class="text-gray-600">Review your submitted documents and submit your application.</p>
                </div>

                <!-- Submitted Documents Summary -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Submitted Documents</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($submittedDocuments as $doc)
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-green-800">{{ strip_tags($doc->document_name) }}</p>
                                            <p class="text-xs text-green-600">{{ ucfirst($doc->document_category) }} Document</p>
                                        </div>
                                    </div>
                                    <a href="{{ $doc->getViewUrl() }}" target="_blank" 
                                       class="inline-flex items-center p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-100 rounded-full transition-colors"
                                       title="View document">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Important Notice</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>Please review all your submitted documents carefully. Once you submit your application, it will be sent for review and you cannot make changes.</p>
                            </div>
                        </div>
                    </div>
                </div>

                @if($application)
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-green-800">Application Submitted</h3>
                                <div class="mt-2 text-sm text-green-700">
                                    <p>Your application has been successfully submitted and is now under review.</p>
                                    <p class="mt-1">Status: <span class="font-medium">{{ ucfirst($application->status) }}</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="flex justify-between">
                        <a href="{{ route('student.apply', ['scholarship_id' => $scholarship->id, 'stage' => 2]) }}" 
                           class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Previous
                        </a>
                        <form method="POST" action="{{ route('student.apply.final-submission', $scholarship->id) }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-green-600 text-white px-8 py-3 rounded-lg hover:bg-green-700 transition-colors font-semibold">
                                Submit Application
                            </button>
                        </form>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection