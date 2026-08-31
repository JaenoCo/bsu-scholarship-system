<div class="max-w-6xl mx-auto px-4 py-8" x-data="{ selectedSubmission: null }">
    <div class="flex flex-col gap-2 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Submitted Grades</h1>
        <p class="text-sm text-gray-500">Review your previous grade submissions and their review status.</p>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        @if(($gradeSubmissions ?? collect())->isEmpty())
            <div class="px-6 py-12 text-center text-gray-500">You have no submitted grades yet.</div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-4">Academic Year</th>
                            <th class="px-5 py-4">Semester</th>
                            <th class="px-5 py-4">Date Uploaded</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Attached File</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($gradeSubmissions as $submission)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4 font-semibold text-gray-900">{{ $submission->school_year }}</td>
                                <td class="px-5 py-4 text-gray-600">{{ $submission->semester }}</td>
                                <td class="px-5 py-4 text-gray-600">{{ $submission->created_at?->format('M d, Y') }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $submission->status === 'approved' ? 'bg-green-100 text-green-700' : ($submission->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                        {{ ucfirst($submission->status ?? 'pending') }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    @if($submission->file_path)
                                        <a href="{{ asset('storage/' . ltrim($submission->file_path, '/')) }}" target="_blank" rel="noopener" class="text-red-700 font-semibold hover:underline">Preview / Download</a>
                                    @else
                                        <span class="text-gray-400">No file</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <button type="button" @click="selectedSubmission = {{ $submission->toJson() }}" class="inline-flex items-center rounded-lg bg-gray-900 px-3 py-2 text-xs font-semibold text-white hover:bg-gray-700">View Details</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div x-show="selectedSubmission" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-gray-900/60" @click="selectedSubmission = null"></div>
        <div class="relative w-full max-w-3xl rounded-xl bg-white shadow-2xl" @click.stop>
            <div class="flex items-start justify-between border-b border-gray-200 px-6 py-5">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Submission Details</h2>
                    <p class="mt-1 text-sm text-gray-500"><span x-text="selectedSubmission?.school_year"></span> · <span x-text="selectedSubmission?.semester"></span></p>
                </div>
                <button type="button" @click="selectedSubmission = null" class="text-2xl leading-none text-gray-400 hover:text-gray-700" aria-label="Close">&times;</button>
            </div>
            <div class="max-h-[60vh] overflow-y-auto px-6 py-5">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                        <tr><th class="py-3 pr-4">Subject Code</th><th class="py-3 pr-4">Subject Name</th><th class="py-3 pr-4">Units</th><th class="py-3">Grade</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="subject in (selectedSubmission?.subjects || [])" :key="subject.id">
                            <tr><td class="py-3 pr-4 font-semibold" x-text="subject.subject_code"></td><td class="py-3 pr-4" x-text="subject.subject_name"></td><td class="py-3 pr-4" x-text="subject.units"></td><td class="py-3 font-semibold" x-text="subject.grade"></td></tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
