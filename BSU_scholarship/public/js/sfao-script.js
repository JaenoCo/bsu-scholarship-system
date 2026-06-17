/**
 * SFAO Dashboard Specific Scripts
 */

// Fix Safari Back Cache Bug
window.addEventListener("pageshow", function (event) {
    if (event.persisted) {
        window.location.reload();
    }
});

// SFAO Statistics Tab Component
window.sfaoStatisticsTab = function (config = {}) {
    // Store chart instances globally or in a scoped tracking object (non-reactive)
    const chartInstances = {
        college: null,
        gender: null,
        scholarshipType: null,
        comparison: null,
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
            rejected: true,
            pending: true,
            inProgress: true,
            newScholars: true,
            oldScholars: true,
            applicants: true,
            scholars: true
        },
        chartStatus: {
            college: true,
            trend: true,
            comparison: true
        },
        resizeObserver: null,
        chartDebounceTimer: null,
        availablePrograms: [],
        filters: {
            campus: 'all',
            search: '',
            timePeriod: 'all'
        },
        subTab: 'scholarships',
        openDropdowns: {},
        searchDebounceTimer: null,

        // Search & Autocomplete State
        searchResults: [],
        showSearchResults: false,
        selectedScholarshipName: '',
        studentDetails: {
            open: false,
            title: '',
            rows: []
        },

        init() {
            try {


                // Fallback for DOM parsing if config not provided (Backwards Compatibility)
                if (Object.keys(this.analyticsData).length === 0) {
                    const dataEl = document.getElementById('sfao-analytics-data');
                    if (dataEl) {
                        try {
                            this.analyticsData = JSON.parse(dataEl.dataset.analytics || '{}');
                        } catch (e) { console.error('Error parsing inline analytics data', e); }
                    }
                }
                if (this.campusOptions.length === 0 && window.sfaoCampusOptions) {
                    this.campusOptions = window.sfaoCampusOptions;
                }

                // Initial Filters
                // Initial Filters
                if (this.campusOptions.length === 1) {
                    this.filters.campus = this.campusOptions[0].id;
                } else {
                    const storedCampus = localStorage.getItem('sfaoStatsCampus');
                    this.filters.campus = (storedCampus && storedCampus !== 'null' && storedCampus !== 'undefined') ? storedCampus : 'all';
                }

                // Persist ViewMode (Student Type)
                const savedViewMode = localStorage.getItem('sfao_view_mode');
                if (savedViewMode && ['applicants', 'scholars'].includes(savedViewMode)) {
                    this.viewMode = savedViewMode;
                }
                this.$watch('viewMode', (val) => {
                    localStorage.setItem('sfao_view_mode', val);
                    this.applyFilters();
                });

                // Auto-update search with debounce to avoid excessive filtering
                this.$watch('filters.search', (val) => {
                    clearTimeout(this.searchDebounceTimer);
                    this.searchDebounceTimer = setTimeout(() => {
                        this.applyFilters();
                    }, 300); // 300ms debounce
                });

                this.availableColleges = this.analyticsData.all_colleges || [];

                // Initialize logic
                // Initialize logic (Deferred ensures reactivity)
                this.$nextTick(() => {
                    this.updateCollegesList(this.filters.campus);
                    this.updateProgramList();
                    this.updateTrackList();
                });

                // DEFERRED RESTORATION:
                // Move the restoration logic here, at the END of init, and wrap in nextTick.
                // This ensures all data (available_scholarships) is ready and no other logic overrides it.
                // Reactivity - Register Synchronously
                this.$watch('filters.campus', (value) => {
                    this.updateCollegesList(value);
                    this.updateProgramList();
                    this.updateTrackList();
                    this.applyFilters();
                });

                // Auto-update on all filter changes
                this.$watch('filters.timePeriod', () => this.applyFilters());
                this.$watch('viewMode', () => this.applyFilters()); // Watch Student Type (Applicants/Scholars)

                // Watch Local Filters
                this.$watch('localFilters.college', (val) => {
                    this.updateProgramList();
                    this.updateTrackList();
                    this.applyFilters();
                });
                this.$watch('localFilters.program', () => {
                    this.updateTrackList();
                    this.applyFilters();
                });

                // Watch Chart Legend
                this.$watch('chartLegend', () => {
                    this.createComparisonChart(); // Optimized update
                }, { deep: true });

                this.$watch('localFilters.track', () => {
                    this.applyFilters();
                });

                // Detect SubTab from URL on Init
                const params = new URLSearchParams(window.location.search);
                const currentTab = params.get('tabs');
                if (currentTab === 'analytics_scholarships') {
                    this.subTab = 'scholarships';
                    this.viewMode = 'comparison';
                } else if (currentTab === 'analytics_applicants') {
                    this.subTab = 'applicants';
                    this.viewMode = 'applicants';
                } else if (currentTab === 'analytics_scholars') {
                    this.subTab = 'scholars';
                    this.viewMode = 'scholars';
                }

                // Initial Data Load triggering
                this.$nextTick(() => {
                    this.applyFilters();
                });

                // Force initial load with delay to ensure DOM/Canvas is ready
                setTimeout(() => {
                    this.applyFilters();
                }, 300);

                // Listen for sidebar campus selection (Global Event)
                window.addEventListener('set-stats-filter', (e) => {
                    this.filters.campus = e.detail;
                });

                // Listen for sidebar sub-tab selection (Global Event)
                window.addEventListener('set-analytics-subtab', (e) => {
                    this.setSubTab(e.detail);
                });

                // Watch for dark mode changes to update chart colors
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        if (mutation.attributeName === 'class') {
                            this.createAllCharts();
                        }
                    });
                });
                observer.observe(document.documentElement, { attributes: true });
            } catch (error) {
                console.error('CRITICAL ERROR in SFAO Statistics Tab init:', error);
            }
        },

        setSubTab(tab) {
            this.subTab = tab;
            if (tab === 'applicants') {
                this.viewMode = 'applicants';
            } else if (tab === 'scholars') {
                this.viewMode = 'scholars';
            } else {
                this.viewMode = 'comparison';
            }
            // Force chart update
            this.$nextTick(() => {
                this.updateCharts();
                window.dispatchEvent(new Event('resize'));
            });
        },

        handleTabChange(newTab) {
            // Always try to render charts if switching to analytics
            if (newTab === 'analytics' || newTab.startsWith('analytics_')) {

                if (newTab === 'analytics_scholarships') this.setSubTab('scholarships');
                else if (newTab === 'analytics_applications') this.setSubTab('applicants');
                else if (newTab === 'analytics_scholars') this.setSubTab('scholars');

                // Force a resize event to trigger observers
                window.dispatchEvent(new Event('resize'));

                // Redundant call removed - createdAllCharts is handled by setSubTab->updateCharts
                // or will be triggered by observer.
                // If we need to force it, we can call it, but let's rely on setSubTab if it was called.
                if (!newTab.startsWith('analytics_')) {
                    // If just switching to 'analytics' main tab without subtab change impling call
                    this.$nextTick(() => {
                        this.updateCharts();
                    });
                }
            }
        },

        getTextColor() {
            return document.documentElement.classList.contains('dark') ? '#ffffff' : '#374151';
        },



        updateCollegesList(campusId) {
            if (campusId === 'all') {
                // Return all colleges directly
                this.availableColleges = this.analyticsData.all_colleges || [];
            } else {
                // Colleges for specific campus
                const campusShortNames = (this.analyticsData.campus_colleges || {})[campusId] || [];
                this.availableColleges = (this.analyticsData.all_colleges || []).filter(d => campusShortNames.includes(d.short_name));
            }

            // Reset college selection if invalid
            if (this.localFilters.college !== 'all' && !this.availableColleges.find(d => d.short_name === this.localFilters.college)) {
                this.localFilters.college = 'all';
            }
        },

        updateProgramList() {
            const campus = this.filters.campus;
            const college = this.localFilters.college;
            // Use strict map (CampusID -> CollegeName -> [Programs])
            const strictMap = this.analyticsData.campus_college_programs || {};

            const allPrograms = new Set();

            // 1. Identify Target Campuses
            let targetCampuses = [];
            if (campus === 'all') {
                targetCampuses = Object.keys(strictMap);
            } else {
                targetCampuses = [String(campus)];
            }

            // 2. Iterate Campus and Collect Programs
            targetCampuses.forEach(cId => {
                const campusData = strictMap[cId] || {};

                // Identify Target Colleges within this Campus
                let targetColleges = [];
                if (college === 'all') {
                    targetColleges = Object.keys(campusData);
                } else {
                    // Only specific college if it exists in this campus
                    if (campusData[college]) targetColleges = [college];
                }

                // Collect
                targetColleges.forEach(colName => {
                    const progs = campusData[colName] || [];
                    progs.forEach(p => allPrograms.add(p));
                });
            });

            this.availablePrograms = Array.from(allPrograms).sort();

            // ONLY reset if the current selection is no longer valid
            if (this.localFilters.program !== 'all' && !this.availablePrograms.includes(this.localFilters.program)) {
                this.localFilters.program = 'all';
            }
        },

        updateTrackList() {
            const campus = this.filters.campus;
            const college = this.localFilters.college;
            const program = this.localFilters.program;
            const tracksMap = this.analyticsData.program_tracks || {};
            // Use strict map (CampusID -> CollegeName -> [Programs])
            const strictMap = this.analyticsData.campus_college_programs || {};

            if (program === 'all') {
                const allTracks = new Set();

                // 1. Identify Target Campuses
                let targetCampuses = [];
                if (campus === 'all') {
                    targetCampuses = Object.keys(strictMap);
                } else {
                    targetCampuses = [String(campus)];
                }

                // 2. Iterate Campuses and Collect Tracks
                targetCampuses.forEach(cId => {
                    const campusData = strictMap[cId] || {};

                    // Identify Target Colleges within this Campus
                    let targetColleges = [];
                    if (college === 'all') {
                        targetColleges = Object.keys(campusData);
                    } else {
                        // Only specific college if it exists in this campus
                        if (campusData[college]) targetColleges = [college];
                    }

                    // Collect Tracks from strictly identified programs
                    targetColleges.forEach(colName => {
                        const progs = campusData[colName] || [];
                        progs.forEach(p => {
                            const pTracks = tracksMap[p] || [];
                            pTracks.forEach(t => allTracks.add(t));
                        });
                    });
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
                const month = date.getMonth(); // 0-11
                // AY Logic: If Month >= 7 (August), AY = Year-(Year+1). Else (Year-1)-Year.
                // Adjusted: Typically AY starts Aug/Sept. Let's use Aug (Index 7).
                const startYear = month >= 7 ? year : year - 1;
                years.add(`${startYear}-${startYear + 1}`);
            });
            // Also add current year if empty
            if (years.size === 0) {
                const now = new Date();
                const curY = now.getMonth() >= 7 ? now.getFullYear() : now.getFullYear() - 1;
                years.add(`${curY}-${curY + 1}`);
            }
            this.academicYearOptions = Array.from(years).sort().reverse();
            // Default to 'all' or possibly the latest AY? Keeping 'all' as default.
        },

        updateFilteredData() {
            let data = JSON.parse(JSON.stringify(this.analyticsData)); // Deep copy
            let allStudents = this.analyticsData.all_students_data || [];
            let allApplications = this.analyticsData.all_applications_data || this.analyticsData.applications || [];

            // 1. Filter College Stats based on Campus (Sidebar)
            let allowedColleges = [];
            if (this.filters.campus === 'all') {
                const allCols = Object.values(this.analyticsData.campus_colleges || {}).flat();
                allowedColleges = [...new Set(allCols)];
            } else {
                allowedColleges = (this.analyticsData.campus_colleges || {})[this.filters.campus] || [];
                // Filter students by campus
                allStudents = allStudents.filter(s => s.campus_id == this.filters.campus);
                // Filter applications by campus
                allApplications = allApplications.filter(a => a.campus_id == this.filters.campus);
            }

            if (data.college_stats) {
                data.college_stats = data.college_stats.filter(d => allowedColleges.includes(d.name));
            }

            // 2. Filter by Scholarship (Search) - Strict Exact Match Logic
            if (this.filters.search && this.filters.search.trim() !== '') {
                const term = this.filters.search.toLowerCase().trim();
                allApplications = allApplications.filter(item =>
                    (item.scholarship_name || '').toLowerCase() === term
                );
            }

            // 3. Filter by Time Period (Academic Year)
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

            // 4. Apply Local Filters (Moved from createCollegeChart)
            // Filter by College
            if (this.localFilters.college !== 'all') {
                allApplications = allApplications.filter(item => item.college === this.localFilters.college);
            }
            // Filter by Program
            if (this.localFilters.program !== 'all') {
                allApplications = allApplications.filter(item => item.program === this.localFilters.program);
            }
            // Filter by Track
            if (this.localFilters.track !== 'all') {
                allApplications = allApplications.filter(item => item.track === this.localFilters.track);
            }

            allApplications.forEach(app => {
                // Determine if Scholar or Applicant
                const isScholarVal = Number(app.is_global_scholar) || (app.scholar_id ? 1 : 0);
                const isScholar = isScholarVal > 0;
                const isApplicant = !isScholar; // Logic: If not a global scholar, treat as applicant

                // Filter by View Mode
                if (this.viewMode === 'scholars' && !isScholar) return;
                if (this.viewMode === 'applicants' && isScholar) return;
                // 'comparison' mode allows BOTH.

                // Status Validity Check for Applicants
                // Scholars are implicitly valid (approved). Applicants need valid status.
                if (isApplicant && !['pending', 'approved', 'rejected', 'in_progress'].includes(app.status)) return;
            });

            // Skipping explicit Scholarship Stats object creation for now as we use groupedData in chart function, 
            // but if we need a table listing later, we can re-add it. 
            // data.all_applications_data is enough for calculating charts.

            data.all_applications_data = allApplications;

            // Calculate Dynamic Summary Counts based on FILTERED data
            const summaryCounts = {
                total: 0,
                approved: 0,
                rejected: 0,
                approvalRate: 0,
                inProgress: 0,
                pending: 0,
                active: 0,
                applicantsCount: 0,
                scholarsCount: 0
            };

            allApplications.forEach(app => {
                const isScholarVal = Number(app.is_global_scholar) || (app.scholar_id ? 1 : 0);
                const isScholar = isScholarVal > 0;
                const isApplicant = !isScholar;

                // View Mode Filtering for Counts
                if (this.viewMode === 'scholars' && !isScholar) return;
                if (this.viewMode === 'applicants' && isScholar) return;

                if (isApplicant && !['pending', 'approved', 'rejected', 'in_progress'].includes(app.status)) return;

                // Aggregating Counts
                summaryCounts.total++;

                if (this.viewMode === 'comparison') {
                    if (isScholar) summaryCounts.scholarsCount++;
                    else summaryCounts.applicantsCount++;
                } else {
                    // Standard Counts
                    if (app.status === 'approved') summaryCounts.approved++;
                    if (app.status === 'rejected') summaryCounts.rejected++;
                    if (app.status === 'in_progress') summaryCounts.inProgress++;
                    if (app.status === 'pending') summaryCounts.pending++;
                }
            });

            // Calculate "Active"
            summaryCounts.active = summaryCounts.total - summaryCounts.approved - summaryCounts.rejected;

            if (summaryCounts.total > 0 && this.viewMode === 'applicants') {
                summaryCounts.approvalRate = ((summaryCounts.approved / summaryCounts.total) * 100).toFixed(1);
            } else {
                summaryCounts.approvalRate = '0.0';
            }

            // Assign to data.counts
            data.counts = summaryCounts;

            // ANIMATION TRIGGER:
            // Update with full data after a micro-delay to allow DOM to register changes
            requestAnimationFrame(() => {
                setTimeout(() => {
                    this.filteredData = data;
                    // Trigger Chart Updates strictly AFTER data is set
                    this.$nextTick(() => {
                        this.updateCharts();
                    });
                }, 50);
            });
        },

        applyFilters() {
            this.updateFilteredData();
            // Charts update is now chained inside updateFilteredData to handle async delay
        },

        getStatisticsHeader() {
            if (this.filters.campus === 'all' || !this.campusOptions.length) return 'All Statistics';
            const campus = this.campusOptions.find(c => c.id == this.filters.campus);
            return (campus ? campus.name : 'All') + ' Statistics';
        },

        getCurrentFilterLabel() {
            let label = 'All Data';
            if (this.filters.campus !== 'all') {
                const campus = this.campusOptions.find(c => c.id == this.filters.campus);
                label = 'Campus: ' + (campus ? campus.name : this.filters.campus);
            }
            if (this.localFilters.college !== 'all') {
                label += (this.filters.campus === 'all' ? ' | ' : ', ') + 'College: ' + this.localFilters.college;
            }
            return label;
        },

        getChartTitle() {
            if (this.filters.campus === 'all') {
                return 'Scholarship Status (Campus Comparison)';
            }
            // Find campus name
            const campus = this.campusOptions.find(c => c.id == this.filters.campus);
            const name = campus ? campus.name : 'College Comparison';
            return `Scholarship Status (${name} - Colleges)`;
        },

        getComparisonChartTitle() {
            if (this.filters.search && this.filters.search.trim() !== '') {
                // Format: Scholarship Status Distribution (Search Term)
                const term = this.selectedScholarshipName || this.filters.search;
                return `Scholarship Status Distribution (${term})`;
            }
            return 'Scholarship Status Distribution (All)';
        },

        handleSearchInput() {
            const term = this.filters.search.toLowerCase().trim();
            if (term === '') {
                this.searchResults = [];
                this.showSearchResults = false;
                this.selectedScholarshipName = '';
                return;
            }

            this.showSearchResults = true;
            const available = this.analyticsData.available_scholarships || [];
            this.searchResults = available.filter(s =>
                (s.scholarship_name || '').toLowerCase().startsWith(term)
            ).slice(0, 10); // Limit to 10 suggestions, StartsWith logic
        },

        selectSearchResult(name) {
            this.filters.search = name;
            this.selectedScholarshipName = name;
            this.showSearchResults = false;
            this.applyFilters();
        },

        performSearch() {
            this.showSearchResults = false;
            this.selectedScholarshipName = this.filters.search;
            this.applyFilters();
        },

        openStudentDetails(scope, type) {
            const rows = this.getStudentDetailRows(scope, type);
            this.studentDetails = {
                open: true,
                title: this.getStudentDetailsTitle(scope, type),
                rows
            };
        },

        closeStudentDetails() {
            this.studentDetails.open = false;
        },

        getStudentDetailRows(scope, type) {
            const source = this.filteredData.all_applications_data || [];
            const validApplicantStatuses = ['pending', 'approved', 'rejected', 'in_progress'];

            return source
                .filter(item => {
                    const isScholar = this.isScholarRecord(item);
                    const isApplicant = !isScholar;
                    const hasValidApplicantStatus = validApplicantStatuses.includes(item.status);

                    if (scope === 'comparison') {
                        if (type === 'applicants') return isApplicant && hasValidApplicantStatus;
                        if (type === 'scholars') return isScholar;
                        return isScholar || (isApplicant && hasValidApplicantStatus);
                    }

                    if (this.viewMode === 'scholars' && !isScholar) return false;
                    if (this.viewMode === 'applicants' && (!isApplicant || !hasValidApplicantStatus)) return false;

                    if (type === 'approved' || type === 'approvalRate') return item.status === 'approved';
                    if (type === 'rejected') return item.status === 'rejected';
                    if (type === 'active') return ['pending', 'in_progress'].includes(item.status);
                    return true;
                })
                .map((item, index) => this.formatStudentDetailRow(item, index))
                .sort((a, b) => a.studentNumber.localeCompare(b.studentNumber));
        },

        isScholarRecord(item) {
            return (Number(item.is_global_scholar) || (item.scholar_id ? 1 : 0)) > 0;
        },

        formatStudentDetailRow(item, index) {
            const nameParts = [item.first_name, item.middle_name, item.last_name]
                .filter(Boolean)
                .map(part => String(part).trim())
                .filter(Boolean);
            const campus = this.campusOptions.find(c => String(c.id) === String(item.campus_id));

            return {
                key: `${item.user_id || 'student'}-${item.scholarship_name || 'scholarship'}-${item.status || 'status'}-${index}`,
                studentNumber: item.sr_code || item.student_number || item.student_no || `ID ${item.user_id || 'N/A'}`,
                name: item.student_name || nameParts.join(' ') || 'Unnamed student',
                campus: campus ? campus.name : (item.campus_name || item.campus || 'Unassigned campus'),
                college: item.college || 'Unassigned college',
                program: item.program || 'Unassigned program',
                scholarship: item.scholarship_name || 'Unassigned scholarship',
                status: this.formatStatusLabel(item.status, this.isScholarRecord(item))
            };
        },

        formatStatusLabel(status, isScholar) {
            if (isScholar && !status) return 'Scholar';
            const labels = {
                approved: 'Approved',
                rejected: 'Rejected',
                pending: 'Pending',
                in_progress: 'In Progress'
            };
            return labels[status] || (isScholar ? 'Scholar' : 'Unknown');
        },

        getStudentDetailsTitle(scope, type) {
            const context = scope === 'comparison' ? this.getComparisonChartTitle() : this.getChartTitle();
            const labels = {
                total: 'Total',
                applicants: 'Applicants',
                scholars: 'Scholars',
                approved: 'Approved',
                rejected: 'Rejected',
                active: 'Pending/In Progress',
                approvalRate: 'Approval Rate - Approved'
            };

            return `${labels[type] || 'Students'}: ${context}`;
        },

        createAllCharts(retryCount = 0) {
            // Debounce Implementation
            clearTimeout(this.chartDebounceTimer);
            this.chartDebounceTimer = setTimeout(() => {
                this._executeCreateAllCharts(retryCount);
            }, 50); // Small debounce delay
        },

        _executeCreateAllCharts(retryCount = 0) {

            if (typeof Chart === 'undefined') {
                if (retryCount > 20) {
                    console.warn('Chart.js failed to load after multiple retries.');
                    return;
                }
                // console.error('Chart.js not loaded yet. Retrying in 500ms...');
                setTimeout(() => this.createAllCharts(retryCount + 1), 500);
                return;
            }

            // Determine primary canvas based on active subTab
            let primaryCanvasId = 'sfaoCollegeChart';
            if (this.subTab === 'scholarships') {
                primaryCanvasId = 'sfaoComparisonChart';
            }

            const ctx = document.getElementById(primaryCanvasId);
            if (!ctx) {
                if (retryCount > 10) return; // Stop silently if element doesn't exist
                // console.error(`Canvas ${primaryCanvasId} not found in DOM`);
                setTimeout(() => this.createAllCharts(retryCount + 1), 500);
                return;
            }

            // CHECK if canvas is visible or has dimensions
            if (ctx.clientWidth === 0 || ctx.clientHeight === 0) {
                if (retryCount > 10) {
                    console.warn(`Canvas ${primaryCanvasId} found but remains hidden (0 dimensions). Stopping retries.`);
                    return;
                }
                setTimeout(() => this.createAllCharts(retryCount + 1), 200);
                return;
            }

            // Auto-resize observer to handle x-show transitions
            const container = ctx.parentElement;

            // Disconnect existing observer to prevent double-firing
            if (this.resizeObserver) {
                this.resizeObserver.disconnect();
            }

            this.resizeObserver = new ResizeObserver(() => {
                // Throttle checking to prevent rapid firing
                if (this.subTab === 'scholarships') {
                    if (chartInstances.comparison && ctx.getBoundingClientRect().width > 0) {
                        chartInstances.comparison.resize();
                    } else if (!chartInstances.comparison && ctx.getBoundingClientRect().width > 0) {
                        this.createComparisonChart();
                    }
                } else {
                    if (chartInstances.college && ctx.getBoundingClientRect().width > 0) {
                        chartInstances.college.resize();
                    } else if (!chartInstances.college && ctx.getBoundingClientRect().width > 0) {
                        this.createCollegeChart();
                    }
                }
            });
            this.resizeObserver.observe(container);

            if (this.subTab === 'scholarships') {
                this.createComparisonChart();
            } else {
                this.createCollegeChart();
                this.createTrendChart();
                // this.createGenderChart(); // HTML based now
                this.createScholarshipStatusChart();
            }
        },

        updateCharts() {
            this.createCollegeChart();
            this.createComparisonChart();
            this.createTrendChart();
            this.createScholarshipStatusChart();
        },

        createCollegeChart() {
            // "Scholarship College" Chart

            const ctx = document.getElementById('sfaoCollegeChart');
            if (!ctx) return;

            if (chartInstances.college) chartInstances.college.destroy();

            // Start with Globally filtered applications (Campus, Global Scholarship, Time)
            let rawData = this.filteredData.all_applications_data || [];

            // Apply Local Filters
            // Apply Local Filters

            // NOTE: Local Filters are now applied in updateFilteredData, so rawData is ALREADY filtered by Dept/Program.
            // We keep the logging but remove the redundant filter to prevent double-filtering (though harmless here, it's cleaner).



            // Group by Department OR Campus based on View
            const groupedData = {};

            // MAP Campus IDs to Names
            const campusMap = {};
            this.campusOptions.forEach(c => {
                if (c.id !== 'all') campusMap[c.id] = c.name;
            });

            // Logic:
            // If filters.campus === 'all', we want to compare CAMPUSES (e.g. Alangilan vs Pablo Borbon)
            // If filters.campus !== 'all', we want to compare COLLEGES (e.g. CICS vs CAS) within that campus.

            const isComparisonMode = (this.filters.campus === 'all');

            // Initialize Groups to ensure 0-data bars appear if desired (Optional, maybe skip for cleaner chart)
            // For now, let's just populate from data found to avoid empty bars clutter.

            rawData.forEach(item => {
                // CRITICAL: Filter out Scholars if in Applicants Mode
                const isGlobalScholar = Number(item.is_global_scholar);
                if (this.viewMode === 'applicants' && isGlobalScholar > 0) return;

                // Determine Group Key
                let groupKey = 'Unknown';
                if (isComparisonMode) {
                    groupKey = campusMap[item.campus_id] || 'Other';
                } else {
                    groupKey = item.college || 'No College';
                }

                if (!groupedData[groupKey]) {
                    groupedData[groupKey] = {
                        pending: new Set(),
                        approved: new Set(),
                        rejected: new Set(),
                        newScholars: new Set(),
                        oldScholars: new Set(),
                        inProgress: new Set()
                    };
                }

                if (this.viewMode === 'applicants') {
                    if (item.status === 'pending') groupedData[groupKey].pending.add(item.user_id);
                    else if (item.status === 'approved') groupedData[groupKey].approved.add(item.user_id);
                    else if (item.status === 'rejected') groupedData[groupKey].rejected.add(item.user_id);
                    else if (item.status === 'in_progress') groupedData[groupKey].inProgress.add(item.user_id);
                } else {
                    // Scholars Mode
                    if (item.status === 'approved' && item.scholar_id) {
                        if (item.scholar_type === 'new') groupedData[groupKey].newScholars.add(item.user_id);
                        else groupedData[groupKey].oldScholars.add(item.user_id);
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
            const pendingData = labels.map(l => groupedData[l].pending.size);
            const approvedData = labels.map(l => groupedData[l].approved.size);
            const rejectedData = labels.map(l => groupedData[l].rejected.size);
            const inProgressData = labels.map(l => groupedData[l].inProgress.size);
            const newScholarsData = labels.map(l => groupedData[l].newScholars.size);
            const oldScholarsData = labels.map(l => groupedData[l].oldScholars.size);
            // nonScholars no longer used in Scholars mode but logic preserved in object structure for safety

            // Process labels for multi-line display to avoid diagonal rotation
            const processedLabels = labels.map(label => {
                const words = label.split(' ');
                const lines = [];
                let currentLine = [];

                words.forEach(word => {
                    // Start new line if current line has >= 2 words OR line length exceeds 15 chars
                    if (currentLine.length >= 2 || (currentLine.join(' ').length + word.length > 15)) {
                        lines.push(currentLine.join(' '));
                        currentLine = [];
                    }
                    currentLine.push(word);
                });
                if (currentLine.length > 0) lines.push(currentLine.join(' '));

                return lines;
            });

            // Update Chart Status (Force Reactivity)
            // CRITICAL: Ensure we actually have data points > 0
            const hasData = labels.some(l => {
                let count = 0;
                if (groupedData[l]) {
                    count += groupedData[l].pending.size + groupedData[l].approved.size + groupedData[l].rejected.size + groupedData[l].inProgress.size + groupedData[l].newScholars.size + groupedData[l].oldScholars.size;
                }
                return count > 0;
            });

            this.chartStatus = { ...this.chartStatus, college: labels.length > 0 && hasData };
            this.chartStatus = { ...this.chartStatus, college: labels.length > 0 && hasData };

            // If no data, destroy chart and return
            if (labels.length === 0 || !hasData) {
                if (chartInstances.college) {
                    chartInstances.college.destroy();
                    chartInstances.college = null;
                }
                return;
            }

            let datasets = [];
            if (this.viewMode === 'applicants') {
                datasets = [
                    {
                        label: 'Approved',
                        data: approvedData,
                        backgroundColor: '#7F1D1D', // Darkest Red (900)
                        hidden: !this.chartLegend.approved
                    },
                    {
                        label: 'Rejected',
                        data: rejectedData,
                        backgroundColor: '#991B1B', // Darker Red (800)
                        hidden: !this.chartLegend.rejected
                    },
                    {
                        label: 'Pending',
                        data: pendingData,
                        backgroundColor: '#B91C1C', // Dark Red (700)
                        hidden: !this.chartLegend.pending
                    },
                    {
                        label: 'In Progress',
                        data: inProgressData,
                        backgroundColor: '#DC2626', // Red (600)
                        hidden: !this.chartLegend.inProgress
                    }
                ];
            } else {
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

            chartInstances.college = new Chart(ctx, {
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
                                autoSkip: false, // Ensure all labels are shown
                                maxRotation: 0,  // Force horizontal
                                minRotation: 0   // Force horizontal
                            },
                            stacked: isComparisonMode // Stacked only for Campus Comparison
                        },
                        y: {
                            ticks: { color: this.getTextColor(), beginAtZero: true, precision: 0 },
                            stacked: isComparisonMode // Stacked only for Campus Comparison
                        }
                    },
                    plugins: {
                        legend: { display: false }, // Use custom legend
                        tooltip: {
                            callbacks: {
                                title: function (context) {
                                    const label = context[0].label;
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

            // 1. Get Base Data (Respecting Campus/Department/Program/Time)
            // Note: We use the existing filteredData because it already respects Global Filters (Campus, Year, etc.)
            // But checking the previous implementation, filteredData might already account for local filters too?
            // Let's check updateFilteredData() logic. It usually applies ALL filters.
            // However, for this specific chart, we WANT to respect the "Scholarship Status" container's LOCAL scholarship filter.
            // AND we want to respect the Global Campus Filter (Overview vs Specific).

            // To be safe and precise, let's start from 'analyticsData.all_applications_data' and re-apply filters manually
            // This ensures we have full control over which "Scholarship" filter to use.

            let rawData = this.analyticsData.all_applications_data || [];


            // A. Global Campus Filter
            if (this.filters.campus !== 'all') {
                rawData = rawData.filter(a => a.campus_id == this.filters.campus);
            }

            // B. Global Academic Year (Time Period)
            if (this.filters.timePeriod !== 'all') {
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

            // C. Local Scholarship Filter
            if (this.filters.scholarship && this.filters.scholarship !== 'all') {
                const selectedScholarship = (this.analyticsData.available_scholarships || []).find(s => String(s.id) === String(this.filters.scholarship));
                if (selectedScholarship) {
                    rawData = rawData.filter(a => a.scholarship_name === selectedScholarship.scholarship_name);
                }
            }



            // D. Department & Program (Global/Local context - typically ignored for Scholarship Status unless explicitly set?)
            // The requirement says "Overview ... total of all campus". It implies standard global filters apply.
            if (this.localFilters.college !== 'all') {
                rawData = rawData.filter(item => item.college === this.localFilters.college);
            }
            if (this.localFilters.program !== 'all') {
                rawData = rawData.filter(item => item.program === this.localFilters.program);
            }


            // Group by Time Unit (Month) given the filtered dataset
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
                    groupedData[key] = {
                        pending: 0,
                        approved: 0,
                        rejected: 0,
                        in_progress: 0,
                        new: 0,
                        old: 0
                    };
                }

                // Count by Status
                if (this.viewMode === 'applicants') {
                    if (item.status === 'pending') groupedData[key].pending++;
                    else if (item.status === 'approved') groupedData[key].approved++;
                    else if (item.status === 'rejected') groupedData[key].rejected++;
                    else if (item.status === 'in_progress') groupedData[key].in_progress++;
                } else {
                    // For scholars, we separate by New vs Old (based on scholar_type if available or just count total approved)
                    // Reverting to Status-based separation for Scholars might mean "New" vs "Old" lines.
                    // The user asked for "Approved, Rejected, Pending, In Progress" lines for application trend.
                    // If viewMode is 'scholars', these statuses don't apply similarly (all are Approved).
                    // So for Scholars, we keep "New" vs "Old".
                    if (item.status === 'approved' && item.scholar_id) {
                        if (item.scholar_type === 'new') groupedData[key].new++;
                        else groupedData[key].old++;
                    }
                }
            });

            // Sort Time Labels
            const sortedLabels = Array.from(timeLabels).map(l => JSON.parse(l)).sort((a, b) => a.key.localeCompare(b.key));
            const chartLabels = sortedLabels.map(l => l.label);
            const timeKeys = sortedLabels.map(l => l.key);

            // Update Chart Status
            this.chartStatus = { ...this.chartStatus, trend: timeKeys.length > 0 };

            if (timeKeys.length === 0) {
                if (chartInstances.trend) {
                    chartInstances.trend.destroy();
                    chartInstances.trend = null;
                }
                return;
            }

            // Define Datasets based on View Mode

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
                        borderColor: '#7F1D1D', // Darkest Red
                        backgroundColor: shouldFill ? 'rgba(127, 29, 29, 0.2)' : '#7F1D1D',
                        fill: shouldFill ? 'origin' : false,
                        tension: 0.3,
                        hidden: !this.chartLegend.approved
                    },
                    {
                        label: 'Rejected',
                        data: timeKeys.map(k => groupedData[k].rejected),
                        borderColor: '#991B1B', // Darker Red
                        backgroundColor: shouldFill ? 'rgba(153, 27, 27, 0.2)' : '#991B1B',
                        fill: shouldFill ? 'origin' : false,
                        tension: 0.3,
                        hidden: !this.chartLegend.rejected
                    },
                    {
                        label: 'Pending',
                        data: timeKeys.map(k => groupedData[k].pending),
                        borderColor: '#B91C1C', // Dark Red
                        backgroundColor: shouldFill ? 'rgba(185, 28, 28, 0.2)' : '#B91C1C',
                        fill: shouldFill ? 'origin' : false,
                        tension: 0.3,
                        hidden: !this.chartLegend.pending
                    },
                    {
                        label: 'In Progress',
                        data: timeKeys.map(k => groupedData[k].in_progress),
                        borderColor: '#DC2626', // Red
                        backgroundColor: shouldFill ? 'rgba(220, 38, 38, 0.2)' : '#DC2626',
                        fill: shouldFill ? 'origin' : false,
                        tension: 0.3,
                        hidden: !this.chartLegend.inProgress
                    }
                ];
            } else {
                // Scholars View
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
                            ticks: {
                                precision: 0,
                                color: this.getTextColor()
                            }
                        },
                        x: {
                            ticks: {
                                color: this.getTextColor()
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false // We use the custom legend buttons above
                        },
                        tooltip: { mode: 'index', intersect: false }
                    }
                }
            });
        },

        updateTrendChart() {
            this.createTrendChart();
        },

        createComparisonChart() {
            const ctx = document.getElementById('sfaoComparisonChart');
            if (!ctx) return;
            if (chartInstances.comparison) chartInstances.comparison.destroy();

            // Source: All applications filtered by Campus/Dept/Time but NOT Scholarship
            let rawData = this.analyticsData.all_applications_data || this.analyticsData.applications || [];

            // Safety check for filter
            if (this.filters.campus === undefined) this.filters.campus = 'all';

            // Apply Campus/Time filters manually.
            // INTENTIONALLY SKIPPING Department and Program filters to keep this separate.

            // 1. Campus
            if (this.filters.campus !== 'all') {
                rawData = rawData.filter(a => a.campus_id == this.filters.campus);
            }
            // 2. Time (Academic Year)
            if (this.filters.timePeriod !== 'all') {
                const now = new Date();
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

            // 3. Dept
            if (this.localFilters.college !== 'all') {
                rawData = rawData.filter(item => item.college === this.localFilters.college);
            }
            // 4. Program
            if (this.localFilters.program !== 'all') {
                rawData = rawData.filter(item => item.program === this.localFilters.program);
            }

            // 5. Search (Exact Match for Chart)
            if (this.filters.search && this.filters.search.trim() !== '') {
                const term = this.filters.search.toLowerCase().trim();
                rawData = rawData.filter(item =>
                    (item.scholarship_name || '').toLowerCase() === term
                );
            }

            // Determine Mode Early
            const isSearchActive = (this.filters.search && this.filters.search.trim() !== '');

            // Group by Scholarship Name (Counting Unique Applicants per Status)
            const groupedData = {};
            rawData.forEach(item => {
                const isScholarVal = Number(item.is_global_scholar) || (item.scholar_id ? 1 : 0);
                const isScholar = isScholarVal > 0;


                // Removed ViewMode restrictions to allow full data capture for Comparison/Search toggles

                let name = 'Unknown';
                if (isSearchActive) {
                    name = item.scholarship_name || 'Unknown';
                } else {
                    // Dynamic Drill-Down: Campus > College > Program
                    if (this.filters.campus === 'all') {
                        // Resolve Campus Name from ID
                        if (item.campus_name) {
                            name = item.campus_name;
                        } else if (item.campus_id) {
                            const cObj = this.campusOptions.find(c => c.id == item.campus_id);
                            name = cObj ? cObj.name : ('Campus ' + item.campus_id);
                        } else {
                            name = item.campus || 'Other Campus';
                        }
                    } else if (this.localFilters.college === 'all') {
                        name = item.college || 'Unassigned College';
                    } else {
                        name = item.program || 'Unassigned Program';
                    }
                }
                if (!groupedData[name]) {
                    groupedData[name] = {
                        pending: new Set(),
                        approved: new Set(),
                        rejected: new Set(),
                        newScholars: new Set(),
                        oldScholars: new Set(),
                        inProgress: new Set(),
                        applicantsSet: new Set(),
                        scholarsSet: new Set()
                    };
                }

                // Always populate High-Level Sets
                if (isScholar) groupedData[name].scholarsSet.add(item.user_id);
                else groupedData[name].applicantsSet.add(item.user_id);

                // Always populate Detailed Status Sets
                if (item.status === 'pending') groupedData[name].pending.add(item.user_id);
                else if (item.status === 'approved') groupedData[name].approved.add(item.user_id);
                else if (item.status === 'rejected') groupedData[name].rejected.add(item.user_id);
                else if (item.status === 'in_progress') groupedData[name].inProgress.add(item.user_id);

                // Always populate Scholar Types
                if (isScholar) {
                    if (item.scholar_type === 'new') groupedData[name].newScholars.add(item.user_id);
                    else groupedData[name].oldScholars.add(item.user_id);
                }
            });

            // Filter labels based on VISIBLE data (legend state)
            const allLabels = Object.keys(groupedData).sort();

            const labels = allLabels.filter(name => {
                // Search Mode: Simplified Visibility
                if (isSearchActive) {
                    return this.chartLegend.applicants || this.chartLegend.scholars;
                }

                // Comparison Mode Logic
                const data = groupedData[name];
                let visibleCount = 0;


                if (this.subTab === 'scholarships') {
                    if (this.chartLegend.applicants) visibleCount += data.applicantsSet.size;
                    if (this.chartLegend.scholars) visibleCount += data.scholarsSet.size;
                } else if (this.viewMode === 'applicants') {
                    if (this.chartLegend.approved) visibleCount += data.approved.size;
                    if (this.chartLegend.pending) visibleCount += data.pending.size;
                    if (this.chartLegend.rejected) visibleCount += data.rejected.size;
                    if (this.chartLegend.inProgress) visibleCount += data.inProgress.size;
                } else if (this.viewMode === 'scholars') {
                    if (this.chartLegend.newScholars) visibleCount += data.newScholars.size;
                    if (this.chartLegend.oldScholars) visibleCount += data.oldScholars.size;
                } else {
                    // Fallback Comparison Mode
                    if (this.chartLegend.applicants) visibleCount += data.applicantsSet.size;
                    if (this.chartLegend.scholars) visibleCount += data.scholarsSet.size;
                }
                return visibleCount > 0;
            });

            // Process labels for multi-line display to avoid diagonal rotation
            let processedLabels = labels.map(label => {
                const words = label.split(' ');
                const lines = [];
                let currentLine = [];

                words.forEach(word => {
                    // Start new line if current line has >= 2 words OR line length exceeds 15 chars
                    if (currentLine.length >= 2 || (currentLine.join(' ').length + word.length > 15)) {
                        lines.push(currentLine.join(' '));
                        currentLine = [];
                    }
                    currentLine.push(word);
                });
                if (currentLine.length > 0) lines.push(currentLine.join(' '));
                return lines;
            });

            // Update Chart Status (Force Reactivity)
            this.chartStatus = { ...this.chartStatus, comparison: labels.length > 0 };

            // If no data, destroy chart and return
            if (labels.length === 0) {
                if (chartInstances.comparison) {
                    chartInstances.comparison.destroy();
                    chartInstances.comparison = null;
                }
                return;
            }



            // Build Datasets
            let datasets = [];

            if (isSearchActive) {
                // SEARCH MODE: Separate Bars for Applicants and Scholars (Two Categories on X-Axis)

                // Labels Override
                processedLabels = ['Applicants', 'Scholars'];

                const targetName = labels[0]; // Assuming single scholarship match
                if (targetName && groupedData[targetName]) {
                    const stats = groupedData[targetName];
                    const d = (isApp, val) => isApp ? [val, 0] : [0, val];

                    datasets = [
                        // Stack: Applicants
                        { label: 'Approved', data: d(true, stats.approved.size), backgroundColor: '#7F1D1D', stack: 'main' },
                        { label: 'Rejected', data: d(true, stats.rejected.size), backgroundColor: '#991B1B', stack: 'main' },
                        { label: 'Pending', data: d(true, stats.pending.size), backgroundColor: '#B91C1C', stack: 'main' },
                        { label: 'In Progress', data: d(true, stats.inProgress.size), backgroundColor: '#DC2626', stack: 'main' },

                        // Stack: Scholars
                        { label: 'New Scholars', data: d(false, stats.newScholars.size), backgroundColor: '#EF4444', stack: 'main' },
                        { label: 'Old Scholars', data: d(false, stats.oldScholars.size), backgroundColor: '#F87171', stack: 'main' }
                    ];
                } else {
                    datasets = [];
                }

            } else {
                // DEFAULT MODE
                const applicantsData = labels.map(l => groupedData[l].applicantsSet.size);
                const scholarsData = labels.map(l => groupedData[l].scholarsSet.size);
                datasets = [
                    { label: 'Applicants', data: applicantsData, backgroundColor: '#991B1B', stack: 'total' },
                    { label: 'Scholars', data: scholarsData, backgroundColor: '#EF4444', stack: 'total' }
                ];
            }


            chartInstances.comparison = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: processedLabels, // Scholarship Names (Split into lines)
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
                            stacked: true // Always stacked
                        },
                        y: {
                            ticks: { color: this.getTextColor(), beginAtZero: true, precision: 0 },
                            stacked: true // Always stacked
                        }
                    },
                    plugins: {
                        legend: { display: isSearchActive }, // Show default legend for Search mode (Status), hide for custom legend
                        tooltip: {
                            enabled: true,
                            callbacks: {
                                title: function (context) {
                                    const label = context[0].label;
                                    return Array.isArray(label) ? label.join(' ') : label;
                                }
                            }

                        }
                    }
                }
            });
        },

        updateComparisonChart() {
            this.createComparisonChart();
        },

        updateDepartmentChart() {
            // this.createDepartmentChart(); 
            // Note: createDepartmentChart might not exist or be named createCollegeChart?
            this.createCollegeChart();
        },



        createScholarshipStatusChart() {
            const ctx = document.getElementById('sfaoScholarshipTypeChart');
            if (!ctx) return;
            if (chartInstances.scholarshipType) chartInstances.scholarshipType.destroy();

            const rawLabels = Object.keys(this.filteredData.scholarshipStats || {});
            const labels = rawLabels.map(name => name.split(' '));
            const scholars = rawLabels.map(name => this.filteredData.scholarshipStats[name].scholars);
            const nonScholars = rawLabels.map(name => this.filteredData.scholarshipStats[name].nonScholars);

            chartInstances.scholarshipType = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        { label: 'Scholars', data: scholars, backgroundColor: '#10B981' },
                        { label: 'Non-Scholars', data: nonScholars, backgroundColor: '#EF4444' }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { color: this.getTextColor() } } },
                    scales: { x: { ticks: { color: this.getTextColor(), autoSkip: false } }, y: { ticks: { color: this.getTextColor() } } }
                }
            });
        },

        updateScholarshipStatusChart() {
            if (chartInstances.scholarshipType) {
                chartInstances.scholarshipType.destroy(); // Recreate to be safe with label changes
                this.createScholarshipStatusChart();
            }
        },

        clearFilters() {
            this.filters.campus = 'all';
            this.filters.department = 'all';
            this.filters.timePeriod = 'all';
        },



        handleSearchInput() {
            const term = (this.filters.search || '').toLowerCase().trim();
            if (!term) {
                this.showSearchResults = false;
                this.searchResults = [];
                this.applyFilters();
                return;
            }

            const currentCampus = this.filters.campus;
            let sourceNames = new Set();

            // Sourcing Strategy: Prioritize Definition Lists over Application History to avoid phantom/archived data

            // 1. Try 'campus_scholarships' (Grouped map if available)
            if (this.analyticsData.campus_scholarships) {
                let list = [];
                if (currentCampus === 'all') {
                    list = Object.values(this.analyticsData.campus_scholarships).flat();
                } else {
                    list = this.analyticsData.campus_scholarships[currentCampus] || [];
                }
                list.forEach(item => sourceNames.add(item.name || item.scholarship_name));
            }
            // 2. Try 'all_scholarships' (Canonical list)
            else if (this.analyticsData.all_scholarships && this.analyticsData.all_scholarships.length > 0) {
                this.analyticsData.all_scholarships.forEach(s => {
                    // Check campus association (assuming s.campus_id exists)
                    if (currentCampus === 'all' || String(s.campus_id) === String(currentCampus)) {
                        sourceNames.add(s.name || s.scholarship_name);
                    }
                });
            }
            // 3. Fallback: 'all_applications_data' (History - may include extra items)
            else {
                const history = this.analyticsData.all_applications_data || [];
                history.forEach(d => {
                    if (currentCampus === 'all' || String(d.campus_id) === String(currentCampus) || d.campus_name === currentCampus) {
                        if (d.scholarship_name) sourceNames.add(d.scholarship_name);
                    }
                });
            }

            // Filter by Term (Starts With)
            this.searchResults = Array.from(sourceNames)
                .filter(name => name && name.toLowerCase().startsWith(term))
                .sort()
                .map(name => ({ id: name, scholarship_name: name }));

            this.showSearchResults = true;
            this.applyFilters();
        },

        selectSearchResult(name) {
            this.filters.search = name;
            this.showSearchResults = false;
            this.applyFilters();
        },

        performSearch() {
            this.showSearchResults = false;
            this.applyFilters();
        },

        clearLocalFilters() {
            this.localFilters.status = 'all';
            this.localFilters.department = 'all';
            this.localFilters.program = 'all';
            this.availablePrograms = [];
        }
    };
};

// SFAO Dashboard State (Main Layout)
window.sfaoDashboardState = function (config) {
    return {
        tab: 'analytics',
        statsCampus: localStorage.getItem('sfaoStatsCampus') || config.defaultStatsCampus,
        campusList: config.campusList || [],
        openDropdowns: { dashboard: false, scholarships: false, applicants: false, scholars: false, reports: false, applicationForms: false, settings: false },

        urlMapping: {
            'analytics_scholarships': 'analytics_scholarships',
            'analytics_applications': 'analytics_applications',
            'analytics_scholars': 'analytics_scholars',
            'overview': 'analytics',
            'all_scholarships': 'scholarships',
            'private_scholarships': 'scholarships-private',
            'government_scholarships': 'scholarships-government',
            'all_applicants': 'applicants',
            'applicants_not_applied': 'applicants-not_applied',
            'applicants_in_progress': 'applicants-in_progress',
            'applicants_pending': 'applicants-pending',
            'applicants_approved': 'applicants-approved',
            'applicants_rejected': 'applicants-rejected',
            'all_scholars': 'scholars',
            'new_scholars': 'scholars-new',
            'old_scholars': 'scholars-old',
            'reports_applicant_summary': 'reports-applicant_summary',
            'reports_scholar_summary': 'reports-scholar_summary',
            'reports_grant_summary': 'reports-grant_summary',
            'all-app-forms': 'all-app-forms',
            'up-app-form': 'up-app-form',
            'import-scholarships': 'import-scholarships',
            'account_settings': 'account',
            'account-info': 'account-info',
            'account-security': 'account-security'
        },

        init() {
            // Watch Stats Campus Change
            this.$watch('statsCampus', val => {
                localStorage.setItem('sfaoStatsCampus', val);
                this.updateUrl(this.tab);
            });

            // Restore Dropdowns
            const savedDropdowns = localStorage.getItem(`sfaoDropdowns_${config.userId}`);
            if (savedDropdowns) {
                try {
                    this.openDropdowns = JSON.parse(savedDropdowns);
                } catch (e) { console.error('Error parsing dropdown state', e); }
            }

            // Watch Dropdowns
            this.$watch('openDropdowns', val => {
                localStorage.setItem(`sfaoDropdowns_${config.userId}`, JSON.stringify(val));
            });

            // Watch Tab Change
            this.$watch('tab', val => {
                if (val === 'account_settings') {
                    this.tab = 'account';
                    return; // Let the next watch trigger handle the rest
                }
                localStorage.setItem('sfaoTab', val);
                this.updateUrl(val);
                this.syncDropdowns(val);
                this.$dispatch('tab-changed', val);
            });

            // Listen for Sidebar Tab Switch Events
            window.addEventListener('switch-tab', event => {
                this.switchTab(event.detail);
            });

            // Listen for Sidebar Stats Filter Events
            window.addEventListener('set-stats-filter', event => {
                this.statsCampus = event.detail;
            });

            // Initialize from URL or LocalStorage
            const urlParams = new URLSearchParams(window.location.search);
            const urlTab = urlParams.get('tabs');

            // Check if URL tab matches a campus slug
            let matchedCampus = null;
            if (this.campusList && this.campusList.length > 0) {
                matchedCampus = this.campusList.find(c => c.slug === urlTab);
            }

            if (urlTab && this.urlMapping[urlTab]) {
                this.tab = this.urlMapping[urlTab];
            } else if (matchedCampus) {
                this.tab = 'analytics';
                this.statsCampus = matchedCampus.id;
            } else {
                // Fallback favoring Analytics ("Overview") if no specific tab
                let savedTab = localStorage.getItem('sfaoTab');
                // Ensure valid tab
                this.tab = savedTab || config.activeTab || 'analytics_scholarships';

                // Cleanup legacy values
                if (this.tab === 'statistics' || this.tab === 'dashboard') this.tab = 'analytics';
                if (this.tab === 'account_settings') this.tab = 'account';
            }

            // Ensure correct dropdown is open
            if (!savedDropdowns) {
                this.syncDropdowns(this.tab);
            }

            this.updateUrl(this.tab);
        },

        switchTab(nextTab) {
            const clickedCurrentTab = this.tab === nextTab;

            this.tab = nextTab;

            if (clickedCurrentTab) {
                localStorage.setItem('sfaoTab', nextTab);
                this.updateUrl(nextTab);
                this.syncDropdowns(nextTab);
                this.$dispatch('tab-changed', nextTab);
                window.dispatchEvent(new CustomEvent('sfao-filter-tab-selected', { detail: nextTab }));
            }
        },

        updateUrl(currentTab) {
            let key = null;

            if (currentTab === 'analytics') {
                if (this.statsCampus === 'all' || !this.statsCampus) {
                    key = 'overview';
                } else {
                    const campus = this.campusList.find(c => c.id == this.statsCampus);
                    key = campus ? campus.slug : 'overview';
                }
            } else if (currentTab.startsWith('analytics_')) {
                key = currentTab;
            } else {
                key = Object.keys(this.urlMapping).find(k => this.urlMapping[k] === currentTab);
            }

            if (key) {
                const url = new URL(window.location);
                url.searchParams.set('tabs', key);
                url.searchParams.delete('tab');
                window.history.pushState({}, '', url);
            }
        },

        syncDropdowns(currentTab) {
            if (currentTab === 'analytics' || currentTab.startsWith('analytics_')) this.openDropdowns.dashboard = true;
            else if (currentTab.startsWith('scholarships')) this.openDropdowns.scholarships = true;
            else if (currentTab.startsWith('applicants')) this.openDropdowns.applicants = true;
            else if (currentTab.startsWith('scholars')) this.openDropdowns.scholars = true;
            else if (currentTab.startsWith('reports')) this.openDropdowns.reports = true;
            else if (currentTab === 'all-app-forms' || currentTab === 'up-app-form') this.openDropdowns.applicationForms = true;
            else if (currentTab.startsWith('account')) this.openDropdowns.settings = true;
        }
    };
};

// SFAO Scholarships Filter
window.sfaoScholarshipsFilter = function (config) {
    const routeUrl = typeof config === 'string' ? config : config.routeUrl;
    const campusOptions = config.campusOptions || [];

    return {
        filters: {
            sort_by: localStorage.getItem('sfaoScholarshipsSortBy') || 'name',
            sort_order: localStorage.getItem('sfaoScholarshipsSortOrder') || 'asc',
            type: localStorage.getItem('sfaoScholarshipsType') || 'all',
            type: localStorage.getItem('sfaoScholarshipsType') || 'all',
            campus: localStorage.getItem('sfaoScholarshipsCampus') || 'all',
            search: localStorage.getItem('sfaoScholarshipsSearch') || ''
        },
        campusOptions: campusOptions,

        init() {
            this.$watch('filters.sort_by', (value) => {
                localStorage.setItem('sfaoScholarshipsSortBy', value);
                this.fetchScholarships();
            });
            this.$watch('filters.sort_order', (value) => {
                localStorage.setItem('sfaoScholarshipsSortOrder', value);
                this.fetchScholarships();
            });
            this.$watch('filters.type', (value) => {
                localStorage.setItem('sfaoScholarshipsType', value);
                this.fetchScholarships();
            });
            this.$watch('filters.campus', (value) => {
                localStorage.setItem('sfaoScholarshipsCampus', value);
                this.fetchScholarships();
            });
            this.$watch('filters.search', (value) => {
                localStorage.setItem('sfaoScholarshipsSearch', value);
                this.fetchScholarships();
            });

            this.updatePaginationLinks();

            window.addEventListener('sfao-filter-tab-selected', event => {
                if (event.detail && event.detail.startsWith('scholarships')) {
                    this.handleTabChange(event.detail);
                }
            });

            if (this.filters.type !== 'all') {
                this.fetchScholarships();
            }
        },

        fetchScholarships(page = 1) {
            const params = new URLSearchParams({
                tab: 'scholarships',
                sort_by: this.filters.sort_by,
                sort_order: this.filters.sort_order,
                type_filter: this.filters.type,
                campus_filter: this.filters.campus,
                search_query: this.filters.search,
                page_scholarships: page
            });

            fetch(`${routeUrl}?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('scholarships-list-container').innerHTML = data.html;
                    this.updatePaginationLinks();
                })
                .catch(error => console.error('Error fetching scholarships:', error));
        },

        updatePaginationLinks() {
            const container = document.getElementById('scholarships-list-container');
            if (!container) return;
            const links = container.querySelectorAll('a.page-link');
            links.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const url = new URL(link.href);

                    let page = 1;
                    // Dynamically detect page parameter (page_all, page_private, page_scholarships, etc.)
                    for (const key of url.searchParams.keys()) {
                        if (key.startsWith('page')) {
                            page = url.searchParams.get(key);
                            break;
                        }
                    }

                    this.fetchScholarships(page);
                });
            });
        },

        resetFilters() {
            this.filters.sort_by = 'name';
            this.filters.sort_order = 'asc';
            this.filters.type = 'all';
            this.filters.campus = 'all';
        },

        getHeaderTitle() {
            if (this.filters.type === 'private') {
                return 'Private';
            } else if (this.filters.type === 'government') {
                return 'Government';
            }
            return 'All';
        },

        getHeaderDescription() {
            if (this.filters.type === 'private') {
                return 'Private scholarship programs';
            } else if (this.filters.type === 'government') {
                return 'Government scholarship programs';
            }
            return 'View all available scholarship programs';
        },

        handleTabChange(tab) {
            if (tab === 'scholarships') {
                if (this.filters.type !== 'all') {
                    this.filters.type = 'all';
                }
            } else if (tab.startsWith('scholarships-')) {
                const type = tab.replace('scholarships-', '');
                if (this.filters.type !== type) {
                    this.filters.type = type;
                }
            }
        }
    };
};

// SFAO Applicants Filter
window.sfaoApplicantsFilter = function (config) {
    return {
        filters: {
            sort_by: localStorage.getItem('sfaoApplicantsSortBy') || 'name',
            sort_order: localStorage.getItem('sfaoApplicantsSortOrder') || 'asc',
            campus: localStorage.getItem('sfaoApplicantsCampus') || 'all',
            college: localStorage.getItem('sfaoApplicantsCollege') || 'all',
            program: localStorage.getItem('sfaoApplicantsProgram') || 'all',
            track: localStorage.getItem('sfaoApplicantsTrack') || 'all',
            academic_year: localStorage.getItem('sfaoApplicantsAcademicYear') || 'all',
            scholarship: localStorage.getItem('sfaoApplicantsScholarship') || 'all',
            status: 'all'
        },
        counts: config.counts || {},
        campusOptions: config.campusOptions || [],
        colleges: config.colleges || [],
        programs: config.programs || [],
        tracks: config.tracks || [],
        academicYears: config.academicYears || [],
        campusCollegePrograms: config.campusCollegePrograms || {},
        programTracks: config.programTracks || {},
        sfaoCampusName: config.sfaoCampusName || '',
        extensionCampuses: config.extensionCampuses || [],
        currentTab: 'applicants',
        showModal: false,
        selectedApplicant: null,
        loading: false,
        // Status Modal State
        showStatusModal: false,
        statusModalLoading: false,
        statusModalApplicants: [],
        statusModalTitle: 'All',
        statusModalCurrentStatus: 'all',

        init() {
            this.$watch('filters.sort_by', (value) => {
                localStorage.setItem('sfaoApplicantsSortBy', value);
                this.fetchApplicants();
            });
            this.$watch('filters.sort_order', (value) => {
                localStorage.setItem('sfaoApplicantsSortOrder', value);
                this.fetchApplicants();
            });
            this.$watch('filters.campus', (value) => {
                localStorage.setItem('sfaoApplicantsCampus', value);
                this.updateColleges();
                this.fetchApplicants();
            });
            this.$watch('filters.college', (value) => {
                localStorage.setItem('sfaoApplicantsCollege', value);
                this.updatePrograms();
                this.fetchApplicants();
            });
            this.$watch('filters.program', (value) => {
                localStorage.setItem('sfaoApplicantsProgram', value);
                this.updateTracks();
                this.fetchApplicants();
            });
            this.$watch('filters.track', (value) => {
                localStorage.setItem('sfaoApplicantsTrack', value);
                this.fetchApplicants();
            });
            this.$watch('filters.academic_year', (value) => {
                localStorage.setItem('sfaoApplicantsAcademicYear', value);
                this.fetchApplicants();
            });
            this.$watch('filters.scholarship', (value) => {
                localStorage.setItem('sfaoApplicantsScholarship', value);
                this.fetchApplicants();
            });
            this.$watch('filters.status', (value) => {
                this.fetchApplicants();
            });

            this.updatePaginationLinks();

            document.addEventListener('open-applicant-modal', (e) => {
                this.openModal(e.detail);
            });

            window.addEventListener('sfao-filter-tab-selected', event => {
                if (event.detail && event.detail.startsWith('applicants')) {
                    this.handleTabChange(event.detail);
                }
            });

            // Initial Sync if values present
            if (this.filters.campus !== 'all') this.updateColleges(false);
            if (this.filters.college !== 'all') this.updatePrograms(false);
            if (this.filters.program !== 'all') this.updateTracks(false);
        },

        updateColleges(reset = true) {
            if (reset) {
                this.filters.college = 'all';
                this.filters.program = 'all';
                this.filters.track = 'all';
            }

            if (this.filters.campus === 'all') {
                // Collect all colleges from all campuses
                let allCols = new Set();
                Object.values(this.campusCollegePrograms).forEach(campusCols => {
                    Object.keys(campusCols).forEach(c => allCols.add(c));
                });
                this.colleges = Array.from(allCols).sort().map(c => ({ name: c, value: c }));
            } else {
                const campusCols = this.campusCollegePrograms[this.filters.campus] || {};
                this.colleges = Object.keys(campusCols).sort().map(c => ({ name: c, value: c }));
            }
            this.updatePrograms(reset);
        },

        updatePrograms(reset = true) {
            if (reset) {
                this.filters.program = 'all';
                this.filters.track = 'all';
            }

            let availablePrograms = new Set();

            if (this.filters.college === 'all') {
                // Context: Current Campus(es)
                const campuses = (this.filters.campus === 'all')
                    ? Object.values(this.campusCollegePrograms)
                    : [this.campusCollegePrograms[this.filters.campus] || {}];

                campuses.forEach(cols => {
                    Object.values(cols).forEach(progs => {
                        if (Array.isArray(progs)) progs.forEach(p => availablePrograms.add(p));
                    });
                });
            } else {
                // Context: Current Campus -> Selected College
                const campuses = (this.filters.campus === 'all')
                    ? Object.values(this.campusCollegePrograms)
                    : [this.campusCollegePrograms[this.filters.campus] || {}];

                campuses.forEach(cols => {
                    const progs = cols[this.filters.college];
                    if (progs) progs.forEach(p => availablePrograms.add(p));
                });
            }

            this.programs = Array.from(availablePrograms).sort();
            this.updateTracks(reset);
        },

        updateTracks(reset = true) {
            if (reset) {
                this.filters.track = 'all';
            }

            if (this.filters.program === 'all') {
                this.tracks = []; // Or all tracks? Usually logic suggests clearing.
                // Or collect all tracks?
                // For simplified UX, let's keep empty or fetch all if needed.
                // Analytics logic usually keeps it specific.
            } else {
                this.tracks = this.programTracks[this.filters.program] || [];
            }
        },

        openModal(applicant) {
            this.selectedApplicant = applicant;
            this.showModal = true;
            document.body.style.overflow = 'hidden';
        },

        closeModal() {
            this.showModal = false;
            this.selectedApplicant = null;
            document.body.style.overflow = '';
        },

        openStatusModal(status) {
            this.handleTabChange(status === 'all' ? 'applicants' : `applicants-${status}`);

            this.statusModalCurrentStatus = status;
            this.showStatusModal = true;
            document.body.style.overflow = 'hidden';
            
            // Set title based on status
            const statusTitles = {
                'all': 'All Applicants',
                'approved': 'Approved Applicants',
                'rejected': 'Rejected Applicants',
                'pending': 'Pending Applications',
                'in_progress': 'In Progress Applications',
                'not_applied': 'Students Not Applied'
            };
            this.statusModalTitle = statusTitles[status] || 'Applicants';
            
            // Fetch applicants for this status
            this.fetchStatusApplicants(status);
        },

        closeStatusModal() {
            this.showStatusModal = false;
            this.statusModalApplicants = [];
            document.body.style.overflow = '';
        },

        fetchStatusApplicants(status) {
            this.statusModalLoading = true;
            const params = new URLSearchParams({
                tab: 'applicants',
                sort_by: this.filters.sort_by,
                sort_order: this.filters.sort_order,
                campus_filter: this.filters.campus,
                college_filter: this.filters.college,
                program_filter: this.filters.program,
                track_filter: this.filters.track,
                academic_year_filter: this.filters.academic_year,
                scholarship_filter: this.filters.scholarship,
                status_filter: status,
                page_applicants: 1,
                fetch_modal_data: true
            });

            fetch(`${config.routeUrl}?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.applicants) {
                        this.statusModalApplicants = data.applicants;
                    }
                    setTimeout(() => { this.statusModalLoading = false; }, 300);
                })
                .catch(error => {
                    console.error('Error fetching status applicants:', error);
                    this.statusModalLoading = false;
                });
        },

        formatDate(dateString) {
            if (!dateString) return 'N/A';
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString(undefined, options);
        },

        fetchApplicants(page = 1) {
            // Guard: Only fetch if we are actually on an applicants tab
            if (!this.currentTab || !this.currentTab.startsWith('applicants')) return;

            this.loading = true;
            const params = new URLSearchParams({
                tab: this.currentTab,
                sort_by: this.filters.sort_by,
                sort_order: this.filters.sort_order,
                campus_filter: this.filters.campus,
                college_filter: this.filters.college,
                program_filter: this.filters.program,
                track_filter: this.filters.track,
                academic_year_filter: this.filters.academic_year,
                scholarship_filter: this.filters.scholarship,
                status_filter: this.filters.status,
                page_applicants: page
            });

            fetch(`${config.routeUrl}?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.html) {
                        const container = document.getElementById('applicants-list-container');
                        if (container) container.innerHTML = data.html;
                    }
                    if (data.counts) {
                        this.counts = data.counts;
                    }
                    this.updatePaginationLinks();
                    // Small delay to ensure smooth transition perception
                    setTimeout(() => { this.loading = false; }, 300);
                })
                .catch(error => {
                    console.error('Error fetching applicants:', error);
                    this.loading = false;
                });
        },

        updatePaginationLinks() {
            const container = document.getElementById('applicants-list-container');
            if (!container) return;

            const links = container.querySelectorAll('a.page-link');
            links.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const url = new URL(link.href);
                    const page = url.searchParams.get('page_applicants') || 1;
                    this.fetchApplicants(page);
                });
            });
        },

        resetFilters() {
            this.filters.sort_by = 'name';
            this.filters.sort_order = 'asc';
            this.filters.campus = 'all';
            this.filters.college = 'all';
            this.filters.program = 'all';
            this.filters.track = 'all';
            this.filters.academic_year = 'all';
            this.filters.status = 'all';
        },

        getHeaderTitle() {
            let title = 'All Applicants';
            let campusName = 'All';

            if (this.filters.campus !== 'all') {
                const campus = this.campusOptions.find(c => c.id == this.filters.campus);
                if (campus) campusName = campus.name;
            }

            if (this.currentTab === 'applicants') {
                title = campusName === 'All' ? 'All Applicants' : `${campusName} Applicants`;
            } else if (this.currentTab === 'applicants-not_applied') {
                title = campusName === 'All' ? 'Not Applied Students' : `${campusName} - Not Applied`;
            } else if (this.currentTab.startsWith('applicants-')) {
                const status = this.currentTab.replace('applicants-', '').replace('_', ' ');
                const statusLabel = status.charAt(0).toUpperCase() + status.slice(1);
                title = campusName === 'All' ? statusLabel : `${campusName} - ${statusLabel}`;
            }

            return title;
        },

        getHeaderDescription() {
            let desc = '';
            let campusName = this.sfaoCampusName;

            if (this.currentTab === 'applicants') {
                desc = `All students with applications from ${campusName} `;
            } else if (this.currentTab === 'applicants-not_applied') {
                desc = `Students who have not submitted any applications`;
            } else {
                const status = this.currentTab.replace('applicants-', '').replace('_', ' ');
                desc = `Students with ${status} applications from ${campusName} `;
            }

            if (this.extensionCampuses.length > 0) {
                desc += ` and its extensions`;
            }
            return desc;
        },

        handleTabChange(tab) {
            this.currentTab = tab;

            if (tab === 'applicants') {
                this.filters.status = 'all';
            } else if (tab.startsWith('applicants-')) {
                this.filters.status = tab.replace('applicants-', '');
            }

            this.fetchApplicants();
        }
    };
};

// SFAO Scholars Filter
window.sfaoScholarsFilter = function (config) {
    return {
        filters: {
            sort_by: localStorage.getItem('sfaoScholarsSortBy') || 'created_at',
            sort_order: localStorage.getItem('sfaoScholarsSortOrder') || 'desc',
            campus: localStorage.getItem('sfaoScholarsCampus') || 'all',
            college: localStorage.getItem('sfaoScholarsCollege') || 'all',
            program: localStorage.getItem('sfaoScholarsProgram') || 'all',
            track: localStorage.getItem('sfaoScholarsTrack') || 'all',
            academic_year: localStorage.getItem('sfaoScholarsAcademicYear') || 'all',
            scholarship: localStorage.getItem('sfaoScholarsScholarship') || 'all',
            type: localStorage.getItem('sfaoScholarsType') || 'all'
        },
        counts: config.counts || {},
        campusOptions: config.campusOptions || [],
        colleges: config.colleges || [],
        programs: config.programs || [],
        tracks: config.tracks || [],
        academicYears: config.academicYears || [],
        campusCollegePrograms: config.campusCollegePrograms || {},
        programTracks: config.programTracks || {},
        sfaoCampusName: config.sfaoCampusName || '',
        extensionCampuses: config.extensionCampuses || [],
        selectedScholars: [],
        selectAll: false,
        showMarkAsModal: false,
        selectedScholarId: null,
        selectedScholarName: '',
        loading: false,

        init() {
            this.$watch('filters.sort_by', (value) => {
                localStorage.setItem('sfaoScholarsSortBy', value);
                this.fetchScholars();
            });
            this.$watch('filters.sort_order', (value) => {
                localStorage.setItem('sfaoScholarsSortOrder', value);
                this.fetchScholars();
            });
            this.$watch('filters.campus', (value) => {
                localStorage.setItem('sfaoScholarsCampus', value);
                this.updateColleges();
                this.fetchScholars();
            });
            this.$watch('filters.college', (value) => {
                localStorage.setItem('sfaoScholarsCollege', value);
                this.updatePrograms();
                this.fetchScholars();
            });
            this.$watch('filters.program', (value) => {
                localStorage.setItem('sfaoScholarsProgram', value);
                this.updateTracks();
                this.fetchScholars();
            });
            this.$watch('filters.track', (value) => {
                localStorage.setItem('sfaoScholarsTrack', value);
                this.fetchScholars();
            });
            this.$watch('filters.academic_year', (value) => {
                localStorage.setItem('sfaoScholarsAcademicYear', value);
                this.fetchScholars();
            });
            this.$watch('filters.scholarship', (value) => {
                localStorage.setItem('sfaoScholarsScholarship', value);
                this.fetchScholars();
            });
            this.$watch('filters.type', (value) => {
                localStorage.setItem('sfaoScholarsType', value);
                this.fetchScholars();
            });

            // Watcher to handle scroll lock when Mark As Modal is closed via any method (backdrop, esc, button)
            this.$watch('showMarkAsModal', (value) => {
                if (!value) {
                    document.body.style.overflow = '';
                }
            });

            // Initial Sync
            if (this.filters.campus !== 'all') this.updateColleges(false);
            if (this.filters.college !== 'all') this.updatePrograms(false);
            if (this.filters.program !== 'all') this.updateTracks(false);

            window.addEventListener('sfao-filter-tab-selected', event => {
                if (event.detail && event.detail.startsWith('scholars')) {
                    this.handleTabChange(event.detail);
                }
            });

            // Initial fetch if filters are active (or always fetch? usually Dashboard loads data initially via blade)
            // But if filters exist in localStorage, we might need to fetch if default view is unfiltered.
            // Dashboard Controller usually loads default.
            // Let's safe-check: if filters are NOT default, fetch.
            // Actually, keep original logic.
            if (Object.values(this.filters).some(v => v !== 'all' && v !== 'created_at' && v !== 'desc')) {
                this.fetchScholars();
            }
        },

        fetchScholars(page = 1) {
            this.loading = true;
            const params = new URLSearchParams({
                tab: 'scholars',
                scholars_sort_by: this.filters.sort_by,
                scholars_sort_order: this.filters.sort_order,
                campus_filter: this.filters.campus,
                college_filter: this.filters.college,
                program_filter: this.filters.program,
                track_filter: this.filters.track,
                academic_year_filter: this.filters.academic_year,
                scholarship_filter: this.filters.scholarship,
                type_filter: this.filters.type,
                page_scholarships: page
            });

            fetch(`${config.routeUrl}?${params.toString()} `, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('scholars-list-container');
                    if (container) container.innerHTML = data.html;
                    if (data.counts) this.counts = data.counts;
                    this.updatePaginationLinks();
                    setTimeout(() => { this.loading = false; }, 300);
                })
                .catch(error => {
                    console.error('Error fetching scholars:', error);
                    this.loading = false;
                });
        },

        updateColleges(reset = true) {
            if (reset) {
                this.filters.college = 'all';
                this.filters.program = 'all';
                this.filters.track = 'all';
            }

            if (this.filters.campus === 'all') {
                let allCols = new Set();
                Object.values(this.campusCollegePrograms).forEach(campusCols => {
                    Object.keys(campusCols).forEach(c => allCols.add(c));
                });
                this.colleges = Array.from(allCols).sort().map(c => ({ name: c, value: c }));
            } else {
                const campusCols = this.campusCollegePrograms[this.filters.campus] || {};
                this.colleges = Object.keys(campusCols).sort().map(c => ({ name: c, value: c }));
            }
            this.updatePrograms(reset);
        },

        updatePrograms(reset = true) {
            if (reset) {
                this.filters.program = 'all';
                this.filters.track = 'all';
            }

            let availablePrograms = new Set();

            if (this.filters.college === 'all') {
                const campuses = (this.filters.campus === 'all')
                    ? Object.values(this.campusCollegePrograms)
                    : [this.campusCollegePrograms[this.filters.campus] || {}];

                campuses.forEach(cols => {
                    Object.values(cols).forEach(progs => {
                        if (Array.isArray(progs)) progs.forEach(p => availablePrograms.add(p));
                    });
                });
            } else {
                const campuses = (this.filters.campus === 'all')
                    ? Object.values(this.campusCollegePrograms)
                    : [this.campusCollegePrograms[this.filters.campus] || {}];

                campuses.forEach(cols => {
                    const progs = cols[this.filters.college];
                    if (progs) progs.forEach(p => availablePrograms.add(p));
                });
            }

            this.programs = Array.from(availablePrograms).sort();
            this.updateTracks(reset);
        },

        updateTracks(reset = true) {
            if (reset) {
                this.filters.track = 'all';
            }

            if (this.filters.program === 'all') {
                this.tracks = [];
            } else {
                this.tracks = this.programTracks[this.filters.program] || [];
            }
        },

        updatePaginationLinks() {
            const container = document.getElementById('scholars-list-container');
            if (!container) return;

            const links = container.querySelectorAll('a.page-link');
            links.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const url = new URL(link.href);
                    const page = url.searchParams.get('page_scholarships') || 1;
                    this.fetchScholars(page);
                });
            });
        },

        resetFilters() {
            this.filters.sort_by = 'created_at';
            this.filters.sort_order = 'desc';
            this.filters.campus = 'all';
            this.filters.scholarship = 'all';
            this.filters.type = 'all';
        },

        toggleSelectAll() {
            if (this.selectAll) {
                // Get all eligible scholar IDs from the page
                const checkboxes = document.querySelectorAll('input[type="checkbox"][\\@change*="toggleScholar"]');
                this.selectedScholars = Array.from(checkboxes).map(cb => {
                    const match = cb.getAttribute('@change').match(/toggleScholar\((\d+)\)/);
                    return match ? parseInt(match[1]) : null;
                }).filter(id => id !== null);
            } else {
                this.selectedScholars = [];
            }
        },

        toggleScholar(scholarId) {
            const index = this.selectedScholars.indexOf(scholarId);
            if (index > -1) {
                this.selectedScholars.splice(index, 1);
            } else {
                this.selectedScholars.push(scholarId);
            }
            // Update selectAll state
            const totalCheckboxes = document.querySelectorAll('input[type="checkbox"][\\@change*="toggleScholar"]').length;
            this.selectAll = this.selectedScholars.length === totalCheckboxes && totalCheckboxes > 0;
        },

        isScholarSelected(scholarId) {
            return this.selectedScholars.includes(scholarId);
        },

        getSelectedCount() {
            return this.selectedScholars.length;
        },

        async bulkMarkClaimed() {
            if (this.selectedScholars.length === 0) {
                alert('Please select at least one scholar.');
                return;
            }

            if (!confirm(`Are you sure you want to mark ${this.selectedScholars.length} scholar(s) as claimed ? This will update their grant history.`)) {
                return;
            }

            try {
                const response = await fetch(config.routeUrl.replace('/sfao', '/sfao/scholars/bulk-mark-claimed'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        scholar_ids: this.selectedScholars
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert(`Successfully marked ${data.success_count} scholar(s) as claimed.${data.skipped_count > 0 ? ` ${data.skipped_count} scholar(s) were skipped (already claimed).` : ''} `);
                    this.selectedScholars = [];
                    this.selectAll = false;
                    this.fetchScholars();
                } else {
                    alert(data.message || 'An error occurred while processing the request.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while marking scholars as claimed.');
            }
        },

        openMarkAsModal(scholarId, scholarName) {
            this.selectedScholarId = scholarId;
            this.selectedScholarName = scholarName;
            this.showMarkAsModal = true;
            document.body.style.overflow = 'hidden';
        },

        async markScholarAs(action) {
            if (!this.selectedScholarId) {
                alert('No scholar selected.');
                return;
            }

            const actionText = action === 'claimed' ? 'claimed' : 'disqualified';
            if (!confirm(`Are you sure you want to mark this scholar as ${actionText}?`)) {
                return;
            }

            try {
                const url = action === 'claimed'
                    ? config.routeUrl.replace('/sfao', `/ sfao / scholars / ${this.selectedScholarId}/mark-claimed`)
                    : config.routeUrl.replace('/sfao', `/sfao/scholars/${this.selectedScholarId}/mark-disqualified`);

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (data.success || response.ok) {
                    alert(data.message || `Scholar marked as ${actionText} successfully.`);
                    this.showMarkAsModal = false;
                    document.body.style.overflow = '';
                    this.fetchScholars();
                } else {
                    alert(data.message || `Failed to mark scholar as ${actionText}.`);
                }
            } catch (error) {
                console.error('Error:', error);
                alert(`An error occurred while marking scholar as ${actionText}.`);
            }
        },

        getHeaderTitle() {
            let title = 'All Scholars';
            let campusName = 'All';

            if (this.filters.campus !== 'all') {
                const campus = this.campusOptions.find(c => c.id == this.filters.campus);
                if (campus) campusName = campus.name;
            }

            let typeLabel = 'Scholars';
            if (this.filters.type === 'new') typeLabel = 'New Scholars';
            else if (this.filters.type === 'old') typeLabel = 'Old Scholars';

            title = campusName === 'All' ? (this.filters.type === 'all' ? 'All Scholars' : typeLabel) : `${campusName} - ${typeLabel}`;

            return title;
        },

        getHeaderDescription() {
            let desc = '';
            let campusName = this.sfaoCampusName;

            if (this.filters.type === 'all') {
                desc = `All students who have been accepted as scholars from ${campusName}`;
            } else if (this.filters.type === 'new') {
                desc = `Scholars who have not yet received any grant from ${campusName}`;
            } else if (this.filters.type === 'old') {
                desc = `Continuing scholars with one or more grants from ${campusName}`;
            }

            if (this.extensionCampuses.length > 0) {
                desc += ` and its extension campuses: ${this.extensionCampuses.join(', ')}`;
            }
            return desc;
        },

        handleTabChange(tab) {
            if (tab === 'scholars') {
                if (this.filters.type !== 'all') {
                    this.filters.type = 'all';
                }
            } else if (tab.startsWith('scholars-')) {
                const type = tab.replace('scholars-', '');
                if (this.filters.type !== type) {
                    this.filters.type = type;
                }
            }
        }
    };
};


// SFAO Student Summary Report Component
window.sfaoStudentSummaryReport = function (config) {
    return {
        studentType: config.studentType || 'applicants',
        college: config.college || 'all',
        program: config.program || 'all',
        track: config.track || 'all',
        academicYear: config.academicYear || 'all',
        scholarshipId: config.scholarshipId || 'all',
        campusId: config.campusId || 'all',

        campusCollegePrograms: config.campusCollegePrograms || {},
        programTracks: config.programTracks || {},

        availablePrograms: [],
        availableTracks: [],

        init() {
            this.updateAvailablePrograms(false);
            this.updateAvailableTracks(false);
        },

        updateAvailablePrograms(reset = true) {
            if (reset) { this.program = 'all'; this.track = 'all'; }

            let programs = [];

            const collectPrograms = (cId) => {
                const campusData = this.campusCollegePrograms[cId] || {};
                if (this.college === 'all') {
                    Object.values(campusData).forEach(progs => programs.push(...progs));
                } else if (campusData[this.college]) {
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

            try {
                const url = new URL(config.routeUrl);
                url.searchParams.set('student_type', this.studentType);
                url.searchParams.set('college', this.college);
                url.searchParams.set('program', this.program);
                url.searchParams.set('track', this.track);
                url.searchParams.set('academic_year', this.academicYear);
                url.searchParams.set('scholarship_id', this.scholarshipId);
                url.searchParams.set('campus_id', this.campusId);
                url.searchParams.set('_t', new Date().getTime()); // Prevent caching

                console.log('Fetching Report:', url.toString());

                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(res => {
                        if (!res.ok) throw new Error('Network response was not ok');
                        return res.text();
                    })
                    .then(html => {
                        if (container) {
                            container.innerHTML = html;
                            // Optional: Re-initialize plugins if used (not needed here)
                        }
                    })
                    .catch(err => {
                        console.error('Error fetching report:', err);
                        // Minimal feedback to user
                        if (container) container.innerHTML = '<p class="text-red-500 text-center py-4">Error loading report data. Please try again.</p>';
                    })
                    .finally(() => {
                        if (container) container.style.opacity = '1';
                    });
            } catch (e) {
                console.error('Error constructing report URL:', e);
                if (container) container.style.opacity = '1';
            }
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
            window.location.href = config.routeUrl + '?' + params.toString();
        }
    };
};

