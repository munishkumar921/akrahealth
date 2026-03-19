<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router } from "@inertiajs/vue3";
import { ref, computed, onMounted } from "vue";
import { useSubscriptionDaysLeft } from "@/Composables/useSubscription";

// Access global toast function from app.js
const toast = window.toast;

const {createExpiryInfo } = useSubscriptionDaysLeft();

const props = defineProps({
    plan: Object,
    currentSubscription: Object,
    isUpgrade: {
        type: Boolean,
        default: false
    },
    upgradeFrom: {
        type: String,
        default: ''
    }
});

const loading = ref(false);
const showSuccess = ref(false);
const successMessage = ref('');
const razorpayKey = ref('');
const razorpayOrder = ref(null);
const subscriptionId = ref(null);

const isCurrentPlan = computed(() => {
    return props.currentSubscription && props.currentSubscription.subscription_plan_id === props.plan?.id;
});

const isExpiredSubscription = computed(() => {
    return props.currentSubscription && props.currentSubscription.status === 'expired';
});

const planExpiry = computed(() => {
    if (!props.currentSubscription) return null;
    return createExpiryInfo(props.currentSubscription.end_date);
});

const goBack = () => {
    router.visit(route('admin.subscription'));
};

/* ---------------- FEATURES PARSER ---------------- */
const parseFeatures = (featuresHtml) => {
    if (!featuresHtml) return [];
    
    const temp = document.createElement('div');
    temp.innerHTML = featuresHtml;
    
    return Array.from(temp.querySelectorAll('li')).map(li => li.textContent.trim());
};

const planFeatures = computed(() => {
    return parseFeatures(props.plan?.features);
});

/* ---------------- RAZORPAY PAYMENT ---------------- */
const loadRazorpayScript = () => {
    return new Promise((resolve) => {
        if (window.Razorpay) {
            resolve();
            return;
        }
        const script = document.createElement('script');
        script.src = 'https://checkout.razorpay.com/v1/checkout.js';
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('Failed to load Razorpay SDK'));
        document.head.appendChild(script);
    });
};

const subscribe = async () => {
    if (loading.value || isCurrentPlan.value) return;
    
    loading.value = true;
    
    try {
        // Determine which endpoint to use based on whether this is an upgrade
        const endpoint = props.isUpgrade 
            ? route('subscriptions.upgrade', props.plan.id)
            : route('subscriptions.subscribe', props.plan.id);
        
        // Create Razorpay order
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                payment_method_id: 'razorpay',
            }),
        });
        
        const data = await response.json();
        
        if (!data.success) {
            loading.value = false;
            alert(data.message || 'Failed to create payment order. Please try again.');
            return;
        }
        
        razorpayKey.value = data.razorpayKey;
        razorpayOrder.value = data.razorpayOrder;
        subscriptionId.value = data.subscription.id;
        
        // Load Razorpay and open checkout
        await loadRazorpayScript();
        openRazorpayCheckout();
        
    } catch (error) {
        loading.value = false;
        console.error('Subscription error:', error);
        alert('An error occurred. Please try again.');
    }
};

const openRazorpayCheckout = () => {
    const options = {
        key: razorpayKey.value,
        amount: razorpayOrder.value.amount,
        currency: razorpayOrder.value.currency,
        name: 'AkraHealth',
        description: props.isUpgrade 
            ? `Upgrade to ${props.plan?.title || 'Plan'}`
            : `Subscription to ${props.plan?.title || 'Plan'}`,
        order_id: razorpayOrder.value.id,
        handler: async (response) => {
            loading.value = true;
            
            try {
                // Determine which verification endpoint to use
                const verifyEndpoint = props.isUpgrade
                    ? route('subscriptions.upgrade.verify-payment', subscriptionId.value)
                    : route('subscriptions.verify-payment', subscriptionId.value);
                
                // Verify payment
                const verifyResponse = await fetch(verifyEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: JSON.stringify({
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_signature: response.razorpay_signature,
                    }),
                });
                
                const verifyData = await verifyResponse.json();
                
                if (verifyData.success) {
                    loading.value = false;
                    showSuccess.value = true;
                    successMessage.value = verifyData.message || 'Payment successful! Your subscription is now active.';
                    toast(verifyData.message, 'success');
                } else {
                    loading.value = false;
                    toast(verifyData.message, 'error');
                }
            } catch (error) {
                loading.value = false;
                console.error('Payment verification error:', error);
                toast('Payment was completed but verification failed. Please contact support.', 'error');
            }
        },
        modal: {
            ondismiss: () => {
                loading.value = false;
                toast('Payment was cancelled.','error');
            },
        },
        prefill: {
            name: props.currentSubscription?.user?.name || '',
            email: props.currentSubscription?.user?.email || '',
            contact: '',
        },
        theme: {
            color: '#0d6efd',
        },
    };
    
    const rzp1 = new window.Razorpay(options);
    rzp1.open();
};

const buttonText = computed(() => {
    if (isCurrentPlan) {
        return 'Upgrade Plan';
    }
    if (props.isUpgrade) {
        return 'Upgrade Now';
    }

    // return 'Subscribe Now';
});

const buttonDescription = computed(() => {
    if (props.isUpgrade) {
        return `${props.plan?.currency} ${props.plan?.price} - Upgrade from ${props.upgradeFrom || 'Expired Plan'}`;
    }
    return `${props.plan?.currency} ${props.plan?.price}`;
});
</script>

<template>
    <AuthLayout
        :title="isUpgrade ? `Upgrade to ${plan?.title || 'Plan'}` : `Subscribe to ${plan?.title || 'Plan'}`"
        :description="isUpgrade ? 'Upgrade your subscription to unlock more features' : 'Complete your subscription'"
        :heading="isUpgrade ? `Upgrade to ${plan?.title || 'Plan'}` : plan?.title || 'Select Plan'"
    >
      <div class="d-flex justify-content-end">
                     <button 
                                @click="goBack"
                                class="btn btn-primary"
                                :disabled="loading"
                            >
                                <i class="fas fa-arrow-left me-2"></i>
                                Back to Plans
                            </button>
                </div>
         <div class="row justify-content-center">
          
            <div class="col-lg-8">
                <!-- Success Message -->
                <div v-if="showSuccess" class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ successMessage }}
                 </div>

                <!-- Upgrade Alert -->
                <div v-if="isUpgrade && !showSuccess" class="alert alert-info alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Upgrade Your Subscription</strong><br>
                    Your previous subscription has expired. Upgrade now to continue using all features.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                

                <div class="row">
                    <!-- Plan Details Card -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100" :class="{ 'border-success': isUpgrade }">
                            <div class="card-header" :class="isUpgrade ? 'bg-success text-white' : 'bg-primary text-white'">
                                <h5 class="mb-0 text-white">
                                    <i v-if="isUpgrade" class="fas fa-arrow-up me-2"></i>
                                    {{ isUpgrade ? 'New Plan (Upgrade)' : 'Plan Details' }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted">Plan Name:</td>
                                        <td class="fw-bold">{{ plan?.title }}</td>
                                    </tr>
                                    
                                    <tr>
                                        <td class="text-muted">Billing Cycle:</td>
                                        <td>{{ plan?.frequency }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Price:</td>
                                        <td>
                                            <span class="h4" :class="isUpgrade ? 'text-success' : 'text-primary'" mb-0>
                                                {{ plan?.currency }} {{ plan?.price }}
                                            </span>
                                            <span class="text-muted">/{{ plan?.frequency?.toLowerCase() || 'mo' }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted align-middle">Features:</td>
                                        <td>
                                            <ul v-if="planFeatures.length" class="list-unstyled mb-0">
                                                <li v-for="(feature, index) in planFeatures" :key="index" class="mb-1 d-flex align-items-start">
                                                    <i class="fas fa-check text-success me-2 mt-1 flex-shrink-0" style="font-size: 0.875rem;"></i>
                                                    <span class="feature-text" style="font-size: 0.875rem;">{{ feature }}</span>
                                                </li>
                                            </ul>
                                            <span v-else class="text-muted">Full access to all features</span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Current Subscription Card -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100" :class="{ 'border-warning': isExpiredSubscription }">
                            <div class="card-header" :class="isExpiredSubscription ? 'bg-warning text-dark' : 'bg-secondary text-white'">
                                <h5 class="mb-0 text-white">
                                    <i v-if="isExpiredSubscription" class="fas fa-exclamation-triangle me-2"></i>
                                    {{ isUpgrade ? 'Previous Subscription (Expired)' : 'Current Subscription' }}
                                </h5>
                            </div>
                            <div class="card-body">
                                 <div v-if="currentSubscription">
                                    <table class="table table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted">Plan Name:</td>
                                            <td class="fw-bold">{{ currentSubscription.subscription_plan?.title || upgradeFrom || 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Status:</td>
                                            <td>
                                                <span class="badge" :class="currentSubscription.status === 'active' ? 'bg-success' : 'bg-warning'">
                                                    {{ currentSubscription.status || 'N/A' }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr v-if="planExpiry && !isUpgrade">
                                            <td class="text-muted">Expires On:</td>
                                            <td :class="planExpiry.isExpired ? 'text-danger fw-bold' : ''">
                                                {{ planExpiry.endDate }}
                                                <span v-if="planExpiry.isExpired" class="badge bg-danger ms-2">Expired</span>
                                                <span v-else class="text-muted">({{ planExpiry.daysLeft }} days left)</span>
                                            </td>
                                        </tr>
                                        <tr v-if="isUpgrade">
                                            <td class="text-muted">Expired On:</td>
                                            <td class="text-danger fw-bold">
                                                {{ currentSubscription.end_date ? new Date(currentSubscription.end_date).toLocaleDateString() : 'Unknown' }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div v-else class="text-center py-4">
                                    <p class="text-muted mb-2">No subscription found</p>
                                    <p class="text-muted small">Subscribe to unlock all features</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Section -->
                <div v-if="!showSuccess" class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-credit-card me-2"></i>
                            {{ isUpgrade ? 'Complete Your Upgrade' : 'Payment Method' }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="card bg-light border-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-credit-card text-primary me-3 fs-4"></i>
                                        <div>
                                            <div class="fw-bold">Razorpay</div>
                                            <div class="text-muted small mb-0">Pay securely with Razorpay</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                           
                            
                            <button 
                                @click="subscribe"
                                class="btn"
                                :class="isUpgrade ? 'btn-success' : 'btn-primary'"
                                :disabled="loading || isCurrentPlan"
                            >
                                <span v-if="loading">
                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                    Processing...
                                </span>
                                <span v-else-if="isCurrentPlan">
                                    <i class="fas fa-check me-2"></i>
                                    Current Plan
                                </span>
                                <span v-else>
                                    <i class="fas fa-lock me-2"></i>
                                    {{ buttonText }} - {{ buttonDescription }}
                                </span>
                            </button>
                        </div>
                        
                        <div v-if="isCurrentPlan" class="text-center mt-2">
                            <span class="text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                You are already subscribed to this plan.
                            </span>
                        </div>
                        
                        <div v-if="isUpgrade" class="text-center mt-2">
                            <span class="text-info">
                                <i class="fas fa-info-circle me-1"></i>
                                Your old subscription will be replaced by this upgrade.
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Success Actions -->
                <div v-if="showSuccess" class="text-center">
                     
                    <button 
                        @click="router.visit(route('subscriptions.index'))"
                        class="btn btn-primary me-2"
                    >
                        <i class="fas fa-list me-2"></i>
                        View My Subscription
                    </button>
                    <button 
                        @click="router.visit(route('dashboard'))"
                        class="btn btn-secondary"
                    >
                        <i class="fas fa-home me-2"></i>
                        Go to Dashboard
                    </button>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<style scoped>
 
.cursor-pointer {
    cursor: pointer;
}
.card {
    transition: all 0.2s ease-in-out;
}

.feature-text {
    word-break: break-word;
    overflow-wrap: break-word;
}

.list-unstyled li {
    display: flex;
    align-items: flex-start;
}

.list-unstyled li i {
    flex-shrink: 0;
    margin-top: 0.25rem;
}
</style>
