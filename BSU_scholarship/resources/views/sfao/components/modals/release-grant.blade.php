@props(['scholarship'])

<template x-teleport="body">
    <div x-cloak
         x-show="showReleaseGrant_{{ $scholarship->id }}"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
         @click.self="showReleaseGrant_{{ $scholarship->id }} = false">

        <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden transform transition-all">
            <!-- Header -->
            <div class="bg-bsu-red p-4 px-6 flex justify-between items-center text-white">
                <h3 class="text-lg font-bold">Release Grant</h3>
                <button type="button" @click="showReleaseGrant_{{ $scholarship->id }} = false" class="text-white hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="{{ route('sfao.scholarships.release-grant', $scholarship->id) }}" method="POST" class="p-6 space-y-4">
                @csrf
                
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-4 bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg border border-blue-100 dark:border-blue-800">
                    <p class="flex gap-2">
                        <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>This will email the grant slip to all active scholars of <strong>{{ $scholarship->scholarship_name }}</strong> in your campus.</span>
                    </p>
                </div>

                <!-- Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Grant Release Date <span class="text-red-500">*</span></label>
                    <input type="date" name="release_date" required class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:ring-bsu-red focus:border-bsu-red shadow-sm" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}">
                </div>

                <!-- Location -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Location <span class="text-red-500">*</span></label>
                    <input type="text" name="location" placeholder="e.g., SFAO Office Main Campus" required class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:ring-bsu-red focus:border-bsu-red shadow-sm">
                </div>

                <!-- Instructions -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Instructions <span class="text-red-500">*</span></label>
                    <textarea name="instructions" rows="4" placeholder="e.g., Please bring your school ID and a copy of your grades..." required class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:ring-bsu-red focus:border-bsu-red shadow-sm"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700 mt-6">
                    <button type="button" @click="showReleaseGrant_{{ $scholarship->id }} = false" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-200 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-bsu-red text-white rounded-lg hover:bg-red-700 transition shadow-lg shadow-red-500/30 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        Send Email Notifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
