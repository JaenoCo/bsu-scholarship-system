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
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-visible">
        <div class="overflow-x-auto custom-scrollbar">
            {{-- FIX: table-fixed + colgroup gives every column a locked share of the
                 table width instead of growing to fit its content. Percentages sum to 100%. --}}
            <table class="min-w-full table-fixed divide-y divide-gray-200 dark:divide-gray-700">
                <colgroup>
                    <col style="width: 4%">   {{-- # --}}
                    <col style="width: 24%">  {{-- Student --}}
                    <col style="width: 18%">  {{-- Scholarship --}}
                    <col style="width: 13%">  {{-- Application Status --}}
                    <col style="width: 15%">  {{-- Documents --}}
                    <col style="width: 12%">  {{-- Grant Count --}}
                    <col style="width: 14%">  {{-- Actions --}}
                </colgroup>
                <thead class="bg-bsu-red text-white">
                    <tr>
                        {{-- FIX: px-6 -> px-3, py-3 -> py-2, added truncate wrapper where needed
                             to reclaim horizontal space across every header/cell. --}}
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">#</th>
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Student</th>
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Scholarship</th>
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Documents</th>
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Grants</th>
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @php $rowIndex = $students->firstItem() ? $students->firstItem() - 1 : 0; @endphp
                    @foreach($students as $student)
                        @if(isset($student->applications) && $student->applications->isNotEmpty())
                            @foreach($student->applications as $application)
                                @php $rowIndex++; @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-3 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $rowIndex }}</td>
                                    <td class="px-3 py-3">
                                        {{-- FIX: min-w-0 on the flex row lets the text column actually
                                             shrink; truncate + title attr keeps long names/emails on one line
                                             with an ellipsis instead of forcing the row wider. --}}
                                        <div class="flex items-center min-w-0">
                                            <div class="flex-shrink-0 h-9 w-9">
                                                <div class="h-9 w-9 rounded-full bg-bsu-red flex items-center justify-center">
                                                    <span class="text-xs font-medium text-white">{{ strtoupper(substr($student->name, 0, 2)) }}</span>
                                                </div>
                                            </div>
                                            <div class="ml-3 min-w-0">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white truncate cursor-pointer hover:text-blue-600 hover:underline" title="{{ $student->name }}" @click="$dispatch('open-applicant-modal', {{ json_encode($student) }})">{{ $student->name }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate" title="{{ $student->email }}">{{ $student->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white truncate" title="{{ $application->scholarship?->scholarship_name ?? 'Unknown Scholarship' }}">{{ $application->scholarship?->scholarship_name ?? 'Unknown Scholarship' }}</div>
                                    </td>
                                    <td class="px-3 py-3">
                                        @php
                                            $status = $application->status ?? 'not_applied';
                                            $statusLabel = match ($status) {
                                                'approved' => 'Approved',
                                                'in_progress' => 'Under Review',
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
                                        {{-- FIX: whitespace-nowrap removed from the wrapping <td> (table-fixed
                                             cells wrap by default), badge itself stays nowrap so it never splits. --}}
                                        <span class="inline-flex whitespace-nowrap px-2 py-1 text-xs font-semibold rounded-full {{ $statusClasses }}">{{ $statusLabel }}</span>
                                    </td>
                                    <td class="px-3 py-3">
                                        @if($application->documents_count > 0)
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 flex-shrink-0 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="text-xs text-green-600 dark:text-green-400 font-medium truncate">{{ $application->documents_count }} uploaded</span>
                                            </div>
                                            @if($application->last_uploaded)
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ \Carbon\Carbon::parse($application->last_uploaded)?->format('M d, Y') }}</div>
                                            @endif
                                        @else
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 flex-shrink-0 text-red-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="text-xs text-red-600 dark:text-red-400 font-medium">No documents</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3">
                                        @php
                                            $grantCount = method_exists($application, 'getGrantCountDisplay') ? $application->getGrantCountDisplay() : $application->grant_count;
                                            $grantBadge = method_exists($application, 'getGrantCountBadgeColor') ? $application->getGrantCountBadgeColor() : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
                                        @endphp
                                        {{-- FIX: added whitespace-nowrap + max-w-full truncate so a long
                                             grant label ("No grants received") shrinks to the badge's own
                                             column instead of stretching the row. --}}
                                        <span class="inline-block max-w-full truncate align-middle px-2 py-1 text-xs font-medium rounded-full {{ $grantBadge }}" title="{{ $grantCount ?? 'None' }}">{{ $grantCount ?? 'None' }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-sm font-medium">
                                        {{-- FIX: buttons stack vertically and go full-width-of-column instead
                                             of side-by-side, so the Actions column stays narrow and never
                                             forces horizontal scroll to see the Reject button. --}}
                                        <div class="flex flex-col gap-1.5">
                                            @if($status === 'pending')
                                                @php $evalUserId = $student->student_id ?? $student->id ?? $student->user_id ?? null; @endphp
                                                @if($evalUserId)
                                                    <a href="{{ route('sfao.evaluation.sfao-documents', ['user_id' => $evalUserId, 'scholarship_id' => $application->scholarship_id]) }}" class="w-full text-center px-2 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-xs font-semibold">Evaluate</a>
                                                @else
                                                    <span class="w-full text-center px-2 py-1 bg-blue-600 text-white rounded-lg opacity-60 cursor-not-allowed text-xs font-semibold">Evaluate</span>
                                                @endif

                                                <form method="POST" action="{{ url('/applications/' . $application->id . '/reject') }}" onsubmit="return confirm('Reject this application?');">
                                                    @csrf
                                                    <button type="submit" class="w-full px-2 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-xs font-semibold">Reject</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            @php $rowIndex++; @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-3 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $rowIndex }}</td>
                                <td class="px-3 py-3">
                                    <div class="flex items-center min-w-0">
                                        <div class="flex-shrink-0 h-9 w-9">
                                            <div class="h-9 w-9 rounded-full bg-bsu-red flex items-center justify-center">
                                                <span class="text-xs font-medium text-white">{{ strtoupper(substr($student->name, 0, 2)) }}</span>
                                            </div>
                                        </div>
                                        <div class="ml-3 min-w-0">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white truncate" title="{{ $student->name }}">{{ $student->name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate" title="{{ $student->email }}">{{ $student->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-500 dark:text-gray-400">No applications</td>
                                <td class="px-3 py-3">
                                    <span class="inline-flex whitespace-nowrap px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">Not Applied</span>
                                </td>
                                <td class="px-3 py-3">
                                    @if($student->has_documents)
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 flex-shrink-0 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="text-xs text-green-600 dark:text-green-400 font-medium truncate">{{ $student->documents_count }} uploaded</span>
                                        </div>
                                        @if($student->last_uploaded)
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ \Carbon\Carbon::parse($student->last_uploaded)?->format('M d, Y') }}</div>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-500 dark:text-gray-400">No documents</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    <span class="inline-block px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">None</span>
                                </td>
                                <td class="px-3 py-3 text-sm font-medium">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">No action</span>
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