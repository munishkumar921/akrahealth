<script setup>
import { computed, ref, onMounted } from "vue";
import AuthLayout from "@/Layouts/AuthLayout2.vue";
import { router } from '@inertiajs/vue3';
import Swal from "sweetalert2";
const props = defineProps({
    orders: {
        type: Object,
        default: () => []
    },
    data: Object,
    patient: Object,
});

const currentTab = ref("laboratory");
const searchQuery = ref("");
const isLoading = ref(false);

// Use real data if available, otherwise fall back to mock data
const isMockData = computed(() => !(props.orders && props.orders.length > 0));

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
                date: order.pending_date,
                text: labText,
                status: order.is_completed ? 'completed' : 'pending',
                description: order.notes || 'No description',
                type: 'laboratory'
            });
        }

        // Handle radiology
        const radiologyText = parseOrderText(order.radiology);
        if (radiologyText) {
            result.imaging.push({
                id: order.id,
                date: order.pending_date,
                text: radiologyText,
                status: order.is_completed ? 'completed' : 'pending',
                description: order.notes || 'No description',
                type: 'imaging'
            });
        }

        // Handle cardiopulmonary
        const cpText = parseOrderText(order.cp);
        if (cpText) {
            result.cardiopulmonary.push({
                id: order.id,
                date: order.pending_date,
                text: cpText,
                status: order.is_completed ? 'completed' : 'pending',
                description: order.notes || 'No description',
                type: 'cardiopulmonary'
            });
        }

        // Handle referrals
        const referralsText = parseOrderText(order.referrals);
        if (referralsText) {
            result.referrals.push({
                id: order.id,
                date: order.pending_date,
                text: referralsText,
                status: order.is_completed ? 'completed' : 'pending',
                description: order.notes || 'No description',
                type: 'referrals'
            });
        }
    });

    // Debug: Log the transformed result
    console.log('Transformed orders result:', result);

    return result;
});

// Use real data if available, otherwise fall back to mock data
const computedData = computed(() => {
    return transformedOrders.value[currentTab.value] || [];
});

// Get count for each tab
const getTabCount = (tabValue) => {
    return transformedOrders.value[tabValue]?.length || 0;
};

// Filter data based on search query
const filteredData = computed(() => {
    if (!searchQuery.value) return computedData.value;
    return computedData.value.filter(order =>
        order.text?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        order.date?.includes(searchQuery.value)
    );
});

const tabs = [
    { value: "laboratory", label: "Laboratory", iconClass: "icon-success", icon: "fa fa-flask" },
    { value: "imaging", label: "Imaging", iconClass: "icon-warning", icon: "fa fa-x-ray" },
    { value: "cardiopulmonary", label: "Cardiopulmonary", iconClass: "icon-primary", icon: "fa fa-heartbeat" },
    { value: "referrals", label: "Referrals", iconClass: "icon-secondary", icon: "fa fa-user-md" },
];

const updateCurrentTab = (newTab) => {
    currentTab.value = newTab;
    searchQuery.value = ""; // Clear search when switching tabs
};

const editOrder = (order) => {
    router.get(route('doctor.orders.edit', order.id));
};

const completeOrder = (order) => {
        Swal.fire(confirmSettings('Are you sure to complete this order?')).then((result) => {
            router.post(route('doctor.orders.complete', order.id), {}, {
            onSuccess: () => {
            }
        });
  
        })
        
};

const deleteOrder = (order) => {
    Swal.fire(confirmSettings('Are you sure to delete this data?', 'You want be get it back')).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('doctor.orders.destroy', order.id));
            
        }
    })
        
};


const openLabOrderModal = () => {
    router.get(route('doctor.orders.create', { type: 'labs' }), {}, {
        onSuccess: () => {
            console.log("Lab order modal opened");
        },
    });
};



const openImagingOrderModal = () => {
    router.get(route('doctor.orders.create', { type: 'imaging' }), {
        onSuccess: () => {
        },
    });
};


const openCardiopulmonaryOrderModal = () => {
    router.get(route('doctor.orders.create', { type: 'cardiopulmonary' }), {
        onSuccess: () => {
        },
    });
};


const openReferralOrderModal = () => {
    router.get(route('doctor.orders.create', { type: 'referrals' }), {
        onSuccess: () => {
        },
    });
};


// Get status color based on order status
const getStatusColor = (status) => {
    switch (status?.toLowerCase()) {
        case 'completed':
            return 'success';
        case 'pending':
            return 'warning';
        case 'cancelled':
            return 'danger';
        default:
            return 'primary';
    }
};

</script>

<template>
    <AuthLayout title="Orders" description="Patient Orders Management" heading="Orders">
        <!-- Patient Info Header -->
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-lg-3">
                <div class="iq-card">
                    <div class="iq-card-body">
                        <h5 class="card-title mb-3">Order Categories</h5>
                        <div class="finance-menu">
                            <button v-for="tab in tabs" :key="tab.value" type="button" class="menu-item"
                                :class="{ active: currentTab === tab.value }" @click="updateCurrentTab(tab.value)">
                                <i :class="tab.icon + ' ' + tab.
                                    iconClass" class="me-2"></i>

                                <span class="label">{{ tab.label }}</span>
                                <span class="badge bg-secondary ms-auto">{{ getTabCount(tab.value) }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="iq-card">
                    <div class="iq-card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">{{tabs.find(t => t.value === currentTab)?.label}} Orders</h6>
                            <small class="text-muted">{{ filteredData.length }} order(s) found</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <!-- Search Bar -->
                            <div class="iq-search-bar">
                                <form class="searchbox">
                                    <input v-model="searchQuery" type="search" class="text search-input"
                                        placeholder="Search orders..." />
                                    <div class="search-link">
                                        <i class="ri-search-line"></i>
                                    </div>
                                </form>
                            </div>

                            <!-- Add Order Dropdown -->
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                    <i class="fa-solid fa-plus me-1"></i>
                                    Add Order
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <button class="dropdown-item" @click="openLabOrderModal">
                                        <i class="fa fa-flask me-2"></i>Add Lab Order
                                    </button>
                                    <button class="dropdown-item" @click="openImagingOrderModal">
                                        <i class="fa fa-x-ray me-2"></i>Add Imaging Order
                                    </button>
                                    <button class="dropdown-item" @click="openCardiopulmonaryOrderModal">
                                        <i class="fa fa-heartbeat me-2"></i>Add Cardiopulmonary Order
                                    </button>
                                    <button class="dropdown-item" @click="openReferralOrderModal">
                                        <i class="fa fa-user-md me-2"></i>Add Referral
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="iq-card-body">
                        <!-- Loading State -->
                        <div v-if="isLoading" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading orders...</p>
                        </div>

                        <!-- Empty State -->
                        <div v-else-if="filteredData.length === 0" class="text-center py-5">
                            <i class="fa fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No orders found</h5>
                            <p class="text-muted">
                                {{ searchQuery ? 'Try adjusting your search criteria.' : 'Start by adding a new order.'
                                }}
                            </p>
                            <button v-if="!searchQuery" class="btn btn-primary" @click="openLabOrderModal">
                                <i class="fa fa-plus me-1"></i>Add First Order
                            </button>
                        </div>

                        <!-- Orders Table -->
                        <div v-else class="table-responsive">
                            <table class="table table-striped">

                                <tbody>
                                    <tr v-for="order in filteredData" :key="order.id || order.text">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <i :class="tabs.find(t => t.value === currentTab)?.icon"
                                                        class="fa-2x text-muted"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">{{ order.text || order.title || 'Untitled Order' }}
                                                    </h6>
                                                    <small class="text-muted">{{ order.description || order.notes || 'No description' }}</small>
                                                </div>
                                            </div>
                                        </td>
<td>
                                            <span class="badge bg-light text-dark">
                                                {{ order.date }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge" :class="`bg-${getStatusColor(order.status)}`">
                                                {{ order.status || 'Pending' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1 justify-content-end">
                                                <button class="btn btn-primary" data-toggle="tooltip"
                                                    data-placement="top" title="Edit" @click="editOrder(order)">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button class="btn btn-success" data-toggle="tooltip"
                                                    data-placement="top" title="Complete" @click="completeOrder(order)">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                                <button class="btn btn-danger" data-toggle="tooltip"
                                                    data-placement="top" title="Delete" @click="deleteOrder(order)">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </div>

                                        </td>
                                    </tr>
                                </tbody>
                            </table>
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
    gap: 12px;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    background: #fff;
    border: 1px solid #eef0f4;
    border-radius: 12px;
    padding: 12px 14px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, .04);
    cursor: pointer;
    transition: .2s;
}

.menu-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 18px rgba(0, 0, 0, .06);
}

.menu-item.active {
    border-color: #6f42c1;
    box-shadow: 0 10px 20px rgba(111, 66, 193, .15);
}

.icon {
    height: 10px;
    width: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}

.icon-success {
    color: #28a745;
}

.icon-warning {
    color: #ffc107;
}

.icon-secondary {
    color: #ff7b29;
}

.icon-primary {
    color: #0d6efd;
}

.label {
    font-size: 14px;
    color: #2b2b2b;
    font-weight: 600;
    flex: 1;
}

.badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    color: #2b2b2b;
}
</style>
