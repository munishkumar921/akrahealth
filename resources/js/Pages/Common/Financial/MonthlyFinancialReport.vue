<script setup>
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import AuthLayout2 from "@/Layouts/AuthLayout2.vue";
import Table from "@/Components/Table/Table.vue";
import Tab from "./Tab.vue";

const props = defineProps({
    bills: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const currentTab = ref("monthly_financial_report");
const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(
    Number(new URLSearchParams(window.location.search).get("per_page")) || Number(props.bills?.per_page) || 10
);
const filterForm = ref({
    keyword: props.filters?.keyword || "",
});

const columns = [
    { label: "Month", key: "month_label", type: "slot", slot: "month", align: "left" },
    { label: "Patients Seen", key: "patients_seen", type: "slot", slot: "patientsSeen", align: "left" },
    { label: "Total Billed", key: "total_billed_label", type: "slot", slot: "totalBilled", align: "left" },
    { label: "Total Payments", key: "total_payments_label", type: "slot", slot: "totalPayments", align: "left" },
    { label: "DNKA", key: "dnka", type: "slot", slot: "dnka", align: "left" },
    { label: "LMC", key: "lmc", type: "slot", slot: "lmc", align: "left" },
];

const buildQuery = (overrides = {}) => {
    const currentRoute = route().current();
    const query = {
        ...filterForm.value,
        per_page: perPage.value,
        ...overrides,
    };

    return {
        currentRoute,
        params: Object.fromEntries(
            Object.entries(query).filter(([, value]) => value !== "" && value !== null && value !== undefined)
        ),
    };
};

const applyFilters = () => {
    const { currentRoute, params } = buildQuery({ page: 1 });
    router.get(route(currentRoute), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
    };

    const currentRoute = route().current();
    router.get(route(currentRoute), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    const { currentRoute, params } = buildQuery({ per_page: perPage.value, page: 1 });
    router.get(route(currentRoute), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const openInsuranceDrilldown = (row) => {
    router.get(route("doctor.finance.financial_insurance", { date: row?.month }));
};

const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);

const hasActiveFilters = computed(() => activeFilterCount.value > 0);

const resultSummary = computed(() => {
    const total = props.bills?.total ?? props.bills?.data?.length ?? 0;
    const from = props.bills?.from ?? (total ? 1 : 0);
    const to = props.bills?.to ?? total;

    if (!total) {
        return "No monthly financial data found";
    }

    return `Showing ${from}-${to} of ${total} monthly report rows`;
});
</script>

<template>
    <AuthLayout2
        title="Monthly Financial Report"
        description="View monthly financial reports"
        heading="Monthly Financial Report"
    >
        <div class="row g-4">
            <Tab :currentTab="currentTab" />

            <div class="col-lg-9">
                <div class="users-toolbar card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                            <div>
                                <h3 class="mb-1">Monthly Financial Report</h3>
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
                                <div class="input-group monthly-search-control">
                                    <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>
                                    <input
                                        v-model="filterForm.keyword"
                                        type="search"
                                        class="form-control border-start-0"
                                        placeholder="Search month or totals"
                                        @keydown.enter.prevent="applyFilters"
                                    />
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
                                <label for="monthly-financial-per-page" class="text-muted small text-uppercase mb-0">Rows</label>
                                <select
                                    id="monthly-financial-per-page"
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

                        <Table
                            :columns="columns"
                            :data="bills"
                            :search-show="false"
                            :PageOptions="false"
                            class="cursor-pointer"
                            @cell-click="({ row }) => openInsuranceDrilldown(row)"
                        >
                            <template #month="{ row }">
                                <div class="text-start">
                                    <div class="fw-semibold text-dark">{{ row.month_label || "-" }}</div>
                                </div>
                            </template>

                            <template #patientsSeen="{ row }">
                                <div class="text-start">
                                    <span class="stat-pill">{{ row.patients_seen ?? 0 }}</span>
                                </div>
                            </template>

                            <template #totalBilled="{ row }">
                                <div class="text-start">
                                    <div class="fw-medium text-dark">{{ row.total_billed_label || "-" }}</div>
                                </div>
                            </template>

                            <template #totalPayments="{ row }">
                                <div class="text-start">
                                    <div class="fw-medium text-dark">{{ row.total_payments_label || "-" }}</div>
                                </div>
                            </template>

                            <template #dnka="{ row }">
                                <div class="text-start">
                                    <span class="metric-pill metric-warning">{{ row.dnka ?? 0 }}</span>
                                </div>
                            </template>

                            <template #lmc="{ row }">
                                <div class="text-start">
                                    <span class="metric-pill metric-secondary">{{ row.lmc ?? 0 }}</span>
                                </div>
                            </template>
                        </Table>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout2>
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

.monthly-search-control {
    max-width: 100%;
}

.rows-select-wrap {
    min-width: 118px;
}

.top-page-select {
    width: 84px;
}

.stat-pill,
.metric-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 56px;
    padding: 0.42rem 0.85rem;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 700;
}

.stat-pill {
    background: #e0f2fe;
    color: #075985;
}

.metric-warning {
    background: #fef3c7;
    color: #92400e;
}

.metric-secondary {
    background: #ede9fe;
    color: #5b21b6;
}
</style>
