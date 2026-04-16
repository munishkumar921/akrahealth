<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    immunizations: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    routeOptions: {
        type: Array,
        default: () => [],
    },
    bodySiteOptions: {
        type: Array,
        default: () => [],
    },
});

const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    route_name: props.filters?.route_name || "",
    body_site: props.filters?.body_site || "",
});

const columns = [
    { label: "Immunization", key: "immunization", type: "slot", slot: "immunization", align: "left" },
    { label: "Dosage", key: "dosage", type: "slot", slot: "dosage", align: "left" },
    { label: "Sequence", key: "sequence", type: "slot", slot: "sequence", align: "left" },
    { label: "Route", key: "route", type: "slot", slot: "route", align: "left" },
    { label: "Body Site", key: "body_site", type: "slot", slot: "bodySite", align: "left" },
    { label: "Manufacturer", key: "manufacturer", type: "slot", slot: "manufacturer", align: "left" },
];

const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);

const hasActiveFilters = computed(() => activeFilterCount.value > 0);

const resultSummary = computed(() => {
    const total = props.immunizations?.total ?? props.immunizations?.data?.length ?? 0;
    const from = props.immunizations?.from ?? (total ? 1 : 0);
    const to = props.immunizations?.to ?? total;

    if (!total) {
        return "No immunizations found";
    }

    return `Showing ${from}-${to} of ${total} immunizations`;
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
    router.get(route("patient.immunizations"), buildQuery({ page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        route_name: "",
        body_site: "",
    };

    router.get(route("patient.immunizations"), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("patient.immunizations"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};
</script>

<template>
    <AuthLayout title="Immunizations" description="Immunizations" heading="Immunizations">
        <div class="users-toolbar card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                    <div>
                        <h3 class="mb-1">Immunizations</h3>
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
                        <div class="input-group immunizations-search-control">
                            <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input
                                v-model="filterForm.keyword"
                                type="search"
                                class="form-control border-start-0"
                                placeholder="Search immunizations"
                                @keydown.enter.prevent="applyFilters"
                            />
                            <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <label class="form-label text-muted small text-uppercase mb-2">Route</label>
                        <select v-model="filterForm.route_name" class="form-select" @change="applyFilters">
                            <option value="">All routes</option>
                            <option v-for="option in routeOptions" :key="option" :value="option">
                                {{ option }}
                            </option>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <label class="form-label text-muted small text-uppercase mb-2">Body Site</label>
                        <select v-model="filterForm.body_site" class="form-select" @change="applyFilters">
                            <option value="">All body sites</option>
                            <option v-for="option in bodySiteOptions" :key="option" :value="option">
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
                        <label for="immunizations-per-page" class="text-muted small text-uppercase mb-0">Rows</label>
                        <select
                            id="immunizations-per-page"
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

                <Table :columns="columns" :data="immunizations" :search-show="false" :PageOptions="false">
                    <template #immunization="{ row }">
                        <div class="text-start">
                            <div class="fw-semibold text-dark">{{ row.immunization || "-" }}</div>
                            <div class="text-muted small">{{ row.brand || row.cvx_code || "--" }}</div>
                        </div>
                    </template>

                    <template #dosage="{ row }">
                        <div class="text-start">
                            <div class="fw-medium text-dark">
                                {{ [row.dosage, row.dosage_unit].filter(Boolean).join(" ") || "-" }}
                            </div>
                        </div>
                    </template>

                    <template #sequence="{ row }">
                        <div class="text-start">
                            <div class="fw-medium text-dark">{{ row.sequence || "-" }}</div>
                        </div>
                    </template>

                    <template #route="{ row }">
                        <div class="text-start">
                            <div class="fw-medium text-dark">{{ row.route || "-" }}</div>
                        </div>
                    </template>

                    <template #bodySite="{ row }">
                        <div class="text-start">
                            <div class="fw-medium text-dark">{{ row.body_site || "-" }}</div>
                        </div>
                    </template>

                    <template #manufacturer="{ row }">
                        <div class="text-start">
                            <div class="fw-medium text-dark">{{ row.manufacturer || "-" }}</div>
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

.immunizations-search-control {
    max-width: 100%;
}

.rows-select-wrap {
    min-width: 118px;
}

.top-page-select {
    width: 84px;
}
</style>
