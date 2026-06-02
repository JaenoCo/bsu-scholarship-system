@if($students->isEmpty())
    <div class="text-center py-12">
        <div class="text-gray-400 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-400 mb-2">No Students Found</h3>
        <p class="text-gray-500 dark:text-gray-500 mb-4">No students found matching the selected filters.</p>
        <button onclick="document.querySelector('[x-data]').__x.$data.resetFilters()" class="px-4 py-2 bg-bsu-red text-white text-sm rounded-lg hover:bg-red-700 transition shadow">
            Reset Filters
        </button>
    </div>
@else
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-bsu-red text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Application Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Documents</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Applied Scholarships</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Grant Count</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($students as $index => $student)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-bsu-red flex items-center justify-center">
                                            <span class="text-sm font-medium text-white">
                                                {{ strtoupper(substr($student->name, 0, 2)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white cursor-pointer hover:text-blue-600 hover:underline" 
                                             @click="$dispatch('open-applicant-modal', {{ json_encode($student) }})">
                                            {{ $student->name }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $student->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($student->applications && $student->applications->isNotEmpty())
                                    <div class="flex flex-col space-y-2">
                                        @foreach($student->applications as $app)
                                            <div class="flex items-center space-x-2">
                                                @if($app->status === 'rejected')
                                                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                        Rejected
                                                    </span>
                                                @elseif($app->status === 'pending')
                                                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                        Pending
                                                    </span>
                                                @elseif($app->status === 'in_progress')
                                                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                                        In Progress
                                                    </span>
                                                @else
                                                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                                        {{ ucfirst(str_replace('_', ' ', $app->status)) }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                            Not Applied
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($student->has_documents)
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="text-sm text-green-600 dark:text-green-400 font-medium">
                                            {{ $student->documents_count }} uploaded
                                        </span>
                                    </div>
                                    @if($student->last_uploaded)
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ \Carbon\Carbon::parse($student->last_uploaded)?->format('M d, Y') }}
                                        </div>
                                    @endif
                                @else
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 text-red-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="text-sm text-red-600 dark:text-red-400 font-medium">No documents</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($student->applications && $student->applications->isNotEmpty())
                                    <button @click="$dispatch('open-applicant-modal', {{ json_encode($student) }})" 
                                            class="text-left group flex items-center space-x-1 focus:outline-none">
                                        <span class="text-sm font-medium text-blue-600 dark:text-blue-400 group-hover:text-blue-800 group-hover:underline">
                                            {{ $student->applications->count() }} Application{{ $student->applications->count() > 1 ? 's' : '' }}
                                        </span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 group-hover:text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                                          <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                @else
                                    <span class="text-sm text-gray-500 dark:text-gray-400">None</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($student->has_applications && isset($student->applications_with_types))
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($student->applications_with_types as $app)
                                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded {{ $app['grant_count_badge_color'] }}">
                                                {{ $app['grant_count_display'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-sm text-gray-500 dark:text-gray-400">None</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    @if($student->has_documents)
                                        <a href="{{ route('sfao.evaluation.show', $student->student_id) }}"
                                           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-200 text-sm font-semibold shadow-md hover:shadow-lg inline-block">
                                            Evaluate
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Links -->
    <!-- Pagination Links -->
    <x-layout.pagination :paginator="$students" />
@endif
