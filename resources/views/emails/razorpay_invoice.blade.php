<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 16px;
            line-height: 1.6;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .email-wrapper {
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #4a6fa5;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
        }
        .invoice-details {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .invoice-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-details td {
            padding: 8px 0;
        }
        .invoice-details td:first-child {
            color: #666;
            width: 40%;
        }
        .invoice-details td:last-child {
            font-weight: 600;
        }
        .amount {
            font-size: 28px;
            color: #4a6fa5;
            font-weight: 700;
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
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        .items-table tr:last-child td {
            border-bottom: none;
        }
        .total-row {
            background-color: #f8f9fa;
        }
        .total-row td {
            padding: 15px;
            font-weight: 700;
            font-size: 18px;
        }
        .btn {
            display: inline-block;
            background-color: #4a6fa5;
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 20px;
        }
        .btn:hover {
            background-color: #3d5d8a;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }
        .footer a {
            color: #4a6fa5;
            text-decoration: none;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="email-wrapper">
            <div class="header">
                <h1>Invoice from AKRA Health</h1>
            </div>
            
            <div class="content">
                <p>Dear {{ $invoice->customer_details['name'] ?? 'Customer' }},</p>
                
                <p>{{ $invoice->status === 'paid' ? 'Thank you for your payment!' : 'Please find your invoice details below.' }}</p>

                <div class="invoice-details">
                    <table>
                        <tr>
                            <td>Invoice Number:</td>
                            <td><strong>{{ $invoice->invoice_number }}</strong></td>
                        </tr>
                        <tr>
                            <td>Date:</td>
                            <td>{{ $invoice->created_at->format('F j, Y') }}</td>
                        </tr>
                        <tr>
                            <td>Due Date:</td>
                            <td>{{ $invoice->due_date ? $invoice->due_date->format('F j, Y') : 'Upon Receipt' }}</td>
                        </tr>
                        <tr>
                            <td>Status:</td>
                            <td>
                                @if($invoice->status === 'paid')
                                    <span class="status-badge status-paid">Paid</span>
                                @elseif($invoice->status === 'overdue')
                                    <span class="status-badge status-overdue">Overdue</span>
                                @else
                                    <span class="status-badge status-pending">Pending</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>

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
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="3" class="text-right">Total Amount:</td>
                            <td class="text-right"><span class="amount">{{ $invoice->currency }} {{ number_format($invoice->total_amount, 2) }}</span></td>
                        </tr>
                        @if($invoice->transactions && $invoice->transactions->where('status', 'completed')->count() > 0)
                        @php $paidAmount = $invoice->transactions->where('status', 'completed')->sum('amount') @endphp
                        <tr>
                            <td colspan="3" class="text-right">Paid:</td>
                            <td class="text-right" style="color: #28a745;">- {{ $invoice->currency }} {{ number_format($paidAmount, 2) }}</td>
                        </tr>
                        <tr class="total-row">
                            <td colspan="3" class="text-right">Balance Due:</td>
                            <td class="text-right"><span class="amount">{{ $invoice->currency }} {{ number_format(max(0, $invoice->total_amount - $paidAmount), 2) }}</span></td>
                        </tr>
                        @endif
                    </tfoot>
                </table>
                @endif

                @if(!$invoice->isPaid() && isset($paymentUrl))
                <div class="text-center">
                    <a href="{{ $paymentUrl }}" class="btn">Pay Now</a>
                </div>
                @endif

                @if($invoice->notes)
                <div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 6px;">
                    <strong>Notes:</strong><br>
                    {{ $invoice->notes }}
                </div>
                @endif

                @if($invoice->terms_conditions)
                <div style="margin-top: 20px; font-size: 14px; color: #666;">
                    <strong>Terms & Conditions:</strong><br>
                    {{ $invoice->terms_conditions }}
                </div>
                @endif
            </div>

            <div class="footer">
                <p>Thank you for choosing AKRA Health!</p>
                <p>
                    <a href="{{ url('/') }}">Visit Website</a> | 
                    <a href="{{ url('/contact') }}">Contact Us</a>
                </p>
                <p style="font-size: 12px; color: #999;">
                    This is an automated message. Please do not reply directly to this email.
                </p>
            </div>
        </div>
    </div>
</body>
</html>

