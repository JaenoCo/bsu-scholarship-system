<div x-show="tab === 'all_scholarships' || tab === 'private_scholarships' || tab === 'government_scholarships'" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-cloak
     class="px-4 py-6"
     x-data="centralScholarshipsFilter()">

    <!-- Header with Type Filter -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    <span x-show="tab === 'all_scholarships'" class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-900 dark:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path d="M12 14l9-5-9-5-9 5 9 5z" />
                          <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                        </svg>
                        All
                    </span>
                    <span x-show="tab === 'private_scholarships'" class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd" />
                        </svg>
                        Private
                    </span>
                    <span x-show="tab === 'government_scholarships'" class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd" />
                        </svg>
                        Government
                    </span>
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    <span x-show="tab === 'all_scholarships'">Manage all scholarship programs</span>
                    <span x-show="tab === 'private_scholarships'">Private scholarship programs</span>
                    <span x-show="tab === 'government_scholarships'">Government scholarship programs</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Sorting and Filtering Controls (SFAO Style) -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <div class="flex flex-wrap gap-4 items-end">
            <!-- Eligibility (Type) -->
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Eligibility</label>
                <div class="relative">
                    <select x-model="filters.type" class="block w-full px-3 py-2 text-base border border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none">
                        <option value="all">All</option>
                        <option value="gwa">GWA Requirement</option>
                        <option value="year_level">Year Level</option>
                        <option value="income">Income Bracket</option>
                        <option value="disability">Disability Status</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                         <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Sort By -->
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Sort By</label>
                <div class="relative">
                    <select x-model="filters.sort_by" class="block w-full px-3 py-2 text-base border border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none">
                        <option value="name">Scholarship Name</option>
                        <option value="created_at">Date Created</option>
                        <option value="submission_deadline">Deadline</option>
                        <option value="grant_amount">Grant Amount</option>
                        <option value="slots_available">Available Slots</option>
                        <option value="applications_count">Applications Count</option>
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

            <!-- Clear -->
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

    <!-- Add Button as Card -->
    <div class="mb-8">
        <a href="{{ route('central.scholarships.create') }}"
            class="group cursor-pointer flex flex-col justify-center items-center bg-gradient-to-br from-red-50 to-red-100 dark:from-gray-700 dark:to-gray-600 hover:from-red-100 hover:to-red-200 dark:hover:from-gray-600 dark:hover:to-gray-500 text-bsu-red dark:text-bsu-red hover:text-bsu-redDark dark:hover:text-bsu-redDark text-2xl font-bold rounded-xl shadow-lg border-2 border-dashed border-bsu-red dark:border-bsu-red hover:border-bsu-redDark dark:hover:border-bsu-redDark p-4 transition-all duration-300 hover:shadow-xl transform hover:-translate-y-1">
            <div class="group-hover:scale-110 transition-transform duration-200">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
            </div>
            <span class="text-base font-semibold group-hover:scale-105 transition-transform duration-200">Add New Scheme</span>
            <span class="text-xs text-bsu-red dark:text-bsu-red mt-1 opacity-75">Create a new scholarship program</span>
        </a>
    </div>

    <!-- Scholarships List -->
    <div>
        <!-- All Scholarships -->
        <div x-show="tab === 'all_scholarships'">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($scholarshipsAll as $scholarship)
                @include('central.partials.components.scholarship-card', ['scholarship' => $scholarship])
            @empty
                <p class="text-center text-gray-500 dark:text-gray-400 col-span-full py-8">
                    No scholarships available.
                </p>
            @endforelse
            </div>
            <div class="mt-8">
                {{ $scholarshipsAll->appends(request()->query())->links('vendor.pagination.custom') }}
            </div>
        </div>

        <!-- Private Scholarships -->
        <div x-show="tab === 'private_scholarships'">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($scholarshipsPrivate as $scholarship)
                @include('central.partials.components.scholarship-card', ['scholarship' => $scholarship])
            @empty
                <div class="col-span-full text-center py-12">
                    <div class="text-gray-400 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-green-500" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-400 mb-2">No Private Scholarships</h3>
                    <p class="text-gray-500 dark:text-gray-500">There are currently no private scholarship programs available.</p>
                </div>
            @endforelse
            </div>
            <div class="mt-8">
                {{ $scholarshipsPrivate->appends(request()->query())->links('vendor.pagination.custom') }}
            </div>
        </div>

        <!-- Government Scholarships -->
        <div x-show="tab === 'government_scholarships'">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($scholarshipsGov as $scholarship)
                @include('central.partials.components.scholarship-card', ['scholarship' => $scholarship])
            @empty
                <div class="col-span-full text-center py-12">
                    <div class="text-gray-400 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-400 mb-2">No Government Scholarships</h3>
                    <p class="text-gray-500 dark:text-gray-500">There are currently no government scholarship programs available.</p>
                </div>
            @endforelse
            </div>
            <div class="mt-8">
                {{ $scholarshipsGov->appends(request()->query())->links('vendor.pagination.custom') }}
            </div>
        </div>
    </div>

    <!-- JavaScript for form handling -->
    <script>
        document.addEventListener('alpine:init', () => {
             Alpine.data('centralScholarshipsFilter', () => ({
                filters: {
                    type: '{{ request('type', 'all') }}',
                    sort_by: '{{ request('sort_by', 'name') }}',
                    sort_order: '{{ request('sort_order', 'asc') }}'
                },
                init() {
                    this.$watch('filters.type', () => this.applyFilters());
                    this.$watch('filters.sort_by', () => this.applyFilters());
                    this.$watch('filters.sort_order', () => this.applyFilters());
                },
                applyFilters() {
                    const params = new URLSearchParams(window.location.search);
                    params.set('type', this.filters.type);
                    params.set('sort_by', this.filters.sort_by);
                    params.set('sort_order', this.filters.sort_order);
                    window.location.search = params.toString();
                },
                resetFilters() {
                    this.filters.type = 'all';
                    this.filters.sort_by = 'name';
                    this.filters.sort_order = 'asc';
                }
             }));
        });

        function confirmDelete(scholarshipName) {
            return confirm('WARNING: This will permanently delete the scholarship "' + scholarshipName + '" and all associated applications. This action cannot be undone. Are you sure you want to proceed?');
        }

        function hasVisibleScholarships(type) {
            const scholarships = document.querySelectorAll('[x-show*="' + type + '"]');
            return scholarships.length > 0;
        }

    </script>
</div>
