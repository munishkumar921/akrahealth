<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { route } from "ziggy-js";

const props = defineProps({
	notifications: {
		type: Object,
		default: () => ({}),
	},
	types: {
		type: Array,
		default: () => [],
	},
	filters: {
		type: Object,
		default: () => ({
			keyword: "",
			type: "",
			status: "",
		}),
	},
});

const filterForm = ref({
	keyword: props.filters?.keyword || "",
	type: props.filters?.type || "",
	status: props.filters?.status || "",
});

const statusOptions = [
	{ value: "", label: "All status" },
	{ value: "read", label: "Read" },
	{ value: "unread", label: "Unread" },
];

const columns = [
	{ label: "Title", key: "title", type: "slot", slot: "title", align: "left" },
	{ label: "Notification", key: "notification", type: "slot", slot: "message", align: "left" },
	{ label: "Type", key: "type", type: "slot", slot: "type", align: "left" },
	{ label: "Channel", key: "channel", type: "slot", slot: "channel", align: "center" },
	{ label: "Status", key: "status", type: "slot", slot: "status", align: "center" },
	{ label: "Sent At", key: "sent_at", type: "slot", slot: "sentAt", align: "left" },
];

const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);
const rows = computed(() => props.notifications?.data ?? []);
const activeFilterCount = computed(() =>
	Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);
const resultSummary = computed(() => {
	const total = props.notifications?.total ?? rows.value.length;
	const from = props.notifications?.from ?? (rows.value.length ? 1 : 0);
	const to = props.notifications?.to ?? rows.value.length;

	if (!total) {
		return "No notifications found";
	}

	return `Showing ${from}-${to} of ${total} notifications`;
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
	router.get(route("admin.notification"), buildQuery(), {
		preserveState: true,
		preserveScroll: true,
		replace: true,
	});
};

const clearFilters = () => {
	filterForm.value = {
		keyword: "",
		type: "",
		status: "",
	};

	router.get(route("admin.notification"), buildQuery(), {
		preserveState: true,
		preserveScroll: true,
		replace: true,
	});
};

const updatePerPage = () => {
	router.get(route("admin.notification"), buildQuery({ per_page: perPage.value, page: 1 }), {
		preserveState: true,
		preserveScroll: true,
		replace: true,
	});
};

const getStatusClass = (status) => {
	const value = String(status || "").toLowerCase();

	return value === "read" ? "status-pill--active" : "status-pill--inactive";
};
</script>

<template>
	<AuthLayout title="Notifications" description="View system notifications" heading="Notifications">
		<div class="notification-page">
			<div class="users-toolbar card border-0 shadow-sm mb-4">
				<div class="card-body">
					<div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
						<div>
							<h3 class="mb-1">Notifications</h3>
							<p class="text-muted mb-0">{{ resultSummary }}</p>
						</div>

						<div class="d-flex align-items-center gap-2 flex-wrap justify-content-lg-end">
							<span v-if="hasActiveFilters" class="filter-count-badge">
								{{ activeFilterCount }} filter{{ activeFilterCount > 1 ? "s" : "" }} active
							</span>
							<button
								v-if="hasActiveFilters"
								type="button"
								class="btn btn-outline-secondary btn-sm"
								@click="clearFilters"
							>
								<i class="bi bi-x-circle me-1"></i>Clear filters
							</button>
						</div>
					</div>

					<div class="row g-3 align-items-end">
						<div class="col-12 col-xl-4">
							<label class="form-label text-muted small text-uppercase mb-2">Search</label>
							<div class="input-group notification-search-control">
								<span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
									<i class="bi bi-search text-muted"></i>
								</span>
								<input
									v-model="filterForm.keyword"
									type="search"
									class="form-control border-start-0"
									placeholder="Search by title, message, or notification data"
									@keydown.enter.prevent="applyFilters"
								/>
								<button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
							</div>
						</div>

						<div class="col-12 col-sm-6 col-xl-3">
							<label class="form-label text-muted small text-uppercase mb-2">Type</label>
							<select v-model="filterForm.type" class="form-select" @change="applyFilters">
								<option value="">All types</option>
								<option v-for="type in types" :key="type" :value="type">
									{{ type }}
								</option>
							</select>
						</div>

						<div class="col-12 col-sm-6 col-xl-2">
							<label class="form-label text-muted small text-uppercase mb-2">Status</label>
							<select v-model="filterForm.status" class="form-select" @change="applyFilters">
								<option v-for="status in statusOptions" :key="status.value" :value="status.value">
									{{ status.label }}
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
							<select
								id="notifications-per-page"
								v-model="perPage"
								class="form-select form-select-sm top-page-select"
								@change="updatePerPage"
							>
								<option v-for="option in perPageOptions" :key="option" :value="option">
									{{ option }}
								</option>
							</select>
						</div>
					</div>

					<Table :columns="columns" :data="notifications" :search-show="false" :PageOptions="false">
						<template #title="{ row }">
							<div class="text-start">
								<div class="fw-semibold text-dark">{{ row.title || "Notification" }}</div>
							</div>
						</template>

						<template #message="{ row }">
							<div class="text-start">
								<div class="fw-medium text-dark">{{ row.notification || "No message" }}</div>
							</div>
						</template>

						<template #type="{ row }">
							<div class="text-start">
								<div class="fw-medium text-dark">{{ row.type || "-" }}</div>
							</div>
						</template>

						<template #channel="{ row }">
							<span class="channel-badge">{{ row.channel || "System" }}</span>
						</template>

						<template #status="{ row }">
							<span class="status-pill" :class="getStatusClass(row.status)">
								{{ row.status || "-" }}
							</span>
						</template>

						<template #sentAt="{ row }">
							<div class="text-start">
								<div class="fw-medium text-dark">{{ row.sent_at || "-" }}</div>
								<div v-if="row.read_at" class="text-muted small">Read: {{ row.read_at }}</div>
							</div>
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

.notification-search-control {
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
}

.status-pill--active {
	background: #dcfce7;
	color: #166534;
}

.status-pill--inactive {
	background: #f1f5f9;
	color: #475569;
}

.channel-badge {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	padding: 0.35rem 0.7rem;
	border-radius: 999px;
	background: #e2e8f0;
	color: #334155;
	font-size: 0.75rem;
	font-weight: 600;
}
</style>
