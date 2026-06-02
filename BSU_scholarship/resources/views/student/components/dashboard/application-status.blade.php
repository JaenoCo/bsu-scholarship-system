@props(['form'])

@php
  $overallProgress = $form ? $form->getOverallProgress() : 0;
  $requiredProgress = $form ? $form->getRequiredFieldsProgress() : 0;
  $isComplete = $form ? $form->isComplete() : false;
@endphp

<div class="mb-8">
  @if($form)
    @if($isComplete)
      <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center">
            <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <div>
              <h3 class="font-semibold text-green-800 dark:text-green-200">Application Status: Complete</h3>
              <p class="text-sm text-green-700 dark:text-green-300">All required fields are filled. Last updated: {{ $form->updated_at->format('M d, Y \a\t g:i A') }}</p>
            </div>
          </div>
        </div>
        
        <!-- Progress Bar -->
        <div class="mt-4">
          <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Overall Progress</span>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $overallProgress }}%</span>
          </div>
          <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 mb-2">
            <div class="bg-green-500 h-3 rounded-full transition-all duration-300" 
                 x-data="{ progress: {{ (int) $overallProgress }} }" 
                 x-bind:style="`width: ${progress}%`"></div>
          </div>
          <div class="flex justify-between items-center text-xs text-gray-600 dark:text-gray-400">
            <span>Required Fields: {{ $requiredProgress }}%</span>
            <span class="flex items-center gap-1">
                @if($form->getRequiredFieldsProgress() >= 100)
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    All required fields completed
                @else
                    Complete required fields to submit
                @endif
            </span>
          </div>
        </div>
      </div>
    @else
      <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center">
            <svg class="w-6 h-6 text-yellow-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            <div>
              <h3 class="font-semibold text-yellow-800 dark:text-yellow-200">Application Status: Incomplete</h3>
              <p class="text-sm text-yellow-700 dark:text-yellow-300">Please complete all required fields below. Last updated: {{ $form->updated_at->format('M d, Y \a\t g:i A') }}</p>
            </div>
          </div>
        </div>
        
        <!-- Progress Bar -->
        <div class="mt-4">
          <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Overall Progress</span>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $overallProgress }}%</span>
          </div>
          <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 mb-2">
            <div class="bg-yellow-500 h-3 rounded-full transition-all duration-300" 
                 x-data="{ progress: {{ (int) $overallProgress }} }" 
                 x-bind:style="`width: ${progress}%`"></div>
          </div>
          <div class="flex justify-between items-center text-xs text-gray-600 dark:text-gray-400">
            <span>Required Fields: {{ $requiredProgress }}%</span>
            <span class="flex items-center gap-1">
                @if($requiredProgress >= 100)
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    All required fields completed
                @else
                    Complete required fields to submit
                @endif
            </span>
          </div>
        </div>
      </div>
    @endif
  @else
    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-6">
      <div class="flex items-center">
        <svg class="w-6 h-6 text-yellow-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        <div>
          <h3 class="font-semibold text-yellow-800 dark:text-yellow-200">Application Status: Not Started</h3>
          <p class="text-sm text-yellow-700 dark:text-yellow-300">Complete all sections below to view available scholarships.</p>
        </div>
      </div>
      
      <!-- Progress Bar for No Form -->
      <div class="mt-4">
        <div class="flex justify-between items-center mb-2">
          <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Overall Progress</span>
          <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">0%</span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 mb-2">
          <div class="bg-yellow-500 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
        </div>
        <div class="flex justify-between items-center text-xs text-gray-600 dark:text-gray-400">
          <span>Required Fields: 0%</span>
          <span>Start filling out the form</span>
        </div>
      </div>
    </div>
  @endif
</div>
