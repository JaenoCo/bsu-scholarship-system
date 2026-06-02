<div x-cloak>
    @if($applications->isEmpty())
        <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 border-dashed">
            <div class="text-gray-400 dark:text-gray-500 mb-4">
                <svg class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No Applications Found</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">You haven't applied for any scholarships yet.</p>
            <button @click="tab = 'scholarships'; subTab = 'all'" 
               class="inline-flex items-center px-6 py-3 bg-bsu-red hover:bg-bsu-redDark text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200">
                Browse Scholarships
            </button>
        </div>
    @else


        <!-- Application List (Unified Layout) -->
        <div class="grid grid-cols-1 gap-6">
            @foreach($applicationTracking as $application)
                <div x-data="{ showModal: false }" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-all duration-300">
                    
                    <!-- Card Header / Main Content -->
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <!-- Left: Info -->
                            <div>
                                <div class="flex items-center gap-3">
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                        {{ $application->scholarship->scholarship_name }}
                                    </h3>
                                    <!-- Status Badge -->
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                                        @if($application->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200
                                        @elseif($application->status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200
                                        @elseif($application->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200
                                        @elseif($application->status === 'claimed') bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-200
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                        @endif">
                                        {{ ucfirst($application->status) }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    Applied on {{ $application->created_at?->format('M d, Y') }}
                                </p>
                            </div>

                            <!-- Right: Actions -->
                            <div class="flex items-center gap-3">
                                <button @click="showModal = true" 
                                        class="inline-flex items-center px-4 py-2 bg-bsu-red border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    View Progress
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Modal -->
                    <div x-show="showModal" 
                         class="fixed inset-0 z-50 overflow-y-auto" 
                         aria-labelledby="modal-title" 
                         role="dialog" 
                         aria-modal="true"
                         x-cloak>
                        
                        <!-- Backdrop -->
                        <div x-show="showModal"
                             x-transition:enter="ease-out duration-300"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                             @click="showModal = false"></div>

                        <!-- Modal Panel -->
                        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
                            <div x-show="showModal"
                                 x-transition:enter="ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                 x-transition:leave="ease-in duration-200"
                                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                 class="relative bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-4xl sm:w-full">
                                
                                <!-- Modal Header -->
                                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 flex justify-between items-center border-b border-gray-200 dark:border-gray-600">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                        Application Details
                                    </h3>
                                    <button @click="showModal = false" type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                                        <span class="sr-only">Close</span>
                                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Modal Body -->
                                <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4 max-h-[70vh] overflow-y-auto">
                                    <!-- Application Header Info -->
                                    <div class="flex items-center justify-between mb-6">
                                        <div class="flex items-center space-x-4">
                                            <div class="flex-shrink-0">
                                                <div class="h-12 w-12 rounded-full bg-bsu-red flex items-center justify-center">
                                                    <span class="text-lg font-bold text-white">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                                            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                                                        </svg>
                                                    </span>
                                                </div>
                                            </div>
                                            <div>
                                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                                    {{ $application->scholarship->scholarship_name }}
                                                </h3>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    Applied on {{ $application->created_at?->format('M d, Y') }}
                                                </p>
                                            </div>
                                        </div>
                                        <span class="inline-flex px-3 py-1 text-sm font-bold uppercase tracking-wider rounded-full
                                            {{ $application->status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                               ($application->status === 'rejected' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 
                                               'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200') }}">
                                            {{ ucfirst($application->status) }}
                                        </span>
                                    </div>

                                    <!-- Progress Timeline -->
                                    <div class="relative mb-8">
                                        <div class="flex items-center justify-between">
                                            <!-- Step 1: Application Submitted -->
                                            <div class="flex flex-col items-center relative z-10">
                                                <div class="flex items-center justify-center w-10 h-10 rounded-full border-4 border-white dark:border-gray-800
                                                    {{ $application->created_at ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-500' }}">
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </div>
                                                <div class="mt-2 text-center">
                                                    <p class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wide">Submitted</p>
                                                    @if($application->created_at)
                                                        <p class="text-xs text-green-600 dark:text-green-400">{{ $application->created_at?->format('M d') }}</p>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Connector Line -->
                                            <div class="flex-1 h-1 mx-2 -mt-6
                                                {{ $application->status !== 'pending' ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-700' }}"></div>

                                            <!-- Step 2: SFAO Review -->
                                            <div class="flex flex-col items-center relative z-10">
                                                <div class="flex items-center justify-center w-10 h-10 rounded-full border-4 border-white dark:border-gray-800
                                                    {{ $application->status === 'approved' || $application->status === 'rejected' ? 'bg-green-500 text-white' : 
                                                       ($application->status === 'pending' ? 'bg-yellow-500 text-white' : 'bg-gray-300 text-gray-500') }}">
                                                    @if($application->status === 'approved' || $application->status === 'rejected')
                                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    @elseif($application->status === 'pending')
                                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    @else
                                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    @endif
                                                </div>
                                                <div class="mt-2 text-center">
                                                    <p class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wide">SFAO Review</p>
                                                    @if($application->status === 'approved' || $application->status === 'rejected')
                                                        <p class="text-xs text-green-600 dark:text-green-400">Completed</p>
                                                    @elseif($application->status === 'pending')
                                                        <p class="text-xs text-yellow-600 dark:text-yellow-400">In Progress</p>
                                                    @else
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">Pending</p>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Connector Line -->
                                            <div class="flex-1 h-1 mx-2 -mt-6
                                                {{ $application->status === 'approved' ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-700' }}"></div>

                                            <!-- Step 3: Central Review -->
                                            <div class="flex flex-col items-center relative z-10">
                                                <div class="flex items-center justify-center w-10 h-10 rounded-full border-4 border-white dark:border-gray-800
                                                    {{ $application->scholar_status === 'selected' ? 'bg-green-500 text-white' : 
                                                       ($application->status === 'rejected' ? 'bg-red-500 text-white' : 'bg-gray-300 text-gray-500') }}">
                                                    @if($application->scholar_status === 'selected')
                                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    @elseif($application->status === 'rejected')
                                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    @else
                                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    @endif
                                                </div>
                                                <div class="mt-2 text-center">
                                                    <p class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wide">Central Review</p>
                                                    @if($application->scholar_status === 'selected')
                                                        <p class="text-xs text-green-600 dark:text-green-400">Completed</p>
                                                    @elseif($application->status === 'rejected')
                                                        <p class="text-xs text-red-600 dark:text-red-400">Completed</p>
                                                    @else
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">Pending</p>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Connector Line -->
                                            <div class="flex-1 h-1 mx-2 -mt-6
                                                {{ $application->scholar_status === 'selected' || $application->status === 'rejected' ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-700' }}"></div>

                                            <!-- Step 4: Final Decision -->
                                            <div class="flex flex-col items-center relative z-10">
                                                <div class="flex items-center justify-center w-10 h-10 rounded-full border-4 border-white dark:border-gray-800
                                                    {{ $application->scholar_status === 'selected' || $application->status === 'rejected' ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-500' }}">
                                                    @if($application->scholar_status === 'selected')
                                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    @elseif($application->status === 'rejected')
                                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    @else
                                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    @endif
                                                </div>
                                                <div class="mt-2 text-center">
                                                    <p class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wide">Decision</p>
                                                    @if($application->scholar_status === 'selected')
                                                        <p class="text-xs text-green-600 dark:text-green-400">Selected</p>
                                                    @elseif($application->status === 'rejected')
                                                        <p class="text-xs text-red-600 dark:text-red-400">Not Selected</p>
                                                    @else
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">Pending</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Evaluation Status Box -->
                                    @if($application->status === 'pending' || $application->status === 'approved' || $application->status === 'rejected' || $application->scholar_status === 'selected')
                                    <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border-l-4 border-blue-400">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-800 rounded-full flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <h4 class="text-sm font-bold text-blue-900 dark:text-blue-100">Current Status</h4>
                                                <div class="mt-2 text-sm text-blue-800 dark:text-blue-200">
                                                    @if($application->scholar_status === 'selected')
                                                        <p class="font-medium">Selected as Scholar</p>
                                                        <p class="mt-1 text-xs">Selected on {{ $application->scholar_selected_at?->format('M d, Y \a\t h:i A') }}</p>
                                                    @elseif($application->status === 'rejected')
                                                        <p class="font-medium">Not Selected</p>
                                                        <p class="mt-1 text-xs">Final decision made on {{ $application->updated_at?->format('M d, Y \a\t h:i A') }}</p>
                                                    @elseif($application->status === 'approved')
                                                        <p class="font-medium">SFAO Approved - Awaiting Central Review</p>
                                                        <p class="mt-1 text-xs">SFAO approved on {{ $application->updated_at?->format('M d, Y \a\t h:i A') }}. Central is reviewing for final selection.</p>
                                                    @else
                                                        <p class="font-medium">Under SFAO Review</p>
                                                        <p class="mt-1 text-xs">SFAO is currently evaluating your application and documents.</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Application Details Grid -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                            <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-2">Scholarship Info</h4>
                                            <div class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                                                <div class="flex justify-between">
                                                    <span class="font-medium">Grant Amount:</span>
                                                    <span>₱{{ number_format((float) $application->scholarship->grant_amount, 2) }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="font-medium">Deadline:</span>
                                                    <span>{{ \Carbon\Carbon::parse($application->scholarship->deadline)?->format('M d, Y') }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="font-medium">Min. GWA:</span>
                                                    <span>{{ $application->scholarship->getGwaRequirement() ?? 'Not specified' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                            <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-2">Application Info</h4>
                                            <div class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                                                <div class="flex justify-between">
                                                    <span class="font-medium">App ID:</span>
                                                    <span>#{{ str_pad($application->id, 6, '0', STR_PAD_LEFT) }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="font-medium">Submitted:</span>
                                                    <span>{{ $application->created_at?->format('M d, Y') }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="font-medium">Last Update:</span>
                                                    <span>{{ $application->updated_at?->format('M d, Y') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Footer -->
                                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 flex justify-end">
                                    <button @click="showModal = false" type="button" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bsu-red sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
