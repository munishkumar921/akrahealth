<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout2 from '@/Layouts/AppLayout2.vue';

const props = defineProps({
    user: Object,
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

// STEP 3: Open Razorpay Checkout
const openRazorpayCheckout = async () => {
    if (isProcessing.value) return;

    isProcessing.value = true;
    paymentStatus.value = null;

    try {
        // Ensure the Razorpay SDK is loaded before proceeding
        await ensureRazorpayLoaded();

        // Initialize Razorpay checkout
        const options = {
            key: props.razorpayKey,
            amount: props.razorpayOrder.amount,
            currency: props.razorpayOrder.currency,
            name: 'Akra Health',
            description: `Subscription Payment - ${props.subscriptionPlan.title}`,
            order_id: props.razorpayOrder.id,
            handler: function (response) {
                // STEP 4A: Handle successful payment
                handlePaymentSuccess(response);
            },
            prefill: {
                name: props.user.name || `${props.user.first_name || ''} ${props.user.last_name || ''}`.trim() || 'User',
                email: props.user.email,
                contact: props.user.mobile || '',
            },
            theme: {
                color: '#2563eb',
            },
            modal: {
                ondismiss: function () {
                    // STEP 4B: Handle payment failure/cancellation
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

// STEP 4A: Payment Success → Activate User
const handlePaymentSuccess = async (response) => {
    try {
        const verifyResponse = await fetch(route('signup.payment.verify'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({
                razorpay_payment_id: response.razorpay_payment_id,
                razorpay_order_id: response.razorpay_order_id,
                razorpay_signature: response.razorpay_signature,
                subscription_id: props.subscription.id,
            }),
        });

        if (!verifyResponse.ok) {
            const errorData = await verifyResponse.json().catch(() => ({}));
            throw new Error(errorData.message || `Server error: ${verifyResponse.status}`);
        }

        const result = await verifyResponse.json();

        if (result.success) {
            paymentStatus.value = {
                type: 'success',
                message: result.message || 'Payment successful! Your account has been activated.',
            };

            // Redirect to login with success message after 2 seconds
            setTimeout(() => {
                const successMessage = encodeURIComponent('Payment successful! Your account has been activated. Please login.');
                router.visit(`${route('login')}?status=${successMessage}`, {
                    preserveState: false,
                    preserveScroll: false,
                });
            }, 500);
        } else {
            // Payment verification failed
            console.error('Payment verification failed:', result);
            handlePaymentFailure(result.message || 'Payment verification failed');
        }
    } catch (error) {
        console.error('Payment verification error:', error);
        const errorMessage = error.message || 'Payment verification failed. Please contact support.';
        handlePaymentFailure(errorMessage);
    } finally {
        isProcessing.value = false;
    }
};

// STEP 4B: Payment Failed → Delete User & Data
const handlePaymentFailure = async (message) => {
    try {
        // Notify backend to delete user and data
        await fetch(route('signup.payment.failed'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({
                subscription_id: props.subscription.id,
            }),
        });

        paymentStatus.value = {
            type: 'error',
            message: message || 'Payment failed. Please try signing up again.',
        };

        // Redirect to signup with selected plan after 3 seconds
        setTimeout(() => {
            const signupUrl = route('signup', {
                subscription_plan_id: props.subscriptionPlan?.id || props.subscription?.subscription_plan_id,
            });
            // Redirect cleanly without passing error in URL
            // The error message is already shown to the user before redirect
            router.visit(signupUrl);
        }, 3000);
    } catch (error) {
        console.error('Payment failure handler error:', error);
        paymentStatus.value = {
            type: 'error',
            message: 'An error occurred. Please contact support.',
        };
    } finally {
        isProcessing.value = false;
    }
};

// Cancel Payment → Redirect to Signup
const cancelPayment = () => {
    const signupUrl = route('signup', {
        subscription_plan_id: props.subscriptionPlan?.id || props.subscription?.subscription_plan_id,
    });
    router.visit(signupUrl);
};


</script>

<template>
    <AppLayout2 title="Payment" description="Complete your subscription payment">
        <section class="sign-in-page">
            <div class="container bg-white p-0">
                <div class="row no-gutters">
                    <div class="col-sm-12 align-self-center">
                        <div class="sign-in-from p-5">
                            <h1 class="dark-signin mb-4 font-size-32 text-center">Complete Your Payment</h1>

                            <!-- Payment Status Messages -->
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

                            <!-- Payment Details -->
                            <div v-if="!paymentStatus" class="card mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Payment Details</h5>
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
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong>Name:</strong>
                                            <p>{{ user.name || `${user.first_name || ''} ${user.last_name || ''}`.trim() || 'N/A' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Email:</strong>
                                            <p>{{ user.email }}</p>
                                        </div>
                                    </div>
                                    <div class="text-center mt-3">
                                        <button
                                            @click="openRazorpayCheckout"
                                            class="btn btn-primary me-2"
                                            :disabled="isProcessing"
                                        >
                                            <span v-if="isProcessing" class="spinner-border spinner-border-sm me-2"></span>
                                            Pay Now
                                        </button>
                                        <button
                                            @click="cancelPayment"
                                            class="btn btn-danger"
                                            :disabled="isProcessing"
                                        >
                                            Cancel
                                        </button>
                                        <div v-if="isProcessing && !isScriptReady" class="mt-2">
                                            <div class="spinner-border" role="status">
                                                <span class="visually-hidden">Loading payment gateway...</span>
                                            </div>
                                            <p class="mt-2">Loading payment gateway...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <!-- Security Notice -->
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
    width: 1rem!important;
    height: 1rem!important;
    border-width: 0.3em;
}
</style>

