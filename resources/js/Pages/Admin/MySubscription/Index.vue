<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, Link } from "@inertiajs/vue3";
import { computed } from "vue";
import { useSubscriptionDaysLeft } from "@/Composables/useSubscription";

const { getDaysLeft, createExpiryInfo } = useSubscriptionDaysLeft();

const props = defineProps({
    subscription: Object,
    subscriptionPlans: {
        type: Array,
        default: () => [],
    },
    isViewingAsAdmin: {
        type: Boolean,
        default: false,
    },
});

const upgradePlan = () => {
    router.visit(route("subscriptions.index"));
};

const subscriptionExpiry = computed(() => {
    if (!props.subscription) return null;
    return createExpiryInfo(props.subscription.end_date);
});

const currentPlan = computed(() => props.subscription?.subscription_plan || null);

const statusLabel = computed(() => {
    if (!props.subscription?.status) return "No active plan";
    return props.subscription.status;
});

const paymentStatusLabel = computed(() => props.subscription?.payment_status || "Unpaid");

const daysLeftLabel = computed(() => {
    if (!props.subscription?.end_date) return "N/A";
    const daysLeft = getDaysLeft(props.subscription.end_date);
    if (subscriptionExpiry.value?.isExpired) return "Expired";
    return `${daysLeft} day${daysLeft === 1 ? "" : "s"} left`;
});

const expiryHeadline = computed(() => {
    if (!subscriptionExpiry.value) return "No active subscription";
    if (subscriptionExpiry.value.isExpired) return "Subscription expired";
    if (subscriptionExpiry.value.isExpiringSoon) return "Subscription expiring soon";
    return `Active until ${subscriptionExpiry.value.endDate}`;
});

const stats = computed(() => {
    if (!props.subscription) {
        return [
            { label: "Plan", value: "No plan" },
            { label: "Billing", value: "N/A" },
            { label: "Renewal", value: "N/A" },
            { label: "Amount", value: "N/A" },
        ];
    }

    return [
        { label: "Plan", value: currentPlan.value?.title || "N/A" },
        { label: "Billing", value: currentPlan.value?.frequency || "N/A" },
        { label: "Renewal", value: subscriptionExpiry.value?.endDate || props.subscription.end_date || "N/A" },
        { label: "Amount", value: props.subscription.amount ? `${props.subscription.amount} ${props.subscription.currency || ""}` : "N/A" },
    ];
});

const availablePlans = computed(() =>
    (props.subscriptionPlans || []).map((plan) => ({
        ...plan,
        isCurrent: currentPlan.value?.id === plan.id,
        priceLabel: plan.price ? `${plan.price} ${plan.currency || ""}` : "Custom",
    }))
);

const getBadgeClass = (status) => {
    const value = String(status || "").toLowerCase();
    if (["active", "paid"].includes(value)) return "status-pill--active";
    if (["pending", "expiring soon"].includes(value)) return "status-pill--pending";
    return "status-pill--inactive";
};

const formatFrequency = (frequency) => {
    if (!frequency) return "Flexible";
    return String(frequency).replace(/_/g, " ");
};
</script>

<template>
    <AuthLayout title="My Subscription" description="My Subscription" heading="My Subscription">
        <div class="subscription-page">
            <section class="subscription-hero">
                <div class="subscription-hero__content">
                    <p class="eyebrow">Subscription Overview</p>
                    <h1>{{ currentPlan?.title || "No Active Plan" }}</h1>
                    <p class="hero-copy">
                        {{ expiryHeadline }}
                    </p>

                    <div class="hero-badges">
                        <span class="status-pill" :class="getBadgeClass(statusLabel)">
                            {{ statusLabel }}
                        </span>
                        <span class="status-pill" :class="getBadgeClass(paymentStatusLabel)">
                            {{ paymentStatusLabel }}
                        </span>
                        <span v-if="props.subscription" class="subtle-chip">
                            {{ daysLeftLabel }}
                        </span>
                    </div>

                    <div class="hero-actions">
                        <button class="btn btn-light subscription-cta" @click="upgradePlan">
                            {{ props.subscription ? "Upgrade Plan" : "Subscribe Now" }}
                        </button>
                        <Link :href="route('admin.subscriptions.history')" class="btn btn-outline-light subscription-secondary">
                            View History
                        </Link>
                    </div>

                    <p v-if="isViewingAsAdmin" class="admin-note">
                        You are viewing this subscription while switched into doctor mode.
                    </p>
                </div>

                <div class="subscription-hero__panel">
                    <div class="metric-card" v-for="item in stats" :key="item.label">
                        <span class="metric-label">{{ item.label }}</span>
                        <strong class="metric-value">{{ item.value }}</strong>
                    </div>
                </div>
            </section>

            <div class="row g-4 mt-1">
                <div class="col-12">
                    <section class="info-card">
                        <div class="info-card__header">
                            <div>
                                <p class="eyebrow mb-1">Current Subscription</p>
                                <h3 class="mb-0">Plan details</h3>
                            </div>
                        </div>

                        <div v-if="subscription" class="details-grid">
                            <div class="detail-item">
                                <span class="detail-label">Plan Name</span>
                                <span class="detail-value">{{ currentPlan?.title || "N/A" }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Billing Cycle</span>
                                <span class="detail-value">{{ formatFrequency(currentPlan?.frequency) }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Start Date</span>
                                <span class="detail-value">{{ subscription.start_date || "N/A" }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">End Date</span>
                                <span class="detail-value">
                                    {{ subscriptionExpiry?.endDate || subscription.end_date || "N/A" }}
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Amount</span>
                                <span class="detail-value">
                                    {{ subscription.amount ? `${subscription.amount} ${subscription.currency || ""}` : "N/A" }}
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Days Remaining</span>
                                <span class="detail-value">{{ daysLeftLabel }}</span>
                            </div>
                        </div>

                        <div v-else class="empty-panel">
                            <div class="empty-panel__icon">
                                <i class="bi bi-credit-card-2-front"></i>
                            </div>
                            <h4>No active subscription found</h4>
                            <p>
                                Choose a plan to unlock subscription-based features and start managing billing from one place.
                            </p>
                            <button @click="upgradePlan" class="btn btn-primary">
                                Subscribe Now
                            </button>
                        </div>
                    </section>
                </div>
            </div>

            <section class="plans-section">
                <div class="section-head">
                    <div>
                        <p class="eyebrow mb-1">Available Plans</p>
                        <h3 class="mb-0">Upgrade options</h3>
                    </div>
                    <button class="btn btn-outline-primary" @click="upgradePlan">
                        Manage Plans
                    </button>
                </div>

                <div class="plans-grid">
                    <article
                        v-for="plan in availablePlans"
                        :key="plan.id"
                        class="plan-card"
                        :class="{ 'plan-card--current': plan.isCurrent }"
                    >
                        <div class="plan-card__top">
                            <div>
                                <h4>{{ plan.title }}</h4>
                                <p class="plan-frequency">{{ formatFrequency(plan.frequency) }}</p>
                            </div>
                            <span v-if="plan.isCurrent" class="current-pill">Current</span>
                        </div>

                        <div class="plan-price">{{ plan.priceLabel }}</div>
                        <p class="plan-meta">{{ plan.plan_for || "Subscription plan" }}</p>

                        <button
                            class="btn w-100"
                            :class="plan.isCurrent ? 'btn-outline-secondary' : 'btn-primary'"
                            @click="upgradePlan"
                        >
                            {{ plan.isCurrent ? "Current Plan" : "Choose Plan" }}
                        </button>
                    </article>
                </div>
            </section>
        </div>
    </AuthLayout>
</template>

<style scoped>
.subscription-page {
    padding-bottom: 2rem;
}

.subscription-hero {
    display: grid;
    grid-template-columns: minmax(0, 1.5fr) minmax(320px, 0.9fr);
    gap: 1.5rem;
    padding: 2rem;
    border-radius: 28px;
    background:
        radial-gradient(circle at top right, rgba(32, 169, 247, 0.1), transparent 26%),
        linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    color: #0f172a;
    border: 1px solid #e1edf8;
    box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
}

.subscription-hero__content h1 {
    margin: 0;
    font-size: clamp(2rem, 4vw, 3.2rem);
    line-height: 1.05;
    font-weight: 800;
    letter-spacing: -0.03em;
}

.eyebrow {
    margin: 0 0 0.4rem;
    font-size: 0.78rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    font-weight: 700;
    opacity: 1;
    color: #6b7ea5;
}

.hero-copy {
    max-width: 48rem;
    margin: 0.9rem 0 0;
    font-size: 1.02rem;
    line-height: 1.7;
    color: #64748b;
}

.hero-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 1.4rem;
}

.hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.8rem;
    margin-top: 1.5rem;
}

.subscription-cta,
.subscription-secondary {
    min-width: 150px;
    border-radius: 999px;
    padding-inline: 1rem;
    font-weight: 600;
}

.subscription-secondary {
    background: #ffffff;
    border-color: #cbd9ea;
    color: #3b4a67;
}

.subscription-secondary:hover {
    background: #f1f6fb;
    color: #0f172a;
}

.subtle-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.45rem 0.85rem;
    border-radius: 999px;
    background: #eef6ff;
    color: #2b6cb0;
    font-size: 0.82rem;
    font-weight: 600;
    border: 1px solid #d7e6f5;
}

.admin-note {
    margin-top: 1rem;
    color: #718096;
    font-size: 0.92rem;
}

.subscription-hero__panel {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.9rem;
    align-content: start;
}

.metric-card {
    padding: 1rem 1.1rem;
    border-radius: 20px;
    background: #f8fbff;
    border: 1px solid #dce8f4;
}

.metric-label {
    display: block;
    margin-bottom: 0.55rem;
    font-size: 0.78rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #7a8ca8;
}

.metric-value {
    display: block;
    font-size: 1rem;
    line-height: 1.5;
    color: #0f172a;
}

.info-card,
.plans-section {
    background: #ffffff;
    border-radius: 24px;
    padding: 1.5rem;
    box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
}

.info-card__header,
.section-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.detail-item {
    padding: 1rem;
    border-radius: 18px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}

.detail-label {
    display: block;
    margin-bottom: 0.45rem;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #64748b;
    font-weight: 700;
}

.detail-value {
    display: block;
    color: #0f172a;
    font-size: 0.98rem;
    font-weight: 600;
    line-height: 1.5;
}

.empty-panel {
    border-radius: 20px;
    background: #f8fafc;
    border: 1px dashed #cbd5e1;
    padding: 2rem 1.2rem;
    text-align: center;
    color: #64748b;
}

.empty-panel__icon {
    width: 68px;
    height: 68px;
    margin: 0 auto 1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 20px;
    background: #e2e8f0;
    color: #0f172a;
    font-size: 1.6rem;
}

.plans-section {
    margin-top: 1.5rem;
}

.plans-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
}

.plan-card {
    padding: 1.25rem;
    border-radius: 22px;
    border: 1px solid #e2e8f0;
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.plan-card--current {
    border-color: #14b8a6;
    box-shadow: inset 0 0 0 1px rgba(20, 184, 166, 0.22);
}

.plan-card__top {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: flex-start;
}

.plan-card h4 {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 700;
    color: #0f172a;
}

.plan-frequency,
.plan-meta {
    margin: 0;
    color: #64748b;
    font-size: 0.9rem;
}

.plan-price {
    font-size: 1.65rem;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.02em;
}

.current-pill {
    display: inline-flex;
    align-items: center;
    padding: 0.38rem 0.7rem;
    border-radius: 999px;
    background: #ccfbf1;
    color: #115e59;
    font-size: 0.75rem;
    font-weight: 700;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 88px;
    padding: 0.42rem 0.8rem;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: capitalize;
}

.status-pill--active {
    background: #dcfce7;
    color: #166534;
}

.status-pill--pending {
    background: #fef3c7;
    color: #92400e;
}

.status-pill--inactive {
    background: #e2e8f0;
    color: #475569;
}

@media (max-width: 1199.98px) {
    .subscription-hero {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 767.98px) {
    .subscription-hero {
        padding: 1.35rem;
        border-radius: 24px;
    }

    .subscription-hero__panel,
    .details-grid {
        grid-template-columns: 1fr;
    }

    .info-card,
    .plans-section {
        padding: 1.2rem;
        border-radius: 20px;
    }
}
</style>
