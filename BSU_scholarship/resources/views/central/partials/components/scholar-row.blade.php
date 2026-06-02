<tr class="hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer"
    @click="selectedScholar = {
        id: {{ $scholar->id }},
        name: '{{ addslashes($scholar->user->name ?? 'Unknown Student') }}',
        campus: '{{ addslashes($scholar->user->campus->name ?? 'N/A') }}',
        scholarship: '{{ addslashes($scholar->scholarship->scholarship_name ?? 'N/A') }}',
        type: '{{ $scholar->type }}',
        status: '{{ $scholar->status }}',
        grant_count: {{ $scholar->grant_count ?? 0 }},
        total_grant_received: {{ $scholar->total_grant_received ?? 0 }},
        program: '{{ addslashes($scholar->program ?? 'N/A') }}',
        year_level: '{{ addslashes($scholar->year_level ?? 'N/A') }}',
        gwa: '{{ $scholar->gwa ?? 'N/A' }}',
        start_date: '{{ optional($scholar->scholarship_start_date)->format('M d, Y') ?? 'N/A' }}',
        end_date: '{{ optional($scholar->scholarship_end_date)->format('M d, Y') ?? 'N/A' }}',
        notes: '{{ addslashes($scholar->notes ?? '') }}',
        show_url: '{{ route('central.scholars.show', $scholar->id) }}',
        edit_url: '{{ route('central.scholars.edit', $scholar->id) }}'
    }; showModal = true">
    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
        {{ $scholar->user->name ?? 'Unknown Student' }}
        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $scholar->program ?? 'N/A' }} • {{ $scholar->year_level ?? 'N/A' }}</div>
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
        {{ $scholar->campus ?? $scholar->user->campus->name ?? 'N/A' }}
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
        {{ $scholar->scholarship->scholarship_name ?? 'N/A' }}
    </td>
    <td class="px-6 py-4 whitespace-nowrap">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold {{ $scholar->type === 'new' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
            {{ ucfirst($scholar->type) }}
        </span>
    </td>
    <td class="px-6 py-4 whitespace-nowrap">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold {{ $scholar->isActive() ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}">
            {{ ucfirst($scholar->status) }}
        </span>
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
        {{ $scholar->grant_count ?? 0 }}
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
        {{ $scholar->total_grant_received ? '₱' . number_format((float)$scholar->total_grant_received, 0) : '₱0' }}
    </td>
</tr>
