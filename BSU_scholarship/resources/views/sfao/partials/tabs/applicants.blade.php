<div x-show="tab.startsWith('applicants')" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-cloak 
     class="px-4 py-6"
     x-data="sfaoApplicantsFilter()"
     x-init="$watch('tab', value => handleTabChange(value))">
    
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
            <span x-text="getHeaderTitle()" class="flex items-center gap-2"></span>
        </h2>
        <p class="text-gray-600 dark:text-gray-300">
            <span x-text="getHeaderDescription()"></span>
        </p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Students</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white" x-text="counts.total"></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white" x-text="counts.pending"></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 dark:bg-red-900 rounded-lg">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Rejected</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white" x-text="counts.rejected"></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-2 bg-gray-100 dark:bg-gray-900 rounded-lg">
                    <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Not Applied</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white" x-text="counts.not_applied"></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Approved</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white" x-text="counts.approved"></p>
                </div>
            </div>
        </div>
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
                    <select x-model="filters.campus" class="block w-full px-3 py-2 text-base border border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none">
                        @foreach($campusOptions as $campus)
                            <option value="{{ $campus['id'] }}">{{ $campus['name'] }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                         <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

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
        @include('sfao.partials.tabs.applicants_list', ['students' => $studentsAll])
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('sfaoApplicantsFilter', () => ({
                filters: {
                    sort_by: localStorage.getItem('sfaoApplicantsSortBy') || 'name',
                    sort_order: localStorage.getItem('sfaoApplicantsSortOrder') || 'asc',
                    campus: localStorage.getItem('sfaoApplicantsCampus') || 'all',
                    status: localStorage.getItem('sfaoApplicantsStatus') || 'all'
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
                        localStorage.setItem('sfaoApplicantsSortBy', value);
                        this.fetchApplicants();
                    });
                    this.$watch('filters.sort_order', (value) => {
                        localStorage.setItem('sfaoApplicantsSortOrder', value);
                        this.fetchApplicants();
                    });
                    this.$watch('filters.campus', (value) => {
                        localStorage.setItem('sfaoApplicantsCampus', value);
                        this.fetchApplicants();
                    });
                    this.$watch('filters.status', (value) => {
                        localStorage.setItem('sfaoApplicantsStatus', value);
                        this.fetchApplicants();
                    });

                    this.updatePaginationLinks();
                    
                    if (this.filters.status !== 'all' || this.filters.campus !== 'all') {
                        this.fetchApplicants();
                    }
                },

                fetchApplicants(page = 1) {
                    const params = new URLSearchParams({
                        tab: 'applicants',
                        sort_by: this.filters.sort_by,
                        sort_order: this.filters.sort_order,
                        campus_filter: this.filters.campus,
                        status_filter: this.filters.status,
                        page_applicants: page
                    });

                    fetch(`{{ route('sfao.dashboard') }}?${params.toString()}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('applicants-list-container').innerHTML = data.html;
                        this.counts = data.counts;
                        this.updatePaginationLinks();
                    })
                    .catch(error => console.error('Error fetching applicants:', error));
                },

                updatePaginationLinks() {
                    const container = document.getElementById('applicants-list-container');
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
                    this.filters.status = 'all';
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
                    if (tab === 'applicants') {
                        if (this.filters.status !== 'all') {
                            this.filters.status = 'all';
                        }
                    } else if (tab.startsWith('applicants-')) {
                        const status = tab.replace('applicants-', '');
                        if (this.filters.status !== status) {
                            this.filters.status = status;
                        }
                    }
                }
            }));
        });
    </script>
</div>
