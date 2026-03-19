<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Test Results Letter</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .hospital-info {
            margin-bottom: 20px;
        }
        .patient-info {
            margin-bottom: 20px;
        }
        .content {
            margin: 20px 0;
        }
        .signature {
            margin-top: 40px;
            text-align: left;
        }
        .date {
            text-align: right;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Test Results</h2>
    </div>

    <div class="date">
        {{ $date }}
    </div>

    <div class="hospital-info">
        {!! $hospitalAddress !!}
    </div>

    <div class="patient-info">
        <p><strong>Patient:</strong> {{ $patientName }}</p>
        <p><strong>Date of Birth:</strong> {{ $patientDOB }}</p>
    </div>

    <div class="content">
        {!! $body !!}
    </div>

    <div class="signature">
        <p>Sincerely,</p>
        <br><br>
        <p>{{ $displayname }}</p>
    </div>
</body>
</html>
