@props([
    'currentSort' => 'name',
    'currentOrder' => 'asc',
    'baseUrl' => '',
    'role' => 'student'
])

@php
    $sortOptions = [
        'name' => 'Scholarship Name',
        'created_at' => 'Date Created',
        'submission_deadline' => 'Deadline',
        'grant_amount' => 'Grant Amount',
        'priority_level' => 'Priority Level',
        'scholarship_type' => 'Type',
        'grant_type' => 'Grant Type',
        'slots_available' => 'Available Slots',
        'gwa_requirement' => 'GWA Requirement'
    ];

    // Add role-specific options
    if ($role === 'sfao' || $role === 'central') {
        $sortOptions['applications_count'] = 'Applications Count';
    }
@endphp

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <label for="sort_by" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Sort by:
            </label>
            <select 
                id="sort_by" 
                name="sort_by" 
                class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:border-bsu-red focus:ring-bsu-red"
                onchange="updateSorting()"
            >
                @foreach($sortOptions as $value => $label)
                    <option value="{{ $value }}" {{ $currentSort === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center space-x-4">
            <label for="sort_order" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Order:
            </label>
            <select 
                id="sort_order" 
                name="sort_order" 
                class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:border-bsu-red focus:ring-bsu-red"
                onchange="updateSorting()"
            >
                <option value="asc" {{ $currentOrder === 'asc' ? 'selected' : '' }}>Ascending</option>
                <option value="desc" {{ $currentOrder === 'desc' ? 'selected' : '' }}>Descending</option>
            </select>
        </div>

        <div class="flex items-center space-x-2">
            <button 
                type="button" 
                onclick="resetSorting()"
                class="px-3 py-1 text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
            >
                Reset
            </button>
        </div>
    </div>
</div>

<script>
function updateSorting() {
    const sortBy = document.getElementById('sort_by').value;
    const sortOrder = document.getElementById('sort_order').value;
    
    const url = new URL(window.location);
    url.searchParams.set('sort_by', sortBy);
    url.searchParams.set('sort_order', sortOrder);
    
    window.location.href = url.toString();
}

function resetSorting() {
    const url = new URL(window.location);
    url.searchParams.delete('sort_by');
    url.searchParams.delete('sort_order');
    
    window.location.href = url.toString();
}
</script>
