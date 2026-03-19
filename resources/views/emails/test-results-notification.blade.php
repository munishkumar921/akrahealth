<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Test Results Available</title>
    <style>
        /* Reset styles */
        body, html { margin: 0; padding: 0; width: 100%; height: 100%; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; font-size: 16px; line-height: 1.6; color: #333333; background-color: #f4f6f8; }
        
        /* Container */
        .email-wrapper { width: 100%; background-color: #f4f6f8; padding: 40px 20px; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); }
        
        /* Header */
        .email-header { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); padding: 40px 30px; text-align: center; }
        .email-header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: 600; }
        .email-header .icon { font-size: 48px; margin-bottom: 15px; }
        
        /* Content */
        .email-content { padding: 40px 30px; }
        .greeting { font-size: 20px; font-weight: 600; color: #1e293b; margin-bottom: 20px; }
        .message-box { background-color: #f8fafc; border-left: 4px solid #2563eb; padding: 20px; border-radius: 0 8px 8px 0; margin: 25px 0; }
        .message-text { color: #475569; font-size: 15px; }
        
        /* CTA Button */
        .cta-container { text-align: center; margin: 35px 0; }
        .cta-button { display: inline-block; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 16px; }
        .cta-button:hover { background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%); }
        
        /* Info Box */
        .info-box { background-color: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 20px; margin: 25px 0; }
        .info-box-title { color: #0369a1; font-weight: 600; margin-bottom: 8px; }
        .info-box-text { color: #0c4a6e; font-size: 14px; }
        
        /* Footer */
        .email-footer { background-color: #f8fafc; padding: 30px; text-align: center; border-top: 1px solid #e2e8f0; }
        .footer-text { color: #64748b; font-size: 13px; margin: 0 0 10px 0; }
        .footer-link { color: #2563eb; text-decoration: none; }
        
        /* Divider */
        .divider { height: 1px; background-color: #e2e8f0; margin: 25px 0; }
        
        /* Responsive */
        @media only screen and (max-width: 640px) {
            .email-wrapper { padding: 20px 15px; }
            .email-container { border-radius: 8px; }
            .email-header { padding: 30px 20px; }
            .email-content { padding: 30px 20px; }
            .email-footer { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="email-header">
                <div class="icon">🩺</div>
                <h1>Your Test Results Are Available</h1>
            </div>
            
            <!-- Content -->
            <div class="email-content">
                <div class="greeting">Hello,</div>
                
                <p>You have new test results available from <strong>{{ $displayname }}</strong>.</p>
                
                <!-- Message based on portal access -->
                @if ($portal == false)
                    <div class="info-box">
                        <div class="info-box-title">📋 How to View Your Results</div>
                        <div class="info-box-text">
                            Reply to this email at <a href="mailto:{{ $email }}" style="color: #0369a1;">{{ $email }}</a> to create a secure account. 
                            After establishing your account, please visit <a href="{{ $patient_portal }}" style="color: #0369a1;">{{ $patient_portal }}</a> to view your results.<br><br>
                            <em>Only authorized users will be able to access the results.</em>
                        </div>
                    </div>
                @else
                    <div class="cta-container">
                        <a href="{{ $patient_portal }}" class="cta-button">View Your Test Results</a>
                    </div>
                    <p style="text-align: center; color: #64748b; font-size: 14px;">
                        Please log in to your patient portal to view your complete test results.
                    </p>
                @endif
                
                <div class="divider"></div>
                
                <p style="color: #64748b; font-size: 14px;">
                    If you have any questions about your test results, please contact your healthcare provider directly.
                </p>
            </div>
            
            <!-- Footer -->
            <div class="email-footer">
                <p class="footer-text">This is an automated notification from AKRA Health.</p>
                <p class="footer-text" style="margin: 0;">Please do not reply directly to this email.</p>
            </div>
        </div>
    </div>
</body>
</html>

