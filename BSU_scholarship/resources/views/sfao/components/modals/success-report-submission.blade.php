@if(session('success'))
<!-- Success Modal -->
<div class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>

    <!-- Modal Panel -->
    <div class="flex min-h-screen items-center justify-center p-4 text-center">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all w-full max-w-xs p-6">
            
            <div class="flex flex-col items-center justify-center">
                <!-- Icon -->
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 mb-4">
                    <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <!-- Content -->
                <h3 class="text-xl font-bold text-gray-900 mb-2" id="modal-title">
                    Submission Successful
                </h3>
                <div class="mt-2">
                    <p class="text-sm text-gray-500 text-center mb-6">
                        {{ session('success') }}
                    </p>
                </div>

                <!-- Button -->
                <button type="button" 
                        onclick="this.closest('.fixed').remove()" 
                        class="w-full inline-flex justify-center rounded-xl border border-transparent bg-green-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors duration-200">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endif
