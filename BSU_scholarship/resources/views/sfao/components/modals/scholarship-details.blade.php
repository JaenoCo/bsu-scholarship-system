@props(['scholarship'])

<div x-cloak
     x-show="showDetails_{{ $scholarship->id }}"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-gray-900/60 backdrop-blur-sm"
     @click.self="showDetails_{{ $scholarship->id }} = false">

    <div class="relative w-full max-w-2xl bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden transform"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-8 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-8 scale-95">

        <!-- Header -->
        <div class="bg-bsu-red p-6 text-white">
            <h2 class="text-2xl font-bold">{{ $scholarship->scholarship_name }}</h2>
            <div class="flex items-center gap-2 mt-2 text-sm opacity-90">
                <span class="bg-white/20 px-2 py-0.5 rounded uppercase tracking-wider">{{ $scholarship->scholarship_type }}</span>
                <span>{{ $scholarship->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
            <button @click="showDetails_{{ $scholarship->id }} = false" class="absolute top-4 right-4 text-white hover:text-gray-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Application Statistics</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Available Slots -->
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-100 dark:border-blue-800 text-center">
                    <span class="block text-3xl font-bold text-blue-600 dark:text-blue-400">
                        {{ $scholarship->slots_available ?: '∞' }}
                    </span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Slots Available</span>
                </div>

                <!-- Filled (Applicants) -->
                <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-xl border border-yellow-100 dark:border-yellow-800 text-center">
                    <span class="block text-3xl font-bold text-yellow-600 dark:text-yellow-400">
                        {{ $scholarship->applications_count ?? 0 }}
                    </span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Total Applicants</span>
                </div>

                <!-- Approved Scholars -->
                <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-xl border border-green-100 dark:border-green-800 text-center">
                    <span class="block text-3xl font-bold text-green-600 dark:text-green-400">
                        {{ $scholarship->scholars_count ?? 0 }}
                    </span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Approved Scholars</span>
                </div>
            </div>

            <!-- Progress Bar -->
            @php
                $slots = $scholarship->slots_available;
                $scholars = $scholarship->scholars_count ?? 0;
                $applications = $scholarship->applications_count ?? 0;
                
                // Applicants here means "Pending/Other applications" that are NOT yet scholars
                // Assumption: applications_count includes scholars if using withCount('applications') unless filtered.
                // Based on controller, it counts all applications.
                // But logically, if someone is a scholar, they have an application.
                // User wants "Approved" vs "Applicants".
                // If 5 approved, 20 total applications -> 15 are just "applicants".
                
                $approvedCount = $scholars;
                $pendingCount = max(0, $applications - $scholars);
                $filledTotal = $approvedCount + $pendingCount;
                
                $slotsLeft = max(0, $slots - $filledTotal);
                
                // Percentages for bar
                $approvedPercent = $slots ? ($approvedCount / $slots) * 100 : 0;
                $pendingPercent = $slots ? ($pendingCount / $slots) * 100 : 0;
            @endphp
            @if($slots)
            <div class="mt-6">
                <!-- Descriptive Text -->
                <div class="flex justify-between text-sm mb-2 font-medium text-gray-700 dark:text-gray-300">
                    <div>
                        @if($approvedCount > 0)
                            <span class="text-green-600 font-bold">{{ $approvedCount }} approved</span>
                            <span class="text-gray-400 mx-1">|</span>
                        @endif
                        <span class="text-yellow-600 font-bold">{{ $pendingCount }} applicants</span>
                        <span class="text-gray-400 mx-1">|</span>
                        <span class="text-gray-500">{{ $slotsLeft }} slots left</span>
                    </div>
                    <span class="text-xs text-gray-500">Total Capacity: {{ $slots }}</span>
                </div>

                <!-- Stacked Bar -->
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 flex overflow-hidden">
                    <!-- Approved Segment -->
                    @if($approvedPercent > 0)
                        <div class="bg-green-500 h-full" style="width: {{ $approvedPercent }}%" title="{{ $approvedCount }} Approved"></div>
                    @endif
                    
                    <!-- Pending Segment -->
                    @if($pendingPercent > 0)
                        <div class="bg-yellow-500 h-full" style="width: {{ $pendingPercent }}%" title="{{ $pendingCount }} Applicants"></div>
                    @endif
                </div>
                
                <!-- Legend -->
                <div class="flex gap-4 mt-2 text-xs">
                    <div class="flex items-center gap-1">
                        <div class="w-3 h-3 bg-green-500 rounded-sm"></div>
                        <span class="text-gray-600 dark:text-gray-400">Approved</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="w-3 h-3 bg-yellow-500 rounded-sm"></div>
                        <span class="text-gray-600 dark:text-gray-400">Applicants</span>
                    </div>
                </div>
            </div>
            @endif

            <div class="mt-8 flex justify-end gap-3">
                @if($scholarship->is_active)
                <form action="{{ route('sfao.scholarships.release-grant', $scholarship->id) }}" method="POST">
                    @csrf
                    <button type="submit" onclick="return confirm('Release grants for {{ $scholarship->scholarship_name }}? Emails will be sent to all active scholars.');" class="px-4 py-2 bg-bsu-red text-white rounded-lg hover:bg-red-700 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Release Grant
                    </button>
                </form>
                @endif
                <button @click="showDetails_{{ $scholarship->id }} = false" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
