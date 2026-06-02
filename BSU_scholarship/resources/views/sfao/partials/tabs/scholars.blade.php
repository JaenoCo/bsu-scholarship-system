<div x-show="tab === 'scholars' || tab.startsWith('scholars-')" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-cloak 
     class="px-4 py-6"
     x-data="sfaoScholarsFilter()"
     x-init="$watch('tab', value => handleTabChange(value))">
    
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
            <span class="flex items-center">
                <span x-show="filters.type === 'all'" class="h-3 w-3 rounded-full bg-blue-500 mr-2"></span>
                <span x-show="filters.type === 'new'" class="h-3 w-3 rounded-full bg-green-500 mr-2"></span>
                <span x-show="filters.type === 'old'" class="h-3 w-3 rounded-full bg-yellow-500 mr-2"></span>
                <span x-text="getHeaderTitle()"></span>
            </span>
        </h2>
        <p class="text-gray-600 dark:text-gray-300">
            <span x-text="getHeaderDescription()"></span>
        </p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Scholars</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white" x-text="counts.total"></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white" x-text="counts.active"></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">New Scholars</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white" x-text="counts.new"></p>
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
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Continuing</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white" x-text="counts.old"></p>
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
                        <option value="created_at">Date Added</option>
                        <option value="name">Name</option>
                        <option value="email">Email</option>
                        <option value="scholarship">Scholarship</option>
                        <option value="status">Status</option>
                        <option value="type">Type</option>
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



            <!-- Status -->
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Status</label>
                <div class="relative">
                    <select x-model="filters.status" class="block w-full px-3 py-2 text-base border border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                        <option value="completed">Completed</option>
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

    <!-- Scholars List Container -->
    <div id="scholars-list-container">
        @include('sfao.partials.tabs.scholars_list', ['scholars' => $scholars])
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('sfaoScholarsFilter', () => ({
                filters: {
                    sort_by: localStorage.getItem('sfaoScholarsSortBy') || 'name',
                    sort_order: localStorage.getItem('sfaoScholarsSortOrder') || 'asc',
                    campus: localStorage.getItem('sfaoScholarsCampus') || 'all',
                    status: localStorage.getItem('sfaoScholarsStatus') || 'all',
                    type: localStorage.getItem('sfaoScholarsType') || 'all'
                },
                counts: {
                    total: {{ $scholars->count() }},
                    active: {{ $scholars->where('status', 'active')->count() }},
                    new: {{ $scholars->where('type', 'new')->count() }},
                    old: {{ $scholars->where('type', 'old')->count() }}
                },
                campusOptions: @json($campusOptions),
                sfaoCampusName: '{{ $sfaoCampus->name }}',
                extensionCampuses: @json($sfaoCampus->extensionCampuses->pluck('name')),

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
                        this.fetchScholars();
                    });
                    this.$watch('filters.status', (value) => {
                        localStorage.setItem('sfaoScholarsStatus', value);
                        this.fetchScholars();
                    });
                    this.$watch('filters.type', (value) => {
                        localStorage.setItem('sfaoScholarsType', value);
                        this.fetchScholars();
                    });

                    if (this.filters.status !== 'all' || this.filters.campus !== 'all' || this.filters.type !== 'all') {
                        this.fetchScholars();
                    }
                },

                fetchScholars() {
                    const params = new URLSearchParams({
                        tab: 'scholars',
                        scholars_sort_by: this.filters.sort_by,
                        scholars_sort_order: this.filters.sort_order,
                        campus_filter: this.filters.campus,
                        status_filter: this.filters.status,
                        type_filter: this.filters.type
                    });

                    fetch(`{{ route('sfao.dashboard') }}?${params.toString()}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('scholars-list-container').innerHTML = data.html;
                        this.counts = data.counts;
                    })
                    .catch(error => console.error('Error fetching scholars:', error));
                },

                resetFilters() {
                    this.filters.sort_by = 'created_at';
                    this.filters.sort_order = 'desc';
                    this.filters.campus = 'all';
                    this.filters.status = 'all';
                    this.filters.type = 'all';
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
            }));
        });
    </script>
</div>
