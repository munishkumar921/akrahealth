<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $payload['subject'] ?? 'Akra Health Notification' }}</title>
    <style>
        body { margin: 0; padding: 0; background: #f3f8fc; font-family: Arial, sans-serif; color: #1f2937; }
        .wrapper { width: 100%; padding: 32px 16px; box-sizing: border-box; }
        .card { max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 12px 40px rgba(15, 23, 42, 0.08); }
        .header { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); color: #fff; padding: 28px 32px; }
        .header h1 { margin: 0; font-size: 24px; line-height: 1.2; }
        .content { padding: 32px; }
        .content p { margin: 0 0 16px; line-height: 1.65; }
        .details { margin: 20px 0 24px; padding: 18px 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; }
        .details ul { margin: 0; padding-left: 18px; }
        .details li { margin: 0 0 8px; line-height: 1.55; }
        .button-wrap { margin-top: 24px; }
        .button { display: inline-block; padding: 12px 22px; border-radius: 999px; background: #0ea5e9; color: #fff !important; text-decoration: none; font-weight: 600; }
        .footer { padding: 0 32px 28px; color: #64748b; font-size: 13px; line-height: 1.6; }
        @media only screen and (max-width: 600px) {
            .header, .content, .footer { padding-left: 20px; padding-right: 20px; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <h1>{{ $payload['headline'] ?? 'Akra Health Notification' }}</h1>
            </div>
            <div class="content">
                @if(!empty($payload['greeting']))
                    <p>{{ $payload['greeting'] }}</p>
                @endif

                <p>{{ $payload['message'] ?? '' }}</p>

                @if(!empty($payload['details']) && is_array($payload['details']))
                    <div class="details">
                        <ul>
                            @foreach($payload['details'] as $detail)
                                <li>{{ $detail }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(!empty($payload['action_url']) && !empty($payload['action_label']))
                    <div class="button-wrap">
                        <a href="{{ $payload['action_url'] }}" class="button">{{ $payload['action_label'] }}</a>
                    </div>
                @endif
            </div>
            <div class="footer">
                {{ $payload['footer'] ?? 'This is an automated email from Akra Health. Please do not reply directly to this message.' }}
            </div>
        </div>
    </div>
</body>
</html>
