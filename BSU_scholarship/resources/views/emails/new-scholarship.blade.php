@component('mail::message')
# New Scholarship Opportunity!

A new scholarship, **{{ $scholarship->scholarship_name }}**, has been posted and might be relevant to you.

**Description:**
{{ Str::limit($scholarship->description, 200) }}

**Application Deadline:** {{ \Carbon\Carbon::parse($scholarship->submission_deadline)->format('F j, Y') }}

Don't miss out on this opportunity! Log in to your account to view the full details and apply.

@component('mail::button', ['url' => route('login')])
Check it out
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
