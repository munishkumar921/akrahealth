<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    order: {
        type: Object,
        required: true
    }
});

// Helper function to parse and format order text
const parseOrderText = (data) => {
    if (!data) return [];

    if (Array.isArray(data)) {
        return data.map(item => {
            if (typeof item === 'string') return item;
            if (item.name) return item.name;
            if (item.text) return item.text;
            return JSON.stringify(item);
        });
    }

    if (typeof data === 'string') {
        if (data.trim() === '') return [];
        try {
            const parsed = JSON.parse(data);
            if (Array.isArray(parsed)) {
                return parsed.map(item => {
                    if (typeof item === 'string') return item;
                    if (item.name) return item.name;
                    if (item.text) return item.text;
                    return JSON.stringify(item);
                });
            } else if (typeof parsed === 'object' && parsed !== null) {
                return [parsed.name || parsed.text || JSON.stringify(parsed)];
            }
            return [parsed.toString()];
        } catch (e) {
            return [data];
        }
    }

    if (typeof data === 'object' && data !== null) {
        return [data.name || data.text || JSON.stringify(data)];
    }

    return [data.toString()];
};

// Get order type based on available fields
const orderType = computed(() => {
    const order = props.order;
    if (order.labs) return 'Laboratory';
    if (order.radiology) return 'Imaging';
    if (order.cp) return 'Cardiopulmonary';
    if (order.referrals) return 'Referral';
    return 'Order';
});

// Get order type icon
const orderTypeIcon = computed(() => {
    switch (orderType.value) {
        case 'Laboratory': return 'fa-flask';
        case 'Imaging': return 'fa-x-ray';
        case 'Cardiopulmonary': return 'fa-heartbeat';
        case 'Referral': return 'fa-user-md';
        default: return 'fa-file-medical';
    }
});

// Get order type color
const orderTypeColor = computed(() => {
    switch (orderType.value) {
        case 'Laboratory': return 'success';
        case 'Imaging': return 'warning';
        case 'Cardiopulmonary': return 'primary';
        case 'Referral': return 'secondary';
        default: return 'info';
    }
});

// Get status class
const getStatusClass = (status) => {
    if (status === 1 || status === true || status === 'completed') {
        return 'bg-success';
    }
    return 'bg-warning';
};

// Get status text
const getStatusText = (status) => {
    if (status === 1 || status === true || status === 'completed') {
        return 'Completed';
    }
    return 'Pending';
};

// Get order items based on type
const orderItems = computed(() => {
    const order = props.order;
    if (order.labs) return parseOrderText(order.labs);
    if (order.radiology) return parseOrderText(order.radiology);
    if (order.cp) return parseOrderText(order.cp);
    if (order.referrals) return parseOrderText(order.referrals);
    return [];
});

// Get ICD codes based on type
const icdCodes = computed(() => {
    const order = props.order;
    if (order.labs && order.labs_icd) return parseOrderText(order.labs_icd);
    if (order.radiology && order.radiology_icd) return parseOrderText(order.radiology_icd);
    if (order.cp && order.cp_icd) return parseOrderText(order.cp_icd);
    if (order.referrals && order.referrals_icd) return parseOrderText(order.referrals_icd);
    return [];
});
</script>

<template>
    <AuthLayout :title="`${orderType} Order Details`" :description="`View details of your ${orderType.toLowerCase()} order`" :heading="`${orderType} Order Details`">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="iq-card border-0 shadow-sm">
                    <!-- Order Header -->
                    <div class="iq-card-header bg-white d-flex flex-wrap justify-content-between align-items-center p-4 border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="order-icon-wrapper me-3">
                                <i :class="['fa-2x', `fa-${orderTypeIcon}`, `text-${orderTypeColor}`]"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">{{ orderType }} Order</h5>
                                <span class="badge" :class="getStatusClass(order.is_completed)">
                                    {{ getStatusText(order.is_completed) }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-3 mt-lg-0">
                            <a :href="route('patient.orders')" class="btn btn-outline-secondary btn-sm">
                                <i class="fa fa-arrow-left me-1"></i> Back to Orders
                            </a>
                        </div>
                    </div>

                    <div class="iq-card-body p-4">
                        <!-- Order Info -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <h6 class="text-muted mb-2">Order Date</h6>
                                <p class="mb-0 fw-bold">{{ order.orders_date || order.created_at }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Order ID</h6>
                                <p class="mb-0 fw-bold font-monospace">{{ order.id }}</p>
                            </div>
                        </div>

                        <!-- Provider Info -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <h6 class="text-muted mb-2">Ordering Provider</h6>
                                <p class="mb-0">
                                    <i class="fa fa-user-md me-2 text-primary"></i>
                                    {{ order.doctor?.name || order.doctor?.user?.name || 'Unknown Provider' }}
                                </p>
                            </div>
                            <div class="col-md-6" v-if="order.encounter_id">
                                <h6 class="text-muted mb-2">Encounter</h6>
                                <p class="mb-0">
                                    <i class="fa fa-file-medical me-2 text-primary"></i>
                                    <a :href="route('patient.encounters.show', order.encounter_id)">
                                        View Encounter #{{ order.encounter_id.substring(0, 8) }}...
                                    </a>
                                </p>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="order-section mb-4" v-if="orderItems.length > 0">
                            <h6 class="text-muted mb-3">
                                <i :class="[`fa-${orderTypeIcon}`, `text-${orderTypeColor}`, 'me-2']"></i>
                                {{ orderType }} Items
                            </h6>
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <ul class="list-unstyled m-0">
                                        <li v-for="(item, index) in orderItems" :key="index" class="mb-2 d-flex align-items-start">
                                            <i class="fa fa-check-circle text-success me-2 mt-1"></i>
                                            <span>{{ item }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- ICD Codes -->
                        <div class="order-section mb-4" v-if="icdCodes.length > 0">
                            <h6 class="text-muted mb-3">
                                <i class="fa fa-code me-2 text-info"></i>
                                ICD Codes
                            </h6>
                            <div class="d-flex flex-wrap gap-2">
                                <span v-for="(code, index) in icdCodes" :key="index" class="badge bg-info text-dark">
                                    {{ code }}
                                </span>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="order-section mb-4" v-if="order.notes">
                            <h6 class="text-muted mb-3">
                                <i class="fa fa-sticky-note me-2 text-warning"></i>
                                Notes
                            </h6>
                            <div class="card bg-light-warning border-0">
                                <div class="card-body">
                                    <p class="mb-0">{{ order.notes }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Labs Obtained -->
                        <div class="order-section mb-4" v-if="order.labs_obtained">
                            <h6 class="text-muted mb-3">
                                <i class="fa fa-vial me-2 text-success"></i>
                                Labs Obtained
                            </h6>
                            <div class="card bg-light-success border-0">
                                <div class="card-body">
                                    <p class="mb-0">{{ order.labs_obtained }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Date -->
                        <div class="order-section" v-if="order.pending_date">
                            <h6 class="text-muted mb-3">
                                <i class="fa fa-clock me-2 text-secondary"></i>
                                Pending Date
                            </h6>
                            <p class="mb-0">{{ order.pending_date }}</p>
                        </div>
                    </div>
                </div>

                <!-- Related Results Section -->
                <div class="iq-card border-0 shadow-sm mt-4" v-if="orderType === 'Laboratory'">
                    <div class="iq-card-header bg-white p-4 border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="fa fa-flask me-2 text-success"></i>
                            Related Test Results
                        </h5>
                    </div>
                    <div class="iq-card-body p-4">
                        <p class="text-muted text-center mb-0">
                            Test results will appear here once available.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<style scoped>
.order-icon-wrapper {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 12px;
}

.bg-light-success {
    background-color: #d4edda !important;
}

.bg-light-warning {
    background-color: #fff3cd !important;
}

.badge {
    font-size: 0.75rem;
    padding: 0.35rem 0.65rem;
}

.card {
    border-radius: 8px;
}
</style>

