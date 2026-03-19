<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import { ref } from "vue";
import axios from "axios";
import Swal from 'sweetalert2/dist/sweetalert2.js';
import 'sweetalert2/src/sweetalert2.scss';
import { route } from "ziggy-js";

const props = defineProps({
	search: String,
	invoices: {
		type: [Array, Object],
		default: () => []
	},
	route: Array,
});

const columns = [
	{ label: "Appointment ID", key: "appointment_id" },
	{ label: "Invoice #", key: "invoice_number" },
	{ label: "Razorpay Payment ID", key: "razorpay_payment_id" },
	{ label: "Name", key: "patient.user.name" },
	{ label: "Amount (₹)", key: "total_amount" },
	{ label: "Currency", key: "currency" },
	{ label: "Status", key: "status" },
	{ label: "Created", key: "created_at" },
];

const downloadInvoice = async (row) => {
	// Validate row data exists
	if (!row || !row.id) {
		Swal.fire({
			title: 'Error!',
			text: 'Invalid invoice data. Please refresh the page and try again.',
			icon: 'error'
		});
		return;
	}

	// Show loading
	Swal.fire({
		title: 'Generating Invoice...',
		text: 'Please wait while we prepare your invoice.',
		icon: 'info',
		allowOutsideClick: false,
		showConfirmButton: false,
		willOpen: () => {
			Swal.showLoading();
		}
	});

	try {
		// Build URL and fetch invoice data
		const url = route('admin.invoice.download', { id: row.id });
		console.log('Downloading invoice from:', url);

		const response = await axios.get(url, {
			timeout: 30000, // 30 seconds timeout
			responseType: 'blob'
		});

		// Create and download file - use invoice_number for filename
		const blob = new Blob([response.data], { type: 'application/pdf' });
		const downloadUrl = window.URL.createObjectURL(blob);
		const link = document.createElement('a');
		link.href = downloadUrl;
		link.download = `Invoice-${row.invoice_number || row.id}.pdf`;
		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
		window.URL.revokeObjectURL(downloadUrl);

		Swal.close();
		Swal.fire({
			title: 'Success!',
			text: 'Invoice downloaded successfully.',
			icon: 'success',
			timer: 2000,
			showConfirmButton: false
		});
	} catch (error) {
		console.error('Error downloading invoice:', error);

		// Handle specific error types
		let errorMessage = 'Failed to download invoice. Please try again.';

		if (error.code === 'ECONNABORTED') {
			errorMessage = 'Request timed out. The server is taking too long to respond. Please try again.';
		} else if (error.response) {
			// Server responded with error
			if (error.response.status === 404) {
				errorMessage = 'Invoice not found. It may have been deleted or moved.';
			} else if (error.response.status === 500) {
				errorMessage = 'Server error. Please contact support if the problem persists.';
			} else if (error.response.data?.message) {
				errorMessage = error.response.data.message;
			} else if (error.response.statusText) {
				errorMessage = `Server error: ${error.response.statusText}`;
			}
		} else if (error.request) {
			// Request made but no response
			errorMessage = 'No response from server. Please check your internet connection and try again.';
		} else if (error.message) {
			// Error in setting up request
			errorMessage = error.message;
		}

		Swal.fire({
			title: 'Download Failed',
			text: errorMessage,
			icon: 'error',
			showConfirmButton: true,
			confirmButtonText: 'Retry'
		}).then((result) => {
			if (result.isConfirmed) {
				// Retry download
				downloadInvoice(row);
			}
		});
	}
};

const removeRow = (row) => {
	const ok = window.confirm(`Delete invoice ${row.invoice_number || row.id}? This cannot be undone.`);
	if (!ok) return;
	// In a real implementation, this would make an API call
	alert('Invoice deleted successfully');
};

const getStatusClass = (status) => {
	const statusMap = {
		'draft': 'badge bg-secondary',
		'sent': 'badge bg-info',
		'viewed': 'badge bg-primary',
		'partial': 'badge bg-warning',
		'paid': 'badge bg-success',
		'overdue': 'badge bg-danger',
		'cancelled': 'badge bg-dark',
		'failed': 'badge bg-danger',
		'active': 'badge bg-success',
		'inactive': 'badge bg-secondary',
		'pending': 'badge bg-warning',
		'completed': 'badge bg-success',
		'trial': 'badge bg-info',
	};

	const lowerStatus = status?.toLowerCase();
	return statusMap[lowerStatus] || 'badge bg-secondary';
};

const getAmountClass = (amount) => {
	return amount > 0 ? 'text-success' : 'text-muted';
};
</script>

<template>
	<AuthLayout title="Invoices" description="Invoices" heading="Invoices">
		<div class="d-flex align-items-center justify-content-between">
			<h3 class="d-flex align-items-center text-xl mb-2">Invoices</h3>

			<div class="d-flex align-items-center gap-3">
			</div>
		</div>
		<Table :columns="columns" :data="invoices.data" :search="search">

			<template #invoice_number="{ row }">
				<span class="fw-bold">{{ row.invoice_number || row.id }}</span>
			</template>
			<template #razorpay_invoice_id="{ row }">
				<span class="text-muted small">{{ row.razorpay_invoice_id || '-' }}</span>
			</template>
			<template #razorpay_payment_id="{ row }">
				<span class="text-muted small">{{ row.razorpay_payment_id || '-' }}</span>
			</template>
			<template #patient_name="{ row }">
				<span>{{ row.patient_name || row.user_name || 'N/A' }}</span>
			</template>
			<template #plan_name="{ row }">
				<span>{{ row.plan_name || '-' }}</span>
			</template>
			<template #total_amount="{ row }">
				<span :class="getAmountClass(row.total_amount)">
					{{ row.currency === 'USD' ? '$' : row.currency === 'EUR' ? '€' : '₹' }}{{ Number(row.total_amount ||
						0).toFixed(2) }}
				</span>
			</template>
			<template #currency="{ row }">
				<span class="badge bg-light text-dark">{{ row.currency || 'INR' }}</span>
			</template>
			<template #status="{ row }">
				<span :class="getStatusClass(row.status)">
					{{ row.status_label || row.status || 'N/A' }}
				</span>
			</template>
			<template #due_date="{ row }">
				<span>{{ row.due_date || '-' }}</span>
			</template>
			<template #created_at="{ row }">
				<span>{{ row.created_at || '-' }}</span>
			</template>
			<template #actions="{ row }">
				<a class="btn btn-outline-primary ml-2"
					:href="route('admin.invoice.print', { id: row.appointment_id })" title="Print" target="_blank">
					<i class="fa fa-print"></i>
				</a>
			</template>
		</Table>
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
	margin: 0 2px;
}

.icon-btn:active {
	transform: scale(0.97);
}

.icon-btn--red {
	background: #ef4444;
}

.icon-btn i {
	font-size: 14px;
	line-height: 1;
}

.icon-btn.btn-primary {
	background: #0d6efd;
}

.icon-btn.btn-danger {
	background: #dc3545;
}

.icon-btn:hover {
	opacity: 0.85;
}

.modal-content {
	border-radius: 12px;
}

.modal-title {
	font-size: 20px;
}

.form-label {
	font-weight: 600;
	color: #374151;
}

.modal-overlay {
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background-color: rgba(0, 0, 0, 0.5);
	display: flex;
	justify-content: center;
	align-items: center;
	z-index: 9999;
	padding: 20px;
}

.modal-container {
	background: white;
	border-radius: 8px;
	box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
	width: 100%;
	max-width: 600px;
	max-height: 90vh;
	overflow: hidden;
	display: flex;
	flex-direction: column;
}

.modal-content {
	display: flex;
	flex-direction: column;
	height: 100%;
}

.modal-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 1rem 1.5rem;
	border-bottom: 1px solid #dee2e6;
	background-color: #f8f9fa;
}

.modal-title {
	font-size: 1.25rem;
	font-weight: 600;
	color: #333;
}

.close {
	background: none;
	border: none;
	font-size: 1.5rem;
	line-height: 1;
	color: #000;
	opacity: .5;
	cursor: pointer;
	padding: 0;
	width: 30px;
	height: 30px;
	border-radius: 50%;
}

.close:hover {
	opacity: 1;
	background-color: rgba(0, 0, 0, .1);
}

.modal-body {
	flex: 1;
	overflow-y: auto;
	padding: 1.5rem;
	max-height: calc(90vh - 140px);
}

.modal-footer {
	display: flex;
	justify-content: flex-end;
	gap: 10px;
	padding: 1rem 1.5rem;
	border-top: 1px solid #dee2e6;
	background-color: #f8f9fa;
}
</style>
