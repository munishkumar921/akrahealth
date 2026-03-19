<script setup>
import AuthLayout2 from '@/Layouts/AuthLayout2.vue';
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    dashboardData: Object,
});

// Computed properties with safe fallbacks
const totalOrders = computed(() => props.dashboardData?.totalOrders ?? 0);
const pendingOrders = computed(() => props.dashboardData?.pendingOrders ?? 0);
const completedOrders = computed(() => props.dashboardData?.completedOrders ?? 0);
const todayOrders = computed(() => props.dashboardData?.todayOrders ?? 0);
const totalMedicines = computed(() => props.dashboardData?.totalMedicines ?? 0);
const totalRevenue = computed(() => props.dashboardData?.totalRevenue ?? 0);
const recentOrders = computed(() => props.dashboardData?.recentOrders ?? []);
const prescriptionsPending = computed(() => props.dashboardData?.prescriptionsPending ?? 0);
const prescriptionsDispensed = computed(() => props.dashboardData?.prescriptionsDispensed ?? 0);
const recentMedicines = computed(() => props.dashboardData?.recentMedicines ?? []);

// Format currency
const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(value || 0);
};

// Get medication count from prescriptions
const getMedicationCount = (order) => {
    if (order.medications && Array.isArray(order.medications)) {
        return order.medications.length;
    }
    return 0;
};

// Get status badge class
const getStatusBadgeClass = (status) => {
    const statusMap = {
        'pending': 'iq-bg-warning',
        'received': 'iq-bg-info',
        'processing': 'iq-bg-secondary',
        'ready': 'iq-bg-info',
        'dispensed': 'iq-bg-success',
        'completed': 'iq-bg-success',
        'cancelled': 'iq-bg-danger',
        'rejected': 'iq-bg-danger'
    };
    return statusMap[status?.toLowerCase()] || 'iq-bg-secondary';
};
</script>

<template>
    <AuthLayout2 title="Pharmacy Dashboard" description="Manage your pharmacy operations" heading="Dashboard">
        <div class="row">

            <!-- Total Orders -->
            <div class="col-md-6 col-lg-3">
                <Link :href="route('pharmacy.orders.index')" class="iq-card iq-card-block iq-card-stretch iq-card-height">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Total Orders</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter fs-34">{{ totalOrders }}</span></h2>
                            </div>
                            <div class="iq-iconbox alert-primary">
                                <i class="fa fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Pending Orders -->
            <div class="col-md-6 col-lg-3">
                <Link :href="route('pharmacy.orders.index')" class="iq-card iq-card-block iq-card-stretch iq-card-height">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Pending Orders</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter fs-34">{{ pendingOrders }}</span></h2>
                            </div>
                            <div class="iq-iconbox alert-warning">
                                <i class="fa fa-clock-o"></i>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Today's Orders -->
            <div class="col-md-6 col-lg-3">
                <Link :href="route('pharmacy.orders.index')" class="iq-card iq-card-block iq-card-stretch iq-card-height">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Today's Orders</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter fs-34">{{ todayOrders }}</span></h2>
                            </div>
                            <div class="iq-iconbox iq-bg-info">
                                <i class="fa fa-calendar-check-o"></i>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Prescriptions Pending -->
            <div class="col-md-6 col-lg-3">
                <Link :href="route('pharmacy.orders.index')" class="iq-card iq-card-block iq-card-stretch iq-card-height">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Prescriptions Pending</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter fs-34">{{ prescriptionsPending }}</span></h2>
                            </div>
                            <div class="iq-iconbox iq-bg-warning">
                                <i class="fa fa-file-prescription"></i>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Completed Orders -->
            <div class="col-md-6 col-lg-3">
                <Link :href="route('pharmacy.reports.index')" class="iq-card iq-card-block iq-card-stretch iq-card-height">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Completed Orders</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter fs-34">{{ completedOrders }}</span></h2>
                            </div>
                            <div class="iq-iconbox iq-bg-success">
                                <i class="fa fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Prescriptions Dispensed -->
            <div class="col-md-6 col-lg-3">
                <Link :href="route('pharmacy.reports.index')" class="iq-card iq-card-block iq-card-stretch iq-card-height">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Prescriptions Dispensed</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter fs-34">{{ prescriptionsDispensed }}</span></h2>
                            </div>
                            <div class="iq-iconbox iq-bg-success">
                                <i class="fa fa-check-double"></i>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Total Medicines -->
            <div class="col-md-6 col-lg-3">
                <Link :href="route('pharmacy.medicines.index')" class="iq-card iq-card-block iq-card-stretch iq-card-height">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Total Medicines</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter fs-34">{{ totalMedicines }}</span></h2>
                            </div>
                            <div class="iq-iconbox iq-bg-primary">
                                <i class="fa fa-medkit"></i>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Total Revenue -->
            <div class="col-md-6 col-lg-3">
                <Link :href="route('pharmacy.transactions.index')" class="iq-card iq-card-block iq-card-stretch iq-card-height">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Total Revenue</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter fs-34">{{ formatCurrency(totalRevenue) }}</span></h2>
                            </div>
                            <div class="iq-iconbox iq-purple">
                                <i class="fa fa-money"></i>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>

        </div>

        <div class="row mt-4">
            <!-- Recent Orders with Prescriptions -->
            <div class="col-sm-12 col-lg-8">
                <div class="iq-card">
                    <div class="iq-card-header d-flex justify-content-between bg-white rounded">
                        <div class="iq-header-title">
                            <h4 class="card-title">Recent Orders & Prescriptions</h4>
                        </div>
                        <Link :href="route('pharmacy.orders.index')" class="btn btn-sm btn-primary">View All</Link>
                    </div>
                    <div class="iq-card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-borderless">
                                <thead>
                                    <tr>
                                        <th scope="col">Order ID</th>
                                        <th scope="col">Patient</th>
                                        <th scope="col">Medications</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Status</th>
                                        <th scope="col" class="text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="order in recentOrders" :key="order.id">
                                        <td>#{{ order.id }}</td>
                                        <td>{{ order.patient?.name ?? order.patient?.user?.name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge iq-bg-info">{{ getMedicationCount(order) }} items</span>
                                        </td>
                                        <td>{{ order.created_at }}</td>
                                        <td>
                                            <span class="badge" :class="getStatusBadgeClass(order.status)">
                                                {{ order.formatted_status ?? order.status ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="text-right">{{ formatCurrency(order.total_amount) }}</td>
                                    </tr>
                                    <tr v-if="!recentOrders.length">
                                        <td colspan="6" class="text-center text-muted py-4">No recent orders found</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Medicines -->
            <div class="col-sm-12 col-lg-4">
                <div class="iq-card">
                    <div class="iq-card-header d-flex justify-content-between bg-white rounded">
                        <div class="iq-header-title">
                            <h4 class="card-title">Recent Medicines</h4>
                        </div>
                        <Link :href="route('pharmacy.medicines.index')" class="btn btn-sm btn-primary">View All</Link>
                    </div>
                    <div class="iq-card-body">
                        <div v-if="recentMedicines.length" class="list-group list-group-flush">
                            <div v-for="medicine in recentMedicines" :key="medicine.id" class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ medicine.brand_name ?? medicine.generic_name ?? 'N/A' }}</h6>
                                    <small class="text-muted">{{ medicine.generic_name ?? '' }}</small>
                                </div>
                                <span class="badge iq-bg-primary">{{ formatCurrency(medicine.price) }}</span>
                            </div>
                        </div>
                        <div v-else class="text-center text-muted py-4">
                            <i class="fa fa-medkit fa-3x text-secondary mb-3"></i>
                            <p class="mb-0">No medicines found</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AuthLayout2>
</template>

