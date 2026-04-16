<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { route } from "ziggy-js";

const props = defineProps({
	pharmacyReports: {
		type: [Array, Object],
		default: () => [],
	},
	filters: {
		type: Object,
		default: () => ({
			keyword: "",
			status: "",
			payment_status: "",
		}),
	},
});

const filterForm = ref({
	keyword: props.filters?.keyword || "",
	status: props.filters?.status || "",
	payment_status: props.filters?.payment_status || "",
});

const columns = [
	{ label: "Order#", key: "order_id", type: "slot", slot: "order", align: "left" },
	{ label: "Pharmacy", key: "pharmacy", type: "slot", slot: "pharmacy", align: "left" },
	{ label: "Patient", key: "patient", type: "slot", slot: "patient", align: "left" },
	{ label: "Doctor", key: "doctor", type: "slot", slot: "doctor", align: "left" },
	{ label: "Amount", key: "amount", type: "slot", slot: "amount", align: "left" },
	{ label: "Payment", key: "payment_status", type: "slot", slot: "payment", align: "center" },
	{ label: "Status", key: "status", type: "slot", slot: "status", align: "center" },
	{ label: "Created", key: "created_at", type: "slot", slot: "created", align: "left" },
];

const statusOptions = [
	{ value: "", label: "All status" },
	{ value: "pending", label: "Pending" },
	{ value: "accepted", label: "Accepted" },
	{ value: "processing", label: "Processing" },
	{ value: "ready", label: "Ready" },
	{ value: "dispensed", label: "Dispensed" },
	{ value: "completed", label: "Completed" },
	{ value: "cancelled", label: "Cancelled" },
	{ value: "rejected", label: "Rejected" },
];

const paymentStatusOptions = [
	{ value: "", label: "All payment" },
	{ value: "paid", label: "Paid" },
	{ value: "pending", label: "Pending" },
	{ value: "failed", label: "Failed" },
	{ value: "refunded", label: "Refunded" },
];

const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);
const rows = computed(() => props.pharmacyReports?.data ?? []);
const activeFilterCount = computed(() =>
	Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);
const resultSummary = computed(() => {
	const total = props.pharmacyReports?.total ?? rows.value.length;
	const from = props.pharmacyReports?.from ?? (rows.value.length ? 1 : 0);
	const to = props.pharmacyReports?.to ?? rows.value.length;

	if (!total) {
		return "No pharmacy reports found";
	}

	return `Showing ${from}-${to} of ${total} pharmacy reports`;
});

const isEmpty = computed(() => rows.value.length === 0);

const buildQuery = (overrides = {}) => {
	const params = new URLSearchParams(window.location.search);
	const query = {
		per_page: params.get("per_page") || undefined,
		sort: params.get("sort") || undefined,
		direction: params.get("direction") || undefined,
		...filterForm.value,
		...overrides,
	};

	return Object.fromEntries(
		Object.entries(query).filter(([, value]) => value !== "" && value !== null && value !== undefined)
	);
};

const applyFilters = () => {
	router.get(route("pharmacyReports"), buildQuery(), {
		preserveState: true,
		preserveScroll: true,
		replace: true,
	});
};

const clearFilters = () => {
	filterForm.value = {
		keyword: "",
		status: "",
		payment_status: "",
	};

	router.get(route("pharmacyReports"), buildQuery(), {
		preserveState: true,
		preserveScroll: true,
		replace: true,
	});
};

const updatePerPage = () => {
	router.get(route("pharmacyReports"), buildQuery({ per_page: perPage.value, page: 1 }), {
		preserveState: true,
		preserveScroll: true,
		replace: true,
	});
};

const getStatusClass = (status) => {
	const lowerStatus = String(status || "").toLowerCase();

	if (["completed", "dispensed"].includes(lowerStatus)) return "status-pill--active";
	if (["pending", "accepted", "processing", "ready"].includes(lowerStatus)) return "status-pill--pending";

	return "status-pill--inactive";
};

const getPaymentStatusClass = (status) => {
	const lowerStatus = String(status || "").toLowerCase();

	if (lowerStatus === "paid") return "status-pill--active";
	if (lowerStatus === "pending") return "status-pill--pending";

	return "status-pill--inactive";
};

const formatAmount = (amount) => {
	if (!amount) return "₹0.00";
	return "₹" + Number(amount).toFixed(2);
};
</script>

<template>
	<AuthLayout title="Pharmacy Reports" description="View pharmacy reports" heading="Pharmacy Reports">
		<div class="pharmacy-reports-page">
			<div class="users-toolbar card border-0 shadow-sm mb-4">
				<div class="card-body">
					<div
						class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
						<div>
							<h3 class="mb-1">Pharmacy Reports</h3>
							<p class="text-muted mb-0">{{ resultSummary }}</p>
						</div>

						<div class="d-flex align-items-center gap-2 flex-wrap justify-content-lg-end">
							<span v-if="hasActiveFilters" class="filter-count-badge">
								{{ activeFilterCount }} filter{{ activeFilterCount > 1 ? "s" : "" }} active
							</span>
							<button v-if="hasActiveFilters" type="button" class="btn btn-outline-secondary btn-sm"
								@click="clearFilters">
								<i class="bi bi-x-circle me-1"></i>Clear filters
							</button>
						</div>
					</div>

					<div class="row g-3 align-items-end">
						<div class="col-12 col-xl-4">
							<label class="form-label text-muted small text-uppercase mb-2">Search</label>
							<div class="input-group pharmacy-reports-search-control">
								<span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
									<i class="bi bi-search text-muted"></i>
								</span>
								<input v-model="filterForm.keyword" type="search" class="form-control border-start-0"
									placeholder="Search by order, patient, doctor, or pharmacy"
									@keydown.enter.prevent="applyFilters" />
								<button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
							</div>
						</div>

						<div class="col-12 col-sm-6 col-xl-3">
							<label class="form-label text-muted small text-uppercase mb-2">Status</label>
							<select v-model="filterForm.status" class="form-select" @change="applyFilters">
								<option v-for="option in statusOptions" :key="option.value" :value="option.value">
									{{ option.label }}
								</option>
							</select>
						</div>

						<div class="col-12 col-sm-6 col-xl-3">
							<label class="form-label text-muted small text-uppercase mb-2">Payment</label>
							<select v-model="filterForm.payment_status" class="form-select" @change="applyFilters">
								<option v-for="option in paymentStatusOptions" :key="option.value"
									:value="option.value">
									{{ option.label }}
								</option>
							</select>
						</div>
					</div>
				</div>
			</div>

			<div v-if="isEmpty" class="text-center py-5">
				<i class="bi bi-prescription2 fs-1 text-muted"></i>
				<p class="mt-3 text-muted">No pharmacy reports found.</p>
			</div>

			<div v-else class="card border-0 shadow-sm">
				<div class="card-body p-0 p-md-3">
					<div class="d-flex justify-content-end align-items-center px-3 px-md-0 pt-3 pt-md-0 pb-2">
						<div class="d-flex align-items-center gap-2 rows-select-wrap">
							<select id="pharmacy-reports-per-page" v-model="perPage"
								class="form-select form-select-sm top-page-select" @change="updatePerPage">
								<option v-for="option in perPageOptions" :key="option" :value="option">
									{{ option }}
								</option>
							</select>
						</div>
					</div>

					<Table :columns="columns" :data="pharmacyReports" :search-show="false" :PageOptions="false">
						<template #order="{ row }">
							<div class="text-start">
								<div class="fw-semibold text-dark">{{ row.order_id || "N/A" }}</div>
							</div>
						</template>

						<template #pharmacy="{ row }">
							<div class="text-start">
								<div class="fw-medium text-dark">{{ row.pharmacy || "N/A" }}</div>
							</div>
						</template>

						<template #patient="{ row }">
							<div class="text-start">
								<div class="fw-medium text-dark">{{ row.patient || "N/A" }}</div>
							</div>
						</template>

						<template #doctor="{ row }">
							<div class="text-start">
								<div class="fw-medium text-dark">{{ row.doctor || "N/A" }}</div>
							</div>
						</template>

						<template #amount="{ row }">
							<div class="text-start">
								<div class="fw-semibold text-dark">{{ formatAmount(row.amount) }}</div>
							</div>
						</template>

						<template #payment="{ row }">
							<span class="status-pill" :class="getPaymentStatusClass(row.payment_status)">
								{{ row.payment_status_label || row.payment_status || "N/A" }}
							</span>
						</template>

						<template #status="{ row }">
							<span class="status-pill" :class="getStatusClass(row.status)">
								{{ row.status_label || row.status || "N/A" }}
							</span>
						</template>

						<template #created="{ row }">
							<div class="text-start">
								<div class="fw-medium text-dark">{{ row.created_at || "-" }}</div>
							</div>
						</template>

						<template #actions="{ row }">
							<button class="icon-btn btn btn-info" title="Download Report">
								<i class="bi bi-download"></i>
							</button>
						</template>
					</Table>
				</div>
			</div>
		</div>
	</AuthLayout>
</template>

<style scoped>
.users-toolbar {
	border-radius: 20px;
}

.filter-count-badge {
	display: inline-flex;
	align-items: center;
	padding: 0.45rem 0.75rem;
	border-radius: 999px;
	background: #eef2ff;
	color: #3730a3;
	font-size: 0.8rem;
	font-weight: 600;
}

.status-pill {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-width: 84px;
	padding: 0.35rem 0.7rem;
	border-radius: 999px;
	font-size: 0.75rem;
	font-weight: 700;
}

.status-pill--active {
	background: #dcfce7;
	color: #166534;
}

.status-pill--inactive {
	background: #f1f5f9;
	color: #475569;
}

.status-pill--pending {
	background: #fef3c7;
	color: #92400e;
}

.top-page-select {
	min-width: 74px;
	width: 74px;
}

.rows-select-wrap {
	flex: 0 0 auto;
}

.pharmacy-reports-search-control {
	max-width: 720px;
}

.icon-btn {
	padding: 0.25rem 0.5rem;
	font-size: 0.875rem;
}
</style>
