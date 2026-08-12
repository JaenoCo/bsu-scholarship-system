<div x-show="tab === 'statistics'" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-cloak 
     x-data="sfaoStatisticsTab()">
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white" x-text="getStatisticsHeader()">All Statistics</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Insights into scholarship applications and student performance for {{ $sfaoCampus->name }} and its extensions.
                </p>
            </div>
        </div>

        <!-- Filter Controls -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
            <div class="flex flex-wrap gap-4 items-end">
                
                <!-- Department Filter -->
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Department</label>
                    <div class="relative">
                        <select x-model="filters.department" 
                                class="block w-full px-3 py-2 text-base border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none"
                                style="border-width: 1px;">
                            <option value="all">All Departments</option>
                            <template x-for="dept in availableDepartments" :key="dept.id">
                                <option :value="dept.short_name" x-text="dept.short_name"></option>
                            </template>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Time Period Filter -->
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Time Period</label>
                    <div class="relative">
                        <select x-model="filters.timePeriod" 
                                class="block w-full px-3 py-2 text-base border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none"
                                style="border-width: 1px;">
                            <option value="all">All Time</option>
                            <option value="this_month">This Month</option>
                            <option value="last_3_months">Last 3 Months</option>
                            <option value="this_year">This Year</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>



                <!-- Reset Filters Icon -->
                <div class="flex flex-col items-center">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Clear</label>
                    <button @click="clearFilters()" 
                            class="inline-flex items-center justify-center p-2 border border-red-500 rounded-full shadow-sm text-gray-500 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-400 dark:border-red-500 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bsu-red transition ease-in-out duration-150 h-[38px] w-[38px]"
                            title="Reset Filters">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>
                </div>

            </div>
        </div>

        <!-- Analytics Charts Section -->
        
        <!-- Row 1: Department Application Status (Full Width) -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mt-6">
            <div class="text-center mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Department Application Status</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400" x-text="getCurrentFilterLabel()"></p>
            </div>
            <div class="h-96 flex items-center justify-center">
                <canvas id="sfaoDepartmentChart"></canvas>
            </div>
        </div>

        <!-- Row 2: Gender & Scholarship Type -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Scholarship Scholar Status Chart -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="text-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Scholarship Scholar Status</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400" x-text="getCurrentFilterLabel()"></p>
                </div>
                <div class="h-64 flex items-center justify-center">
                    <canvas id="sfaoScholarshipTypeChart"></canvas>
                </div>
            </div>

            <!-- Key Metrics Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Applications -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Applications</dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white" x-text="filteredData.total_applications || 0"></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approved Applications -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Approved Applications</dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white" x-text="filteredData.approved_applications || 0"></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Applications -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Pending Applications</dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white" x-text="filteredData.pending_applications || 0"></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approval Rate -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-bsu-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Approval Rate</dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white" x-text="(filteredData.approval_rate || 0) + '%'"></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Department Statistics Table -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Department Statistics Summary</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Students</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Applications</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Approved</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Approval Rate</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-for="dept in filteredData.department_stats" :key="dept.name">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white" x-text="dept.name"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300" x-text="dept.total_students"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300" x-text="dept.total_applications"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300" x-text="dept.approved_applications"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300" x-text="dept.approval_rate + '%'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart.js Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Analytics Data -->
    <div id="sfao-analytics-data" data-analytics='@json($analytics ?? [])' style="display: none;"></div>
    
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('sfaoStatisticsTab', () => {
                // Store chart instances outside of Alpine's reactive scope
                let charts = {
                    department: null,
                    gender: null,
                    scholarshipType: null
                };

                return {
                    analyticsData: JSON.parse(document.getElementById('sfao-analytics-data').dataset.analytics || '{}'),
                    campusOptions: @json($campusOptions),
                    filteredData: {},
                    availableDepartments: [],
                    filters: {
                        campus: localStorage.getItem('sfaoStatsCampus') || '{{ $defaultStatsCampus }}',
                        department: 'all',
                        timePeriod: 'all'
                    },
                    
                    init() {
                        console.log('SFAO Statistics tab initialized');
                        
                        this.availableDepartments = this.analyticsData.all_departments || [];
                        
                        // Initialize departments list based on current campus filter
                        this.updateDepartmentsList(this.filters.campus);
                        this.updateFilteredData();
                        
                        this.$watch('filters.campus', (value) => {
                            this.updateDepartmentsList(value);
                            this.applyFilters();
                        });

                        this.$watch('filters.department', () => {
                            this.applyFilters();
                        });

                        this.$watch('filters.timePeriod', () => {
                            this.applyFilters();
                        });
                        
                        this.$nextTick(() => {
                            setTimeout(() => {
                                this.createAllCharts();
                            }, 100);
                        });

                        // Listen for sidebar campus selection
                        window.addEventListener('set-stats-filter', (e) => {
                            this.filters.campus = e.detail;
                            // No need to call applyFilters here because $watch('filters.campus') handles it
                        });

                        // Watch for dark mode changes
                        const observer = new MutationObserver((mutations) => {
                            mutations.forEach((mutation) => {
                                if (mutation.attributeName === 'class') {
                                    this.createAllCharts();
                                }
                            });
                        });
                        observer.observe(document.documentElement, { attributes: true });
                    },

                    getTextColor() {
                        return document.documentElement.classList.contains('dark') ? '#ffffff' : '#374151';
                    },

                    updateDepartmentsList(campusId) {
                        if (campusId === 'all') {
                            // All unique departments from all campuses
                            const allShortNames = [...new Set(Object.values(this.analyticsData.campus_departments || {}).flat())];
                            this.availableDepartments = (this.analyticsData.all_departments || []).filter(d => allShortNames.includes(d.short_name));
                        } else {
                            // Departments for specific campus
                            const campusShortNames = this.analyticsData.campus_departments[campusId] || [];
                            this.availableDepartments = (this.analyticsData.all_departments || []).filter(d => campusShortNames.includes(d.short_name));
                        }
                        
                        // Reset department selection if invalid
                        if (this.filters.department !== 'all' && !this.availableDepartments.find(d => d.short_name === this.filters.department)) {
                            this.filters.department = 'all';
                        }
                    },
                    
                    updateFilteredData() {
                        let data = JSON.parse(JSON.stringify(this.analyticsData)); // Deep copy
                        let allStudents = this.analyticsData.all_students_data || [];
                        let allApplications = this.analyticsData.all_applications_data || [];

                        // 1. Filter Department Stats based on Campus (Sidebar)
                        let allowedDepartments = [];
                        if (this.filters.campus === 'all') {
                            const allDepts = Object.values(this.analyticsData.campus_departments || {}).flat();
                            allowedDepartments = [...new Set(allDepts)];
                            // allStudents remains as is (all campuses under SFAO)
                        } else {
                            allowedDepartments = this.analyticsData.campus_departments[this.filters.campus] || [];
                            // Filter students by campus
                            allStudents = allStudents.filter(s => s.campus_id == this.filters.campus);
                            // Filter applications by campus
                            allApplications = allApplications.filter(a => a.campus_id == this.filters.campus);
                        }

                        if (data.department_stats) {
                            data.department_stats = data.department_stats.filter(d => allowedDepartments.includes(d.name));
                        }

                        // 2. Filter by Department (Dropdown)
                        if (this.filters.department !== 'all') {
                            // Filter the stats list
                            data.department_stats = data.department_stats.filter(d => d.name === this.filters.department);
                            
                            // Filter students by department
                            allStudents = allStudents.filter(s => s.college === this.filters.department);

                            // Filter applications by department
                            allApplications = allApplications.filter(a => a.college === this.filters.department);
                            
                            // Update Global Metrics based on the selected department
                            const selectedDeptStats = this.analyticsData.department_stats.find(d => d.name === this.filters.department);
                            
                            if (selectedDeptStats) {
                                data.total_students = selectedDeptStats.total_students;
                                data.total_applications = selectedDeptStats.total_applications;
                                data.approved_applications = selectedDeptStats.approved_applications;
                                data.pending_applications = selectedDeptStats.pending_applications || 0;
                                data.rejected_applications = selectedDeptStats.rejected_applications || 0;
                                data.students_with_applications = selectedDeptStats.total_applications; // Approximation
                                data.approval_rate = selectedDeptStats.approval_rate;
                            } else {
                                data.total_students = 0;
                                data.total_applications = 0;
                                data.approved_applications = 0;
                                data.pending_applications = 0;
                                data.rejected_applications = 0;
                                data.approval_rate = 0;
                            }
                        }

                        // 3. Filter by Time Period (if applicable)
                        if (this.filters.timePeriod !== 'all') {
                            const now = new Date();
                            allApplications = allApplications.filter(a => {
                                const date = new Date(a.created_at);
                                if (this.filters.timePeriod === 'this_month') {
                                    return date.getMonth() === now.getMonth() && date.getFullYear() === now.getFullYear();
                                } else if (this.filters.timePeriod === 'last_3_months') {
                                    const threeMonthsAgo = new Date();
                                    threeMonthsAgo.setMonth(now.getMonth() - 3);
                                    return date >= threeMonthsAgo;
                                } else if (this.filters.timePeriod === 'this_year') {
                                    return date.getFullYear() === now.getFullYear();
                                }
                                return true;
                            });
                        }

                        // Calculate Scholarship Stats (Scholars vs Non-Scholars)
                        const scholarshipStats = {};
                        allApplications.forEach(app => {
                            const name = app.scholarship_name || 'Unknown Scholarship';
                            if (!scholarshipStats[name]) {
                                scholarshipStats[name] = { scholars: 0, nonScholars: 0 };
                            }
                            
                            if (app.status === 'approved') {
                                scholarshipStats[name].scholars++;
                            } else {
                                scholarshipStats[name].nonScholars++;
                            }
                        });
                        data.scholarshipStats = scholarshipStats;

                        this.filteredData = data;
                    },

                    applyFilters() {
                        this.updateFilteredData();
                        this.updateCharts();
                    },
                    
                    getStatisticsHeader() {
                        if (this.filters.campus === 'all') {
                            return 'All Statistics';
                        }
                        const campus = this.campusOptions.find(c => c.id == this.filters.campus);
                        return (campus ? campus.name : 'All') + ' Statistics';
                    },

                    getCurrentFilterLabel() {
                        let label = 'All Data';
                        if (this.filters.campus !== 'all') {
                            const campus = this.campusOptions.find(c => c.id == this.filters.campus);
                            label = 'Campus: ' + (campus ? campus.name : this.filters.campus);
                        }
                        if (this.filters.department !== 'all') {
                            label += (this.filters.campus === 'all' ? ' | ' : ', ') + 'Dept: ' + this.filters.department;
                        }
                        return label;
                    },

                    createAllCharts() {
                        this.createDepartmentChart();
                        this.createScholarshipStatusChart();
                    },
                    
                    updateCharts() {
                        this.updateDepartmentChart();
                        this.updateScholarshipStatusChart();
                    },
                    
                    createDepartmentChart() {
                        const ctx = document.getElementById('sfaoDepartmentChart');
                        if (!ctx) return;
                        
                        if (charts.department) {
                            charts.department.destroy();
                        }

                        // Prepare data from filteredData.department_stats
                        const labels = (this.filteredData.department_stats || []).map(d => d.name);
                        const pending = (this.filteredData.department_stats || []).map(d => d.pending_applications || 0);
                        const approved = (this.filteredData.department_stats || []).map(d => d.approved_applications || 0);
                        const rejected = (this.filteredData.department_stats || []).map(d => d.rejected_applications || 0);
                        const notApplied = (this.filteredData.department_stats || []).map(d => (d.total_students || 0) - (d.total_applications || 0));

                        charts.department = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [
                                    {
                                        label: 'Not Applied',
                                        data: notApplied,
                                        backgroundColor: '#9CA3AF'
                                    },
                                    {
                                        label: 'Pending',
                                        data: pending,
                                        backgroundColor: '#F59E0B'
                                    },
                                    {
                                        label: 'Approved',
                                        data: approved,
                                        backgroundColor: '#10B981'
                                    },
                                    {
                                        label: 'Rejected',
                                        data: rejected,
                                        backgroundColor: '#EF4444'
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { 
                                        position: 'bottom',
                                        labels: { color: this.getTextColor() }
                                    },
                                    tooltip: {
                                        mode: 'index',
                                        intersect: false
                                    }
                                },
                                scales: {
                                    x: {
                                        ticks: { color: this.getTextColor() },
                                        grid: { color: this.getTextColor() === '#ffffff' ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)' }
                                    },
                                    y: {
                                        ticks: { color: this.getTextColor() },
                                        grid: { color: this.getTextColor() === '#ffffff' ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)' }
                                    }
                                }
                            }
                        });
                    },

                    updateDepartmentChart() {
                        if (charts.department) {
                            const labels = (this.filteredData.department_stats || []).map(d => d.name);
                            const pending = (this.filteredData.department_stats || []).map(d => d.pending_applications || 0);
                            const approved = (this.filteredData.department_stats || []).map(d => d.approved_applications || 0);
                            const rejected = (this.filteredData.department_stats || []).map(d => d.rejected_applications || 0);
                            const notApplied = (this.filteredData.department_stats || []).map(d => (d.total_students || 0) - (d.total_applications || 0));
                            
                            charts.department.data.labels = labels;
                            charts.department.data.datasets[0].data = notApplied;
                            charts.department.data.datasets[1].data = pending;
                            charts.department.data.datasets[2].data = approved;
                            charts.department.data.datasets[3].data = rejected;
                            charts.department.update();
                        }
                    },



                    createScholarshipStatusChart() {
                        const ctx = document.getElementById('sfaoScholarshipTypeChart');
                        if (!ctx) return;

                        if (charts.scholarshipType) {
                            charts.scholarshipType.destroy();
                        }

                        // Prepare data
                        const rawLabels = Object.keys(this.filteredData.scholarshipStats || {});
                        // Split labels into array of words for multi-line display
                        const labels = rawLabels.map(name => name.split(' '));
                        const scholars = rawLabels.map(name => this.filteredData.scholarshipStats[name].scholars);
                        const nonScholars = rawLabels.map(name => this.filteredData.scholarshipStats[name].nonScholars);

                        charts.scholarshipType = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [
                                    {
                                        label: 'Scholars',
                                        data: scholars,
                                        backgroundColor: '#10B981' // Green
                                    },
                                    {
                                        label: 'Non-Scholars',
                                        data: nonScholars,
                                        backgroundColor: '#EF4444' // Red
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: {
                                    mode: 'index',
                                    intersect: false,
                                },
                                plugins: {
                                    legend: { 
                                        position: 'bottom',
                                        labels: { color: this.getTextColor() }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            title: function(context) {
                                                // Join the array back to string for tooltip title
                                                return context[0].label.join(' ');
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        ticks: {
                                            color: this.getTextColor(),
                                            maxRotation: 0, // Prevent rotation to force wrapping
                                            autoSkip: false
                                        },
                                        grid: { color: this.getTextColor() === '#ffffff' ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)' }
                                    },
                                    y: {
                                        ticks: { color: this.getTextColor() },
                                        grid: { color: this.getTextColor() === '#ffffff' ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)' }
                                    }
                                }
                            }
                        });
                    },

                    updateScholarshipStatusChart() {
                        if (charts.scholarshipType) {
                            const rawLabels = Object.keys(this.filteredData.scholarshipStats || {});
                            const labels = rawLabels.map(name => name.split(' '));
                            const scholars = rawLabels.map(name => this.filteredData.scholarshipStats[name].scholars);
                            const nonScholars = rawLabels.map(name => this.filteredData.scholarshipStats[name].nonScholars);

                            charts.scholarshipType.data.labels = labels;
                            charts.scholarshipType.data.datasets[0].data = scholars;
                            charts.scholarshipType.data.datasets[1].data = nonScholars;
                            charts.scholarshipType.update();
                        }
                    },
                    
                    clearFilters() {
                        this.filters.campus = 'all';
                        this.filters.department = 'all';
                        this.filters.timePeriod = 'all';
                    },
                    
                    refreshAnalytics() {
                        window.location.reload();
                    }
                };
            });
        });
    </script>
</div>
