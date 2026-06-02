<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Maintenance | Batangas State University</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bsu-red: #d90000;
            --bsu-dark: #1f2937;
            --bg-gray: #f9fafb;
        }
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-gray);
            color: var(--bsu-dark);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .container {
            background-color: white;
            padding: 3.5rem 2.5rem;
            border-radius: 1.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            max-width: 550px;
            width: 100%;
            text-align: center;
            border-bottom: 6px solid var(--bsu-red);
            position: relative;
            overflow: hidden;
        }
        .container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--bsu-red), #ff4d4d);
        }
        .logo {
            width: 90px;
            height: auto;
            margin-bottom: 2rem;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
        }
        h1 {
            color: var(--bsu-dark);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            line-height: 1.2;
            letter-spacing: -0.02em;
        }
        p {
            color: #4b5563;
            font-size: 1.05rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .illustration {
            width: 100%;
            max-width: 250px;
            margin: 0 auto 2rem;
            display: block;
        }
        .spinner {
            border: 3px solid #e5e7eb;
            border-top: 3px solid var(--bsu-red);
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .footer {
            margin-top: 2.5rem;
            font-size: 0.85rem;
            color: #9ca3af;
            font-weight: 300;
        }
        .highlight {
            color: var(--bsu-red);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Optional: Replace with actual SVG illustration -->
        <svg class="illustration" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300" fill="none">
            <rect width="400" height="300" fill="none"/>
            <circle cx="200" cy="140" r="60" fill="#FEE2E2" opacity="0.5"/>
            <path d="M160 140 H240 M200 100 V180" stroke="#EF4444" stroke-width="2" stroke-linecap="round" stroke-dasharray="4 4"/>
            <path d="M170 190 L200 220 L230 190" stroke="#F87171" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M180 80 L220 80 L200 50 Z" fill="#EF4444"/>
            <rect x="150" y="220" width="100" height="10" rx="5" fill="#E5E7EB"/>
            <circle cx="200" cy="140" r="50" stroke="#DC2626" stroke-width="4"/>
            <path d="M185 140 L200 155 L225 125" stroke="#DC2626" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>

        <img src="images/lugo.png" alt="BSU Logo" class="logo" onerror="this.style.display='none'">
        
        <h1>System <span class="highlight">Maintenance</span></h1>
        
        <p>
            We are currently undergoing a minor system remodel.
            <br>
            This might affect user data, but we will do everything to keep the data preserved.
            <br><br>
            It might take a long time to open the system again.
        </p>

        <div class="spinner"></div>
    </div>

    <div class="footer">
        &copy; <?php echo date('Y'); ?> Batangas State University.<br>The National Engineering University.
    </div>
</body>
</html>
