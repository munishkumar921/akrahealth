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
	pharmacyReports: {
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
	{ label: "Order#", key: "order_id" },
	{ label: "Pharmacy", key: "pharmacy" },
	{ label: "Patient", key: "patient" },
	{ label: "Doctor", key: "doctor" },
	{ label: "Amount", key: "amount" },
	{ label: "Payment", key: "payment_status" },
	{ label: "Status", key: "status" },
	{ label: "Created", key: "created_at" },
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
	router.get(route('pharmacyReports'), {
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
		'pending': 'bg-warning',
		'accepted': 'bg-info',
		'processing': 'bg-primary',
		'ready': 'bg-secondary',
		'dispensed': 'bg-success',
		'completed': 'bg-success',
		'cancelled': 'bg-danger',
		'rejected': 'bg-danger',
		'active': 'bg-success',
		'inactive': 'bg-secondary',
	};
	const lowerStatus = status?.toLowerCase();
	return statusMap[lowerStatus] || 'bg-secondary';
};

// Payment status badge class
const getPaymentStatusClass = (status) => {
	const statusMap = {
		'paid': 'bg-success',
		'pending': 'bg-warning',
		'failed': 'bg-danger',
		'refunded': 'bg-info',
	};
	const lowerStatus = status?.toLowerCase();
	return statusMap[lowerStatus] || 'bg-secondary';
};

// Amount formatting
const formatAmount = (amount) => {
	if (!amount) return '₹0.00';
	return '₹' + Number(amount).toFixed(2);
};

// Helper to check if data is empty
const isEmpty = computed(() => {
	if (Array.isArray(props.pharmacyReports)) {
		return props.pharmacyReports.length === 0;
	}
	return !props.pharmacyReports || !props.pharmacyReports.data || props.pharmacyReports.data.length === 0;
});
</script>

<template>
	<AuthLayout
		title="Pharmacy Reports"
		description="View pharmacy reports"
		heading="Pharmacy Reports"
	>
		<!-- ================= HEADER ================= -->
		<div class="">
			<!-- ================= DESKTOP VIEW - Title and Filters in Same Row ================= -->
			<div class="d-none d-md-flex align-items-center justify-content-between mb-3">
				<!-- Title -->
				<h3 class="text-xl mb-0">Pharmacy Reports</h3>

				<!-- Controls -->
				<div class="d-flex align-items-center gap-3">
					<!-- Search -->
					<input
						type="text"
						class="form-control form-control-sm"
						placeholder="Search orders..."
						v-model="searchKeyword"
						@keyup.enter="search"
						style="width: 200px;"
					/>

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

					<!-- Search Button -->
					<button 
						class="btn btn-primary btn-sm" 
						type="button" 
						@click="search"
					>
						<i class="bi bi-search"></i>
					</button>

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
				<h3 class="text-xl mb-3">Pharmacy Reports</h3>
				
				<!-- Search Input -->
				<input
					type="text"
					class="form-control mb-3"
					placeholder="Search orders..."
					v-model="searchKeyword"
					@keyup.enter="search"
				/>

				<!-- Status Filters -->
				<div class="d-flex gap-2 mb-3">
					<select 
						class="form-select form-select-sm" 
						v-model="selectedStatus"
						@change="search"
					>
						<option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
							{{ opt.label }}
						</option>
					</select>

					<select 
						class="form-select form-select-sm" 
						v-model="selectedPaymentStatus"
						@change="search"
					>
						<option v-for="opt in paymentStatusOptions" :key="opt.value" :value="opt.value">
							{{ opt.label }}
						</option>
					</select>
				</div>
			</div>
		</div>

		<!-- ================= EMPTY STATE ================= -->
		<div v-if="isEmpty" class="text-center py-5">
			<i class="bi bi-prescription2 fs-1 text-muted"></i>
			<p class="mt-3 text-muted">No pharmacy reports found.</p>
		</div>

		<!-- ================= TABLE + PAGINATION ================= -->
		<div v-else class="table-responsive">
			<Table :columns="columns" :data="pharmacyReports" :search="keyword">
				<template #order_id="{ row }">
					<span class="fw-semibold text-primary">{{ row.order_id || 'N/A' }}</span>
				</template>
				<template #pharmacy="{ row }">
					<span>{{ row.pharmacy || 'N/A' }}</span>
				</template>
				<template #patient="{ row }">
					<span>{{ row.patient || 'N/A' }}</span>
				</template>
				<template #doctor="{ row }">
					<span>{{ row.doctor || 'N/A' }}</span>
				</template>
				<template #amount="{ row }">
					<span class="fw-semibold">{{ formatAmount(row.amount) }}</span>
				</template>
				<template #payment_status="{ row }">
					<span :class="'badge ' + getPaymentStatusClass(row.payment_status)">
						{{ row.payment_status_label || row.payment_status || 'N/A' }}
					</span>
				</template>
				<template #status="{ row }">
					<span :class="'badge ' + getStatusClass(row.status)">
						{{ row.status_label || row.status || 'N/A' }}
					</span>
				</template>
				<template #created_at="{ row }">
					<span class="text-muted small">{{ row.created_at }}</span>
				</template>
				<template #actions="{ row }">
					<button class="icon-btn btn btn-info" title="Download Report">
						<i class="bi bi-download"></i>
					</button>
				</template>
			</Table>
		</div>
	</AuthLayout>
</template>

<style scoped>
.icon-btn {
	padding: 9px 8px 6px 8px;
	border: none;
	border-radius: 12px;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	color: #fff;
	cursor: pointer;
	transition: transform .07s ease-in-out, opacity .15s ease-in-out;
}
.icon-btn:active { transform: scale(0.97); }
.icon-btn i { font-size: 14px; line-height: 1; }

.badge {
	font-size: 0.75rem;
	padding: 0.35em 0.65em;
	font-weight: 500;
}

/* Colored badges for different statuses */
.bg-primary { background-color: #0d6efd !important; }
.bg-secondary { background-color: #6c757d !important; }
.bg-success { background-color: #198754 !important; }
.bg-warning { background-color: #ffc107 !important; color: #000 !important; }
.bg-danger { background-color: #dc3545 !important; }
.bg-info { background-color: #0dcaf0 !important; color: #000 !important; }
</style>

