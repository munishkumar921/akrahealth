<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #555; font-size: 14px; line-height: 24px; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, .15); }
        .header { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #333; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 5px; vertical-align: top; }
        .heading { background: #eee; border-bottom: 1px solid #ddd; font-weight: bold; }
        .heading td { padding: 10px; }
        .item td { padding: 10px; border-bottom: 1px solid #eee; }
        .total td { padding: 10px; border-top: 2px solid #eee; font-weight: bold; }
        .text-right { text-align: right; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; color: white; }
        .bg-success { background-color: #28a745; }
        .bg-warning { background-color: #ffc107; color: #333; }
        .bg-danger { background-color: #dc3545; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0" style="width: 100%;">
            <tr class="top">
                <td colspan="2">
                    <table style="width: 100%;">
                        <tr>
                            <td class="title">
                                <h1>INVOICE</h1>
                            </td>
                            <td class="text-right">
                                Invoice #: {{ $invoice_number }}<br>
                                Date: {{ $date }}<br>
                                Status: <span class="badge {{ $subscription->status == 'active' || $subscription->status == 'completed' ? 'bg-success' : 'bg-warning' }}">{{ ucfirst($subscription->status) }}</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="information">
                <td colspan="2">
                    <table style="width: 100%;">
                        <tr>
                            <td>
                                <strong>Bill To:</strong><br>
                                {{ $user->name ?? 'N/A' }}<br>
                                {{ $user->email ?? 'N/A' }}<br>
                                @if($user->address)
                                    {{ $user->address->address_line_1 }}<br>
                                    {{ $user->address->city }}, {{ $user->address->state }} {{ $user->address->zip }}
                                @endif
                            </td>
                            <td class="text-right">
                                <strong>Payment Details:</strong><br>
                                ID: {{ $subscription->razorpay_payment_id ?? 'N/A' }}<br>
                                Order: {{ $subscription->razorpay_order_id ?? $subscription->id }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <br>

        <table class="info-table">
            <tr class="heading">
                <td>Item Description</td>
                <td class="text-right">Price</td>
                <td class="text-right">Total</td>
            </tr>

            <tr class="item">
                <td>{{ $plan->title ?? 'Subscription Plan' }}</td>
                <td class="text-right">{{ $subscription->currency ?? 'INR' }} {{ number_format($subscription->amount, 2) }}</td>
                <td class="text-right">{{ $subscription->currency ?? 'INR' }} {{ number_format($subscription->amount, 2) }}</td>
            </tr>

            <tr class="total">
                <td></td>
                <td class="text-right">Total:</td>
                <td class="text-right">{{ $subscription->currency ?? 'INR' }} {{ number_format($subscription->amount, 2) }}</td>
            </tr>
        </table>

        <div style="margin-top: 30px; text-align: center; color: #777; font-size: 12px;">
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>
</html>