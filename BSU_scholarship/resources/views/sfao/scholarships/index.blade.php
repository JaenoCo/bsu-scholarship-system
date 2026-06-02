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

      <!-- Row 2: Search Filter -->
      <div class="flex gap-4 items-end w-full">
          <div class="flex-1">
              <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Search</label>
              <div class="relative">
                  <input type="text" 
                         x-model.debounce.500ms="filters.search" 
                         placeholder="Search Scholarship..." 
                         class="block w-full px-3 py-2 text-base border border-red-500 dark:border-red-500 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-full dark:bg-gray-700 dark:text-white text-center">
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                  </div>
              </div>
          </div>
          
          <div class="w-auto flex flex-col items-center">
               <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Clear</label>
               <button type="button" @click="filters.search = ''" class="bg-white dark:bg-gray-700 text-gray-500 dark:text-gray-400 border border-red-500 dark:border-red-500 p-2 rounded-full hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bsu-red shadow-sm h-[38px] w-[38px] flex items-center justify-center" title="Clear Search">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
              </button>
          </div>
      </div>
  </div>

  <!-- Scholarships List Container -->
  <div id="scholarships-list-container">
    @include('sfao.scholarships.list', ['scholarships' => $scholarshipsAll])
  </div>
  


</div>
