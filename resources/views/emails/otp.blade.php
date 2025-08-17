<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Your OTP Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f6f6f6;
            margin: 0;
            padding: 0;
        }

        .container {
            background: #ffffff;
            max-width: 500px;
            margin: 30px auto;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .otp-code {
            background: #f5f5f5;
            padding: 15px;
            text-align: center;
            font-size: 24px;
            letter-spacing: 5px;
            border-radius: 6px;
            border: 1px dashed #333;
            margin: 20px 0;
        }

        p {
            color: #555;
        }

        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #888;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Your One-Time Password (OTP)</h2>

        <p>Dear User,</p>

        <p>Use the OTP below to complete your authentication process. This OTP is valid for the next 5 minutes:</p>

        <div class="otp-code">
            {{ $otp }}
        </div>

        <p>If you did not request this code, please ignore this email.</p>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>

</html>
