<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            margin: 0;
            padding: 60px 50px 40px 50px;
            color: #1a1a1a;
        }
        .header {
            border-bottom: 2px solid #0066cc;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .hospital-info {
            text-align: left;
        }
        .hospital-name {
            font-size: 16pt;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 3px;
        }
        .hospital-address {
            font-size: 9pt;
            color: #666;
            line-height: 1.4;
        }
        .letter-meta {
            text-align: right;
        }
        .date {
            font-size: 10pt;
            color: #1a1a1a;
            margin-bottom: 5px;
        }
        .ref-no {
            font-size: 9pt;
            color: #666;
        }
        .letter-type-badge {
            display: inline-block;
            background-color: #0066cc;
            color: white;
            padding: 5px 15px;
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 25px;
            border-radius: 3px;
        }
        .patient-info-box {
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
            padding: 12px 15px;
            margin-bottom: 25px;
            border-radius: 4px;
        }
        .patient-info-title {
            font-size: 9pt;
            font-weight: bold;
            color: #0066cc;
            margin-bottom: 8px;
        }
        .patient-info-row {
            display: flex;
            font-size: 9pt;
            margin-bottom: 4px;
        }
        .patient-info-label {
            font-weight: bold;
            color: #666;
            width: 100px;
            flex-shrink: 0;
        }
        .patient-info-value {
            color: #1a1a1a;
        }
        .recipient-section {
            margin-bottom: 20px;
        }
        .recipient-label {
            font-size: 9pt;
            color: #666;
            margin-bottom: 3px;
        }
        .recipient-name {
            font-size: 11pt;
            font-weight: bold;
            color: #1a1a1a;
        }
        .referral-details {
            background-color: #e8f4fc;
            border: 1px solid #0066cc;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 4px;
        }
        .referral-title {
            font-size: 10pt;
            font-weight: bold;
            color: #0066cc;
            margin-bottom: 10px;
        }
        .referral-row {
            display: flex;
            font-size: 9pt;
            margin-bottom: 5px;
        }
        .referral-label {
            font-weight: bold;
            color: #333;
            width: 120px;
            flex-shrink: 0;
        }
        .referral-value {
            color: #1a1a1a;
        }
        .urgency-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 8pt;
            font-weight: bold;
            margin-left: 10px;
        }
        .urgency-routine {
            background-color: #27ae60;
            color: white;
        }
        .urgency-urgent {
            background-color: #f39c12;
            color: white;
        }
        .urgency-emergency {
            background-color: #e74c3c;
            color: white;
        }
        .subject-section {
            margin-bottom: 20px;
        }
        .subject-label {
            font-size: 9pt;
            color: #666;
            margin-bottom: 3px;
        }
        .subject-text {
            font-size: 11pt;
            font-weight: bold;
            color: #1a1a1a;
        }
        .salutation {
            margin-bottom: 20px;
            font-size: 11pt;
        }
        .body-content {
            text-align: justify;
            margin-bottom: 30px;
            white-space: pre-wrap;
            line-height: 1.7;
        }
        .closing {
            margin-bottom: 5px;
            font-size: 11pt;
        }
        .signature-section {
            margin-top: 40px;
        }
        .signature-block {
            margin-bottom: 5px;
        }
        .signature-name {
            font-size: 11pt;
            font-weight: bold;
            color: #1a1a1a;
        }
        .signature-title {
            font-size: 9pt;
            color: #666;
        }
        .signature-credentials {
            font-size: 8pt;
            color: #999;
            margin-top: 3px;
        }
        .signature-contact {
            font-size: 8pt;
            color: #666;
            margin-top: 3px;
        }
        .qr-section {
            position: fixed;
            bottom: 80px;
            right: 50px;
            text-align: center;
        }
        .qr-code {
            width: 80px;
            height: 80px;
        }
        .qr-label {
            font-size: 6pt;
            color: #999;
            margin-top: 3px;
        }
        .footer {
            position: fixed;
            bottom: 20px;
            left: 50px;
            right: 50px;
            border-top: 1px solid #e0e0e0;
            padding-top: 10px;
            text-align: center;
        }
        .footer-text {
            font-size: 7pt;
            color: #999;
            line-height: 1.4;
        }
        .footer-contact {
            margin-top: 5px;
            font-size: 8pt;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="hospital-info">
                <div class="hospital-name">{{ $hospital['name'] ?? 'Healthcare Medical Center' }}</div>
                <div class="hospital-address">
                    {{ $hospital['address'] ?? '123 Medical Avenue' }}<br>
                    {{ $hospital['city'] ?? 'Healthcare City' }} | Tel: {{ $hospital['phone'] ?? '+1 (555) 123-4567' }}
                </div>
            </div>
            <div class="letter-meta">
                <div class="date">{{ $date }}</div>
                <div class="ref-no">Ref: {{ $letter_type ?? 'Referral Letter' }}</div>
            </div>
        </div>
    </div>

    <div style="text-align: center;">
        <div class="letter-type-badge">REFERRAL LETTER</div>
    </div>

    @if(isset($found) && $found)
    <div class="patient-info-box">
        <div class="patient-info-title">PATIENT INFORMATION</div>
        <div class="patient-info-row">
            <span class="patient-info-label">Name:</span>
            <span class="patient-info-value">{{ $patient['patient']['name'] ?? 'N/A' }}</span>
        </div>
        <div class="patient-info-row">
            <span class="patient-info-label">Date of Birth:</span>
            <span class="patient-info-value">{{ $patient['patient']['dob'] ?? 'N/A' }}</span>
        </div>
        <div class="patient-info-row">
            <span class="patient-info-label">Gender:</span>
            <span class="patient-info-value">{{ $patient['patient']['gender'] ?? 'N/A' }}</span>
        </div>
        @if(($patient['patient']['phone'] ?? ''))
        <div class="patient-info-row">
            <span class="patient-info-label">Phone:</span>
            <span class="patient-info-value">{{ $patient['patient']['phone'] }}</span>
        </div>
        @endif
    </div>
    @endif

    <div class="referral-details">
        <div class="referral-title">REFERRAL DETAILS</div>
        <div class="referral-row">
            <span class="referral-label">Patient Name:</span>
            <span class="referral-value">{{ $patient['patient']['name'] ?? 'N/A' }}</span>
        </div>
        <div class="referral-row">
            <span class="referral-label">Date of Birth:</span>
            <span class="referral-value">{{ $patient['patient']['dob'] ?? 'N/A' }}</span>
        </div>
        <div class="referral-row">
            <span class="referral-label">Referring Dr:</span>
            <span class="referral-value">Dr. {{ $doctor['name'] ?? 'N/A' }}</span>
        </div>
        <div class="referral-row">
            <span class="referral-label">Specialty:</span>
            <span class="referral-value">{{ $doctor['specialty'] ?? 'General Practice' }}</span>
        </div>
        <div class="referral-row">
            <span class="referral-label">Urgency:</span>
            <span class="urgency-badge urgency-routine">ROUTINE</span>
        </div>
    </div>

    <div class="recipient-section">
        <div class="recipient-label">To,</div>
        <div class="recipient-name">{{ $to }}</div>
    </div>

    <div class="subject-section">
        <div class="subject-label">Subject</div>
        <div class="subject-text">{{ $subject }}</div>
    </div>

    <div class="salutation">Dear Dr. {{ $to }},</div>

    <div class="body-content">{{ $body }}</div>

    <div class="closing">Thank you for your cooperation.</div>

    <div class="signature-section">
        <div class="closing">Yours sincerely,</div>
        @if(isset($signature) && $signature)
        <div style="margin: 10px 0;">
            <img src="{{ $signature }}" style="max-width: 120px; max-height: 50px;" alt="Signature">
        </div>
        @endif
        <div class="signature-block">
            <div class="signature-name">Dr. {{ $doctor['name'] ?? 'N/A' }}</div>
            <div class="signature-title">{{ $doctor['specialty'] ?? 'General Practice' }}</div>
            @if(($doctor['qualification'] ?? ''))
            <div class="signature-credentials">{{ $doctor['qualification'] }}</div>
            @endif
            @if(($doctor['registration_number'] ?? ''))
            <div class="signature-credentials">Reg. No: {{ $doctor['registration_number'] }}</div>
            @endif
            <div class="signature-contact">
                Tel: {{ $doctor['phone'] ?? '' }} | Email: {{ $doctor['email'] ?? 'N/A' }}
            </div>
        </div>
    </div>

    @if(isset($qr_data) && $qr_data)
    <div class="qr-section">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={{ urlencode($qr_data) }}" alt="Verification QR" class="qr-code">
        <div class="qr-label">Scan to Verify</div>
    </div>
    @endif

    <div class="footer">
        <div class="footer-text">
            This referral is valid for 30 days from the date of issuance. For any queries, please contact the referring physician.
        </div>
        <div class="footer-contact">
            {{ $hospital['name'] ?? 'Healthcare Medical Center' }} | {{ $hospital['address'] ?? '123 Medical Avenue' }} | {{ $hospital['phone'] ?? '+1 (555) 123-4567' }}
        </div>
    </div>
</body>
</html>

