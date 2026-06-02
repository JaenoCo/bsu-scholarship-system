@component('mail::message')
# Grant Release Notification

Dear {{ $scholar->user->name }},

Congratulations! Your grant for **{{ $scholarship->scholarship_name }}** has been approved and is ready for release.

## Grant Details
**Amount:** ₱{{ number_format($scholarship->grant_amount, 2) }}  
**Release Date:** {{ \Carbon\Carbon::parse($details['release_date'])->format('F d, Y') }}

## Claim Instructions
Please proceed to the **Scholarship and Financial Assistance Office (SFAO)** at **{{ $scholar->user->campus->name ?? 'your campus' }}** to claim your grant.

### Required Documents:
- Valid **Student ID**
- **Certificate of Registration (COR)** for the current semester
- This email or the attached Grant Slip (printed or digital)

### Office Hours:
Monday to Friday, 8:00 AM - 5:00 PM

Please ensure you bring all required documents to expedite the release process.

If you have any questions or concerns, please contact the SFAO at your campus.

Best regards,  
{{ config('app.name') }} - Scholarship Office
@endcomponent

