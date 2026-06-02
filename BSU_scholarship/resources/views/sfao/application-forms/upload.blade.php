<!-- Upload Application Form Tab -->
<div x-show="tab === 'up-app-form'" x-cloak>
    <div class="container mx-auto px-4 py-8">
        
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-bsu-red">
                <h2 class="text-xl font-bold text-white">Upload New Form</h2>
                <p class="text-sm text-white/80 mt-1">Share downloadable resources with students</p>
            </div>

            <div class="p-6" x-data="{ fileName: null }">
                <form action="{{ route('sfao.application-forms.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Left Column: Form Details -->
                        <div class="lg:col-span-2 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Form Name -->
                                <div>
                                    <label for="form_name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Form Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           id="form_name" 
                                           name="form_name" 
                                           value="{{ old('form_name') }}"
                                           required
                                           class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-bsu-red focus:border-bsu-red dark:text-white transition-all"
                                           placeholder="e.g., Scholarship Application 2025">
                                    @error('form_name')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Form Type -->
                                <div>
                                    <label for="form_type" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Category
                                    </label>
                                    <div class="relative">
                                        <select id="form_type" 
                                                name="form_type"
                                                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-bsu-red focus:border-bsu-red dark:text-white appearance-none transition-all">
                                            <option value="">Select Category (Optional)</option>
                                            @if(isset($activeScholarshipsList))
                                                @foreach($activeScholarshipsList as $scholarship)
                                                    <option value="{{ $scholarship->scholarship_name }}" {{ old('form_type') == $scholarship->scholarship_name ? 'selected' : '' }}>{{ $scholarship->scholarship_name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Description
                                </label>
                                <textarea id="description" 
                                          name="description" 
                                          rows="6"
                                          class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-bsu-red focus:border-bsu-red dark:text-white transition-all"
                                          placeholder="Briefly describe what this form is for...">{{ old('description') }}</textarea>
                            </div>
                        </div>

                        <!-- Right Column: File Upload -->
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Attachment <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-xl hover:border-bsu-red dark:hover:border-red-500 transition-colors group bg-gray-50 dark:bg-gray-900/30 h-full min-h-[200px] flex-col justify-center">
                                    <div class="space-y-2 text-center">
                                        <div x-show="!fileName">
                                            <div class="mx-auto h-12 w-12 text-gray-400 group-hover:text-bsu-red transition-colors">
                                                <svg stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </div>
                                            <div class="flex text-sm text-gray-600 dark:text-gray-400 justify-center">
                                                <label for="file" class="relative cursor-pointer rounded-md font-medium text-bsu-red hover:text-red-700 focus-within:outline-none focus:underline">
                                                    <span>Choose a file</span>
                                                    <input id="file" name="file" type="file" class="sr-only" required accept=".pdf,.doc,.docx,.xls,.xlsx" @change="fileName = $event.target.files[0].name">
                                                </label>
                                                <p class="pl-1">or drag and drop</p>
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                                PDF, Word, Excel up to 10MB
                                            </p>
                                        </div>
                                        
                                        <div x-show="fileName" x-cloak class="flex flex-col items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white" x-text="fileName"></span>
                                            <button type="button" @click="fileName = null; document.getElementById('file').value = ''" class="mt-2 text-xs text-red-500 hover:text-red-700 font-medium">Remove</button>
                                        </div>
                                    </div>
                                </div>
                                @error('file')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Visibility Info Banner -->
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 flex items-start gap-3">
                                <svg class="h-5 w-5 text-blue-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="text-sm text-blue-700 dark:text-blue-300">
                                    <p class="font-medium">Automatic Visibility</p>
                                    <p class="mt-0.5">
                                        If a <strong>Category</strong> is selected, this form will be visible to all students eligible for that scholarship regardless of campus. 
                                        If no category is selected, it will be visible only to students of <strong>{{ $user->campus->name }}</strong>.
                                    </p>
                                </div>
                            </div>
                            <!-- Default to uploader's campus for reference/origin -->
                            <input type="hidden" name="campus_id" value="{{ $user->campus_id }}">
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="mt-8 flex justify-end gap-3 items-center border-t border-gray-100 dark:border-gray-700 pt-5">
                        <button type="button" @click="$dispatch('switch-tab', 'all-app-forms')" class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-bsu-red hover:bg-red-700 text-white text-sm font-medium rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Publish Form
                        </button>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
