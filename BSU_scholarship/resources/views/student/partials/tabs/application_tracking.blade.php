<div x-show="tab === 'tracking'" x-transition x-cloak class="px-4 py-6">
    <div class="mb-6">
        <h2 class="flex items-center gap-2 text-2xl font-bold text-bsu-red dark:text-red-400 mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Application Tracking
        </h2>
        <p class="text-gray-600 dark:text-gray-300">Monitor the progress of your scholarship applications</p>
    </div>

    @if($applications->isEmpty())
        <div class="text-center py-12">
            <div class="text-gray-400 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-400 mb-2">No Applications Found</h3>
            <p class="text-gray-500 dark:text-gray-500 mb-4">You haven't applied to any scholarships yet.</p>
            <button @click="tab = 'scholarships'" 
               class="inline-flex items-center px-4 py-2 bg-bsu-red text-white rounded-lg hover:bg-red-700 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Browse Scholarships
            </button>
        </div>
    @else
        <!-- Application Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Applications</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $applications->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $applications->where('status', 'pending')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Approved</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $applications->where('status', 'approved')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 dark:bg-red-900 rounded-lg">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Rejected</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $applications->where('status', 'rejected')->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Application Progress Cards -->
        <div class="space-y-6">
            @foreach($applications as $application)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                    <!-- Application Header -->
                    <div class="bg-gradient-to-r from-bsu-red to-red-600 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-white">{{ $application->scholarship->scholarship_name }}</h3>
                                <p class="text-red-100 text-sm">Applied: {{ $application->created_at?->format('M d, Y') }}</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full
                                    {{ $application->status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                       ($application->status === 'rejected' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 
                                       'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200') }}">
                                    {{ ucfirst($application->status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Steps -->
                    <div class="p-6">
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Application Progress</h4>
                            
                        <!-- Enhanced Progress Steps -->
                        <div class="relative">
                            <!-- Step 1: Application Submitted -->
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h5 class="text-sm font-medium text-gray-900 dark:text-white">Application Submitted</h5>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Your application has been submitted successfully</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $application->created_at?->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>

                                <!-- Connecting Line -->
                                <div class="absolute left-4 top-8 w-0.5 h-8 bg-gray-300 dark:bg-gray-600"></div>

                                <!-- Step 2: Documents Uploaded & Evaluation Status -->
                                <div class="flex items-center mb-4">
                                    <div class="flex-shrink-0 w-8 h-8 {{ $application->has_documents ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600' }} rounded-full flex items-center justify-center">
                                        @if($application->has_documents)
                                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <h5 class="text-sm font-medium text-gray-900 dark:text-white">Documents Uploaded & Evaluated</h5>
                                        @if($application->has_documents)
                                            <div class="mt-2">
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $application->documents_count }} documents uploaded</span>
                                                    <span class="text-xs text-gray-400 dark:text-gray-500">Last updated: {{ $application->last_document_upload ? $application->last_document_upload?->format('M d, Y h:i A') : 'N/A' }}</span>
                                                </div>
                                                
                                                <!-- Document Evaluation Status -->
                                                <div class="grid grid-cols-3 gap-2 text-xs">
                                                    <div class="flex items-center">
                                                        <div class="w-2 h-2 bg-green-500 rounded-full mr-1"></div>
                                                        <span class="text-green-600 dark:text-green-400">{{ $application->approved_documents_count ?? 0 }} Approved</span>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <div class="w-2 h-2 bg-yellow-500 rounded-full mr-1"></div>
                                                        <span class="text-yellow-600 dark:text-yellow-400">{{ $application->pending_documents_count ?? 0 }} Pending</span>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <div class="w-2 h-2 bg-red-500 rounded-full mr-1"></div>
                                                        <span class="text-red-600 dark:text-red-400">{{ $application->rejected_documents_count ?? 0 }} Rejected</span>
                                                    </div>
                                                </div>
                                                
                                                @if(($application->rejected_documents_count ?? 0) > 0)
                                                    <div class="mt-2 p-2 bg-red-50 dark:bg-red-900/20 rounded-md">
                                                        <p class="text-xs text-red-600 dark:text-red-400">
                                                            <strong>Action Required:</strong> Some documents were rejected. Please review and resubmit.
                                                        </p>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <p class="text-sm text-red-500 dark:text-red-400">Documents not uploaded yet</p>
                                            <a href="{{ route('student.upload-documents', $application->scholarship_id) }}" 
                                               class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                                Upload documents now
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                <!-- Connecting Line -->
                                <div class="absolute left-4 top-16 w-0.5 h-8 bg-gray-300 dark:bg-gray-600"></div>

                                <!-- Step 3: SFAO 4-Stage Evaluation -->
                                <div class="flex items-center mb-4">
                                    <div class="flex-shrink-0 w-8 h-8 {{ $application->status !== 'pending' ? 'bg-green-500' : ($application->has_documents ? 'bg-yellow-500' : 'bg-gray-300 dark:bg-gray-600') }} rounded-full flex items-center justify-center">
                                        @if($application->status === 'approved' || $application->status === 'rejected')
                                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        @elseif($application->has_documents)
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <h5 class="text-sm font-medium text-gray-900 dark:text-white">SFAO 4-Stage Evaluation</h5>
                                        @if($application->status === 'approved')
                                            <p class="flex items-center gap-1 text-sm text-green-600 dark:text-green-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Application approved by SFAO
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">All 4 evaluation stages completed successfully</p>
                                        @elseif($application->status === 'rejected')
                                            <p class="flex items-center gap-1 text-sm text-red-600 dark:text-red-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Application rejected by SFAO
                                            </p>
                                            @if($application->remarks)
                                                <p class="text-xs text-red-500 dark:text-red-400 mt-1">Reason: {{ $application->remarks }}</p>
                                            @endif
                                        @elseif($application->has_documents)
                                            <p class="flex items-center gap-1 text-sm text-yellow-600 dark:text-yellow-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Under SFAO evaluation
                                            </p>
                                            <div class="mt-2">
                                                <div class="grid grid-cols-4 gap-1 text-xs">
                                                    <div class="text-center p-1 rounded {{ $application->evaluation_stage >= 1 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                                        Stage 1
                                                    </div>
                                                    <div class="text-center p-1 rounded {{ $application->evaluation_stage >= 2 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                                        Stage 2
                                                    </div>
                                                    <div class="text-center p-1 rounded {{ $application->evaluation_stage >= 3 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                                        Stage 3
                                                    </div>
                                                    <div class="text-center p-1 rounded {{ $application->evaluation_stage >= 4 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                                        Stage 4
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Waiting for document upload</p>
                                        @endif
                                        @if($application->updated_at && $application->updated_at != $application->created_at)
                                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Last updated: {{ $application->updated_at?->format('M d, Y h:i A') }}</p>
                                        @endif
                                    </div>
                                </div>

                                <!-- Connecting Line -->
                                <div class="absolute left-4 top-24 w-0.5 h-8 bg-gray-300 dark:bg-gray-600"></div>

                                <!-- Step 4: Central Review & Scholar Selection -->
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-8 h-8 {{ $application->scholar_status === 'selected' ? 'bg-green-500' : ($application->status === 'approved' ? 'bg-yellow-500' : 'bg-gray-300 dark:bg-gray-600') }} rounded-full flex items-center justify-center">
                                        @if($application->scholar_status === 'selected')
                                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        @elseif($application->status === 'approved')
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <h5 class="text-sm font-medium text-gray-900 dark:text-white">Central Review & Scholar Selection</h5>
                                        @if($application->scholar_status === 'selected')
                                            <p class="flex items-center gap-1 text-sm text-green-600 dark:text-green-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                  <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                                  <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                                                </svg>
                                               <span class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Congratulations!
                                    </span> You have been selected as a scholar
                                            </p>
                                            <div class="mt-2 p-2 bg-green-50 dark:bg-green-900/20 rounded-md">
                                                <p class="text-xs text-green-700 dark:text-green-300">
                                                    <strong>Scholar Status:</strong> {{ ucfirst($application->scholar_type ?? 'New') }} Scholar
                                                </p>
                                                @if($application->scholar_grant_count > 0)
                                                    <p class="text-xs text-green-700 dark:text-green-300">
                                                        <strong>Grant Count:</strong> {{ $application->scholar_grant_count }} grants received
                                                    </p>
                                                @endif
                                            </div>
                                        @elseif($application->status === 'approved')
                                            <p class="flex items-center gap-1 text-sm text-yellow-600 dark:text-yellow-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Under central review for scholar selection
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Central admin is reviewing your application for final scholar selection</p>
                                        @else
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Pending SFAO approval</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Application Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h5 class="font-semibold text-gray-900 dark:text-white mb-2">Scholarship Details</h5>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Grant Amount:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ $application->scholarship->grant_amount ? '₱' . number_format((float) $application->scholarship->grant_amount, 2) : 'N/A' }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Deadline:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ $application->scholarship->deadline ? \Carbon\Carbon::parse($application->scholarship->deadline)?->format('M d, Y') : 'N/A' }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Slots Available:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ $application->scholarship->slots_available ?? 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h5 class="font-semibold text-gray-900 dark:text-white mb-2">Application Info</h5>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Application ID:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">#{{ $application->id }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Type:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">Application</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Documents:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ $application->has_documents ? $application->documents_count . ' uploaded' : 'Not uploaded' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-6 flex flex-wrap gap-3">
                            @if(!$application->has_documents)
                                <a href="{{ route('student.upload-documents', $application->scholarship_id) }}" 
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    Upload Documents
                                </a>
                            @endif
                            
                            @if($application->has_documents)
                                <a href="{{ route('student.view-documents', $application->scholarship_id) }}" 
                                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    View Documents
                                </a>
                            @endif

                            @if($application->status === 'rejected')
                                <button class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                    Contact Support
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
