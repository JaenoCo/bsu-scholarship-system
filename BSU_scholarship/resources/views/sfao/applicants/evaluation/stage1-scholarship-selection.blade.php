@extends('layouts.focused')
@section('navbar-title', 'Document Evaluation')
@section('back-url', route('sfao.dashboard'))
@section('back-text', 'Back to Dashboard')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        @include('sfao.components.page-header', [
            'title' => 'Document Evaluation',
            'subtitle' => 'Stage 1: Select Scholarship to Evaluate'
        ])

        <!-- Student Info -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-8">
            <div class="flex items-center space-x-4">
                <div class="h-16 w-16 rounded-full bg-bsu-red flex items-center justify-center">
                    <span class="text-xl font-bold text-white">
                        {{ strtoupper(substr($student->name, 0, 2)) }}
                    </span>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $student->name }}</h2>
                    <p class="text-gray-600 dark:text-gray-400">{{ $student->email }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-500">{{ $student->campus->name }}</p>
                </div>
            </div>
        </div>

        <!-- Progress Indicator -->
        <div class="mb-8">
            <div class="flex items-center justify-center space-x-2">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-bsu-red text-white rounded-full flex items-center justify-center text-sm font-bold">1</div>
                    <span class="ml-2 text-sm font-medium text-gray-900 dark:text-white">Select Scholarship</span>
                </div>
                <div class="w-12 h-1 bg-gray-300 dark:bg-gray-600"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-400 rounded-full flex items-center justify-center text-sm font-bold">2</div>
                    <span class="ml-2 text-sm font-medium text-gray-500 dark:text-gray-400">SFAO Documents</span>
                </div>
                <div class="w-12 h-1 bg-gray-300 dark:bg-gray-600"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-400 rounded-full flex items-center justify-center text-sm font-bold">3</div>
                    <span class="ml-2 text-sm font-medium text-gray-500 dark:text-gray-400">Scholarship Documents</span>
                </div>
                <div class="w-12 h-1 bg-gray-300 dark:bg-gray-600"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-400 rounded-full flex items-center justify-center text-sm font-bold">4</div>
                    <span class="ml-2 text-sm font-medium text-gray-500 dark:text-gray-400">Final Review</span>
                </div>
            </div>
        </div>

        @if($applications->count() > 0)
            <!-- Applied Scholarships -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Applied Scholarships</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Select a scholarship to evaluate the student's submitted documents.</p>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($applications as $application)
                            @php
                                $scholarship = $application->scholarship;
                                $isEvaluated = in_array($application->status, ['approved', 'rejected']);
                            @endphp
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow {{ $isEvaluated ? 'opacity-75' : '' }}">
                                <div class="flex items-start justify-between mb-3">
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $scholarship->scholarship_name }}</h4>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $scholarship->getStatusBadge()['color'] }}">
                                        {{ $scholarship->getStatusBadge()['text'] }}
                                    </span>
                                </div>
                                
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ \Illuminate\Support\Str::limit($scholarship->description, 100) }}</p>
                                
                                <div class="space-y-2 mb-4">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">Type:</span>
                                        <span class="font-medium">{{ ucfirst($scholarship->scholarship_type) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">Priority:</span>
                                        <span class="font-medium">{{ ucfirst($scholarship->priority_level) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">Grant Amount:</span>
                                        <span class="font-medium">₱{{ number_format($scholarship->grant_amount, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                    </div>
                                </div>
                                
                                @if($isEvaluated)
                                    <!-- Show evaluation status -->
                                    <div class="text-center">
                                        <div class="mb-2">
                                            @if($application->status === 'approved')
                                                <div class="inline-flex items-center px-3 py-2 rounded-lg bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span class="font-semibold">Accepted</span>
                                                </div>
                                            @elseif($application->status === 'rejected')
                                                <div class="inline-flex items-center px-3 py-2 rounded-lg bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                    <span class="font-semibold">Rejected</span>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        @if($application->remarks)
                                            <div class="text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 rounded p-2 mb-2">
                                                <strong>Remarks:</strong> {{ \Illuminate\Support\Str::limit($application->remarks, 80) }}
                                            </div>
                                        @endif
                                        
                                        <div class="text-xs text-gray-400 dark:text-gray-500">
                                            Evaluated on {{ $application->updated_at->format('M d, Y g:i A') }}
                                        </div>
                                        
                                        <!-- View Details Button -->
                                        <a href="{{ route('sfao.evaluation.final', ['user_id' => $student->id, 'scholarship_id' => $scholarship->id, 'from_status' => 'true']) }}" 
                                           class="mt-2 inline-block text-xs text-bsu-red hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                            <span class="flex items-center gap-1">
                                                View Details
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                </svg>
                                            </span>
                                        </a>
                                    </div>
                                @else
                                    <!-- Show evaluation button for pending applications -->
                                    <a href="{{ route('sfao.evaluation.sfao-documents', ['user_id' => $student->id, 'scholarship_id' => $scholarship->id]) }}" 
                                       class="w-full bg-bsu-red text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors text-center block">
                                        Start Evaluation
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <!-- No Applications -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-8 text-center">
                <div class="text-gray-400 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-400 mb-2">No Applications Found</h3>
                <p class="text-gray-500 dark:text-gray-500">This student has not applied to any scholarships yet.</p>
            </div>
        @endif
    </div>
</div>
@endsection
