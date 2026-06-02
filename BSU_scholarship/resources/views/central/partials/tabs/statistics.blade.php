<div x-show="tab === 'all_statistics' || tab.endsWith('_statistics')" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-cloak 
     x-data='statisticsTab({ analytics: @json($analytics ?? []), campusOptions: @json($campusOptions ?? []) })'
     @change-stats-campus.window="filters.campus = $event.detail; applyFilters()">
    <div class="space-y-6">

        <!-- Filter Controls -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
            <div class="flex flex-wrap gap-4 items-end">
                
                <!-- Student Type Filter (Global) -->
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Student Type</label>
                    <div class="relative">
                        <select x-model="viewMode" @change="updateCharts()" 
                                class="block w-full px-3 py-2 text-base border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none"
                                style="border-width: 1px;">
                            <option value="applicants">Applicants</option>
                            <option value="scholars">Scholars</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- College Filter (Global) -->
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">College</label>
                    <div class="relative">
                        <select x-model="localFilters.college" 
                                class="block w-full px-3 py-2 text-base border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none"
                                style="border-width: 1px;">
                            <option value="all">All</option>
                            <template x-for="college in availableColleges" :key="college.id">
                                <option :value="college.short_name" x-text="college.short_name"></option>
                            </template>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Program Filter (Global) -->
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Program</label>
                    <div class="relative">
                    <select x-model="localFilters.program" 
                                :key="localFilters.college"
                                class="block w-full px-3 py-2 text-base border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none"
                                style="border-width: 1px;">
                            <option value="all">All</option>
                            <template x-for="prog in availablePrograms" :key="prog">
                                <option :value="prog" x-text="prog"></option>
                            </template>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Track Filter (Global) -->
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Track / Major</label>
                    <div class="relative">
                    <select x-model="localFilters.track" 
                                :disabled="!availableTracks || availableTracks.length === 0"
                                :class="{'opacity-50 cursor-not-allowed': !availableTracks || availableTracks.length === 0}"
                                class="block w-full px-3 py-2 text-base border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none"
                                style="border-width: 1px;">
                            <option value="all" x-text="(!availableTracks || availableTracks.length === 0) ? 'No Tracks Available' : 'All'"></option>
                            <template x-for="track in availableTracks" :key="track">
                                <option :value="track" x-text="track"></option>
                            </template>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Time Period Filter -->
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Academic Year</label>
                    <div class="relative">
                        <select x-model="filters.timePeriod" 
                                class="block w-full px-3 py-2 text-base border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none"
                                style="border-width: 1px;">
                            <option value="all">All Time</option>
                            <template x-for="ay in academicYearOptions" :key="ay">
                                <option :value="ay" x-text="ay"></option>
                            </template>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

            </div>
            
            <!-- Global Legend Buttons (Row 2) -->
            <div class="mt-4 flex flex-wrap justify-between gap-4 w-full">
                 <!-- Applicants Mode Legend -->
                <template x-if="viewMode === 'applicants'">
                    <div class="flex flex-wrap justify-between w-full gap-2">
                        <!-- Approved -->
                         <button @click="chartLegend.approved = !chartLegend.approved"
                                :class="chartLegend.approved ? 'bg-green-500 text-white ring-2 ring-green-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 hover:bg-gray-200'"
                                class="flex-1 px-4 py-2 rounded-full text-sm font-medium transition-colors duration-200 focus:outline-none flex items-center justify-center shadow-sm">
                                <span class="w-2 h-2 rounded-full mr-2 bg-white" x-show="chartLegend.approved"></span>
                                Approved
                        </button>
                        <!-- Rejected -->
                        <button @click="chartLegend.rejected = !chartLegend.rejected"
                                :class="chartLegend.rejected ? 'bg-red-500 text-white ring-2 ring-red-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 hover:bg-gray-200'"
                                class="flex-1 px-4 py-2 rounded-full text-sm font-medium transition-colors duration-200 focus:outline-none flex items-center justify-center shadow-sm">
                                <span class="w-2 h-2 rounded-full mr-2 bg-white" x-show="chartLegend.rejected"></span>
                                Rejected
                        </button>
                        <!-- Pending -->
                         <button @click="chartLegend.pending = !chartLegend.pending"
                                :class="chartLegend.pending ? 'bg-yellow-500 text-white ring-2 ring-yellow-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 hover:bg-gray-200'"
                                class="flex-1 px-4 py-2 rounded-full text-sm font-medium transition-colors duration-200 focus:outline-none flex items-center justify-center shadow-sm">
                                <span class="w-2 h-2 rounded-full mr-2 bg-white" x-show="chartLegend.pending"></span>
                                Pending
                        </button>
                        <!-- In Progress -->
                        <button @click="chartLegend.inProgress = !chartLegend.inProgress"
                                :class="chartLegend.inProgress ? 'bg-blue-500 text-white ring-2 ring-blue-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 hover:bg-gray-200'"
                                class="flex-1 px-4 py-2 rounded-full text-sm font-medium transition-colors duration-200 focus:outline-none flex items-center justify-center shadow-sm">
                                <span class="w-2 h-2 rounded-full mr-2 bg-white" x-show="chartLegend.inProgress"></span>
                                In Progress
                        </button>
                    </div>
                </template>

                 <!-- Scholars Mode Legend -->
                 <template x-if="viewMode === 'scholars'">
                    <div class="flex flex-wrap justify-between w-full gap-2">
                        <!-- Old Scholars -->
                        <button @click="chartLegend.oldScholars = !chartLegend.oldScholars"
                                :class="chartLegend.oldScholars ? 'bg-green-500 text-white ring-2 ring-green-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 hover:bg-gray-200'"
                                class="flex-1 px-4 py-2 rounded-full text-sm font-medium transition-colors duration-200 focus:outline-none flex items-center justify-center shadow-sm">
                                <span class="w-2 h-2 rounded-full mr-2 bg-white" x-show="chartLegend.oldScholars"></span>
                                Old Scholars
                        </button>
                        <!-- New Scholars -->
                        <button @click="chartLegend.newScholars = !chartLegend.newScholars"
                                :class="chartLegend.newScholars ? 'bg-blue-500 text-white ring-2 ring-blue-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 hover:bg-gray-200'"
                                class="flex-1 px-4 py-2 rounded-full text-sm font-medium transition-colors duration-200 focus:outline-none flex items-center justify-center shadow-sm">
                                <span class="w-2 h-2 rounded-full mr-2 bg-white" x-show="chartLegend.newScholars"></span>
                                New Scholars
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Analytics Charts Section -->
        
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mt-6">
            <!-- Header Section (Centered) -->
            <div class="text-center mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white" x-text="getChartTitle()">Scholarship Status</h3>
            </div>


            <!-- Filters Section within Card -->
            <div class="flex flex-wrap items-end gap-4 mb-6 justify-center">
                 <!-- Scholarship Filter (Local) -->
                 <div class="flex-1 min-w-[300px] max-w-lg">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Scholarship</label>
                    <div class="relative">
                        <select x-model="filters.scholarship" 
                                class="block w-full px-3 py-2 text-base border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none"
                                style="border-width: 1px;">
                            <option value="all">All Scholarships</option>
                            <template x-for="scholarship in analyticsData.available_scholarships" :key="scholarship.id">
                                <option :value="String(scholarship.id)" x-text="scholarship.scholarship_name"></option>
                            </template>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Summary Counts -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                 <!-- Total -->
                 <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 text-center border border-gray-100 dark:border-gray-600">
                     <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Total</p>
                     <p class="text-xl font-bold text-gray-900 dark:text-white" x-text="filteredData.counts?.total || 0"></p>
                 </div>
                 <!-- Approved -->
                 <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 text-center border border-green-100 dark:border-green-800">
                     <p class="text-xs font-semibold text-green-600 dark:text-green-400 uppercase">Approved</p>
                     <p class="text-xl font-bold text-green-700 dark:text-green-300" x-text="filteredData.counts?.approved || 0"></p>
                 </div>
                 <!-- Rejected -->
                 <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3 text-center border border-red-100 dark:border-red-800">
                     <p class="text-xs font-semibold text-red-600 dark:text-red-400 uppercase">Rejected</p>
                     <p class="text-xl font-bold text-red-700 dark:text-red-300" x-text="filteredData.counts?.rejected || 0"></p>
                 </div>
                 <!-- Active / Pending -->
                 <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-3 text-center border border-yellow-100 dark:border-yellow-800">
                     <p class="text-xs font-semibold text-yellow-600 dark:text-yellow-400 uppercase">Pending/In Progress</p>
                     <p class="text-xl font-bold text-yellow-700 dark:text-yellow-300" x-text="filteredData.counts?.active || 0"></p>
                 </div>
                 <!-- Rate -->
                 <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 text-center border border-blue-100 dark:border-blue-800">
                     <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase">Approval Rate</p>
                     <p class="text-xl font-bold text-blue-700 dark:text-blue-300" x-text="(filteredData.counts?.approvalRate || '0.0') + '%'"></p>
                 </div>
            </div>


            <!-- Chart Container -->
            <div class="relative h-96 w-full mb-6">
                <div x-show="chartStatus.college" class="h-full w-full">
                    <canvas id="sfaoCollegeChart"></canvas>
                </div>
                <!-- No Data Message -->
                <div x-show="!chartStatus.college" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="text-center p-6 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white" 
                            x-text="viewMode === 'applicants' ? 'No Applicants Found' : 'No Scholars Found'">
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your filters.</p>
                    </div>
                </div>
            </div>

            <!-- Trend Graph (Integrated) -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h4 class="text-md font-bold text-gray-900 dark:text-white mb-4 text-center">Trend Analysis</h4>
                <div class="relative h-64 w-full">
                    <div x-show="chartStatus.trend" class="h-full w-full">
                        <canvas id="sfaoTrendChart"></canvas>
                    </div>
                    <div x-show="!chartStatus.trend" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <p class="text-sm text-gray-500 dark:text-gray-400">No trend data available for this selection.</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Comparison Charts Layout -->
        <div class="flex flex-col lg:flex-row gap-6 mt-6 mb-6">
            
            <!-- Scholarship Comparison Graph (Flex Grow - Maximized Width) -->
            <div class="flex-1 min-w-0 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 text-center">Scholarship Comparison (All Programs)</h3>
                <!-- Scroll Container -->
                <div class="overflow-x-auto w-full">
                    <div class="relative" :style="'height: 600px; min-width: ' + Math.max(100, (chartStatus.comparisonCount || 1) * 15) + '%'">
                            <div x-show="chartStatus.comparison" class="h-full w-full">
                            <canvas id="sfaoComparisonChart"></canvas>
                        </div>
                            <!-- No Data Message -->
                            <div x-show="!chartStatus.comparison" class="absolute inset-0 flex items-center justify-center pointer-events-none" style="left: 0; right: 0;">
                            <div class="text-center p-6 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No Comparison Data</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your filters.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Student Comparison Graph (Fixed Width: 15rem/240px) -->
            <div class="lg:w-60 flex-none bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 text-center">Student Ratio</h3>
                <div class="relative w-full flex items-center justify-center" style="height: 600px;">
                     <div class="h-full w-full">
                        <canvas id="sfaoStudentComparisonChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Analytics Data and Configuration passed from Controller -->
    <!-- The Alpine component 'statisticsTab' matches 'sfaoStatisticsTab' logic but is defined inline here to avoid loading issues -->
    
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('statisticsTab', (config = {}) => {
            // Private Chart Instances (Non-Reactive)
            const chartInstances = {
                college: null,
                gender: null,
                scholarshipType: null,
                comparison: null,
                studentComparison: null,
                trend: null
            };

            return {
                viewMode: 'applicants',
                analyticsData: config.analytics || {},
                campusOptions: config.campusOptions || [],
                academicYearOptions: [], // Dynamic list
                filteredData: {
                    college_stats: [],
                    scholarshipStats: {}
                },
                availableColleges: [],
                availableTracks: [],
                localFilters: {
                    college: 'all',
                    program: 'all',
                    track: 'all'
                },
                chartLegend: {
                    approved: true,
                    newScholars: true,
                    oldScholars: true,
                    rejected: true,
                    pending: true,
                    inProgress: true,
                    nonScholars: true
                },
                chartStatus: { // Track if charts have data
                    college: true,
                    comparison: true,
                    comparisonCount: 0,
                    trend: true
                },
                availablePrograms: [],
                filters: {
                    campus: 'all',
                    scholarship: 'all',
                    timePeriod: 'all'
                },

                init() {
                    try {
                        console.log('Central Stats Tab Initializing', this.analyticsData);

                        if (this.campusOptions.length === 0 && window.sfaoCampusOptions) {
                            this.campusOptions = window.sfaoCampusOptions;
                        }

                        // Initial Filters
                        this.filters.campus = localStorage.getItem('centralStatsCampus') || 'all';

                        // Persist ViewMode (Student Type)
                        const savedViewMode = localStorage.getItem('central_view_mode');
                        if (savedViewMode && ['applicants', 'scholars'].includes(savedViewMode)) {
                            this.viewMode = savedViewMode;
                        }
                        this.$watch('viewMode', (val) => {
                            localStorage.setItem('central_view_mode', val);
                            this.applyFilters();
                        });

                        this.$watch('filters.scholarship', (val) => {
                            localStorage.setItem('central_scholarship_filter', String(val));
                            this.applyFilters();
                        });

                        this.availableColleges = this.analyticsData.all_colleges || [];

                        // Initialize logic
                        this.updateCollegesList(this.filters.campus);
                        this.generateAcademicYears(); // Populate AY options
                        this.updateProgramList();
                        this.updateTrackList();

                        this.$nextTick(() => {
                            const savedScholarship = localStorage.getItem('central_scholarship_filter');

                            if (savedScholarship !== null) {
                                let val = savedScholarship.replace(/^"|"$/g, '');
                                if (val !== 'all') {
                                    val = String(val);
                                }
                                const exists = val === 'all' || (this.analyticsData.available_scholarships || []).some(s => String(s.id) === val);
                                if (exists) {
                                    this.filters.scholarship = val;
                                } else {
                                    this.filters.scholarship = 'all';
                                }
                            } else {
                                const available = this.analyticsData.available_scholarships || [];
                                if (available.length > 0) {
                                    this.filters.scholarship = String(available[0].id);
                                }
                            }
                            this.applyFilters();
                        });

                        this.$watch('filters.campus', (value) => {
                            this.updateCollegesList(value);
                            this.updateProgramList();
                            this.updateTrackList();
                            this.applyFilters();
                        });

                        this.$watch('filters.scholarship', () => this.applyFilters());
                        this.$watch('filters.timePeriod', () => this.applyFilters());
                        this.$watch('viewMode', () => this.applyFilters());

                        this.$watch('localFilters.college', (val) => {
                            this.updateProgramList();
                            this.updateTrackList();
                            this.applyFilters();
                        });
                        this.$watch('localFilters.program', () => {
                            this.updateTrackList();
                            this.applyFilters();
                        });

                        this.$watch('localFilters.track', () => {
                            this.applyFilters();
                        });

                        this.$watch('chartLegend', () => {
                            this.applyFilters();
                        }, { deep: true });

                        this.$nextTick(() => {
                            setTimeout(() => this.createAllCharts(), 300);
                        });

                        window.addEventListener('set-stats-filter', (e) => {
                            this.filters.campus = e.detail;
                        });

                        const observer = new MutationObserver((mutations) => {
                            mutations.forEach((mutation) => {
                                if (mutation.attributeName === 'class') {
                                    this.createAllCharts();
                                }
                            });
                        });
                        observer.observe(document.documentElement, { attributes: true });
                    } catch (error) {
                        console.error('CRITICAL ERROR in Central Statistics Tab init:', error);
                    }
                },

                handleTabChange(newTab) {
                    if (newTab && newTab.includes('statistics')) {
                        window.dispatchEvent(new Event('resize'));
                        requestAnimationFrame(() => {
                            setTimeout(() => {
                                this.createAllCharts();
                            }, 100);
                        });
                    }
                },

                createStudentComparisonChart() {
                    const ctx = document.getElementById('sfaoStudentComparisonChart');
                    if (!ctx) return;
                    if (chartInstances.studentComparison) chartInstances.studentComparison.destroy();

                    const applicantsCount = this.analyticsData.users?.unique_applicants || 0;
                    const scholarsCount = this.analyticsData.users?.unique_scholars || 0;

                    chartInstances.studentComparison = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['Total'],
                            datasets: [
                                {
                                    label: 'Applicants',
                                    data: [applicantsCount],
                                    backgroundColor: 'rgba(59, 130, 246, 0.7)', // Blue
                                    borderColor: 'rgba(59, 130, 246, 1)',
                                    borderWidth: 1,
                                    barThickness: 60
                                },
                                {
                                    label: 'Scholars',
                                    data: [scholarsCount],
                                    backgroundColor: 'rgba(16, 185, 129, 0.7)', // Green
                                    borderColor: 'rgba(16, 185, 129, 1)',
                                    borderWidth: 1,
                                    barThickness: 60
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom' },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.dataset.label + ': ' + context.raw.toLocaleString();
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    stacked: true,
                                    beginAtZero: true,
                                    grid: { display: true, color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb' },
                                    ticks: { color: this.getTextColor(), precision: 0 }
                                },
                                x: {
                                    stacked: true,
                                    grid: { display: false },
                                    ticks: { display: false } // Hide 'Total' label for cleaner look
                                }
                            }
                        }
                    });
                },

                getTextColor() {
                    return document.documentElement.classList.contains('dark') ? '#ffffff' : '#374151';
                },



                updateCollegesList(campusId) {
                    if (campusId === 'all') {
                        const allShortNames = [...new Set(Object.values(this.analyticsData.campus_colleges || {}).flat())];
                        this.availableColleges = (this.analyticsData.all_colleges || []).filter(d => allShortNames.includes(d.short_name));
                    } else {
                        const campusShortNames = (this.analyticsData.campus_colleges || {})[campusId] || [];
                        this.availableColleges = (this.analyticsData.all_colleges || []).filter(d => campusShortNames.includes(d.short_name));
                    }
                    if (this.localFilters.college !== 'all' && !this.availableColleges.find(d => d.short_name === this.localFilters.college)) {
                        this.localFilters.college = 'all';
                    }
                },

                updateProgramList() {
                    const campus = this.filters.campus;
                    const college = this.localFilters.college;
                    const strictMap = this.analyticsData.campus_college_programs || {};

                    const allPrograms = new Set();
                    let targetCampuses = [];
                    if (campus === 'all') {
                        targetCampuses = Object.keys(strictMap);
                    } else {
                        targetCampuses = [String(campus)];
                    }

                    targetCampuses.forEach(cId => {
                        const campusData = strictMap[cId] || {};
                        let targetColleges = [];
                        if (college === 'all') {
                            targetColleges = Object.keys(campusData);
                        } else {
                            if (campusData[college]) targetColleges = [college];
                        }

                        targetColleges.forEach(colName => {
                            const progs = campusData[colName] || [];
                            progs.forEach(p => allPrograms.add(p));
                        });
                    });

                    this.availablePrograms = Array.from(allPrograms).sort();
                    if (this.localFilters.program !== 'all' && !this.availablePrograms.includes(this.localFilters.program)) {
                        this.localFilters.program = 'all';
                    }
                },

                updateTrackList() {
                    const program = this.localFilters.program;
                    const tracksMap = this.analyticsData.program_tracks || {};

                    if (program === 'all') {
                        // Reuse updateProgramList logic to get all visible programs and then their tracks
                        // Simplified: Get all tracks for available programs
                         const allTracks = new Set();
                         this.availablePrograms.forEach(p => {
                             const pTracks = tracksMap[p] || [];
                             pTracks.forEach(t => allTracks.add(t));
                         });
                        this.availableTracks = Array.from(allTracks).sort();
                        this.localFilters.track = 'all';
                    } else {
                        this.availableTracks = tracksMap[program] || [];
                        if (this.localFilters.track !== 'all' && !this.availableTracks.includes(this.localFilters.track)) {
                            this.localFilters.track = 'all';
                        }
                    }
                },

                generateAcademicYears() {
                    const allApps = this.analyticsData.all_applications_data || [];
                    const years = new Set();
                    allApps.forEach(a => {
                        if (!a.created_at) return;
                        const date = new Date(a.created_at);
                        const year = date.getFullYear();
                        const month = date.getMonth(); 
                        const startYear = month >= 7 ? year : year - 1;
                        years.add(`${startYear}-${startYear + 1}`);
                    });
                    if (years.size === 0) {
                        const now = new Date();
                        const curY = now.getMonth() >= 7 ? now.getFullYear() : now.getFullYear() - 1;
                        years.add(`${curY}-${curY + 1}`);
                    }
                    this.academicYearOptions = Array.from(years).sort().reverse();
                },

                updateFilteredData() {
                    let data = JSON.parse(JSON.stringify(this.analyticsData));
                    let allStudents = this.analyticsData.all_students_data || [];
                    let allApplications = this.analyticsData.all_applications_data || [];

                    let allowedColleges = [];
                    if (this.filters.campus === 'all') {
                        const allCols = Object.values(this.analyticsData.campus_colleges || {}).flat();
                        allowedColleges = [...new Set(allCols)];
                    } else {
                        allowedColleges = (this.analyticsData.campus_colleges || {})[this.filters.campus] || [];
                        allStudents = allStudents.filter(s => s.campus_id == this.filters.campus);
                        allApplications = allApplications.filter(a => a.campus_id == this.filters.campus);
                    }

                    if (data.college_stats) {
                        data.college_stats = data.college_stats.filter(d => allowedColleges.includes(d.name));
                    }

                    if (this.filters.scholarship && this.filters.scholarship !== 'all') {
                        const selectedScholarship = (this.analyticsData.available_scholarships || []).find(s => s.id == this.filters.scholarship);
                        if (selectedScholarship) {
                            allApplications = allApplications.filter(item => item.scholarship_name === selectedScholarship.scholarship_name);
                        }
                    }

                    if (this.filters.timePeriod !== 'all') {
                        allApplications = allApplications.filter(a => {
                            if (!a.created_at) return false;
                            const date = new Date(a.created_at);
                            const year = date.getFullYear();
                            const month = date.getMonth();
                            const startYear = month >= 7 ? year : year - 1;
                            const ay = `${startYear}-${startYear + 1}`;
                            return ay === this.filters.timePeriod;
                        });
                    }

                    if (this.localFilters.college !== 'all') {
                        allApplications = allApplications.filter(item => item.college === this.localFilters.college);
                    }
                    if (this.localFilters.program !== 'all') {
                        allApplications = allApplications.filter(item => item.program === this.localFilters.program);
                    }
                    if (this.localFilters.track !== 'all') {
                        allApplications = allApplications.filter(item => item.track === this.localFilters.track);
                    }

                    allApplications.forEach(app => {
                        if (this.viewMode === 'scholars' && (app.status !== 'approved' || !app.scholar_id)) return;
                        const isScholarVal = Number(app.is_global_scholar);
                        if (this.viewMode === 'applicants' && isScholarVal > 0) return;

                        if (this.viewMode === 'applicants') {
                            if (!['pending', 'approved', 'rejected', 'in_progress'].includes(app.status)) return;
                            if (app.status === 'pending' && !this.chartLegend.pending) return;
                            if (app.status === 'approved' && !this.chartLegend.approved) return;
                            if (app.status === 'rejected' && !this.chartLegend.rejected) return;
                            if (app.status === 'in_progress' && !this.chartLegend.inProgress) return;
                        } else {
                            if (app.status === 'approved' && app.scholar_id) {
                                const isNew = app.scholar_type === 'new';
                                if (isNew && !this.chartLegend.newScholars) return;
                                if (!isNew && !this.chartLegend.oldScholars) return;
                            }
                        }
                    });

                    const scholarshipStats = {};
                    allApplications.forEach(app => {
                        if (this.viewMode === 'scholars' && (app.status !== 'approved' || !app.scholar_id)) return;
                        const isScholarVal2 = Number(app.is_global_scholar);
                        if (this.viewMode === 'applicants' && isScholarVal2 > 0) return;

                        const name = app.scholarship_name || 'Unknown';
                        if (!scholarshipStats[name]) {
                            scholarshipStats[name] = {
                                scholars: new Set(),
                                nonScholars: new Set()
                            };
                        }
                        if (app.status === 'approved' && app.scholar_id) {
                            scholarshipStats[name].scholars.add(app.user_id);
                        } else {
                            scholarshipStats[name].nonScholars.add(app.user_id);
                        }
                    });

                    const finalScholarshipStats = {};
                    Object.keys(scholarshipStats).forEach(key => {
                        finalScholarshipStats[key] = {
                            scholars: scholarshipStats[key].scholars.size,
                            nonScholars: scholarshipStats[key].nonScholars.size
                        };
                    });

                    data.scholarshipStats = finalScholarshipStats;
                    data.all_applications_data = allApplications;

                    const summaryCounts = {
                        total: 0,
                        approved: 0,
                        rejected: 0,
                        approvalRate: 0,
                        inProgress: 0,
                        pending: 0
                    };

                    allApplications.forEach(app => {
                        if (this.viewMode === 'scholars' && (app.status !== 'approved' || !app.scholar_id)) return;
                        const isScholarVal = Number(app.is_global_scholar);
                        if (this.viewMode === 'applicants' && isScholarVal > 0) return;
                        if (this.viewMode === 'applicants' && !['pending', 'approved', 'rejected', 'in_progress'].includes(app.status)) return;

                        summaryCounts.total++;
                        if (app.status === 'approved') summaryCounts.approved++;
                        if (app.status === 'rejected') summaryCounts.rejected++;
                        if (app.status === 'in_progress') summaryCounts.inProgress++;
                        if (app.status === 'pending') summaryCounts.pending++;
                    });

                    summaryCounts.active = summaryCounts.total - summaryCounts.approved - summaryCounts.rejected;
                    if (summaryCounts.total > 0) {
                        summaryCounts.approvalRate = ((summaryCounts.approved / summaryCounts.total) * 100).toFixed(1);
                    } else {
                        summaryCounts.approvalRate = '0.0';
                    }

                    data.counts = summaryCounts;
                    this.filteredData = data;
                },

                applyFilters() {
                    this.updateFilteredData();
                    this.updateCharts();
                },

                getChartTitle() {
                    if (this.filters.campus === 'all') {
                        return 'Scholarship Status (Campus Comparison)';
                    }
                    const campus = this.campusOptions.find(c => c.id == this.filters.campus);
                    const name = campus ? campus.name : 'College Comparison';
                    return `Scholarship Status (${name} - Colleges)`;
                },

                createAllCharts(retryCount = 0) {
                    if (typeof Chart === 'undefined') {
                        if (retryCount > 20) return;
                        setTimeout(() => this.createAllCharts(retryCount + 1), 500);
                        return;
                    }
                    const ctx = document.getElementById('sfaoCollegeChart');
                    if (!ctx) {
                        if (retryCount > 10) return;
                        setTimeout(() => this.createAllCharts(retryCount + 1), 500);
                        return;
                    }

                    if (ctx.clientWidth === 0 || ctx.clientHeight === 0) {
                        if (retryCount > 10) return;
                        setTimeout(() => this.createAllCharts(retryCount + 1), 200);
                        return;
                    }

                    const container = ctx.parentElement;
                    const ro = new ResizeObserver(() => {
                        if (chartInstances.college && ctx.getBoundingClientRect().width > 0) {
                            chartInstances.college.resize();
                        } else if (!chartInstances.college && ctx.getBoundingClientRect().width > 0) {
                            this.createCollegeChart();
                        }
                    });
                    ro.observe(container);

                    this.createCollegeChart();
                    this.createCollegeChart();
                    this.createComparisonChart();
                    this.createStudentComparisonChart();
                    this.createTrendChart();
                },

                updateCharts() {
                    this.createCollegeChart();
                    this.createComparisonChart();
                    this.createStudentComparisonChart();
                    this.createTrendChart();
                },

                createCollegeChart() {
                    const ctx = document.getElementById('sfaoCollegeChart');
                    if (!ctx) return;
                    if (chartInstances.college) chartInstances.college.destroy();

                    let rawData = this.filteredData.all_applications_data || [];
                    const groupedData = {};
                    const campusMap = {};
                    this.campusOptions.forEach(c => {
                        if (c.id !== 'all') campusMap[c.id] = c.name;
                    });
                    const isComparisonMode = (this.filters.campus === 'all');

                    rawData.forEach(item => {
                        const isGlobalScholar = Number(item.is_global_scholar);
                        if (this.viewMode === 'applicants' && isGlobalScholar > 0) return;

                        let groupKey = 'Unknown';
                        if (isComparisonMode) {
                            groupKey = campusMap[item.campus_id] || 'Other';
                        } else {
                            groupKey = item.college || 'No College';
                        }

                        if (!groupedData[groupKey]) {
                            groupedData[groupKey] = {
                                pending: new Set(), approved: new Set(), rejected: new Set(),
                                newScholars: new Set(), oldScholars: new Set(), inProgress: new Set()
                            };
                        }

                        if (this.viewMode === 'applicants') {
                            if (item.status === 'pending') groupedData[groupKey].pending.add(item.user_id);
                            else if (item.status === 'approved') groupedData[groupKey].approved.add(item.user_id);
                            else if (item.status === 'rejected') groupedData[groupKey].rejected.add(item.user_id);
                            else if (item.status === 'in_progress') groupedData[groupKey].inProgress.add(item.user_id);
                        } else {
                            if (item.status === 'approved' && item.scholar_id) {
                                if (item.scholar_type === 'new') groupedData[groupKey].newScholars.add(item.user_id);
                                else groupedData[groupKey].oldScholars.add(item.user_id);
                            }
                        }
                    });

                    const allLabels = Object.keys(groupedData).sort();
                    const labels = allLabels.filter(name => {
                        const data = groupedData[name];
                        let visibleCount = 0;
                        if (this.viewMode === 'applicants') {
                            if (this.chartLegend.approved) visibleCount += data.approved.size;
                            if (this.chartLegend.pending) visibleCount += data.pending.size;
                            if (this.chartLegend.rejected) visibleCount += data.rejected.size;
                            if (this.chartLegend.inProgress) visibleCount += data.inProgress.size;
                        } else {
                            if (this.chartLegend.newScholars) visibleCount += data.newScholars.size;
                            if (this.chartLegend.oldScholars) visibleCount += data.oldScholars.size;
                        }
                        return visibleCount > 0;
                    });

                    const pendingData = labels.map(l => groupedData[l].pending.size);
                    const approvedData = labels.map(l => groupedData[l].approved.size);
                    const rejectedData = labels.map(l => groupedData[l].rejected.size);
                    const inProgressData = labels.map(l => groupedData[l].inProgress.size);
                    const newScholarsData = labels.map(l => groupedData[l].newScholars.size);
                    const oldScholarsData = labels.map(l => groupedData[l].oldScholars.size);

                    const processedLabels = labels.map(label => {
                        const words = label.split(' ');
                        const lines = [];
                        let currentLine = [];
                        words.forEach(word => {
                            if (currentLine.length >= 2 || (currentLine.join(' ').length + word.length > 15)) {
                                lines.push(currentLine.join(' '));
                                currentLine = [];
                            }
                            currentLine.push(word);
                        });
                        if (currentLine.length > 0) lines.push(currentLine.join(' '));
                        return lines;
                    });

                    const hasData = labels.some(l => {
                        let count = 0;
                        if (groupedData[l]) {
                            count += groupedData[l].pending.size + groupedData[l].approved.size + groupedData[l].rejected.size + groupedData[l].inProgress.size + groupedData[l].newScholars.size + groupedData[l].oldScholars.size;
                        }
                        return count > 0;
                    });
                    this.chartStatus = { ...this.chartStatus, college: labels.length > 0 && hasData };

                    if (labels.length === 0 || !hasData) return;

                    const datasets = [];
                   if (this.viewMode === 'applicants') {
                        if (this.chartLegend.approved) datasets.push({ label: 'Approved', data: approvedData, backgroundColor: '#10B981' });
                        if (this.chartLegend.pending) datasets.push({ label: 'Pending', data: pendingData, backgroundColor: '#F59E0B' });
                        if (this.chartLegend.rejected) datasets.push({ label: 'Rejected', data: rejectedData, backgroundColor: '#EF4444' });
                        if (this.chartLegend.inProgress) datasets.push({ label: 'In Progress', data: inProgressData, backgroundColor: '#3B82F6' });
                    } else {
                        if (this.chartLegend.newScholars) datasets.push({ label: 'New Scholars', data: newScholarsData, backgroundColor: '#3B82F6' });
                        if (this.chartLegend.oldScholars) datasets.push({ label: 'Old Scholars', data: oldScholarsData, backgroundColor: '#10B981' });
                    }

                    chartInstances.college = new Chart(ctx, {
                        type: 'bar',
                        data: { labels: processedLabels, datasets: datasets },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: { stacked: true, ticks: { color: this.getTextColor() }, grid: { display: false } },
                                y: { stacked: true, beginAtZero: true, ticks: { color: this.getTextColor() }, grid: { color: document.documentElement.classList.contains('dark') ? '#4B5563' : '#E5E7EB' } }
                            },
                            plugins: { legend: { display: false } }
                        }
                    });
                },

                createComparisonChart() {
                    const ctx = document.getElementById('sfaoComparisonChart');
                    if (!ctx) return;
                    if (chartInstances.comparison) chartInstances.comparison.destroy();

                    // Source: All applications filtered by Campus/Dept/Time but NOT Scholarship
                    let rawData = this.analyticsData.all_applications_data || [];

                    // 1. Campus
                    if (this.filters.campus !== 'all') {
                        rawData = rawData.filter(a => a.campus_id == this.filters.campus);
                    }
                    // 2. Time (Academic Year)
                    if (this.filters.timePeriod && this.filters.timePeriod !== 'all') {
                        rawData = rawData.filter(a => {
                            if (!a.created_at) return false;
                            const date = new Date(a.created_at);
                            const year = date.getFullYear();
                            const month = date.getMonth();
                            const startYear = month >= 7 ? year : year - 1;
                            const ay = `${startYear}-${startYear + 1}`;
                            return ay === this.filters.timePeriod;
                        });
                    }

                    // 3. College
                    if (this.localFilters.college !== 'all') {
                        rawData = rawData.filter(item => item.college === this.localFilters.college);
                    }
                    // 4. Program
                    if (this.localFilters.program !== 'all') {
                        rawData = rawData.filter(item => item.program === this.localFilters.program);
                    }
                    // 5. Track
                    if (this.localFilters.track !== 'all') {
                        rawData = rawData.filter(item => item.track === this.localFilters.track);
                    }


                    // Group by Scholarship Name (Counting Unique Applicants per Status)
                    const groupedData = {};
                    rawData.forEach(item => {
                        // View Mode Filter
                        if (this.viewMode === 'scholars' && (item.status !== 'approved' || !item.scholar_id)) return;

                        const isScholarVal = Number(item.is_global_scholar);
                        if (this.viewMode === 'applicants' && isScholarVal > 0) return;
                        
                        // Graph only shows: Pending, Approved, Rejected, AND In Progress.
                        if (this.viewMode === 'applicants') {
                            if (!['pending', 'approved', 'rejected', 'in_progress'].includes(item.status)) return;
                        }

                        const name = item.scholarship_name || 'Unknown';
                        if (!groupedData[name]) {
                            groupedData[name] = {
                                pending: new Set(),
                                approved: new Set(),
                                rejected: new Set(),
                                newScholars: new Set(),
                                oldScholars: new Set(),
                                inProgress: new Set()
                            };
                        }

                        if (this.viewMode === 'applicants') {
                            if (item.status === 'pending') groupedData[name].pending.add(item.user_id);
                            else if (item.status === 'approved') groupedData[name].approved.add(item.user_id);
                            else if (item.status === 'rejected') groupedData[name].rejected.add(item.user_id);
                            else if (item.status === 'in_progress') groupedData[name].inProgress.add(item.user_id);
                        } else {
                            // Scholars Mode
                            if (item.status === 'approved' && item.scholar_id) {
                                if (item.scholar_type === 'new') groupedData[name].newScholars.add(item.user_id);
                                else groupedData[name].oldScholars.add(item.user_id);
                            }
                        }
                    });

                    // Filter labels based on VISIBLE data (legend state)
                    const allLabels = Object.keys(groupedData).sort();

                    const labels = allLabels.filter(name => {
                        const data = groupedData[name];
                        let visibleCount = 0;

                        if (this.viewMode === 'applicants') {
                            if (this.chartLegend.approved) visibleCount += data.approved.size;
                            if (this.chartLegend.pending) visibleCount += data.pending.size;
                            if (this.chartLegend.rejected) visibleCount += data.rejected.size;
                            if (this.chartLegend.inProgress) visibleCount += data.inProgress.size;
                        } else {
                            if (this.chartLegend.newScholars) visibleCount += data.newScholars.size;
                            if (this.chartLegend.oldScholars) visibleCount += data.oldScholars.size;
                        }
                        return visibleCount > 0;
                    });

                    // Process labels for multi-line display
                    const processedLabels = labels.map(l => l.length > 20 ? l.substring(0, 20) + '...' : l);

                    // Update Chart Status
                    this.chartStatus.comparison = labels.length > 0;
                    this.chartStatus.comparisonCount = labels.length;

                    // Destroy old chart first
                    if (chartInstances.comparison) chartInstances.comparison.destroy();

                    if (labels.length === 0) {
                        return;
                    }

                    // Build Datasets
                    let datasets = [];
                    if (this.viewMode === 'applicants') {
                        const pendingData = labels.map(l => groupedData[l].pending.size);
                        const approvedData = labels.map(l => groupedData[l].approved.size);
                        const rejectedData = labels.map(l => groupedData[l].rejected.size);
                        const inProgressData = labels.map(l => groupedData[l].inProgress.size);

                        datasets = [
                            {
                                label: 'Approved',
                                data: approvedData,
                                backgroundColor: '#10B981',
                                hidden: !this.chartLegend.approved
                            },
                            {
                                label: 'Rejected',
                                data: rejectedData,
                                backgroundColor: '#EF4444',
                                hidden: !this.chartLegend.rejected
                            },
                            {
                                label: 'Pending',
                                data: pendingData,
                                backgroundColor: '#F59E0B',
                                hidden: !this.chartLegend.pending
                            },
                            {
                                label: 'In Progress',
                                data: inProgressData,
                                backgroundColor: '#3B82F6',
                                hidden: !this.chartLegend.inProgress
                            }
                        ];
                    } else {
                        const newScholarsData = labels.map(l => groupedData[l].newScholars.size);
                        const oldScholarsData = labels.map(l => groupedData[l].oldScholars.size);
                        datasets = [
                            {
                                label: 'Old Scholars',
                                data: oldScholarsData,
                                backgroundColor: '#10B981',
                                hidden: !this.chartLegend.oldScholars
                            },
                            {
                                label: 'New Scholars',
                                data: newScholarsData,
                                backgroundColor: '#3B82F6',
                                hidden: !this.chartLegend.newScholars
                            }
                        ];
                    }

                    chartInstances.comparison = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: processedLabels,
                            datasets: datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    ticks: {
                                        color: this.getTextColor(),
                                        autoSkip: false,
                                        maxRotation: 0,
                                        minRotation: 0
                                    },
                                    stacked: true
                                },
                                y: {
                                    ticks: { color: this.getTextColor(), beginAtZero: true, precision: 0 },
                                    stacked: true
                                }
                            },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        title: (items) => {
                                            const label = items[0].label;
                                            return Array.isArray(label) ? label.join(' ') : label;
                                        }
                                    }
                                }
                            }
                        }
                    });
                },

                createTrendChart() {
                    const ctx = document.getElementById('sfaoTrendChart');
                    if (!ctx) return;
                    if (chartInstances.trend) chartInstances.trend.destroy();

                    // Use pre-filtered data (Respects all Global Filters)
                    let rawData = this.filteredData.all_applications_data || [];

                    // Group by Time Unit (Month)
                    const groupedData = {};
                    const timeLabels = new Set();
                    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

                    rawData.forEach(item => {
                        if (!item.created_at) return;

                        // View Mode Logic 
                        if (this.viewMode === 'scholars' && (item.status !== 'approved' || !item.scholar_id)) return;
                        const isScholarVal = Number(item.is_global_scholar);
                        if (this.viewMode === 'applicants' && isScholarVal > 0) return;
                        if (this.viewMode === 'applicants' && !['pending', 'approved', 'rejected', 'in_progress'].includes(item.status)) return;

                        const date = new Date(item.created_at);
                        const year = date.getFullYear();
                        const month = date.getMonth();
                        const key = `${year}-${String(month + 1).padStart(2, '0')}`; // YYYY-MM
                        const label = `${monthNames[month]} ${year}`;

                        timeLabels.add(JSON.stringify({ key, label }));

                        if (!groupedData[key]) {
                            groupedData[key] = { pending: 0, approved: 0, rejected: 0, in_progress: 0, new: 0, old: 0 };
                        }

                        if (this.viewMode === 'applicants') {
                            if (item.status === 'pending') groupedData[key].pending++;
                            else if (item.status === 'approved') groupedData[key].approved++;
                            else if (item.status === 'rejected') groupedData[key].rejected++;
                            else if (item.status === 'in_progress') groupedData[key].in_progress++;
                        } else {
                            if (item.status === 'approved' && item.scholar_id) {
                                if (item.scholar_type === 'new') groupedData[key].new++;
                                else groupedData[key].old++;
                            }
                        }
                    });

                    // Sort Labels
                    const sortedLabels = Array.from(timeLabels).map(l => JSON.parse(l)).sort((a, b) => a.key.localeCompare(b.key));
                    const chartLabels = sortedLabels.map(l => l.label);
                    const timeKeys = sortedLabels.map(l => l.key);

                    // Update Status
                    this.chartStatus.trend = timeKeys.length > 0;
                    if (timeKeys.length === 0) return;

                    // Calculate Visible Count for Shading logic
                    let visibleCount = 0;
                    if (this.viewMode === 'applicants') {
                        if (this.chartLegend.approved) visibleCount++;
                        if (this.chartLegend.rejected) visibleCount++;
                        if (this.chartLegend.pending) visibleCount++;
                        if (this.chartLegend.inProgress) visibleCount++;
                    } else {
                        if (this.chartLegend.oldScholars) visibleCount++;
                        if (this.chartLegend.newScholars) visibleCount++;
                    }
                    const shouldFill = (visibleCount === 1);

                    let datasets = [];
                   if (this.viewMode === 'applicants') {
                        datasets = [
                            {
                                label: 'Approved',
                                data: timeKeys.map(k => groupedData[k].approved),
                                borderColor: '#10B981',
                                backgroundColor: shouldFill ? 'rgba(16, 185, 129, 0.2)' : '#10B981',
                                fill: shouldFill ? 'origin' : false,
                                tension: 0.3,
                                hidden: !this.chartLegend.approved
                            },
                             {
                                label: 'Rejected',
                                data: timeKeys.map(k => groupedData[k].rejected),
                                borderColor: '#EF4444',
                                backgroundColor: shouldFill ? 'rgba(239, 68, 68, 0.2)' : '#EF4444',
                                fill: shouldFill ? 'origin' : false,
                                tension: 0.3,
                                hidden: !this.chartLegend.rejected
                            },
                            {
                                label: 'Pending',
                                data: timeKeys.map(k => groupedData[k].pending),
                                borderColor: '#F59E0B',
                                backgroundColor: shouldFill ? 'rgba(245, 158, 11, 0.2)' : '#F59E0B',
                                fill: shouldFill ? 'origin' : false,
                                tension: 0.3,
                                hidden: !this.chartLegend.pending
                            },
                            {
                                label: 'In Progress',
                                data: timeKeys.map(k => groupedData[k].in_progress),
                                borderColor: '#3B82F6',
                                backgroundColor: shouldFill ? 'rgba(59, 130, 246, 0.2)' : '#3B82F6',
                                fill: shouldFill ? 'origin' : false,
                                tension: 0.3,
                                hidden: !this.chartLegend.inProgress
                            }
                        ];
                    } else {
                        datasets = [
                             {
                                label: 'Old Scholars',
                                data: timeKeys.map(k => groupedData[k].old),
                                borderColor: '#10B981',
                                backgroundColor: shouldFill ? 'rgba(16, 185, 129, 0.2)' : '#10B981',
                                fill: shouldFill ? 'origin' : false,
                                tension: 0.3,
                                hidden: !this.chartLegend.oldScholars
                            },
                            {
                                label: 'New Scholars',
                                data: timeKeys.map(k => groupedData[k].new),
                                borderColor: '#3B82F6',
                                backgroundColor: shouldFill ? 'rgba(59, 130, 246, 0.2)' : '#3B82F6',
                                fill: shouldFill ? 'origin' : false,
                                tension: 0.3,
                                hidden: !this.chartLegend.newScholars
                            }
                        ];
                    }

                    chartInstances.trend = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: chartLabels,
                            datasets: datasets
                        },
                         options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { precision: 0, color: this.getTextColor() }
                                },
                                x: {
                                    ticks: { color: this.getTextColor() }
                                }
                            },
                            plugins: {
                                legend: { display: false },
                                tooltip: { mode: 'index', intersect: false }
                            }
                        }
                    });
                }
            };
        });
    });
    </script>
</div>
