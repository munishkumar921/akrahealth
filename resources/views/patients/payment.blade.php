<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment for Appointment</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="container">
        <h1>Payment for Appointment</h1>
        <p>Please complete your payment for the appointment with {{ $appointment->doctor->user->name }}.</p>
        <p>Amount: {{ $appointment->fee_amount }} {{ $appointment->currency }}</p>

        <form action="{{ route('payment.verify') }}" method="POST" id="razorpay-form">
            @csrf
            <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
            <input type="hidden" name="razorpay_order_id" id="razorpay_order_id" value="{{ $order_id }}">
            <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
            <input type="hidden" name="razorpay_signature" id="razorpay_signature">

            <button id="rzp-button1" class="btn btn-primary">Pay Now</button>
        </form>
    </div>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script src="{{ asset('js/razorpay.js') }}"></script>
</body>
</html>