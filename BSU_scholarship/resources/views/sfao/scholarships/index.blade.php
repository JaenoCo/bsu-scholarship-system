<div x-show="tab === 'scholarships' || tab.startsWith('scholarships-')" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-cloak
     class="px-4 py-6"
     x-data='sfaoScholarshipsFilter({ 
        routeUrl: @json(route("sfao.dashboard")), 
        campusOptions: @json($allCampuses->map(fn($c) => ["id" => $c->id, "name" => $c->name])) 
     })'
     x-init="$watch('tab', value => handleTabChange(value))">
     
  <!-- Header -->
  <!-- Header Removed -->

  <!-- Campus Information -->
  <!-- Campus Information Removed -->

  <!-- Sorting and Filtering Controls -->
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
      <!-- Row 1: Filters -->
      <div class="flex flex-wrap gap-4 items-end mb-4">
          <!-- Campus Filter -->
          <div class="flex-1 min-w-[140px]">
              <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Campus</label>
              <div class="relative">
                  <select x-model="filters.campus" class="block w-full px-3 py-2 text-base border border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none">
                      <option value="all">All</option>
                      <template x-for="campus in campusOptions" :key="campus.id">
                        <option :value="campus.id" x-text="campus.name"></option>
                      </template>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                       <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                  </div>
              </div>
          </div>

          <!-- Eligibility (Type) -->
          <div class="flex-1 min-w-[140px]">
              <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Eligibility</label>
              <div class="relative">
                  <select x-model="filters.type" class="block w-full px-3 py-2 text-base border border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none">
                      <option value="all">All</option>
                      <option value="gwa">GWA Requirement</option>
                      <option value="year_level">Year Level</option>
                      <option value="income">Income Bracket</option>
                      <option value="disability">Disability Status</option>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                       <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                  </div>
              </div>
          </div>

          <!-- Sort By -->
          <div class="flex-1 min-w-[140px]">
              <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Sort By</label>
              <div class="relative">
                  <select x-model="filters.sort_by" class="block w-full px-3 py-2 text-base border border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none">
                      <option value="name">Scholarship Name</option>
                      <option value="created_at">Date Created</option>
                      <option value="submission_deadline">Deadline</option>
                      <option value="grant_amount">Grant Amount</option>
                      <option value="slots_available">Available Slots</option>
                      <option value="applications_count">Applications Count</option>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                       <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                  </div>
              </div>
          </div>

          <!-- Order -->
          <div class="flex-1 min-w-[140px]">
              <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Order</label>
              <div class="relative">
                  <select x-model="filters.sort_order" class="block w-full px-3 py-2 text-base border border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center appearance-none">
                      <option value="asc">Ascending</option>
                      <option value="desc">Descending</option>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                       <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                  </div>
              </div>
          </div>
      </div>
  </div>

  <!-- Scholarships List Container -->
  <div id="scholarships-list-container">
    @include('sfao.scholarships.list', ['scholarships' => $scholarshipsAll])
  </div>
  


</div>
