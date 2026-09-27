@php
    $heroSlides = collect($announcements ?? [])->take(3)->values()->map(function ($scholarship, $index) {
        return [
            'title' => $scholarship->announcement_title ?: $scholarship->scholarship_name,
            'description' => \Illuminate\Support\Str::limit($scholarship->announcement_message ?: $scholarship->description, 140),
            'type' => ucfirst($scholarship->scholarship_type) . ' scholarship',
            'url' => route('student.apply', ['scholarship_id' => $scholarship->id]),
            'image' => $scholarship->getBackgroundImageUrl() ?: asset('images/scholarship' . (($index % 3) + 1) . '.jpg'),
        ];
    })->all();

    if (empty($heroSlides)) {
        $heroSlides = collect([1, 2, 3])->map(fn ($number) => [
            'title' => 'Scholarship opportunities',
            'description' => 'Explore available scholarship programs for your campus.',
            'type' => 'Student update',
            'url' => route('student.dashboard', ['tab' => 'all_scholarships']),
            'image' => asset('images/scholarship' . $number . '.jpg'),
        ])->all();
    }
@endphp

<div class="space-y-6">
    <section x-data="{ activeSlide: 0, slides: @js($heroSlides) }"
             x-init="setInterval(() => activeSlide = (activeSlide + 1) % slides.length, 5000)"
             class="relative h-56 overflow-hidden rounded-xl bg-gray-900 shadow-sm sm:h-64">
        <template x-for="(slide, index) in slides" :key="slide.title + index">
            <div x-show="activeSlide === index" x-transition.opacity class="absolute inset-0">
                <img :src="slide.image" :alt="slide.title" class="h-full w-full object-cover opacity-60">
                <div class="absolute inset-0 bg-gradient-to-r from-black/75 via-black/45 to-black/10"></div>
                <div class="absolute inset-0 flex max-w-2xl flex-col justify-center p-6 text-white sm:p-8">
                    <p class="text-xs font-semibold uppercase tracking-wider text-white/75" x-text="slide.type"></p>
                    <h2 class="mt-2 text-2xl font-semibold sm:text-3xl" x-text="slide.title"></h2>
                    <p class="mt-2 text-sm leading-6 text-white/85" x-text="slide.description"></p>
                    <a :href="slide.url" class="mt-4 inline-flex w-fit rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 hover:bg-gray-100">View opportunity</a>
                </div>
            </div>
        </template>
        <div class="absolute bottom-4 right-5 flex gap-1.5">
            <template x-for="(_, index) in slides" :key="index">
                <button type="button" @click="activeSlide = index" :aria-label="`Show slide ${index + 1}`" class="h-1.5 w-5 rounded-full bg-white/50" :class="activeSlide === index ? 'bg-white' : ''"></button>
            </template>
        </div>
    </section>

    @if(isset($announcements) && $announcements->count() > 0)
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @foreach($announcements as $scholarship)
                <article class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ ucfirst($scholarship->scholarship_type) }} scholarship</p>
                            <h2 class="mt-1 text-base font-semibold text-gray-900 dark:text-white">{{ $scholarship->announcement_title ?: $scholarship->scholarship_name }}</h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $scholarship->scholarship_name }}</p>
                        </div>
                        <span class="shrink-0 text-xs text-gray-500 dark:text-gray-400">{{ $scholarship->updated_at ? $scholarship->updated_at->diffForHumans() : 'Recently' }}</span>
                    </div>
                    <p class="mt-3 text-sm leading-5 text-gray-600 dark:text-gray-300">{{ \Illuminate\Support\Str::limit($scholarship->announcement_message ?: $scholarship->description, 150) }}</p>
                    <a href="{{ route('student.apply', ['scholarship_id' => $scholarship->id]) }}" class="mt-3 inline-flex text-sm font-semibold text-bsu-red hover:underline">View scholarship <span aria-hidden="true" class="ml-1">&rarr;</span></a>
                </article>
            @endforeach
        </div>
    @else
        <div class="border-y border-gray-200 py-10 text-center dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">No scholarship announcements yet</h2>
            <p class="mx-auto mt-2 max-w-xl text-sm text-gray-600 dark:text-gray-400">There are no active announcements for your campus right now.</p>
            <button @click="$dispatch('switch-tab', 'all_scholarships')" class="mt-4 text-sm font-semibold text-bsu-red hover:underline">Browse scholarships <span aria-hidden="true">&rarr;</span></button>
        </div>
    @endif
</div>
