<!-- Unified Applications Tab -->
<div x-data="{ 
    init() {
        // Initialize subTab if not set
        if (!this.subTab || (this.subTab !== 'all' && this.subTab !== 'tracking')) {
             this.subTab = 'all';
        }
    }
}" x-init="init()">

    <!-- Unified Header -->
    <div class="bg-bsu-red dark:bg-red-900 rounded-xl shadow-lg p-6 mb-6 relative overflow-hidden">

        
        <div class="relative z-10 text-white">
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <span x-show="subTab === 'all'" class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    My Applications
                </span>
                <span x-show="subTab === 'tracking'" class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Application Tracking
                </span>
            </h2>
            <p class="text-red-100 mt-1 font-medium">
                <span x-show="subTab === 'all'">Track the status of your scholarship applications.</span>
                <span x-show="subTab === 'tracking'">Monitor the progress of your scholarship applications</span>
            </p>
        </div>
    </div>

    <!-- Shared Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Applications -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 flex items-center">
            <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Applications</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $applications->count() }}</p>
            </div>
        </div>

        <!-- Approved -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 flex items-center">
            <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Approved</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $applications->where('status', 'approved')->count() }}</p>
            </div>
        </div>

        <!-- Pending -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 flex items-center">
            <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
                <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $applications->where('status', 'pending')->count() }}</p>
            </div>
        </div>

        <!-- Claimed -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 flex items-center">
            <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Claimed</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $applications->where('status', 'claimed')->count() }}</p>
            </div>
        </div>
    </div>

    <!-- My Applications Content -->
    <div x-show="subTab === 'all'"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100">
         
        <!-- Applications List -->
        <div class="grid grid-cols-1 gap-6">
            @forelse($applications as $application)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-all duration-300">
                    <!-- Header -->
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-3">
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                        {{ $application->scholarship->scholarship_name ?? 'Scholarship Unavailable' }}
                                    </h3>
                                    <!-- Status Badge -->
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                                        @if($application->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200
                                        @elseif($application->status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200
                                        @elseif($application->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200
                                        @elseif($application->status === 'claimed') bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-200
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                        @endif">
                                        @if($application->status === 'approved')
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Approved
                                        @elseif($application->status === 'rejected')
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            Rejected
                                        @elseif($application->status === 'pending')
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            Pending
                                        @elseif($application->status === 'claimed')
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            Claimed
                                        @else
                                            {{ ucfirst($application->status) }}
                                        @endif
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    Applied on {{ $application->created_at->format('M d, Y') }}
                                </p>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="flex items-center gap-3">


                                @if($application->status === 'pending')
                                    <form method="POST" action="{{ route('student.withdraw') }}" class="inline" onsubmit="return confirm('Are you sure you want to withdraw this application?');">
                                        @csrf
                                        <input type="hidden" name="scholarship_id" value="{{ $application->scholarship_id }}">
                                        <button type="submit" 
                                                class="inline-flex items-center px-4 py-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm font-medium text-red-700 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors shadow-sm">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Withdraw
                                        </button>
                                    </form>

                                    <a href="{{ route('student.apply', ['scholarship_id' => $application->scholarship_id, 'resubmit' => 1]) }}" 
                                       class="inline-flex items-center px-4 py-2 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg text-sm font-medium text-yellow-700 dark:text-yellow-400 hover:bg-yellow-100 dark:hover:bg-yellow-900/40 transition-colors shadow-sm">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Resubmit
                                    </a>
                                @endif

                                @if($application->status === 'approved')
                                    <button onclick="alert('Please contact the SFAO office to claim your scholarship.')"
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Claim
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <!-- Deadline -->
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Submission Deadline</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    @if($application->scholarship && $application->scholarship->submission_deadline)
                                        {{ \Carbon\Carbon::parse($application->scholarship->submission_deadline)->format('M d, Y') }}
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>
                            
                            <!-- Amount -->
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Grant Amount</p>
                                <p class="text-sm font-semibold text-green-600 dark:text-green-400">
                                    @if($application->scholarship && $application->scholarship->grant_amount)
                                        ₱{{ number_format($application->scholarship->grant_amount, 2) }}
                                    @else
                                        TBD
                                    @endif
                                </p>
                            </div>

                            <!-- Grant Count -->
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Grant Count</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $application->grant_count }} grants
                                </p>
                            </div>
                        </div>

                        <!-- SFAO Remarks -->
                        @if($application->remarks)
                            <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 dark:border-blue-500 p-4 rounded-r-lg">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400 dark:text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">SFAO Remarks</h3>
                                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                                            <p>{{ $application->remarks }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 border-dashed">
                    <div class="text-gray-400 dark:text-gray-500 mb-4">
                        <svg class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No Applications Found</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">You haven't applied for any scholarships yet.</p>
                    <button @click="tab = 'scholarships'" 
                       class="inline-flex items-center px-6 py-3 bg-bsu-red hover:bg-bsu-redDark text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200">
                        Browse Scholarships
                    </button>
                </div>
            @endforelse
        </div>
    </div>
    
    <!-- Application Tracking Content -->
    <div x-show="subTab === 'tracking'"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100">
        @include('student.partials.application_tracking')
    </div>
</div>
