<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { router } from "@inertiajs/vue3";
import { ref, computed } from "vue";

const props = defineProps({
	keyword: String,
	labOrders: {
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
  { label: 'Lab Name', key: 'lab_name' },
  { label: 'Order Type', key: 'order_type' },
  { label: 'Pending Date', key: 'pending_date' },
  { label: 'Notes', key: 'notes' },
];

const filterActive = ref(true);
const filterInactive = ref(true);
const searchKeyword = ref(props.keyword || '');
 
const selectedStatus = ref(props.filters.status || '');
const selectedPaymentStatus = ref(props.filters.payment_status || '');

const filteredRows = computed(() => {
	if (!props.labOrders || !props.labOrders.data) {
		return [];
	}
	
	return props.labOrders.data.filter(r => {
		const active = r.is_active;
		return (filterActive.value && active) ||
			   (filterInactive.value && !active);
	});
});

// Search function
const search = () => {
	router.get(route('admin.lab-orders.list'), {
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
</script>

<template>
	<AuthLayout
		title="Lab Orders"
		description="Manage lab orders"
		heading="Lab Orders"
	>
		<!-- ================= HEADER ================= -->
		<div class="">
			<!-- ================= DESKTOP VIEW - Title and Controls in Same Row ================= -->
			<div class="d-none d-md-flex align-items-center justify-content-between mb-3">
				<!-- Title -->
				<h3 class="text-xl mb-0">Lab Orders</h3>

				 
			</div>

			<!-- ================= MOBILE VIEW - Title ================= -->
			<div class="d-md-none">
				<h3 class="text-xl mb-3">Lab Orders</h3>
			</div>
		</div>

		<!-- ================= TABLE + PAGINATION ================= -->
		<div class="table-responsive">
			<Table :columns="columns" :data="{ data: labOrders.data }" :search="keyword">
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
				<template #total_amount="{ row }">
					{{ formatAmount(row.total_amount) }}
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
</style>

