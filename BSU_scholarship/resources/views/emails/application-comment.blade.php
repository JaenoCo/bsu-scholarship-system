@component('mail::message')
# New Comment on Your Application

You have received a new comment on your application for **{{ $application->scholarship->scholarship_name ?? 'Scholarship' }}**.

**Comment:**
{{ $comment }}

Login to your account to view the full discussion and respond.

@component('mail::button', ['url' => route('login')])
Check it out
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
