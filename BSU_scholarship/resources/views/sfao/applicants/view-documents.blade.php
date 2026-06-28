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
                    <li class="flex flex-col gap-3 border p-4 rounded-lg bg-gray-50 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" name="document_{{ $doc->id }}_approved"
                                    class="h-5 w-5 text-green-600 border-gray-300 rounded"
                                    {{ $doc->evaluation_status === 'approved' ? 'checked' : '' }}>
                                <span class="font-semibold text-gray-800">{{ $doc->document_name }}</span>
                            </div>
                            <span class="text-sm px-2 py-1 rounded-full {{ $doc->evaluation_status === 'approved' ? 'bg-green-100 text-green-700' : ($doc->evaluation_status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                {{ ucfirst($doc->evaluation_status ?? 'pending') }}
                            </span>
                        </div>
                        <div>
                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="text-red-700 underline">
                                {{ $doc->original_filename ?? basename($doc->file_path) }}
                            </a>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Uploaded: {{ $doc->created_at?->format('M j, Y H:i') ?? 'N/A' }}</p>
                        </div>
                        @if($doc->evaluation_notes)
                            <div class="text-sm text-gray-700 bg-white border border-gray-200 rounded p-3">
                                <strong>Notes:</strong> {{ $doc->evaluation_notes }}
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>

            <!-- Evaluate Button -->
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
