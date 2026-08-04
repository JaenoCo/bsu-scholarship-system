@extends('layouts.dashboard', ['user' => $user, 'title' => 'Edit Scholar'])

@section('sidebar-menu')
    @include('central.components.sidebar-menu', ['user' => $user, 'campuses' => $campuses])
@endsection

@section('navbar')
    <x-layout.navbar
        title="Edit Scholar"
        subtitle="Central Dashboard"
        :user="$user"
        :profile="false"
        :settings="true"
        settings-click="$dispatch('switch-tab', 'account_settings')"
        :logout="true"
    />
@endsection

@section('content')
    <style>
        :root {
            --bsu-bg: #f8fafc;
            --bsu-primary: #7B1113;
            --bsu-secondary: #991B1B;
            --bsu-border: #e5e7eb;
            --bsu-shadow: 0 2px 10px rgba(0,0,0,.05);
            --bsu-radius: 14px;
        }

        .bsu-card {
            background: #fff;
            border: 1px solid var(--bsu-border);
            border-radius: var(--bsu-radius);
            box-shadow: var(--bsu-shadow);
        }

        .bsu-card-header {
            padding: 1.15rem 1.25rem;
            border-bottom: 1px solid var(--bsu-border);
        }

        .bsu-card-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: #111827;
        }

        .bsu-card-subtitle {
            margin: .2rem 0 0;
            color: #64748b;
            font-size: .84rem;
        }

        .bsu-label {
            display: block;
            margin-bottom: .4rem;
            font-weight: 700;
            color: #334155;
        }

        .bsu-input, .bsu-select, .bsu-textarea {
            width: 100%;
            border: 1px solid var(--bsu-border);
            border-radius: 10px;
            padding: .7rem .8rem;
            background: #fff;
        }

        .bsu-input:focus, .bsu-select:focus, .bsu-textarea:focus {
            outline: none;
            border-color: var(--bsu-primary);
            box-shadow: 0 0 0 3px rgba(123,17,19,.12);
        }
    </style>

    <div class="mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <a href="{{ route('central.scholars.show', $scholar->id) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to Details
                    </a>
                </div>
                <h1 class="fw-bold fs-3 mb-1">Edit Scholar</h1>
                <p class="text-muted mb-0">Update the scholar's award status, type, and notes.</p>
            </div>
        </div>
    </div>

    <div class="bsu-card">
        <div class="bsu-card-header">
            <div>
                <h2 class="bsu-card-title">Scholar Record</h2>
                <p class="bsu-card-subtitle">{{ $scholar->user->name ?? 'Student' }} • {{ $scholar->scholarship->scholarship_name ?? 'Scholarship' }}</p>
            </div>
        </div>

        <div class="p-4">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('central.scholars.update', $scholar->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    <div class="col-12 col-lg-6">
                        <label class="bsu-label" for="scholarship_id">Scholarship</label>
                        <select id="scholarship_id" name="scholarship_id" class="bsu-select" required>
                            @foreach ($scholarships as $scholarship)
                                <option value="{{ $scholarship->id }}" {{ old('scholarship_id', $scholar->scholarship_id) == $scholarship->id ? 'selected' : '' }}>
                                    {{ $scholarship->scholarship_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-lg-3">
                        <label class="bsu-label" for="type">Scholar Type</label>
                        <select id="type" name="type" class="bsu-select" required>
                            <option value="new" {{ old('type', $scholar->type) == 'new' ? 'selected' : '' }}>New</option>
                            <option value="old" {{ old('type', $scholar->type) == 'old' ? 'selected' : '' }}>Old</option>
                        </select>
                    </div>

                    <div class="col-12 col-lg-3">
                        <label class="bsu-label" for="status">Status</label>
                        <select id="status" name="status" class="bsu-select" required>
                            <option value="active" {{ old('status', $scholar->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $scholar->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="suspended" {{ old('status', $scholar->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="completed" {{ old('status', $scholar->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>

                    <div class="col-12 col-lg-6">
                        <label class="bsu-label" for="scholarship_start_date">Start Date</label>
                        <input type="date" id="scholarship_start_date" name="scholarship_start_date" class="bsu-input" value="{{ old('scholarship_start_date', optional($scholar->scholarship_start_date)->format('Y-m-d')) }}" required>
                    </div>

                    <div class="col-12 col-lg-6">
                        <label class="bsu-label" for="scholarship_end_date">End Date</label>
                        <input type="date" id="scholarship_end_date" name="scholarship_end_date" class="bsu-input" value="{{ old('scholarship_end_date', optional($scholar->scholarship_end_date)->format('Y-m-d')) }}">
                    </div>

                    <div class="col-12">
                        <label class="bsu-label" for="notes">Notes</label>
                        <textarea id="notes" name="notes" rows="4" class="bsu-textarea">{{ old('notes', $scholar->notes) }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('central.scholars.show', $scholar->id) }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-bsu">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection
