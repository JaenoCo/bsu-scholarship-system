@props([
    'type' => 'text',
    'label' => null,
    'error' => null,
    'required' => false,
    'placeholder' => null
])

<div class="space-y-1">
    @if($label)
        <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <input 
        type="{{ $type }}" 
        {{ $attributes->merge([
            'class' => 'w-full border border-red-500 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-bsu-red focus:border-transparent dark:bg-gray-700 dark:text-white dark:border-gray-600',
            'placeholder' => $placeholder
        ]) }}
        @if($required) required @endif
    >
    
    @if($error)
        <p class="text-red-500 text-sm">{{ $error }}</p>
    @endif
</div>

