@component('mail::message')
# Scholarship Grant Released

Dear Red Spartan,

Your scholarship **"{{ $scholarship->scholarship_name }}"** has released its grant of **"₱{{ number_format((float) $grantRelease->amount, 2) }}"**.

Please visit the SFAO office and present the QR code below to the SFAO staff for verification and processing.

**Tracking Number:** {{ $grantRelease->tracking_number }}

<img src="{{ $message->embedData($grantRelease->qr_code, 'grant-qr.svg', 'image/svg+xml') }}" alt="Grant tracking QR code" width="256" height="256">

Thank you.

SFAO
@endcomponent
