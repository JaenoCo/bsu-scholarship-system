@props(['type' => 'error', 'message'])

@php
    $colors = [
        'success' => 'green',
        'error' => 'red',
        'warning' => 'yellow',
        'info' => 'blue',
    ];
    $color = $colors[$type] ?? 'red';
    
    $titles = [
        'success' => 'Success!',
        'error' => 'Error',
        'warning' => 'Warning',
        'info' => 'Information',
    ];
    $title = $titles[$type] ?? 'Error';
@endphp

<div x-data="{ show: true }" 
     x-show="show" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto" 
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true">
     
    {{-- Backdrop --}}
    <div x-show="show"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity"
         aria-hidden="true"></div>

    <div class="flex min-h-screen items-center justify-center p-4 text-center">
        {{-- Modal Panel --}}
        <div x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             @click.away="show = false"
             class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all w-full max-w-xs p-6">
            
            <div class="flex flex-col items-center justify-center">
                {{-- Icon --}}
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-{{ $color }}-100 dark:bg-{{ $color }}-900/30 mb-4">
                    @if($type === 'error')
                        <svg class="h-8 w-8 text-{{ $color }}-600 dark:text-{{ $color }}-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    @elseif($type === 'success')
                        <svg class="h-8 w-8 text-{{ $color }}-600 dark:text-{{ $color }}-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @elseif($type === 'warning')
                        <svg class="h-8 w-8 text-{{ $color }}-600 dark:text-{{ $color }}-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    @else
                        <svg class="h-8 w-8 text-{{ $color }}-600 dark:text-{{ $color }}-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @endif
                </div>

                {{-- Content --}}
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2" id="modal-title">
                    {{ $title }}
                </h3>
                <div class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6 w-full">
                    <p>{{ $message }}</p>
                    
                    {{-- Specific handling for validation links if present in message --}}
                    @if(Str::contains($message, 'email is not verified'))
                        <div class="mt-4">
                            <form method="POST" action="{{ route('verification.resend') }}">
                                @csrf
                                <input type="hidden" name="email" value="{{ old('email') }}">
                                <button type="submit" class="w-full justify-center rounded-xl border border-transparent bg-bsu-red px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200">
                                    Resend Verification Email
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                {{-- Button --}}
                <button type="button" 
                        @click="show = false"
                        class="w-full inline-flex justify-center rounded-xl border border-transparent bg-bsu-red px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200">
                    {{ $type === 'success' ? 'Continue' : ($type === 'error' ? 'Try Again' : 'Okay') }}
                </button>
            </div>
        </div>
    </div>
</div>
