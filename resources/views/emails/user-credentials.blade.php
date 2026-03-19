<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your Account Credentials - AkraHealth</title>
    <style>
        /* Reset for email clients */
        body,
        table,
        td,
        a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table,
        td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            -webkit-font-smoothing: antialiased;
        }

        .header {
            background: linear-gradient(135deg, #09ACFF 0%, #09ACFF 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }

        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }

        .credentials-box {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .credential-item {
            margin-bottom: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }

        .button {
            display: inline-block;
            background: #09ACFF;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }

        .security-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }

        /* Mobile responsiveness */
        @media only screen and (max-width: 600px) {
            body {
                padding: 10px;
            }

            .header,
            .content {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Welcome to AkraHealth!</h1>
        <p>Your Telehealth Partner</p>
    </div>

    <div class="content">
        <h2>Hello, {{ $data['name'] }}!</h2>

        <p>Your account has been successfully created. You can now access our telehealth platform using the credentials
            below:</p>

        <div class="credentials-box">
            <h3>Your Login Credentials</h3>

            <div class="credential-item">
                <strong>Email Address:</strong><br>
                {{ $data['email'] }}
            </div>

            <div class="credential-item">
                <strong>Temporary Password:</strong><br>
                <span style="font-family: monospace; font-size: 16px; letter-spacing: 1px;">
                    {{ $data['password'] }}
                </span>
            </div>
        </div>

        <div style="text-align: center;">
            <a href="{{ url('/login') }}" class="button">Login to Your Account</a>
        </div>

        <div class="security-notice">
            <strong>🔒 Security Recommendation:</strong><br>
            For your security, please change your password after your first login. Do not share your credentials with
            anyone.
        </div>

        <p><strong>Getting Started:</strong></p>
        <ul>
            <li>Log in to your account using the credentials above</li>
            <li>Complete your profile information</li>
            <li>Explore our telehealth services</li>
            <li>Schedule your first appointment</li>
        </ul>

        <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>

        <p>Best regards,<br>
            <strong>The AkraHealth Team</strong>
        </p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} AkraHealth. All rights reserved.</p>
        <p>
            Contact Support: support@akrahealth.com<br>
            Phone: +91 6381250184
        </p>
    </div>
</body>

</html>
