@props(['form'])

<div class="bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/20 dark:to-indigo-800/20 border border-indigo-200 dark:border-indigo-700 rounded-lg p-6 hover:shadow-lg transition-shadow">
  <div class="flex items-center mb-4">
    <div class="w-12 h-12 bg-indigo-500 rounded-lg flex items-center justify-center mr-4">
      <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
      </svg>
    </div>
    <div>
      <h3 class="text-lg font-semibold text-indigo-800 dark:text-indigo-200">Essay / Question</h3>
      <p class="text-sm text-indigo-600 dark:text-indigo-300">Reason for applying</p>
    </div>
  </div>
  
  <div class="space-y-2 mb-4">
    <div class="text-sm">
      <span class="text-gray-600 dark:text-gray-300">Reason for Applying:</span>
      <p class="mt-2 text-gray-800 dark:text-gray-200 font-medium">
        @if($form && $form->reason_for_applying)
          {{ \Illuminate\Support\Str::limit($form->reason_for_applying, 150) }}
          @if(strlen($form->reason_for_applying) > 150)
            <span class="text-gray-500">...</span>
          @endif
        @else
          <span class="text-gray-400 italic">Not provided</span>
        @endif
      </p>
    </div>
  </div>
  
  <div class="flex items-center justify-between">
    <div class="flex items-center">
      @if($form && $form->reason_for_applying && strlen(trim($form->reason_for_applying)) > 0)
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
    <a href="{{ route('student.forms.application_form') }}?stage=4" 
       class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium">
      <span class="flex items-center gap-1">
          Edit
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
          </svg>
      </span>
    </a>
  </div>
</div>
