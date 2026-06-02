<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SFAO Account Created - BSU Scholarship System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #8B0000, #660000);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header h2 {
            margin: 10px 0 0 0;
            font-size: 18px;
            opacity: 0.9;
        }
        .content {
            background: white;
            padding: 30px;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 10px 10px;
        }
        .button {
            display: inline-block;
            background: #8B0000;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
        }
        .button:hover {
            background: #660000;
        }
        .details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎓 BSU Scholarship System</h1>
        <h2>SFAO Admin Account Created</h2>
    </div>
    
    <div class="content">
        <h3>Hello {{ $user->name }},</h3>
        
        <p>Your SFAO (Student Financial Assistance Office) Administrator account has been created successfully.</p>
        
        <div class="details">
            <h4>Account Details:</h4>
            <ul>
                <li><strong>Name:</strong> {{ $user->name }}</li>
                <li><strong>Email:</strong> {{ $user->email }}</li>
                <li><strong>Campus:</strong> {{ $user->campus->name }}</li>
                <li><strong>Role:</strong> SFAO Administrator</li>
            </ul>
        </div>
        
        <p>To complete your account setup, please verify your email address by clicking the button below:</p>
        
        <div style="text-align: center;">
            <a href="{{ \Illuminate\Support\Facades\URL::signedRoute('verification.verify', ['id' => $user->id, 'hash' => sha1($user->email)]) }}" class="button">
                Verify Email & Set Up Password
            </a>
        </div>
        
        <p><strong>Next Steps:</strong></p>
        <ul>
            <li>Click the button above to verify your email</li>
            <li>After verification, you'll be redirected to set up your password</li>
            <li>Once your password is set, you'll have full access to manage scholarship applications for {{ $user->campus->name }}</li>
        </ul>
        
        <p>If you did not expect this account creation or have any questions, please contact the system administrator.</p>
        
        <p>Best regards,<br>
        BSU Scholarship System Team</p>
    </div>
    
    <div class="footer">
        <p>This is an automated message from the BSU Scholarship System.</p>
    </div>
</body>
</html>
