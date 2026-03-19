<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout2 from '@/Layouts/AppLayout2.vue';

const props = defineProps({
    subscription: Object,
    subscriptionPlan: Object,
    razorpayOrder: Object,
    razorpayKey: String,
});

const isProcessing = ref(false);
const paymentStatus = ref(null);
const isScriptReady = ref(!!window.Razorpay);

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

const openRazorpayCheckout = async () => {
    if (isProcessing.value) return;

    isProcessing.value = true;
    paymentStatus.value = null;

    try {
        await ensureRazorpayLoaded();

        const options = {
            key: props.razorpayKey,
            amount: props.razorpayOrder.amount,
            currency: props.razorpayOrder.currency,
            name: 'Akra Health',
            description: `Subscription Renewal - ${props.subscriptionPlan.title}`,
            order_id: props.razorpayOrder.id,
            handler: function (response) {
                handlePaymentSuccess(response);
            },
            prefill: {
                name: props.subscription.user?.name || 'User',
                email: props.subscription.user?.email || '',
                contact: props.subscription.user?.mobile || '',
            },
            theme: {
                color: '#2563eb',
            },
            modal: {
                ondismiss: function () {
                    handlePaymentFailure('Payment cancelled by user');
                }
            }
        };

        const rzp = new Razorpay(options);
        rzp.open();

    } catch (error) {
        console.error('Payment initialization error:', error);
        paymentStatus.value = {
            type: 'error',
            message: 'Failed to initialize payment. Please try again.',
        };
        isProcessing.value = false;
    }
};

const handlePaymentSuccess = async (response) => {
    try {
        const verifyResponse = await fetch(route('subscriptions.renew.payment', props.subscription.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({
                razorpay_payment_id: response.razorpay_payment_id,
                razorpay_order_id: response.razorpay_order_id,
                razorpay_signature: response.razorpay_signature,
            }),
        });

        const result = await verifyResponse.json();

        if (result.success) {
            paymentStatus.value = {
                type: 'success',
                message: result.message || 'Payment successful! Your subscription has been renewed.',
            };

            setTimeout(() => {
                router.visit(route('subscriptions.index'), {
                    preserveState: false,
                    preserveScroll: false,
                });
            }, 2000);
        } else {
            handlePaymentFailure(result.message || 'Payment verification failed');
        }
    } catch (error) {
        console.error('Payment verification error:', error);
        handlePaymentFailure('Payment verification failed. Please contact support.');
    } finally {
        isProcessing.value = false;
    }
};

const handlePaymentFailure = (message) => {
    paymentStatus.value = {
        type: 'error',
        message: message || 'Payment failed. Please try again.',
    };
    isProcessing.value = false;
};

onMounted(() => {
    openRazorpayCheckout();
});
</script>

<template>
    <AppLayout2 title="Renew Subscription" description="Renew your subscription">
        <section class="sign-in-page">
            <div class="container bg-white p-0">
                <div class="row no-gutters">
                    <div class="col-sm-12 align-self-center">
                        <div class="sign-in-from p-5">
                            <h1 class="dark-signin mb-4 font-size-32 text-center">Renew Your Subscription</h1>

                            <div v-if="paymentStatus" class="mb-4">
                                <div
                                    v-if="paymentStatus.type === 'success'"
                                    class="alert alert-success"
                                    role="alert"
                                >
                                    <i class="fas fa-check-circle me-2"></i>
                                    {{ paymentStatus.message }}
                                </div>
                                <div
                                    v-else-if="paymentStatus.type === 'error'"
                                    class="alert alert-danger"
                                    role="alert"
                                >
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    {{ paymentStatus.message }}
                                </div>
                            </div>

                            <div v-if="!paymentStatus" class="card mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Renewal Details</h5>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong>Subscription Plan:</strong>
                                            <p>{{ subscriptionPlan.title }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Amount:</strong>
                                            <p>{{ subscriptionPlan.currency }} {{ subscriptionPlan.price }}</p>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <div v-if="isProcessing && !isScriptReady" class="spinner-border" role="status">
                                            <span class="visually-hidden">Loading payment gateway...</span>
                                        </div>
                                        <p v-if="isProcessing && !isScriptReady" class="mt-2">
                                            Loading payment gateway...
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div v-if="paymentStatus && paymentStatus.type === 'error'" class="text-center">
                                <button
                                    @click="openRazorpayCheckout"
                                    class="btn btn-primary"
                                    :disabled="isProcessing"
                                >
                                    <span v-if="isProcessing" class="spinner-border spinner-border-sm me-2"></span>
                                    Try Again
                                </button>
                            </div>

                            <div class="text-center mt-4">
                                <small class="text-muted">
                                    <i class="fas fa-lock me-1"></i>
                                    Secured by Razorpay • SSL Encrypted
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout2>
</template>

<style scoped>
.spinner-border {
    width: 3rem;
    height: 3rem;
    border-width: 0.3em;
}
</style>

