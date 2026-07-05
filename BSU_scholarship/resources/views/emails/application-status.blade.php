@component('mail::message')
# Application Status Update

Your application for **{{ $application->scholarship->scholarship_name ?? 'Scholarship' }}** has been updated.

@php
	$statusLabel = ucwords(str_replace('_', ' ', $status));
@endphp

**New Status:** {{ $statusLabel }}

@if($customMessage)
**Message:**
{{ $customMessage }}
@endif

Login to your account to view more details.

@component('mail::button', ['url' => route('login')])
Check it out
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
