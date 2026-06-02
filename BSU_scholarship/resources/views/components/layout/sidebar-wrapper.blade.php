@props(['user'])

<!-- Sidebar -->
<aside class="fixed inset-y-0 left-0 w-64 bg-bsu-red text-white transform transition-transform duration-300 z-50 flex flex-col"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       x-cloak>
  
  <!-- Close Button -->
  <div class="absolute top-0 right-0 pt-4 pr-4 md:hidden">
    <button @click="sidebarOpen = false" class="text-white hover:text-gray-200 focus:outline-none">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
  </div>

  <!-- Navigation Content Slot -->
  {{ $slot }}

</aside>

<!-- Mobile Overlay Backdrop -->
<div x-show="sidebarOpen" 
     x-transition:enter="transition-opacity ease-linear duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-gray-900/50 z-40 md:hidden"
     @click="sidebarOpen = false"
     x-cloak>
</div>
