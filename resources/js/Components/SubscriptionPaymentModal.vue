<script setup>
import { ref, onMounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    subscription: Object,
    subscriptionPlan: Object,
    razorpayKey: String,
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'payment-success']);

const isProcessing = ref(false);
const paymentStatus = ref(null);
const isScriptReady = ref(!!window.Razorpay);
const razorpayOrder = ref(null);

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

const createOrder = async () => {
    try {
        const response = await fetch(route('subscriptions.renew.order', props.subscription.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        });

        const result = await response.json();
        if (result.success) {
            razorpayOrder.value = result.order;
            return true;
        }
        return false;
    } catch (error) {
        console.error('Order creation error:', error);
        return false;
    }
};

const openRazorpayCheckout = async () => {
    if (isProcessing.value || !props.show) return;

    isProcessing.value = true;
    paymentStatus.value = null;

    try {
        await ensureRazorpayLoaded();

        // Create order if not already created
        if (!razorpayOrder.value) {
            const orderCreated = await createOrder();
            if (!orderCreated) {
                throw new Error('Failed to create payment order');
            }
        }

        const options = {
            key: props.razorpayKey,
            amount: razorpayOrder.value.amount,
            currency: razorpayOrder.value.currency,
            name: 'Akra Health',
            description: `Subscription Renewal - ${props.subscriptionPlan.title}`,
            order_id: razorpayOrder.value.id,
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
                    isProcessing.value = false;
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
                emit('payment-success');
                emit('close');
                router.reload();
            }, 2000);
        } else {
            paymentStatus.value = {
                type: 'error',
                message: result.message || 'Payment verification failed',
            };
        }
    } catch (error) {
        console.error('Payment verification error:', error);
        paymentStatus.value = {
            type: 'error',
            message: 'Payment verification failed. Please contact support.',
        };
    } finally {
        isProcessing.value = false;
    }
};

const closeModal = () => {
    emit('close');
};

// Watch for show prop changes
watch(() => props.show, (newVal) => {
    if (newVal && props.subscription && !razorpayOrder.value) {
        createOrder();
    }
});

onMounted(() => {
    if (props.show && props.subscription) {
        createOrder();
    }
});
</script>

<template>
    <div v-if="show" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Subscription Expired
                    </h5>
                    <button type="button" class="btn-close" @click="closeModal" :disabled="isProcessing"></button>
                </div>
                <div class="modal-body">
                    <div v-if="paymentStatus && paymentStatus.type === 'success'" class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ paymentStatus.message }}
                    </div>
                    <div v-else-if="paymentStatus && paymentStatus.type === 'error'" class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ paymentStatus.message }}
                    </div>
                    <div v-else>
                        <p class="mb-3">
                            Your subscription has expired. To continue using all features, please renew your subscription.
                        </p>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Renewal Details</h6>
                                <p class="mb-1"><strong>Plan:</strong> {{ subscriptionPlan.title }}</p>
                                <p class="mb-0"><strong>Amount:</strong> {{ subscriptionPlan.currency }} {{ subscriptionPlan.price }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        v-if="!paymentStatus || paymentStatus.type === 'error'"
                        type="button"
                        class="btn btn-primary"
                        @click="openRazorpayCheckout"
                        :disabled="isProcessing || !razorpayOrder"
                    >
                        <span v-if="isProcessing" class="spinner-border spinner-border-sm me-2"></span>
                        <span v-else><i class="fas fa-credit-card me-2"></i></span>
                        Renew Now
                    </button>
                    <button
                        v-if="paymentStatus && paymentStatus.type !== 'success'"
                        type="button"
                        class="btn btn-danger"
                        @click="closeModal"
                        :disabled="isProcessing"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.modal.show {
    display: block;
}
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
    border-width: 0.15em;
}
</style>

