<script setup>
import AuthLayout from "@/Layouts/AuthLayout2.vue";
import Table from "@/Components/Table/Table.vue";
import { Link, router } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";

const props = defineProps({
    forms: {
        type: Array,
        default: () => [],
    },
    completedForms: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const currentTab = ref("Forms to Fill Out");
const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(10);
const currentPage = ref(1);

const filterForm = ref({
    keyword: props.filters?.keyword || "",
});

const tabs = [
    { key: "Forms to Fill Out", label: "Forms to Fill Out", iconClass: "icon-warning", icon: "fa-regular fa-clipboard" },
    { key: "Completed Forms", label: "Completed Forms", iconClass: "icon-success", icon: "fa-solid fa-check-circle" },
];

const formsColumns = [
    { label: "Form", key: "label", type: "slot", slot: "form", align: "left" },
];

const completedColumns = [
    { label: "Completed Form", key: "title", type: "slot", slot: "completed_form", align: "left" },
];

const activeRows = computed(() =>
    currentTab.value === "Forms to Fill Out" ? props.forms : props.completedForms
);

const paginationData = computed(() => {
    const total = activeRows.value.length;
    const start = (currentPage.value - 1) * perPage.value;
    const data = activeRows.value.slice(start, start + perPage.value);
    const from = total === 0 ? 0 : start + 1;
    const to = total === 0 ? 0 : Math.min(start + perPage.value, total);
    const lastPage = Math.max(1, Math.ceil(total / perPage.value));

    return {
        data,
        current_page: currentPage.value,
        last_page: lastPage,
        per_page: perPage.value,
        total,
        from,
        to,
        prev_page_url: currentPage.value > 1 ? "#" : null,
        next_page_url: currentPage.value < lastPage ? "#" : null,
    };
});

const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);

const hasActiveFilters = computed(() => activeFilterCount.value > 0);

const resultSummary = computed(() => {
    const total = activeRows.value.length;
    const label = currentTab.value === "Forms to Fill Out" ? "forms to fill out" : "completed forms";

    if (!total) {
        return `No ${label} found`;
    }

    return `Showing ${paginationData.value.from}-${paginationData.value.to} of ${total} ${label}`;
});

const applyFilters = () => {
    router.get(
        route("patient.forms"),
        Object.fromEntries(
            Object.entries({
                keyword: filterForm.value.keyword || undefined,
            }).filter(([, value]) => value !== undefined && value !== "")
        ),
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const clearFilters = () => {
    filterForm.value.keyword = "";
    router.get(route("patient.forms"), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetPagination = () => {
    currentPage.value = 1;
};

const updatePerPage = () => {
    resetPagination();
};

const changeTab = (tab) => {
    currentTab.value = tab;
    resetPagination();
};

watch(
    () => perPage.value,
    () => resetPagination()
);
</script>

<template>
    <AuthLayout title="Patient Forms" description="View and complete patient forms" heading="Patient Forms">
        <div class="row g-4">
            <div class="col-12 col-xl-3">
                <div class="card border-0 shadow-sm forms-side-card">
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
                                <h3 class="mb-1">Forms</h3>
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
                                <div class="input-group forms-search-control">
                                    <span
                                        class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>
                                    <input v-model="filterForm.keyword" type="search"
                                        class="form-control border-start-0" placeholder="Search forms"
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
                                <label for="forms-per-page" class="text-muted small text-uppercase mb-0">Rows</label>
                                <select id="forms-per-page" v-model="perPage"
                                    class="form-select form-select-sm top-page-select" @change="updatePerPage">
                                    <option v-for="option in perPageOptions" :key="option" :value="option">
                                        {{ option }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <Table :columns="currentTab === 'Forms to Fill Out' ? formsColumns : completedColumns"
                            :data="paginationData" :search-show="false" :PageOptions="false">
                            <template #form="{ row }">
                                <div class="text-start">
                                    <div class="fw-semibold text-dark">{{ row.label || "-" }}</div>
                                </div>
                            </template>

                            <template #completed_form="{ row }">
                                <div class="text-start">
                                    <div class="fw-semibold text-dark">
                                        <span class="text-muted">{{ row.date || "-" }} - </span>{{ row.title || "-" }}
                                    </div>
                                </div>
                            </template>

                            <template #actions="{ row }">
                                <div class="d-flex gap-2 justify-content-end">
                                    <Link v-if="currentTab === 'Forms to Fill Out' && row.view" :href="row.view"
                                        class="btn btn-success action-btn" title="View">
                                        <i class="bi bi-eye"></i>
                                    </Link>
                                    <Link v-if="currentTab === 'Completed Forms' && row.id"
                                        :href="route('patient.form.completeform', row.id)"
                                        class="btn btn-success action-btn" title="View">
                                        <i class="bi bi-eye"></i>
                                    </Link>
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
.forms-side-card {
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

.icon-warning {
    color: #f59e0b;
}

.icon-success {
    color: #22c55e;
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

.forms-search-control {
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
