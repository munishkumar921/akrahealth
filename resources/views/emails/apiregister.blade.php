<!DOCTYPE html>
<html>
<head>
    <title>Provider Invitation</title>
</head>
<body>
    <h2>Provider Invitation</h2>
    <p>Dear {{ $provider_name }},</p>
    <p>You have been invited to access the personal electronic medical record of {{ $patient }}.</p>
    <p>Click the button below to register and access the records:</p>
    <a href="{{ $temp_url }}" style="background-color: #4CAF50; color: white; padding: 14px 25px; text-align: center; text-decoration: none; display: inline-block;">Register Now</a>
    <p>This invitation will expire in 3 days.</p>
    <p>Thanks,<br>{{ config('app.name') }}</p>
</body>
</html>