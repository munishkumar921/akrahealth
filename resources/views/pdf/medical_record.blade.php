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

        .mt-4 { margin-top: 4px; }
        .mt-8 { margin-top: 8px; }
        .mb-0 { margin-bottom: 0; }

        /* Encounter Table Styles */
        .encounter-section {
            margin-top: 20px;
            page-break-before: always;
        }

        .encounter-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 11px;
        }

        .encounter-table th,
        .encounter-table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
            text-align: left;
        }

        .encounter-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .pagination-info {
            text-align: center;
            margin-top: 15px;
            font-size: 11px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
        }

        .page-number {
            font-size: 12px;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Page Header --}}
        <div class="page-header">
            <div class="page-title">MEDICAL RECORD</div>
            <div class="page-number">{{ $pagination_info ?? '' }}</div>
        </div>

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

        {{-- Section 1: Patient Demographics & Insurance --}}
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

        {{-- Section 2: Active Issues --}}
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

        {{-- Section 3: Active Medications --}}
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

        {{-- Section 4: Immunizations --}}
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

        {{-- Section 5: Allergies --}}
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

        {{-- Section 6: Encounters (Paginated) --}}
        <div class="encounter-section">
            <div class="section-title">ENCOUNTER HISTORY</div>
            
            @if(count($encounters))
                <table class="encounter-table">
                    <thead>
                        <tr>
                            <th style="width: 15%;">Date</th>
                            <th style="width: 25%;">Provider</th>
                            <th style="width: 35%;">Chief Complaint</th>
                            <th style="width: 25%;">Diagnosis</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($encounters as $encounter)
                            <tr>
                                <td>{{ $encounter->encounter_date_of_service ?? 'N/A' }}</td>
                                <td>
                                    @if($encounter->doctor)
                                        {{ $encounter->doctor->full_name ?? 'N/A' }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $encounter->chief_complaint ?? 'N/A' }}</td>
                                <td>
                                    @if($encounter->assessment)
                                        {{ $encounter->assessment }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No encounter records found.</p>
            @endif
        </div>

        {{-- Pagination Info --}}
        <div class="pagination-info">
            {{ $pagination_info ?? '' }}
        </div>

        {{-- Printed by --}}
        <div class="section-block mt-8">
            Printed by {{ $printedBy }}.
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p class="mb-0">
                CONFIDENTIALITY NOTICE: The information contained in this document or facsimile transmission is intended for the recipient named above.
            </p>
            <p class="mb-0">
                If you are not the intended recipient or the intended recipient's agent, you are hereby notified that dissemination, copying, or distribution of the
                information contained in the transmission is prohibited. If you are not the intended recipient, please notify us immediately by return the documents to telephone and
                us by mail. Thank you.
            </p>
            <p class="mb-0">
                This document was generated by AKRA TELEHEALTH - Page {{ $page ?? 1 }}
            </p>
        </div>
    </div>
</body>
</html>

