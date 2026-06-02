@props([
    'label',
    'name',
    'id' => null,
    'placeholder' => '',
    'required' => false,
    'autocomplete' => 'new-password',
    'ariaDescribedby' => null,
    'value' => '',
    'error' => null,
    'showStrength' => false
])

@php
$id = $id ?? $name;
$errorClass = $error ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-red-500';
@endphp

<div class="mb-4" x-data="{ 
    showPassword: false, 
    strength: 0,
    updateStrength(val) {
        let strength = 0;
        if (val.length >= 8) strength++;
        if (/[a-z]/.test(val)) strength++;
        if (/[A-Z]/.test(val)) strength++;
        if (/[0-9]/.test(val)) strength++;
        if (/[^A-Za-z0-9]/.test(val)) strength++;
        this.strength = Math.min(strength, 4);
    }
}">
  <label for="{{ $id }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
    {{ $label }}
    @if($required)
      <span class="text-red-500">*</span>
    @endif
  </label>
  
  <div class="relative">
    <input 
      :type="showPassword ? 'text' : 'password'" 
      id="{{ $id }}" 
      name="{{ $name }}" 
      value="{{ old($name, $value) }}"
      @if($required) required @endif
      @if($placeholder) placeholder="{{ $placeholder }}" @endif
      @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
      @if($ariaDescribedby) aria-describedby="{{ $ariaDescribedby }}" @endif
      @if($showStrength) @input="updateStrength($event.target.value)" @endif
      class="w-full px-3 py-2 pr-10 border {{ $errorClass }} rounded-lg focus:outline-none focus:ring-2 focus:border-transparent dark:bg-gray-700 dark:text-white dark:border-gray-600 transition-colors duration-200"
      {{ $attributes }}
    >
    
    <button 
      type="button" 
      @click="showPassword = !showPassword"
      aria-label="Toggle password visibility"
      class="absolute inset-y-0 right-0 px-3 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-red-500 rounded">
      <span x-text="showPassword ? 'Hide' : 'Show'" aria-hidden="true"></span>
    </button>
  </div>
  
  @if($showStrength)
    <div x-show="$el.querySelector('input').value.length > 0" x-transition class="mt-2">
      <div class="flex items-center space-x-2">
        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
          <div class="h-2 rounded-full transition-all duration-300" 
               :class="{
                 'bg-red-500 w-1/4': strength <= 1,
                 'bg-orange-500 w-1/2': strength === 2,
                 'bg-yellow-500 w-3/4': strength === 3,
                 'bg-green-500 w-full': strength >= 4
               }"></div>
        </div>
        <span class="text-xs text-gray-600 dark:text-gray-400" 
              x-text="{
                1: 'Weak',
                2: 'Fair', 
                3: 'Good',
                4: 'Strong'
              }[strength] || 'Very Weak'"></span>
      </div>
    </div>
  @endif
  
  @if($error)
    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
  @endif
  
  @if($slot->isNotEmpty())
    <div class="mt-1">
      {{ $slot }}
    </div>
  @endif
</div>
