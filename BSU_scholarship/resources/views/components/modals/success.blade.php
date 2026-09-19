@php
    $successMessage = session('success');

    if (! $successMessage && session('status') === 'profile-updated') {
        $successMessage = 'Profile information updated successfully.';
    }
@endphp

@if ($successMessage)
    <div x-data="{ open: true }"
         x-show="open"
         x-cloak
         x-transition.opacity
         @keydown.escape.window="open = false"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4"
         role="dialog"
         aria-modal="true"
         aria-labelledby="success-modal-title">
        <button type="button" class="absolute inset-0 cursor-default bg-slate-950/55 backdrop-blur-sm" @click="open = false" aria-label="Close success message"></button>

        <section x-transition.scale.origin.center
                 class="relative w-full max-w-sm rounded-2xl bg-white p-6 text-center shadow-2xl dark:bg-gray-800">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-300">
                <svg class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                </svg>
            </div>
            <h2 id="success-modal-title" class="mt-4 text-xl font-bold text-slate-900 dark:text-white">Success</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-gray-300">{{ $successMessage }}</p>
            <button type="button" @click="open = false" class="mt-6 w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">Continue</button>
        </section>
    </div>
@endif
