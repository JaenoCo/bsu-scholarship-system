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
        <button onclick="document.querySelector('[x-data]').__x.$data.resetFilters()" class="px-4 py-2 bg-bsu-red text-white text-sm rounded-lg hover:bg-red-700 transition shadow">
            Reset Filters
        </button>
    </div>
@else
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-visible">
        <div class="overflow-x-auto custom-scrollbar sfao-scholar-management-scroll">
            <table class="min-w-full table-fixed divide-y divide-gray-200 dark:divide-gray-700 sfao-scholar-management-table">
                <colgroup>
                    <col style="width: 4%">
                    <col style="width: 17%">
                    <col style="width: 9%">
                    <col style="width: 13%">
                    <col style="width: 7%">
                    <col style="width: 8%">
                    <col style="width: 5%">
                    <col style="width: 10%">
                    <col style="width: 14%">
                    <col style="width: 13%">
                </colgroup>
                <thead class="bg-bsu-red text-white">
                    <tr>
                        <th class="px-2 py-3 text-center">
                            <input type="checkbox" 
                                   x-model="selectAll" 
                                   @change="toggleSelectAll()"
                                   class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500 focus:ring-2 cursor-pointer">
                        </th>
                        <th class="px-2 py-3 text-left text-[11px] font-medium uppercase tracking-wide">Scholar</th>
                        <th class="px-2 py-3 text-left text-[11px] font-medium uppercase tracking-wide">Campus</th>
                        <th class="px-2 py-3 text-left text-[11px] font-medium uppercase tracking-wide">Scholarship</th>
                        <th class="px-2 py-3 text-left text-[11px] font-medium uppercase tracking-wide">Type</th>
                        <th class="px-2 py-3 text-left text-[11px] font-medium uppercase tracking-wide">Status</th>
                        <th class="px-2 py-3 text-left text-[11px] font-medium uppercase tracking-wide">Grants</th>
                        <th class="px-2 py-3 text-left text-[11px] font-medium uppercase tracking-wide">Received</th>
                        <th class="px-2 py-3 text-left text-[11px] font-medium uppercase tracking-wide">Tracking Number</th>
                        <th class="px-2 py-3 text-left text-[11px] font-medium uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($scholars as $index => $scholar)
                        @php
                            $canMarkScholar = $scholar->can_mark ?? true;
                            $isEligible = $canMarkScholar && !(($scholar->scholarship->grant_type ?? null) === 'one_time' && $scholar->grant_count > 0);
                            $isActive = ($scholar->status ?? 'active') === 'active';
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-2 py-4 text-center">
                                @if($isEligible)
                                    <input type="checkbox" 
                                           :checked="isScholarSelected({{ $scholar->id }})"
                                           @change="toggleScholar({{ $scholar->id }})"
                                           class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500 focus:ring-2 cursor-pointer">
                                @endif
                            </td>
                            <td class="px-2 py-4">
                                <div class="flex min-w-0 items-center">
                                    <div class="h-9 w-9 flex-shrink-0">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-bsu-red">
                                            <span class="text-xs font-medium text-white">
                                                {{ strtoupper(substr($scholar->user->name ?? 'N/A', 0, 2)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-2 min-w-0">
                                        <div class="truncate text-sm font-medium text-gray-900 dark:text-white" title="{{ $scholar->user->name ?? 'Unknown Student' }}">{{ $scholar->user->name ?? 'Unknown Student' }}</div>
                                        <div class="truncate text-xs text-gray-500 dark:text-gray-400" title="{{ $scholar->user->email ?? 'N/A' }}">{{ $scholar->user->email ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="truncate px-2 py-4 text-sm text-gray-700 dark:text-gray-300" title="{{ $scholar->user->campus->name ?? 'N/A' }}">
                                {{ $scholar->user->campus->name ?? 'N/A' }}
                            </td>
                            <td class="truncate px-2 py-4 text-sm text-gray-700 dark:text-gray-300" title="{{ $scholar->scholarship->scholarship_name ?? 'N/A' }}">
                                {{ $scholar->scholarship->scholarship_name ?? 'N/A' }}
                            </td>
                            <td class="px-2 py-4">
                                <span class="inline-flex whitespace-nowrap items-center rounded px-2 py-0.5 text-xs font-semibold {{ $scholar->type === 'new' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                    {{ ucfirst($scholar->type) }}
                                </span>
                            </td>
                            <td class="px-2 py-4">
                                <span class="inline-flex whitespace-nowrap items-center rounded px-2 py-0.5 text-xs font-semibold {{ $isActive ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}">
                                    {{ ucfirst($scholar->status) }}
                                </span>
                            </td>
                            <td class="px-2 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ $scholar->grant_count ?? 0 }}
                            </td>
                            <td class="px-2 py-4 text-sm text-gray-500 dark:text-gray-400">
                                <span class="whitespace-nowrap">{{ $scholar->total_grant_received ? '₱' . number_format((float)$scholar->total_grant_received, 0) : '₱0' }}</span>
                                <div class="mt-1 text-xs text-gray-400">Updated: {{ $scholar->updated_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-2 py-4 text-xs text-gray-700 dark:text-gray-300">
                                @forelse($scholar->grant_releases ?? $scholar->grantReleases ?? collect() as $grantRelease)
                                    <a href="{{ route('sfao.grant-releases.verify', ['trackingNumber' => $grantRelease->tracking_number]) }}"
                                       class="block truncate text-blue-700 hover:underline dark:text-blue-300"
                                       title="{{ $grantRelease->tracking_number }}">
                                        {{ $grantRelease->tracking_number }}
                                    </a>
                                @empty
                                    <span class="text-gray-400">Not released</span>
                                @endforelse
                            </td>
                            <td class="px-2 py-4 text-sm font-medium">
                                @if(!$canMarkScholar)
                                    <button disabled class="whitespace-nowrap rounded-lg border border-green-200 bg-green-100 px-3 py-2 text-sm font-semibold text-green-700 cursor-not-allowed">
                                        Approved
                                    </button>
                                @elseif(($scholar->scholarship->grant_type ?? null) === 'one_time' && $scholar->grant_count > 0)
                                    <button disabled class="whitespace-nowrap rounded-lg border border-gray-300 bg-gray-200 px-3 py-2 text-sm font-semibold text-gray-500 cursor-not-allowed">
                                        Claimed
                                    </button>
                                @else
                                    <button @click="openMarkAsModal({{ $scholar->id }}, '{{ addslashes($scholar->user->name ?? 'Scholar') }}')" 
                                            class="whitespace-nowrap rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-md transition-all duration-200 hover:bg-blue-700 hover:shadow-lg">
                                        Mark as
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
