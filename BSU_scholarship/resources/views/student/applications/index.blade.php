    <div class="mb-8">
        <div x-show="subTab === 'tracking'" style="display: none;">
            @include('student.partials.page-header', [
              'title' => 'Application Tracking',
              'subtitle' => 'Track the status and progress of your applications'
            ])
        </div>
    </div>

    <!-- Statistics Cards (Only visible in Tracking tab) -->
    <div x-show="subTab === 'tracking'" x-transition> 
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Applications</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $applications->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Approved</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $applications->where('status', 'approved')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Pending</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $applications->where('status', 'pending')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Claimed</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $applications->where('status', 'claimed')->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tracking Timeline View -->
        @include('student.partials.application_tracking', ['applicationTracking' => $applications])
    </div>

    <!-- Applications List (Only visible in Applied Scholarships tab) -->
    <div x-show="subTab === 'all'" class="space-y-6">
        @forelse($applications as $application)
            @if(!$application->scholarship)
                {{-- Skip applications where the scholarship has been deleted --}}
                @continue
            @endif
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 hover:shadow-xl transition-shadow duration-300">
                <!-- Application Header -->
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <h3 class="text-xl font-bold text-gray-900">
                                    {{ $application->scholarship->scholarship_name }}
                                </h3>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    @if($application->status === 'approved') bg-green-100 text-green-800
                                    @elseif($application->status === 'rejected') bg-red-100 text-red-800
                                    @elseif($application->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($application->status === 'claimed') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    @if($application->status === 'approved')
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Approved
                                    @elseif($application->status === 'rejected')
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        Rejected
                                    @elseif($application->status === 'pending')
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Pending
                                    @elseif($application->status === 'claimed')
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Claimed
                                    @else
                                        {{ ucfirst($application->status) }}
                                    @endif
                                </span>
                            </div>
                            <p class="text-gray-600 mb-4">
                                {{ \Illuminate\Support\Str::limit($application->scholarship->description, 200) }}
                            </p>
                        </div>
                        <div class="text-right ml-4">
                            <p class="text-sm text-gray-500 mb-1">Applied</p>
                            <p class="font-medium text-gray-900">{{ $application->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Application Details -->
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-600">Application</span>
                            </div>
                            <p class="text-lg font-semibold text-gray-900">Scholarship Application</p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-600">Deadline</span>
                            </div>
                            <p class="text-lg font-semibold text-gray-900">{{ \Carbon\Carbon::parse($application->scholarship->submission_deadline)->format('M d, Y') }}</p>
                        </div>

                        @if($application->scholarship->grant_amount)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-600">Grant Amount</span>
                            </div>
                            <p class="text-lg font-semibold text-gray-900">₱{{ number_format($application->scholarship->grant_amount, 2) }}</p>
                        </div>
                        @endif

                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-600">Grant Count</span>
                            </div>
                            <p class="text-lg font-semibold text-gray-900">{{ $application->grant_count }} grants</p>
                        </div>
                    </div>
                </div>

                <!-- Status Messages -->
                @if($application->status === 'approved')
                    <div class="mx-6 mb-6">
                        <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800">Application Approved!</h3>
                                    <div class="mt-2 text-sm text-green-700">
                                        <p>Congratulations! Your scholarship application has been approved. You can now proceed to claim your scholarship.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($application->status === 'rejected')
                    <div class="mx-6 mb-6">
                        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Application Not Approved</h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <p>Unfortunately, your scholarship application was not approved. You can apply for other available scholarships.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($application->status === 'pending')
                    <div class="mx-6 mb-6">
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">Application Under Review</h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <p>Your application is currently being reviewed by the SFAO. Please wait for the evaluation results.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($application->status === 'claimed')
                    <div class="mx-6 mb-6">
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">Scholarship Claimed</h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <p>Your scholarship has been successfully claimed. Thank you for using the BSU Scholarship System!</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- SFAO Remarks -->
                @if($application->remarks)
                    <div class="mx-6 mb-6">
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">SFAO Remarks</h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <p>{{ $application->remarks }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl">
                    <div class="flex flex-wrap gap-3">
                        <!-- Print Application Button -->
                        <button type="button" onclick="printApplication()"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd" />
                            </svg>
                            Print Application
                        </button>
                        
                        @if($application->status === 'pending')
                            <a href="{{ route('student.apply', ['scholarship_id' => $application->scholarship_id, 'resubmit' => 1]) }}" 
                               class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Resubmit
                            </a>
                        @endif

                        @if($application->status === 'approved')
                            <button type="button" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2"
                                    onclick="alert('Please contact the SFAO office to claim your scholarship.')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Claim Scholarship
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <div class="text-gray-400 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-medium text-gray-900 mb-2">No applications yet</h3>
                <p class="text-gray-600 mb-6">You haven't applied for any scholarships yet.</p>
                <a href="{{ route('student.dashboard', ['tab' => 'all_scholarships']) }}" 
                   class="bg-bsu-red hover:bg-bsu-redDark text-white px-6 py-3 rounded-lg font-medium transition">
                    Browse Scholarships
                </a>
            </div>
        @endforelse
    </div>

<script>
    // Print Application Function
    function printApplication() {
        // Open the PDF in a new window for printing
        window.open('{{ url("/student/print-application") }}', '_blank');
    }
</script>
