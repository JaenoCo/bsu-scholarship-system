@props(['form'])

<div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 border border-green-200 dark:border-green-700 rounded-lg p-6 hover:shadow-lg transition-shadow">
  <div class="flex items-center mb-4">
    <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center mr-4">
      <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
      </svg>
    </div>
    <div>
      <h3 class="text-lg font-semibold text-green-800 dark:text-green-200">Academic Data</h3>
      <p class="text-sm text-green-600 dark:text-green-300">Program, grades & achievements</p>
    </div>
  </div>
  
  <div class="space-y-2 mb-4">
    <div class="flex justify-between text-sm">
      <span class="text-gray-600 dark:text-gray-300">Program:</span>
      <span class="font-medium text-gray-800 dark:text-gray-200">
        @if($form && $form->program)
          {{ $form->program }}
        @else
          <span class="text-gray-400">Not provided</span>
        @endif
      </span>
    </div>
    <div class="flex justify-between text-sm">
      <span class="text-gray-600 dark:text-gray-300">Year Level:</span>
      <span class="font-medium text-gray-800 dark:text-gray-200">
        @if($form && $form->year_level)
          {{ $form->year_level }}
        @else
          <span class="text-gray-400">Not provided</span>
        @endif
      </span>
    </div>
    <div class="flex justify-between text-sm">
      <span class="text-gray-600 dark:text-gray-300">GWA:</span>
      <span class="font-medium text-gray-800 dark:text-gray-200">
        @if($form && $form->previous_gwa)
          {{ number_format($form->previous_gwa, 2) }}
        @else
          <span class="text-gray-400">Not provided</span>
        @endif
      </span>
    </div>
  </div>
  
  <div class="flex items-center justify-between">
    <div class="flex items-center">
      @if($form && $form->program && $form->year_level && $form->previous_gwa)
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
    <a href="{{ route('student.forms.application_form') }}?stage=2" 
       class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 text-sm font-medium">
      Edit →
    </a>
  </div>
</div>
