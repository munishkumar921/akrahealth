<script setup>
import { computed, ref, watch } from "vue";
import AuthLayout from "@/Layouts/AuthLayout2.vue";
import { router } from '@inertiajs/vue3';

const props = defineProps({
    orders: {
        type: Object,
        default: () => []
    },
    data: Object,
    patient: Object,
    keyword: {
        type: String,
        default: ''
    }
});

const currentTab = ref("laboratory");
const searchQuery = ref(props.keyword || "");
const selectedStatus = ref("");
const sortOrder = ref("newest");
const isLoading = ref(false);

const formatDate = (dateString) => {
    if (!dateString) return 'No Date';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) {
        return dateString; // Return original string if invalid
    }
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
};

// Transform orders data into the expected structure
const transformedOrders = computed(() => {
    const result = {
        laboratory: [],
        imaging: [],
        cardiopulmonary: [],
        referrals: []
    };

    props.orders.forEach(order => {
        // Helper function to parse and format order text
        const parseOrderText = (data) => {
            if (!data) return null;

            if (Array.isArray(data)) {
                if (data.length === 0) return null;
                return data.map(item => {
                    if (typeof item === 'string') return item;
                    if (item.name) return item.name;
                    if (item.text) return item.text;
                    return JSON.stringify(item);
                }).join(', ');
            }

            if (typeof data === 'string') {
                if (data.trim() === '') return null;
                try {
                    const parsed = JSON.parse(data);
                    if (Array.isArray(parsed)) {
                        if (parsed.length === 0) return null;
                        return parsed.map(item => {
                            if (typeof item === 'string') return item;
                            if (item.name) return item.name;
                            if (item.text) return item.text;
                            return JSON.stringify(item);
                        }).join(', ');
                    } else if (typeof parsed === 'object' && parsed !== null) {
                        return parsed.name || parsed.text || JSON.stringify(parsed);
                    }
                    return parsed.toString();
                } catch (e) {
                    return data;
                }
            }

            if (typeof data === 'object' && data !== null) {
                return data.name || data.text || JSON.stringify(data);
            }

            return data.toString();
        };

        // Handle labs
        const labText = parseOrderText(order.labs);
        if (labText) {
            result.laboratory.push({
                id: order.id,
                date: formatDate(order.orders_date),
                raw_date: order.orders_date,
                text: labText,
                status: order.is_completed ? 'completed' : 'pending',
                description: order.notes || 'No description',
                type: 'laboratory',
                doctor: order.doctor?.name ||order.doctor?.user?.name || 'Unknown Doctor',
                encounter_id: order.encounter_id
            });
        }

        // Handle radiology
        const radiologyText = parseOrderText(order.radiology);
        if (radiologyText) {
            result.imaging.push({
                id: order.id,
                date: formatDate(order.orders_date),
                raw_date: order.orders_date,
                text: radiologyText,
                status: order.is_completed ? 'completed' : 'pending',
                description: order.notes || 'No description',
                type: 'imaging',
                doctor: order.doctor?.name ||order.doctor?.user?.name || 'Unknown Doctor',
                encounter_id: order.encounter_id
            });
        }

        // Handle cardiopulmonary
        const cpText = parseOrderText(order.cp);
        if (cpText) {
            result.cardiopulmonary.push({
                id: order.id,
                date: formatDate(order.orders_date),
                raw_date: order.orders_date,
                text: cpText,
                status: order.is_completed ? 'completed' : 'pending',
                description: order.notes || 'No description',
                type: 'cardiopulmonary',
                doctor: order.doctor?.name ||order.doctor?.user?.name || 'Unknown Doctor',
                encounter_id: order.encounter_id
            });
        }

        // Handle referrals
        const referralsText = parseOrderText(order.referrals);
        if (referralsText) {
            result.referrals.push({
                id: order.id,
                date: formatDate(order.orders_date),
                raw_date: order.orders_date,
                text: referralsText,
                status: order.is_completed ? 'completed' : 'pending',
                description: order.notes || 'No description',
                type: 'referrals',
                doctor: order.doctor?.name ||order.doctor?.user?.name || 'Unknown Doctor',
                encounter_id: order.encounter_id
            });
        }
    });

    return result;
});

// Get data for current tab
const computedData = computed(() => {
    return transformedOrders.value[currentTab.value] || [];
});

// Get count for each tab
const getTabCount = (tabValue) => {
    return transformedOrders.value[tabValue]?.length || 0;
};

// Get status count
const getStatusCount = (status) => {
    return computedData.value.filter(order => order.status === status).length;
};

// Filter and sort data based on search query, status, and sort order
const filteredData = computed(() => {
    let data = computedData.value;

    // Apply status filter
    if (selectedStatus.value) {
        data = data.filter(order => order.status === selectedStatus.value);
    }

    // Apply search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        data = data.filter(order =>
            order.text?.toLowerCase().includes(query) ||
            order.description?.toLowerCase().includes(query) ||
            order.date?.includes(query) ||
            order.doctor?.toLowerCase().includes(query)
        );
    }

    // Apply sorting
    data = [...data].sort((a, b) => {
        const dateA = new Date(a.raw_date);
        const dateB = new Date(b.raw_date);
        return sortOrder.value === "newest" ? dateB - dateA : dateA - dateB;
    });

    return data;
});

const tabs = [
    { value: "laboratory", label: "Laboratory", iconClass: "icon-success", icon: "fa fa-flask", color: "text-success" },
    { value: "imaging", label: "Imaging", iconClass: "icon-warning", icon: "fa fa-x-ray", color: "text-warning" },
    { value: "cardiopulmonary", label: "Cardiopulmonary", iconClass: "icon-primary", icon: "fa fa-heartbeat", color: "text-primary" },
    { value: "referrals", label: "Referrals", iconClass: "icon-secondary", icon: "fa fa-user-md", color: "text-secondary" },
];

const updateCurrentTab = (newTab) => {
    currentTab.value = newTab;
    searchQuery.value = "";
    selectedStatus.value = "";
};

// Get status color class
const getStatusClass = (status) => {
    switch (status?.toLowerCase()) {
        case 'completed':
            return 'bg-success';
        case 'pending':
            return 'bg-warning';
        case 'cancelled':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
};

// Get status badge style
const getStatusStyle = (status) => {
    switch (status?.toLowerCase()) {
        case 'completed':
            return { backgroundColor: '#28a745', color: '#fff' };
        case 'pending':
            return { backgroundColor: '#ffc107', color: '#212529' };
        case 'cancelled':
            return { backgroundColor: '#dc3545', color: '#fff' };
        default:
            return { backgroundColor: '#6c757d', color: '#fff' };
    }
};

// Clear all filters
const clearFilters = () => {
    searchQuery.value = "";
    selectedStatus.value = "";
};

// Check if any filters are active
const hasActiveFilters = computed(() => {
    return searchQuery.value || selectedStatus.value;
});

// Get current tab info
const currentTabInfo = computed(() => {
    return tabs.find(t => t.value === currentTab.value) || tabs[0];
});

// Watch for filter changes and apply
watch([searchQuery, selectedStatus, sortOrder], () => {
    // Could implement live filtering here if needed
});

</script>

<template>
    <AuthLayout 
        :title="`${currentTabInfo.label} Orders`" 
        :description="`Manage your ${currentTabInfo.label.toLowerCase()} orders`" 
        :heading="`${currentTabInfo.label} Orders`"
    >
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-lg-3">
                <div class="iq-card border-0 shadow-sm mb-4">
                    <div class="iq-card-header bg-white py-3">
                        <h5 class="card-title mb-0">
                            <i class="fa fa-list-ul me-2"></i>Order Categories
                        </h5>
                    </div>
                    <div class="iq-card-body">
                        <div class="finance-menu">
                            <button 
                                v-for="tab in tabs" 
                                :key="tab.value" 
                                type="button" 
                                class="menu-item"
                                :class="{ 
                                    active: currentTab === tab.value,
                                    'border-start border-4': currentTab === tab.value
                                }" 
                                @click="updateCurrentTab(tab.value)"
                            >
                                <div class="d-flex align-items-center w-100">
                                    <i :class="[tab.icon, tab.iconClass, tab.color]" class="me-3 fs-5"></i>
                                    <div class="flex-grow-1 text-start">
                                        <span class="label d-block">{{ tab.label }}</span>
                                        <small class="text-muted">{{ getTabCount(tab.value) }} order(s)</small>
                                    </div>
                                    <span 
                                        class="badge ms-2" 
                                        :class="currentTab === tab.value ? 'bg-primary' : 'bg-light text-dark'"
                                    >
                                        {{ getTabCount(tab.value) }}
                                    </span>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats Card -->
                <div class="iq-card border-0 shadow-sm">
                    <div class="iq-card-header bg-white py-3">
                        <h6 class="card-title mb-0">
                            <i class="fa fa-chart-pie me-2"></i>Quick Stats
                        </h6>
                    </div>
                    <div class="iq-card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">Total Orders</span>
                            <span class="badge bg-primary">{{ computedData.length }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-success">Completed</span>
                            <span class="badge bg-success">{{ getStatusCount('completed') }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-warning">Pending</span>
                            <span class="badge bg-warning text-dark">{{ getStatusCount('pending') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="iq-card border-0 shadow-sm">
                    <div class="iq-card-header bg-white d-flex flex-wrap justify-content-between align-items-center py-3">
                        <div>
                            <h6 class="card-title mb-0">
                                <i :class="[currentTabInfo.icon, currentTabInfo.color, 'me-2']"></i>
                                {{ currentTabInfo.label }} Orders
                            </h6>
                            <small class="text-muted">{{ filteredData.length }} order(s) found</small>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap mt-3 mt-lg-0">
                            <!-- Sort Dropdown -->
                            <select 
                                v-model="sortOrder" 
                                class="form-select form-select-sm"
                                style="width: auto;"
                            >
                                <option value="newest">Newest First</option>
                                <option value="oldest">Oldest First</option>
                            </select>

                            <!-- Status Filter -->
                            <select 
                                v-model="selectedStatus" 
                                class="form-select form-select-sm"
                                style="width: auto;"
                            >
                                <option value="">All Status</option>
                                <option value="completed">Completed</option>
                                <option value="pending">Pending</option>
                            </select>

                            <!-- Clear Filters Button -->
                            <button 
                                v-if="hasActiveFilters"
                                class="btn btn-outline-danger btn-sm"
                                @click="clearFilters"
                                title="Clear filters"
                            >
                                <i class="fa fa-times me-1"></i>Clear
                            </button>
                        </div>
                    </div>

                    <!-- Search Bar -->
                    <div class="px-3 pt-2 pb-0">
                        <div class="iq-search-bar">
                            <form class="searchbox">
                                <input 
                                    v-model="searchQuery" 
                                    type="search" 
                                    class="text search-input form-control"
                                    :placeholder="`Search ${currentTabInfo.label.toLowerCase()} orders...`" 
                                />
                                <div class="search-link">
                                    <i class="ri-search-line"></i>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="iq-card-body">
                        <!-- Empty State -->
                        <div v-if="filteredData.length === 0" class="text-center py-5 px-3">
                            <div class="empty-state-icon mb-3">
                                <i :class="[currentTabInfo.icon, 'fa-4x', currentTabInfo.color]"></i>
                            </div>
                            <h5 class="text-muted mb-2">
                                {{ hasActiveFilters ? 'No matching orders found' : `No ${currentTabInfo.label.toLowerCase()} orders yet` }}
                            </h5>
                            <p class="text-muted mb-0">
                                {{ hasActiveFilters ? 'Try adjusting your search or filter criteria.' : 'Your orders will appear here once created by your healthcare provider.' }}
                            </p>
                            <button 
                                v-if="hasActiveFilters"
                                class="btn btn-primary mt-3"
                                @click="clearFilters"
                            >
                                <i class="fa fa-redo me-1"></i>View All Orders
                            </button>
                        </div>

                        <!-- Orders List -->
                        <div v-else class="orders-list px-3">
                            <div 
                                v-for="order in filteredData" 
                                :key="order.id" 
                                class="order-item border rounded-3 p-3 mb-3 shadow-sm"
                            >
                                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                                    <div class="d-flex align-items-start flex-grow-1">
                                        <div class="order-icon-wrapper me-3">
                                            <i :class="[currentTabInfo.icon, 'fa-2x', currentTabInfo.color]"></i>
                                        </div>
                                        <div class="order-content flex-grow-1">
                                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                                <h6 class="mb-0 text-dark">{{ order.text || 'Untitled Order' }}</h6>
                                                <span 
                                                    class="badge" 
                                                    :style="getStatusStyle(order.status)"
                                                >
                                                    {{ order.status || 'Pending' }}
                                                </span>
                                            </div>
                                            <p class="mb-1 text-muted small">
                                                {{ order.description || 'No description available' }}
                                            </p>
                                            <div class="d-flex flex-wrap gap-3 text-muted small">
                                                <span>
                                                    <i class="fa fa-calendar-alt me-1"></i>
                                                    {{ order.date }}
                                                </span>
                                                <span v-if="order.doctor">
                                                    <i class="fa fa-user-md me-1"></i>
                                                    {{ order.doctor }}
                                                </span>
                                                <span v-if="order.encounter_id">
                                                    <i class="fa fa-file-medical me-1"></i>
                                                    Encounter #{{ order.encounter_id }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="order-actions">
                                        <button 
                                            class="btn btn-outline-primary btn-sm"
                                            title="View Details"
                                        >
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<style scoped>
.finance-menu {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.menu-item {
    display: flex;
    align-items: center;
    width: 100%;
    background: #fff;
    border: 1px solid #eef0f4;
    border-radius: 8px;
    padding: 12px 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: left;
}

.menu-item:hover {
    background: #f8f9fa;
    border-color: #d1d5db;
}

.menu-item.active {
    background: linear-gradient(135deg, #f0f4ff 0%, #e8ecf4 100%);
    border-color: #6f42c1;
    box-shadow: 0 2px 8px rgba(111, 66, 193, 0.15);
}

.menu-item.active .label {
    color: #6f42c1;
    font-weight: 600;
}

.label {
    font-size: 14px;
    color: #2b2b2b;
    transition: color 0.2s ease;
}

.badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    font-weight: 500;
}

/* Order Item Styles */
.order-item {
    transition: background-color 0.2s ease, box-shadow 0.2s ease;
    animation: fadeIn 0.3s ease;
}

.order-item:hover {
    background-color: #f8f9fa;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;
}

.order-icon-wrapper {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 12px;
    flex-shrink: 0;
}

.order-actions {
    flex-shrink: 0;
}

/* Icon Colors */
.icon-success {
    color: #28a745;
}

.icon-warning {
    color: #ffc107;
}

.icon-primary {
    color: #0d6efd;
}

.icon-secondary {
    color: #6c757d;
}

/* Empty State */
.empty-state-icon {
    opacity: 0.5;
}

/* Search Box */
.searchbox {
    position: relative;
}

.search-input {
    padding-left: 40px;
    border-radius: 8px;
    border: 1px solid #eef0f4;
}

.search-input:focus {
    border-color: #6f42c1;
    box-shadow: 0 0 0 2px rgba(111, 66, 193, 0.1);
}

.search-link {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}

/* Responsive */
@media (max-width: 768px) {
    .orders-header {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 12px;
    }

    .order-item {
        padding: 16px !important;
    }

    .order-content {
        min-width: 100%;
    }

    .iq-search-bar,
    .searchbox,
    .search-input {
        width: 100%;
    }

    .menu-item {
        padding: 14px;
    }
}

/* Card Styles */
.iq-card {
    border-radius: 12px;
    overflow: hidden;
}

.iq-card-header {
    border-bottom: 1px solid #eef0f4;
}

/* Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
