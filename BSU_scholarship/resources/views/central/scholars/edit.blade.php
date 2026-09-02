@extends('layouts.focused')

@section('navbar-title', 'Edit Scholar')
@section('back-url', route('central.scholars.show', $scholar->id))
@section('back-text', 'Back to Scholar')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div>
        <p class="text-sm font-medium uppercase tracking-[0.2em] text-bsu-red">Update Scholar</p>
        <h1 class="mt-1 text-3xl font-extrabold text-gray-900 dark:text-white">Edit Scholar Record</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Review and adjust the scholarship assignment for {{ $scholar->user->name ?? 'this scholar' }}.</p>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-200">
            <p class="font-semibold">Please fix the following errors:</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('central.scholars.update', $scholar->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="mb-5 border-b border-gray-200 pb-4 dark:border-gray-700">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Scholar Information</h2>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-200">Student</label>
                    <input type="text" value="{{ $scholar->user->name ?? 'Unknown Student' }}" class="w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 text-gray-700 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200" disabled>
                </div>

                <div>
                    <label for="scholarship_id" class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-200">Scholarship Program</label>
                    <select id="scholarship_id" name="scholarship_id" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 focus:border-bsu-red focus:outline-none focus:ring-2 focus:ring-bsu-red/20 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" required>
                        @foreach($scholarships as $scholarship)
                            <option value="{{ $scholarship->id }}" {{ old('scholarship_id', $scholar->scholarship_id) == $scholarship->id ? 'selected' : '' }}>
                                {{ $scholarship->scholarship_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="type" class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-200">Scholar Type</label>
                    <select id="type" name="type" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 focus:border-bsu-red focus:outline-none focus:ring-2 focus:ring-bsu-red/20 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" required>
                        <option value="new" {{ old('type', $scholar->type) === 'new' ? 'selected' : '' }}>New</option>
                        <option value="old" {{ old('type', $scholar->type) === 'old' ? 'selected' : '' }}>Old</option>
                    </select>
                </div>

                <div>
                    <label for="status" class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-200">Status</label>
                    <select id="status" name="status" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 focus:border-bsu-red focus:outline-none focus:ring-2 focus:ring-bsu-red/20 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" required>
                        @foreach(['active', 'inactive', 'suspended', 'completed'] as $status)
                            <option value="{{ $status }}" {{ old('status', $scholar->status) === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="scholarship_start_date" class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-200">Start Date</label>
                    <input type="date" id="scholarship_start_date" name="scholarship_start_date" value="{{ old('scholarship_start_date', optional($scholar->scholarship_start_date)->format('Y-m-d')) }}" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 focus:border-bsu-red focus:outline-none focus:ring-2 focus:ring-bsu-red/20 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" required>
                </div>

                <div>
                    <label for="scholarship_end_date" class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-200">End Date</label>
                    <input type="date" id="scholarship_end_date" name="scholarship_end_date" value="{{ old('scholarship_end_date', optional($scholar->scholarship_end_date)->format('Y-m-d')) }}" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 focus:border-bsu-red focus:outline-none focus:ring-2 focus:ring-bsu-red/20 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                </div>

                <div class="md:col-span-2">
                    <label for="notes" class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-200">Notes</label>
                    <textarea id="notes" name="notes" rows="5" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 focus:border-bsu-red focus:outline-none focus:ring-2 focus:ring-bsu-red/20 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" placeholder="Add scholarship notes or remarks...">{{ old('notes', $scholar->notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <a href="{{ route('central.scholars.show', $scholar->id) }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700">
                Cancel
            </a>
            <button type="submit" class="inline-flex items-center rounded-lg bg-bsu-red px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
