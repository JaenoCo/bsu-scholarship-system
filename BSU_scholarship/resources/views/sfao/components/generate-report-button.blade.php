@props(['url', 'label' => 'Generate Report'])

<a href="{{ $url }}"
   {{ $attributes ?? '' }}
   class="inline-flex items-center px-4 py-2 bg-bsu-red border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-bsu-redDark focus:bg-bsu-redDark active:bg-bsu-redDark focus:outline-none focus:ring-2 focus:ring-bsu-red focus:ring-offset-2 transition ease-in-out duration-150">
  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
  </svg>
  {{ $label }}
</a>
