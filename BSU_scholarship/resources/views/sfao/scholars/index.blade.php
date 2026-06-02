<div x-show="tab === 'scholars' || tab.startsWith('scholars-')" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-cloak 
     class="px-4 py-6"
     x-data='sfaoScholarsFilter({
        routeUrl: @json(route("sfao.dashboard")),
        counts: {
            total: {{ $scholars->count() }},
            active: {{ $scholars->where("status", "active")->count() }},
            new: {{ $scholars->where("type", "new")->count() }},
            old: {{ $scholars->where("type", "old")->count() }}
        },
        campusOptions: @json($campusOptions),
        colleges: [], 
        programs: [],
        tracks: @json($tracks),
        academicYears: @json($academicYears),
        campusCollegePrograms: @json($analytics['campus_college_programs'] ?? []),
        programTracks: @json($analytics['program_tracks'] ?? []),
        sfaoCampusName: @json($sfaoCampus->name),
        extensionCampuses: @json($sfaoCampus->extensionCampuses->pluck("name"))
     })'
     x-init="$watch('tab', value => handleTabChange(value))">
    
    <!-- Header Removed -->

    <!-- Sorting and Filtering Controls -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <div class="flex flex-wrap gap-4 items-end">
            
            <!-- Scholarship -->
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Scholarship</label>
                <div class="relative">
                    <select x-model="filters.scholarship" class="block w-full px-3 py-2 text-base border border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none cursor-pointer">
                        <option value="all">All Scholarships</option>
                        @foreach($scholarshipsAll as $scholarship)
                            <option value="{{ $scholarship->id }}">{{ $scholarship->scholarship_name }}</option>
                        @endforeach
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
                    <select x-model="filters.campus" class="block w-full px-3 py-2 text-base border border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none cursor-pointer">
                        @foreach($campusOptions as $campus)
                            <option value="{{ $campus['id'] }}">{{ $campus['name'] }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                         <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <!-- College -->
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">College</label>
                <div class="relative">
                    <select x-model="filters.college" class="block w-full px-3 py-2 text-base border border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none cursor-pointer">
                        <option value="all">All Colleges</option>
                        <template x-for="opt in colleges" :key="opt.value">
                            <option :value="opt.value" x-text="opt.name"></option>
                        </template>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                         <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Program -->
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Program</label>
                <div class="relative">
                    <select x-model="filters.program" class="block w-full px-3 py-2 text-base border border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none cursor-pointer">
                        <option value="all">All Programs</option>
                        <template x-for="program in programs" :key="program">
                            <option :value="program" x-text="program"></option>
                        </template>
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
                    <select x-model="filters.track" 
                            :disabled="tracks.length === 0" 
                            :class="{'opacity-50 cursor-not-allowed': tracks.length === 0}"
                            class="block w-full px-3 py-2 text-base border border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none cursor-pointer">
                        <option value="all">All Tracks</option>
                        <template x-for="track in tracks" :key="track">
                            <option :value="track" x-text="track"></option>
                        </template>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                         <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Academic Year -->
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Academic Year</label>
                <div class="relative">
                    <select x-model="filters.academic_year" class="block w-full px-3 py-2 text-base border border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none cursor-pointer">
                        <option value="all">All Years</option>
                        <template x-for="year in academicYears" :key="year">
                            <option :value="year" x-text="year"></option>
                        </template>
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
                    <select x-model="filters.sort_by" class="block w-full px-3 py-2 text-base border border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none cursor-pointer">
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
                    <select x-model="filters.sort_order" class="block w-full px-3 py-2 text-base border border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none cursor-pointer">
                        <option value="asc">Ascending</option>
                        <option value="desc">Descending</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                         <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>
            
            <!-- Clear Button Removed to match Applicants tab design -->
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <!-- Total Scholars -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4" x-show="tab === 'scholars'">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Scholars</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white" x-text="counts.total"></p>
                </div>
            </div>
        </div>

        <!-- Old Scholars -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4" x-show="tab === 'scholars' || tab === 'scholars-old'">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400" x-text="tab === 'scholars-old' ? 'Total Old Scholars' : 'Old Scholars'"></p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white" x-text="counts.old"></p>
                </div>
            </div>
        </div>

        <!-- New Scholars -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4" x-show="tab === 'scholars' || tab === 'scholars-new'">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400" x-text="tab === 'scholars-new' ? 'Total New Scholars' : 'New Scholars'"></p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white" x-text="counts.new"></p>
                </div>
            </div>
        </div>
    </div>

    {{-- Bulk Action Bar --}}
    <div x-show="getSelectedCount() > 0" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-medium text-blue-900 dark:text-blue-100">
                    <span x-text="getSelectedCount()"></span> scholar(s) selected
                </span>
            </div>
            <div class="flex items-center space-x-3">
                <button @click="selectedScholars = []; selectAll = false" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                    Clear Selection
                </button>
                <button @click="bulkMarkDisqualified()" 
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-all duration-200 text-sm font-semibold shadow-md hover:shadow-lg">
                    Mark as Disqualified
                </button>
                <button @click="bulkMarkClaimed()" 
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all duration-200 text-sm font-semibold shadow-md hover:shadow-lg">
                    Mark as Claimed
                </button>
            </div>
        </div>
    </div>

    <!-- Scholars List Container -->
    <div x-show="loading" class="flex justify-center items-center py-20">
         <svg class="animate-spin -ml-1 mr-3 h-10 w-10 text-bsu-red" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
         </svg>
    </div>
    <div id="scholars-list-container" x-show="!loading" x-transition.opacity.duration.300ms>
        @include('sfao.scholars.list', ['scholars' => $scholars])
    </div>

    {{-- Mark As Modal --}}
    <div x-show="showMarkAsModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <!-- Background Overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm transition-opacity duration-300"></div>
        
        <!-- Modal Container -->
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-8 transform transition-all duration-300 my-8"
                 @click.away="showMarkAsModal = false">
                <!-- Close Button -->
                <button @click="showMarkAsModal = false" 
                        class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <!-- Modal Header -->
                <div class="mb-6">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Mark Scholar</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400" x-text="'Scholar: ' + selectedScholarName"></p>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <button @click="markScholarAs('claimed')" 
                            class="w-full px-6 py-4 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-all duration-200 font-semibold shadow-lg hover:shadow-xl flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Mark as Claimed</span>
                    </button>

                    <button @click="markScholarAs('disqualified')" 
                            class="w-full px-6 py-4 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-all duration-200 font-semibold shadow-lg hover:shadow-xl flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Mark as Disqualified</span>
                    </button>

                    <button @click="showMarkAsModal = false" 
                            class="w-full px-6 py-4 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200 font-semibold">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>


</div>
