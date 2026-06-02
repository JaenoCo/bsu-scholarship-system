@props(['form'])

<div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 border border-blue-200 dark:border-blue-700 rounded-lg p-6 hover:shadow-lg transition-shadow">
  <div class="flex items-center mb-4">
    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mr-4">
      <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
      </svg>
    </div>
    <div>
      <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200">Personal Data</h3>
      <p class="text-sm text-blue-600 dark:text-blue-300">Basic information & contact</p>
    </div>
  </div>
  
  <div class="space-y-2 mb-4">
    <div class="flex justify-between text-sm">
      <span class="text-gray-600 dark:text-gray-300">Name:</span>
      <span class="font-medium text-gray-800 dark:text-gray-200">
        @if($form && $form->first_name)
          {{ $form->first_name }} {{ $form->last_name }}
        @else
          <span class="text-gray-400">Not provided</span>
        @endif
      </span>
    </div>
    <div class="flex justify-between text-sm">
      <span class="text-gray-600 dark:text-gray-300">Age:</span>
      <span class="font-medium text-gray-800 dark:text-gray-200">
        @if($form && $form->age)
          {{ $form->age }} years old
        @else
          <span class="text-gray-400">Not provided</span>
        @endif
      </span>
    </div>
    <div class="flex justify-between text-sm">
      <span class="text-gray-600 dark:text-gray-300">Address:</span>
      <span class="font-medium text-gray-800 dark:text-gray-200">
        @if($form && $form->town_city)
          {{ $form->town_city }}, {{ $form->province }}
        @else
          <span class="text-gray-400">Not provided</span>
        @endif
      </span>
    </div>
  </div>
  
  <div class="flex items-center justify-between">
    <div class="flex items-center">
      @if($form && $form->first_name && $form->age && $form->town_city)
        <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <span class="text-sm text-green-600 dark:text-green-400 font-medium">Complete</span>
      @else
        <svg class="w-5 h-5 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        <span class="text-sm text-yellow-600 dark:text-yellow-400 font-medium">Incomplete</span>
      @endif
    </div>
    <a href="{{ route('student.forms.application_form') }}?stage=1" 
       class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
      <span class="flex items-center gap-1">
          Edit
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
          </svg>
      </span>
    </a>
  </div>
</div>
