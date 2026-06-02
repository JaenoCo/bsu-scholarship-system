{{-- 
    Reusable page header component for student non-dashboard pages
    Usage: @include('student.partials.page-header', ['title' => 'Page Title'])
--}}
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6 pb-4 border-b-2 border-bsu-red">
    <div>
        <h1 class="text-2xl md:text-3xl font-bold text-bsu-red">{{ $title ?? 'Page Title' }}</h1>
        @isset($subtitle)
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $subtitle }}</p>
        @endisset
    </div>
    {{-- Back to Dashboard button removed as per user request --}}
    {{-- <a href="{{ route('student.dashboard') }}" 
       id="backToDashboardLink"
       class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold rounded-lg border-2 border-bsu-red whitespace-nowrap
              bg-white text-bsu-red">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Back to Dashboard
    </a> --}}
</div>

