{{-- 
    Reusable page header component for SFAO non-dashboard pages
    Usage: @include('sfao.partials.page-header', ['title' => 'Page Title'])
--}}
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6 pb-4 border-b-2 border-bsu-red">
    <div>
        <h1 class="text-2xl md:text-3xl font-bold text-bsu-red">{{ $title ?? 'Page Title' }}</h1>
        @isset($subtitle)
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $subtitle }}</p>
        @endisset
    </div>
    <div class="flex items-center gap-4">
        <!-- Dark Mode Toggle -->
        <button @click="darkMode = !darkMode" 
                class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors focus:outline-none"
                :title="darkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
            <!-- Sun Icon (for Dark Mode) -->
            <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <!-- Moon Icon (for Light Mode) -->
            <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
        </button>

        <a href="{{ route('sfao.dashboard') }}" 
           class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold rounded-lg border-2 border-bsu-red whitespace-nowrap
                  bg-white text-bsu-red hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-red-400 dark:border-red-400 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Dashboard
        </a>
    </div>
</div>

