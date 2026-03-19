<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header-bar {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 10px;
        }

        .header-main {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }

        .hospital-logo {
            width: 45%;
        }

        .hospital-logo img {
            max-height: 60px;
            max-width: 200px;
        }

        .hospital-info {
            width: 55%;
            text-align: right;
            font-size: 11px;
        }

        .hospital-name {
            font-size: 14px;
            font-weight: bold;
        }

        .page-title {
            text-align: left;
            font-weight: bold;
            font-size: 13px;
            margin-top: 4px;
        }

        .section-title {
            font-weight: bold;
            margin-top: 12px;
        }

        .section-block {
            margin-top: 6px;
            margin-bottom: 6px;
        }

        .small-label {
            font-weight: bold;
        }

        .footer {
            margin-top: 25px;
            border-top: 1px solid #000;
            padding-top: 6px;
            font-size: 9px;
        }

        .mt-4 {
            margin-top: 4px;
        }

        .mt-8 {
            margin-top: 8px;
        }

        .mb-0 {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Patient header line --}}
        <div class="header-bar">
            {{ $patientHeaderLine }}
        </div>

        {{-- Hospital header with logo left, details right --}}
        <div class="header-main">
            <div class="hospital-logo">
                {!! $hospitalLogoHtml !!}
            </div>
            <div class="hospital-info">
                <div class="hospital-name">{{ $hospitalName }}</div>
                @if($hospitalAddress)
                    <div>{!! nl2br(e($hospitalAddress)) !!}</div>
                @endif
                @if($hospitalPhone)
                    <div>Phone: {{ $hospitalPhone }}</div>
                @endif
            </div>
        </div>

        {{-- Page intro --}}
        <div class="page-title">
            Continuity of Care Record
        </div>

        {{-- Demographics + insurance --}}
        <div class="section-block mt-8">
            <div class="section-title">
                PATIENT DEMOGRAPHICS GUARANTOR AND INSURANCE INFORMATION
            </div>
            <div class="mt-4">
                {{ $patientName }}<br>
                Date of Birth: {{ $patientDob }}<br>
                @if($patientAddressLine)
                    {{ $patientAddressLine }}<br>
                @endif
                @if($patientCityStateZip)
                    {{ $patientCityStateZip }}<br>
                @endif
                @if($patientPhone)
                    {{ $patientPhone }}<br>
                @endif
            </div>
            @if($insuranceSummary)
                <div class="mt-4">
                    {!! nl2br(e($insuranceSummary)) !!}
                </div>
            @endif
        </div>

        {{-- Active Issues --}}
        <div class="section-block">
            <span class="section-title">Active Issues:</span><br>
            @if(count($activeIssues))
                @foreach($activeIssues as $issue)
                    - {{ $issue }}<br>
                @endforeach
            @else
                None
            @endif
        </div>

        {{-- Active Medications --}}
        <div class="section-block">
            <span class="section-title">Active Medications:</span><br>
            @if(count($activeMedications))
                @foreach($activeMedications as $med)
                    - {{ $med }}<br>
                @endforeach
            @else
                None
            @endif
        </div>

        {{-- Immunizations --}}
        <div class="section-block">
            <span class="section-title">Immunizations:</span><br>
            @if(count($immunizations))
                @foreach($immunizations as $imm)
                    - {{ $imm }}<br>
                @endforeach
            @else
                None
            @endif
        </div>

        {{-- Allergies --}}
        <div class="section-block">
            <span class="section-title">Allergies:</span><br>
            @if(count($allergies))
                @foreach($allergies as $all)
                    - {{ $all }}<br>
                @endforeach
            @else
                None
            @endif
        </div>

        {{-- Printed by --}}
        <div class="section-block mt-8">
            Printed by {{ $printedBy }}.
        </div>

        {{-- Footer copied from sample --}}
        <div class="footer">
            <p class="mb-0">
                CONFIDENTIALITY NOTICE: The information contained in this document or facsimile transmission is intended for the recipient named above.
            </p>
            <p class="mb-0">
                If you are not the intended recipient or the intended recipient's agent, you are hereby notified that dissemination, copying, or distribution of the
                information contained in the transmission is prohibited. If you are not the intended recipient, please notify us immediately by telephone and
                return the documents to us by mail. Thank you.
            </p>
            <p class="mb-0">
                This document was generated by AKRA TELEHEALTH
            </p>
        </div>
    </div>
</body>
</html>

