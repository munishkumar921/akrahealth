<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { Link, router } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import { computed, ref } from "vue";

const props = defineProps({
    encounters: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    status: props.filters?.status || "",
});

const statusOptions = [
    { value: "", label: "All status" },
    { value: "completed", label: "Completed" },
    { value: "pending", label: "Pending" },
];

const columns = [
    { label: "Date", key: "encounter_date_of_service", type: "slot", slot: "date", align: "left" },
    { label: "Patient", key: "patient.user.name", type: "slot", slot: "patient", align: "left" },
    { label: "Complaint", key: "chief_complaint", type: "slot", slot: "complaint", align: "left" },
    { label: "Provider", key: "doctor.user.name", type: "slot", slot: "provider", align: "left" },
    { label: "Status", key: "date_signed", type: "slot", slot: "status", align: "center" },
];

const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);

const hasActiveFilters = computed(() => activeFilterCount.value > 0);

const resultSummary = computed(() => {
    const total = props.encounters?.total ?? props.encounters?.data?.length ?? 0;
    const from = props.encounters?.from ?? (total ? 1 : 0);
    const to = props.encounters?.to ?? total;

    if (!total) {
        return "No encounters found";
    }

    return `Showing ${from}-${to} of ${total} encounters`;
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
    router.get(route("patient.encounters"), buildQuery({ page: 1 }), {
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

    router.get(route("patient.encounters"), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("patient.encounters"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const encounterStatus = (row) => (row.date_signed ? "Completed" : "Pending");
</script>

<template>
    <AuthLayout title="Encounters" description="Encounters" heading="Encounters">
        <div class="users-toolbar card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                    <div>
                        <h3 class="mb-1">Encounters</h3>
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
                        <div class="input-group encounters-search-control">
                            <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input
                                v-model="filterForm.keyword"
                                type="search"
                                class="form-control border-start-0"
                                placeholder="Search encounters"
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
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0 p-md-3">
                <div class="d-flex justify-content-end align-items-center px-3 px-md-0 pt-3 pt-md-0 pb-2">
                    <div class="d-flex align-items-center gap-2 rows-select-wrap">
                        <label for="encounters-per-page" class="text-muted small text-uppercase mb-0">Rows</label>
                        <select
                            id="encounters-per-page"
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

                <Table :columns="columns" :data="encounters" :search-show="false" :PageOptions="false">
                    <template #date="{ row }">
                        <div class="text-start">
                            <div class="fw-medium text-dark">{{ row.encounter_date_of_service || row.created_at || "-" }}</div>
                        </div>
                    </template>

                    <template #patient="{ row }">
                        <div class="text-start">
                            <div class="fw-semibold text-dark">{{ row.patient?.user?.name || "-" }}</div>
                        </div>
                    </template>

                    <template #complaint="{ row }">
                        <div class="text-start">
                            <div class="fw-medium text-dark">{{ row.chief_complaint || "-" }}</div>
                        </div>
                    </template>

                    <template #provider="{ row }">
                        <div class="text-start">
                            <div class="fw-medium text-dark">{{ row.doctor?.user?.name || "-" }}</div>
                        </div>
                    </template>

                    <template #status="{ row }">
                        <div class="d-flex justify-content-center">
                            <span
                                class="status-pill"
                                :class="row.date_signed ? 'status-pill--completed' : 'status-pill--pending'"
                            >
                                {{ encounterStatus(row) }}
                            </span>
                        </div>
                    </template>

                    <template #actions="{ row }">
                        <div class="d-flex gap-2 justify-content-end">
                            <Link class="btn btn-success action-btn" :href="route('patient.encounters.show', row.id)" title="View">
                                <i class="bi bi-eye"></i>
                            </Link>
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

.encounters-search-control {
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

.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 90px;
    padding: 0.45rem 0.8rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: capitalize;
}

.status-pill--completed {
    background: #dcfce7;
    color: #166534;
}

.status-pill--pending {
    background: #fef3c7;
    color: #92400e;
}
</style>
