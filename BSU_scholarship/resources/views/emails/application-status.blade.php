@component('mail::message')
# Application Status Update

Your application for **{{ $application->scholarship->scholarship_name ?? 'Scholarship' }}** has been updated.

**New Status:** {{ ucfirst($status) }}

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
