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

</div>

