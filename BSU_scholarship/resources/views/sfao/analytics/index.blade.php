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
            
            <!-- Scholarship Status Distribution Metrics -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                 <!-- Total Applicants -->
                 <div x-on:click="openStudentDetails('comparison', 'applicants')"
                      x-on:keydown.enter.prevent="openStudentDetails('comparison', 'applicants')"
                      x-on:keydown.space.prevent="openStudentDetails('comparison', 'applicants')"
                      role="button"
                      tabindex="0"
                      class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 text-center border border-blue-100 dark:border-blue-800 cursor-pointer hover:ring-1 hover:ring-blue-300 transition">
                     <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase">Total Applicants</p>
                     <p class="text-xl font-bold text-blue-700 dark:text-blue-300" x-text="filteredData.scholarshipDistribution?.applicantsInCampus || 0"></p>
                 </div>
                 <!-- Approved Applicants (Scholars) -->
                 <div x-on:click="openStudentDetails('comparison', 'scholars')"
                      x-on:keydown.enter.prevent="openStudentDetails('comparison', 'scholars')"
                      x-on:keydown.space.prevent="openStudentDetails('comparison', 'scholars')"
                      role="button"
                      tabindex="0"
                      class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 text-center border border-green-100 dark:border-green-800 cursor-pointer hover:ring-1 hover:ring-green-300 transition">
                     <p class="text-xs font-semibold text-green-600 dark:text-green-400 uppercase">Approved Applicants (Scholars)</p>
                     <p class="text-xl font-bold text-green-700 dark:text-green-300" x-text="filteredData.scholarshipDistribution?.approvedApplicants || 0"></p>
                 </div>
            </div>

            <!-- Scholarship Ranking (by number of applicants) -->
            <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg border border-gray-100 dark:border-gray-700 p-4 mb-6">
                <h4 class="text-sm font-bold text-gray-700 dark:text-gray-200 mb-3 text-center uppercase tracking-wide">Scholarship Rankings (by Applicants)</h4>
                <template x-if="!filteredData.scholarshipDistribution?.ranking || filteredData.scholarshipDistribution.ranking.length === 0">
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-2">No applicant data available for this selection.</p>
                </template>
                <div x-show="filteredData.scholarshipDistribution?.ranking && filteredData.scholarshipDistribution.ranking.length > 0"
                     class="relative w-full" style="height: 220px;">
                    <canvas id="sfaoScholarshipRankingChart"></canvas>
                </div>
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
            <template x-if="subTab === 'applicants'">
                <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
                     <!-- Total -->
                     <div x-on:click="openStudentDetails('total')"
                          x-on:keydown.enter.prevent="openStudentDetails('total')"
                          x-on:keydown.space.prevent="openStudentDetails('total')"
                          role="button"
                          tabindex="0"
                          class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 text-center border border-gray-100 dark:border-gray-600 cursor-pointer hover:ring-1 hover:ring-gray-300 transition">
                         <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Total</p>
                         <p class="text-xl font-bold text-gray-900 dark:text-white" x-text="filteredData.counts?.total || 0"></p>
                     </div>
                     <!-- Approved -->
                     <div x-on:click="openStudentDetails('approved')"
                          x-on:keydown.enter.prevent="openStudentDetails('approved')"
                          x-on:keydown.space.prevent="openStudentDetails('approved')"
                          role="button"
                          tabindex="0"
                          class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 text-center border border-green-100 dark:border-green-800 cursor-pointer hover:ring-1 hover:ring-green-300 transition">
                         <p class="text-xs font-semibold text-green-600 dark:text-green-400 uppercase">Approved</p>
                         <p class="text-xl font-bold text-green-700 dark:text-green-300" x-text="filteredData.counts?.approved || 0"></p>
                     </div>
                     <!-- Rejected -->
                     <div x-on:click="openStudentDetails('rejected')"
                          x-on:keydown.enter.prevent="openStudentDetails('rejected')"
                          x-on:keydown.space.prevent="openStudentDetails('rejected')"
                          role="button"
                          tabindex="0"
                          class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3 text-center border border-red-100 dark:border-red-800 cursor-pointer hover:ring-1 hover:ring-red-300 transition">
                         <p class="text-xs font-semibold text-red-600 dark:text-red-400 uppercase">Rejected</p>
                         <p class="text-xl font-bold text-red-700 dark:text-red-300" x-text="filteredData.counts?.rejected || 0"></p>
                     </div>
                     <!-- Active / Pending -->
                     <div x-on:click="openStudentDetails('active')"
                          x-on:keydown.enter.prevent="openStudentDetails('active')"
                          x-on:keydown.space.prevent="openStudentDetails('active')"
                          role="button"
                          tabindex="0"
                          class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-3 text-center border border-yellow-100 dark:border-yellow-800 cursor-pointer hover:ring-1 hover:ring-yellow-300 transition">
                         <p class="text-xs font-semibold text-yellow-600 dark:text-yellow-400 uppercase">Pending/In Progress</p>
                         <p class="text-xl font-bold text-yellow-700 dark:text-yellow-300" x-text="filteredData.counts?.active || 0"></p>
                     </div>
                     <!-- Rate -->
                     <div x-on:click="openStudentDetails('approvalRate')"
                          x-on:keydown.enter.prevent="openStudentDetails('approvalRate')"
                          x-on:keydown.space.prevent="openStudentDetails('approvalRate')"
                          role="button"
                          tabindex="0"
                          class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 text-center border border-blue-100 dark:border-blue-800 cursor-pointer hover:ring-1 hover:ring-blue-300 transition">
                         <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase">Approval Rate</p>
                         <p class="text-xl font-bold text-blue-700 dark:text-blue-300" x-text="(filteredData.counts?.approvalRate || '0.0') + '%'"></p>
                     </div>
                </div>
            </template>

            <template x-if="subTab === 'scholars'">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                     <!-- Total Scholars -->
                     <div x-on:click="openStudentDetails('total')"
                          x-on:keydown.enter.prevent="openStudentDetails('total')"
                          x-on:keydown.space.prevent="openStudentDetails('total')"
                          role="button"
                          tabindex="0"
                          class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 text-center border border-gray-100 dark:border-gray-600 cursor-pointer hover:ring-1 hover:ring-gray-300 transition">
                         <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Total Scholars</p>
                         <p class="text-xl font-bold text-gray-900 dark:text-white" x-text="filteredData.counts?.total || 0"></p>
                     </div>
                     <!-- New Scholars -->
                     <div x-on:click="openStudentDetails('newScholars')"
                          x-on:keydown.enter.prevent="openStudentDetails('newScholars')"
                          x-on:keydown.space.prevent="openStudentDetails('newScholars')"
                          role="button"
                          tabindex="0"
                          class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 text-center border border-blue-100 dark:border-blue-800 cursor-pointer hover:ring-1 hover:ring-blue-300 transition">
                         <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase">New Scholars</p>
                         <p class="text-xl font-bold text-blue-700 dark:text-blue-300" x-text="filteredData.counts?.newScholars || 0"></p>
                     </div>
                     <!-- Continuing / Old Scholars -->
                     <div x-on:click="openStudentDetails('oldScholars')"
                          x-on:keydown.enter.prevent="openStudentDetails('oldScholars')"
                          x-on:keydown.space.prevent="openStudentDetails('oldScholars')"
                          role="button"
                          tabindex="0"
                          class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 text-center border border-green-100 dark:border-green-800 cursor-pointer hover:ring-1 hover:ring-green-300 transition">
                         <p class="text-xs font-semibold text-green-600 dark:text-green-400 uppercase">Continuing Scholars</p>
                         <p class="text-xl font-bold text-green-700 dark:text-green-300" x-text="filteredData.counts?.oldScholars || 0"></p>
                     </div>
                </div>
            </template>

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

        <!-- Student Details Modal -->
        <div x-show="studentDetails.open" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-black/50" @click="closeStudentDetails()"></div>
            <div class="relative max-w-4xl w-full bg-white dark:bg-gray-900 rounded-lg shadow-lg overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="studentDetails.title"></h3>
                    <button @click="closeStudentDetails()" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div class="p-4 max-h-[60vh] overflow-auto">
                    <template x-if="!studentDetails.rows || studentDetails.rows.length === 0">
                        <div class="text-center text-sm text-gray-500 dark:text-gray-400 py-6">No students to display for this selection.</div>
                    </template>

                    <template x-if="studentDetails.rows && studentDetails.rows.length > 0">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-800 sticky top-0">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Student #</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Name</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Campus</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">College</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Program</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Scholarship</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-800">
                                <template x-for="row in studentDetails.rows" :key="row.key">
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                        <td class="px-3 py-2 text-gray-800 dark:text-gray-200" x-text="row.studentNumber"></td>
                                        <td class="px-3 py-2 text-gray-800 dark:text-gray-200" x-text="row.name"></td>
                                        <td class="px-3 py-2 text-gray-800 dark:text-gray-200" x-text="row.campus"></td>
                                        <td class="px-3 py-2 text-gray-800 dark:text-gray-200" x-text="row.college"></td>
                                        <td class="px-3 py-2 text-gray-800 dark:text-gray-200" x-text="row.program"></td>
                                        <td class="px-3 py-2 text-gray-800 dark:text-gray-200" x-text="row.scholarship"></td>
                                        <td class="px-3 py-2 text-gray-800 dark:text-gray-200" x-text="row.status"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </template>
                </div>
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                    <button @click="closeStudentDetails()" class="px-4 py-2 rounded-md bg-bsu-red text-white">Close</button>
                </div>
            </div>
        </div>
    </div>