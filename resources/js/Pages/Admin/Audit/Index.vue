<script setup>
import { ref, computed } from "vue";
import { usePage, Link, router } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import BaseDatePicker from "@/Components/Common/Input/BaseDatePicker.vue";
import { route } from "ziggy-js";

const props = defineProps({
    auditLogs: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({
            keyword: "",
            module: "",
            action: "",
            user_id: "",
            admin_id: "",
            date_from: "",
            date_to: "",
        }),
    },
    modules: {
        type: Array,
        default: () => [],
    },
    actions: {
        type: Array,
        default: () => [],
    },
});

const filterForm = ref({
    keyword: props.filters.keyword || "",
    module: props.filters.module || "",
    action: props.filters.action || "",
    date_from: props.filters.date_from || "",
    date_to: props.filters.date_to || "",
});

const columns = [
    { label: "User", key: "user", type: "slot", slot: "user", align: "left" },
    { label: "Module", key: "module_label", type: "slot", slot: "module", align: "left" },
    { label: "Action", key: "action", type: "slot", slot: "action", align: "center" },
    { label: "Description", key: "description", type: "slot", slot: "description", align: "left" },
    { label: "Date", key: "formatted_date", type: "slot", slot: "date", align: "left" },
];

const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 15);
const rows = computed(() => props.auditLogs?.data ?? []);
const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);
const resultSummary = computed(() => {
    const total = props.auditLogs?.total ?? rows.value.length;
    const from = props.auditLogs?.from ?? (rows.value.length ? 1 : 0);
    const to = props.auditLogs?.to ?? rows.value.length;

    if (!total) {
        return "No audit logs found";
    }

    return `Showing ${from}-${to} of ${total} audit logs`;
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
    router.get(route("admin.audit-logs.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        module: "",
        action: "",
        date_from: "",
        date_to: "",
    };

    router.get(route("admin.audit-logs.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("admin.audit-logs.index"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const exportWithFilters = (targetRoute) => {
    const params = new URLSearchParams(buildQuery());
    window.location.href = route(targetRoute) + (params.toString() ? `?${params.toString()}` : "");
};

const exportCsv = () => exportWithFilters("admin.audit-logs.export.csv");
const exportPdf = () => exportWithFilters("admin.audit-logs.export.pdf");

const capitalize = (str) => {
    if (!str) return "";
    return str.charAt(0).toUpperCase() + str.slice(1);
};

const getActionClass = (action) => {
    const value = String(action || "").toLowerCase();
    if (value === "create") return "status-pill--active";
    if (value === "update" || value === "view") return "status-pill--pending";
    return "status-pill--inactive";
};
</script>

<template>
    <AuthLayout title="Audit Logs" description="Audit Logs" heading="Audit Logs">
        <div class="audit-page">
            <div class="users-toolbar card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div
                        class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                        <div>
                            <h3 class="mb-1">Audit Logs</h3>
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
                            <button class="btn btn-success" @click="exportCsv">
                                <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export CSV
                            </button>
                            <button class="btn btn-danger" @click="exportPdf">
                                <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
                            </button>
                        </div>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-xl-4">
                            <label class="form-label text-muted small text-uppercase mb-2">Search</label>
                            <div class="input-group audit-search-control">
                                <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input v-model="filterForm.keyword" type="search" class="form-control border-start-0"
                                    placeholder="Search by user, module, action, or description"
                                    @keydown.enter.prevent="applyFilters" />
                                <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Module</label>
                            <select v-model="filterForm.module" class="form-select" @change="applyFilters">
                                <option value="">All modules</option>
                                <option v-for="module in modules" :key="module" :value="module">
                                    {{ capitalize(module) }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Action</label>
                            <select v-model="filterForm.action" class="form-select" @change="applyFilters">
                                <option value="">All actions</option>
                                <option v-for="action in actions" :key="action" :value="action">
                                    {{ capitalize(action) }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase">From</label>
                            <BaseDatePicker v-model="filterForm.date_from" marginBottom="0px" type="date" placeholder="Select date" />
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase">To</label>
                            <BaseDatePicker v-model="filterForm.date_to" marginBottom="0px" type="date" placeholder="Select date" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0 p-md-3">
                    <div class="d-flex justify-content-end align-items-center px-3 px-md-0 pt-3 pt-md-0 pb-2">
                        <div class="d-flex align-items-center gap-2 rows-select-wrap">
                            <select id="audit-logs-per-page" v-model="perPage"
                                class="form-select form-select-sm top-page-select" @change="updatePerPage">
                                <option v-for="option in perPageOptions" :key="option" :value="option">
                                    {{ option }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <Table :columns="columns" :data="auditLogs" :search-show="false" :PageOptions="false">
                        <template #user="{ row }">
                            <div class="text-start">
                                <div class="fw-semibold text-dark">{{ row.user || "-" }}</div>
                                <div v-if="row.user_email" class="text-muted small">{{ row.user_email }}</div>
                            </div>
                        </template>

                        <template #module="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.module_label || row.module || "-" }}</div>
                            </div>
                        </template>

                        <template #action="{ row }">
                            <span class="status-pill" :class="getActionClass(row.action)">
                                {{ capitalize(row.action) || "-" }}
                            </span>
                        </template>

                        <template #description="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.description || "-" }}</div>
                                <div v-if="row.ip_address" class="text-muted small">{{ row.ip_address }}</div>
                            </div>
                        </template>

                        <template #date="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.formatted_date || row.created_at }}</div>
                            </div>
                        </template>

                        <template #actions="{ row }">
                            <Link :href="route('admin.audit-logs.show', row.id)" class="btn btn-primary icon-btn"
                                title="View Details">
                                <i class="bi bi-eye"></i>
                            </Link>
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

.audit-search-control {
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

.status-pill--pending {
    background: #fef3c7;
    color: #92400e;
}

.icon-btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>
