@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button'
])

@php
    $baseClasses = 'font-semibold rounded-lg shadow transition duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2';
    
    $variants = [
        'primary' => 'bg-bsu-red hover:bg-bsu-redDark text-white focus:ring-bsu-red',
        'secondary' => 'bg-gray-500 hover:bg-gray-600 text-white focus:ring-gray-500',
        'success' => 'bg-green-600 hover:bg-green-700 text-white focus:ring-green-500',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500',
        'warning' => 'bg-yellow-600 hover:bg-yellow-700 text-white focus:ring-yellow-500',
        'info' => 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500',
    ];
    
    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-base',
        'lg' => 'px-6 py-3 text-lg',
        'xl' => 'px-8 py-4 text-xl',
    ];
    
    $classes = $baseClasses . ' ' . $variants[$variant] . ' ' . $sizes[$size];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>

