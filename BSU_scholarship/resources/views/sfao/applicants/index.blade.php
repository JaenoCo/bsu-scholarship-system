<div x-show="tab.startsWith('applicants')" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-cloak 
     class="px-4 py-6"
     x-data='sfaoApplicantsFilter({
        activeTab: @json($activeTab ?? 'applicants'),
        routeUrl: @json(route("sfao.applicants.list")),
        counts: {
             total: {{ $studentsAll->total() }},
             in_progress: {{ $studentsInProgress ? $studentsInProgress->total() : 0 }},
             pending: {{ $studentsPending->total() }},
             approved: {{ $studentsApproved->total() }},
             rejected: {{ $studentsRejected->total() }},
             not_applied: {{ $studentsNotApplied->total() }}
        },
        campusOptions: @json($campusOptions),
        colleges: @json($colleges),
        programs: @json($programs),
        tracks: @json($tracks),
        academicYears: @json($academicYears),
        campusCollegePrograms: @json($analytics['campus_college_programs'] ?? []),
        programTracks: @json($analytics['program_tracks'] ?? []),
        sfaoCampusName: @json($sfaoCampus->name),
        extensionCampuses: @json($sfaoCampus->extensionCampuses->pluck("name"))
     })'
      x-init="handleTabChange(tab); $watch('tab', value => handleTabChange(value))">
    
    <!-- Header removed -->

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
                    <select x-model="filters.sort_order" class="block w-full px-3 py-2 text-base border border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none cursor-pointer">
                        <option value="asc">Ascending</option>
                        <option value="desc">Descending</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                         <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Applicants List Container -->
    <div x-show="loading" class="flex justify-center items-center py-20">
         <svg class="animate-spin -ml-1 mr-3 h-10 w-10 text-bsu-red" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
         </svg>
    </div>
    <div id="applicants-list-container" x-show="!loading" x-transition.opacity.duration.300ms>
        @include('sfao.applicants.list', ['students' => $students])
    </div>
    
    <!-- Applicant Details Modal -->
    @include('sfao.components.modals.applicant-details')

    <!-- Status List Modal -->
    <div x-show="showStatusModal" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div @click="closeStatusModal()" x-show="showStatusModal" x-transition class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity" style="display: none;"></div>

            <!-- Modal panel -->
            <div class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <!-- Header -->
                <div class="bg-bsu-red text-white px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-medium" x-text="'Applicants - ' + statusModalTitle"></h3>
                    <button @click="closeStatusModal()" class="text-white hover:text-gray-200 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="px-6 py-4 max-h-96 overflow-y-auto">
                    <div x-show="statusModalLoading" class="flex justify-center items-center py-8">
                        <svg class="animate-spin h-8 w-8 text-bsu-red" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>

                    <template x-if="!statusModalLoading && statusModalApplicants.length > 0">
                        <div class="space-y-3">
                            <template x-for="applicant in statusModalApplicants" :key="applicant.id">
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-900 dark:text-white" x-text="applicant.name"></p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400" x-text="applicant.email"></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1" x-text="'Application ID: ' + (applicant.application_id || 'N/A')"></p>
                                    </div>
                                    <button @click="$dispatch('open-applicant-modal', applicant)" class="ml-4 px-3 py-2 bg-bsu-red text-white rounded hover:bg-red-700 transition text-sm">
                                        View Details
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="!statusModalLoading && statusModalApplicants.length === 0">
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">No applicants found in this category.</p>
                        </div>
                    </template>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3 flex justify-end">
                    <button @click="closeStatusModal()" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-900 dark:text-white rounded hover:bg-gray-400 dark:hover:bg-gray-500 transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>


</div>
