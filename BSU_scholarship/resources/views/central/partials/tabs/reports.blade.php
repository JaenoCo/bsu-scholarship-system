<div x-show="tab === 'sfao-reports'" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-cloak
     x-data="reportsTab()"
     x-init="init()">
    
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('reportsTab', () => ({
            activeReportTab: localStorage.getItem('sfao_report_status') || 'submitted',
            // Use Analytics Hierarchy for Name-based filtering (matches Report Titles)
            hierarchy: @json($analytics['campus_college_programs'] ?? []),
            programTracks: @json($analytics['program_tracks'] ?? []),
            allReports: @json($allReportsForReportsTab ?? []),
            
            // Filter States
            filters: {
                campus: '{{ request('campus', 'all') }}',
                scholarship: '{{ request('scholarship_filter', 'all') }}',
                college: '{{ request('college_filter', 'all') }}',
                program: '{{ request('program_filter', 'all') }}',
                track: '{{ request('track_filter', 'all') }}',
                academicYear: '{{ request('academic_year', 'all') }}'
            },

            // Dropdown Options
            availableColleges: [],
            availablePrograms: [],
            availableTracks: [],
            
            // Pagination (Client Side)
            currentPage: 1,
            itemsPerPage: 10,

            get filteredReports() {
                return this.allReports.filter(report => {
                    // 1. Status Filter
                    if (report.status !== this.activeReportTab) return false;

                    // 2. Campus Filter (ID based)
                    if (this.filters.campus !== 'all' && String(report.campus_id) !== String(this.filters.campus)) return false;

                    // 3. Academic Year Filter
                    if (this.filters.academicYear !== 'all' && report.academic_year !== this.filters.academicYear) return false;

                    // 4. College Filter (Match Short Name/Name in Title)
                    if (this.filters.college !== 'all') {
                        if (report.college_name) {
                            if (report.college_name !== this.filters.college) return false;
                        } else {
                            if (!this.reportTitleMatches(report.title, this.filters.college)) return false;
                        }
                    }

                    // 5. Program Filter (Match Name in Title)
                    if (this.filters.program !== 'all') {
                         if (report.program_name) {
                            if (report.program_name !== this.filters.program) return false;
                        } else {
                            if (!this.reportTitleMatches(report.title, this.filters.program)) return false;
                        }
                    }

                    // 6. Track Filter (Match Name in Title)
                    if (this.filters.track !== 'all') {
                         if (report.track_name) {
                            if (report.track_name !== this.filters.track) return false;
                        } else {
                            if (!this.reportTitleMatches(report.title, this.filters.track)) return false;
                        }
                    }

                    // 7. Scholarship Filter
                    if (this.filters.scholarship !== 'all') {
                        // Optional: match scholarship if needed
                    }
                    
                    return true;
                });
            },

            // Helper to match text loosely (case-insensitive)
            reportTitleMatches(title, keyword) {
                if (!title || !keyword) return true;
                return title.toLowerCase().includes(keyword.toLowerCase());
            },

            get paginatedReports() {
                const start = (this.currentPage - 1) * this.itemsPerPage;
                const end = start + this.itemsPerPage;
                return this.filteredReports.slice(start, end);
            },

            get totalPages() {
                return Math.ceil(this.filteredReports.length / this.itemsPerPage) || 1;
            },
            
            get reportStats() {
                 return {
                     submitted: this.allReports.filter(r => r.status === 'submitted').length,
                     reviewed: this.allReports.filter(r => r.status === 'reviewed').length,
                     approved: this.allReports.filter(r => r.status === 'approved').length,
                     rejected: this.allReports.filter(r => r.status === 'rejected').length
                 };
            },

            init() {
                this.$watch('activeReportTab', value => {
                    localStorage.setItem('sfao_report_status', value);
                    this.currentPage = 1;
                });

                // Initialize cascading options
                this.updateAvailableColleges(false);
                // Note: We don't auto-update downstream on init unless filters are set

                // Watchers for cascading logic
                this.$watch('filters.campus', () => {
                    this.updateAvailableColleges();
                    this.currentPage = 1;
                });

                this.$watch('filters.college', () => {
                    this.updateAvailablePrograms();
                    this.currentPage = 1;
                });
                
                this.$watch('filters.program', () => {
                     this.updateAvailableTracks();
                     this.currentPage = 1;
                });

                this.$watch('filters.track', () => { this.currentPage = 1; });
                this.$watch('filters.academicYear', () => { this.currentPage = 1; });
                this.$watch('filters.scholarship', () => { this.currentPage = 1; });
            },

            setReportStatus(status) {
                this.activeReportTab = status;
            },
            
            prevPage() {
                if (this.currentPage > 1) this.currentPage--;
            },
            
            nextPage() {
                if (this.currentPage < this.totalPages) this.currentPage++;
            },

            updateAvailableColleges(reset = true) {
                if (reset) {
                    this.filters.college = 'all';
                    this.filters.program = 'all';
                    this.filters.track = 'all';
                }
                
                this.availableColleges = [];
                
                if (this.filters.campus === 'all') {
                    // Aggregate ALL colleges from ALL campuses
                    const allCampuses = Object.values(this.hierarchy);
                    const collegesSet = new Set();
                    allCampuses.forEach(campusObj => {
                        Object.keys(campusObj).forEach(college => collegesSet.add(college));
                    });
                    this.availableColleges = Array.from(collegesSet).sort();
                } else {
                    // Specific Campus
                    // hierarchy is { campus_id: { college_name: [programs] } }
                    const collegesObj = this.hierarchy[this.filters.campus] || {};
                    this.availableColleges = Object.keys(collegesObj).sort();
                }
                
                // Validate selection
                if (this.filters.college !== 'all' && !this.availableColleges.includes(this.filters.college)) {
                    this.filters.college = 'all';
                    this.updateAvailablePrograms();
                } else if (this.filters.college !== 'all') {
                    this.updateAvailablePrograms(false);
                } else {
                    // Even if reset to all, update programs to show ALL programs if needed
                    this.updateAvailablePrograms(false);
                }
            },

            updateAvailablePrograms(reset = true) {
                if (reset) {
                    this.filters.program = 'all';
                    this.filters.track = 'all';
                }
                
                this.availablePrograms = [];
                
                // Calculate available programs logic
                if (this.filters.college === 'all') {
                    // Does user want ALL programs from ALL colleges?
                    // Usually we might want to restrict to selected college, but if college is 'all',
                    // we show nothing or everything? Let's show everything if campus is all, or everything in campus.
                    // However, UI typically waits for College selection. 
                    // BUT user request implies flexibly selecting.
                    // Let's allow selecting Program without College if College is ALL?
                    // Typically 'Programs' belong to a 'College'. It's hard to list 100 programs flat.
                    // COMPROMISE: If college is 'all', show ALL programs from valid scope (All Campus or Specific Campus).
                    
                    let scopeCampuses = [];
                    if (this.filters.campus === 'all') {
                        scopeCampuses = Object.values(this.hierarchy);
                    } else {
                        scopeCampuses = [this.hierarchy[this.filters.campus] || {}];
                    }
                    
                    const programsSet = new Set();
                    scopeCampuses.forEach(campusObj => {
                        Object.values(campusObj).forEach(programList => {
                             programList.forEach(p => programsSet.add(p));
                        });
                    });
                    this.availablePrograms = Array.from(programsSet).sort();

                } else {
                    // College is selected
                     const programsSet = new Set();
                     
                     if (this.filters.campus === 'all') {
                         // Find this college in ALL campuses
                         Object.values(this.hierarchy).forEach(campusObj => {
                             if (campusObj[this.filters.college]) {
                                 campusObj[this.filters.college].forEach(p => programsSet.add(p));
                             }
                         });
                     } else {
                         // Specific campus
                         const collegesObj = this.hierarchy[this.filters.campus] || {};
                         const programs = collegesObj[this.filters.college] || [];
                         programs.forEach(p => programsSet.add(p));
                     }
                     this.availablePrograms = Array.from(programsSet).sort();
                }
                
                // Validate selection
                if (this.filters.program !== 'all' && !this.availablePrograms.includes(this.filters.program)) {
                    this.filters.program = 'all';
                    this.updateAvailableTracks();
                } else if (this.filters.program !== 'all') {
                     this.updateAvailableTracks(false);
                } else {
                    this.updateAvailableTracks(false);
                }
            },

            updateAvailableTracks(reset = true) {
                if (reset) {
                    this.filters.track = 'all';
                }
                
                this.availableTracks = [];
                
                if (this.filters.program === 'all') {
                    // Similar logic: show all tracks if program is all?
                    // Tracks depend on programs.
                    // If Program is 'all', showing all tracks might be too much, but let's be consistent.
                    // Simply aggregate ALL tracks in this.programTracks
                    this.availableTracks = Object.values(this.programTracks).flat();
                    // Deduplicate
                    this.availableTracks = [...new Set(this.availableTracks)].sort();
                } else {
                    const tracks = this.programTracks[this.filters.program] || [];
                    this.availableTracks = tracks.sort();
                }
                
                 if (this.filters.track !== 'all' && !this.availableTracks.includes(this.filters.track)) {
                    this.filters.track = 'all';
                }
            },
            
            getBadgeClass(status) {
                switch(status) {
                    case 'submitted': return 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200';
                    case 'reviewed': return 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200';
                    case 'approved': return 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200';
                    case 'rejected': return 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200';
                    default: return 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300';
                }
            },
            
            getReviewActionText(status) {
                switch(status) {
                    case 'submitted': return 'Review';
                    case 'reviewed': return 'View';
                    case 'approved': return 'View';
                    case 'rejected': return 'View';
                    default: return 'View';
                }
            },
            
            getReportDateLabel(status) {
                 switch(status) {
                    case 'submitted': return 'Submitted';
                    case 'reviewed': return 'Reviewed';
                    case 'approved': return 'Approved';
                    case 'rejected': return 'Rejected';
                    default: return 'Date';
                }
            },
            
            getReportDateValue(report) {
                 if (['reviewed', 'approved', 'rejected'].includes(this.activeReportTab)) {
                     return report.display_reviewed_at;
                 }
                 return report.display_submitted_at;
            }
        }));
    });
    </script>
    
    <div class="space-y-6">
        
        <!-- Filters (SFAO Style) -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
            <div class="flex flex-wrap gap-4 items-end mb-4">
                <!-- 1. Campus Filter -->
                <div class="flex-1 min-w-[200px]">
                    <label for="campus" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Campus</label>
                    <div class="relative">
                        <select id="campus" x-model="filters.campus" 
                                class="block w-full px-3 py-2 text-base border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none cursor-pointer"
                                style="border-width: 1px;">
                            <option value="all">All Campuses</option>
                            @foreach($campuses as $campus)
                                <option value="{{ $campus->id }}">
                                    {{ $campus->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- 2. Scholarship Filter -->
                <div class="flex-1 min-w-[200px]">
                    <label for="scholarship" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Scholarship</label>
                    <div class="relative">
                        <select id="scholarship" x-model="filters.scholarship" 
                                class="block w-full px-3 py-2 text-base border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none cursor-pointer"
                                style="border-width: 1px;">
                            <option value="all">All Scholarships</option>
                            @if(isset($scholarshipOptions))
                                @foreach($scholarshipOptions as $option)
                                    <option value="{{ $option['id'] }}">
                                        {{ $option['name'] }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- 3. College Filter -->
                <div class="flex-1 min-w-[200px]">
                    <label for="college" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">College</label>
                    <div class="relative">
                        <select id="college" x-model="filters.college" 
                                class="block w-full px-3 py-2 text-base border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none cursor-pointer"
                                style="border-width: 1px;"
                                :disabled="!availableColleges.length">
                            <option value="all">All Colleges</option>
                            <template x-for="college in availableColleges" :key="college">
                                <option :value="college" x-text="college"></option>
                            </template>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- 4. Program Filter -->
                <div class="flex-1 min-w-[200px]">
                    <label for="program" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Program</label>
                    <div class="relative">
                        <select id="program" x-model="filters.program" 
                                class="block w-full px-3 py-2 text-base border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none cursor-pointer"
                                style="border-width: 1px;"
                                :disabled="!availablePrograms.length">
                            <option value="all">All Programs</option>
                            <template x-for="program in availablePrograms" :key="program">
                                <option :value="program" x-text="program"></option>
                            </template>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- 5. Track Filter -->
                <div class="flex-1 min-w-[200px]">
                    <label for="track" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Track</label>
                    <div class="relative">
                        <select id="track" x-model="filters.track" 
                                class="block w-full px-3 py-2 text-base border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none cursor-pointer"
                                style="border-width: 1px;"
                                :disabled="!availableTracks.length">
                            <option value="all">All Tracks</option>
                            <template x-for="track in availableTracks" :key="track">
                                <option :value="track" x-text="track"></option>
                            </template>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- 6. Academic Year Filter -->
                <div class="flex-1 min-w-[200px]">
                    <label for="academic_year" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Academic Year</label>
                    <div class="relative">
                        <select id="academic_year" x-model="filters.academicYear" 
                                class="block w-full px-3 py-2 text-base border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none cursor-pointer"
                                style="border-width: 1px;">
                            <option value="all">All Years</option>
                            @isset($academicYearOptions)
                                @foreach($academicYearOptions as $option)
                                    <option value="{{ $option }}">
                                        {{ $option }}
                                    </option>
                                @endforeach
                            @endisset
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Style Filters (Legend/Status Toggles) -->
            <div class="flex flex-wrap justify-between gap-4 w-full">
                <!-- Submitted (Yellow) -->
                <button @click.prevent="setReportStatus('submitted')"
                        :class="activeReportTab === 'submitted' ? 'bg-yellow-500 text-white ring-2 ring-yellow-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 hover:bg-gray-200'"
                        class="flex-1 px-4 py-2 rounded-full text-sm font-medium transition-colors duration-200 focus:outline-none flex items-center justify-center shadow-sm">
                        <span class="w-2 h-2 rounded-full mr-2 bg-white" x-show="activeReportTab === 'submitted'"></span>
                        Submitted <span class="ml-1 text-xs opacity-75" x-text="'(' + reportStats.submitted + ')'"></span>
                </button>

                <!-- Reviewed (Blue) -->
                <button @click.prevent="setReportStatus('reviewed')"
                        :class="activeReportTab === 'reviewed' ? 'bg-blue-500 text-white ring-2 ring-blue-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 hover:bg-gray-200'"
                        class="flex-1 px-4 py-2 rounded-full text-sm font-medium transition-colors duration-200 focus:outline-none flex items-center justify-center shadow-sm">
                        <span class="w-2 h-2 rounded-full mr-2 bg-white" x-show="activeReportTab === 'reviewed'"></span>
                        Reviewed <span class="ml-1 text-xs opacity-75" x-text="'(' + reportStats.reviewed + ')'"></span>
                </button>

                <!-- Approved (Green) -->
                <button @click.prevent="setReportStatus('approved')"
                        :class="activeReportTab === 'approved' ? 'bg-green-500 text-white ring-2 ring-green-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 hover:bg-gray-200'"
                        class="flex-1 px-4 py-2 rounded-full text-sm font-medium transition-colors duration-200 focus:outline-none flex items-center justify-center shadow-sm">
                        <span class="w-2 h-2 rounded-full mr-2 bg-white" x-show="activeReportTab === 'approved'"></span>
                        Accepted <span class="ml-1 text-xs opacity-75" x-text="'(' + reportStats.approved + ')'"></span>
                </button>

                <!-- Rejected (Red) -->
                <button @click.prevent="setReportStatus('rejected')"
                        :class="activeReportTab === 'rejected' ? 'bg-red-500 text-white ring-2 ring-red-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 hover:bg-gray-200'"
                        class="flex-1 px-4 py-2 rounded-full text-sm font-medium transition-colors duration-200 focus:outline-none flex items-center justify-center shadow-sm">
                        <span class="w-2 h-2 rounded-full mr-2 bg-white" x-show="activeReportTab === 'rejected'"></span>
                        Rejected <span class="ml-1 text-xs opacity-75" x-text="'(' + reportStats.rejected + ')'"></span>
                </button>
            </div>
            
        </div>

        <!-- Reports List (Alpine Client-Side) -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                <template x-for="report in paginatedReports" :key="report.id">
                    <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center">
                                    <a :href="'{{ url('central/reports') }}/' + report.id" class="text-lg font-medium text-gray-900 dark:text-white hover:text-bsu-red" x-text="report.title"></a>
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          :class="getBadgeClass(report.status)"
                                          x-text="report.status.charAt(0).toUpperCase() + report.status.slice(1)">
                                    </span>
                                </div>
                                <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400 space-x-4">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span x-text="report.campus_name"></span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span x-text="report.report_type_display"></span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span x-text="getReportDateLabel(activeReportTab) + ' ' + getReportDateValue(report)"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <a :href="'{{ url('central/reports') }}/' + report.id"
                                   class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bsu-red"
                                   x-text="getReviewActionText(activeReportTab)">
                                </a>
                            </div>
                        </div>
                    </div>
                </template>
                
                <div x-show="filteredReports.length === 0" class="px-6 py-8 text-center bg-white dark:bg-gray-800">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No reports found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your filters.</p>
                </div>
            </div>
            
            <!-- Pagination Controls -->
            <div x-show="totalPages > 1" class="bg-white dark:bg-gray-800 px-6 py-4 border-t border-gray-200 dark:border-gray-600 flex justify-center items-center gap-4">
                <button @click="prevPage()" :disabled="currentPage === 1" 
                        class="px-3 py-1 rounded border dark:border-gray-600 disabled:opacity-50">
                    Previous
                </button>
                <span class="text-sm text-gray-700 dark:text-gray-300">
                    Page <span x-text="currentPage"></span> of <span x-text="totalPages"></span>
                </span>
                <button @click="nextPage()" :disabled="currentPage === totalPages"
                        class="px-3 py-1 rounded border dark:border-gray-600 disabled:opacity-50">
                    Next
                </button>
            </div>
        </div>

    </div>
</div>
