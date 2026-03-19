<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { router } from "@inertiajs/vue3";
import { ref, computed } from "vue";


const props = defineProps({
	keyword: {
		type: String,
		default: ''
	},
	pharmacyOrders: {
		type: [Array, Object],
		default: () => []
	},
	filters: {
		type: Object,
		default: () => ({
			status: '',
			payment_status: ''
		})
	},
	route: Array,
});

const columns = [
	{ label: "Pharmacy", key: "pharmacy.name" },
 	{ label: "Patient", key: "patient.name" },
	{ label: "Doctor", key: "doctor.name" },
	 { label: "Active Date", key: "date_active" },
    { label: "Inactive Date", key: "date_inactive" },
    { label:"Due Date", key:"due_date" },
    { label: "Prescription Status", type:"status", key: "prescription" },
 ];

const searchKeyword = ref(props.keyword || '');
const selectedStatus = ref(props.filters.status || '');
const selectedPaymentStatus = ref(props.filters.payment_status || '');

// Status filter options
const statusOptions = [
	{ value: '', label: 'All Status' },
	{ value: 'pending', label: 'Pending' },
	{ value: 'accepted', label: 'Accepted' },
	{ value: 'processing', label: 'Processing' },
	{ value: 'ready', label: 'Ready' },
	{ value: 'dispensed', label: 'Dispensed' },
	{ value: 'completed', label: 'Completed' },
	{ value: 'cancelled', label: 'Cancelled' },
	{ value: 'rejected', label: 'Rejected' },
];

// Payment status options
const paymentStatusOptions = [
	{ value: '', label: 'All Payment' },
	{ value: 'paid', label: 'Paid' },
	{ value: 'pending', label: 'Pending' },
	{ value: 'failed', label: 'Failed' },
	{ value: 'refunded', label: 'Refunded' },
];

// Search function
const search = () => {
	router.get(route('admin.pharmacy-orders.list'), {
		keyword: searchKeyword.value,
		status: selectedStatus.value,
		payment_status: selectedPaymentStatus.value,
	}, {
		preserveState: true,
		replace: true,
	});
};

// Clear filters
const clearFilters = () => {
	searchKeyword.value = '';
	selectedStatus.value = '';
	selectedPaymentStatus.value = '';
	search();
};

// Status badge class
const getStatusClass = (status) => {
	const statusMap = {
		'pending': 'badge bg-warning',
		'accepted': 'badge bg-info',
		'processing': 'badge bg-primary',
		'ready': 'badge bg-secondary',
		'dispensed': 'badge bg-success',
		'completed': 'badge bg-success',
		'cancelled': 'badge bg-danger',
		'rejected': 'badge bg-danger',
		'active': 'badge bg-success',
		'inactive': 'badge bg-secondary',
	};
	const lowerStatus = status?.toLowerCase();
	return statusMap[lowerStatus] || 'badge bg-secondary';
};

// Payment status badge class
const getPaymentStatusClass = (status) => {
	const statusMap = {
		'paid': 'badge bg-success',
		'pending': 'badge bg-warning',
		'failed': 'badge bg-danger',
		'refunded': 'badge bg-info',
	};
	const lowerStatus = status?.toLowerCase();
	return statusMap[lowerStatus] || 'badge bg-secondary';
};

// Amount formatting
const formatAmount = (amount) => {
	if (!amount) return '₹0.00';
	return '₹' + Number(amount).toFixed(2);
};
</script>

<template>
	<AuthLayout
		title="Pharmacy Orders"
		description="Manage pharmacy orders"
		heading="Pharmacy Orders"
	>
		<!-- ================= HEADER ================= -->
		<div class="">
			<!-- ================= DESKTOP VIEW - Title and Controls in Same Row ================= -->
			<div class="d-none d-md-flex align-items-center justify-content-between mb-3">
				<!-- Title -->
				<h3 class="text-xl mb-0">Pharmacy Orders</h3>

				<!-- Controls -->
				<div class="d-flex align-items-center gap-3">
					<!-- Status Filter -->
					<select 
						class="form-select form-select-sm" 
						style="width: 140px;"
						v-model="selectedStatus"
						@change="search"
					>
						<option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
							{{ opt.label }}
						</option>
					</select>

					<!-- Payment Status Filter -->
					<select 
						class="form-select form-select-sm" 
						style="width: 140px;"
						v-model="selectedPaymentStatus"
						@change="search"
					>
						<option v-for="opt in paymentStatusOptions" :key="opt.value" :value="opt.value">
							{{ opt.label }}
						</option>
					</select>

					<!-- Clear Filters -->
					<button 
						v-if="searchKeyword || selectedStatus || selectedPaymentStatus"
						class="btn btn-outline-danger btn-sm" 
						type="button" 
						@click="clearFilters"
						title="Clear filters"
					>
						<i class="bi bi-x-lg"></i>
					</button>
				</div>
			</div>

			<!-- ================= MOBILE VIEW - Title ================= -->
			<div class="d-md-none">
				<h3 class="text-xl mb-3">Pharmacy Orders</h3>
			</div>
		</div>

		<!-- ================= TABLE + PAGINATION ================= -->
		<div class="table-responsive">
			<Table :columns="columns" :data="{ data: pharmacyOrders.data }" :search="keyword">
				<template #order_id="{ row }">
					<span class="fw-semibold text-primary">{{ row.order_id || 'N/A' }}</span>
				</template>
				<template #patient="{ row }">
					<div>
						<span class="d-block">{{ row.patient || 'N/A' }}</span>
						<small v-if="row.medication_count" class="text-muted">
							{{ row.medication_count }} medication(s)
						</small>
					</div>
				</template>
				<template #pharmacy="{ row }">
					<span>{{ row.pharmacy || 'N/A' }}</span>
				</template>
				<template #doctor="{ row }">
					<span>{{ row.doctor || 'N/A' }}</span>
				</template>
				<template #status="{ row }">
					<span :class="getStatusClass(row.status)">
						{{ row.status_label || row.status || 'N/A' }}
					</span>
				</template>
				<template #payment_status="{ row }">
					<span :class="getPaymentStatusClass(row.payment_status)">
						{{ row.payment_status_label || row.payment_status || 'N/A' }}
					</span>
				</template>
				<template #total="{ row }">
					<span class="fw-semibold">{{ formatAmount(row.total) }}</span>
				</template>
				<template #created_at="{ row }">
					<span class="text-muted small">{{ row.created_at }}</span>
				</template>
			</Table>
		</div>
	</AuthLayout>
</template>

<style scoped>
.status-check {
	appearance: none;
	width: 16px;
	height: 16px;
	border: 2px solid #d1d5db;
	border-radius: 4px;
	display: inline-block;
	position: relative;
	cursor: pointer;
	background: #fff;
}
.status-check:focus { outline: none; box-shadow: 0 0 0 2px rgba(59,130,246,.2); }

.status-check--green:checked {
	border-color: #06c270;
	background-color: #06c270;
}
.status-check--grey:checked {
    border-color: #9ca3af;
	background-color: #9ca3af;
}
.status-check--red:checked {
    border-color: #f35353;
	background-color: #f35353;
}

/* tick icon */
.status-check:checked::after {
	content: "";
	position: absolute;
	left: 4px;
	top: 1px;
	width: 4px;
	height: 8px;
	border: solid #fff;
	border-width: 0 2px 2px 0;
	transform: rotate(45deg);
}

.badge {
	font-size: 0.75rem;
	padding: 0.35em 0.65em;
	font-weight: 500;
}

/* Colored badges for different statuses */
.bg-primary {
	background-color: #0d6efd !important;
}
.bg-secondary {
	background-color: #6c757d !important;
}
.bg-success {
	background-color: #198754 !important;
}
.bg-warning {
	background-color: #ffc107 !important;
	color: #000 !important;
}
.bg-danger {
	background-color: #dc3545 !important;
}
.bg-info {
	background-color: #0dcaf0 !important;
	color: #000 !important;
}
</style>

