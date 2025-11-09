<?php
/** @var string $otp */
/** @var int $expiry */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f8f8f8;
            padding: 20px;
            border-radius: 5px;
        }
        .otp-code {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            text-align: center;
            padding: 15px;
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome to EsteticaNow</h2>
        <p>Dear User,</p>
        <p>Please use the following One-Time Password (OTP) to verify your email address:</p>
        <div class="otp-code"><?= htmlspecialchars($otp) ?></div>
        <p>This OTP is valid for <?= $expiry ?> minutes only.</p>
        <p>If you didn't request this OTP, please ignore this email or contact our support team.</p>
        <p>Best regards,<br>The EsteticaNow Team</p>
    </div>
    <div class="footer">
        <p>This is an automated message, please do not reply directly to this email.</p>
        <p><a href="mailto:support@esteticanow.com">Contact Support</a> | 
        <a href="mailto:unsubscribe@esteticanow.com?subject=Unsubscribe">Unsubscribe</a></p>
    </div>
</body>
</html>