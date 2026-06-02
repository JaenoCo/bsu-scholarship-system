@props(['form'])

<div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 border border-purple-200 dark:border-purple-700 rounded-lg p-6 hover:shadow-lg transition-shadow">
  <div class="flex items-center mb-4">
    <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center mr-4">
      <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
      </svg>
    </div>
    <div>
      <h3 class="text-lg font-semibold text-purple-800 dark:text-purple-200">Family Data</h3>
      <p class="text-sm text-purple-600 dark:text-purple-300">Parents & family information</p>
    </div>
  </div>
  
  <div class="space-y-2 mb-4">
    <div class="flex justify-between text-sm">
      <span class="text-gray-600 dark:text-gray-300">Father:</span>
      <span class="font-medium text-gray-800 dark:text-gray-200">
        @if($form && $form->father_name)
          {{ $form->father_name }}
        @else
          <span class="text-gray-400">Not provided</span>
        @endif
      </span>
    </div>
    <div class="flex justify-between text-sm">
      <span class="text-gray-600 dark:text-gray-300">Mother:</span>
      <span class="font-medium text-gray-800 dark:text-gray-200">
        @if($form && $form->mother_name)
          {{ $form->mother_name }}
        @else
          <span class="text-gray-400">Not provided</span>
        @endif
      </span>
    </div>
    <div class="flex justify-between text-sm">
      <span class="text-gray-600 dark:text-gray-300">Siblings:</span>
      <span class="font-medium text-gray-800 dark:text-gray-200">
        @if($form && $form->siblings_count)
          {{ $form->siblings_count }} siblings
        @else
          <span class="text-gray-400">Not provided</span>
        @endif
      </span>
    </div>
    <div class="flex justify-between text-sm">
      <span class="text-gray-600 dark:text-gray-300">Annual Income:</span>
      <span class="font-medium text-gray-800 dark:text-gray-200">
        @if($form && $form->estimated_gross_annual_income)
          @php
            $incomeLabels = [
              'not_over_250000' => 'Not over P 250,000.00',
              'over_250000_not_over_400000' => 'Over P 250,000 but not over P 400,000',
              'over_400000_not_over_800000' => 'Over P 400,000 but not over P 800,000',
              'over_800000_not_over_2000000' => 'Over P 800,000 but not over P 2,000,000',
              'over_2000000_not_over_8000000' => 'Over P 2,000,000 but not over P 8,000,000',
              'over_8000000' => 'Over P 8,000,000'
            ];
          @endphp
          {{ $incomeLabels[$form->estimated_gross_annual_income] ?? $form->estimated_gross_annual_income }}
        @else
          <span class="text-gray-400">Not provided</span>
        @endif
      </span>
    </div>
  </div>
  
  <div class="flex items-center justify-between">
    <div class="flex items-center">
      @if($form && $form->father_name && $form->mother_name && $form->estimated_gross_annual_income)
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
    <a href="{{ route('student.forms.application_form') }}?stage=3" 
       class="text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 text-sm font-medium">
      <span class="flex items-center gap-1">
          Edit
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
          </svg>
      </span>
    </a>
  </div>
</div>
