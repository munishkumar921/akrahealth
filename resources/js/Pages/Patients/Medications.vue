<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    medications: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    prescriptionStatuses: {
        type: Array,
        default: () => [],
    },
});

const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    status: props.filters?.status || "",
    prescription_status: props.filters?.prescription_status || "",
});

const columns = [
    { label: "Medication", key: "medication", type: "slot", slot: "medication", align: "left" },
    { label: "Date Active", key: "date_active", align: "left" },
    { label: "Date Inactive", key: "date_inactive", align: "left" },
    { label: "Due Date", key: "due_date", align: "left" },
    { label: "Prescription Status", key: "prescription", type: "slot", slot: "prescription", align: "left" },
    { label: "Status", key: "status_label", type: "slot", slot: "status", align: "center" },
];

const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);

const hasActiveFilters = computed(() => activeFilterCount.value > 0);

const resultSummary = computed(() => {
    const total = props.medications?.total ?? props.medications?.data?.length ?? 0;
    const from = props.medications?.from ?? (total ? 1 : 0);
    const to = props.medications?.to ?? total;

    if (!total) {
        return "No medications found";
    }

    return `Showing ${from}-${to} of ${total} medications`;
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
    router.get(route("patient.medications"), buildQuery({ page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        status: "",
        prescription_status: "",
    };

    router.get(route("patient.medications"), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("patient.medications"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};
</script>

<template>
    <AuthLayout title="Medications" description="Medications" heading="Medications">
        <div class="users-toolbar card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                    <div>
                        <h3 class="mb-1">Medications</h3>
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
                    <div class="col-12 col-sm-6 col-xl-4">
                        <label class="form-label text-muted small text-uppercase mb-2">Search</label>
                        <div class="input-group medications-search-control">
                            <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input v-model="filterForm.keyword" type="search" class="form-control border-start-0"
                                placeholder="Search medications" @keydown.enter.prevent="applyFilters" />
                            <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <label class="form-label text-muted small text-uppercase mb-2">Status</label>
                        <select v-model="filterForm.status" class="form-select" @change="applyFilters">
                            <option value="">All status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <label class="form-label text-muted small text-uppercase mb-2">Prescription Status</label>
                        <select v-model="filterForm.prescription_status" class="form-select" @change="applyFilters">
                            <option value="">All prescription status</option>
                            <option v-for="status in prescriptionStatuses" :key="status" :value="status">
                                {{ status }}
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
                        <label for="medications-per-page" class="text-muted small text-uppercase mb-0">Rows</label>
                        <select id="medications-per-page" v-model="perPage"
                            class="form-select form-select-sm top-page-select" @change="updatePerPage">
                            <option v-for="option in perPageOptions" :key="option" :value="option">
                                {{ option }}
                            </option>
                        </select>
                    </div>
                </div>

                <Table :columns="columns" :data="medications" :search-show="false" :PageOptions="false">
                    <template #medication="{ row }">
                        <div class="text-start">
                            <div class="fw-semibold text-dark">{{ row.medication || "-" }}</div>
                        </div>
                    </template>

                    <template #prescription="{ row }">
                        <div class="text-start">
                            <div class="fw-medium text-dark">{{ row.prescription || "-" }}</div>
                        </div>
                    </template>

                    <template #status="{ row }">
                        <div class="d-flex justify-content-center">
                            <span class="status-pill" :class="{
                                'status-pill--active': row.status_label === 'Active',
                                'status-pill--inactive': row.status_label === 'Inactive',
                            }">
                                {{ row.status_label }}
                            </span>
                        </div>
                    </template>
                </Table>
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

.medications-search-control {
    max-width: 100%;
}

.rows-select-wrap {
    min-width: 118px;
}

.top-page-select {
    width: 84px;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 90px;
    padding: 0.45rem 0.8rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
    background: #e2e8f0;
    color: #475569;
}

.status-pill--active {
    background: #dcfce7;
    color: #166534;
}

.status-pill--inactive {
    background: #e2e8f0;
    color: #475569;
}
</style>
