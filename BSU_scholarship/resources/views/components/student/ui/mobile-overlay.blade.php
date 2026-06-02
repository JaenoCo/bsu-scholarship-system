  <!-- Mobile Overlay -->
  <div class="md:hidden fixed inset-0 bg-black bg-opacity-50 z-40"
       x-show="sidebarOpen"
       x-transition.opacity
       @click="sidebarOpen = false"
       x-cloak></div>
