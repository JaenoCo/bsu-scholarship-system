@props([
    'type' => 'text',
    'label',
    'name',
    'id' => null,
    'placeholder' => '',
    'required' => false,
    'autocomplete' => 'off',
    'pattern' => null,
    'ariaDescribedby' => null,
    'value' => '',
    'error' => null
])

@php
$id = $id ?? $name;
$errorClass = $error ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-red-500';
@endphp

<div class="mb-4">
  <label for="{{ $id }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
    {{ $label }}
    @if($required)
      <span class="text-red-500">*</span>
    @endif
  </label>
  
  <input 
    type="{{ $type }}" 
    id="{{ $id }}" 
    name="{{ $name }}" 
    value="{{ old($name, $value) }}"
    @if($required) required @endif
    @if($placeholder) placeholder="{{ $placeholder }}" @endif
    @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
    @if($pattern) pattern="{{ $pattern }}" @endif
    @if($ariaDescribedby) aria-describedby="{{ $ariaDescribedby }}" @endif
    class="w-full px-3 py-2 border {{ $errorClass }} rounded-lg focus:outline-none focus:ring-2 focus:border-transparent dark:bg-gray-700 dark:text-white dark:border-gray-600 transition-colors duration-200"
    {{ $attributes }}
  >
  
  @if($error)
    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
  @endif
  
  @if($slot->isNotEmpty())
    <div class="mt-1">
      {{ $slot }}
    </div>
  @endif
</div>
