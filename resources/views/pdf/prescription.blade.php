<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        /* Base Styles */
        body { 
            font-family: DejaVu Sans, Arial, sans-serif; 
            margin: 0;
            padding: 0;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        /* Layout */
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header Section */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            border-bottom: 2px solid #2c5aa0;
            padding-bottom: 15px;
        }
        
        .practice-info {
            flex: 1;
        }
        
        .practice-logo {
            text-align: right;
        }
        
        .practice-logo img {
            max-height: 80px;
            max-width: 200px;
        }
        
        .practice-name {
            font-size: 18px;
            font-weight: bold;
            color: #2c5aa0;
            margin-bottom: 5px;
        }
        
        .practice-address {
            font-size: 11px;
            color: #666;
            line-height: 1.3;
        }
        
        /* Patient Information */
        .patient-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
            border-left: 4px solid #2c5aa0;
        }
        
        .patient-info h2 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #2c5aa0;
        }
        
        .patient-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        
        .patient-detail {
            margin-bottom: 5px;
        }
        
        .patient-detail strong {
            color: #555;
        }
        
        /* Prescription Section */
        .prescription-section {
            margin: 25px 0;
        }
        
        .prescription-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        
        .rx-icon {
            margin-right: 10px;
        }
        
        .prescription-header h2 {
            margin: 0;
            font-size: 16px;
            color: #2c5aa0;
        }
        
        .prescription-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .prescription-item {
            margin-bottom: 12px;
        }
        
        .prescription-label {
            font-weight: bold;
            color: #555;
            display: block;
            margin-bottom: 3px;
            font-size: 11px;
        }
        
        .prescription-value {
            font-size: 13px;
            padding: 8px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-height: 35px;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        /* Medical Information */
        .medical-info {
            margin: 25px 0;
        }
        
        .info-section {
            margin-bottom: 20px;
        }
        
        .info-section h3 {
            font-size: 13px;
            color: #2c5aa0;
            margin-bottom: 8px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        
        .allergy-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .allergy-list li {
            padding: 5px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .allergy-list li:last-child {
            border-bottom: none;
        }
        
        /* Signature Section */
        .signature-area {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #2c5aa0;
        }
        
        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #333;
            width: 300px;
        }
        
        .provider-info {
            margin-top: 5px;
            font-weight: bold;
        }
        
        .license-info {
            font-size: 10px;
            color: #666;
            margin-top: 3px;
        }
        
        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #888;
            text-align: center;
        }
        
        /* Utility Classes */
        .text-center {
            text-align: center;
        }
        
        .mb-0 {
            margin-bottom: 0;
        }
        
        .mt-20 {
            margin-top: 20px;
        }
        
        /* Print Styles */
        @media print {
            body {
                margin: 0;
                padding: 10px;
                font-size: 11px;
            }

            .container {
                padding: 0;
                max-width: none;
            }

            .header {
                margin-bottom: 20px;
            }

            .patient-info {
                background: transparent !important;
                border: 1px solid #000 !important;
                padding: 10px;
            }

            .prescription-section {
                margin: 20px 0;
            }

            .prescription-details {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }

            .prescription-value {
                font-size: 12px;
                padding: 6px;
                min-height: 30px;
            }

            .signature-area {
                margin-top: 40px;
                page-break-inside: avoid;
            }

            .footer {
                margin-top: 30px;
                font-size: 9px;
            }

            /* Ensure no page breaks in important sections */
            .patient-info,
            .prescription-details,
            .signature-area {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header with Practice Info -->
        <div class="header">
            <div class="practice-info">
                <div class="practice-name">{{ $practiceName ?? 'Medical Practice' }}</div>
                <div class="practice-address">{!! $practiceInfo ?? 'Address not available' !!}</div>
                @if(isset($website) && $website)
                <div class="practice-website">Website: {{ $website }}</div>
                @endif
            </div>
            @if(isset($practiceLogo) && $practiceLogo)
            <div class="practice-logo">
                {!! $practiceLogo !!}
            </div>
            @endif
        </div>

        <!-- Patient Information -->
        <div class="patient-info">
            <h2>PATIENT INFORMATION</h2>
            <div class="patient-details">
                <div class="patient-detail">
                    <strong>Name:</strong> {{ $patientInfo->firstname ?? '' }} {{ $patientInfo->lastname ?? '' }}
                </div>
                <div class="patient-detail">
                    <strong>Date of Birth:</strong> {{ $dob ?? 'N/A' }}
                </div>
                <div class="patient-detail">
                    <strong>Prescription Date:</strong> {{ $rx_date ?? 'N/A' }}
                </div>
                <div class="patient-detail">
                    <strong>Patient ID:</strong> {{ $patientInfo->id ?? 'N/A' }}
                </div>
            </div>
        </div>

        <!-- Prescription Details -->
        <div class="prescription-section">
            <div class="prescription-header">
                <div class="rx-icon">
                    {!! $rxicon ?? '℞' !!}
                </div>
                <h2>PRESCRIPTION</h2>
            </div>
            
            <div class="prescription-details">
                <div class="prescription-item">
                    <span class="prescription-label">Medication</span>
                    <div class="prescription-value">{{ $prescription->medication ?? 'Not specified' }}</div>
                </div>
                
                <div class="prescription-item">
                    <span class="prescription-label">Dosage</span>
                    <div class="prescription-value">
                        {{ $prescription->dosage ?? '' }} {{ $prescription->dosage_unit ?? '' }}
                    </div>
                </div>
                
                <div class="prescription-item">
                    <span class="prescription-label">Frequency</span>
                    <div class="prescription-value">{{ $prescription->frequency ?? 'As directed' }}</div>
                </div>
                
                <div class="prescription-item">
                    <span class="prescription-label">Route</span>
                    <div class="prescription-value">{{ $prescription->route ?? 'Oral' }}</div>
                </div>
                
                <div class="prescription-item">
                    <span class="prescription-label">Quantity</span>
                    <div class="prescription-value">
                        {{ $prescription->quantity ?? '0' }} ({{ $quantity_words ?? 'zero' }})
                    </div>
                </div>
                
                <div class="prescription-item">
                    <span class="prescription-label">Refills</span>
                    <div class="prescription-value">
                        {{ $prescription->refill ?? '0' }} ({{ $refill_words ?? 'zero' }})
                    </div>
                </div>
                
                <div class="prescription-item full-width">
                    <span class="prescription-label">Instructions (SIG)</span>
                    <div class="prescription-value" style="min-height: 60px;">
                        {{ $prescription->instructions ?? $prescription->sig ?? 'Take as directed by physician.' }}
                    </div>
                </div>
                
                @if(isset($prescription->reason) && $prescription->reason)
                <div class="prescription-item full-width">
                    <span class="prescription-label">Reason for Medication</span>
                    <div class="prescription-value">{{ $prescription->reason }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Medical Information -->
        <div class="medical-info">
            <!-- Allergies -->
            <div class="info-section">
                <h3>ALLERGIES & SENSITIVITIES</h3>
                <div class="prescription-value">
                    {!! $allergyInfo ?? 'No known allergies reported.' !!}
                </div>
            </div>

            <!-- Insurance Information -->
            @if(isset($insuranceInfo) && $insuranceInfo)
            <div class="info-section">
                <h3>INSURANCE INFORMATION</h3>
                <div class="prescription-value">
                    {!! $insuranceInfo !!}
                </div>
            </div>
            @endif
        </div>

        <!-- Provider Signature -->
        <div class="signature-area">
            <div class="signature-line"></div>
            <div class="provider-info">
                {!! $signature ?? 'Provider Signature' !!}
            </div>
            @if(isset($prescription->dea) && $prescription->dea)
            <div class="license-info">
                {{ $prescription->dea }}
            </div>
            @endif
            <div class="license-info">
                Prescription generated electronically - Valid without handwritten signature
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p class="mb-0">This prescription was generated by AKRA TELEHEALTH on {{ date('m/d/Y g:i A') }}</p>
            <p class="mb-0">CONFIDENTIALITY NOTICE: This document contains protected health information. Unauthorized disclosure is prohibited.</p>
        </div>
    </div>
</body>
</html>