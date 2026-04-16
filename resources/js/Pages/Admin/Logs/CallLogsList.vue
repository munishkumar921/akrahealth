<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const props = defineProps({
	appointments: {
		type: Object,
		default: () => ({}),
	},
	filters: {
		type: Object,
		default: () => ({
			keyword: "",
			status: "",
		}),
	},
});

const filterForm = ref({
	keyword: props.filters?.keyword || "",
	status: props.filters?.status || "",
});

const statusOptions = [
	{ value: "", label: "All status" },
	{ value: "completed", label: "Completed" },
	{ value: "pending", label: "Missed" },
	{ value: "cancelled", label: "Cancelled" },
];

const columns = [
	{ label: "Patient", key: "patient.user.name", align: "left" },
	{ label: "Doctor", key: "doctor.user.name", align: "left" },
	{ label: "Type", key: "appointment_mode", align: "left" },
	{ label: "Appointment", key: "appointment_date", align: "left" },
	{ label: "Call Start Time", key: "appointment_time", align: "left" },
	{ label: "Call Duration", key: "duration_minutes", type: "slot", slot: "duration", align: "left" },
	{ label: "Reason", key: "reason", align: "left" },
	{ label: "Call Status", key: "status", type: "slot", slot: "status", align: "center" },
];

const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 20);
const rows = computed(() => props.appointments?.data ?? []);
const activeFilterCount = computed(() =>
	Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);
const resultSummary = computed(() => {
	const total = props.appointments?.total ?? rows.value.length;
	const from = props.appointments?.from ?? (rows.value.length ? 1 : 0);
	const to = props.appointments?.to ?? rows.value.length;

	if (!total) {
		return "No call logs found";
	}

	return `Showing ${from}-${to} of ${total} call logs`;
});

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
	router.get(route("admin.callLogs.list"), buildQuery(), {
		preserveState: true,
		preserveScroll: true,
		replace: true,
	});
};

const clearFilters = () => {
	filterForm.value = {
		keyword: "",
		status: "",
	};

	router.get(route("admin.callLogs.list"), buildQuery(), {
		preserveState: true,
		preserveScroll: true,
		replace: true,
	});
};

const updatePerPage = () => {
	router.get(route("admin.callLogs.list"), buildQuery({ per_page: perPage.value, page: 1 }), {
		preserveState: true,
		preserveScroll: true,
		replace: true,
	});
};

const getStatusClass = (status) => {
	const value = String(status || "").toLowerCase();
	if (value === "completed") return "status-pill--active";
	if (value === "pending") return "status-pill--pending";
	return "status-pill--inactive";
};
</script>

<template>
	<AuthLayout title="Call Logs" description="View call logs" heading="Call Logs">
		<div class="call-logs-page">
			<div class="users-toolbar card border-0 shadow-sm mb-4">
				<div class="card-body">
					<div
						class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
						<div>
							<h3 class="mb-1">Call Logs</h3>
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
							<div class="input-group call-logs-search-control">
								<span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
									<i class="bi bi-search text-muted"></i>
								</span>
								<input v-model="filterForm.keyword" type="search" class="form-control border-start-0"
									placeholder="Search by patient, doctor, reason, type, or date"
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
					</div>
				</div>
			</div>

			<div class="card border-0 shadow-sm">
				<div class="card-body p-0 p-md-3">
					<div class="d-flex justify-content-end align-items-center px-3 px-md-0 pt-3 pt-md-0 pb-2">
						<div class="d-flex align-items-center gap-2 rows-select-wrap">
							<select id="call-logs-per-page" v-model="perPage"
								class="form-select form-select-sm top-page-select" @change="updatePerPage">
								<option v-for="option in perPageOptions" :key="option" :value="option">
									{{ option }}
								</option>
							</select>
						</div>
					</div>

					<Table :columns="columns" :data="appointments" :search-show="false" :PageOptions="false">
						<template #duration="{ row }">
							<div class="text-start">
								<div class="fw-medium text-dark">{{ row.duration_minutes ? `${row.duration_minutes} min`
									: "-" }}</div>
							</div>
						</template>

						<template #status="{ row }">
							<span class="status-pill" :class="getStatusClass(row.status)">
								{{ row.status || "-" }}
							</span>
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

.top-page-select {
	min-width: 74px;
	width: 74px;
}

.rows-select-wrap {
	flex: 0 0 auto;
}

.call-logs-search-control {
	max-width: 720px;
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
	text-transform: capitalize;
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
</style>
