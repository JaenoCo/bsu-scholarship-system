  <!-- Sidebar -->
  <aside class="fixed inset-y-0 left-0 w-64 bg-bsu-red text-white dark:bg-gray-800 transform transition-transform duration-300 z-50 flex flex-col"
         :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
         x-cloak>
    
    <!-- Close Button -->
    <div class="absolute top-0 right-0 pt-4 pr-4">
      <button @click="sidebarOpen = false" class="text-white hover:text-gray-200 focus:outline-none">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>


    <!-- Navigation - Scrollable -->
    <nav class="mt-6 px-4 pb-4 overflow-y-auto flex-1 space-y-4 custom-scrollbar">
      <!-- Scholarships Header -->
      <div class="space-y-1">
        <div class="px-4 py-2 text-sm font-semibold text-gray-200 uppercase tracking-wider">
          Scholarships
        </div>
        <button type="button" @click="tab = 'scholarships'; subTab = 'all'"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="(tab === 'scholarships' && subTab === 'all') ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M12 14l9-5-9-5-9 5 9 5z" />
            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
          </svg>
          All Scholarships
        </button>
        <button type="button" @click="tab = 'scholarships'; subTab = 'private'"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="(tab === 'scholarships' && subTab === 'private') ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
          Private Scholarships
        </button>
        <button type="button" @click="tab = 'scholarships'; subTab = 'government'"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="(tab === 'scholarships' && subTab === 'government') ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
          </svg>
          Government Scholarships
        </button>
        <button type="button" @click="tab = 'scholarships'; subTab = 'my_scholarships'"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="(tab === 'scholarships' && subTab === 'my_scholarships') ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
          My Scholarships
        </button>
      </div>

      <!-- Application Forms Header -->
      <div class="space-y-1">
        <div class="px-4 py-2 text-sm font-semibold text-gray-200 uppercase tracking-wider">
          Application Forms
        </div>
        <button type="button" @click="tab = 'scholarships'; subTab = 'form'"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="(tab === 'scholarships' && subTab === 'form') ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          SFAO Application Form
        </button>
        <button type="button" @click="tab = 'scholarships'; subTab = 'gvsreap_form'"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="(tab === 'scholarships' && subTab === 'gvsreap_form') ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          TDP Application Form
        </button>
      </div>

      <!-- Applications Header -->
      <div class="space-y-1">
        <div class="px-4 py-2 text-sm font-semibold text-gray-200 uppercase tracking-wider">
          Applications
        </div>
        <button type="button" @click="tab = 'applied-scholarships'; subTab = 'all'"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="(tab === 'applied-scholarships' && subTab === 'all') ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M12 14l9-5-9-5-9 5 9 5z" />
            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
          </svg>
          Applied Scholarships
        </button>
        <button type="button" @click="tab = 'applied-scholarships'; subTab = 'tracking'"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="(tab === 'applied-scholarships' && subTab === 'tracking') ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
          Application Tracking
        </button>
      </div>

      <!-- Notifications Header -->
      <div class="space-y-1">
        <div class="px-4 py-2 text-sm font-semibold text-gray-200 uppercase tracking-wider flex justify-between items-center">
          <span>Notifications</span>
        </div>
        
        <button type="button" @click="tab = 'notifications'; subTab = 'all'"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="(tab === 'notifications' && (subTab === 'all' || !subTab)) ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
          All Notifications
          <span x-show="unreadCount > 0" 
                x-text="unreadCount"
                class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5 ml-auto">
          </span>
        </button>

        <button type="button" @click="tab = 'notifications'; subTab = 'scholarship_created'"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="(tab === 'notifications' && subTab === 'scholarship_created') ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
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

        <button type="button" @click="tab = 'notifications'; subTab = 'application_status'"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="(tab === 'notifications' && subTab === 'application_status') ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
          </svg>
          Status Updates
          <span x-show="unreadCountStatus > 0" 
                x-text="unreadCountStatus"
                class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5 ml-auto">
          </span>
        </button>

        <button type="button" @click="tab = 'notifications'; subTab = 'sfao_comment'"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="(tab === 'notifications' && subTab === 'sfao_comment') ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
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
    </nav>

    <!-- Settings Section Removed -->

  </aside>
