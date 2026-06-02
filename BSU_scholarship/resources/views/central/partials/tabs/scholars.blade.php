<!-- central/partials/tabs/scholars.blade.php -->

<div x-data="{ showModal: false, selectedScholar: null }" 
     x-show="tab === 'all_scholars' || tab === 'new_scholars' || tab === 'old_scholars'" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-cloak>

    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    <span x-show="tab === 'all_scholars'" class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd" />
                        </svg>
                        All Scholars
                    </span>
                    <span x-show="tab === 'new_scholars'" class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd" />
                        </svg>
                        New Scholars
                    </span>
                    <span x-show="tab === 'old_scholars'" class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd" />
                        </svg>
                        Old Scholars
                    </span>
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    <span x-show="tab === 'all_scholars'">Manage all accepted scholars across campuses</span>
                    <span x-show="tab === 'new_scholars'">Scholars who have not yet received any grant</span>
                    <span x-show="tab === 'old_scholars'">Continuing scholars with one or more grants</span>
                </p>
            </div>
        </div>
    </div>

    <!-- All Scholars Table -->
    <div x-show="tab === 'all_scholars'">
        <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 custom-scrollbar">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Campus</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Scholarship</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Grants</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($scholarsAll as $scholar)
                        @include('central.partials.components.scholar-row', ['scholar' => $scholar])
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                No scholars found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4 flex justify-center">
            {{ $scholarsAll->appends(['tab' => 'all_scholars'])->links('vendor.pagination.custom') }}
        </div>
    </div>

    <!-- New Scholars Table -->
    <div x-show="tab === 'new_scholars'">
        <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 custom-scrollbar">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Campus</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Scholarship</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Grants</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($scholarsNew as $scholar)
                         @include('central.partials.components.scholar-row', ['scholar' => $scholar])
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                No new scholars found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4 flex justify-center">
            {{ $scholarsNew->appends(['tab' => 'new_scholars'])->links('vendor.pagination.custom') }}
        </div>
    </div>

    <!-- Old Scholars Table -->
    <div x-show="tab === 'old_scholars'">
        <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 custom-scrollbar">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Campus</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Scholarship</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Grants</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($scholarsOld as $scholar)
                        @include('central.partials.components.scholar-row', ['scholar' => $scholar])
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                No old scholars found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4 flex justify-center">
            {{ $scholarsOld->appends(['tab' => 'old_scholars'])->links('vendor.pagination.custom') }}
        </div>
    </div>

    <!-- Scholar Details Modal -->
    <div x-show="showModal && selectedScholar" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
         @keydown.escape.window="showModal = false"
         @click.away="showModal = false"
         x-cloak>
        <div class="w-full max-w-3xl bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
             @click.stop>
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white" x-text="selectedScholar?.name || 'Scholar Details'"></h3>
                <button @click="showModal = false" 
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-5 max-h-[70vh] overflow-y-auto" x-show="selectedScholar">
                <!-- Basic Information -->
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Basic Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Campus</div>
                            <div class="text-sm text-gray-900 dark:text-white" x-text="selectedScholar?.campus || 'N/A'"></div>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Scholarship</div>
                            <div class="text-sm text-gray-900 dark:text-white" x-text="selectedScholar?.scholarship || 'N/A'"></div>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Program</div>
                            <div class="text-sm text-gray-900 dark:text-white" x-text="selectedScholar?.program || 'N/A'"></div>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Year Level</div>
                            <div class="text-sm text-gray-900 dark:text-white" x-text="selectedScholar?.year_level || 'N/A'"></div>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">GWA</div>
                            <div class="text-sm text-gray-900 dark:text-white" x-text="selectedScholar?.gwa || 'N/A'"></div>
                        </div>
                    </div>
                </div>

                <!-- Status & Type -->
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Scholar Status</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Type</div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold" 
                                  :class="selectedScholar?.type === 'new' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'"
                                  x-text="selectedScholar?.type ? selectedScholar.type.charAt(0).toUpperCase() + selectedScholar.type.slice(1) : 'N/A'"></span>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Status</div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                  :class="selectedScholar?.status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'"
                                  x-text="selectedScholar?.status ? selectedScholar.status.charAt(0).toUpperCase() + selectedScholar.status.slice(1) : 'N/A'"></span>
                        </div>
                    </div>
                </div>

                <!-- Grant Information -->
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Grant Information</h4>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700 text-center">
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Grants Received</div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white" x-text="selectedScholar?.grant_count || 0"></div>
                        </div>
                        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700 text-center">
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Total Received</div>
                            <div class="text-xl font-bold text-green-600 dark:text-green-400" 
                                 x-text="'₱' + (selectedScholar?.total_grant_received ? Number(selectedScholar.total_grant_received).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}) : '0')"></div>
                        </div>
                        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700 text-center">
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">GWA</div>
                            <div class="text-xl font-bold text-gray-900 dark:text-white" x-text="selectedScholar?.gwa || 'N/A'"></div>
                        </div>
                    </div>
                </div>

                <!-- Dates -->
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Scholarship Period</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Start Date</div>
                            <div class="text-sm text-gray-900 dark:text-white" x-text="selectedScholar?.start_date || 'N/A'"></div>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">End Date</div>
                            <div class="text-sm text-gray-900 dark:text-white" x-text="selectedScholar?.end_date || 'N/A'"></div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div x-show="selectedScholar?.notes" class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Notes</h4>
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                        <p class="text-sm text-gray-700 dark:text-gray-300" x-text="selectedScholar?.notes || ''"></p>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 flex justify-end gap-3">
                <button @click="showModal = false" 
                        class="px-4 py-2 text-sm bg-white hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg border border-gray-300 dark:border-gray-600 transition-colors font-medium">
                    Close
                </button>
            </div>
        </div>
    </div>

</div>


