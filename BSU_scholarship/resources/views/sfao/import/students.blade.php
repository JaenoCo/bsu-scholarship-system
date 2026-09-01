<div x-show="tab === 'import-students'" x-cloak class="px-4 py-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Import Student Records</h2>
        <p class="text-gray-600 dark:text-gray-300 mt-1">Upload a CSV or Excel file to create or update students in your managed campuses.</p>
    </div>

    @if(session('student_import_result'))
        @php($result = session('student_import_result'))
        <div class="mb-6 rounded-lg border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 p-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm font-semibold text-green-800 dark:text-green-200">
                <div>Created: {{ $result['created'] ?? 0 }}</div>
                <div>Updated: {{ $result['updated'] ?? 0 }}</div>
                <div>Skipped: {{ $result['skipped'] ?? 0 }}</div>
            </div>
            @if(!empty($result['errors']))
                <ul class="mt-4 list-disc pl-5 text-sm text-red-700 dark:text-red-300 space-y-1">
                    @foreach($result['errors'] as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <form method="POST" action="{{ route('sfao.students.import') }}" enctype="multipart/form-data" class="xl:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-6" x-data="{ fileName: null }">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">CSV or Excel File</label>
                <label for="student_import_file" class="flex justify-center px-6 pt-6 pb-7 border-2 border-dashed rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/30 cursor-pointer hover:border-bsu-red">
                    <span class="text-sm font-semibold text-bsu-red" x-text="fileName || 'Choose CSV, XLS, or XLSX file'"></span>
                    <input id="student_import_file" name="file" type="file" class="sr-only" accept=".csv,.xls,.xlsx" required @change="fileName = $event.target.files[0]?.name || null">
                </label>
                <p class="mt-2 text-xs text-gray-500">Maximum file size: 10MB</p>
                @error('file')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-bsu-red hover:bg-red-700 text-white text-sm font-semibold rounded-lg">
                    <span>Import Student Records</span>
                </button>
            </div>
        </form>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Accepted Columns</h3>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Required:</p>
            <p class="mt-1 font-mono text-xs bg-gray-100 dark:bg-gray-900 p-3 rounded text-gray-700 dark:text-gray-300">sr_code, email, name</p>
            <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">You may use <span class="font-semibold">first_name</span> and <span class="font-semibold">last_name</span> instead of name.</p>
            <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">Optional:</p>
            <p class="mt-1 font-mono text-xs bg-gray-100 dark:bg-gray-900 p-3 rounded text-gray-700 dark:text-gray-300">middle_name, campus_id, campus, college, program, track, year_level, education_level</p>
        </div>
    </div>
</div>
