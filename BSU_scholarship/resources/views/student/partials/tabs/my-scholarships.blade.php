<div x-data="{
       openWarning: false,
       selected: null
     }">

    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-bsu-red" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    My Scholarships
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    Scholarships you are currently enrolled in.
                </p>
            </div>
        </div>
    </div>

    @if ($myScholarships->count())
      <!-- Scholarships List -->
      <div class="space-y-8">
          @foreach ($myScholarships as $scholarship)
            <div x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100">
                 
                 @include('central.partials.components.scholarship-card', [
                    'scholarship' => $scholarship,
                    'role' => 'student',
                    'hasActiveApplication' => false, // Already a scholar
                    'fillPercentage' => 0 // Optional
                 ])
            </div>
          @endforeach
      </div>

    @else
      <div class="text-center py-12">
        <div class="text-gray-400 mb-4">
          <svg class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-400 mb-2">No Active Scholarships</h3>
        <p class="text-gray-500 dark:text-gray-500">You are not currently enrolled in any scholarships.</p>
      </div>
    @endif
</div>
