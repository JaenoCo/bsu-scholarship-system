<nav class="fixed top-0 left-0 w-full bg-white shadow-md z-50 transition-all duration-300" x-data="{ open: false }">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center space-x-3">
                <a href="#home" class="flex items-center space-x-2">
                    <!-- Increased logo size -->
                    <img src="{{ asset('images/lugo.png') }}" alt="Logo" class="h-10 md:h-14 lg:h-16 w-auto">
                    <div class="text-gray-800">
                        <div class="text-base md:text-lg font-bold leading-tight">Batangas State University</div>
                        <div class="text-xs md:text-sm italic text-red-600 leading-tight">The National Engineering University</div>
                    </div>
                </a>
            </div>

            <div class="hidden md:flex items-center space-x-6">
                <a href="#home" class="text-gray-800 hover:text-red-600 transition">Home</a>
                <a href="#about" class="text-gray-800 hover:text-red-600 transition">About</a>
                <a href="#contact" class="text-gray-800 hover:text-red-600 transition">Contact</a>
                <a href="{{ url('/login') }}" 
                   class="bg-red-600 text-white px-4 py-2 rounded-full shadow-md hover:bg-red-700 transition animate-pulseButton">
                    Login
                </a>
            </div>

            <div class="md:hidden flex items-center">
                <button @click="open = !open" class="text-gray-800 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path :class="{ 'hidden': open }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                      <path :class="{ 'hidden': !open }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>


    <!-- Mobile Menu -->
    <div x-show="open" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="md:hidden fixed top-16 left-0 w-full bg-white shadow-lg space-y-2 px-4 pt-4 pb-6 z-40 border-t border-gray-200">
        <a href="#home" @click="open = false" class="block text-gray-800 hover:text-red-600 hover:bg-gray-100 px-3 py-2 rounded-lg transition">Home</a>
        <a href="#about" @click="open = false" class="block text-gray-800 hover:text-red-600 hover:bg-gray-100 px-3 py-2 rounded-lg transition">About</a>
        <a href="#contact" @click="open = false" class="block text-gray-800 hover:text-red-600 hover:bg-gray-100 px-3 py-2 rounded-lg transition">Contact</a>
        <a href="{{ url('/login') }}" 
            class="block bg-red-600 text-white px-5 py-2 rounded-full text-center hover:bg-red-700 transition mt-4 animate-pulseButton">
            Login
        </a>
    </div>
</nav>
