<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router } from "@inertiajs/vue3";
import { ref,computed } from "vue";
import { useSubscriptionDaysLeft } from "@/Composables/useSubscription";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
 
const { createExpiryInfo, formatDate, isExpired, getDaysLeft } = useSubscriptionDaysLeft();

const props = defineProps({
    plans: {
        type: Array,
        default: () => []
    },
    activeSubscription: {
        type: Object,
        default: null
    },
    expiredSubscription: {
        type: Object,
        default: null
    },
    canUpgrade: {
        type: Boolean,
        default: false
    }
});

/* ---------------- BILLING OPTIONS ---------------- */
const billingOptions = [
    { value: 'monthly', label: 'Monthly' },
    { value: 'yearly', label: 'Annual' },
    { value: 'custom', label: 'Custom' }
];

const billingCycle = ref('monthly');

/* ---------------- FILTERED PLANS ---------------- */
const filteredPlans = computed(() => {
    if (!props.plans || props.plans.length === 0) return [];
    
    return props.plans.filter(plan => {
        if (billingCycle.value === 'custom') {
            // Show all custom plans or plans marked as custom
            return plan.frequency === 'custom' || plan.billing_cycle?.toLowerCase() === 'custom';
        }
        return plan.frequency === billingCycle.value;
    });
});

/* ---------------- PLAN HELPERS ---------------- */
const getPlanByTitleAndFrequency = (title, frequency) => {
    return props.plans.find(p => 
        p.title?.toLowerCase() === title.toLowerCase() && 
        p.frequency === frequency
    );
};

const getCurrentPlanForBillingCycle = (title) => {
    return getPlanByTitleAndFrequency(title, billingCycle.value);
};

const isCurrentPlan = (planId) => {
    return props.activeSubscription && props.activeSubscription.subscription_plan_id === planId;
};

const hasExpiredSubscription = computed(() => {
    return props.expiredSubscription !== null;
});

const selectPlan = (plan) => {
    if (isCurrentPlan(plan.id)) {
        alert('You are already subscribed to this plan.');
        return;
    }
    
    // If user has expired subscription and can upgrade, use upgrade route
    if (hasExpiredSubscription.value && props.canUpgrade) {
        router.visit(route('subscriptions.upgrade.show', plan.id));
        return;
    }
    
    router.visit(route('subscriptions.show', plan.id));
};

const getButtonText = (plan) => {
    if (isCurrentPlan(plan.id)) {
        return 'Current Plan';
    }
    if (hasExpiredSubscription.value && props.canUpgrade) {
        return 'Upgrade';
    }
    return 'Select Plan';
};

const getButtonClass = (plan) => {
    if (isCurrentPlan(plan.id)) {
        return 'btn-secondary disabled';
    }
    if (hasExpiredSubscription.value && props.canUpgrade) {
        return 'btn-warning';
    }
    return 'btn-primary';
};

/* ---------------- FEATURES PARSER ---------------- */
const parseFeatures = (featuresHtml) => {
    if (!featuresHtml) return [];
    
    const temp = document.createElement('div');
    temp.innerHTML = featuresHtml;
    
    return Array.from(temp.querySelectorAll('li')).map(li => li.textContent.trim());
};

const getPlanFeatures = (plan) => {
    return parseFeatures(plan?.features);
};

const buttons = [
	{
		label: "Back",
		function: () =>window.history.back(),    
		icon: "bi bi-arrow-left",
	},
];
</script>

<template>
    <AuthLayout
        :title="hasExpiredSubscription ? 'Upgrade Your Subscription' : 'Subscription Plans'"
        :description="hasExpiredSubscription ? 'Your subscription has expired. Choose a plan to upgrade.' : 'Choose the plan that\'s right for you'"
        :heading="hasExpiredSubscription ? 'Upgrade Your Subscription' : 'Subscription Plans'"
    >
    	<div class="">

			<!-- ================= DESKTOP VIEW - Title and Controls in Same Row ================= -->
			<div class="d-none d-md-flex align-items-center justify-content-between mb-3">

				<!-- Title -->
				<h3 class="text-xl mb-0">{{ hasExpiredSubscription ? 'Upgrade Your Subscription' : 'Subscription Plans' }}</h3>
				<!-- Add Button -->
				<ActionButtons :actionButtons="buttons" />
			</div>
		</div>
        
        <!-- Expired Subscription Alert -->
        <div v-if="hasExpiredSubscription && canUpgrade" class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1">Your Subscription Has Expired</h5>
                    <p class="mb-0">
                        Your subscription to <strong>{{ expiredSubscription?.subscriptionPlan?.title || 'Previous Plan' }}</strong> 
                        expired on <strong>{{ expiredSubscription?.end_date ? new Date(expiredSubscription.end_date).toLocaleDateString() : 'Unknown date' }}</strong>.
                        Please upgrade to continue using all features.
                    </p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

         <!-- Plans Grid -->
        
        <!-- Billing Cycle Toggle -->
        <div class="d-flex justify-content-center align-items-center mb-4">
            <div class="btn-group" role="group" aria-label="Billing cycle options">
                <button 
                    v-for="option in billingOptions" 
                    :key="option.value"
                    type="button"
                    class="btn"
                    :class="billingCycle === option.value ? 'btn-primary' : 'btn-outline-primary'"
                    @click="billingCycle = option.value"
                >
                    {{ option.label }}
                </button>
            </div>
        </div>
        
        <!-- Plans Grid -->
        <div class="row">

            <div v-for="plan in filteredPlans" :key="plan.id" class="col-md-4 mb-4">
                <div class="card h-100" :class="{ 
                    'border-primary': isCurrentPlan(plan.id),
                    'border-warning': hasExpiredSubscription && canUpgrade && !isCurrentPlan(plan.id)
                }">
                    <div class="card-header bg-primary text-white text-center py-3" :class="{ 'bg-warning': hasExpiredSubscription && canUpgrade && !isCurrentPlan(plan.id) }">
                        <h5 class="mb-0 text-white">{{ plan.title }}</h5>
                        <h3 class="card-title pricing-card-title text-white mb-0 mt-2">
                            {{ plan.currency }} {{ plan.price }}
                            <small class="text-white fw-normal ms-1">/{{ plan.billing_cycle?.toLowerCase() || 'mo' }}</small>
                        </h3>
                    </div>
                    <div class="card-body text-center">
                        
                        <ul class="list-unstyled mt-3 mb-4 text-start">
                            <li v-for="(feature, index) in getPlanFeatures(plan)" :key="index" class="mb-2 d-flex align-items-start">
                                <i class="fas fa-check text-success me-2 mt-1 flex-shrink-0"></i>
                                <span class="feature-text">{{ feature }}</span>
                            </li>
                            <li v-if="!getPlanFeatures(plan).length" class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Full access to all features
                            </li>
                        </ul>
                        <button 
                            @click="selectPlan(plan)"
                            class="btn btn-lg w-100"
                            :class="getButtonClass(plan)"
                            :disabled="isCurrentPlan(plan.id)"
                        >
                            <span v-if="isCurrentPlan(plan.id)">
                                <i class="fas fa-check me-2"></i>
                                Current Plan
                            </span>
                            <span v-else-if="hasExpiredSubscription && canUpgrade">
                                <i class="fas fa-arrow-up me-2"></i>
                                Upgrade
                            </span>
                            <span v-else>
                                Select Plan
                            </span>
                        </button>
                    </div>
                    <div v-if="isCurrentPlan(plan.id)" class="card-footer text-center text-white rounded-bottom">
                        <small>Active Plan</small>
                    </div>
                    <div v-else-if="hasExpiredSubscription && canUpgrade" class="card-footer text-center bg-warning text-dark rounded-bottom">
                        <small><i class="fas fa-arrow-up me-1"></i>Upgrade Available</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!plans || plans.length === 0" class="text-center py-5">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No subscription plans available. Please contact support.
            </div>
        </div>
    </AuthLayout>
</template>

<style scoped>
.feature-list {
    overflow: hidden;
}

.feature-list li {
    display: flex;
    align-items: flex-start;
    line-height: 1.6;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.feature-list li i {
    flex-shrink: 0;
    width: 20px;
    margin-top: 0.25rem;
}

.feature-list li .feature-text {
    flex: 1;
    word-break: break-word;
    overflow-wrap: break-word;
    hyphens: auto;
    max-width: 100%;
}

.list-unstyled li {
    display: flex;
    align-items: flex-start;
}

.list-unstyled li i {
    flex-shrink: 0;
    margin-top: 0.25rem;
}

.list-unstyled li .feature-text {
    flex: 1;
    word-break: break-word;
    overflow-wrap: break-word;
}
.card-footer {
    background-color:#09acff !important;
    color: #fff;
border-bottom-left-radius: calc(0.95rem - 1px)!important;
border-bottom-right-radius: calc(0.95rem - 1px)!important;
}
</style>
