<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Scholarship Announcements</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300 max-w-2xl">
                Stay informed about the latest scholarship announcements available to your campus.
            </p>
        </div>
        <div class="flex gap-2">
            <button @click="$dispatch('switch-tab', 'all_scholarships')"
                    class="px-5 py-2 rounded-full bg-white text-gray-800 border border-gray-300 hover:bg-gray-50 transition">
                Browse Scholarships
            </button>
        </div>
    </div>

    @if(isset($announcements) && $announcements->count() > 0)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($announcements as $scholarship)
                <article class="group rounded-3xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm p-6 transition hover:shadow-lg">
                    <div class="flex items-start justify-between gap-4">
                        <div class="space-y-3">
                            <div class="inline-flex items-center gap-2 rounded-full bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-300 px-3 py-1 text-xs font-semibold uppercase tracking-wide">
                                Announcement
                            </div>
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $scholarship->announcement_title }}</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $scholarship->scholarship_name }}</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-xs font-semibold uppercase tracking-wide px-3 py-1">
                                {{ ucfirst($scholarship->scholarship_type) }}
                            </span>
                        </div>
                    </div>

                    <p class="mt-5 text-sm leading-7 text-gray-600 dark:text-gray-300">{{ \Illuminate\Support\Str::limit($scholarship->announcement_message, 220) }}</p>

                    <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('student.apply', ['scholarship_id' => $scholarship->id]) }}"
                               class="inline-flex items-center justify-center rounded-full bg-bsu-red px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-bsu-red/10 hover:bg-bsu-redDark transition">
                                Learn More
                            </a>
                            <a href="{{ route('student.apply', ['scholarship_id' => $scholarship->id]) }}"
                               class="inline-flex items-center justify-center rounded-full border border-bsu-red bg-white px-5 py-2.5 text-sm font-semibold text-bsu-red hover:bg-red-50 transition">
                                Apply Now
                            </a>
                        </div>

                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            Updated {{ $scholarship->updated_at ? $scholarship->updated_at->diffForHumans() : 'recently' }}
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="rounded-3xl border border-yellow-200 bg-yellow-50 dark:border-yellow-700 dark:bg-yellow-900/20 p-8 text-center">
            <h2 class="text-lg font-semibold text-yellow-900 dark:text-yellow-200">No Scholarship Announcements Yet</h2>
            <p class="mt-2 text-sm text-yellow-700 dark:text-yellow-100 max-w-2xl mx-auto">
                There are no active scholarship announcements for your campus at the moment. Check back soon or browse all available scholarships.
            </p>
            <button @click="$dispatch('switch-tab', 'all_scholarships')"
                    class="mt-5 inline-flex items-center justify-center rounded-full bg-bsu-red px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-bsu-red/10 hover:bg-bsu-redDark transition">
                Browse Scholarships
            </button>
        </div>
    @endif
</div>
