@props([
    'scholarship',
    'applied' => false,
    'gwa' => null
])

<x-student.ui.card 
    :variant="$applied ? 'applied' : 'default'"
    class="relative"
>
    <x-slot name="title">
        {{ $scholarship->scholarship_name }}
    </x-slot>
    
    <x-slot name="subtitle">
        {{ $scholarship->description }}
    </x-slot>

    <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1 mb-12">
        <p><strong>Required GWA:</strong> {{ $scholarship->getGwaRequirement() ?? 'No requirement' }}</p>
        <p><strong>Deadline:</strong> {{ \Carbon\Carbon::parse($scholarship->deadline)?->format('M d, Y') }}</p>
        <p><strong>Slots:</strong> {{ $scholarship->slots_available ?? 0 }}</p>

        @if($scholarship->grant_amount)
            <p><strong>Grant Amount:</strong> ₱{{ number_format((float) $scholarship->grant_amount, 2) }}</p>
        @endif

        <p><strong>Renewal Allowed:</strong> {{ $scholarship->renewal_allowed ? 'Yes' : 'No' }}</p>
    </div>

    <!-- Action Buttons -->
    <div class="absolute bottom-4 left-4">
        @if($applied)
            <x-student.ui.button 
                variant="secondary" 
                onclick="openWithdrawModal('{{ $scholarship->id }}')"
            >
                <span class="flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Withdraw
                </span>
            </x-student.ui.button>
        @else
            <a href="{{ route('student.apply', ['scholarship_id' => $scholarship->id]) }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <span class="flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path d="M12 14l9-5-9-5-9 5 9 5z" />
                      <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                    </svg>
                    Apply Now
                </span>
            </a>
        @endif
    </div>
</x-student.ui.card>

