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
                    <li class="flex items-center gap-3 border p-4 rounded-lg bg-gray-50 shadow-sm">
                        
                        <!-- Checkbox comes first -->
                        <input type="checkbox" name="form_137_approved"
                            class="h-5 w-5 text-green-600 border-gray-300 rounded"
                            {{ $doc->form_137_status === 'approved' ? 'checked' : '' }}>
                        
                        <!-- Document label + link -->
                        <span>
                            <strong>Form 137:</strong>
                            <a href="{{ asset('storage/' . $doc->form_137) }}" target="_blank" class="text-red-700 underline">View</a>
                        </span>
                    </li>

                    <li class="flex items-center gap-3 border p-4 rounded-lg bg-gray-50 shadow-sm">
                        <input type="checkbox" name="grades_approved"
                            class="h-5 w-5 text-green-600 border-gray-300 rounded"
                            {{ $doc->grades_status === 'approved' ? 'checked' : '' }}>
                        <span>
                            <strong>Grades:</strong>
                            <a href="{{ asset('storage/' . $doc->grades) }}" target="_blank" class="text-red-700 underline">View</a>
                        </span>
                    </li>

                    <li class="flex items-center gap-3 border p-4 rounded-lg bg-gray-50 shadow-sm">
                        <input type="checkbox" name="certificate_approved"
                            class="h-5 w-5 text-green-600 border-gray-300 rounded"
                            {{ $doc->certificate_status === 'approved' ? 'checked' : '' }}>
                        <span>
                            <strong>Certificate:</strong>
                            <a href="{{ asset('storage/' . $doc->certificate) }}" target="_blank" class="text-red-700 underline">View</a>
                        </span>
                    </li>

                    <li class="flex items-center gap-3 border p-4 rounded-lg bg-gray-50 shadow-sm">
                        <input type="checkbox" name="application_form_approved"
                            class="h-5 w-5 text-green-600 border-gray-300 rounded"
                            {{ $doc->application_form_status === 'approved' ? 'checked' : '' }}>
                        <span>
                            <strong>Application Form:</strong>
                            <a href="{{ asset('storage/' . $doc->application_form) }}" target="_blank" class="text-red-700 underline">View</a>
                        </span>
                        
                    </li>
                    <!-- Comment Section -->
                    <div class="mt-4">
                        <label for="comment-{{ $doc->id }}" class="block font-semibold mb-1 text-gray-700">Comment / Feedback</label>
                        <textarea id="comment-{{ $doc->id }}" name="comment" rows="3" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-700">{{ $doc->comment ?? '' }}</textarea>
                    </div>
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
