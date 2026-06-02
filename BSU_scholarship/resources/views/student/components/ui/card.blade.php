@props([
    'title' => null,
    'subtitle' => null,
    'variant' => 'default'
])

@php
    $baseClasses = 'rounded-lg shadow border-2 p-5 transition';
    
    $variants = [
        'default' => 'bg-white dark:bg-gray-900 border-bsu-red hover:bg-bsu-light dark:hover:bg-gray-800',
        'applied' => 'bg-gray-200 dark:bg-gray-800 border-gray-400',
        'success' => 'bg-green-50 dark:bg-green-900 border-green-400',
        'warning' => 'bg-yellow-50 dark:bg-yellow-900 border-yellow-400',
        'error' => 'bg-red-50 dark:bg-red-900 border-red-400',
    ];
    
    $classes = $baseClasses . ' ' . $variants[$variant];
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @if($title)
        <h3 class="text-lg font-bold text-bsu-red dark:text-white mb-2">
            {{ $title }}
        </h3>
    @endif
    
    @if($subtitle)
        <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">
            {{ $subtitle }}
        </p>
    @endif
    
    {{ $slot }}
</div>

