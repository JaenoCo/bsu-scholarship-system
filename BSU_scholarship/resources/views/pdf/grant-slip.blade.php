<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Grant Slip</title>
    <style>
        body { padding: 40px; font-family: sans-serif; }
        .header { text-align: center; margin-bottom: 40px; }
        .logo { width: 80px; height: auto; display: block; margin: 0 auto; }
        .title { font-size: 24px; font-weight: bold; margin-top: 20px; text-transform: uppercase; }
        .subtitle { font-size: 14px; color: #666; }
        .content { margin-top: 40px; width: 100%; border-collapse: collapse; }
        .content td { padding: 10px; font-size: 14px; }
        .label { font-weight: bold; width: 150px; }
        .amount-box { margin-top: 30px; border: 2px solid #000; padding: 15px; text-align: center; font-size: 20px; font-weight: bold; width: 300px; margin-left: auto; margin-right: auto; }
        .footer { margin-top: 60px; font-size: 12px; color: #666; text-align: center; border-top: 1px solid #ccc; padding-top: 20px; }
        .signatures { margin-top: 80px; display: table; width: 100%; }
        .signature-box { display: table-cell; text-align: center; width: 50%; }
        .line { border-top: 1px solid #000; width: 80%; margin: 0 auto; margin-top: 40px; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">Batangas State University</h1>
        <p class="subtitle">The National Engineering University</p>
        <h2 style="margin-top: 30px;">GRANT RELEASE SLIP</h2>
    </div>

    <table class="content">
        <tr>
            <td class="label">Date:</td>
            <td>{{ \Carbon\Carbon::parse($details['release_date'])->format('F d, Y') }}</td>
        </tr>
        <tr>
            <td class="label">Scholarship:</td>
            <td>{{ $scholarship->scholarship_name }}</td>
        </tr>
        <tr>
            <td class="label">Scholar Name:</td>
            <td>{{ $scholar->user->name }}</td>
        </tr>
        <tr>
            <td class="label">Student ID/email:</td>
            <td>{{ $scholar->user->email }}</td>
        </tr>
        <tr>
            <td class="label">Description:</td>
            <td>Grant release for {{ $scholarship->scholarship_type }} scholarship program.</td>
        </tr>
    </table>

    <div class="amount-box">
        AMOUNT: ₱{{ number_format($scholarship->grant_amount, 2) }}
    </div>

    <div class="signatures">
        <div class="signature-box">
            <div class="line"></div>
            <strong>Scholar's Signature</strong>
        </div>
        <div class="signature-box">
            <div class="line"></div>
            <strong>SFAO Officer</strong>
        </div>
    </div>

    <div class="footer">
        <p>This document serves as proof of grant release. Please keep for your records.</p>
        <p>&copy; {{ date('Y') }} Batangas State University Scholarship Office</p>
    </div>
</body>
</html>
