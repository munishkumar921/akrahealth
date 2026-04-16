<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    allergies: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    severityOptions: {
        type: Array,
        default: () => [],
    },
});

const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    status: props.filters?.status || "",
    severity: props.filters?.severity || "",
});

const statusOptions = [
    { value: "", label: "All status" },
    { value: "active", label: "Active" },
    { value: "inactive", label: "Inactive" },
];

const columns = [
    { label: "Substance or Medication", key: "allergies_medicine", type: "slot", slot: "substance", align: "left" },
    { label: "Reaction", key: "allergies_reaction", type: "slot", slot: "reaction", align: "left" },
    { label: "Severity", key: "allergies_severity", type: "slot", slot: "severity", align: "left" },
    { label: "Notes", key: "notes", type: "slot", slot: "notes", align: "left" },
    { label: "Active Date", key: "active_date_label", type: "slot", slot: "activeDate", align: "left" },
    { label: "Status", key: "status_label", type: "slot", slot: "status", align: "center" },
];

const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);

const hasActiveFilters = computed(() => activeFilterCount.value > 0);

const resultSummary = computed(() => {
    const total = props.allergies?.total ?? props.allergies?.data?.length ?? 0;
    const from = props.allergies?.from ?? (total ? 1 : 0);
    const to = props.allergies?.to ?? total;

    if (!total) {
        return "No allergies found";
    }

    return `Showing ${from}-${to} of ${total} allergies`;
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
    router.get(route("patient.allergies"), buildQuery({ page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        status: "",
        severity: "",
    };

    router.get(route("patient.allergies"), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("patient.allergies"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};
</script>

<template>
    <AuthLayout title="Allergies" description="Allergies" heading="Allergies">
        <div class="users-toolbar card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                    <div>
                        <h3 class="mb-1">Allergies</h3>
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
                    <div class="col-12 col-sm-6 col-xl-4">
                        <label class="form-label text-muted small text-uppercase mb-2">Search</label>
                        <div class="input-group allergies-search-control">
                            <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input
                                v-model="filterForm.keyword"
                                type="search"
                                class="form-control border-start-0"
                                placeholder="Search allergies"
                                @keydown.enter.prevent="applyFilters"
                            />
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
                        <label class="form-label text-muted small text-uppercase mb-2">Severity</label>
                        <select v-model="filterForm.severity" class="form-select" @change="applyFilters">
                            <option value="">All severity</option>
                            <option v-for="option in severityOptions" :key="option" :value="option">
                                {{ option }}
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
                        <label for="allergies-per-page" class="text-muted small text-uppercase mb-0">Rows</label>
                        <select
                            id="allergies-per-page"
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

                <Table :columns="columns" :data="allergies" :search-show="false" :PageOptions="false">
                    <template #substance="{ row }">
                        <div class="text-start">
                            <div class="fw-semibold text-dark">{{ row.allergies_medicine || "-" }}</div>
                        </div>
                    </template>

                    <template #reaction="{ row }">
                        <div class="text-start">
                            <div class="fw-medium text-dark">{{ row.allergies_reaction || "-" }}</div>
                        </div>
                    </template>

                    <template #severity="{ row }">
                        <div class="text-start">
                            <div class="fw-medium text-dark">{{ row.allergies_severity || "-" }}</div>
                        </div>
                    </template>

                    <template #notes="{ row }">
                        <div class="text-start">
                            <div class="fw-medium text-dark">{{ row.notes || "-" }}</div>
                        </div>
                    </template>

                    <template #activeDate="{ row }">
                        <div class="text-start">
                            <div class="fw-medium text-dark">{{ row.active_date_label || "-" }}</div>
                        </div>
                    </template>

                    <template #status="{ row }">
                        <div class="d-flex justify-content-center">
                            <span
                                class="status-pill"
                                :class="{
                                    'status-pill--active': row.status_label === 'Active',
                                    'status-pill--inactive': row.status_label === 'Inactive',
                                }"
                            >
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

.allergies-search-control {
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
