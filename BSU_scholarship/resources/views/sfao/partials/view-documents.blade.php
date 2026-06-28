@php
    $user = \App\Models\User::find(session('user_id'));
@endphp
<!DOCTYPE html>
<html lang="en"
    :class="{ 'dark': darkMode }"
    x-data="{ darkMode: localStorage.getItem('darkMode_{{ $user->id }}') === 'true' }"
    x-init="$watch('darkMode', val => localStorage.setItem('darkMode_{{ $user->id }}', val))">
<head>
    <script>
        if (localStorage.getItem('darkMode_{{ $user->id }}') === 'true') {
            document.documentElement.classList.add('dark');
        }
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Documents | BSU Scholarship</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans min-h-screen flex flex-col">

    <!-- Header -->
    <header class="bg-red-700 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <h1 class="text-2xl font-bold">BSU Scholarship System</h1>
            <a href="{{ url('/sfao') }}" class="bg-white text-red-700 font-semibold px-3 py-2 rounded hover:bg-gray-100">Dashboard</a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-5xl mx-auto mt-12 p-6 bg-white rounded-lg shadow-md flex-1">
        <h2 class="text-3xl font-bold text-red-700 mb-6 text-center">Documents for {{ $student->name }}</h2>

        @if($documents->isEmpty())
            <p class="text-gray-700 text-center">No documents uploaded yet.</p>
        @else
            <ul class="space-y-4">
                @foreach($documents as $doc)
                    <li class="border p-4 rounded-lg bg-gray-50 shadow-sm">
                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                            <div class="space-y-2">
                                <div class="flex flex-wrap gap-2 items-center">
                                    <span class="inline-flex px-2 py-1 rounded-full bg-gray-200 text-gray-800 text-xs font-semibold uppercase">
                                        {{ $doc->getDocumentCategoryDisplayName() }}
                                    </span>
                                    @if($doc->is_mandatory)
                                        <span class="inline-flex px-2 py-1 rounded-full bg-red-100 text-red-700 text-xs font-semibold uppercase">
                                            Required
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-semibold uppercase">
                                            Optional
                                        </span>
                                    @endif
                                </div>

                                <h3 class="text-lg font-semibold text-gray-900">{{ $doc->document_name }}</h3>
                                <p class="text-sm text-gray-600">{{ $doc->description ?? 'No description available.' }}</p>
                            </div>

                            <div class="space-y-2 text-sm text-gray-700 md:text-right">
                                <div>
                                    <p class="uppercase tracking-wider text-xs text-gray-500">Status</p>
                                    <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold {{ $doc->getEvaluationStatusBadgeColor() }}">
                                        {{ $doc->getEvaluationStatusDisplayName() }}
                                    </span>
                                </div>
                                <div>
                                    <p class="uppercase tracking-wider text-xs text-gray-500">Uploaded</p>
                                    <p class="font-medium text-gray-900">{{ $doc->created_at?->format('M j, Y H:i') ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <p class="uppercase tracking-wider text-xs text-gray-500">File</p>
                                <a href="{{ $doc->getViewUrl() }}" target="_blank" class="text-red-700 underline font-medium">
                                    {{ $doc->original_filename ?? basename($doc->file_path) }}
                                </a>
                            </div>
                            @if($doc->scholarship)
                                <div>
                                    <p class="uppercase tracking-wider text-xs text-gray-500">Scholarship</p>
                                    <p class="font-medium text-gray-900">{{ $doc->scholarship->scholarship_name }}</p>
                                </div>
                            @endif
                        </div>

                        @if($doc->evaluation_notes)
                            <div class="mt-4 rounded-lg border border-gray-200 bg-white p-4 text-sm text-gray-700">
                                <strong>Notes</strong>
                                <p class="mt-2">{{ $doc->evaluation_notes }}</p>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>

            <div class="mt-6 text-center">
                <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg">
                    Evaluate
                </button>
            </div>
        @endif
    </main>

    <!-- Footer -->
    <footer class="bg-gray-200 text-gray-600 mt-12 py-6 text-center">
        &copy; {{ date('Y') }} Batangas State University. All rights reserved.
    </footer>

</body>
</html>
