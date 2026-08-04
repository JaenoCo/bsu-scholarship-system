@if($students->isEmpty())
    <div class="text-center py-12">
        <div class="text-gray-400 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-400 mb-2">No Students Found</h3>
        <p class="text-gray-500 dark:text-gray-500 mb-4">No students found matching the selected filters.</p>
        <button @click="resetFilters()" class="px-4 py-2 bg-bsu-red text-white text-sm rounded-lg hover:bg-red-700 transition shadow">
            Reset Filters
        </button>
    </div>
@else
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-bsu-red text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Scholarship</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Application Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Documents</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Grant Count</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @php $rowIndex = $students->firstItem() ? $students->firstItem() - 1 : 0; @endphp
                    @foreach($students as $student)
                        @if(isset($student->applications) && $student->applications->isNotEmpty())
                            @foreach($student->applications as $application)
                                @php $rowIndex++; @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $rowIndex }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-bsu-red flex items-center justify-center">
                                                    <span class="text-sm font-medium text-white">{{ strtoupper(substr($student->name, 0, 2)) }}</span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white cursor-pointer hover:text-blue-600 hover:underline" @click="$dispatch('open-applicant-modal', {{ json_encode($student) }})">{{ $student->name }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $student->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $application->scholarship?->scholarship_name ?? 'Unknown Scholarship' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $status = $application->status ?? 'not_applied';
                                            $statusLabel = match ($status) {
                                                'approved' => 'Approved',
                                                'in_progress' => 'In Progress',
                                                'pending' => 'Pending',
                                                'rejected' => 'Rejected',
                                                default => 'Not Applied',
                                            };
                                            $statusClasses = match ($status) {
                                                'approved' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                                'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                                'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                                'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                                default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                            };
                                        @endphp
                                        <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full {{ $statusClasses }}">{{ $statusLabel }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($application->documents_count > 0)
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="text-sm text-green-600 dark:text-green-400 font-medium">{{ $application->documents_count }} uploaded</span>
                                            </div>
                                            @if($application->last_uploaded)
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ \Carbon\Carbon::parse($application->last_uploaded)?->format('M d, Y') }}</div>
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
                                        @php
                                            $grantCount = method_exists($application, 'getGrantCountDisplay') ? $application->getGrantCountDisplay() : $application->grant_count;
                                            $grantBadge = method_exists($application, 'getGrantCountBadgeColor') ? $application->getGrantCountBadgeColor() : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
                                        @endphp
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $grantBadge }}">{{ $grantCount ?? 'None' }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex flex-wrap gap-2">
                                            @if($status === 'pending')
                                                @php $evalUserId = $student->student_id ?? $student->id ?? $student->user_id ?? null; @endphp
                                                @if($evalUserId)
                                                    <a href="{{ route('sfao.evaluation.sfao-documents', ['user_id' => $evalUserId, 'scholarship_id' => $application->scholarship_id]) }}" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-semibold">Evaluate</a>
                                                @else
                                                    <span class="px-3 py-1.5 bg-blue-600 text-white rounded-lg opacity-60 cursor-not-allowed text-sm font-semibold">Evaluate</span>
                                                @endif

                                                <form method="POST" action="{{ url('/applications/' . $application->id . '/reject') }}" onsubmit="return confirm('Reject this application?');">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-semibold">Reject</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            @php $rowIndex++; @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $rowIndex }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-bsu-red flex items-center justify-center">
                                                <span class="text-sm font-medium text-white">{{ strtoupper(substr($student->name, 0, 2)) }}</span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $student->name }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $student->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">No applications</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">Not Applied</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($student->has_documents)
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="text-sm text-green-600 dark:text-green-400 font-medium">{{ $student->documents_count }} uploaded</span>
                                        </div>
                                        @if($student->last_uploaded)
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ \Carbon\Carbon::parse($student->last_uploaded)?->format('M d, Y') }}</div>
                                        @endif
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">No documents</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">None</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">No action</span>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Links -->
    <div class="mt-8">
        {{ $students->links('vendor.pagination.custom') }}
    </div>
@endif
