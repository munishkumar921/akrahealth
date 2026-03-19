<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import { ref, computed } from "vue";

const props = defineProps({
	keyword: String,
	transactions: {
		type: Object,
		default: () => ({})
	},
	filters: {
		type: Object,
		default: () => ({
			type: 'all',
			status: ''
		})
	}
});

const columns = [
 	{ label: "Order ID", key: "order_id" },
	{ label: "Payment ID", key: "payment_id" },
	{ label: "Type", key: "type_label" },
	{ label: "User", key: "user" },
	{ label: "Description", key: "description" },
	{ label: "Amount (₹)", key: "amount" },
	{ label: "Currency", key: "currency" },
	{ label: "Status", key: "status" },
	{ label: "Created", key: "created_at"},
];

const transactionTypes = [
	{ value: 'all', label: 'All Types' },
	{ value: 'subscription', label: 'Subscription' },
	{ value: 'lab_order', label: 'Lab Order' },
	{ value: 'pharmacy_order', label: 'Pharmacy Order' },
	{ value: 'invoice', label: 'Invoice' },
	{ value: 'appointment', label: 'Appointment' },
];

const statusOptions = [
	{ value: '', label: 'All Status' },
	{ value: 'pending', label: 'Pending' },
	{ value: 'completed', label: 'Completed' },
	{ value: 'active', label: 'Active' },
	{ value: 'failed', label: 'Failed' },
	{ value: 'refunded', label: 'Refunded' },
	{ value: 'cancelled', label: 'Cancelled' },
	{ value: 'trial', label: 'Trial' },
];

const selectedType = ref(props.filters.type || 'all');
const selectedStatus = ref(props.filters.status || '');

const applyFilters = () => {
	router.get(route('admin.transaction.list'), {
		keyword: props.keyword,
		type: selectedType.value,
		status: selectedStatus.value
	}, {
		preserveState: true,
		replace: true
	});
};

const clearFilters = () => {
	selectedType.value = 'all';
	selectedStatus.value = '';
	router.get(route('admin.transaction.list'), {
		keyword: props.keyword,
	}, {
		preserveState: true,
		replace: true
	});
};

const getStatusClass = (status) => {
	const statusMap = {
		'active': 'badge bg-success',
		'inactive': 'badge bg-secondary',
		'pending': 'badge bg-warning',
		'cancelled': 'badge bg-danger',
		'completed': 'badge bg-success',
		'trial': 'badge bg-info',
		'failed': 'badge bg-danger',
		'refunded': 'badge bg-secondary',
	};
	
	const lowerStatus = status?.toLowerCase();
	return statusMap[lowerStatus] || 'badge bg-secondary';
};

const getTypeClass = (type) => {
	const typeMap = {
		'subscription': 'badge bg-primary',
		'lab_order': 'badge bg-info',
		'pharmacy_order': 'badge bg-warning',
		'invoice': 'badge bg-dark',
	};
	
	return typeMap[type] || 'badge bg-secondary';
};

const getAmountClass = (amount) => {
	return amount > 0 ? 'text-success' : 'text-muted';
};
</script>

<template>
	<AuthLayout
		title="Razorpay Transactions"
		description="All Razorpay Payment Transactions"
		heading="Razorpay Transactions"
	>
		<div class="d-flex align-items-center justify-content-between mb-4">
			<h3 class="d-flex align-items-center text-xl mb-2">Transactions</h3>
		</div>

		<!-- Filters -->
		<div class="card mb-4">
			<div class="card-body">
				<div class="row g-3">
					<div class="col-md-3">
						<label class="form-label">Transaction Type</label>
						<select v-model="selectedType" class="form-select" @change="applyFilters">
							<option v-for="type in transactionTypes" :key="type.value" :value="type.value">
								{{ type.label }}
							</option>
						</select>
					</div>
					<div class="col-md-3">
						<label class="form-label">Status</label>
						<select v-model="selectedStatus" class="form-select" @change="applyFilters">
							<option v-for="status in statusOptions" :key="status.value" :value="status.value">
								{{ status.label }}
							</option>
						</select>
					</div>
					<div class="col-md-3 d-flex align-items-end">
						<button class="btn btn-secondary me-2" @click="clearFilters">
							Clear Filters
						</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Summary Cards -->
		<div class="row mb-4">
			<div class="col-md-3">
				<div class="card bg-primary text-white">
					<div class="card-body">
						<h5 class="card-title text-white">Total Transactions</h5>
						<p class="card-text h3 mb-0 text-white">{{ transactions.data?.length || 0 }}</p>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card bg-success text-white">
					<div class="card-body text-white">
						<h5 class="card-title text-white">Completed</h5>
						<p class="card-text h3 text-white">{{ transactions.data?.filter(t => t.status === 'completed' || t.status === 'active').length || 0 }}</p>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card bg-warning text-white">
					<div class="card-body">
						<h5 class="card-title text-white">Pending</h5>
						<p class="card-text h3 text-white">{{ transactions.data?.filter(t => t.status === 'pending').length || 0 }}</p>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card bg-danger text-white">
					<div class="card-body"> 
						<h5 class="card-title text-white">Failed</h5>
						<p class="card-text h3 text-white">{{ transactions.data?.filter(t => t.status === 'failed').length || 0 }}</p>
					</div>
				</div>
			</div>
		</div>

		<Table :columns="columns" :data="transactions" :search="keyword">
			<template #type_label="{ row }">
				<span :class="getTypeClass(row.type)">
					{{ row.type_label || 'N/A' }}
				</span>
			</template>
			<template #amount="{ row }">
				<span :class="getAmountClass(row.amount)">
					{{ row.currency === 'USD' ? '$' : row.currency === 'EUR' ? '€' : '₹' }}{{ Number(row.amount || 0).toFixed(2) }}
				</span>
			</template>
			<template #status="{ row }">
				<span :class="getStatusClass(row.status)">
					{{ row.status || 'N/A' }}
				</span>
			</template>
		</Table>
	</AuthLayout>
</template>

