<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Letter</title>
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
            border-bottom: 2px solid #0066cc;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .content {
            margin-bottom: 20px;
        }
        .footer {
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $subject }}</h2>
    </div>
    
    <div class="content">
        <p>Dear Sir/Madam,</p>
        
        <p>{!! nl2br(e($body)) !!}</p>
        
        <p>Please find attached the medical letter for your reference.</p>
        
        <p>Should you have any questions, please do not hesitate to contact us.</p>
    </div>
    
    <div class="footer">
        <p>Sincerely,</p>
        <p><strong>{{ $doctorName }}</strong></p>
        <p>This email and any attachments are confidential and intended solely for the use of the individual or entity to whom they are addressed.</p>
    </div>
</body>
</html>

