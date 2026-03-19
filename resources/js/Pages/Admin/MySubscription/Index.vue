<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { router, Link } from "@inertiajs/vue3";
import { computed } from "vue";
import { useSubscriptionDaysLeft } from "@/Composables/useSubscription";

const { getDaysLeft, createExpiryInfo } = useSubscriptionDaysLeft();

const props = defineProps({
    subscription: Object,
    subscriptionPlans: Array,
    isViewingAsAdmin: {
        type: Boolean,
        default: false
    }
});

const upgradePlan = () => {
    // Navigate to user subscription plans page to upgrade
    router.visit(route('subscriptions.index'));
};

const subscriptionExpiry = computed(() => {
    if (!props.subscription) return null;
    return createExpiryInfo(props.subscription.end_date);
});

const getStatusClass = (status) => {
    const map = {
        active: 'bg-success',
        expired: 'bg-danger',
        pending: 'bg-warning text-dark',
        cancelled: 'bg-secondary',
        paid: 'bg-success',
        bridges: 'bg-info',
    };
    return map[status?.toLowerCase()] || 'bg-info';
};

const subscriptionData = computed(() => {
    if (!props.subscription) return [];

    return [
        {
            field: "Current Plan",
            value: props.subscription?.subscription_plan?.title || 'N/A'
        },
        {
            field: "Billing Cycle",
            value: props.subscription?.subscription_plan?.frequency || 'N/A'
        },
        {
            field: "Status",
            value: props.subscription?.status || 'N/A',
            class: getStatusClass(props.subscription?.status)
        },
        {
            field: "Start Date",
            value: props.subscription?.start_date || 'N/A'
        },
        {
            field: "End Date",
            value: subscriptionExpiry.value?.isExpired
                ? `${subscriptionExpiry.value.endDate} (Expired)`
                : subscriptionExpiry.value?.isExpiringSoon
                    ? `${subscriptionExpiry.value.endDate} (Expiring Soon)`
                    : subscriptionExpiry.value?.endDate || props.subscription?.end_date || 'N/A'
        },
        {
            field: "Days Left",
            value: getDaysLeft(props.subscription?.end_date) || 'N/A'
        },
        {
            field: "Amount",
            value: props.subscription?.amount ? `${props.subscription.amount} ${props.subscription.currency || ''}` : 'N/A'
        },
        {
            field: "Payment Status",
            value: props.subscription?.payment_status || 'N/A',
            class: getStatusClass(props.subscription?.payment_status)
        }
    ];
});
</script>

<template>
    <AuthLayout title="My Subscription" description="My Subscription" heading="My Subscription">
        <div class="container-fluid px-4">
            <div class="row">
                <div class="col-12">

                    <div class="card">
                        <div
                            class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                            <h5 class="mb-0 text-white"><i class="fas fa-credit-card me-2"></i>My Subscription</h5>
                            <div>
                                <button v-if="subscription" @click="upgradePlan" class="btn btn-light btn-sm me-2">
                                    <i class="fas fa-arrow-up me-1"></i>
                                    Upgrade Plan
                                </button>
                                <Link :href="route('admin.subscriptions.history')" class="btn btn-light btn-sm">
                                    <i class="fas fa-history me-1"></i>
                                    History
                                </Link>
                            </div>
                        </div>
                        <div class="card-body">
                            <div v-if="subscription">
                                <dl class="row">
                                    <template v-for="(row, i) in subscriptionData" :key="i">
                                        <dt class="col-sm-3 text-muted">{{ row.field }}</dt>
                                        <dd class="col-sm-9">
                                            <span v-if="row.field === 'Status' || row.field === 'Payment Status'" class="badge text-capitalize" :class="row.class">{{ row.value }}</span>
                                            <span v-else>{{ row.value }}</span>
                                        </dd>
                                    </template>
                                </dl>
                            </div>

                            <div v-else class="text-center py-5">
                                <i class="fas fa-credit-card fa-4x text-muted mb-3"></i>
                                <p class="text-muted">No active subscription found.</p>
                                <button @click="upgradePlan" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>
                                    Subscribe Now
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
