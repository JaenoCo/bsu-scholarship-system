@php
    $currentUser = \App\Models\User::find(session('user_id'));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Grade Records</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-red-700">Student Grade Submission</h1>
                    <p class="text-gray-600 mt-1">{{ $student->name }} • {{ $student->email }}</p>
                </div>
                <a href="{{ url('/sfao') }}" class="inline-flex items-center justify-center bg-red-700 hover:bg-red-800 text-white font-semibold px-4 py-2 rounded-lg">
                    Back to Dashboard
                </a>
            </div>
        </div>

        @if($grades->isEmpty())
            <div class="bg-white rounded-xl shadow-md p-6 text-center text-gray-600">
                No grade submissions have been uploaded for this student yet.
            </div>
        @else
            @foreach($grades->groupBy('school_year') as $schoolYear => $yearEntries)
                <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                    <div class="mb-4">
                        <h2 class="text-xl font-bold text-gray-800">School Year: {{ $schoolYear }}</h2>
                    </div>

                    @foreach($yearEntries->groupBy('semester') as $semester => $semesterEntries)
                        <div class="mb-6 border border-gray-200 rounded-lg overflow-hidden">
                            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-700">{{ $semester }}</h3>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead class="bg-gray-100 text-gray-700">
                                        <tr>
                                            <th class="px-4 py-3 font-semibold">Subject Code</th>
                                            <th class="px-4 py-3 font-semibold">Subject Name</th>
                                            <th class="px-4 py-3 font-semibold">Grade</th>
                                            <th class="px-4 py-3 font-semibold">Document</th>
                                            <th class="px-4 py-3 font-semibold">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($semesterEntries as $grade)
                                            <tr class="border-t border-gray-200">
                                                <td class="px-4 py-3">{{ $grade->subject_code }}</td>
                                                <td class="px-4 py-3">{{ $grade->subject_name }}</td>
                                                <td class="px-4 py-3 font-semibold">{{ number_format((float) $grade->grade, 2) }}</td>
                                                <td class="px-4 py-3">
                                                    @if($grade->document_path)
                                                        <a href="{{ Storage::disk('public')->url($grade->document_path) }}" target="_blank" class="text-red-700 underline">
                                                            View document
                                                        </a>
                                                    @else
                                                        <span class="text-gray-400">No document</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3">
                                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $grade->status === 'verified' ? 'bg-green-100 text-green-700' : ($grade->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                                        {{ ucfirst($grade->status ?? 'pending') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endif
    </div>
</body>
</html>
