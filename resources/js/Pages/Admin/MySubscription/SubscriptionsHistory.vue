<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { Link } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import { computed } from "vue";

const props = defineProps({
    subscriptions: {
        type: Array,
        default: () => []
    }
});

// Format currency helper function
const formatCurrency = (amount, currency) => {
    if (!amount) return 'N/A';
    const currencySymbols = {
        'USD': '$',
        'EUR': '€',
        'GBP': '£',
        'INR': '₹'
    };
    const symbol = currencySymbols[currency] || currency || '$';
    return `${symbol}${Number(amount).toFixed(2)}`;
};

// Get status badge class
const getStatusClass = (status) => {
    const statusMap = {
        'active': 'badge bg-success',
        'expired': 'badge bg-danger',
        'pending': 'badge bg-warning',
        'cancelled': 'badge bg-secondary',
        'completed': 'badge bg-info',
        'trial': 'badge bg-primary',
        'unpaid': 'badge bg-warning',
        'paid': 'badge bg-success',
        'failed': 'badge bg-danger'
    };
    
    const lowerStatus = status?.toLowerCase();
    return statusMap[lowerStatus] || 'badge bg-secondary';
};

// Transform subscriptions for table display
const tableData = computed(() => {
    if (!props.subscriptions || props.subscriptions.length === 0) {
        return [];
    }
    
    return props.subscriptions.map(subscription => ({
        id: subscription.id,
        plan_name: subscription.subscription_plan?.title || 'N/A',
        plan_features: subscription.subscription_plan?.features || 'No features listed',
        billing_cycle: subscription.subscription_plan?.frequency || 'N/A',
        status: subscription.status || 'pending',
        start_date: subscription.start_date || 'N/A',
        end_date: subscription.end_date || 'N/A',
        amount: subscription.amount ? formatCurrency(subscription.amount, subscription.currency) : 'N/A',
        payment_status: subscription.payment_status || 'unpaid',
        created_at: subscription.created_at || 'N/A',
        is_current: subscription.status === 'active'
    }));
});

const columns = [
    { label: "Plan Name", key: "plan_name" },
    { label: "Billing Cycle", key: "billing_cycle" },
    { label: "Status", key: "status" },
    { label: "Start Date", key: "start_date" },
    { label: "End Date", key: "end_date" },
    { label: "Amount", key: "amount" },
    { label: "Payment Status", key: "payment_status" },
    { label: "Created", key: "created_at" },
];
</script>

<template>
    <AuthLayout
        title="Subscription History"
        description="My Subscription History"
        heading="Subscription History"
    >
        <div class="container-fluid px-4">
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-end">
                    <Link 
                        :href="route('admin.subscription')"
                        class="btn btn-primary btn-sm"
                    >
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Subscription
                    </Link>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-history me-2"></i>Subscription History
                            </h5>
                            <span class="badge bg-light text-dark">
                                {{ subscriptions?.length || 0 }} Records
                            </span>
                        </div>
                        <div class="card-body">
                            <Table
                                v-if="tableData.length > 0"
                                :columns="columns"
                                :data="{ data: tableData }"
                                :searchShow="true"
                                :PageOptions="true"
                            >
                                <template #status="{ row }">
                                    <span :class="getStatusClass(row.status)">
                                        {{ row.status || 'N/A' }}
                                    </span>
                                </template>
                                <template #payment_status="{ row }">
                                    <span :class="getStatusClass(row.payment_status)">
                                        {{ row.payment_status || 'N/A' }}
                                    </span>
                                </template>
                                <template #plan_name="{ row }">
                                    <div>
                                        <span class="fw-bold">{{ row.plan_name }}</span>
                                        <small v-if="row.is_current" class="d-block text-success">
                                            <i class="fas fa-check-circle me-1"></i>Current
                                        </small>
                                    </div>
                                </template>
                                <template #amount="{ row }">
                                    <span class="fw-bold">{{ row.amount }}</span>
                                </template>
                            </Table>

                            <div v-else class="text-center py-5">
                                <i class="fas fa-history fa-4x text-muted mb-3"></i>
                                <p class="text-muted">No subscription history found.</p>
                                <Link 
                                    :href="route('subscriptions.index')"
                                    class="btn btn-primary"
                                >
                                    <i class="fas fa-plus me-1"></i>
                                    Subscribe Now
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

