<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    appointment: Object,
});

window.onload = function () {
    window.print();
    window.close();
};
</script>
<template>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div>
                <div class="company-name">AkraHealth</div>
                <div>Telehealth & Online Consultation</div>
            </div>
            <div class="text-right">
                <div class="invoice-title">INVOICE</div>
                <div>Invoice #: INV-{{ appointment.invoice.invoice_number }}</div>
                <div>Date: {{ appointment.invoice.created_at }}</div>
            </div>
        </div>

        <!-- Patient & Doctor Info -->
        <div class="section">
            <div class="info-box">
                <div class="section-title" style="text-align: left;">Billed To:</div>
                <div>{{ appointment.patient.user.name }}</div>
                <div>{{ appointment.patient.user.email }}</div>
                <div>{{ appointment.patient.user.mobile }}</div>
            </div>

            <div class="info-box" style="float:right;">
                <div class="section-title" style="text-align: left;">Doctor:</div>
                <div>Dr. {{ appointment.doctor.user.name }}</div>
                <div> {{ appointment.doctor.specialities[0]?.name ?? 'N/A' }}</div>
                <div>Appointment mode {{ appointment.appointment_mode }}</div>
            </div>
            <div style="clear:both;"></div>
        </div>

        <!-- Appointment Table -->
        <div class="section">
            <div class="section-title">Appointment Details</div>

            <table>
                <thead>
                    <tr>
                        <th style="color:black">Description</th>
                        <th style="color:black">Date</th>
                        <th style="color:black">Duration</th>
                        <th style="color:black" class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ appointment.reason ?? 'No reason provided' }}</td>
                        <td>{{ appointment.appointment_date }}</td>
                        <td>{{ appointment.duration_minutes }} mins</td>
                        <td class="text-right">{{ appointment?.fee_amount }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="totals">
            <div class="totals-row">
                <span>Discount</span>
                <span> {{ appointment?.discount_amount ?? 0 }}</span>
            </div>
            <div class="totals-row">
                <span>Tax</span>
                <span> {{ appointment?.tax_amount ?? 0 }}</span>
            </div>
            <div class="totals-row total">
                <span>Total</span>
                <span>{{ appointment?.total_amount }}</span>
            </div>
        </div>
        <div style="clear:both;"></div>

        <!-- Payment Info -->
        <div class="section">
            <div class="section-title">Payment Information</div>
            <div>Payment Method: {{ appointment?.invoice?.payment_method?.toUpperCase() }}</div>
            <div>Razorpay Order ID: {{ appointment?.invoice?.razorpay_payment_id }}</div>
            <div class="payment-status">Payment Status: {{ appointment?.invoice?.status?.toUpperCase() }}</div>
        </div>

        <!-- {{ appointment?.invoice }} -->

        <!-- Footer -->
        <div class="footer">
            This is a computer-generated invoice and does not require a signature.<br>
            © 2026 AkraHealth. All rights reserved.
        </div>

    </div>


</template>

<style>
body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f4f6f9;
    padding: 40px;
    margin: 0;
}

.invoice-container {
    max-width: 900px;
    margin: auto;
    background: #ffffff;
    padding: 40px;
    border-radius: 6px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.header {
    display: flex;
    justify-content: space-between;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 20px;
}

.company-name {
    font-size: 24px;
    font-weight: bold;
    color: #2563eb;
}

.invoice-title {
    font-size: 20px;
    font-weight: bold;
}

.section {
    margin-top: 30px;
}

.section-title {
    font-weight: bold;
    margin-bottom: 10px;
    color: #333;
}

.info-box {
    width: 48%;
    display: inline-block;
    vertical-align: top;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

table th,
table td {
    border: 1px solid #e5e7eb;
    padding: 10px;
    text-align: left;
}

table th {
    background-color: #f9fafb;
}

.text-right {
    text-align: right;
}

.totals {
    width: 50%;
    float: right;
    margin-top: 20px;
}

.totals-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
}

.totals-row.total {
    font-weight: bold;
    font-size: 18px;
    border-top: 2px solid #ddd;
    padding-top: 12px;
}

.payment-status {
    color: green;
    font-weight: bold;
    margin-top: 5px;
}

.footer {
    margin-top: 60px;
    text-align: center;
    font-size: 12px;
    color: #777;
    border-top: 1px solid #eee;
    padding-top: 15px;
}

@media print {
    body {
        background: white;
        padding: 0;
    }

    .invoice-container {
        box-shadow: none;
        border-radius: 0;
    }
}
</style>