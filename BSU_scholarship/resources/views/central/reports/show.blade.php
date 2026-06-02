@extends('layouts.focused')

@section('title', 'Report Details - Central Administration')
@section('navbar-title', $report->title)
@section('back-url', route('central.dashboard', ['tabs' => 'sfao-reports']))
@section('back-text', 'Back to Reports')
@section('content-width', 'max-w-[95%] 2xl:max-w-full')

@section('content')



    <!-- Actions Bar (No-Print) -->
    <div class="mb-6 flex justify-end items-center gap-3 print:hidden">
        @if($report->status === 'submitted')
            <button onclick="openReviewModal()" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-bsu-red hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bsu-red">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Review Report
            </button>
        @endif

        <button onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-bsu-red hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bsu-red">
            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print Report
        </button>
    </div>

    <!-- Main Report Paper (Official Content Only) -->
    <div id="printable-area" class="bg-white shadow-lg rounded-lg overflow-hidden min-h-[500px] p-8 sm:p-12 mb-8 print:shadow-none print:p-0 w-full mx-auto relative">
        
        <!-- Official BSU Header -->
        <div class="mb-10 text-center border-b-2 border-black pb-8">
            <div class="flex flex-col items-center mb-8">
                 <img src="{{ asset('images/lugo.png') }}" alt="BSU Logo" class="h-20 w-20 object-contain mb-4">
                 <div>
                    <h2 class="text-xl font-bold uppercase tracking-wider text-black leading-tight">Batangas State University</h2>
                    <h3 class="text-base font-semibold text-bsu-red uppercase leading-tight">The National Engineering University</h3>
                 </div>
            </div>
            
            <h1 class="text-2xl font-bold uppercase underline decoration-2 underline-offset-4 text-black mb-6">{{ $report->title }}</h1>
            
            <!-- Metadata Centered -->
            <div class="text-sm text-black font-medium space-y-1">
                <p><span class="font-bold">Campus:</span> {{ $report->campus->name ?? 'All Campuses' }}</p>
                <p><span class="font-bold">Period:</span> {{ $report->getPeriodDisplayName() }}</p>
                <p><span class="font-bold">Generated:</span> {{ $report->submitted_at ? $report->submitted_at->format('F d, Y') : $report->created_at->format('F d, Y') }}</p>
                <p><span class="font-bold">Prepared by:</span> {{ $report->sfaoUser->name ?? 'Unknown' }}</p>
            </div>
        </div>

        <!-- Main Report Content Table -->
        <div class="text-black text-left">
            @if(\Illuminate\Support\Str::startsWith($report->report_type, 'scholar_summary'))
                @php
                    $reportDataRaw = $report->report_data;
                    $reportDetails = $reportDataRaw['details'] ?? [];
                    $scholarshipId = $reportDataRaw['scholarship_id'] ?? null;
                    $selectedScholarship = $scholarshipId ? \App\Models\Scholarship::find($scholarshipId) : null;
                @endphp
                @include('sfao.reports.partials.scholar-summary-table', ['reportData' => $reportDetails, 'selectedScholarship' => $selectedScholarship])
            
             @elseif(\Illuminate\Support\Str::startsWith($report->report_type, 'student_summary'))
                 @php
                    $reportDataRaw = $report->report_data;
                    $reportDetails = $reportDataRaw['details'] ?? [];
                    // Extract subtype (applicants or scholars) from column or fallback
                    $studentType = $report->student_type ?? (\Illuminate\Support\Str::contains($report->report_type, 'scholars') ? 'scholars' : 'applicants');
                 @endphp
                 
                 @include('sfao.reports.partials.student-summary-table', [
                     'reportData' => $reportDetails, 
                     'studentType' => $studentType,
                     'dynamicTitle' => 'Student Summary Report - ' . ucfirst($studentType)
                 ])
                 
             @else
                <!-- Fallback for other report types -->
                <div class="p-4 border border-gray-200 rounded">
                    <h3 class="text-lg font-bold mb-2">Report Data</h3>
                    <pre class="bg-gray-50 p-4 rounded overflow-auto text-xs">{{ json_encode($report->report_data, JSON_PRETTY_PRINT) }}</pre>
                </div>
            @endif
        </div>

        <!-- Footer Signatures (Print Only) -->
        <div class="mt-16 break-inside-avoid hidden print:flex justify-between px-8">
            <div class="text-center">
                <div class="border-b border-black w-48 mb-2"></div>
                <p class="text-xs font-bold uppercase">Prepared By</p>
                <p class="text-xs">{{ $report->sfaoUser->name ?? 'Admin' }}</p>
            </div>
            <div class="text-center">
                <div class="border-b border-black w-48 mb-2"></div>
                <p class="text-xs font-bold uppercase">Certified Correct</p>
            </div>
            <div class="text-center">
                <div class="border-b border-black w-48 mb-2"></div>
                <p class="text-xs font-bold uppercase">Approved By</p>
            </div>
        </div>
    </div>

    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #printable-area, #printable-area * {
                visibility: visible;
            }
            #printable-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
            /* Ensure no other overlays interfere */
            nav, header, footer, .sidebar, .modal {
                display: none !important;
            }
        }
    </style>

    <!-- Metadata & Comments Container (Separate) -->
    <div class="no-print space-y-6 w-full">
        <!-- Description & Notes -->
        @if($report->description || $report->notes)
            <div class="bg-white shadow rounded-lg p-6 border border-gray-100">
                <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Report Details & Notes</h3>
                
                @if($report->description)
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Description</label>
                        <p class="text-gray-700 bg-gray-50 p-4 rounded-md">{{ $report->description }}</p>
                    </div>
                @endif

                @if($report->notes)
                    <div>
                        <label class="block text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">SFAO Notes</label>
                        <p class="text-gray-700 bg-yellow-50 p-4 rounded-md border border-yellow-100">{{ $report->notes }}</p>
                    </div>
                @endif
            </div>
        @endif
        <!-- Central Feedback (If exists) -->
        @if($report->central_feedback)
            <div class="bg-white shadow rounded-lg p-6 border border-gray-100">
                <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Central Administration Feedback</h3>
                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm text-blue-700 font-medium">
                                {{ $report->central_feedback }}
                            </p>
                            @if($report->reviewed_at)
                                <div class="mt-2 text-xs text-blue-500">
                                    Reviewed by {{ $report->reviewer->name ?? 'Admin' }} on {{ $report->reviewed_at->format('M d, Y') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    </div> <!-- End Report Paper -->

    <!-- Review Modal -->
    <div id="reviewModal" class="fixed z-10 inset-0 overflow-y-auto hidden no-print" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeReviewModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('central.reports.review', $report->id) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Review Report</h3>
                                <div class="mt-2 text-sm text-gray-500">
                                    Please evaluate the submitted report.
                                </div>
                                
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700">Action</label>
                                    <div class="mt-2 space-y-2">
                                        <div class="flex items-center">
                                            <input id="approve" name="action" type="radio" value="approve" checked class="focus:ring-bsu-red h-4 w-4 text-bsu-red border-gray-300">
                                            <label for="approve" class="ml-3 block text-sm font-medium text-gray-700">Approve Report</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input id="reject" name="action" type="radio" value="reject" class="focus:ring-bsu-red h-4 w-4 text-bsu-red border-gray-300">
                                            <label for="reject" class="ml-3 block text-sm font-medium text-gray-700">Reject Report (Requires Feedback)</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <label for="feedback" class="block text-sm font-medium text-gray-700">Feedback / Remarks</label>
                                    <textarea id="feedback" name="feedback" rows="3" class="shadow-sm focus:ring-bsu-red focus:border-bsu-red mt-1 block w-full sm:text-sm border border-gray-300 rounded-md" placeholder="Enter comments here..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Submit Review
                        </button>
                        <button type="button" onclick="closeReviewModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openReviewModal() {
            document.getElementById('reviewModal').classList.remove('hidden');
        }

        function closeReviewModal() {
            document.getElementById('reviewModal').classList.add('hidden');
        }
    </script>
@endsection