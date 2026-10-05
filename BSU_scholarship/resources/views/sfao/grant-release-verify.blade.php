<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Grant Release Verification</title>
</head>
<body style="font-family: Arial, sans-serif; max-width: 640px; margin: 40px auto; padding: 24px; color: #1f2937;">
    <h1 style="color: #991b1b;">Grant Release Verification</h1>
    <p><strong>Status:</strong> {{ ucfirst($release->status) }}</p>
    <p><strong>Tracking Number:</strong> {{ $release->tracking_number }}</p>
    <p><strong>Student:</strong> {{ $release->student->name }}</p>
    <p><strong>Scholarship:</strong> {{ $release->scholarship->scholarship_name }}</p>
    <p><strong>Amount:</strong> ₱{{ number_format((float) $release->amount, 2) }}</p>
    <p><strong>Grant installment:</strong> {{ $release->grant_number }}</p>
    <p><strong>Released:</strong> {{ $release->released_at->format('F j, Y g:i A') }}</p>
</body>
</html>
