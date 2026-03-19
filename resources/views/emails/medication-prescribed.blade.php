<!DOCTYPE html>
<html>
<head>
    <title>New Medication Prescribed</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .button { display: inline-block; padding: 10px 20px; background: #007bff; 
                 color: white; text-decoration: none; border-radius: 5px; }
        .footer { margin-top: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h2>New Medication Prescribed</h2>
        
        <p>Hello,</p>
        
        <p>{{ $message }}</p>
        
        <p>You have a new medication prescribed to you. For more details, click the button below:</p>
        
        <a href="{{ $link }}" class="button">View Medication Details</a>
        
        <div class="footer">
            <p>If you're having trouble clicking the button, copy and paste the URL below into your web browser:</p>
            <p>{{ $link }}</p>
        </div>
    </div>
</body>
</html>