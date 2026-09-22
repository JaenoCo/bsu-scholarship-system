<div class="max-w-6xl mx-auto px-4 py-8" x-data="{ selectedSubmissionId: null }">
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
                            <th class="px-5 py-4">Attached File</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($gradeSubmissions as $submission)
                            <tr class="hover:bg-gray-50" data-submission-row="{{ $submission->id }}">
                                <td class="px-5 py-4 font-semibold text-gray-900" data-submission-school-year>{{ $submission->school_year }}</td>
                                <td class="px-5 py-4 text-gray-600" data-submission-semester>{{ $submission->semester }}</td>
                                <td class="px-5 py-4 text-gray-600">{{ $submission->created_at?->format('M d, Y') }}</td>

                                <td class="px-5 py-4">
                                    @if($submission->file_path)
                                        <a href="{{ route('student.grades.document', $submission) }}" target="_blank" rel="noopener" class="text-red-700 font-semibold hover:underline">Preview / Download</a>
                                    @else
                                        <span class="text-gray-400">No file</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <button type="button" @click="selectedSubmissionId = {{ $submission->id }}" class="inline-flex items-center rounded-lg bg-gray-900 px-3 py-2 text-xs font-semibold text-white hover:bg-gray-700">View Details</button>
                                        <button type="button" data-id="{{ $submission->id }}" class="btn-edit inline-flex items-center rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">Edit</button>
                                        <form method="POST" action="{{ route('student.grades.submissions.destroy', $submission) }}" onsubmit="return confirm('Delete this grade submission?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center rounded-lg border border-red-200 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @foreach($gradeSubmissions as $submission)
        @php
            $modalSubjects = $submission->subjects()->get();
            if ($modalSubjects->isEmpty()) {
                $modalSubjects = \App\Models\StudentGrade::where('user_id', $submission->user_id)
                    ->where('school_year', $submission->school_year)
                    ->where('semester', $submission->semester)
                    ->orderBy('created_at')
                    ->get();
            }
        @endphp
        <div x-show="selectedSubmissionId === {{ $submission->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-gray-900/60" @click="selectedSubmissionId = null"></div>
            <div class="relative w-full max-w-3xl rounded-xl bg-white shadow-2xl" @click.stop>
                <div class="flex items-start justify-between border-b border-gray-200 px-6 py-5">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Submission Details</h2>
                        <p class="mt-1 text-sm text-gray-500" data-submission-details-period>{{ $submission->school_year }} · {{ $submission->semester }}</p>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-amber-600">{{ ucfirst($submission->status ?? 'pending') }}</p>
                    </div>
                    <button type="button" @click="selectedSubmissionId = null" class="text-2xl leading-none text-gray-400 hover:text-gray-700" aria-label="Close">&times;</button>
                </div>
                <div class="max-h-[60vh] overflow-y-auto px-6 py-5 text-gray-700" style="color: #374151;">
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                            <tr><th class="py-3 pr-4">Subject Code</th><th class="py-3 pr-4">Subject Name</th><th class="py-3 pr-4">Units</th><th class="py-3">Grade</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100" data-submission-details-subjects>
                            @forelse($modalSubjects as $subject)
                                <tr class="text-gray-700" style="color: #374151;"><td class="py-3 pr-4 font-semibold text-gray-900" style="color: #111827;">{{ $subject->subject_code }}</td><td class="py-3 pr-4 text-gray-700" style="color: #374151;">{{ $subject->subject_name }}</td><td class="py-3 pr-4 text-gray-700" style="color: #374151;">{{ $subject->units }}</td><td class="py-3 font-semibold text-gray-900" style="color: #111827;">{{ $subject->grade }}</td></tr>
                            @empty
                                <tr><td colspan="4" class="py-6 text-center text-gray-500">No subject grades were recorded for this submission.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($submission->file_path)
                    <div class="flex justify-end border-t border-gray-200 px-6 py-4">
                        <a href="{{ route('student.grades.document', $submission) }}" target="_blank" rel="noopener" class="text-sm font-semibold text-red-700 hover:underline">Preview / Download Attached File</a>
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>

<div id="gradeEditModal" class="fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto bg-gray-900/60 p-4" aria-modal="true" role="dialog">
    <div class="relative flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-3">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Edit Grade Submission</h3>
            </div>
            <button type="button" id="closeGradeEditModal" class="text-2xl leading-none text-gray-400 hover:text-gray-700" aria-label="Close">&times;</button>
        </div>

        <form id="gradeEditForm" method="POST" enctype="multipart/form-data" class="min-h-0 flex-1 space-y-4 overflow-y-auto px-5 py-4">
            @csrf

            <div>
                <label for="edit_school_year" class="mb-1.5 block text-sm font-semibold text-gray-700">Academic Year</label>
                <input id="edit_school_year" name="school_year" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100" placeholder="2025-2026" required>
            </div>

            <div>
                <label for="edit_semester" class="mb-1.5 block text-sm font-semibold text-gray-700">Semester</label>
                <select id="edit_semester" name="semester" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100" required>
                    <option value="">Select Semester</option>
                    <option value="1st Semester">1st Semester</option>
                    <option value="2nd Semester">2nd Semester</option>
                    <option value="Summer">Summer</option>
                </select>
            </div>

            <div>
                <div class="mb-2 flex items-center justify-between gap-3">
                    <label class="block text-sm font-semibold text-gray-700">Subjects</label>
                    <button type="button" id="addEditSubjectRow" class="rounded-lg border border-red-200 bg-red-50 px-2.5 py-1.5 text-[11px] font-semibold text-red-700 hover:bg-red-100">+ Add</button>
                </div>
                <div id="edit_subject_rows" class="space-y-3"></div>
            </div>

            <div>
                <label for="edit_document" class="mb-1.5 block text-sm font-semibold text-gray-700">Grade File Attachment</label>
                <input id="edit_document" name="document" type="file" accept=".pdf,.jpg,.jpeg,.png" class="block w-full rounded-lg border border-dashed border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-600 file:mr-3 file:rounded file:border-0 file:bg-red-600 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-red-700">
                <p id="edit_document_name" class="mt-2 text-xs text-gray-500">Leave blank to keep the current attachment.</p>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-3">
                <button type="button" id="cancelGradeEditModal" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="rounded-lg bg-red-700 px-3 py-2 text-sm font-semibold text-white hover:bg-red-800">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('gradeEditModal');
        const form = document.getElementById('gradeEditForm');
        const schoolYearInput = document.getElementById('edit_school_year');
        const semesterInput = document.getElementById('edit_semester');
        const documentInput = document.getElementById('edit_document');
        const documentName = document.getElementById('edit_document_name');
        const subjectRowsContainer = document.getElementById('edit_subject_rows');
        const addSubjectButton = document.getElementById('addEditSubjectRow');
        const closeButtons = [
            document.getElementById('closeGradeEditModal'),
            document.getElementById('cancelGradeEditModal')
        ];

        function createSubjectRow(subject = {}) {
            const row = document.createElement('div');
            row.className = 'grid gap-2 rounded-xl border border-gray-200 bg-gray-50 p-3 md:grid-cols-[1fr_1.2fr_.7fr_.7fr_44px]';

            const fields = [
                { name: 'subject_code', label: 'Code', value: subject.subject_code || '' },
                { name: 'subject_name', label: 'Subject', value: subject.subject_name || '' },
                { name: 'units', label: 'Units', value: subject.units || '' },
                { name: 'grade', label: 'Grade', value: subject.grade || '' },
            ];

            fields.forEach((field) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'flex flex-col gap-1';

                const label = document.createElement('label');
                label.className = 'text-[11px] font-semibold uppercase tracking-wide text-gray-500';
                label.textContent = field.label;

                const input = document.createElement('input');
                input.type = field.name === 'subject_name' ? 'text' : 'text';
                input.value = field.value;
                input.name = field.name;
                input.placeholder = field.name === 'subject_code' ? 'IT101' : field.name === 'subject_name' ? 'Intro to Computing' : field.name === 'units' ? '3' : '1.75';
                input.className = 'w-full rounded-lg border border-gray-300 bg-white px-2.5 py-2 text-sm text-gray-900 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100';
                input.required = field.name !== 'units';

                if (field.name === 'grade') {
                    input.min = '1.00';
                    input.max = '5.00';
                    input.step = '0.25';
                }

                if (field.name === 'units') {
                    input.inputMode = 'decimal';
                    input.pattern = '[0-9.]*';
                }

                wrapper.appendChild(label);
                wrapper.appendChild(input);
                row.appendChild(wrapper);
            });

            const removeWrapper = document.createElement('div');
            removeWrapper.className = 'flex items-end justify-end';
            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'h-10 w-10 rounded-lg border border-red-200 bg-red-50 text-lg font-bold text-red-700 hover:bg-red-100';
            removeButton.textContent = '×';
            removeButton.title = 'Remove subject';
            removeButton.addEventListener('click', () => {
                const rows = subjectRowsContainer.querySelectorAll('.edit-subject-row');
                if (rows.length > 1) {
                    row.remove();
                }
            });

            removeWrapper.appendChild(removeButton);
            row.appendChild(removeWrapper);
            row.classList.add('edit-subject-row');
            return row;
        }

        function renderSubjectRows(subjects = []) {
            subjectRowsContainer.innerHTML = '';

            if (!subjects.length) {
                subjectRowsContainer.appendChild(createSubjectRow());
                return;
            }

            subjects.forEach((subject) => {
                subjectRowsContainer.appendChild(createSubjectRow(subject));
            });
        }

        function buildSubjectsPayload() {
            const rows = Array.from(subjectRowsContainer.querySelectorAll('.edit-subject-row'));
            const payload = rows.map((row) => {
                const code = row.querySelector('input[name="subject_code"]')?.value?.trim() || '';
                const name = row.querySelector('input[name="subject_name"]')?.value?.trim() || '';
                const units = row.querySelector('input[name="units"]')?.value?.trim() || '';
                const grade = row.querySelector('input[name="grade"]')?.value?.trim() || '';

                return {
                    subject_code: code,
                    subject_name: name,
                    units,
                    grade,
                };
            }).filter((row) => row.subject_code || row.subject_name || row.units || row.grade);

            return payload;
        }

        function escapeHtml(value) {
            const element = document.createElement('span');
            element.textContent = String(value ?? '');
            return element.innerHTML;
        }

        function reflectUpdatedSubmission(submission) {
            const row = document.querySelector(`[data-submission-row="${submission.id}"]`);
            if (row) {
                row.querySelector('[data-submission-school-year]').textContent = submission.school_year;
                row.querySelector('[data-submission-semester]').textContent = submission.semester;
                const attachment = row.querySelector('[data-submission-attachment]');
                if (attachment) {
                    attachment.innerHTML = submission.has_file
                        ? `<a href="/student/grades/submissions/${submission.id}/document" target="_blank" rel="noopener" class="text-red-700 font-semibold hover:underline">Preview / Download</a>`
                        : '<span class="text-gray-400">No file</span>';
                }
            }

            const detailModal = document.querySelector(`[x-show*="${submission.id}"]`);
            const period = detailModal?.querySelector('[data-submission-details-period]');
            if (period) period.textContent = `${submission.school_year} · ${submission.semester}`;
            const subjectBody = detailModal?.querySelector('[data-submission-details-subjects]');
            if (subjectBody) {
                subjectBody.innerHTML = submission.subjects.map((subject) => `
                    <tr class="text-gray-700">
                        <td class="py-3 pr-4 font-semibold text-gray-900">${escapeHtml(subject.subject_code)}</td>
                        <td class="py-3 pr-4 text-gray-700">${escapeHtml(subject.subject_name)}</td>
                        <td class="py-3 pr-4 text-gray-700">${escapeHtml(subject.units)}</td>
                        <td class="py-3 font-semibold text-gray-900">${escapeHtml(subject.grade)}</td>
                    </tr>`).join('');
            }
        }

        function openModal() {
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.style.display = 'none';
            form.reset();
            form.dataset.submissionId = '';
            renderSubjectRows([]);
            documentName.textContent = 'Leave blank to keep the current attachment.';
        }

        closeButtons.forEach((button) => {
            if (button) {
                button.addEventListener('click', closeModal);
            }
        });

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });

        addSubjectButton.addEventListener('click', () => {
            subjectRowsContainer.appendChild(createSubjectRow());
        });

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        document.querySelectorAll('.btn-edit').forEach((button) => {
            button.addEventListener('click', async () => {
                const submissionId = button.dataset.id;

                try {
                    const response = await fetch(`/student/grades/${submissionId}`, {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Unable to load this submission.');
                    }

                    form.dataset.submissionId = String(submissionId);
                    schoolYearInput.value = data.school_year || '';
                    semesterInput.value = data.semester || '';
                    documentInput.value = '';
                    renderSubjectRows(data.subjects || []);
                    documentName.textContent = data.has_file
                        ? 'Current attachment is already saved. Upload a new file to replace it.'
                        : 'No attachment saved yet.';

                    openModal();
                } catch (error) {
                    window.alert(error.message || 'Something went wrong while loading the submission.');
                }
            });
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const submissionId = form.dataset.submissionId;
            if (!submissionId) {
                window.alert('No submission was selected for editing.');
                return;
            }

            const schoolYearValue = schoolYearInput.value.trim();
            const semesterValue = semesterInput.value.trim();

            if (!schoolYearValue) {
                window.alert('Academic year is required.');
                schoolYearInput.focus();
                return;
            }

            if (!semesterValue) {
                window.alert('Semester is required.');
                semesterInput.focus();
                return;
            }

            const subjects = buildSubjectsPayload();
            if (!subjects.length) {
                window.alert('Please add at least one subject row before saving.');
                return;
            }

            const invalidSubject = subjects.find((subject) => !subject.subject_code || !subject.subject_name || !subject.grade);
            if (invalidSubject) {
                window.alert('Please complete every subject row with a code, subject name, and grade before saving.');
                return;
            }

            const formData = new FormData();
            // PHP reliably parses multipart form fields and uploaded files for POST requests.
            // Laravel applies this method override as a PUT request.
            formData.append('_method', 'PUT');
            formData.append('school_year', schoolYearValue);
            formData.append('semester', semesterValue);

            if (documentInput.files && documentInput.files[0]) {
                formData.append('document', documentInput.files[0]);
            }

            subjects.forEach((subject, index) => {
                formData.append(`subjects[${index}][subject_code]`, subject.subject_code);
                formData.append(`subjects[${index}][subject_name]`, subject.subject_name);
                formData.append(`subjects[${index}][units]`, subject.units || '0');
                formData.append(`subjects[${index}][grade]`, subject.grade);
            });

            try {
                const response = await fetch(`/student/grades/${submissionId}`, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'The grade submission could not be updated.');
                }

                reflectUpdatedSubmission(result.submission);
                closeModal();
            } catch (error) {
                window.alert(error.message || 'Something went wrong while saving the submission.');
            }
        });

        renderSubjectRows([]);
    });
</script>
