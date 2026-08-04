@props(['user', 'sfaoCampus'])

<!-- Navigation - Scrollable -->
  <!-- Profile Section -->
  <div class="p-6">
    <div class="flex flex-col items-center">
        <img src="{{ $user && $user->profile_picture ? asset('storage/profile_pictures/' . $user->profile_picture) . '?' . now()->timestamp : asset('images/default-avatar.png') }}"
             alt="Profile Picture"
             class="h-20 w-20 rounded-full border-4 border-gray-600 object-cover mb-3">
        <h3 class="text-lg font-bold text-white text-center">{{ $user?->name ?? 'SFAO User' }}</h3>
        <p class="text-sm text-gray-300 font-medium">SFAO Staff</p>
    </div>
  </div>

<nav class="mt-6 px-4 pb-4 overflow-y-auto flex-1 space-y-4 custom-scrollbar" x-data="{
    openMenu: null,
    activeTab: 'analytics_scholarships',
    init() {
        const params = new URLSearchParams(window.location.search);
        this.activeTab = this.normalizeTab(params.get('tabs') || localStorage.getItem('sfaoTab') || 'analytics_scholarships');
        this.openMenu = this.sectionForTab(this.activeTab);
        this.markActiveNav();
        this.$watch('activeTab', () => this.markActiveNav());
        this.$nextTick(() => this.$dispatch('sidebar-accordion-open', this.openMenu));
    },
    toggleMenu(menu) {
        this.openMenu = this.openMenu === menu ? null : menu;
    },
    normalizeTab(tab) {
        const map = {
            overview: 'analytics',
            all_scholarships: 'scholarships',
            private_scholarships: 'scholarships-private',
            government_scholarships: 'scholarships-government',
            all_applicants: 'applicants',
            applicants_in_progress: 'applicants-in_progress',
            applicants_pending: 'applicants-pending',
            applicants_approved: 'applicants-approved',
            all_scholars: 'scholars',
            new_scholars: 'scholars-new',
            old_scholars: 'scholars-old',
            reports_student_summary: 'reports-student_summary',
            reports_grant_summary: 'reports-grant_summary',
            account_settings: 'account-info'
        };
        return map[tab] || tab || 'analytics_scholarships';
    },
    sectionForTab(tab) {
        const normalized = this.normalizeTab(tab);
        if (normalized.startsWith('analytics')) return 'analytics';
        if (normalized.startsWith('scholarships')) return 'scholarships';
        if (normalized.startsWith('applicants')) return 'applicants';
        if (normalized.startsWith('scholars')) return 'scholars';
        if (normalized === 'all-app-forms' || normalized === 'up-app-form' || normalized === 'import-scholarships') return 'application_forms';
        if (normalized.startsWith('reports')) return 'reports';
        if (normalized.startsWith('account')) return 'settings';
        return null;
    },
    switchTab(tab) {
        this.activeTab = this.normalizeTab(tab);
        this.openMenu = this.sectionForTab(tab);
        this.$dispatch('sidebar-accordion-open', this.openMenu);
        $dispatch('switch-tab', tab); 
    },
    markActiveNav() {
        this.$nextTick(() => {
            this.$root.querySelectorAll('button').forEach((button) => {
                const click = button.getAttribute('@click') || button.getAttribute('x-on:click') || '';
                const match = click.match(/switch-tab'\s*,\s*'([^']+)'/);
                const active = match && this.normalizeTab(match[1]) === this.activeTab;
                button.classList.toggle('sidebar-tab-active', !!active);
                if (active) button.setAttribute('aria-current', 'page');
                else button.removeAttribute('aria-current');
            });

            this.$root.querySelectorAll('.space-y-1').forEach((section) => {
                const trigger = section.querySelector(':scope > button');
                if (!trigger) return;
                const hasActiveChild = !!section.querySelector('.sidebar-tab-active');
                trigger.classList.toggle('sidebar-section-active', hasActiveChild && !trigger.classList.contains('sidebar-tab-active'));
            });
        });
    }
}"
x-on:switch-tab.window="activeTab = normalizeTab($event.detail); openMenu = sectionForTab($event.detail); $dispatch('sidebar-accordion-open', sectionForTab($event.detail))">
  
  <!-- Analytics Dropdown -->
  <div class="space-y-1">
    <button @click="toggleMenu('analytics')" 
            class="w-full flex items-center justify-between px-4 py-2 text-sm font-semibold text-white uppercase tracking-wider focus:outline-none bg-transparent border-2 border-transparent rounded-lg transition-colors">
      <div class="flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <span>Analytics</span>
      </div>
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200" :class="openMenu === 'analytics' ? 'transform rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>
    
    <div x-show="openMenu === 'analytics'" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="space-y-1">
        <button @click="$dispatch('switch-tab', 'analytics_scholarships')"
                class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M12 14l9-5-9-5-9 5 9 5z" />
            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
          </svg>
          Scholarships
        </button>
        <button @click="$dispatch('switch-tab', 'analytics_applications')"
                class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
          Applicants
        </button>
        <button @click="$dispatch('switch-tab', 'analytics_scholars')"
                class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M12 14l9-5-9-5-9 5 9 5z" />
            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
          </svg>
          Scholars
        </button>
    </div>
  </div>

   <!-- Scholarships Dropdown -->
  <div class="space-y-1">
    <button @click="toggleMenu('scholarships')" 
            class="w-full flex items-center justify-between px-4 py-2 text-sm font-semibold text-white uppercase tracking-wider focus:outline-none bg-transparent border-2 border-transparent rounded-lg transition-colors">
      <div class="flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path d="M12 14l9-5-9-5-9 5 9 5z" />
          <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
        </svg>
        <span>Scholarships</span>
      </div>
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200" :class="openMenu === 'scholarships' ? 'transform rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>
    <div x-show="openMenu === 'scholarships'" x-cloak class="space-y-1">
        <button @click="$dispatch('switch-tab', 'scholarships')"
                class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
             <path d="M12 14l9-5-9-5-9 5 9 5z" />
             <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
          </svg>
          All
        </button>
        <button @click="$dispatch('switch-tab', 'scholarships-private')" class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            Private
        </button>
        <button @click="$dispatch('switch-tab', 'scholarships-government')" class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
            </svg>
            Government
        </button>
    </div>
  </div>
  
  <!-- Applicants Dropdown -->
  <div class="space-y-1">
     <button @click="toggleMenu('applicants')" 
            class="w-full flex items-center justify-between px-4 py-2 text-sm font-semibold text-white uppercase tracking-wider focus:outline-none bg-transparent border-2 border-transparent rounded-lg transition-colors">
       <div class="flex items-center gap-2">
         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
         </svg>
         <span>Applicants</span>
       </div>
       <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200" :class="openMenu === 'applicants' ? 'transform rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
       </svg>
     </button>
     <div x-show="openMenu === 'applicants'" x-cloak class="space-y-1">
          <button @click="$dispatch('switch-tab', 'applicants')" class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
             </svg>
             All
          </button>
          <button @click="$dispatch('switch-tab', 'applicants-in_progress')" class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
             </svg>
             In Progress
          </button>
          <button @click="$dispatch('switch-tab', 'applicants-pending')" class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
             </svg>
             Pending
          </button>
          <button @click="$dispatch('switch-tab', 'applicants-approved')" class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
             </svg>
             Approved
          </button>
     </div>
   </div>
 
    <!-- Scholars Dropdown -->
   <div class="space-y-1">
     <button @click="toggleMenu('scholars')" class="w-full flex items-center justify-between px-4 py-2 text-sm font-semibold text-white uppercase bg-transparent">
       <div class="flex items-center gap-2">
         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
           <path d="M12 14l9-5-9-5-9 5 9 5z" />
           <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
         </svg>
         <span>Scholars</span>
       </div>
       <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200" :class="openMenu === 'scholars' ? 'transform rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
     </button>
     <div x-show="openMenu === 'scholars'" x-cloak class="space-y-1">
         <button @click="$dispatch('switch-tab', 'scholars')" class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path d="M12 14l9-5-9-5-9 5 9 5z" />
               <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
             </svg>
             All
         </button>
         <button @click="$dispatch('switch-tab', 'scholars-new')" class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 3.214L18 21l-6-3-6 3 2.714-5.786L3 12l5.714-3.214L10 2z" />
             </svg>
             New
         </button>
         <button @click="$dispatch('switch-tab', 'scholars-old')" class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
             </svg>
             Old
         </button>
     </div>
   </div>
 
   <!-- Application Forms Dropdown -->
   <div class="space-y-1">
     <button @click="toggleMenu('application_forms')" 
             class="w-full flex items-center justify-between px-4 py-2 text-sm font-semibold text-white uppercase tracking-wider focus:outline-none bg-transparent border-2 border-transparent rounded-lg transition-colors">
       <div class="flex items-center gap-2">
         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
         </svg>
         <span class="whitespace-nowrap">App Forms</span>
       </div>
       <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200" :class="openMenu === 'application_forms' ? 'transform rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
       </svg>
     </button>
     <div x-show="openMenu === 'application_forms'" x-cloak class="space-y-1">
         <button @click="$dispatch('switch-tab', 'all-app-forms')" class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
             </svg>
             All
          </button>
          <button @click="$dispatch('switch-tab', 'up-app-form')" class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
             </svg>
             Upload
          </button>
     </div>
   </div>

  <!-- Reports Dropdown -->
  <div class="space-y-1">
    <button @click="toggleMenu('reports')" class="w-full flex items-center justify-between px-4 py-2 text-sm font-semibold text-white uppercase bg-transparent">
      <div class="flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <span>Reports</span>
      </div>
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200" :class="openMenu === 'reports' ? 'transform rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
    </button>
    <div x-show="openMenu === 'reports'" x-cloak class="space-y-1">
         <a href="{{ route('sfao.reports.student-summary', ['student_type' => 'applicants', 'campus_id' => 'all']) }}" class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Student Summary
         </a>
         <a href="{{ route('sfao.reports.grant-summary', ['campus_id' => 4]) }}" class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Grant Summary
         </a>
    </div>
  </div>

  <!-- Settings Dropdown -->
  <div class="space-y-1">
    <button @click="toggleMenu('settings')" class="w-full flex items-center justify-between px-4 py-2 text-sm font-semibold text-white uppercase bg-transparent">
      <div class="flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <span>Settings</span>
      </div>
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200" :class="openMenu === 'settings' ? 'transform rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
    </button>
    <div x-show="openMenu === 'settings'" x-cloak class="space-y-1">
         <button @click="$dispatch('switch-tab', 'account-info')" class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Account Information
         </button>
         <button @click="$dispatch('switch-tab', 'account-security')" class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            Login & Security
         </button>
    </div>
  </div>

</nav>
