@props(['user'])

<!-- Profile Section -->
<div class="p-6">
    <div class="flex flex-col items-center">
        <img src="{{ $user && $user->profile_picture ? asset('storage/profile_pictures/' . $user->profile_picture) . '?' . now()->timestamp : asset('images/default-avatar.png') }}"
             alt="Profile Picture"
             class="h-20 w-20 rounded-full border-4 border-gray-600 object-cover mb-3">
        <h3 class="text-lg font-bold text-white text-center">{{ $user?->name ?? 'Student' }}</h3>
        <p class="text-sm text-gray-300 font-medium">Student</p>
    </div>
</div>

<!-- Navigation - Scrollable -->
<nav class="mt-6 px-4 pb-4 overflow-y-auto flex-1 space-y-4 custom-scrollbar" x-data="{
    unreadCount: {{ $unreadCount ?? 0 }},
    unreadCountScholarships: {{ $unreadCountScholarships ?? 0 }},
    unreadCountStatus: {{ $unreadCountStatus ?? 0 }},
    unreadCountComments: {{ $unreadCountComments ?? 0 }},
    activeTab: 'announcements',
    openMenu: null,

    init() {
        const params = new URLSearchParams(window.location.search);
        this.activeTab = this.normalizeTab(params.get('tab') || localStorage.getItem('studentActiveTab') || 'announcements');
        this.openMenu = this.sectionForTab(this.activeTab);
        this.markActiveNav();
        this.$watch('activeTab', () => this.markActiveNav());
        this.$nextTick(() => this.$dispatch('sidebar-accordion-open', this.openMenu));
    },

    normalizeTab(tab) {
        const map = {
            scholarships: 'all_scholarships',
            'applied-scholarships': 'applied_scholarships',
            notifications: 'all_notifications',
            account: 'account_settings'
        };
        return map[tab] || tab || 'announcements';
    },

    sectionForTab(tab) {
        const normalized = this.normalizeTab(tab);
        if (['all_scholarships', 'private_scholarships', 'government_scholarships'].includes(normalized)) return 'scholarships';
        if (['sfao_form', 'all-app-forms'].includes(normalized)) return 'application_forms';
        if (['applied_scholarships', 'application_tracking', 'announcements'].includes(normalized)) return 'applications';
        if (['all_notifications', 'scholarship_notifications', 'status_updates', 'comments'].includes(normalized)) return 'notifications';
        return null;
    },

    // Helper to dispatch
    switchTab(tab) {
        this.activeTab = this.normalizeTab(tab);
        this.openMenu = this.sectionForTab(tab);
        this.$dispatch('sidebar-accordion-open', this.openMenu);
        $dispatch('switch-tab', tab);
    },

    toggleMenu(menu) {
        this.openMenu = this.openMenu === menu ? null : menu;
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
x-on:switch-tab.window="activeTab = normalizeTab($event.detail); openMenu = sectionForTab($event.detail); $dispatch('sidebar-accordion-open', sectionForTab($event.detail))"
@notification-changed.window="
    const status = $event.detail.status;
    const type = $event.detail.type;
    if (status === 'read') {
        if (unreadCount > 0) unreadCount--;
        // Update specific counters if needed
        if (type === 'scholarship_created' && unreadCountScholarships > 0) unreadCountScholarships--;
        if (type === 'application_status' && unreadCountStatus > 0) unreadCountStatus--;
        if (type === 'sfao_comment' && unreadCountComments > 0) unreadCountComments--;
    } else if (status === 'unread') {
        unreadCount++;
        if (type === 'scholarship_created') unreadCountScholarships++;
        if (type === 'application_status') unreadCountStatus++;
        if (type === 'sfao_comment') unreadCountComments++;
    }
"
@notifications-read-all.window="
    unreadCount = 0;
    unreadCountScholarships = 0;
    unreadCountStatus = 0;
    unreadCountComments = 0;
">

<!-- Scholarships Dropdown -->
<div class="space-y-1">
    <button @click="toggleMenu('scholarships')" 
            class="w-full flex items-center justify-between px-4 py-2 text-sm font-semibold text-white uppercase tracking-wider focus:outline-none bg-transparent border-2 border-transparent rounded-lg transition-colors">
      <div class="flex items-center gap-2 overflow-hidden">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path d="M12 14l9-5-9-5-9 5 9 5z" />
          <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
        </svg>
        <span class="whitespace-nowrap truncate">Scholarships</span>
      </div>
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200 flex-shrink-0" :class="openMenu === 'scholarships' ? 'transform rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>
    <div x-show="openMenu === 'scholarships'" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="space-y-1">
        <button @click="$dispatch('switch-tab', 'all_scholarships')"
                class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
             <path d="M12 14l9-5-9-5-9 5 9 5z" />
             <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
          </svg>
          All Scholarships
        </button>
        <button @click="$dispatch('switch-tab', 'private_scholarships')" class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            Private Scholarships
        </button>
        <button @click="$dispatch('switch-tab', 'government_scholarships')" class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
            </svg>
            Government Scholarships
        </button>
    </div>
</div>

<!-- Application Forms Dropdown -->
<div class="space-y-1">
    <button @click="toggleMenu('application_forms')" 
            class="w-full flex items-center justify-between px-4 py-2 text-sm font-semibold text-white uppercase tracking-wider focus:outline-none bg-transparent border-2 border-transparent rounded-lg transition-colors">
      <div class="flex items-center gap-2 overflow-hidden">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <span class="whitespace-nowrap truncate">App Forms</span>
      </div>
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200 flex-shrink-0" :class="openMenu === 'application_forms' ? 'transform rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>
    <div x-show="openMenu === 'application_forms'" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="space-y-1">
         <button @click="$dispatch('switch-tab', 'sfao_form')" class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            SFAO Application Form
         </button>
         <button @click="$dispatch('switch-tab', 'all-app-forms')" class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Additional Application Forms
         </button>
    </div>
</div>

<!-- Applications Dropdown -->
<div class="space-y-1">
    <button @click="toggleMenu('applications')" 
            class="w-full flex items-center justify-between px-4 py-2 text-sm font-semibold text-white uppercase tracking-wider focus:outline-none bg-transparent border-2 border-transparent rounded-lg transition-colors">
      <div class="flex items-center gap-2 overflow-hidden">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path d="M12 14l9-5-9-5-9 5 9 5z" />
          <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
        </svg>
        <span class="whitespace-nowrap truncate">Applications</span>
      </div>
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200 flex-shrink-0" :class="openMenu === 'applications' ? 'transform rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>
    <div x-show="openMenu === 'applications'" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="space-y-1">
         <button @click="$dispatch('switch-tab', 'applied_scholarships')" class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path d="M12 14l9-5-9-5-9 5 9 5z" />
               <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
            </svg>
            Applied Scholarships
         </button>
         <button @click="$dispatch('switch-tab', 'application_tracking')" class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Application Tracking
         </button>
         <button @click="$dispatch('switch-tab', 'announcements')" class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            Announcements
         </button>
    </div>
</div>

<!-- Notifications Dropdown -->
<div class="space-y-1">
    <button @click="toggleMenu('notifications')" 
            class="w-full flex items-center justify-between px-4 py-2 text-sm font-semibold text-white uppercase tracking-wider focus:outline-none bg-transparent border-2 border-transparent rounded-lg transition-colors">
      <div class="flex items-center gap-2 overflow-hidden">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <span class="whitespace-nowrap truncate">Notifications</span>
         <span x-show="unreadCount > 0" 
            x-text="unreadCount"
            class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5 ml-2 flex-shrink-0">
         </span>
      </div>
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200 flex-shrink-0" :class="openMenu === 'notifications' ? 'transform rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>
    <div x-show="openMenu === 'notifications'" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="space-y-1">
         <button @click="$dispatch('switch-tab', 'all_notifications')" class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            All Notifications
         </button>
         <button @click="$dispatch('switch-tab', 'scholarship_notifications')" class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path d="M12 14l9-5-9-5-9 5 9 5z" />
               <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
             </svg>
            Scholarships
            <span x-show="unreadCountScholarships > 0" 
                  x-text="unreadCountScholarships"
                  class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5 ml-auto">
            </span>
         </button>
         <button @click="$dispatch('switch-tab', 'status_updates')" class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            Status Updates
             <span x-show="unreadCountStatus > 0" 
                  x-text="unreadCountStatus"
                  class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5 ml-auto">
             </span>
         </button>
         <button @click="$dispatch('switch-tab', 'comments')" class="w-full text-left pr-4 py-2 transition text-sm flex items-center gap-2 border-l-4 border-transparent text-gray-300 hover:text-white" style="padding-left: 2.5rem">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            Comments
             <span x-show="unreadCountComments > 0" 
                  x-text="unreadCountComments"
                  class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5 ml-auto">
             </span>
         </button>
    </div>
</div>

</nav>
