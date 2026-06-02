@props(['form'])

<div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 border border-red-200 dark:border-red-700 rounded-lg p-6 hover:shadow-lg transition-shadow">
  <div class="flex items-center mb-4">
    <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center mr-4">
      <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
    </div>
    <div>
      <h3 class="text-lg font-semibold text-red-800 dark:text-red-200">Certification</h3>
      <p class="text-sm text-red-600 dark:text-red-300">Signature & verification</p>
    </div>
  </div>
  
  <div class="space-y-2 mb-4">
    <div class="flex justify-between text-sm">
      <span class="text-gray-600 dark:text-gray-300">Signature:</span>
      <span class="font-medium text-gray-800 dark:text-gray-200">
        @if($form && $form->student_signature)
          {{ $form->student_signature }}
        @else
          <span class="text-gray-400">Not provided</span>
        @endif
      </span>
    </div>
    <div class="flex justify-between text-sm">
      <span class="text-gray-600 dark:text-gray-300">Date Signed:</span>
      <span class="font-medium text-gray-800 dark:text-gray-200">
        @if($form && $form->date_signed)
          {{ $form->date_signed->format('M d, Y') }}
        @else
          <span class="text-gray-400">Not provided</span>
        @endif
      </span>
    </div>
    <div class="flex justify-between text-sm">
      <span class="text-gray-600 dark:text-gray-300">Status:</span>
      <span class="font-medium text-gray-800 dark:text-gray-200">
        @if($form && $form->student_signature && $form->date_signed)
          <span class="text-green-600 dark:text-green-400">Verified</span>
        @else
          <span class="text-yellow-600 dark:text-yellow-400">Pending</span>
        @endif
      </span>
    </div>
  </div>
  
  <div class="flex items-center justify-between">
    <div class="flex items-center">
      @if($form && $form->student_signature && $form->date_signed)
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
    <a href="{{ route('student.forms.application_form') }}?stage=5" 
       class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium">
      <span class="flex items-center gap-1">
          Edit
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
          </svg>
      </span>
    </a>
  </div>
</div>
