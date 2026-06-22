<div x-show="tab === 'analytics' || tab.startsWith('analytics_')" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-cloak 
     x-data='sfaoStatisticsTab({ analytics: @json($analytics ?? []), campusOptions: @json($campusOptions) })'
     @tab-changed.window="handleTabChange($event.detail)">
    <div class="space-y-6">
        
        <!-- Analytics Sub-Tabs -->


        <!-- Filter Controls -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
            <div class="flex flex-wrap gap-4 items-end">
                
                <!-- Scholarship Filters Removed -->

                <!-- Campus Filter (Global) -->
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Campus</label>
                    <div class="relative">
                        <select x-model="filters.campus" 
                                class="block w-full px-3 py-2 text-base border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none"
                                style="border-width: 1px;">
                            <template x-if="campusOptions.length > 1">
                                <option value="all">All</option>
                            </template>
                            <template x-for="campus in campusOptions" :key="campus.id">
                                <option :value="campus.id" x-text="campus.name"></option>
                            </template>
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
                            <template x-for="college in availableColleges" :key="college.short_name">
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
                            <option value="all">All</option>
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
            
              <!-- Row 2: Search Filter -->
            <div class="flex gap-4 items-end w-full mt-4 border-t border-gray-200 dark:border-gray-700 pt-4 relative">
                <div class="flex-1 relative">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Search Scholarship</label>
                    <div class="relative">
                        <input type="text" 
                               x-model="filters.search" 
                               @input="handleSearchInput()"
                               @keydown.enter="performSearch()"
                               placeholder="Search Scholarship..." 
                               class="block w-full px-3 py-2 text-base border border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center">
                    </div>

                    <!-- Autocomplete Dropdown -->
                    <div x-show="showSearchResults && searchResults.length > 0" 
                         @click.away="showSearchResults = false"
                         class="absolute z-50 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto"
                         style="display: none;">
                        <template x-for="result in searchResults" :key="result.id">
                            <div @click="selectSearchResult(result.scholarship_name)" 
                                 class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm text-gray-700 dark:text-gray-200 transition-colors">
                                <span x-text="result.scholarship_name"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Search Button -->
                <div class="w-auto flex flex-col items-center">
                       <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Search</label>
                       <button type="button" @click="performSearch()" class="bg-white dark:bg-gray-700 text-gray-500 dark:text-gray-400 border border-red-500 dark:border-red-500 p-2 rounded-full hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bsu-red shadow-sm h-[38px] w-[38px] flex items-center justify-center" title="Search">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                          </svg>
                      </button>
                </div>

                 <!-- Clear Button -->
                 <div class="w-auto flex flex-col items-center">
                       <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Clear</label>
                       <button type="button" @click="filters.search = ''; handleSearchInput(); performSearch()" class="bg-white dark:bg-gray-700 text-gray-500 dark:text-gray-400 border border-red-500 dark:border-red-500 p-2 rounded-full hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bsu-red shadow-sm h-[38px] w-[38px] flex items-center justify-center" title="Clear Search">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                          </svg>
                      </button>
                  </div>
            </div>
            
            <!-- Global Legend Buttons (Row 2) -->
            <div class="mt-4 flex flex-wrap justify-between gap-4 w-full">
                 <!-- Applicants Mode Legend -->
                <template x-if="subTab === 'applicants'">
                    <div class="flex flex-wrap justify-between w-full gap-2">
                        <!-- Approved -->
                         <button @click="chartLegend.approved = !chartLegend.approved"
                                :class="chartLegend.approved ? 'text-white ring-2 ring-red-800' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 hover:bg-gray-200'"
                                :style="chartLegend.approved ? 'background-color: #7F1D1D;' : ''"
                                class="flex-1 px-4 py-2 rounded-full text-sm font-medium transition-colors duration-200 focus:outline-none flex items-center justify-center shadow-sm">
                                <span class="w-2 h-2 rounded-full mr-2 bg-white" x-show="chartLegend.approved"></span>
                                Approved
                        </button>
                        <!-- Rejected -->
                        <button @click="chartLegend.rejected = !chartLegend.rejected"
                                :class="chartLegend.rejected ? 'text-white ring-2 ring-red-800' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 hover:bg-gray-200'"
                                :style="chartLegend.rejected ? 'background-color: #991B1B;' : ''"
                                class="flex-1 px-4 py-2 rounded-full text-sm font-medium transition-colors duration-200 focus:outline-none flex items-center justify-center shadow-sm">
                                <span class="w-2 h-2 rounded-full mr-2 bg-white" x-show="chartLegend.rejected"></span>
                                Rejected
                        </button>
                        <!-- Pending -->
                         <button @click="chartLegend.pending = !chartLegend.pending"
                                :class="chartLegend.pending ? 'text-white ring-2 ring-red-600' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 hover:bg-gray-200'"
                                :style="chartLegend.pending ? 'background-color: #B91C1C;' : ''"
                                class="flex-1 px-4 py-2 rounded-full text-sm font-medium transition-colors duration-200 focus:outline-none flex items-center justify-center shadow-sm">
                                <span class="w-2 h-2 rounded-full mr-2 bg-white" x-show="chartLegend.pending"></span>
                                Pending
                        </button>
                        <!-- In Progress -->
                        <button @click="chartLegend.inProgress = !chartLegend.inProgress"
                                :class="chartLegend.inProgress ? 'text-white ring-2 ring-red-500' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 hover:bg-gray-200'"
                                :style="chartLegend.inProgress ? 'background-color: #DC2626;' : ''"
                                class="flex-1 px-4 py-2 rounded-full text-sm font-medium transition-colors duration-200 focus:outline-none flex items-center justify-center shadow-sm">
                                <span class="w-2 h-2 rounded-full mr-2 bg-white" x-show="chartLegend.inProgress"></span>
                                In Progress
                        </button>
                    </div>
                </template>

                 <!-- Scholars Mode Legend -->
                 <template x-if="subTab === 'scholars'">
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

                <!-- Scholarships (Comparison) Mode Legend Moved Below Chart -->
            </div>
        </div>

        <!-- SCHOLARSHIPS SubTab Content -->
        <div x-show="subTab === 'scholarships'" 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mt-6 mb-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 text-center" x-text="getComparisonChartTitle()"></h3>
            
            <!-- Summary Cards for Scholarships Tab -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                 <!-- Total -->
                 <button type="button"
                         @click="openStudentDetails('comparison', 'total')"
                         class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 text-center border border-gray-100 dark:border-gray-600 hover:ring-2 hover:ring-gray-300 dark:hover:ring-gray-500 focus:outline-none focus:ring-2 focus:ring-bsu-red transition"
                         title="Show student numbers">
                     <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Total</p>
                     <p class="text-xl font-bold text-gray-900 dark:text-white" x-text="filteredData.counts?.total || 0"></p>
                 </button>
                 <!-- Applicants -->
                 <button type="button"
                         @click="openStudentDetails('comparison', 'applicants')"
                         class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 text-center border border-blue-100 dark:border-blue-800 hover:ring-2 hover:ring-blue-300 dark:hover:ring-blue-700 focus:outline-none focus:ring-2 focus:ring-bsu-red transition"
                         title="Show applicant student numbers">
                     <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase">Applicants</p>
                     <p class="text-xl font-bold text-blue-700 dark:text-blue-300" x-text="filteredData.counts?.applicantsCount || 0"></p>
                 </button>
                 <!-- Scholars -->
                 <button type="button"
                         @click="openStudentDetails('comparison', 'scholars')"
                         class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 text-center border border-green-100 dark:border-green-800 hover:ring-2 hover:ring-green-300 dark:hover:ring-green-700 focus:outline-none focus:ring-2 focus:ring-bsu-red transition"
                         title="Show scholar student numbers">
                     <p class="text-xs font-semibold text-green-600 dark:text-green-400 uppercase">Scholars</p>
                     <p class="text-xl font-bold text-green-700 dark:text-green-300" x-text="filteredData.counts?.scholarsCount || 0"></p>
                 </button>
            </div>



            <div class="relative h-96 w-full mb-6">
                 <div x-show="chartStatus.comparison" class="h-full w-full">
                    <canvas id="sfaoComparisonChart"></canvas>
                </div>
                 <!-- No Data Message -->
                 <div x-show="!chartStatus.comparison" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="text-center p-6 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white" x-text="(!chartLegend.applicants && !chartLegend.scholars) ? 'Select a category to view data' : ((filters.search && filters.search.trim() !== '') ? 'There is no Scholarship named ' + filters.search : 'No Comparison Data')"></h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your filters.</p>
                    </div>
                </div>
            </div>

            <!-- Scholarships (Comparison) Mode Legend (Moved Here) -->

        </div>

        <!-- APPLICANTS & SCHOLARS Charts Section -->
        <div x-show="subTab === 'applicants' || subTab === 'scholars'" 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mt-6">
            <!-- Header Section (Centered) -->
            <div class="text-center mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white" x-text="getChartTitle()">Scholarship Status</h3>
            </div>


            <!-- Filters Section within Card - REMOVED (Moved to Global) -->

            <!-- Dynamic Summary Counts -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                 <!-- Total -->
                 <button type="button"
                         @click="openStudentDetails('status', 'total')"
                         class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 text-center border border-gray-100 dark:border-gray-600 hover:ring-2 hover:ring-gray-300 dark:hover:ring-gray-500 focus:outline-none focus:ring-2 focus:ring-bsu-red transition"
                         title="Show student numbers">
                     <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Total</p>
                     <p class="text-xl font-bold text-gray-900 dark:text-white" x-text="filteredData.counts?.total || 0"></p>
                 </button>
                 <!-- Approved -->
                 <button type="button"
                         @click="openStudentDetails('status', 'approved')"
                         class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 text-center border border-green-100 dark:border-green-800 hover:ring-2 hover:ring-green-300 dark:hover:ring-green-700 focus:outline-none focus:ring-2 focus:ring-bsu-red transition"
                         title="Show approved student numbers">
                     <p class="text-xs font-semibold text-green-600 dark:text-green-400 uppercase">Approved</p>
                     <p class="text-xl font-bold text-green-700 dark:text-green-300" x-text="filteredData.counts?.approved || 0"></p>
                 </button>
                 <!-- Rejected -->
                 <button type="button"
                         @click="openStudentDetails('status', 'rejected')"
                         class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3 text-center border border-red-100 dark:border-red-800 hover:ring-2 hover:ring-red-300 dark:hover:ring-red-700 focus:outline-none focus:ring-2 focus:ring-bsu-red transition"
                         title="Show rejected student numbers">
                     <p class="text-xs font-semibold text-red-600 dark:text-red-400 uppercase">Rejected</p>
                     <p class="text-xl font-bold text-red-700 dark:text-red-300" x-text="filteredData.counts?.rejected || 0"></p>
                 </button>
                 <!-- Active / Pending -->
                 <button type="button"
                         @click="openStudentDetails('status', 'active')"
                         class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-3 text-center border border-yellow-100 dark:border-yellow-800 hover:ring-2 hover:ring-yellow-300 dark:hover:ring-yellow-700 focus:outline-none focus:ring-2 focus:ring-bsu-red transition"
                         title="Show pending and in-progress student numbers">
                     <p class="text-xs font-semibold text-yellow-600 dark:text-yellow-400 uppercase">Pending/In Progress</p>
                     <p class="text-xl font-bold text-yellow-700 dark:text-yellow-300" x-text="filteredData.counts?.active || 0"></p>
                 </button>
                 <!-- Rate -->
                 <button type="button"
                         @click="openStudentDetails('status', 'approvalRate')"
                         class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 text-center border border-blue-100 dark:border-blue-800 hover:ring-2 hover:ring-blue-300 dark:hover:ring-blue-700 focus:outline-none focus:ring-2 focus:ring-bsu-red transition"
                         title="Show approved student numbers used for the rate">
                     <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase">Approval Rate</p>
                     <p class="text-xl font-bold text-blue-700 dark:text-blue-300" x-text="(filteredData.counts?.approvalRate || '0.0') + '%'"></p>
                 </button>
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
                            x-text="filters.search ? 'No scholarship named \'' + filters.search + '\' found' : (viewMode === 'applicants' ? 'No Applicants Found' : 'No Scholars Found')">
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" 
                           x-text="filters.search ? 'Try checking for typos or use a different keyword.' : 'Try adjusting your filters.'">
                        </p>
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

    </div>

    <div x-show="studentDetails.open"
         x-cloak
         x-transition.opacity
         @keydown.escape.window="closeStudentDetails()"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-2 sm:p-4"
         style="display: none;">
        <div @click.away="closeStudentDetails()"
             class="flex w-full flex-col overflow-hidden rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-xl"
             style="height: 88vh; max-width: 72rem;">
            <div class="flex shrink-0 items-center justify-between gap-4 border-b border-gray-200 dark:border-gray-700 px-4 py-3 sm:px-5 sm:py-4">
                <div class="min-w-0">
                    <h3 class="truncate text-sm font-semibold text-gray-900 dark:text-white sm:text-base" x-text="studentDetails.title"></h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        <span x-text="studentDetails.rows.length"></span>
                        <span x-text="studentDetails.rows.length === 1 ? 'record' : 'records'"></span>
                    </p>
                </div>
                <button type="button"
                        @click="closeStudentDetails()"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-bsu-red dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                        title="Close">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex-1"
                 style="min-height: 0; max-height: calc(88vh - 74px); overflow-y: auto; overflow-x: auto; overscroll-behavior: contain;">
                <template x-if="studentDetails.rows.length === 0">
                    <div class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                        No student numbers found for this selection.
                    </div>
                </template>

                <table x-show="studentDetails.rows.length > 0"
                       class="w-full table-fixed divide-y divide-gray-200 dark:divide-gray-700 text-xs sm:text-sm"
                       style="min-width: 920px;">
                    <colgroup>
                        <col style="width: 140px;">
                        <col style="width: 180px;">
                        <col style="width: 240px;">
                        <col style="width: 120px;">
                        <col style="width: 120px;">
                        <col style="width: 260px;">
                    </colgroup>
                    <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-900 shadow-sm">
                        <tr>
                            <th class="w-[140px] px-3 py-3 text-left text-[11px] font-semibold uppercase text-gray-500 dark:text-gray-400 sm:px-4">Student No.</th>
                            <th class="w-[180px] px-3 py-3 text-left text-[11px] font-semibold uppercase text-gray-500 dark:text-gray-400 sm:px-4">Name</th>
                            <th class="w-[240px] px-3 py-3 text-left text-[11px] font-semibold uppercase text-gray-500 dark:text-gray-400 sm:px-4">Scholarship</th>
                            <th class="w-[120px] px-3 py-3 text-left text-[11px] font-semibold uppercase text-gray-500 dark:text-gray-400 sm:px-4">Status</th>
                            <th class="w-[120px] px-3 py-3 text-left text-[11px] font-semibold uppercase text-gray-500 dark:text-gray-400 sm:px-4">College</th>
                            <th class="w-[260px] px-3 py-3 text-left text-[11px] font-semibold uppercase text-gray-500 dark:text-gray-400 sm:px-4">Program</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        <template x-for="row in studentDetails.rows" :key="row.key">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/60">
                                <td class="px-3 py-2.5 font-semibold text-gray-900 dark:text-white sm:px-4" x-text="row.studentNumber"></td>
                                <td class="truncate px-3 py-2.5 text-gray-700 dark:text-gray-200 sm:px-4" x-text="row.name" :title="row.name"></td>
                                <td class="truncate px-3 py-2.5 text-gray-700 dark:text-gray-200 sm:px-4" x-text="row.scholarship" :title="row.scholarship"></td>
                                <td class="px-3 py-2.5 text-gray-700 dark:text-gray-200 sm:px-4" x-text="row.status"></td>
                                <td class="truncate px-3 py-2.5 text-gray-700 dark:text-gray-200 sm:px-4" x-text="row.college" :title="row.college"></td>
                                <td class="truncate px-3 py-2.5 text-gray-700 dark:text-gray-200 sm:px-4" x-text="row.program" :title="row.program"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
