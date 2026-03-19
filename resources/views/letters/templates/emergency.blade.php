<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12pt; line-height: 1.6; margin: 0; padding: 40px; color: #333; }
        .header { text-align: center; border-bottom: 3px solid #e74c3c; padding-bottom: 20px; margin-bottom: 30px; }
        .hospital-name { font-size: 18pt; font-weight: bold; color: #2c3e50; margin-bottom: 5px; }
        .hospital-address { font-size: 10pt; color: #666; }
        .letter-type { text-align: center; font-size: 18pt; font-weight: bold; color: #e74c3c; margin-bottom: 30px; text-transform: uppercase; letter-spacing: 3px; border: 3px solid #e74c3c; padding: 10px; display: inline-block; }
        .date { text-align: right; margin-bottom: 20px; }
        .patient-info { background-color: #fdf2f2; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #e74c3c; }
        .patient-info-row { display: flex; margin-bottom: 5px; }
        .patient-info-label { font-weight: bold; width: 120px; }
        .body-content { text-align: justify; margin-bottom: 40px; white-space: pre-wrap; }
        .emergency-box { background-color: #fdf2f2; border: 3px solid #e74c3c; padding: 20px; border-radius: 5px; margin: 20px 0; text-align: center; }
        .signature-section { margin-top: 50px; }
        .signature-line { width: 250px; border-top: 2px solid #e74c3c; margin-top: 40px; padding-top: 5px; }
        .doctor-info { margin-top: 5px; font-size: 10pt; }
        .footer { border-top: 1px solid #ddd; padding-top: 20px; margin-top: 40px; }
        .urgency-high { background-color: #e74c3c; color: white; padding: 5px 15px; border-radius: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="hospital-name">{{ $hospital['name'] ?? 'Healthcare Medical Center' }}</div>
        <div class="hospital-address">{{ $hospital['address'] ?? '123 Medical Avenue, Healthcare City' }}<br>
            Tel: {{ $hospital['phone'] ?? '+1 (555) 123-4567' }} | Email: {{ $hospital['email'] ?? 'info@healthcare.com' }}</div>
    </div>
    <div style="text-align: center;">
        <span class="urgency-high">⚠️ URGENT</span><br><br>
        <div class="letter-type">EMERGENCY LETTER</div>
    </div>
    <div class="date"><strong>Date:</strong> {{ $date }}<br><strong>Time:</strong> {{ date('H:i') }}</div>
    @if(isset($found) && $found)
    <div class="patient-info">
        <div class="patient-info-row"><span class="patient-info-label">Patient:</span><span>{{ $patient['patient']['name'] ?? 'N/A' }}</span></div>
        <div class="patient-info-row"><span class="patient-info-label">DOB:</span><span>{{ $patient['patient']['dob'] ?? 'N/A' }}</span></div>
        <div class="patient-info-row"><span class="patient-info-label">Gender:</span><span>{{ $patient['patient']['gender'] ?? 'N/A' }}</span></div>
        @if(($patient['patient']['phone'] ?? ''))<div class="patient-info-row"><span class="patient-info-label">Phone:</span><span>{{ $patient['patient']['phone'] }}</span></div>@endif
    </div>
    @endif
    <div><div><strong>To:</strong> {{ $to }}</div></div>
    <div class="subject"><strong>Re:</strong> {{ $subject }}</div>
    <div class="emergency-box"><strong style="color: #e74c3c;">🚨 REQUIRES IMMEDIATE ATTENTION</strong></div>
    <div class="body-content">{{ $body }}</div>
    <div class="signature-section"><div class="signature-line"></div><div class="doctor-info"><strong>Dr. {{ $doctor['name'] ?? 'N/A' }}</strong><br>{{ $doctor['specialty'] ?? 'General Practice' }}<br>@if(($doctor['phone'] ?? ''))Phone: {{ $doctor['phone'] }}<br>@endif @if(($doctor['registration_number'] ?? ''))Reg. No: {{ $doctor['registration_number'] }}<br>@endif</div></div>
    @if(isset($signature) && $signature)<div style="margin-top: 20px;"><img src="{{ $signature }}" style="max-width: 150px; max-height: 60px;" alt="Signature"></div>@endif
    <div class="footer"><div style="text-align: center; font-size: 9pt; color: #999;">This is an emergency document. Please attend to immediately.</div></div>
</body>
</html>
