@props(['onclick' => ''])

<div x-show="showLogoutModal" 
     x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
     @click.self="showLogoutModal = false">
  <div x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0 transform scale-95"
       x-transition:enter-end="opacity-100 transform scale-100"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100 transform scale-100"
       x-transition:leave-end="opacity-0 transform scale-95"
       class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Confirm Logout</h3>
    <p class="text-gray-600 dark:text-gray-300 mb-6">Are you sure you want to logout?</p>
    <div class="flex justify-end gap-3">
      <button @click="showLogoutModal = false"
              class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">
        Cancel
      </button>
      <a href="{{ url('/logout') }}"
         @if($onclick) onclick="{{ $onclick }}" @endif
         class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
        Logout
      </a>
    </div>
  </div>
</div>
