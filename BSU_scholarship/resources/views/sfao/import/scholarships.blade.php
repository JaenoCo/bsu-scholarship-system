<div x-show="tab === 'import-scholarships'" x-cloak class="px-4 py-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Import Scholarships</h2>
        <p class="text-gray-600 dark:text-gray-300 mt-1">
            Upload a CSV or Excel file to create or update scholarships and distribute them to managed campuses.
        </p>
    </div>

    @if(session('import_result'))
        @php($result = session('import_result'))
        <div class="mb-6 rounded-lg border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 p-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                <div class="font-semibold text-green-800 dark:text-green-200">Created: {{ $result['created'] ?? 0 }}</div>
                <div class="font-semibold text-green-800 dark:text-green-200">Updated: {{ $result['updated'] ?? 0 }}</div>
                <div class="font-semibold text-green-800 dark:text-green-200">Skipped: {{ $result['skipped'] ?? 0 }}</div>
            </div>

            @if(!empty($result['errors']))
                <div class="mt-4 text-sm text-red-700 dark:text-red-300">
                    <p class="font-semibold">Rows needing attention:</p>
                    <ul class="mt-2 list-disc pl-5 space-y-1">
                        @foreach($result['errors'] as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <form method="POST" action="{{ route('sfao.scholarships.import') }}" enctype="multipart/form-data" class="p-6 space-y-6" x-data="{ fileName: null }">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        CSV or Excel File
                    </label>
                    <div class="flex justify-center px-6 pt-6 pb-7 border-2 border-dashed rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/30 hover:border-bsu-red transition-colors">
                        <div class="text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0-12l-4 4m4-4l4 4M4 20h16" />
                            </svg>
                            <div class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                                <label for="scholarship_import_file" class="cursor-pointer font-semibold text-bsu-red hover:text-red-700">
                                    <span x-text="fileName || 'Choose CSV, XLS, or XLSX file'"></span>
                                    <input id="scholarship_import_file" name="file" type="file" class="sr-only" accept=".csv,.xls,.xlsx" required @change="fileName = $event.target.files[0]?.name || null">
                                </label>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">Maximum file size: 10MB</p>
                        </div>
                    </div>
                    @error('file')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-3 rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3 cursor-pointer">
                    <input type="checkbox" name="update_existing" value="1" checked class="rounded border-gray-300 text-bsu-red focus:ring-bsu-red">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Update existing scholarships with the same name</span>
                </label>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-bsu-red hover:bg-red-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0-12l-4 4m4-4l4 4M4 20h16" />
                        </svg>
                        Import to Database
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Accepted Columns</h3>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                Required: <span class="font-semibold">scholarship_name</span>, <span class="font-semibold">description</span>, and <span class="font-semibold">submission_deadline</span>.
            </p>

            <div class="mt-4 text-sm text-gray-600 dark:text-gray-300 space-y-2">
                <p>Optional fields:</p>
                <p class="font-mono text-xs bg-gray-100 dark:bg-gray-900 p-3 rounded border border-gray-200 dark:border-gray-700">
                    scholarship_type, application_start_date, slots_available, grant_amount, grant_type, is_active, renewal_allowed, allow_existing_scholarship, eligibility_notes, campus_id, campus_ids, campus, campuses
                </p>
            </div>

            <div class="mt-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4 text-sm text-blue-800 dark:text-blue-200">
                Use campus names or IDs separated by commas. Use <span class="font-semibold">all</span> to distribute a scholarship to every campus under {{ $user->campus->name }}. If omitted, the scholarship is assigned to {{ $user->campus->name }}.
            </div>
        </div>
    </div>
</div>
