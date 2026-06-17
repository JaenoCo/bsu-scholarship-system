@props(['title', 'subtitle', 'user', 'settings' => false, 'logout' => false, 'profile' => false, 'sidebar' => true, 'backUrl' => null, 'backText' => null, 'settingsClick' => null, 'settingsUrl' => null, 'onclick' => null, 'actionText' => null, 'actionClick' => null, 'actionUrl' => null, 'actionTitle' => null])

<!-- Main Header -->
<header class="flex items-center justify-between px-8 py-4 bg-[#2f2f2f] dark:bg-gray-800 shadow-sm sticky top-0 z-30 border-b border-gray-700 transition-all duration-300 print:hidden"
        :class="{ 'md:ml-64': sidebarOpen }">
  <!-- Branding -->
  <div class="flex items-center space-x-2 md:space-x-3">
      
      <!-- Back Button Mode -->
      @if($backUrl)
          <a href="{{ $backUrl }}" 
             @if($onclick) onclick="{{ $onclick }}" @endif
             class="flex items-center text-white hover:text-bsu-red transition-colors mr-2 md:mr-4 group" 
             title="{{ $backText ?? 'Go Back' }}">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              @if($backText)
                  <span class="ml-2 text-sm font-medium hidden md:block">{{ $backText }}</span>
              @endif
          </a>
      
      <!-- Sidebar Toggle Mode -->
      @elseif($sidebar)
          <button @click="sidebarOpen = !sidebarOpen" class="text-white hover:text-bsu-red transition-colors focus:outline-none mr-1 md:mr-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
          </button>
      @endif

      <img src="{{ asset('images/lugo.png') }}" alt="Logo" class="h-10 md:h-12 w-auto">
      <div class="text-white">
          <div class="text-sm md:text-base font-bold leading-tight">Batangas State University</div>
          <div class="text-xs font-light hidden md:block">{{ $title ?? 'The National Engineering University' }}</div>
      </div>
  </div>

  <!-- Dark Mode Toggle & User Profile -->
  <div class="flex items-center gap-2">
      @if($actionText)
        @if($actionUrl)
            <a href="{{ $actionUrl }}"
               class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-bsu-red hover:bg-red-700 text-white text-sm font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bsu-red focus:ring-offset-[#2f2f2f]"
               title="{{ $actionTitle ?? $actionText }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0-12l-4 4m4-4l4 4M4 20h16" />
                </svg>
                <span class="hidden sm:inline">{{ $actionText }}</span>
            </a>
        @else
            <button type="button"
                    @click="{{ $actionClick }}"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-bsu-red hover:bg-red-700 text-white text-sm font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bsu-red focus:ring-offset-[#2f2f2f]"
                    title="{{ $actionTitle ?? $actionText }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0-12l-4 4m4-4l4 4M4 20h16" />
                </svg>
                <span class="hidden sm:inline">{{ $actionText }}</span>
            </button>
        @endif
      @endif

      <!-- Dark Mode Toggle -->
      <button @click="darkMode = !darkMode" 
              class="p-2 rounded-full hover:bg-gray-700 transition-colors focus:outline-none"
              :title="darkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
          <!-- Sun Icon (for Dark Mode) -->
          <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
          <!-- Moon Icon (for Light Mode) -->
          <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
          </svg>
      </button>

      <!-- Account Settings (Gear Icon) -->
      @if(isset($settings) && $settings)
        @if(isset($settingsUrl))
            <a href="{{ $settingsUrl }}"
                class="p-2 rounded-full hover:bg-gray-700 transition-colors focus:outline-none text-gray-400 hover:text-white"
                title="Account Settings">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </a>
        @else
            <button @click="{{ $settingsClick ?? "tab = 'account'" }}" 
                    class="p-2 rounded-full hover:bg-gray-700 transition-colors focus:outline-none text-gray-400 hover:text-white"
                    title="Account Settings">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </button>
        @endif
      @endif

      <!-- Logout (Off Icon) -->
      @if(isset($logout) && $logout)
        <button @click="showLogoutModal = true" 
                class="p-2 rounded-full hover:bg-gray-700 transition-colors focus:outline-none text-red-500 hover:text-red-400"
                title="Logout">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
        </button>
      @endif

      <!-- Profile Dropdown (Optional, if we want it here instead of separate sidebar) -->
      @if(isset($profile) && $profile)
        <div class="relative">
          <div class="flex items-center">
              <img src="{{ $user && $user->profile_picture ? asset('storage/profile_pictures/' . $user->profile_picture) . '?' . now()->timestamp : asset('images/default-avatar.png') }}"
                   alt="Profile"
                   class="h-10 w-10 rounded-full border-2 border-white object-cover">
          </div>
        </div>
      @endif

  </div>
</header>
