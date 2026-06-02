@extends('layouts.focused')

@section('title', 'Student Summary Report')
@section('navbar-title', 'Student Summary Report')
@section('back-url', route('sfao.dashboard', ['tabs' => 'reports_student_summary']))
@section('back-text', 'Back to Reports')
@section('content-width', 'max-w-[95%] 2xl:max-w-full')

@section('content')
<div class="w-full" x-data="studentSummaryReport()">

    <!-- Actions / Filter Bar (No-Print) -->
    <div class="mb-8 print:hidden">
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wide">Report Filters</h3>
            <div class="flex flex-wrap items-end gap-4">
                <!-- Scholarship -->
                <div class="flex-1 min-w-[150px]">
                     <label class="block text-xs font-medium text-gray-500 mb-1 uppercase tracking-wider text-center">Scholarship</label>
                     <div class="relative">
                        <select x-model="scholarshipId" @change="updateReport()" class="block w-full px-3 py-2 text-base border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full text-center appearance-none" style="border-width: 1px;">
                            <option value="all">All Scholarships</option>
                            @foreach($scholarships as $scholarship)
                                <option value="{{ $scholarship->id }}">{{ $scholarship->scholarship_name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Student Type -->
                <div class="flex-1 min-w-[150px]">
                     <label class="block text-xs font-medium text-gray-500 mb-1 uppercase tracking-wider text-center">Student Type</label>
                     <div class="relative">
                        <select x-model="studentType" @change="updateReport()" class="block w-full px-3 py-2 text-base border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full text-center appearance-none" style="border-width: 1px;">
                            <option value="applicants">Applicants</option>
                            <option value="scholars">Scholars</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- College -->
                <div class="flex-1 min-w-[150px]">
                     <label class="block text-xs font-medium text-gray-500 mb-1 uppercase tracking-wider text-center">College</label>
                     <div class="relative">
                        <select x-model="college" @change="updateAvailablePrograms(); updateReport()" class="block w-full px-3 py-2 text-base border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full text-center appearance-none" style="border-width: 1px;">
                            <option value="all">All Colleges</option>
                            <template x-for="col in availableColleges" :key="col.short_name">
                                <option :value="col.short_name" x-text="col.short_name"></option>
                            </template>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Program -->
                <div class="flex-1 min-w-[150px]">
                     <label class="block text-xs font-medium text-gray-500 mb-1 uppercase tracking-wider text-center">Program</label>
                     <div class="relative">
                        <select x-model="program" @change="updateAvailableTracks(); updateReport()" class="block w-full px-3 py-2 text-base border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full text-center appearance-none" style="border-width: 1px;">
                            <option value="all">All Programs</option>
                            <template x-for="prog in availablePrograms" :key="prog">
                                <option :value="prog" x-text="prog"></option>
                            </template>
                        </select>
                         <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>
                
                <!-- Track -->
                <div class="flex-1 min-w-[150px]">
                     <label class="block text-xs font-medium text-gray-500 mb-1 uppercase tracking-wider text-center">Track</label>
                     <div class="relative">
                        <select x-model="track" @change="updateReport()" :disabled="availableTracks.length === 0" :class="{'opacity-50 cursor-not-allowed': availableTracks.length === 0}" class="block w-full px-3 py-2 text-base border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full text-center appearance-none" style="border-width: 1px;">
                            <option value="all">All Tracks</option>
                            <template x-for="trk in availableTracks" :key="trk">
                                <option :value="trk" x-text="trk"></option>
                            </template>
                        </select>
                         <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>
                
                 <!-- Academic Year -->
                <div class="flex-1 min-w-[150px]">
                     <label class="block text-xs font-medium text-gray-500 mb-1 uppercase tracking-wider text-center">Academic Year</label>
                     <div class="relative">
                        <select x-model="academicYear" class="block w-full px-3 py-2 text-base border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full text-center appearance-none" style="border-width: 1px;">
                            <option value="all">All Academic Years</option>
                            <template x-for="ay in availableAcademicYears" :key="ay">
                                <option :value="ay" x-text="ay"></option>
                            </template>
                            <option value="custom">Custom Date Range</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Custom Date Display -->
            <div x-show="academicYear === 'custom' && customStart && customEnd" class="mt-2 text-center">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-bsu-red">
                    Custom Range: <span x-text="customStart"></span> to <span x-text="customEnd"></span>
                    <button @click="openDateModal()" class="ml-2 text-bsu-red hover:text-red-900 focus:outline-none">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </button>
                </span>
            </div>

            <!-- Export Button (Centered) -->
            <div class="flex justify-center mt-6 border-t border-gray-100 pt-4">
                 <button @click="exportToExcel()" class="inline-flex justify-center items-center px-8 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all transform hover:-translate-y-0.5">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export to Excel
                </button>
            </div>
        </div>
    </div>

    <!-- Report Paper View -->
    <div class="bg-white text-black p-8 shadow-lg print:shadow-none print:p-0 w-full mx-auto min-h-[210mm] overflow-hidden mb-8">
        
        <!-- Report Header -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center mb-4">
                 <img src="{{ asset('images/lugo.png') }}" alt="BSU Logo" class="h-24 w-24 object-contain">
            </div>
            <div class="mb-2">
                <h2 class="text-xl font-bold uppercase tracking-wider">Batangas State University</h2>
                <h3 class="text-lg font-semibold text-bsu-red uppercase">The National Engineering University</h3>
            </div>
            
            
            <div class="text-sm space-y-1">
                <p><span class="font-semibold">Campus:</span> {{ $monitoredCampuses->count() > 1 && request('campus_id', 'all') == 'all' ? 'All Campuses' : $monitoredCampuses->first()->name }}</p>
                <p><span class="font-semibold">Generated on:</span> {{ now()->format('F d, Y') }}</p>
                <p><span class="font-semibold">Prepared by:</span> {{ $user->name }}</p>
            </div>
        </div>

        <!-- Report Content -->
        <div id="report-content-container" class="space-y-8 min-h-[200px]">
            @include('sfao.reports.partials.student-summary-table', ['reportData' => $reportData, 'studentType' => $studentType])
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
                    <p class="text-xs text-gray-500 mt-0.5">Submits the currently filtered report.</p>
                </div>
            </div>

            <form action="{{ route('sfao.reports.summary-submit') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="report_type" value="student_summary">
                <input type="hidden" name="campus_id" value="{{ request('campus_id', 'all') }}">
                
                <!-- Use alpine values to populate hidden fields before submit? -->
                <!-- We can't rely on Alpine binding to hidden input for standard form submit if outside x-data scope, but here inside. 
                     Wait, x-model on hidden input doesn't work well for standard submit unless value attr matches. 
                     Uses :value binding. 
                -->
                <input type="hidden" name="student_type" :value="studentType">
                <input type="hidden" name="college" :value="college">
                <input type="hidden" name="program" :value="program">
                <input type="hidden" name="track" :value="track">
                <input type="hidden" name="academic_year" :value="academicYear">
                <input type="hidden" name="scholarship_id" :value="scholarshipId">

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

    <!-- Custom Date Modal -->
    <!-- Custom Date Modal -->
    <div x-show="showDateModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="closeDateModal()">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full border border-gray-200" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800" id="modal-title">
                        Custom Date Range
                    </h3>
                    <button @click="closeDateModal()" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="bg-white px-4 pt-5 pb-6 sm:p-6">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Start Date</label>
                            <input type="date" x-model="tempStart" class="focus:ring-bsu-red focus:border-bsu-red block w-full sm:text-sm border-gray-300 rounded-md py-2 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">End Date</label>
                            <input type="date" x-model="tempEnd" class="focus:ring-bsu-red focus:border-bsu-red block w-full sm:text-sm border-gray-300 rounded-md py-2 shadow-sm">
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-col sm:flex-row-reverse gap-2">
                    <button type="button" @click="applyCustomDate()" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-bsu-red text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bsu-red sm:w-auto sm:text-sm">
                        Apply Filter
                    </button>
                    <button type="button" @click="closeDateModal()" class="w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

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
        table {
            width: 100%;
        }
        #report-content-container {
             zoom: 85%;
        }
    }
</style>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('studentSummaryReport', () => ({
            studentType: @json($studentType),
            college: @json(request('college', 'all')),
            program: @json(request('program', 'all')),
            track: @json(request('track', 'all')),
            academicYear: @json(request('academic_year', 'all')),
            scholarshipId: @json(request('scholarship_id', 'all')),
            campusId: @json(request('campus_id', 'all')),
            
            // Custom Date
            customStart: @json(request('custom_start')),
            customEnd: @json(request('custom_end')),
            showDateModal: false,
            tempStart: '',
            tempEnd: '',
            
            campusCollegePrograms: @json($campusCollegePrograms),
            programTracks: @json($programTracks),
            routeUrl: @json(route('sfao.reports.student-summary')),

            availablePrograms: [],
            availableTracks: [],
            availableColleges: [],
            allColleges: @json($colleges),
            
            campusAcademicYearMap: @json($campusAcademicYearMap),
            availableAcademicYears: [],

            init() {
                this.updateAvailableColleges(false);
                this.updateAvailablePrograms(false);
                this.updateAvailableTracks(false);
                this.updateAvailableAcademicYears();

                // Watchers
                this.$watch('campusId', () => {
                    this.updateAvailableColleges(); // Update colleges first
                    this.updateAvailablePrograms();
                    this.updateAvailableAcademicYears();
                    this.updateReport();
                });
                
                this.$watch('studentType', () => {
                    this.updateAvailableAcademicYears();
                    this.updateReport();
                });
                
                this.$watch('college', () => {
                    this.updateAvailablePrograms();
                    this.updateReport();
                });

                this.$watch('program', () => {
                   this.updateAvailableTracks();
                   this.updateReport();
                });
                
                this.$watch('track', () => this.updateReport());
                this.$watch('scholarshipId', () => this.updateReport());

                // Watch for Academic Year Change to Custom
                this.$watch('academicYear', value => {
                    if (value === 'custom') {
                        // If no dates set yet, open modal
                        if (!this.customStart || !this.customEnd) {
                             this.openDateModal();
                        } else {
                            this.updateReport();
                        }
                    } else {
                        this.updateReport();
                    }
                });
            },

            updateAvailableColleges(reset = true) {
                if (reset) { this.college = 'all'; }
                
                let collegeShortNames = new Set();
                
                // If specific campus, get its colleges from map
                if (this.campusId !== 'all') {
                     const collegesInCampus = this.campusCollegePrograms[this.campusId] || {};
                     Object.keys(collegesInCampus).forEach(cName => collegeShortNames.add(cName));
                } else {
                     // If all campuses, flatten all colleges from the map
                     Object.values(this.campusCollegePrograms).forEach(campusData => {
                         Object.keys(campusData).forEach(cName => collegeShortNames.add(cName));
                     });
                }
                
                // Now match these short names to the allColleges objects to preserve info if needed, 
                // or just use the short names directly. Current loop uses full object.
                // We'll filter this.allColleges based on the Set.
                
                this.availableColleges = this.allColleges.filter(c => collegeShortNames.has(c.short_name));
                // Sort by name?
                this.availableColleges.sort((a,b) => a.short_name.localeCompare(b.short_name));
            },

            updateAvailableAcademicYears() {
                // Determine which years to show based on campusId and studentType
                const typeData = this.campusAcademicYearMap[this.studentType] || {};
                let years = [];
                
                if (this.campusId === 'all') {
                    years = typeData['all'] || [];
                } else if (typeData[this.campusId]) {
                    years = typeData[this.campusId];
                }
                
                this.availableAcademicYears = years;
                
                // If current academicYear is not in list (and not 'all' or 'custom'), reset to 'all'
                if (this.academicYear !== 'all' && this.academicYear !== 'custom' && !years.includes(this.academicYear)) {
                    this.academicYear = 'all';
                }
            },

            openDateModal() {
                this.tempStart = this.customStart || new Date().toISOString().split('T')[0];
                this.tempEnd = this.customEnd || new Date().toISOString().split('T')[0];
                this.showDateModal = true;
            },

            closeDateModal() {
                this.showDateModal = false;
                // If cancelled and no date set, revert to 'all' or previous? 
                if (!this.customStart) {
                    this.academicYear = 'all'; 
                }
            },

            applyCustomDate() {
                if (this.tempStart && this.tempEnd) {
                    this.customStart = this.tempStart;
                    this.customEnd = this.tempEnd;
                    this.showDateModal = false;
                    this.updateReport();
                } else {
                    alert('Please select both start and end dates.');
                }
            },

            updateAvailablePrograms(reset = true) {
                if (reset) { this.program = 'all'; this.track = 'all'; }
                
                let programs = [];
                
                // Helper to collect programs
                const collectPrograms = (cId) => {
                     const campusData = this.campusCollegePrograms[cId] || {};
                     // Use the 'college' property which holds the selected short_name
                     // If 'all', collect all programs from this campus
                     if (this.college === 'all') {
                         Object.values(campusData).forEach(progs => programs.push(...progs));
                     } else if (campusData[this.college]) {
                         // Only collect from selected college
                         programs.push(...campusData[this.college]);
                     }
                };
        
                if (this.campusId === 'all') {
                    Object.keys(this.campusCollegePrograms).forEach(cId => collectPrograms(cId));
                } else {
                    collectPrograms(this.campusId);
                }
                
                this.availablePrograms = [...new Set(programs)].sort();
            },
        
            updateAvailableTracks(reset = true) {
                if (reset) { this.track = 'all'; }
                
                if (this.program === 'all') {
                     this.availableTracks = [];
                } else {
                     this.availableTracks = this.programTracks[this.program] || [];
                }
            },
        
            updateReport() {
                const container = document.getElementById('report-content-container');
                if (container) container.style.opacity = '0.5';
                
                const url = new URL(this.routeUrl);
                url.searchParams.set('student_type', this.studentType);
                url.searchParams.set('college', this.college);
                url.searchParams.set('program', this.program);
                url.searchParams.set('track', this.track);
                url.searchParams.set('academic_year', this.academicYear);
                url.searchParams.set('scholarship_id', this.scholarshipId);
                url.searchParams.set('campus_id', this.campusId);
                
                if (this.academicYear === 'custom') {
                    url.searchParams.set('custom_start', this.customStart);
                    url.searchParams.set('custom_end', this.customEnd);
                }

                url.searchParams.set('_t', new Date().getTime());
        
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => {
                     if (!res.ok) throw new Error('Network response not ok');
                     return res.text();
                })
                .then(html => {
                    if (container) container.innerHTML = html;
                    // Also update title if returned in response? 
                    // The whole page isn't reloading, just the container. 
                    // The title is outside the container.
                    // We might need to return the title in the HTML or a JSON response. 
                    // For now, let's just live with title not updating on AJAX unless we wrap it.
                    
                    // Actually, let's reload the page for 'Custom' to be safe? No, AJAX is better.
                    // To update title, we can parse it from response or move title INSIDE the container.
                    // Let's move the title inside report-content-container in the partial? 
                    // Or parsing it. 
                    
                    // QUICK FIX: Reload page to update Title properly if we want to be lazy, 
                    // OR move the header into the partial.
                    // Moving header to partial is best.
                })
                .catch(err => {
                    console.error(err);
                    if (container) container.innerHTML = '<p class=\'text-center text-red-500 py-4\'>Error loading report.</p>';
                })
                .finally(() => {
                    if (container) container.style.opacity = '1';
                });
            },
            
            printReport() {
                window.print();
            },
            exportToExcel() {
                const params = new URLSearchParams({
                    student_type: this.studentType,
                    college: this.college,
                    program: this.program,
                    track: this.track,
                    academic_year: this.academicYear,
                    scholarship_id: this.scholarshipId,
                    campus_id: this.campusId,
                    export: 'excel'
                });
                
                if (this.academicYear === 'custom') {
                    params.set('custom_start', this.customStart);
                    params.set('custom_end', this.customEnd);
                }
                
                window.location.href = `${this.routeUrl}?${params.toString()}`;
            }
        }));
    });
</script>
@endsection
