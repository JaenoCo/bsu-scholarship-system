<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Application Forms</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Download application forms for your campus</p>
    </div>

    @if($forms->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-400 mb-2">No Application Forms Available</h3>
            <p class="text-gray-500 dark:text-gray-500">There are currently no application forms available for your campus.</p>
            <p class="text-gray-500 dark:text-gray-500 mt-2">Please check back later or contact your SFAO office.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($forms as $form)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-bsu-red" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    @if($form->form_type)
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                                            {{ $form->form_type }}
                                        </span>
                                    @endif
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                    {{ $form->form_name }}
                                </h3>
                                @if($form->description)
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                        {{ Str::limit($form->description, 100) }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-4">
                            <div class="flex items-center gap-4">
                                <span class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    {{ strtoupper($form->file_type) }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    {{ $form->download_count }} downloads
                                </span>
                            </div>
                        </div>

                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                            <span>Uploaded: {{ $form->created_at->format('M d, Y') }}</span>
                        </div>

                        <a href="{{ route('application-forms.download', $form->id) }}" 
                           class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-bsu-red text-white rounded-lg hover:bg-red-700 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download Form
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
