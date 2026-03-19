<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { router } from "@inertiajs/vue3";
import { ref, computed } from "vue";

const props = defineProps({
	keyword: String,
	labReports: {
		type: [Array, Object],
		default: () => []
	},
	filters: {
		type: Object,
		default: () => ({
			status: '',
			payment_status: '',
			date_from: '',
			date_to: ''
		})
	},
	route: Array,
});

const columns = [
	{ label: "Order#", key: "order_id" },
	{ label: "Patient", key: "patient" },
	{ label: "Lab", key: "lab" },
	{ label: "Doctor", key: "doctor" },
	{ label: "Amount", key: "amount" },
	{ label: "Payment", key: "payment_status" },
	{ label: "Status", key: "status" },
	{ label: "Created", key: "created_at" },
];

const searchKeyword = ref(props.keyword || '');
const selectedStatus = ref(props.filters.status || '');
const selectedPaymentStatus = ref(props.filters.payment_status || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');

// Status filter options
const statusOptions = [
	{ value: '', label: 'All Status' },
	{ value: 'pending', label: 'Pending' },
	{ value: 'completed', label: 'Completed' },
	{ value: 'cancelled', label: 'Cancelled' },
	{ value: 'in_progress', label: 'In Progress' },
];

// Payment status options
const paymentStatusOptions = [
	{ value: '', label: 'All Payment' },
	{ value: 'paid', label: 'Paid' },
	{ value: 'unpaid', label: 'Unpaid' },
	{ value: 'refunded', label: 'Refunded' },
	{ value: 'pending', label: 'Pending' },
];

// Search function
const search = () => {
	router.get(route('admin.labReports'), {
		keyword: searchKeyword.value,
		status: selectedStatus.value,
		payment_status: selectedPaymentStatus.value,
		date_from: dateFrom.value,
		date_to: dateTo.value,
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
	dateFrom.value = '';
	dateTo.value = '';
	search();
};

// Status badge class
const getStatusClass = (status) => {
	const statusMap = {
		'completed': 'badge bg-success',
		'pending': 'badge bg-warning',
		'cancelled': 'badge bg-danger',
		'in_progress': 'badge bg-info',
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
		'unpaid': 'badge bg-danger',
		'refunded': 'badge bg-info',
		'pending': 'badge bg-warning',
	};
	const lowerStatus = status?.toLowerCase();
	return statusMap[lowerStatus] || 'badge bg-secondary';
};

// Amount formatting
const formatAmount = (amount) => {
	if (!amount) return '₹0.00';
	return '₹' + Number(amount).toFixed(2);
};

// Download report function
const downloadReport = (row) => {
	if (row.report_file) {
		window.open(row.report_file, '_blank');
	}
};
</script>

<template>
	<AuthLayout
		title="Lab Reports"
		description="View lab reports"
		heading="Lab Reports"
	>
		<!-- ================= HEADER ================= -->
		<div class="">
			<!-- ================= DESKTOP VIEW - Title and Filters in Same Row ================= -->
			<div class="d-none d-md-flex align-items-center justify-content-between mb-3">
				<!-- Title -->
				<h3 class="text-xl mb-0">Lab Reports</h3>

				<!-- Controls -->
				<div class="d-flex align-items-center gap-3">
					<!-- Search Input -->
					<input 
						type="text" 
						class="form-control form-control-sm" 
						placeholder="Search..." 
						style="width: 200px;"
						v-model="searchKeyword"
						@keyup.enter="search"
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

					<!-- Clear Filters -->
					<button 
						v-if="searchKeyword || selectedStatus || selectedPaymentStatus || dateFrom || dateTo"
						class="btn btn-outline-danger btn-sm" 
						type="button" 
						@click="clearFilters"
						title="Clear filters"
					>
						<i class="bi bi-x-lg"></i>
					</button>
				</div>
			</div>

			<!-- ================= MOBILE VIEW - Title and Filters ================= -->
			<div class="d-md-none">
				<h3 class="text-xl mb-3">Lab Reports</h3>
				
				<!-- Search Input -->
				<input 
					type="text" 
					class="form-control form-control-sm mb-2" 
					placeholder="Search..." 
					v-model="searchKeyword"
					@keyup.enter="search"
				/>

				<!-- Status Filter -->
				<select 
					class="form-select form-select-sm mb-2" 
					v-model="selectedStatus"
					@change="search"
				>
					<option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
						{{ opt.label }}
					</option>
				</select>

				<!-- Payment Status Filter -->
				<select 
					class="form-select form-select-sm mb-2" 
					v-model="selectedPaymentStatus"
					@change="search"
				>
					<option v-for="opt in paymentStatusOptions" :key="opt.value" :value="opt.value">
						{{ opt.label }}
					</option>
				</select>

				<!-- Clear Filters -->
				<button 
					v-if="searchKeyword || selectedStatus || selectedPaymentStatus || dateFrom || dateTo"
					class="btn btn-outline-danger btn-sm mb-3" 
					type="button" 
					@click="clearFilters"
				>
					Clear Filters
				</button>
			</div>
		</div>

		<!-- ================= TABLE + PAGINATION ================= -->
		<div class="table-responsive">
			<Table :columns="columns" :data="labReports" :search="keyword">
				<template #status="{ row }">
					<span :class="getStatusClass(row.status)">
						{{ row.status || 'N/A' }}
					</span>
				</template>
				<template #payment_status="{ row }">
					<span :class="getPaymentStatusClass(row.payment_status)">
						{{ row.payment_status || 'N/A' }}
					</span>
				</template>
				<template #amount="{ row }">
					{{ formatAmount(row.amount) }}
				</template>
				<template #actions="{ row }">
					<button 
						class="icon-btn btn btn-info" 
						title="Download Report"
						@click="downloadReport(row)"
						:disabled="!row.report_file"
					>
						<i class="bi bi-download"></i>
					</button>
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

.icon-btn {
	padding: 0.25rem 0.5rem;
	font-size: 0.875rem;
}
</style>
