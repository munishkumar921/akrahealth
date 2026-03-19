<script setup>
import { ref, onMounted } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

import AuthLayout from "@/Layouts/AuthLayout2.vue";

const props = defineProps({
    appointment: Object,
    razorpayKey: String,
});

const isProcessing = ref(false);
const paymentStatus = ref(null);
const isScriptReady = ref(!!window.Razorpay);

const formatDateTime = (date, time) => {
    const dateTime = new Date(`${date} ${time}`);
    return dateTime.toLocaleString('en-IN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const ensureRazorpayLoaded = () => {
    if (window.Razorpay) {
        isScriptReady.value = true;
        return Promise.resolve();
    }
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = 'https://checkout.razorpay.com/v1/checkout.js';
        script.async = true;
        script.onload = () => {
            isScriptReady.value = true;
            resolve();
        };
        script.onerror = () => reject(new Error('Failed to load Razorpay'));
        document.head.appendChild(script);
    });
};

const initiatePayment = async () => {
    if (isProcessing.value) return;

    isProcessing.value = true;
    paymentStatus.value = null;

    try {
        // Ensure the Razorpay SDK is loaded before proceeding
        await ensureRazorpayLoaded();

        const response = await fetch(route('patient.create.order'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({
                appointment_id: props.appointment.id,
            }),
        });

        if (!response.ok) {
            throw new Error('Failed to create order');
        }

        const orderData = await response.json();

        if (!orderData.success) {
            throw new Error(orderData.message || 'Failed to create order');
        }

        // Initialize Razorpay checkout
        const options = {
            key: props.razorpayKey,
            amount: orderData.amount,
            currency: orderData.currency,
            name: 'Akra Health',
            description: 'Appointment Payment',
            order_id: orderData.id,
            handler: function (response) {
                // Handle successful payment
                verifyPayment(response);
            },
            prefill: {
                name: props.appointment.patient.user.name,
                email: props.appointment.patient.user.email,
                contact: props.appointment.patient.phone || '',
            },
            theme: {
                color: '#2563eb',
            },
            modal: {
                ondismiss: function () {
                    isProcessing.value = false;
                }
            }
        };

        const rzp = new Razorpay(options);
        rzp.open();

    } catch (error) {
        paymentStatus.value = {
            success: false,
            message: 'Failed to initiate payment. Please try again.',
        };
        isProcessing.value = false;
    }
};

const verifyPayment = async (response) => {
    isProcessing.value = true;
    try {
        const verifyResponse = await fetch(route('patient.verify.payment'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({
                razorpay_payment_id: response.razorpay_payment_id,
                razorpay_order_id: response.razorpay_order_id,
                razorpay_signature: response.razorpay_signature,
                appointment_id: props.appointment.id,
            }),
        });

        const result = await verifyResponse.json();

        if (result.success) {
            paymentStatus.value = {
                success: true,
                message: 'Payment successful! Redirecting...',
            };

            setTimeout(() => {
                window.history.back();

                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }, 5000);
        } else {
            paymentStatus.value = {
                success: false,
                message: result.message || 'Payment verification failed.',
            };
        }
    } catch (error) {
        console.error('Payment verification failed:', error);
        paymentStatus.value = {
            success: false,
            message: 'Payment verification failed: ' + error.message,
        };
    } finally {
        isProcessing.value = false;
    }
};

onMounted(() => {
    ensureRazorpayLoaded().catch(() => {
        paymentStatus.value = {
            success: false,
            message: 'Unable to load payment SDK. Please retry.',
        };
    });
});
</script>

<style scoped>
.payment-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.payment-header {
    text-align: center;
    margin-bottom: 2.5rem;
}

.payment-header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.payment-header p {
    color: #6b7280;
    font-size: 1rem;
}

.payment-card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    padding: 2rem;
    margin-bottom: 1.5rem;
    transition: transform 0.2s, box-shadow 0.2s;
}

.payment-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-title i {
    font-size: 1.5rem;
    color: #3b82f6;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-label {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 500;
}

.detail-value {
    font-size: 0.875rem;
    color: #111827;
    font-weight: 600;
    text-align: right;
}

.payment-summary {
    background: #09acff;
    border-radius: 12px;
    padding: 1.5rem;
    color: white;
    margin-bottom: 1.5rem;
}

.payment-summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.payment-summary-row:last-child {
    margin-bottom: 0;
    padding-top: 0.75rem;
    border-top: 1px solid rgba(255, 255, 255, 0.3);
    font-size: 1.25rem;
    font-weight: 700;
}

.payment-button {
    width: 100%;
    padding: 1rem 2rem;
    background: #09acff;
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1.125rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.payment-button:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
}

.payment-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.payment-button i {
    font-size: 1.25rem;
}

.status-message {
    margin-top: 1rem;
    padding: 1rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.875rem;
    font-weight: 500;
}

.status-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.status-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.status-message i {
    font-size: 1.25rem;
}

.security-badge {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 1rem;
    padding: 0.75rem;
    background: #f3f4f6;
    border-radius: 8px;
    font-size: 0.75rem;
    color: #6b7280;
}

.security-badge i {
    color: #10b981;
    font-size: 1rem;
}

.loading-spinner {
    display: inline-block;
    width: 1rem;
    height: 1rem;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

@media (max-width: 768px) {
    .payment-container {
        padding: 1rem 0.5rem;
    }

    .payment-card {
        padding: 1.5rem;
    }

    .payment-header h1 {
        font-size: 1.5rem;
    }
}
</style>
<template>
    <AuthLayout title="Payment">
        <div class="iq-card">
            <div class="iq-card-body payment-container">
                <div class="payment-header">
                    <h1><i class="ri-bill-line"></i> Appointment Payment</h1>
                    <p>Complete your payment to confirm your appointment</p>
                </div>

                <div class="row">
                    <!-- Appointment Details Card -->
                    <div class="col-lg-6 mb-4">
                        <div class="payment-card">
                            <h2 class="card-title">
                                <i class="ri-calendar-check-line"></i>
                                Appointment Details
                            </h2>
                            <div class="detail-item">
                                <span class="detail-label"><i class="ri-user-line"></i> Doctor</span>
                                <span class="detail-value">{{ appointment?.doctor?.name || 'N/A' }} {{
                                    appointment?.doctor?.user?.name || 'N/A' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="ri-calendar-line"></i> Date & Time</span>
                                <span class="detail-value">{{ appointment.appointment_date }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="ri-hospital-line"></i> Type</span>
                                <span class="detail-value">{{ appointment.appointment_type || 'N/A' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="ri-time-line"></i> Duration</span>
                                <span class="detail-value">{{ appointment.duration_minutes || 0 }} minutes</span>
                            </div>
                            <div v-if="appointment.reason" class="detail-item">
                                <span class="detail-label"><i class="ri-file-text-line"></i> Reason</span>
                                <span class="detail-value">{{ appointment.reason }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Details Card -->
                    <div class="col-lg-6 mb-4">
                        <div class="payment-card">
                            <h2 class="card-title">
                                <i class="ri-wallet-3-line"></i>
                                Payment Summary
                            </h2>

                            <div class="payment-summary">
                                <div class="payment-summary-row">
                                    <span>Consultation Fee</span>
                                    <span>{{ appointment.fee_amount || 0 }} {{ appointment?.currency || 'INR' }}</span>
                                </div>
                                <div class="payment-summary-row">
                                    <span>Total Amount</span>
                                    <span>{{ appointment.fee_amount || 0 }} {{ appointment?.currency || 'INR' }}</span>
                                </div>
                            </div>

                            <button @click="initiatePayment" :disabled="isProcessing || !isScriptReady"
                                class="payment-button">
                                <span v-if="isProcessing" class="loading-spinner"></span>
                                <i v-else class="ri-lock-line"></i>
                                <span v-if="isProcessing">Processing Payment...</span>
                                <span v-else-if="!isScriptReady">Loading Payment Gateway...</span>
                                <span v-else>Pay Securely</span>
                            </button>

                            <div v-if="paymentStatus" class="status-message"
                                :class="paymentStatus.success ? 'status-success' : 'status-error'">
                                <i
                                    :class="paymentStatus.success ? 'ri-checkbox-circle-fill' : 'ri-error-warning-fill'"></i>
                                <span>{{ paymentStatus.message }}</span>
                            </div>

                            <div class="security-badge">
                                <i class="ri-shield-check-line"></i>
                                <span>Secured by Razorpay • SSL Encrypted</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
