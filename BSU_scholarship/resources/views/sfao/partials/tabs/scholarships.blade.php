<div x-show="tab === 'scholarships' || tab.startsWith('scholarships-')" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-cloak
     class="px-4 py-6"
     x-data="sfaoScholarshipsFilter()"
     x-init="$watch('tab', value => handleTabChange(value))">
     
  <!-- Header -->
  <div class="mb-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
          <span class="flex items-center gap-2">
            <template x-if="filters.type === 'all'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-900 dark:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                </svg>
            </template>
            <template x-if="filters.type === 'private'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd" />
                </svg>
            </template>
            <template x-if="filters.type === 'government'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd" />
                </svg>
            </template>
            <span x-text="getHeaderTitle()"></span>
          </span>
        </h2>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
          <span x-text="getHeaderDescription()"></span>
        </p>
      </div>
    </div>
  </div>

  <!-- Campus Information -->
  <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
    <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200 mb-2">
      Managing Campus: {{ $sfaoCampus->name }}
    </h3>
    <p class="text-sm text-blue-600 dark:text-blue-300">
      @if($sfaoCampus->extensionCampuses->count() > 0)
        Including extension campuses: 
        {{ $sfaoCampus->extensionCampuses->pluck('name')->join(', ') }}
      @else
        No extension campuses under this constituent campus.
      @endif
    </p>
  </div>

  <!-- Sorting and Filtering Controls -->
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
      <div class="flex flex-wrap gap-4 items-end">
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
          


          <!-- Actions -->
          <div class="flex flex-col items-center">
              <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider text-center">Clear</label>
              <button type="button" @click="resetFilters()" class="bg-white dark:bg-gray-700 text-gray-500 dark:text-gray-400 border border-red-500 dark:border-red-500 p-2 rounded-full hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bsu-red shadow-sm h-[38px] w-[38px] flex items-center justify-center" title="Reset Filters">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
              </button>
          </div>
      </div>
  </div>

  <!-- Scholarships List Container -->
  <div id="scholarships-list-container">
    @include('sfao.partials.tabs.scholarships_list', ['scholarships' => $scholarshipsAll])
  </div>
  
  <script>
      document.addEventListener('alpine:init', () => {
          Alpine.data('sfaoScholarshipsFilter', () => ({
              filters: {
                  sort_by: localStorage.getItem('sfaoScholarshipsSortBy') || 'name',
                  sort_order: localStorage.getItem('sfaoScholarshipsSortOrder') || 'asc',
                  type: localStorage.getItem('sfaoScholarshipsType') || 'all'
              },

              init() {
                  this.$watch('filters.sort_by', (value) => {
                      localStorage.setItem('sfaoScholarshipsSortBy', value);
                      this.fetchScholarships();
                  });
                  this.$watch('filters.sort_order', (value) => {
                      localStorage.setItem('sfaoScholarshipsSortOrder', value);
                      this.fetchScholarships();
                  });
                   this.$watch('filters.type', (value) => {
                      localStorage.setItem('sfaoScholarshipsType', value);
                      this.fetchScholarships();
                  });

                  this.updatePaginationLinks();
                  
                  if (this.filters.type !== 'all') {
                      this.fetchScholarships();
                  }
              },

              fetchScholarships(page = 1) {
                  const params = new URLSearchParams({
                      tab: 'scholarships',
                      sort_by: this.filters.sort_by,
                      sort_order: this.filters.sort_order,
                      type_filter: this.filters.type,
                      page_scholarships: page
                  });

                  fetch(`{{ route('sfao.dashboard') }}?${params.toString()}`, {
                      headers: { 'X-Requested-With': 'XMLHttpRequest' }
                  })
                  .then(response => response.json())
                  .then(data => {
                      document.getElementById('scholarships-list-container').innerHTML = data.html;
                      this.updatePaginationLinks();
                  })
                  .catch(error => console.error('Error fetching scholarships:', error));
              },

              updatePaginationLinks() {
                  const container = document.getElementById('scholarships-list-container');
                  const links = container.querySelectorAll('a.page-link'); 
                  links.forEach(link => {
                      link.addEventListener('click', (e) => {
                          e.preventDefault();
                          const url = new URL(link.href);
                          const page = url.searchParams.get('page_scholarships') || 1;
                          this.fetchScholarships(page);
                      });
                  });
              },

              resetFilters() {
                  this.filters.sort_by = 'name';
                  this.filters.sort_order = 'asc';
                  this.filters.type = 'all';
              },

              getHeaderTitle() {
                  if (this.filters.type === 'private') {
                      return 'Private Scholarships';
                  } else if (this.filters.type === 'government') {
                      return 'Government Scholarships';
                  }
                  return 'All Scholarships';
              },

              getHeaderDescription() {
                  if (this.filters.type === 'private') {
                      return 'Private scholarship programs';
                  } else if (this.filters.type === 'government') {
                      return 'Government scholarship programs';
                  }
                  return 'View all available scholarship programs';
              },

              handleTabChange(tab) {
                  if (tab === 'scholarships') {
                      if (this.filters.type !== 'all') {
                          this.filters.type = 'all';
                      }
                  } else if (tab.startsWith('scholarships-')) {
                      const type = tab.replace('scholarships-', '');
                      if (this.filters.type !== type) {
                          this.filters.type = type;
                      }
                  }
              }
          }));
      });
  </script>

</div>
