@if($scholars->isEmpty())
    <div class="text-center py-12">
        <div class="text-gray-400 dark:text-gray-500 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path d="M12 14l9-5-9-5-9 5 9 5z" />
              <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
            </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-400 mb-2">No Scholars Found</h3>
        <p class="text-gray-500 dark:text-gray-500 mb-4">No scholars found matching the selected filters.</p>
        <button @click="resetFilters()" class="px-4 py-2 bg-bsu-red text-white text-sm rounded-lg hover:bg-red-700 transition shadow">
            Reset Filters
        </button>
    </div>
@else
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-visible">
        <div class="overflow-x-auto custom-scrollbar">
            {{-- FIX: table-fixed + colgroup locks each of the 9 columns to a fixed
                 share of the table width (sums to 100%) instead of letting content
                 (long names/emails/campus strings) push the table wider than its
                 container, which is what was clipping the Actions column off-screen. --}}
            <table class="min-w-full table-fixed divide-y divide-gray-200 dark:divide-gray-700">
                <colgroup>
                    <col style="width: 3%">   {{-- # --}}
                    <col style="width: 20%">  {{-- Scholar --}}
                    <col style="width: 11%">  {{-- Campus --}}
                    <col style="width: 16%">  {{-- Scholarship --}}
                    <col style="width: 8%">   {{-- Type --}}
                    <col style="width: 9%">   {{-- Status --}}
                    <col style="width: 7%">   {{-- Grants --}}
                    <col style="width: 13%">  {{-- Total Received --}}
                    <col style="width: 13%">  {{-- Actions --}}
                </colgroup>
                <thead class="bg-bsu-red text-white">
                    {{-- FIX: px-6 -> px-3, py-3 -> py-2 to claw back horizontal room across the header row. --}}
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">#</th>
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Scholar</th>
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Campus</th>
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Scholarship</th>
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Type</th>
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Grants</th>
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Received</th>
                        <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($scholars as $index => $scholar)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-3 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                {{ $index + 1 }}
                            </td>
                            <td class="px-3 py-3">
                                {{-- FIX: min-w-0 lets this flex column shrink below its content size;
                                     truncate + title keeps long names/emails on a single line with an
                                     ellipsis rather than forcing the row (and every column after it) wider. --}}
                                <div class="flex items-center min-w-0">
                                    <div class="flex-shrink-0 h-9 w-9">
                                        <div class="h-9 w-9 rounded-full bg-bsu-red flex items-center justify-center">
                                            <span class="text-xs font-medium text-white">
                                                {{ strtoupper(substr($scholar->user->name ?? 'N/A', 0, 2)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-3 min-w-0">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white truncate" title="{{ $scholar->user->name ?? 'Unknown Student' }}">{{ $scholar->user->name ?? 'Unknown Student' }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate" title="{{ $scholar->user->email ?? 'N/A' }}">{{ $scholar->user->email ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-700 dark:text-gray-300 truncate" title="{{ $scholar->user->campus->name ?? 'N/A' }}">
                                {{ $scholar->user->campus->name ?? 'N/A' }}
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-700 dark:text-gray-300 truncate" title="{{ $scholar->scholarship->scholarship_name ?? 'N/A' }}">
                                {{ $scholar->scholarship->scholarship_name ?? 'N/A' }}
                            </td>
                            <td class="px-3 py-3">
                                <span class="inline-flex whitespace-nowrap items-center px-2 py-0.5 rounded text-xs font-semibold {{ $scholar->type === 'new' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                    {{ ucfirst($scholar->type) }}
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <span class="inline-flex whitespace-nowrap items-center px-2 py-0.5 rounded text-xs font-semibold {{ $scholar->isActive() ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}">
                                    {{ ucfirst($scholar->status) }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-700 dark:text-gray-300">
                                {{ $scholar->grant_count ?? 0 }}
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{-- FIX: whitespace-nowrap on the amount so "₱X,XXX" never wraps mid-number. --}}
                                <span class="whitespace-nowrap">{{ $scholar->total_grant_received ? '₱' . number_format((float)$scholar->total_grant_received, 0) : '₱0' }}</span>
                                <div class="text-xs text-gray-400 mt-1">{{ $scholar->updated_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-3 py-3 text-sm font-medium">
                                @if($scholar->scholarship->grant_type === 'one_time' && $scholar->grant_count > 0)
                                    <button disabled class="w-full text-center text-gray-400 bg-gray-100 px-2 py-1 rounded-md cursor-not-allowed text-xs font-semibold">
                                        Claimed
                                    </button>
                                @else
                                    <form action="{{ route('sfao.scholars.mark-claimed', $scholar->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to mark this grant as claimed? This will update the scholar\'s grant history.');">
                                        @csrf
                                        <button type="submit" class="w-full text-center text-green-600 hover:text-green-900 bg-green-100 hover:bg-green-200 px-2 py-1 rounded-md transition-colors text-xs font-semibold">
                                            Mark Claimed
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif