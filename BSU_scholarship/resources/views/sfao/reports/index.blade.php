<div x-show="tab.startsWith('reports')" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-cloak>
  <div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
      <!-- Header Text Removed -->
      <div class="mt-4 sm:mt-0">

        
        <!-- Default Create Report Button (Fallback) -->
        <a href="{{ route('sfao.reports.create') }}"
           x-show="!tab.startsWith('reports-')"
           class="inline-flex items-center px-4 py-2 bg-bsu-red border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-bsu-redDark focus:bg-bsu-redDark active:bg-bsu-redDark focus:outline-none focus:ring-2 focus:ring-bsu-red focus:ring-offset-2 transition ease-in-out duration-150">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
          Create Report
        </a>
      </div>
    </div>



    <!-- Campus Selection Cards (Student Summary) -->
    <div x-show="tab === 'reports-student_summary'" class="mt-6">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- All Campuses Card -->
            @if(isset($monitoredCampuses) && $monitoredCampuses->count() > 1)
            <a href="{{ route('sfao.reports.student-summary', ['student_type' => 'applicants', 'campus_id' => 'all']) }}" 
               class="block bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-200 dark:border-gray-700 group">
                <div class="p-6 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-bsu-red group-hover:bg-bsu-red group-hover:text-white transition-colors duration-300">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-bsu-red dark:group-hover:text-red-400 transition-colors">All Campuses</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Generate student report for all campuses</p>
                        </div>
                    </div>
                    <svg class="w-6 h-6 text-gray-400 group-hover:text-bsu-red transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </a>
            @endif

            <!-- Individual Campus Cards -->
            @if(isset($monitoredCampuses))
                @foreach($monitoredCampuses as $campus)
                    <a href="{{ route('sfao.reports.student-summary', ['student_type' => 'applicants', 'campus_id' => $campus->id]) }}" 
                       class="block bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-200 dark:border-gray-700 group">
                        <div class="p-6 flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-blue-100 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $campus->name }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Generate student report for this campus</p>
                                </div>
                            </div>
                            <svg class="w-6 h-6 text-gray-400 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </a>
                @endforeach
            @endif
        </div>
    </div>

    </div>

    <!-- Campus Selection Cards (Grant Summary) -->
    <div x-show="tab === 'reports-grant_summary'" class="mt-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- All Campuses Card -->
            @if(isset($monitoredCampuses) && $monitoredCampuses->count() > 1)
            <a href="{{ route('sfao.reports.grant-summary', ['campus_id' => 'all']) }}" 
               class="block bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-200 dark:border-gray-700 group">
                <div class="p-6 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-bsu-red group-hover:bg-bsu-red group-hover:text-white transition-colors duration-300">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-bsu-red dark:group-hover:text-red-400 transition-colors">All Campuses</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Generate grant report for all campuses</p>
                        </div>
                    </div>
                    <svg class="w-6 h-6 text-gray-400 group-hover:text-bsu-red transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </a>
            @endif

            <!-- Individual Campus Cards -->
            @if(isset($monitoredCampuses))
                @foreach($monitoredCampuses as $campus)
                    <a href="{{ route('sfao.reports.grant-summary', ['campus_id' => $campus->id]) }}" 
                       class="block bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-200 dark:border-gray-700 group">
                        <div class="p-6 flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-blue-100 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $campus->name }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Generate grant report for this campus</p>
                                </div>
                            </div>
                            <svg class="w-6 h-6 text-gray-400 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </a>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Filters (Shared for now) - Hidden on Summary Reports -->
    <div x-show="!['reports-student_summary', 'reports-grant_summary'].includes(tab)" class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
      <form method="GET" action="{{ route('sfao.dashboard') }}" class="flex flex-wrap gap-4">
        <input type="hidden" name="tab" :value="tab">
        <div class="flex-1 min-w-0">
          <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
          <select name="status" id="status"
            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-bsu-red focus:border-bsu-red">
            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
            <option value="reviewed" {{ request('status') == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
          </select>
        </div>
        <div class="flex-1 min-w-0">
          <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
          <select name="type" id="type"
            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-bsu-red focus:border-bsu-red">
            <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>All Types</option>
            <option value="monthly" {{ request('type') == 'monthly' ? 'selected' : '' }}>Monthly</option>
            <option value="quarterly" {{ request('type') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
            <option value="annual" {{ request('type') == 'annual' ? 'selected' : '' }}>Annual</option>
            <option value="custom" {{ request('type') == 'custom' ? 'selected' : '' }}>Custom</option>
          </select>
        </div>
        <div class="flex items-end">
          <button type="submit"
            class="inline-flex items-center px-4 py-2 bg-bsu-red border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-bsu-redDark focus:bg-bsu-redDark active:bg-bsu-redDark focus:outline-none focus:ring-2 focus:ring-bsu-red focus:ring-offset-2 transition ease-in-out duration-150">
            Filter
          </button>
        </div>
      </form>
    </div>

    <!-- Reports Table - Hidden on Summary Reports -->
    <div x-show="!['reports-student_summary', 'reports-grant_summary'].includes(tab)" class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
      @if (isset($reports) && $reports->count() > 0)
        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
          @foreach ($reports as $report)
            <li class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700">
              <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                  <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                      <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $report->getStatusBadgeColor() }}">
                        {{ ucfirst($report->status) }}
                      </span>
                    </div>
                    <div class="flex-1 min-w-0">
                      <h3 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                        {{ $report->title }}
                      </h3>
                      <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $report->getReportTypeDisplayName() }} • {{ $report->getPeriodDisplayName() }}
                      </p>
                      @if ($report->description)
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                          {{ \Illuminate\Support\Str::limit($report->description, 100) }}
                        </p>
                      @endif
                    </div>
                  </div>
                </div>
                <div class="flex items-center space-x-2">
                  <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $report->created_at->format('M d, Y') }}
                  </div>
                  <div class="flex space-x-2">
                    <!-- View Button -->
                    <a href="{{ route('sfao.reports.show', $report->id) }}"
                      class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bsu-red transition duration-150 ease-in-out h-9">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                        </path>
                      </svg>
                      View
                    </a>

                    @if ($report->isDraft())
                      <!-- Edit Button -->
                      <a href="{{ route('sfao.reports.edit', $report->id) }}"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bsu-red transition duration-150 ease-in-out h-9">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                          </path>
                        </svg>
                        Edit
                      </a>

                      <!-- Submit Button -->
                      <form method="POST" action="{{ route('sfao.reports.submit', $report->id) }}" class="inline">
                        @csrf
                        <button type="submit"
                          class="inline-flex items-center px-3 py-2 border border-bsu-red shadow-sm text-sm font-medium rounded-md text-white bg-bsu-red hover:bg-bsu-redDark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bsu-red transition duration-150 ease-in-out h-9"
                          onclick="return confirm('Are you sure you want to submit this report?')">
                          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                          </svg>
                          Submit
                        </button>
                      </form>

                      <!-- Delete Button -->
                      <form method="POST" action="{{ route('sfao.reports.delete', $report->id) }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                          class="inline-flex items-center px-3 py-2 border border-red-600 shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-150 ease-in-out h-9"
                          onclick="return confirm('Are you sure you want to delete this report?')">
                          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                          </svg>
                          Delete
                        </button>
                      </form>
                    @endif
                  </div>
                </div>
              </div>
              @if ($report->central_feedback)
                <div
                  class="mt-2 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-md">
                  <div class="flex">
                    <div class="flex-shrink-0">
                      <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                          d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                          clip-rule="evenodd"></path>
                      </svg>
                    </div>
                    <div class="ml-3">
                      <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        Central Admin Feedback
                      </h3>
                      <div class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                        {{ $report->central_feedback }}
                      </div>
                    </div>
                  </div>
                </div>
              @endif
            </li>
          @endforeach
        </ul>

        <!-- Pagination -->
        <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
          {{ $reports->links() }}
        </div>
      @else
        <div class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
            </path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No reports found</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Get started by creating a new report.
          </p>
          <div class="mt-6">
            <!-- Default Create Report Button (Empty State) -->
            <div x-show="!tab.startsWith('reports-')">
              <a href="{{ route('sfao.reports.create') }}"
                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-bsu-red hover:bg-bsu-redDark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bsu-red">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create Report
              </a>
            </div>
          </div>
        </div>
      @endif
    </div>


  </div>
</div>
