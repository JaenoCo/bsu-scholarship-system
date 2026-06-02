@if($scholarships->isEmpty())
    <div class="col-span-full text-center py-12">
        <div class="text-gray-400 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path d="M12 14l9-5-9-5-9 5 9 5z" />
                <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
            </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-400 mb-2">No Scholarships Available</h3>
        <p class="text-gray-500 dark:text-gray-500">There are currently no scholarships to display.</p>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    @foreach($scholarships as $scholarship)
        <div x-data="{ showDetails_{{ $scholarship->id }}: false, showReleaseModal: false }">
            <div @click="showDetails_{{ $scholarship->id }} = true" class="h-full">
                @include('central.partials.components.scholarship-card', [
                    'scholarship' => $scholarship,
                    'role' => 'sfao',
                    'fillPercentage' => $scholarship->fill_percentage ?? 0,
                    'disableModal' => true
                ])
            </div>
            @include('sfao.components.modals.scholarship-details', ['scholarship' => $scholarship])

        </div>
    @endforeach
    </div>

    <!-- Pagination Links -->
    <div class="mt-8 col-span-full">
        {{ $scholarships->appends(request()->except('page_scholarships'))->links('vendor.pagination.custom') }}
    </div>
@endif
