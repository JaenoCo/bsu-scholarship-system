<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Grades</title>

    <style>
        * {
            box-sizing: border-box;
        }

        /* Alpine hides [x-cloak] elements once it boots; this hides them before that,
           so the lock overlay doesn't flash visible on initial page load. */
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 14px;
            margin: 0;
            background: #f3f4f6;
        }

        .upload-wrapper {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px 12px 40px;
        }

        .page-header {
            margin-bottom: 28px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        .page-description {
            margin-top: 8px;
            color: #6b7280;
            font-size: 14px;
        }

        .card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
        }

        .card-header {
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        .card-description {
            color: #6b7280;
            font-size: 13px;
            margin-top: 5px;
        }

        .academic-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .field label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 7px;
        }

        .input,
        .select {
            width: 100%;
            box-sizing: border-box;
            padding: 11px 13px;
            border: 1px solid #d1d5db;
            border-radius: 9px;
            background: #ffffff;
            color: #1f2937;
            font-size: 14px;
            outline: none;
            transition: 0.2s;
        }

        .input:focus,
        .select:focus {
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.10);
        }

        /*
            Lock overlay shown while the form is submitting.
            IMPORTANT: this must NOT use the native `disabled` attribute on the
            inputs — a disabled form field is excluded from the browser's
            submitted FormData entirely, so disabling fields at submit time
            silently strips them from the POST body (that's what caused every
            field to come back "required" even though they were filled in).
            Instead this is a plain absolutely-positioned div that visually
            dims the form and eats clicks via pointer-events, while every
            input stays enabled and still gets submitted normally.
        */
        .form-lock-wrapper {
            position: relative;
        }

        .form-lock-overlay {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.5);
            cursor: not-allowed;
            z-index: 5;
        }

        .subjects-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 20px;
        }

        .add-button {
            border: none;
            background: #b91c1c;
            color: #fff;
            padding: 12px 20px;
            border-radius: 9px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            min-height: 44px;
            touch-action: manipulation;
        }

        .add-button:hover {
            background: #991b1b;
        }

        .subjects-container {
            display: grid;
            gap: 12px;
            padding: 16px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
        }

        .grade-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            align-items: end;
        }

        .remove-wrapper {
            display: flex;
            justify-content: flex-end;
            align-items: end;
        }

        .remove-button {
            width: 44px;
            height: 44px;
            border: none;
            border-radius: 8px;
            background: #fee2e2;
            color: #b91c1c;
            font-size: 20px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .remove-button:hover {
            background: #fecaca;
        }

        .upload-section {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }

        .file-upload {
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            background: #fafafa;
            transition: 0.2s;
        }

        .file-upload:hover {
            border-color: #dc2626;
            background: #fffafa;
        }

        .file-icon {
            font-size: 32px;
            margin-bottom: 8px;
        }

        .file-title {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
        }

        .file-description {
            color: #6b7280;
            font-size: 12px;
            margin-top: 5px;
            margin-bottom: 15px;
        }

        .file-input {
            display: block;
            margin: 0 auto;
            max-width: 100%;
            font-size: 13px;
        }

        .file-selected {
            margin-top: 12px;
            font-size: 12px;
            color: #374151;
            font-weight: 600;
            word-break: break-word;
        }

        .submit-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 25px;
        }

        .submit-button {
            border: none;
            background: #b91c1c;
            color: #fff;
            padding: 14px 28px;
            border-radius: 9px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            min-height: 48px;
            touch-action: manipulation;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .submit-button:hover {
            background: #991b1b;
        }

        .submit-button:disabled {
            background: #f3a6a6;
            cursor: not-allowed;
        }

        /* Small inline spinner shown on the submit button while submitting=true */
        .btn-spinner {
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255, 255, 255, 0.5);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .alert {
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .error-alert {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .error-alert ul {
            margin: 8px 0 0 20px;
        }

        /* ===== Toast ===== */
        .toast-wrapper {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 60;
            max-width: 360px;
            width: calc(100% - 40px);
        }

        .toast {
            background: #ffffff;
            border: 1px solid #bbf7d0;
            border-left: 4px solid #16a34a;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
            padding: 14px 16px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .toast-icon {
            width: 22px;
            height: 22px;
            flex-shrink: 0;
            color: #16a34a;
        }

        .toast-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #166534;
        }

        .toast-message {
            font-size: 13px;
            color: #374151;
            margin-top: 2px;
        }

        .toast-link {
            display: inline-block;
            margin-top: 6px;
            color: #b91c1c;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
        }

        .toast-close {
            border: none;
            background: transparent;
            color: #9ca3af;
            font-size: 16px;
            line-height: 1;
            cursor: pointer;
            padding: 2px;
        }

        .toast-close:hover {
            color: #4b5563;
        }

        @media (min-width: 640px) {
            .card {
                padding: 20px;
            }

            .page-title {
                font-size: 26px;
            }

            .card-title {
                font-size: 17px;
            }

            .subjects-header {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }

            .add-button {
                width: auto;
            }

            .submit-section {
                justify-content: flex-end;
            }

            .submit-button {
                width: auto;
            }

            .field label {
                font-size: 13px;
            }

            .grade-row {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (min-width: 768px) {
            .academic-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (min-width: 1024px) {
            .card {
                padding: 24px;
            }

            .page-title {
                font-size: 28px;
            }

            .card-title {
                font-size: 18px;
            }

            .grade-row {
                grid-template-columns: 150px minmax(250px, 1fr) 80px 110px 45px;
                align-items: end;
            }
        }
    </style>
</head>

<body>

<div class="upload-wrapper">
    <div class="page-header">
        <h1 class="page-title">Upload Grades</h1>
        <p class="page-description">Submit your academic grades and the corresponding supporting document.</p>
    </div>

        @php
        $isReadOnly = $isReadOnly ?? false;
        $isEditMode = $isEditMode ?? false;
        $submittedGrades = $submittedGrades ?? collect();
            $latestSubmission = $latestSubmission ?? null;
    @endphp

    {{--
        TOAST (replaces the old full-screen modal overlay).
        - Sits fixed top-right so it doesn't block the page.
        - x-init starts a 3s timer that flips show=false, which triggers the leave transition.
        - The × button lets the user dismiss it early.
        - This only ever renders once, on the response that carries session('success') —
          i.e. right after a successful submit/update, before the read-only view takes over.
    --}}
    @if(session('success'))
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 3000)"
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="toast-wrapper"
        >
            <div class="toast">
                <svg class="toast-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <div style="flex:1">
                    <div class="toast-title">Success</div>
                    <div class="toast-message">{{ session('success') }}</div>
                    <a href="{{ route('student.dashboard', ['tab' => 'grade_history']) }}" class="toast-link">View Submitted Grades</a>
                </div>
                <button type="button" @click="show = false" class="toast-close" aria-label="Dismiss">&times;</button>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert error-alert">
            <strong>Please correct the following:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{--
        READ-ONLY STATE
        Rendered once grades exist and $isReadOnly is true (set server-side after a
        successful submission). The "Edit submitted grades" button routes to
        student.grades.edit, which should flip $isReadOnly back to false / $isEditMode
        to true so the form below becomes editable again.
    --}}
    @if($isReadOnly)
        <div class="card" style="padding: 24px;">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:18px;">
                <div>
                    <h2 class="card-title" style="font-size: 18px; font-weight: 700;">Submitted Grades</h2>
                    <p class="card-description" style="margin-top: 6px;">Your latest grade submission is currently locked for review.</p>
                </div>
                <a href="{{ route('student.dashboard') }}" class="inline-flex items-center justify-center bg-red-700 hover:bg-red-800 text-white font-semibold px-4 py-3 rounded-lg" style="padding: 10px 16px; border-radius: 8px; font-size: 14px; text-decoration: none;">Back to Dashboard</a>
            </div>

            <div class="academic-grid" style="margin-bottom: 20px;">
                <div class="field">
                    <label>School Year</label>
                    <input type="text" value="{{ old('school_year', $latestSubmission?->school_year ?? $submittedGrades->first()->school_year ?? '') }}" class="input" disabled style="background:#f9fafb; color:#111827; cursor:default;">
                </div>

                <div class="field">
                    <label>Semester</label>
                    <select class="select" disabled style="background:#f9fafb; color:#111827; cursor:default;">
                        <option value="">Select Semester</option>
                        <option value="1st Semester" {{ old('semester', $latestSubmission?->semester ?? $submittedGrades->first()->semester ?? '') == '1st Semester' ? 'selected' : '' }}>1st Semester</option>
                        <option value="2nd Semester" {{ old('semester', $latestSubmission?->semester ?? $submittedGrades->first()->semester ?? '') == '2nd Semester' ? 'selected' : '' }}>2nd Semester</option>
                        <option value="Summer" {{ old('semester', $latestSubmission?->semester ?? $submittedGrades->first()->semester ?? '') == 'Summer' ? 'selected' : '' }}>Summer</option>
                    </select>
                </div>
            </div>

            <div class="subjects-header" style="margin-bottom: 12px;">
                <div>
                    <h2 class="card-title" style="font-size: 18px; font-weight: 700;">Subject Grades</h2>
                    <p class="card-description" style="margin-top: 6px;">Add all subjects included in this grade submission.</p>
                </div>
            </div>

            <div id="grades-container" class="subjects-container" style="padding: 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px;">
                @if($submittedGrades->isNotEmpty())
                    @foreach($submittedGrades as $index => $grade)
                        <div class="grade-row" style="display:grid; grid-template-columns: 1fr 1.5fr 0.7fr 0.8fr 44px; gap: 12px; align-items:end; margin-bottom: 12px;">
                            <div class="field">
                                <label>Subject Code</label>
                                <input type="text" value="{{ old('grades.' . $index . '.subject_code', $grade->subject_code) }}" class="input" disabled style="background:#f9fafb; color:#111827; cursor:default;">
                            </div>

                            <div class="field subject-name">
                                <label>Subject Name</label>
                                <input type="text" value="{{ old('grades.' . $index . '.subject_name', $grade->subject_name) }}" class="input" disabled style="background:#f9fafb; color:#111827; cursor:default;">
                            </div>

                            <div class="field">
                                    <label>Units</label>
                                    <input type="number" value="{{ old('grades.' . $index . '.units', $grade->units ?? 0) }}" class="input" disabled style="background:#f9fafb; color:#111827; cursor:default;">
                                </div>

                                <div class="field">
                                <label>Grade</label>
                                <input type="number" value="{{ old('grades.' . $index . '.grade', $grade->grade) }}" class="input" disabled style="background:#f9fafb; color:#111827; cursor:default;">
                            </div>

                            <div class="remove-wrapper" style="display:flex; justify-content:flex-end; align-items:flex-end;">
                                <button type="button" class="remove-button" title="Remove subject" disabled style="width:40px; height:40px; border:none; border-radius:8px; background:#fef2f2; color:#b91c1c; cursor:not-allowed; opacity:0.7;">×</button>
                            </div>
                        </div>
                    @endforeach
                @else
                        <div class="grade-row" style="display:grid; grid-template-columns: 1fr 1.5fr 0.7fr 0.8fr 44px; gap: 12px; align-items:end; margin-bottom: 12px;">
                        <div class="field">
                            <label>Subject Code</label>
                            <input type="text" class="input" disabled style="background:#f9fafb; color:#111827; cursor:default;">
                        </div>

                        <div class="field subject-name">
                            <label>Subject Name</label>
                            <input type="text" class="input" disabled style="background:#f9fafb; color:#111827; cursor:default;">
                        </div>

                        <div class="field"><label>Units</label><input type="number" class="input" disabled style="background:#f9fafb; color:#111827; cursor:default;"></div>

                        <div class="field">
                            <label>Grade</label>
                            <input type="number" class="input" disabled style="background:#f9fafb; color:#111827; cursor:default;">
                        </div>

                        <div class="remove-wrapper" style="display:flex; justify-content:flex-end; align-items:flex-end;">
                            <button type="button" class="remove-button" title="Remove subject" disabled style="width:40px; height:40px; border:none; border-radius:8px; background:#fef2f2; color:#b91c1c; cursor:not-allowed; opacity:0.7;">×</button>
                        </div>
                    </div>
                @endif
            </div>

            <div class="upload-section" style="margin-top: 20px; padding-top: 18px; border-top: 1px solid #e5e7eb;">
                <div class="field">
                    <label>Supporting Document</label>
                    <div class="file-upload" style="border: 1px dashed #d1d5db; border-radius: 12px; padding: 22px 20px; text-align: center; background:#fafafa; opacity:0.9; cursor:default;">
                        <div class="file-icon" style="font-size: 32px; margin-bottom: 8px;">📄</div>
                        <div class="file-title" style="font-size: 14px; font-weight: 600; color: #374151;">Upload your grade document</div>
                        <div class="file-description" style="color: #6b7280; font-size: 12px; margin-top: 5px; margin-bottom: 12px;">This document is currently locked for review.</div>
                        <input type="file" class="file-input" disabled style="display:block; margin:0 auto; max-width:100%;">
                        <div id="file-selected" class="file-selected" style="margin-top: 12px; font-size: 12px; color: #374151; font-weight: 600;">{{ $latestSubmission?->file_path || $submittedGrades->first()?->document_path ? 'Current file attached' : 'No file chosen' }}</div>
                    </div>
                </div>
            </div>

            <div class="submit-section" style="display:flex; justify-content:flex-end; margin-top: 20px;">
               <a
    href="{{ route('student.grades.upload', ['edit' => 1]) }}"
    class="submit-button"
    style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center; padding:12px 20px; border-radius:8px; font-size:14px; font-weight:700; background:#b91c1c; color:white;"
>
    Edit submitted grades
</a>
            </div>
        </div>
    @else
        {{--
            EDITABLE FORM STATE
            x-data="{ submitting: false }" tracks whether the form is mid-request.
            @submit sets submitting=true immediately. This drives:
              1. a click-blocking overlay (.form-lock-overlay) so the user can't
                 edit fields or double-click "Add Subject" / "Remove" mid-submit
              2. the submit button's own disabled state + a spinner/"Submitting..."
                 label, so it can't be clicked twice
            Deliberately NOT using the native `disabled` attribute anywhere on the
            actual form fields — a disabled input is dropped from the browser's
            FormData at submit time, which would silently strip real values from
            the POST/PUT body. The overlay blocks interaction visually and via
            pointer-events without touching each field's disabled state.
            Because this is a normal (non-AJAX) form POST, this state naturally
            resets once the page navigates to the redirect response.
        --}}
        <form
            x-data="{ submitting: false }"
            @submit="submitting = true"
            action="{{ $isEditMode ? route('student.grades.upload.update') : route('student.grades.upload.submit') }}"
            method="POST"
            enctype="multipart/form-data"
            class="form-lock-wrapper"
        >
            @csrf
            @if($isEditMode)
                @method('PUT')
            @endif

            {{-- Blocks clicks on everything behind it while submitting; inputs stay enabled/submittable --}}
            <div class="form-lock-overlay" x-show="submitting" x-cloak></div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Academic Information</h2>
                    <p class="card-description">Provide the school year and semester for these grades.</p>
                </div>

                <div class="academic-grid">
                    <div class="field">
                        <label>School Year</label>
                        <input type="text" name="school_year" value="{{ old('school_year', $latestSubmission?->school_year ?? $submittedGrades->first()->school_year ?? '') }}" placeholder="2025-2026" required class="input">
                    </div>

                    <div class="field">
                        <label>Semester</label>
                        <select name="semester" required class="select">
                            <option value="">Select Semester</option>
                            <option value="1st Semester" {{ old('semester', $latestSubmission?->semester ?? $submittedGrades->first()->semester ?? '') == '1st Semester' ? 'selected' : '' }}>1st Semester</option>
                            <option value="2nd Semester" {{ old('semester', $latestSubmission?->semester ?? $submittedGrades->first()->semester ?? '') == '2nd Semester' ? 'selected' : '' }}>2nd Semester</option>
                            <option value="Summer" {{ old('semester', $latestSubmission?->semester ?? $submittedGrades->first()->semester ?? '') == 'Summer' ? 'selected' : '' }}>Summer</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="subjects-header">
                    <div>
                        <h2 class="card-title">Subject Grades</h2>
                        <p class="card-description">Add all subjects included in this grade submission.</p>
                    </div>

                    <button type="button" onclick="addGradeRow()" class="add-button">+ Add Subject</button>
                </div>

                <div id="grades-container" class="subjects-container">
                    @if($submittedGrades->isNotEmpty())
                        @foreach($submittedGrades as $index => $grade)
                            <div class="grade-row">
                                <div class="field">
                                    <label>Subject Code</label>
                                    <input type="text" name="grades[{{ $index }}][subject_code]" value="{{ old('grades.' . $index . '.subject_code', $grade->subject_code) }}" placeholder="IT101" required class="input">
                                </div>

                                <div class="field subject-name">
                                    <label>Subject Name</label>
                                    <input type="text" name="grades[{{ $index }}][subject_name]" value="{{ old('grades.' . $index . '.subject_name', $grade->subject_name) }}" placeholder="Introduction to Computing" required class="input">
                                </div>

                                <div class="field">
                                    <label>Units</label>
                                    <input type="number" name="grades[{{ $index }}][units]" min="0" max="99.99" step="0.01" value="{{ old('grades.' . $index . '.units', $grade->units ?? 0) }}" placeholder="3" required class="input">
                                </div>

                                <div class="field">
                                    <label>Grade</label>
                                    <input type="number" name="grades[{{ $index }}][grade]" min="0" max="100" step="0.01" value="{{ old('grades.' . $index . '.grade', $grade->grade) }}" placeholder="90" required class="input">
                                </div>

                                <div class="remove-wrapper">
                                    <button type="button" onclick="removeGradeRow(this)" class="remove-button" title="Remove subject">×</button>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="grade-row">
                            <div class="field">
                                <label>Subject Code</label>
                                <input type="text" name="grades[0][subject_code]" placeholder="IT101" required class="input">
                            </div>

                            <div class="field subject-name">
                                <label>Subject Name</label>
                                <input type="text" name="grades[0][subject_name]" placeholder="Introduction to Computing" required class="input">
                            </div>

                            <div class="field">
                                <label>Units</label>
                                <input type="number" name="grades[0][units]" min="0" max="99.99" step="0.01" placeholder="3" required class="input">
                            </div>

                            <div class="field">
                                <label>Grade</label>
                                <input type="number" name="grades[0][grade]" min="0" max="100" step="0.01" placeholder="90" required class="input">
                            </div>

                            <div class="remove-wrapper">
                                <button type="button" onclick="removeGradeRow(this)" class="remove-button" title="Remove subject">×</button>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="upload-section">
                    <div class="field">
                        <label>Supporting Document</label>
                        <div class="file-upload">
                            <div class="file-icon">📄</div>
                            <div class="file-title">Upload your grade document</div>
                            <div class="file-description">Upload one PDF, JPG, JPEG, or PNG containing your supporting grades document.</div>
                            <input id="studentDocument" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" {{ $isEditMode ? '' : 'required' }} class="file-input">
                            <div id="file-selected" class="file-selected">{{ $isEditMode && ($latestSubmission?->file_path || $submittedGrades->first()?->document_path) ? 'Current file attached' : 'No file chosen' }}</div>
                        </div>
                    </div>
                </div>

                <div class="submit-section">
                    {{-- :disabled + x-text drive the "locked while submitting" button state --}}
                    <button type="submit" class="submit-button" :disabled="submitting">
                        <span x-show="submitting" class="btn-spinner"></span>
                        <span x-text="submitting ? 'Submitting...' : '{{ $isEditMode ? 'Save Changes' : 'Submit Grades' }}'"></span>
                    </button>
                </div>
            </div>

        </form>
    @endif
</div>

<script>
    let gradeIndex = {{ max($submittedGrades->count(), 1) }};

    const fileInput = document.getElementById('studentDocument');
    const fileSelected = document.getElementById('file-selected');

    if (fileInput && fileSelected) {
        fileInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                    const file = this.files[0];
                    const size = file.size < 1024 * 1024
                        ? `${(file.size / 1024).toFixed(1)} KB`
                        : `${(file.size / (1024 * 1024)).toFixed(1)} MB`;
                    fileSelected.textContent = `Selected file: ${file.name} (${size})`;
                return;
            }

            fileSelected.textContent = 'No file chosen';
        });
    }

    function addGradeRow() {
        const container = document.getElementById('grades-container');
        const row = document.createElement('div');
        row.className = 'grade-row';

        row.innerHTML = `
            <div class="field">
                <label>Subject Code</label>
                <input type="text" name="grades[${gradeIndex}][subject_code]" placeholder="IT101" required class="input">
            </div>

            <div class="field">
                <label>Units</label>
                <input type="number" name="grades[${gradeIndex}][units]" min="0" max="99.99" step="0.01" placeholder="3" required class="input">
            </div>

            <div class="field subject-name">
                <label>Subject Name</label>
                <input type="text" name="grades[${gradeIndex}][subject_name]" placeholder="Introduction to Computing" required class="input">
            </div>

            <div class="field">
                <label>Grade</label>
                <input type="number" name="grades[${gradeIndex}][grade]" min="0" max="100" step="0.01" placeholder="90" required class="input">
            </div>

            <div class="remove-wrapper">
                <button type="button" onclick="removeGradeRow(this)" class="remove-button" title="Remove subject">×</button>
            </div>
        `;

        container.appendChild(row);
        gradeIndex++;
    }

    function removeGradeRow(button) {
        const rows = document.querySelectorAll('.grade-row');
        if (rows.length <= 1) {
            return;
        }

        button.closest('.grade-row').remove();
    }
</script>

</body>
</html>