<script setup>
import { computed, onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout2 from '@/Layouts/AppLayout2.vue';

const props = defineProps({
    user: Object,
    subscription: Object,
    subscriptionPlan: Object,
    razorpaySubscriptionId: String,
    razorpaySubscriptionUrl: String,
    razorpayKey: String,
    trialEndsAt: String,
});

const isProcessing = ref(false);
const paymentStatus = ref(null);
const isScriptReady = ref(!!window.Razorpay);

const userDisplayName = computed(() => {
    return props.user?.name
        || `${props.user?.first_name || ''} ${props.user?.last_name || ''}`.trim()
        || 'Subscriber';
});

const formattedAmount = computed(() => {
    return `${props.subscriptionPlan?.currency || 'INR'} ${props.subscriptionPlan?.price || '0.00'}`;
});

const trialDeadlineLabel = computed(() => props.trialEndsAt || '14 days from today');

const successRedirectMessage = 'We have sent you a verification email. Please verify your email. Your 14-day free trial has started.';

const ensureRazorpayLoaded = () => {
    if (window.Razorpay) {
        isScriptReady.value = true;
        return Promise.resolve(window.Razorpay);
    }

    return new Promise((resolve, reject) => {
        const existingScript = document.querySelector('script[data-razorpay-checkout="true"]');
        if (existingScript) {
            existingScript.addEventListener('load', () => {
                isScriptReady.value = true;
                resolve(window.Razorpay);
            }, { once: true });
            existingScript.addEventListener('error', () => reject(new Error('Failed to load Razorpay')), { once: true });
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://checkout.razorpay.com/v1/checkout.js';
        script.async = true;
        script.dataset.razorpayCheckout = 'true';
        script.onload = () => {
            isScriptReady.value = true;
            resolve(window.Razorpay);
        };
        script.onerror = () => reject(new Error('Failed to load Razorpay'));
        document.head.appendChild(script);
    });
};

const redirectToLogin = () => {
    const successMessage = encodeURIComponent(successRedirectMessage);
    router.visit(`${route('login')}?status=${successMessage}`, {
        preserveState: false,
        preserveScroll: false,
    });
};

const handlePaymentFailure = (message) => {
    paymentStatus.value = {
        type: 'error',
        message: message || 'Authorization was not completed. Please retry to start your 14-day free trial.',
    };
    isProcessing.value = false;
};

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
                razorpay_subscription_id: response.razorpay_subscription_id,
                razorpay_signature: response.razorpay_signature,
                subscription_id: props.subscription.id,
            }),
        });

        if (!verifyResponse.ok) {
            const errorData = await verifyResponse.json().catch(() => ({}));
            throw new Error(errorData.message || `Server error: ${verifyResponse.status}`);
        }

        const result = await verifyResponse.json();

        if (!result.success) {
            throw new Error(result.message || 'Payment verification failed.');
        }

        paymentStatus.value = {
            type: 'success',
            message: result.message || successRedirectMessage,
        };

        setTimeout(() => {
            redirectToLogin();
        }, 700);
    } catch (error) {
        console.error('Payment verification error:', error);
        handlePaymentFailure(error.message || 'Payment verification failed. Please contact support.');
    } finally {
        isProcessing.value = false;
    }
};

const openHostedRazorpayPage = () => {
    if (!props.razorpaySubscriptionUrl) {
        handlePaymentFailure('Authorization page is not available right now. Please refresh and retry.');
        return;
    }

    window.location.href = props.razorpaySubscriptionUrl;
};

const openRazorpayCheckout = async () => {
    if (isProcessing.value) return;

    isProcessing.value = true;
    paymentStatus.value = null;

    try {
        const RazorpayCheckout = await ensureRazorpayLoaded();

        if (!RazorpayCheckout) {
            throw new Error('Payment SDK unavailable');
        }

        if (!props.razorpayKey || !props.razorpaySubscriptionId) {
            throw new Error('Missing payment authorization details');
        }

        const options = {
            key: props.razorpayKey,
            name: 'Akra Health',
            description: `Authorize Subscription - ${props.subscriptionPlan.title}`,
            subscription_id: props.razorpaySubscriptionId,
            handler(response) {
                handlePaymentSuccess(response);
            },
            prefill: {
                name: userDisplayName.value,
                email: props.user?.email || '',
                contact: props.user?.mobile || '',
            },
            readonly: {
                email: true,
                contact: true,
                name: false,
            },
            theme: {
                color: '#1294ea',
            },
            modal: {
                ondismiss() {
                    handlePaymentFailure('Authorization was not completed. You can retry without creating a duplicate account.');
                },
            },
        };

        const razorpayInstance = new RazorpayCheckout(options);
        razorpayInstance.open();
    } catch (error) {
        console.error('Payment initialization error:', error);

        if (props.razorpaySubscriptionUrl) {
            paymentStatus.value = {
                type: 'error',
                message: 'Embedded payment could not open in this browser. Use the secure Razorpay page below to continue authorization.',
            };
        } else {
            paymentStatus.value = {
                type: 'error',
                message: 'Failed to initialize payment. Please try again.',
            };
        }

        isProcessing.value = false;
    }
};

const retryLater = () => {
    paymentStatus.value = {
        type: 'error',
        message: 'Authorization is still pending. You can come back and complete it later using the same signup flow.',
    };
};

onMounted(() => {
    ensureRazorpayLoaded().catch(() => {
        paymentStatus.value = {
            type: 'error',
            message: props.razorpaySubscriptionUrl
                ? 'Embedded payment could not load. Use the secure Razorpay page below to continue.'
                : 'Unable to load the payment gateway. Please retry.',
        };
    });
});
</script>

<template>
    <AppLayout2 title="Payment" description="Complete your subscription authorization" :hideLogo="false">
        <section class="signup-payment-page">
            <div class="container py-5">
                <div class="payment-shell">
                    <div class="flex justify-content-center mb-2">
                        <nav class="navbar navbar-expand-lg" id="navbar">
                            <img src="/images/akrahealth.webp" alt="" />
                        </nav>
                    </div>
                    <div class="payment-hero">
                        <div>
                            <p class="payment-kicker">Secure Trial Activation</p>
                            <h1 class="payment-title">Authorize your subscription and start your 14-day free trial</h1>
                            <p class="payment-subtitle">
                                No subscription fee is charged today. Your payment method is only authorized now, and
                                the actual plan amount is auto-charged after your trial ends on
                                <strong>{{ trialDeadlineLabel }}</strong>.
                            </p>
                            <p class="payment-subtitle payment-subtitle-muted text-pink font-weight-bold">
                                A small temporary amount may be charged by your bank or payment provider to verify your
                                card or payment method. If charged, that temporary verification amount is typically
                                reversed, and your actual subscription amount is charged only after the trial period.
                            </p>
                        </div>
                        <div class="trial-badge-card">
                            <span class="trial-badge">14-Day Trial</span>
                            <div class="trial-meta-label">Charged today</div>
                            <div class="trial-meta-value">{{ subscriptionPlan.currency }} 0.00</div>
                            <div class="trial-meta-caption">First billing after trial end</div>
                        </div>
                    </div>

                    <div v-if="paymentStatus" class="status-banner"
                        :class="paymentStatus.type === 'success' ? 'status-success' : 'status-error'">
                        <i
                            :class="paymentStatus.type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle'"></i>
                        <span>{{ paymentStatus.message }}</span>
                    </div>

                    <div class="payment-grid">
                        <div class="summary-card">
                            <div class="card-header-row">
                                <div>
                                    <p class="card-eyebrow">Plan Summary</p>
                                    <h2>{{ subscriptionPlan.title }}</h2>
                                </div>
                                <span class="frequency-pill">{{ subscriptionPlan.frequency || 'Recurring' }}</span>
                            </div>

                            <div class="summary-list">
                                <div class="summary-item">
                                    <span class="summary-label">Plan amount</span>
                                    <span class="summary-value">{{ formattedAmount }}</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">Subscriber</span>
                                    <span class="summary-value">{{ userDisplayName }}</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">Email</span>
                                    <span class="summary-value">{{ user.email }}</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">Trial ends</span>
                                    <span class="summary-value">{{ trialDeadlineLabel }}</span>
                                </div>
                            </div>

                            <div class="security-strip">
                                <i class="fas fa-lock"></i>
                                <span>Secured by Razorpay with SSL-encrypted authorization</span>
                            </div>
                        </div>

                        <div class="action-card">
                            <p class="card-eyebrow">What happens next</p>
                            <ul class="timeline-list">
                                <li>
                                    <span class="timeline-step">1</span>
                                    <div>
                                        <strong>Authorize your payment method</strong>
                                        <p>Razorpay validates your payment method now without collecting the full plan
                                            fee.</p>
                                    </div>
                                </li>
                                <li>
                                    <span class="timeline-step">2</span>
                                    <div>
                                        <strong>Start your 14-day free trial</strong>
                                        <p>Your account is activated and your verification email is already on its way.
                                        </p>
                                    </div>
                                </li>
                                <li>
                                    <span class="timeline-step">3</span>
                                    <div>
                                        <strong>Auto-charge after the trial</strong>
                                        <p>The actual subscription amount of {{ formattedAmount }} is charged after the
                                            trial ends.</p>
                                    </div>
                                </li>
                            </ul>

                            <div class="action-buttons">
                                <button @click="openRazorpayCheckout" class="primary-action" :disabled="isProcessing">
                                    <span v-if="isProcessing" class="spinner-border spinner-border-sm"></span>
                                    <span>{{ isProcessing ? 'Opening Razorpay...' : 'Authorize & Start Trial' }}</span>
                                </button>

                                <button v-if="razorpaySubscriptionUrl" type="button" class="secondary-action"
                                    :disabled="isProcessing" @click="openHostedRazorpayPage">
                                    Open Secure Razorpay Page
                                </button>

                                <button type="button" class="ghost-action" :disabled="isProcessing" @click="retryLater">
                                    Retry Later
                                </button>
                            </div>

                            <p class="helper-note">
                                A small temporary verification amount may appear during authorization and is usually
                                reversed by your bank or payment provider. If the popup does not open in your browser,
                                use the secure Razorpay page option and continue from there.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout2>
</template>

<style scoped>
.signup-payment-page {
    background:
        radial-gradient(circle at top left, rgba(18, 148, 234, 0.12), transparent 28%),
        radial-gradient(circle at top right, rgba(20, 184, 166, 0.14), transparent 22%),
        #f6fbff;
    min-height: calc(100vh - 240px);
}

.payment-shell {
    max-width: 1180px;
    margin: 0 auto;
    background: rgba(255, 255, 255, 0.8);
    border: 1px solid rgba(18, 148, 234, 0.08);
    border-radius: 32px;
    box-shadow: 0 28px 60px rgba(15, 23, 42, 0.08);
    padding: 36px;
    backdrop-filter: blur(10px);
}

.payment-hero {
    display: grid;
    grid-template-columns: minmax(0, 1.7fr) minmax(280px, 0.8fr);
    gap: 24px;
    align-items: stretch;
    margin-bottom: 24px;
}

.payment-kicker,
.card-eyebrow {
    margin: 0 0 10px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #1294ea;
}

.payment-title {
    margin: 0 0 14px;
    font-size: 48px;
    line-height: 1.05;
    font-weight: 700;
    color: #0f172a;
}

.payment-subtitle {
    margin: 0;
    max-width: 820px;
    font-size: 18px;
    line-height: 1.7;
    color: #475569;
}

.payment-subtitle-muted {
    margin-top: 14px;
    font-size: 15px;
    color: #64748b;
}

.trial-badge-card {
    background: linear-gradient(160deg, #0ea5e9 0%, #1d4ed8 100%);
    color: #fff;
    border-radius: 28px;
    padding: 28px;
    box-shadow: 0 22px 40px rgba(29, 78, 216, 0.28);
}

.trial-badge {
    display: inline-flex;
    align-items: center;
    padding: 8px 14px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.14);
    border: 1px solid rgba(255, 255, 255, 0.18);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.trial-meta-label {
    margin-top: 28px;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    opacity: 0.84;
}

.trial-meta-value {
    margin-top: 6px;
    font-size: 42px;
    line-height: 1;
    font-weight: 700;
}

.trial-meta-caption {
    margin-top: 12px;
    font-size: 14px;
    line-height: 1.6;
    opacity: 0.92;
}

.status-banner {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    border-radius: 18px;
    padding: 16px 18px;
    margin-bottom: 24px;
    font-weight: 500;
}

.status-success {
    background: #ecfdf3;
    border: 1px solid #bbf7d0;
    color: #166534;
}

.status-error {
    background: #fff1f2;
    border: 1px solid #fecdd3;
    color: #be123c;
}

.payment-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.1fr) minmax(320px, 0.9fr);
    gap: 24px;
}

.summary-card,
.action-card {
    background: #fff;
    border: 1px solid rgba(148, 163, 184, 0.18);
    border-radius: 28px;
    padding: 28px;
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
}

.card-header-row {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    align-items: flex-start;
    margin-bottom: 22px;
}

.card-header-row h2 {
    margin: 0;
    font-size: 34px;
    line-height: 1.15;
    color: #0f172a;
}

.frequency-pill {
    display: inline-flex;
    align-items: center;
    padding: 10px 14px;
    border-radius: 999px;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: 13px;
    font-weight: 700;
    text-transform: capitalize;
}

.summary-list {
    display: grid;
    gap: 14px;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    padding: 15px 18px;
    background: #f8fbff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
}

.summary-label {
    color: #64748b;
    font-size: 14px;
}

.summary-value {
    color: #0f172a;
    font-weight: 600;
    text-align: right;
}

.security-strip {
    margin-top: 22px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    border-radius: 999px;
    background: #eff6ff;
    color: #1e40af;
    padding: 12px 16px;
    font-size: 14px;
    font-weight: 500;
}

.timeline-list {
    list-style: none;
    padding: 0;
    margin: 0 0 28px;
    display: grid;
    gap: 18px;
}

.timeline-list li {
    display: grid;
    grid-template-columns: 42px minmax(0, 1fr);
    gap: 14px;
    align-items: flex-start;
}

.timeline-step {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: linear-gradient(135deg, #38bdf8, #2563eb);
    color: #fff;
    font-weight: 700;
    box-shadow: 0 10px 22px rgba(37, 99, 235, 0.24);
}

.timeline-list strong {
    display: block;
    margin-bottom: 4px;
    color: #0f172a;
    font-size: 16px;
}

.timeline-list p {
    margin: 0;
    color: #64748b;
    line-height: 1.6;
}

.action-buttons {
    display: grid;
    gap: 12px;
}

.primary-action,
.secondary-action,
.ghost-action {
    border: none;
    border-radius: 16px;
    font-weight: 600;
    min-height: 54px;
    transition: transform 0.18s ease, box-shadow 0.18s ease, opacity 0.18s ease;
}

.primary-action:hover:not(:disabled),
.secondary-action:hover:not(:disabled),
.ghost-action:hover:not(:disabled) {
    transform: translateY(-1px);
}

.primary-action {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, #1294ea, #2563eb);
    color: #fff;
    box-shadow: 0 18px 30px rgba(37, 99, 235, 0.22);
}

.secondary-action {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
}

.ghost-action {
    background: #fff;
    color: #475569;
    border: 1px solid #e2e8f0;
}

.primary-action:disabled,
.secondary-action:disabled,
.ghost-action:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

.helper-note {
    margin: 16px 0 0;
    color: #64748b;
    font-size: 14px;
    line-height: 1.6;
}

.spinner-border {
    width: 1rem !important;
    height: 1rem !important;
    border-width: 0.2em;
}

@media (max-width: 991px) {
    .payment-shell {
        padding: 24px;
        border-radius: 24px;
    }

    .payment-hero,
    .payment-grid {
        grid-template-columns: 1fr;
    }

    .payment-title {
        font-size: 36px;
    }
}

@media (max-width: 575px) {
    .signup-payment-page {
        min-height: auto;
    }

    .container.py-5 {
        padding-top: 1.5rem !important;
        padding-bottom: 1.5rem !important;
    }

    .payment-shell {
        padding: 18px;
        border-radius: 22px;
    }

    .payment-title {
        font-size: 30px;
    }

    .payment-subtitle {
        font-size: 16px;
    }

    .payment-subtitle-muted {
        font-size: 14px;
    }

    .trial-meta-value {
        font-size: 36px;
    }

    .summary-item {
        flex-direction: column;
    }

    .summary-value {
        text-align: left;
    }
}
</style>
