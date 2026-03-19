<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 14px;
            line-height: 1.6;
        }
        .invoice-box {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
            background: #fff;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #4a6fa5;
        }
        .header-left h1 {
            color: #4a6fa5;
            font-size: 32px;
            margin: 0 0 10px 0;
        }
        .header-right {
            text-align: right;
        }
        .company-info {
            margin-bottom: 20px;
        }
        .company-info h2 {
            color: #4a6fa5;
            font-size: 20px;
            margin-bottom: 5px;
        }
        .company-info p {
            color: #666;
            font-size: 13px;
            margin: 2px 0;
        }
        .invoice-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .invoice-info table {
            width: 100%;
        }
        .invoice-info td {
            padding: 5px 10px;
        }
        .invoice-info td:first-child {
            color: #666;
            width: 150px;
        }
        .invoice-info td:last-child {
            font-weight: 600;
        }
        .bill-to {
            margin-bottom: 30px;
        }
        .bill-to h3 {
            color: #4a6fa5;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .bill-to p {
            margin: 3px 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #4a6fa5;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        .items-table tr:last-child td {
            border-bottom: none;
        }
        .items-table .text-right {
            text-align: right;
        }
        .totals {
            width: 300px;
            margin-left: auto;
        }
        .totals table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals td {
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
        }
        .totals td:first-child {
            color: #666;
        }
        .totals td:last-child {
            text-align: right;
            font-weight: 600;
        }
        .totals .total-row {
            background-color: #4a6fa5;
            color: white;
            font-size: 16px;
            font-weight: 700;
        }
        .totals .total-row td {
            border: none;
            padding: 15px;
        }
        .payment-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-top: 30px;
        }
        .payment-info h4 {
            color: #4a6fa5;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .payment-info p {
            margin: 5px 0;
            font-size: 13px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .footer p {
            margin: 5px 0;
        }
        .notes {
            background-color: #fff9e6;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin-top: 20px;
        }
        .notes h4 {
            color: #856404;
            font-size: 13px;
            margin-bottom: 8px;
        }
        .notes p {
            color: #856404;
            font-size: 13px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-overdue {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status-draft {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .status-cancelled {
            background-color: #d6d8d9;
            color: #1b1e21;
        }
        .page-break {
            page-break-before: always;
        }
        @media print {
            .invoice-box {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h1>INVOICE</h1>
                <div class="company-info">
                    <h2>AKRA Health</h2>
                    <p>Healthcare Management System</p>
                    @if(isset($hospital) && $hospital)
                    <p>{{ $hospital->name }}</p>
                    @endif
                </div>
            </div>
            <div class="header-right">
                <div class="invoice-info">
                    <table>
                        <tr>
                            <td>Invoice #:</td>
                            <td><strong>{{ $invoice->invoice_number }}</strong></td>
                        </tr>
                        <tr>
                            <td>Date:</td>
                            <td>{{ $invoice->created_at->format('F j, Y') }}</td>
                        </tr>
                        @if($invoice->due_date)
                        <tr>
                            <td>Due Date:</td>
                            <td>{{ $invoice->due_date->format('F j, Y') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td>Status:</td>
                            <td>
                                @switch($invoice->status)
                                    @case('paid')
                                        <span class="status-badge status-paid">PAID</span>
                                        @break
                                    @case('partial')
                                        <span class="status-badge status-pending">PARTIAL</span>
                                        @break
                                    @case('overdue')
                                        <span class="status-badge status-overdue">OVERDUE</span>
                                        @break
                                    @case('cancelled')
                                        <span class="status-badge status-cancelled">CANCELLED</span>
                                        @break
                                    @case('draft')
                                        <span class="status-badge status-draft">DRAFT</span>
                                        @break
                                    @default
                                        <span class="status-badge status-pending">{{ strtoupper($invoice->status) }}</span>
                                @endswitch
                            </td>
                        </tr>
                        @if($invoice->razorpay_payment_id)
                        <tr>
                            <td>Payment ID:</td>
                            <td>{{ $invoice->razorpay_payment_id }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Bill To -->
        <div class="bill-to">
            <h3>Bill To:</h3>
            @if(isset($patient) && $patient)
                <p><strong>{{ $patient->name ?? ($patientUser->name ?? 'N/A') }}</strong></p>
                @if(isset($patientUser) && $patientUser)
                    <p>{{ $patientUser->email ?? '' }}</p>
                    @if($patientUser->phone)
                        <p>{{ $patientUser->phone }}</p>
                    @endif
                @endif
                @if($patient->address)
                    <p>{{ $patient->address->address_line_1 }}</p>
                    <p>{{ $patient->address->city }}, {{ $patient->address->state }} {{ $patient->address->zip }}</p>
                @endif
            @elseif(isset($user) && $user)
                <p><strong>{{ $user->name }}</strong></p>
                <p>{{ $user->email }}</p>
                @if($user->phone)
                    <p>{{ $user->phone }}</p>
                @endif
            @else
                <p><strong>{{ $invoice->customer_details['name'] ?? 'Customer' }}</strong></p>
                @if(isset($invoice->customer_details['email']))
                    <p>{{ $invoice->customer_details['email'] }}</p>
                @endif
            @endif

            @if(isset($doctor) && $doctor)
            <p style="margin-top: 10px; color: #666;">
                <small>Doctor: {{ $doctor->user->name ?? 'Dr. ' . $doctor->first_name . ' ' . $doctor->last_name }}</small>
            </p>
            @endif
        </div>

        <!-- Items -->
        @if($invoice->items && count($invoice->items) > 0)
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>
                        <strong>{{ $item['name'] ?? 'Item' }}</strong>
                        @if(isset($item['description']) && $item['description'])
                            <br><small style="color: #666;">{{ $item['description'] }}</small>
                        @endif
                    </td>
                    <td class="text-right">{{ $item['quantity'] ?? 1 }}</td>
                    <td class="text-right">{{ $invoice->currency }} {{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                    <td class="text-right">{{ $invoice->currency }} {{ number_format(($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0), 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Totals -->
        <div class="totals">
            <table>
                <tr>
                    <td>Subtotal:</td>
                    <td>{{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}</td>
                </tr>
                @if($invoice->tax_amount > 0)
                <tr>
                    <td>Tax:</td>
                    <td>{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 2) }}</td>
                </tr>
                @endif
                @if($invoice->discount_amount > 0)
                <tr>
                    <td>Discount:</td>
                    <td>-{{ $invoice->currency }} {{ number_format($invoice->discount_amount, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>Total:</td>
                    <td>{{ $invoice->currency }} {{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
                @if(isset($transactions) && $transactions->count() > 0)
                    @php $paidAmount = $transactions->where('status', 'completed')->sum('amount') @endphp
                    @if($paidAmount > 0)
                    <tr>
                        <td>Paid:</td>
                        <td style="color: #28a745;">-{{ $invoice->currency }} {{ number_format($paidAmount, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td>Balance Due:</td>
                        <td>{{ $invoice->currency }} {{ number_format(max(0, $invoice->total_amount - $paidAmount), 2) }}</td>
                    </tr>
                    @endif
                @endif
            </table>
        </div>

        <!-- Payment Info -->
        @if($invoice->razorpay_payment_id)
        <div class="payment-info">
            <h4>Payment Information</h4>
            <p><strong>Payment ID:</strong> {{ $invoice->razorpay_payment_id }}</p>
            @if($invoice->payment_method)
                <p><strong>Payment Method:</strong> {{ ucfirst($invoice->payment_method) }}</p>
            @endif
            @if($invoice->paid_at)
                <p><strong>Paid On:</strong> {{ $invoice->paid_at->format('F j, Y h:i A') }}</p>
            @endif
        </div>
        @endif

        <!-- Notes -->
        @if($invoice->notes)
        <div class="notes">
            <h4>Notes</h4>
            <p>{{ $invoice->notes }}</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p><strong>Thank you for your business!</strong></p>
            @if($invoice->terms_conditions)
                <p><strong>Terms & Conditions:</strong> {{ $invoice->terms_conditions }}</p>
            @endif
            <p style="margin-top: 15px; color: #999; font-size: 11px;">
                Generated by AKRA Health | {{ $invoice->created_at->format('F j, Y h:i A') }}
            </p>
        </div>
    </div>
</body>
</html>

