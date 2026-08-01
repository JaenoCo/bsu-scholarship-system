<div x-show="tab.startsWith('applicants')" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-cloak 
    class="px-4 py-6"
     x-data='sfaoApplicantsFilter({
        activeTab: @json(str_replace("_", "-", $activeTab ?? "applicants")),
        routeUrl: @json(route("sfao.applicants.list")),
        counts: {
            total: {{ $studentsAll->total() }},
            in_progress: {{ $studentsInProgress->total() }},
            pending: {{ $studentsPending->total() }},
            approved: {{ $studentsApproved->total() }},
            rejected: {{ $studentsRejected->total() }}
        },
        campusOptions: @json($campusOptions),
        colleges: @json($colleges),
        programs: @json($programs),
        tracks: @json($tracks),
        academicYears: @json($academicYears),
        campusCollegePrograms: @json($analytics['campus_college_programs'] ?? []),
        programTracks: @json($analytics['program_tracks'] ?? []),
        sfaoCampusName: @json($sfaoCampus->name),
        extensionCampuses: @json($sfaoCampus->extensionCampuses->pluck('name'))
     })'
     x-init="handleTabChange(tab); $watch('tab', value => handleTabChange(value));">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
            <span x-text="getHeaderTitle()" class="flex items-center gap-2"></span>
        </h2>
        <p class="text-gray-600 dark:text-gray-300">
            <span x-text="getHeaderDescription()"></span>
        </p>
    </div>

    <!-- Sorting and Filtering Controls -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <div class="flex flex-wrap gap-4 items-end">
            <!-- Sort By -->
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Sort By</label>
                <div class="relative">
                    <select x-model="filters.sort_by" class="block w-full px-3 py-2 text-base border border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none">
                        <option value="name">Name</option>
                        <option value="email">Email</option>
                        <option value="date_joined">Date Joined</option>
                        <option value="last_uploaded">Last Upload</option>
                        <option value="documents_count">Document Count</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                         <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Order -->
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Order</label>
                <div class="relative">
                    <select x-model="filters.sort_order" class="block w-full px-3 py-2 text-base border border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none">
                        <option value="asc">Ascending</option>
                        <option value="desc">Descending</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                         <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Campus -->
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Campus</label>
                <div class="relative">
                    <select x-model="filters.campus" class="block w-full px-3 py-2 text-base border border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none">                        <option value="all">All Campuses</option>                        @foreach($campusOptions as $campus)
                            <option value="{{ $campus['id'] }}">{{ $campus['name'] }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                         <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <!-- College -->
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">College</label>
                <div class="relative">
                    <select x-model="filters.college" class="block w-full px-3 py-2 text-base border border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none">
                        <option value="all">All Colleges</option>
                        @foreach($colleges ?? [] as $college)
                            <option value="{{ $college['value'] }}">{{ $college['name'] }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                         <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Program -->
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Program</label>
                <div class="relative">
                    <select x-model="filters.program" class="block w-full px-3 py-2 text-base border border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none">
                        <option value="all">All Programs</option>
                        @foreach($programs ?? [] as $program)
                            @if(!empty($program))
                                <option value="{{ $program }}">{{ $program }}</option>
                            @endif
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                         <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Track -->
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Track</label>
                <div class="relative">
                    <select x-model="filters.track" class="block w-full px-3 py-2 text-base border border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none">
                        <option value="all">All Tracks</option>
                        @foreach($tracks ?? [] as $track)
                            @if(!empty($track))
                                <option value="{{ $track }}">{{ $track }}</option>
                            @endif
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                         <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Academic Year -->
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Academic Year</label>
                <div class="relative">
                        <select x-model="filters.academic_year" class="block w-full px-3 py-2 text-base border border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none">
                            <option value="all">All Years</option>
                            @foreach($academicYears ?? [] as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                    <button type="button" @click="filters.academic_year = 'all'" class="mt-2 text-xs font-semibold text-bsu-red hover:text-red-700 focus:outline-none">Select All Years</button>
            </div>

            <!-- Status -->
            <fieldset class="flex-[2] min-w-[280px]">
                <legend class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Status</legend>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 rounded-lg border border-red-500 p-1 dark:border-red-500">
                    <template x-for="option in [
                        { value: 'all', label: 'All' },
                        { value: 'pending', label: 'Pending' },
                        { value: 'approved', label: 'Approved' },
                        { value: 'rejected', label: 'Declined' }
                    ]" :key="option.value">
                        <label class="flex cursor-pointer items-center justify-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition"
                               :class="filters.status === option.value ? 'bg-bsu-red text-white shadow-sm' : 'text-gray-700 hover:bg-red-50 dark:text-gray-200 dark:hover:bg-gray-700'">
                            <input type="radio" class="sr-only" name="sfao_applicant_status_legacy" :value="option.value" x-model="filters.status">
                            <span x-text="option.label"></span>
                        </label>
                    </template>
                </div>
            </fieldset>

            <!-- Actions -->
            <div class="flex flex-col items-center">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Clear</label>
                <button type="button" @click="resetFilters()" class="bg-white dark:bg-gray-700 text-gray-500 dark:text-gray-400 border border-red-500 dark:border-red-500 p-2 rounded-full hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bsu-red shadow-sm h-[38px] w-[38px] flex items-center justify-center" title="Reset Filters">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Applicants List Container -->
    <div id="applicants-list-container">
        @include('sfao.partials.tabs.applicants_list', ['students' => $students])
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('sfaoApplicantsFilter', () => ({
                activeTab: @json(str_replace('_', '-', $activeTab ?? 'applicants')),
                currentTab: @json(str_replace('_', '-', $activeTab ?? 'applicants')),
                loading: false,
                suspendFetch: false,
                filters: {
                    sort_by: localStorage.getItem('sfaoApplicantsSortBy') || 'name',
                    sort_order: localStorage.getItem('sfaoApplicantsSortOrder') || 'asc',
                    campus: localStorage.getItem('sfaoApplicantsCampus') || 'all',
                    college: localStorage.getItem('sfaoApplicantsCollege') || 'all',
                    program: localStorage.getItem('sfaoApplicantsProgram') || 'all',
                    track: localStorage.getItem('sfaoApplicantsTrack') || 'all',
                    academic_year: localStorage.getItem('sfaoApplicantsAcademicYear') || 'all',
                    status: (@json(str_replace('_', '-', $activeTab ?? 'applicants')).startsWith('applicants-')
                        ? @json(str_replace('_', '-', $activeTab ?? 'applicants')).replace('applicants-', '')
                        : (localStorage.getItem('sfaoApplicantsStatus') || 'all'))
                },
                counts: {
                    total: {{ $studentsAll->total() }},
                    pending: {{ $studentsPending->total() }},
                    rejected: {{ $studentsRejected->total() }},
                    not_applied: {{ $studentsNotApplied->total() }},
                    approved: {{ $studentsApproved->total() }}
                },
                campusOptions: @json($campusOptions),
                sfaoCampusName: '{{ $sfaoCampus->name }}',
                extensionCampuses: @json($sfaoCampus->extensionCampuses->pluck('name')),

                init() {
                    this.$watch('filters.sort_by', (value) => {
                        if (this.suspendFetch) return;
                        localStorage.setItem('sfaoApplicantsSortBy', value);
                        this.fetchApplicants();
                    });
                    this.$watch('filters.sort_order', (value) => {
                        if (this.suspendFetch) return;
                        localStorage.setItem('sfaoApplicantsSortOrder', value);
                        this.fetchApplicants();
                    });
                    this.$watch('filters.campus', (value) => {
                        if (this.suspendFetch) return;
                        localStorage.setItem('sfaoApplicantsCampus', value);
                        this.fetchApplicants();
                    });
                    this.$watch('filters.status', (value) => {
                        if (this.suspendFetch) return;
                        localStorage.setItem('sfaoApplicantsStatus', value);
                        this.fetchApplicants();
                    });
                    this.$watch('filters.college', (value) => {
                        if (this.suspendFetch) return;
                        localStorage.setItem('sfaoApplicantsCollege', value);
                        this.fetchApplicants();
                    });
                    this.$watch('filters.program', (value) => {
                        if (this.suspendFetch) return;
                        localStorage.setItem('sfaoApplicantsProgram', value);
                        this.fetchApplicants();
                    });
                    this.$watch('filters.track', (value) => {
                        if (this.suspendFetch) return;
                        localStorage.setItem('sfaoApplicantsTrack', value);
                        this.fetchApplicants();
                    });
                    this.$watch('filters.academic_year', (value) => {
                        if (this.suspendFetch) return;
                        localStorage.setItem('sfaoApplicantsAcademicYear', value);
                        this.fetchApplicants();
                    });

                    this.updatePaginationLinks();
                    this.handleTabChange(this.activeTab);
                },

                fetchApplicants(page = 1) {
                    const params = {
                        tab: this.currentTab,
                        sort_by: this.filters.sort_by,
                        sort_order: this.filters.sort_order,
                        campus_filter: this.filters.campus,
                        college_filter: this.filters.college,
                        program_filter: this.filters.program,
                        track_filter: this.filters.track,
                        academic_year_filter: this.filters.academic_year,
                        status_filter: this.filters.status,
                        page_applicants: page
                    };
                    const queryString = Object.entries(params)
                        .map(([key, value]) => encodeURIComponent(key) + '=' + encodeURIComponent(value))
                        .join('&');

                    this.loading = true;
                    fetch(`{{ route('sfao.applicants.list') }}?${queryString}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('applicants-list-container').innerHTML = data.html;
                        this.counts = data.counts;
                        this.updatePaginationLinks();
                    })
                    .catch(error => console.error('Error fetching applicants:', error))
                    .finally(() => {
                        this.loading = false;
                    });
                },

                updatePaginationLinks() {
                    const container = document.getElementById('applicants-list-container');
                    const links = container.querySelectorAll('a.page-link');
                    links.forEach(link => {
                        link.addEventListener('click', (e) => {
                            e.preventDefault();
                            const href = link.getAttribute('href') || '';
                            const match = href.match(/[?&]page_applicants=(\d+)/);
                            const page = match ? Number(match[1]) : 1;
                            this.fetchApplicants(page);
                        });
                    });
                },

                resetFilters() {
                    this.suspendFetch = true;
                    this.filters.sort_by = 'name';
                    this.filters.sort_order = 'asc';
                    this.filters.campus = 'all';
                    this.filters.college = 'all';
                    this.filters.program = 'all';
                    this.filters.track = 'all';
                    this.filters.academic_year = 'all';
                    this.filters.status = 'all';
                    this.suspendFetch = false;
                    this.fetchApplicants();
                },

                getHeaderTitle() {
                    let title = 'All Applicants';
                    let campusName = 'All';
                    
                    if (this.filters.campus !== 'all') {
                        const campus = this.campusOptions.find(c => c.id == this.filters.campus);
                        if (campus) campusName = campus.name;
                    }

                    if (this.filters.status === 'all') {
                        title = campusName === 'All' ? 'All Applicants' : `${campusName} Applicants`;
                    } else {
                        const statusLabel = this.filters.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                        title = campusName === 'All' ? statusLabel : `${campusName} - ${statusLabel}`;
                    }
                    return title;
                },

                getHeaderDescription() {
                    let desc = '';
                    let campusName = this.sfaoCampusName;
                    
                    if (this.filters.status === 'all') {
                        desc = `All students from ${campusName}`;
                    } else {
                        desc = `Students with this status from ${campusName}`;
                    }

                    if (this.extensionCampuses.length > 0) {
                        desc += ` and its extension campuses: ${this.extensionCampuses.join(', ')}`;
                    }
                    return desc;
                },

                handleTabChange(tab) {
                    const normalizedTab = tab.replace('applicants_', 'applicants-');
                    this.currentTab = normalizedTab === 'applicants-not_applied' ? 'applicants' : normalizedTab;

                    if (this.currentTab === 'applicants') {
                        if (this.filters.status !== 'all') {
                            this.filters.status = 'all';
                        }
                    } else if (this.currentTab.startsWith('applicants-')) {
                        const status = this.currentTab.replace('applicants-', '');
                        if (this.filters.status !== status) {
                            this.filters.status = status;
                        }
                    }

                    localStorage.setItem('sfaoApplicantsStatus', this.filters.status);
                }
            }));
        });
    </script>
</div>
