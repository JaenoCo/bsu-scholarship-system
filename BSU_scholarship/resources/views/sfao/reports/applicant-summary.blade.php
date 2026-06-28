@extends('layouts.focused')

@section('title', 'Applicant Summary Report')
@section('navbar-title', 'Applicant Summary Report')
@section('back-url', route('sfao.dashboard', ['tabs' => 'reports_applicant_summary']))
@section('back-text', 'Back to Reports')
@section('content-width', 'max-w-[95%] 2xl:max-w-full')

@section('content')
<div class="w-full" x-data="{ 
    selectedScholarship: '{{ $selectedScholarship ? $selectedScholarship->id : 'all' }}',
    printReport() {
        window.print();
    }
}">

    <!-- Actions / Filter Bar (No-Print) -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4 print:hidden">
        <div class="flex items-center gap-4">
            <!-- Back button is handled by navbar now -->
        </div>
        
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full md:w-auto justify-end">
             <!-- Scholarship Filter -->
             <div class="relative w-full md:w-64">
                <form action="{{ route('sfao.reports.applicant-summary') }}" method="GET" id="filterForm">
                     <!-- Keep Campus Filter if exists -->
                     @if(request('campus_id'))
                        <input type="hidden" name="campus_id" value="{{ request('campus_id') }}">
                     @endif
                     
                    <select name="scholarship_id" 
                            x-model="selectedScholarship"
                            @change="updateReport($event.target.value)"
                            class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm">
                        <!-- 'All' option removed as per request -->
                        @foreach($scholarships as $scholarship)
                            <option value="{{ $scholarship->id }}">{{ $scholarship->scholarship_name }}</option>
                        @endforeach
                    </select>
                </form>
             </div>

             <button @click="printReport()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-bsu-red hover:bg-bsu-redDark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bsu-red whitespace-nowrap">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print Report
            </button>
        </div>
    </div>

    <!-- Report Paper View -->
    <div class="bg-white text-black p-8 shadow-lg print:shadow-none print:p-0 w-full mx-auto min-h-[210mm] overflow-hidden mb-8">
        
        <!-- Report Header -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center mb-4">
                 <!-- Logo -->
                 <img src="{{ asset('images/lugo.png') }}" alt="BSU Logo" class="h-24 w-24 object-contain">
            </div>
            <div class="mb-2">
                <h2 class="text-xl font-bold uppercase tracking-wider">Batangas State University</h2>
                <h3 class="text-lg font-semibold text-bsu-red uppercase">The National Engineering University</h3>
            </div>
            <div class="mt-6 mb-4">
                <h1 class="text-2xl font-bold uppercase underline decoration-2 underline-offset-4">Applicant Summary Report</h1>
            </div>
            
            <div class="text-sm space-y-1">
                <p><span class="font-semibold">Campus:</span> {{ $monitoredCampuses->count() > 1 ? 'All Campuses' : $monitoredCampuses->first()->name }}</p>
                <p><span class="font-semibold">Generated on:</span> {{ now()->format('F d, Y') }}</p>
                <p><span class="font-semibold">Prepared by:</span> {{ $user->name }}</p>
            </div>
        </div>

        <!-- Report Content -->
        <div id="report-content-container" class="space-y-8 min-h-[200px]">
            @include('sfao.reports.partials.applicant-summary-table', ['reportData' => $reportData, 'selectedScholarship' => $selectedScholarship])
        </div>

        <!-- Footer Signatures (Placeholder for print) -->
        <div class="mt-16 break-inside-avoid hidden print:flex justify-between px-8">
            <div class="text-center">
                <div class="border-b border-black w-48 mb-2"></div>
                <p class="text-xs font-bold uppercase">Prepared By</p>
                <p class="text-xs">{{ $user->name }}</p>
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
    
    <!-- Submission Form (No-Print) -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 border-t-4 border-t-bsu-red print:hidden w-full mx-auto overflow-hidden">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-6 border-b border-gray-100 pb-4">
                <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-red-50">
                    <svg class="h-5 w-5 text-bsu-red" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Submit Report to Central Office</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Ensure all details above are correct before submitting.</p>
                </div>
            </div>

            <form action="{{ route('sfao.reports.summary-submit') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="report_type" value="student_summary">
                <input type="hidden" name="campus_id" value="{{ request('campus_id', 'all') }}">
                <!-- Note: We use the blade selectedScholarship here since the form reloads the page anyway if changed via filter -->
                <input type="hidden" name="scholarship_id" :value="selectedScholarship">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                         <label for="frequency" class="block text-sm font-bold text-gray-700 mb-2">Report Frequency</label>
                        <select name="frequency" id="frequency" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-bsu-red focus:border-bsu-red block w-full p-3 shadow-sm transition-colors hover:bg-white">
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="semi-annual">Semi-Annual</option>
                            <option value="annual">Annual</option>
                        </select>
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-bold text-gray-700 mb-2">Additional Notes</label>
                        <textarea name="description" id="description" rows="1" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-bsu-red focus:border-bsu-red block w-full p-3 shadow-sm transition-colors hover:bg-white" placeholder="Optional remarks..."></textarea>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-transparent shadow-md px-8 py-2.5 bg-bsu-red text-sm font-semibold text-white hover:bg-bsu-redDark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bsu-red transition-all transform hover:-translate-y-0.5">
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        Submit Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function updateReport(scholarshipId) {
        const container = document.getElementById('report-content-container');
        
        // Add loading state opacity
        container.style.opacity = '0.5';
        
        // Prepare URL with query params
        const url = new URL("{{ route('sfao.reports.applicant-summary') }}");
        url.searchParams.set('scholarship_id', scholarshipId);
        // Preserve campus filter if present
        const urlParams = new URLSearchParams(window.location.search);
        if(urlParams.get('campus_id')) {
            url.searchParams.set('campus_id', urlParams.get('campus_id'));
        }

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
            container.style.opacity = '1';
        })
        .catch(error => {
            console.error('Error fetching report:', error);
            container.style.opacity = '1';
            alert('Failed to update report. Please try again.');
        });
    }
</script>

<style>
    @media print {
        @page {
            size: landscape;
            margin: 0.5cm;
        }
        body {
            background: white;
            color: black;
            -webkit-print-color-adjust: exact;
            width: 100%;
        }
        .print\:hidden {
            display: none !important;
        }
        .print\:flex {
            display: flex !important;
        }
        .print\:shadow-none {
            box-shadow: none !important;
        }
        .print\:p-0 {
            padding: 0 !important;
        }
        /* Allow table to scale */
        table {
            width: 100%;
        }
        /* Fit to page */
        #report-content-container {
             zoom: 85%; /* Webkit browsers */
        }
    }
</style>
@endsection
