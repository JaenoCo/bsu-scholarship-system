<div class="space-y-6" x-data="studentScholarshipsFilter()">
    
    <!-- Sorting and Filtering Controls -->
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
                        <option value="created_at">Date Created</option>
                        <option value="name">Scholarship Name</option>
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
    
    <!-- Scholarships List -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($scholarships as $scholarship)
            <div x-show="subTab !== 'my_scholarships'" class="col-span-1">
                @php
                    // Use the controller-passed variable for pending application check
                    // Logic: User has active application if controller says so, AND they are not applying to THIS scholarship (which would be 'applied' state)
                    $hasActiveApplication = ($hasPendingApplication ?? false) && 
                                            !($scholarship->is_scholar ?? false) && 
                                            !($scholarship->applied ?? false);
                                            
                     // Calculate fill percentage if not passed
                     $applicationsCount = $scholarship->applications_count ?? 0;
                     $slotsAvailable = $scholarship->slots_available ?? 1;
                     $fillPercentage = min(100, ($applicationsCount / $slotsAvailable) * 100);
                @endphp

                @include('central.partials.components.scholarship-card', [
                    'scholarship' => $scholarship,
                    'role' => 'student',
                    'hasActiveApplication' => $hasActiveApplication,
                    'fillPercentage' => $fillPercentage
                ])
            </div>
        @empty
            <div x-show="subTab !== 'my_scholarships'" class="col-span-full flex flex-col items-center justify-center py-12 text-center">
                <div class="bg-gray-100 p-4 rounded-full mb-4">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">No scholarships found</h3>
                <p class="text-gray-500">Try adjusting your filters or check back later.</p>
            </div>
        @endforelse
        
        <!-- My Scholarships (Active) - Only visible when subTab is my_scholarships -->
        @if(isset($myScholarships) && $myScholarships->count() > 0)
             @foreach($myScholarships as $scholarship)
                <div x-show="subTab === 'my_scholarships'" class="col-span-1" x-cloak>
                    @include('central.partials.components.scholarship-card', [
                        'scholarship' => $scholarship,
                        'role' => 'student',
                        'hasActiveApplication' => false,
                        'fillPercentage' => 0
                    ])
                </div>
             @endforeach
        @endif
    </div>

    <!-- Pagination -->
    <div x-show="subTab !== 'my_scholarships'" class="mt-8 flex justify-center">
        {{ $scholarships->appends(request()->query())->links('vendor.pagination.custom') }}
    </div>

    <!-- Script for Filters -->
    <script>
        document.addEventListener('alpine:init', () => {
             Alpine.data('studentScholarshipsFilter', () => ({
                filters: {
                    type: '{{ request('type', 'all') }}',
                    sort_by: '{{ request('sort_by', 'created_at') }}',
                    sort_order: '{{ request('sort_order', 'desc') }}'
                },
                init() {
                    // Watchers to trigger reload on change
                    this.$watch('filters.type', () => this.applyFilters());
                    this.$watch('filters.sort_by', () => this.applyFilters());
                    this.$watch('filters.sort_order', () => this.applyFilters());
                },
                applyFilters() {
                    const params = new URLSearchParams(window.location.search);
                    params.set('type', this.filters.type);
                    params.set('sort_by', this.filters.sort_by);
                    params.set('sort_order', this.filters.sort_order);
                    // Reset to page 1 on filter change implies new result set, 
                    // but Laravel handles paginating simple params. 
                    // Ideally we should reset 'page' to 1, but we'll let user nav handle it or rely on Laravel/User.
                    // Usually safer to remove 'page' param on filter change.
                    params.delete('page');
                    
                    window.location.search = params.toString();
                },
                resetFilters() {
                    this.filters.type = 'all';
                    this.filters.sort_by = 'created_at';
                    this.filters.sort_order = 'desc';
                }
             }));
        });
    </script>
</div>