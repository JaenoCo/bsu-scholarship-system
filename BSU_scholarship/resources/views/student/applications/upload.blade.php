@extends('layouts.focused')

@section('page-title', 'Upload Required Documents')

@section('navbar-title', 'Upload Documents')
@section('back-url', route('student.dashboard'))
@section('back-text', 'Back to Dashboard')

@section('content')
<div class="max-w-4xl mx-auto mt-12 p-6 bg-white rounded-lg shadow-md">
    @include('student.partials.page-header', [
      'title' => 'Upload Required Documents',
      'subtitle' => 'For ' . $scholarship->scholarship_name
    ])

    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 p-4 mb-6 rounded">
            <p class="text-green-700 font-medium">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Validation Errors -->
    @if($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 p-4 mb-6 rounded">
            <ul class="list-disc pl-5 text-red-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Upload Form -->
    <form action="{{ route('student.upload-documents.submit', ['scholarship_id' => $scholarship->id]) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div>
            <label class="block font-semibold mb-2 text-gray-700">Form 137 <span class="text-red-500">*</span></label>
            <input type="file" name="form_137" accept=".pdf,.jpg,.png,.docx" required
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-bsu-red">
        </div>

        <div>
            <label class="block font-semibold mb-2 text-gray-700">Copy of Grades (Previous Semester) <span class="text-red-500">*</span></label>
            <input type="file" name="grades" accept=".pdf,.jpg,.png,.docx" required
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-bsu-red">
        </div>

        <div>
            <label class="block font-semibold mb-2 text-gray-700">Certificate of Employment (if government employee)</label>
            <input type="file" name="certificate" accept=".pdf,.jpg,.png,.docx"
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-bsu-red">
            <p class="text-sm text-gray-500 mt-1">Optional if not applicable</p>
        </div>

        <div>
            <label class="block font-semibold mb-2 text-gray-700">Application Form <span class="text-red-500">*</span></label>
            <input type="file" name="application_form" accept=".pdf,.jpg,.png,.docx" required
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-bsu-red">
        </div>

        <button type="submit" class="w-full bg-bsu-red text-white font-semibold px-4 py-3 rounded hover:bg-bsu-redDark transition-colors">
            Submit Documents
        </button>
    </form>
</div>
@endsection
