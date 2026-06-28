<div x-show="showModal" 
     style="display: none;" 
     class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-gray-900/60 backdrop-blur-sm" 
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true"
     @click.self="closeModal()">
    
    <!-- Modal Panel -->
    <div x-show="showModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-8 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-8 scale-95" 
         class="relative w-full max-w-2xl bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden transform">
        
        <!-- Header -->
        <div class="bg-bsu-red p-6 text-white relative">
            <div class="flex items-center gap-4">
                <!-- Profile Picture -->
                <div class="flex-shrink-0 h-16 w-16 rounded-full overflow-hidden border-2 border-white bg-white">
                    <template x-if="selectedApplicant?.profile_picture">
                        <img :src="selectedApplicant.profile_picture_url" 
                             alt="Profile" 
                             class="h-full w-full object-cover">
                    </template>
                    <template x-if="!selectedApplicant?.profile_picture">
                        <div class="h-full w-full flex items-center justify-center bg-gray-200 text-gray-500 font-bold text-xl">
                            <span x-text="selectedApplicant?.name?.charAt(0).toUpperCase() || '?'"></span>
                        </div>
                    </template>
                </div>

                <!-- Name and Email -->
                <div>
                    <h3 class="text-2xl font-bold" id="modal-title" x-text="selectedApplicant?.name || 'Applicant Details'"></h3>
                    <p class="text-white/80 text-sm mt-1 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <span x-text="selectedApplicant?.email || ''"></span>
                    </p>
                </div>
            </div>
            
            <button @click="closeModal()" class="absolute top-4 right-4 text-white hover:text-gray-200 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6">
            
            <!-- Personal Information Section -->
            <div class="mb-6">
                <h4 class="text-sm uppercase tracking-wider text-gray-500 dark:text-gray-400 font-bold mb-4 border-b pb-2 dark:border-gray-700">Personal Information</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 uppercase">SR Code</span>
                        <span class="block text-base font-medium text-gray-900 dark:text-white" x-text="selectedApplicant?.sr_code || 'N/A'"></span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 uppercase">Contact Number</span>
                        <span class="block text-base font-medium text-gray-900 dark:text-white" x-text="selectedApplicant?.contact_number || 'N/A'"></span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 uppercase">Sex</span>
                        <span class="block text-base font-medium text-gray-900 dark:text-white capitalize" x-text="selectedApplicant?.sex || 'N/A'"></span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 uppercase">Birthdate</span>
                        <span class="block text-base font-medium text-gray-900 dark:text-white" x-text="formatDate(selectedApplicant?.birthdate)"></span>
                    </div>
                </div>
            </div>

            <!-- Academic Information Section -->
            <div>
                <h4 class="text-sm uppercase tracking-wider text-gray-500 dark:text-gray-400 font-bold mb-4 border-b pb-2 dark:border-gray-700">Academic Information</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="sm:col-span-2">
                        <span class="block text-xs text-gray-500 dark:text-gray-400 uppercase">College</span>
                        <span class="block text-base font-medium text-gray-900 dark:text-white" x-text="selectedApplicant?.college || 'N/A'"></span>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="block text-xs text-gray-500 dark:text-gray-400 uppercase">Program</span>
                        <span class="block text-base font-medium text-gray-900 dark:text-white" x-text="selectedApplicant?.program || 'N/A'"></span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 uppercase">Year Level</span>
                        <span class="block text-base font-medium text-gray-900 dark:text-white" x-text="selectedApplicant?.year_level || 'N/A'"></span>
                    </div>
                </div>
            </div>

            <!-- Documents Summary Section -->
            <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                    <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Uploaded Documents</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white" x-text="selectedApplicant?.documents_count ?? 0"></p>
                </div>
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                    <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Last Upload</p>
                    <p class="mt-2 text-sm text-gray-900 dark:text-white" x-text="selectedApplicant?.last_uploaded ? new Date(selectedApplicant.last_uploaded).toLocaleDateString() : 'N/A'"></p>
                </div>
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">
                    <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Application Status</p>
                    <p class="mt-2 text-sm font-medium text-gray-900 dark:text-white" x-text="selectedApplicant?.application_status?.join(', ') || 'N/A'"></p>
                </div>
            </div>

            <!-- Scholarships Information Section -->
            <div class="mt-6">
                 <h4 class="text-sm uppercase tracking-wider text-gray-500 dark:text-gray-400 font-bold mb-4 border-b pb-2 dark:border-gray-700">Scholarship Applications</h4>
                 <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Scholarship</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Grant Count</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                             <template x-for="app in selectedApplicant?.applications_with_types || []" :key="app.id">
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="app.scholarship_name"></td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full capitalize"
                                              :class="{
                                                  'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': app.status === 'approved',
                                                  'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': app.status === 'rejected',
                                                  'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': app.status === 'pending',
                                                  'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200': app.status === 'in_progress'
                                              }"
                                              x-text="app.status.replace('_', ' ')">
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                         <span class="inline-flex px-2 py-1 text-xs font-medium rounded capitalize"
                                              :class="app.grant_count_badge_color"
                                              x-text="app.grant_count_display">
                                         </span>
                                    </td>
                                </tr>
                             </template>
                             <template x-if="!selectedApplicant?.applications_with_types || selectedApplicant.applications_with_types.length === 0">
                                <tr>
                                    <td colspan="3" class="px-4 py-2 text-center text-sm text-gray-500 dark:text-gray-400">No applications found.</td>
                                </tr>
                             </template>
                        </tbody>
                    </table>
                 </div>
            </div>

            <!-- Footer Action -->
            <div class="mt-8 flex justify-end">
                <button type="button" 
                        class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition font-medium" 
                        @click="closeModal()">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
