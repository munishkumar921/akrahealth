<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
	order: {
		type: Object,
		required: true
	},
	orderType: {
		type: String,
		required: true
	},
});

const getStatusClass = (status) => {
	const statusMap = {
		'completed': 'badge bg-success',
		'pending': 'badge bg-warning',
		'cancelled': 'badge bg-danger',
		'in_progress': 'badge bg-info',
		'accepted': 'badge bg-primary',
	};
	const lowerStatus = status?.toLowerCase();
	return statusMap[lowerStatus] || 'badge bg-secondary';
};

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

const getOrderTypeLabel = (type) => {
	const labels = {
		'lab_order': 'Lab Order',
		'pharmacy_order': 'Pharmacy Order',
		'order': 'General Order',
	};
	return labels[type] || type;
};

const goBack = () => {
	router.get(route('admin.orders.index'));
};

const formatAmount = (amount) => {
	if (!amount && amount !== 0) return '₹0.00';
	return '₹' + Number(amount).toFixed(2);
};
</script>

<template>
	<AuthLayout
		:title="getOrderTypeLabel(orderType) + ' Details'"
		:description="'View order details'"
		:heading="getOrderTypeLabel(orderType) + ' Details'"
	>
		<!-- ================= HEADER ================= -->
		<div class="mb-4">
			<button class="btn btn-outline-secondary mb-3" @click="goBack">
				<i class="bi bi-arrow-left"></i> Back to Orders
			</button>
			
			<div class="d-flex justify-content-between align-items-center">
				<div>
					<h3 class="mb-0">Order #{{ order.id }}</h3>
					<p class="text-muted mb-0">Created: {{ order.created_at }}</p>
				</div>
				<div class="d-flex gap-2">
					<span :class="getStatusClass(order.status)">
						{{ order.status || 'N/A' }}
					</span>
					<span :class="getPaymentStatusClass(order.payment_status)">
						{{ order.payment_status || 'N/A' }}
					</span>
				</div>
			</div>
		</div>

		<!-- ================= ORDER DETAILS ================= -->
		<div class="row">
			<!-- Patient Info -->
			<div class="col-md-4 mb-4">
				<div class="card h-100">
					<div class="card-header bg-light">
						<h5 class="card-title mb-0">Patient Information</h5>
					</div>
					<div class="card-body">
						<p class="mb-1"><strong>Name:</strong> {{ order.patient || 'N/A' }}</p>
						<p class="mb-1"><strong>ID:</strong> {{ order.patient_id || 'N/A' }}</p>
					</div>
				</div>
			</div>

			<!-- Doctor Info -->
			<div class="col-md-4 mb-4">
				<div class="card h-100">
					<div class="card-header bg-light">
						<h5 class="card-title mb-0">Doctor Information</h5>
					</div>
					<div class="card-body">
						<p class="mb-1"><strong>Name:</strong> {{ order.doctor || 'N/A' }}</p>
						<p class="mb-1"><strong>ID:</strong> {{ order.doctor_id || 'N/A' }}</p>
					</div>
				</div>
			</div>

			<!-- Provider Info -->
			<div class="col-md-4 mb-4">
				<div class="card h-100">
					<div class="card-header bg-light">
						<h5 class="card-title mb-0">
							{{ orderType === 'lab_order' ? 'Lab Information' : 
							   (orderType === 'pharmacy_order' ? 'Pharmacy Information' : 'Provider Information') }}
						</h5>
					</div>
					<div class="card-body">
						<p v-if="orderType === 'lab_order'" class="mb-1">
							<strong>Lab:</strong> {{ order.lab || 'N/A' }}
						</p>
						<p v-else-if="orderType === 'pharmacy_order'" class="mb-1">
							<strong>Pharmacy:</strong> {{ order.pharmacy || 'N/A' }}
						</p>
						<p class="mb-1"><strong>ID:</strong> {{ order.lab_id || order.pharmacy_id || 'N/A' }}</p>
					</div>
				</div>
			</div>
		</div>

		<!-- ================= ORDER-SPECIFIC DETAILS ================= -->
		<div class="row">
			<!-- Lab Order Specific -->
			<template v-if="orderType === 'lab_order'">
				<div class="col-md-4 mb-4">
					<div class="card h-100">
						<div class="card-header bg-light">
							<h5 class="card-title mb-0">Lab Schedule</h5>
						</div>
						<div class="card-body">
							<p class="mb-1"><strong>Scheduled:</strong> {{ order.scheduled_at || 'Not Scheduled' }}</p>
							<p class="mb-1"><strong>Reported:</strong> {{ order.reported_at || 'Pending' }}</p>
						</div>
					</div>
				</div>
			</template>

			<!-- Pharmacy Order Specific -->
			<template v-else-if="orderType === 'pharmacy_order'">
				<div class="col-md-4 mb-4">
					<div class="card h-100">
						<div class="card-header bg-light">
							<h5 class="card-title mb-0">Delivery Information</h5>
						</div>
						<div class="card-body">
							<p class="mb-1"><strong>Required:</strong> {{ order.delivery_required ? 'Yes' : 'No' }}</p>
							<p class="mb-1"><strong>Status:</strong> {{ order.delivery_status || 'N/A' }}</p>
						</div>
					</div>
				</div>
			</template>

			<!-- General Order Specific -->
			<template v-else-if="orderType === 'order'">
				<div class="col-md-4 mb-4">
					<div class="card h-100">
						<div class="card-header bg-light">
							<h5 class="card-title mb-0">Order Date</h5>
						</div>
						<div class="card-body">
							<p class="mb-1"><strong>Order Date:</strong> {{ order.orders_date || 'N/A' }}</p>
							<p class="mb-1"><strong>Pending Date:</strong> {{ order.pending_date || 'N/A' }}</p>
						</div>
					</div>
				</div>
			</template>

			<!-- Amount & Payment -->
			<div class="col-md-4 mb-4">
				<div class="card h-100">
					<div class="card-header bg-light">
						<h5 class="card-title mb-0">Payment Information</h5>
					</div>
					<div class="card-body">
						<p class="mb-1"><strong>Total Amount:</strong> {{ formatAmount(order.total_amount) }}</p>
						<p class="mb-1"><strong>Payment Status:</strong> 
							<span :class="getPaymentStatusClass(order.payment_status)">
								{{ order.payment_status || 'N/A' }}
							</span>
						</p>
					</div>
				</div>
			</div>

			<!-- Notes -->
			<div class="col-md-4 mb-4">
				<div class="card h-100">
					<div class="card-header bg-light">
						<h5 class="card-title mb-0">Notes</h5>
					</div>
					<div class="card-body">
						<p class="mb-0">{{ order.notes || 'No notes' }}</p>
					</div>
				</div>
			</div>
		</div>

		<!-- ================= GENERAL ORDER ITEMS ================= -->
		<template v-if="orderType === 'order'">
			<div class="row">
				<div class="col-md-3 mb-4">
					<div class="card h-100">
						<div class="card-header bg-primary text-white">
							<h5 class="card-title mb-0">Labs</h5>
						</div>
						<div class="card-body">
							<p class="mb-0">{{ order.labs || 'None' }}</p>
						</div>
					</div>
				</div>
				<div class="col-md-3 mb-4">
					<div class="card h-100">
						<div class="card-header bg-info text-dark">
							<h5 class="card-title mb-0">Radiology</h5>
						</div>
						<div class="card-body">
							<p class="mb-0">{{ order.radiology || 'None' }}</p>
						</div>
					</div>
				</div>
				<div class="col-md-3 mb-4">
					<div class="card h-100">
						<div class="card-header bg-warning text-dark">
							<h5 class="card-title mb-0">Referrals</h5>
						</div>
						<div class="card-body">
							<p class="mb-0">{{ order.referrals || 'None' }}</p>
						</div>
					</div>
				</div>
				<div class="col-md-3 mb-4">
					<div class="card h-100">
						<div class="card-header bg-secondary text-white">
							<h5 class="card-title mb-0">Cardiopulmonary</h5>
						</div>
						<div class="card-body">
							<p class="mb-0">{{ order.cp || 'None' }}</p>
						</div>
					</div>
				</div>
			</div>
		</template>

		<!-- ================= PHARMACY MEDICATIONS ================= -->
		<template v-if="orderType === 'pharmacy_order' && order.medications && order.medications.length">
			<div class="row">
				<div class="col-12 mb-4">
					<div class="card">
						<div class="card-header bg-success text-white">
							<h5 class="card-title mb-0">Medications</h5>
						</div>
						<div class="card-body">
							<table class="table table-striped">
								<thead>
									<tr>
										<th>Medication</th>
										<th>Quantity</th>
										<th>Dosage</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="(med, index) in order.medications" :key="index">
										<td>{{ med.name || med }}</td>
										<td>{{ med.quantity || '-' }}</td>
										<td>{{ med.dosage || '-' }}</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</template>
	</AuthLayout>
</template>

<style scoped>
.card {
	border: none;
	border-radius: 8px;
	box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.card-header {
	border-bottom: 1px solid rgba(0,0,0,0.1);
}
.badge {
	font-size: 0.75rem;
	padding: 0.35em 0.65em;
	font-weight: 500;
}
</style>

