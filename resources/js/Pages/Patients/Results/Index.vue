<script setup>
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import AuthLayout from "@/Layouts/AuthLayout2.vue";

const props = defineProps({
    results: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const tabs = [
    { key: "Laboratory", label: "Laboratory", iconClass: "icon-success", icon: "fa-solid fa-vial" },
    { key: "Imaging", label: "Imaging", iconClass: "icon-warning", icon: "fa-solid fa-x-ray" },
];

const currentTab = ref(props.filters?.tab || "Laboratory");
const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);

const filterForm = ref({
    keyword: props.filters?.keyword || "",
});

const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);

const hasActiveFilters = computed(() => activeFilterCount.value > 0);

const resultSummary = computed(() => {
    const total = props.results?.total ?? props.results?.data?.length ?? 0;
    const from = props.results?.from ?? (total ? 1 : 0);
    const to = props.results?.to ?? total;

    if (!total) {
        return "No results found";
    }

    return `Showing ${from}-${to} of ${total} results`;
});

const resultColumns = [
    { label: "Result", key: "name", type: "slot", slot: "result_name", align: "left" },
    { label: "Value", key: "result", type: "slot", slot: "result_value", align: "left" },
    { label: "Reference", key: "reference", type: "slot", slot: "reference", align: "left" },
    { label: "Date", key: "created_at", type: "slot", slot: "result_date", align: "left" },
];

const buildQuery = (overrides = {}) => {
    const params = new URLSearchParams(window.location.search);
    const query = {
        per_page: params.get("per_page") || undefined,
        sort: params.get("sort") || undefined,
        direction: params.get("direction") || undefined,
        tab: currentTab.value,
        ...filterForm.value,
        ...overrides,
    };

    return Object.fromEntries(
        Object.entries(query).filter(([, value]) => value !== "" && value !== null && value !== undefined)
    );
};

const applyFilters = () => {
    router.get(route("patient.results"), buildQuery({ page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value.keyword = "";

    router.get(route("patient.results"), buildQuery({ keyword: undefined, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("patient.results"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const changeTab = (tab) => {
    currentTab.value = tab;
    router.get(route("patient.results"), buildQuery({ tab, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const showResult = (id) => {
    router.get(route("patient.results.show", id));
};
</script>

<template>
    <AuthLayout title="Results" description="View your test results" heading="Results">
        <div class="row g-4">
            <div class="col-12 col-xl-3">
                <div class="card border-0 shadow-sm results-side-card">
                    <div class="card-body">
                        <div class="finance-menu">
                            <button v-for="tab in tabs" :key="tab.key" type="button" class="menu-item"
                                :class="{ active: currentTab === tab.key }" @click="changeTab(tab.key)">
                                <i :class="`${tab.icon} ${tab.iconClass}`"></i>
                                <span class="label">{{ tab.label }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-9">
                <div class="users-toolbar card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div
                            class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                            <div>
                                <h3 class="mb-1">Results</h3>
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
                                <div class="input-group results-search-control">
                                    <span
                                        class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>
                                    <input v-model="filterForm.keyword" type="search"
                                        class="form-control border-start-0" placeholder="Search current tab"
                                        @keydown.enter.prevent="applyFilters" />
                                    <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0 p-md-3">
                        <div class="d-flex justify-content-end align-items-center px-3 px-md-0 pt-3 pt-md-0 pb-2">
                            <div class="d-flex align-items-center gap-2 rows-select-wrap">
                                <label for="results-per-page" class="text-muted small text-uppercase mb-0">Rows</label>
                                <select id="results-per-page" v-model="perPage"
                                    class="form-select form-select-sm top-page-select" @change="updatePerPage">
                                    <option v-for="option in perPageOptions" :key="option" :value="option">
                                        {{ option }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <Table :columns="resultColumns" :data="results" :search-show="false" :PageOptions="false">
                            <template #result_name="{ row }">
                                <div class="text-start">
                                    <div class="fw-semibold text-dark">{{ row.name || "-" }}</div>
                                    <div class="text-muted small">{{ row.code || "-" }}</div>
                                </div>
                            </template>

                            <template #result_value="{ row }">
                                <div class="text-start">
                                    <div class="fw-medium text-dark">{{ row.result || "-" }}</div>
                                    <div class="text-muted small">{{ row.units || "-" }}</div>
                                </div>
                            </template>

                            <template #reference="{ row }">
                                <div class="text-start">
                                    <div class="fw-medium text-dark">{{ row.reference || "-" }}</div>
                                </div>
                            </template>

                            <template #result_date="{ row }">
                                <div class="text-start">
                                    <div class="fw-medium text-dark">{{ row.created_at || "-" }}</div>
                                </div>
                            </template>

                            <template #actions="{ row }">
                                <div class="d-flex gap-2 justify-content-end">
                                    <button class="btn btn-success action-btn" @click="showResult(row.id)" title="View">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </template>
                        </Table>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<style scoped>
.users-toolbar,
.results-side-card {
    border-radius: 20px;
}

.finance-menu {
    display: flex;
    flex-direction: column;
    gap: 0.625rem;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    width: 100%;
    padding: 0.85rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    background: #fff;
    color: #334155;
    text-align: left;
    transition: all 0.2s ease;
}

.menu-item.active {
    background: #eff6ff;
    border-color: #93c5fd;
    color: #1d4ed8;
}

.label {
    font-weight: 600;
    font-size: 0.95rem;
}

.icon-success {
    color: #22c55e;
}

.icon-warning {
    color: #f59e0b;
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

.results-search-control {
    max-width: 100%;
}

.rows-select-wrap {
    min-width: 118px;
}

.top-page-select {
    width: 84px;
}

.action-btn {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
}
</style>
