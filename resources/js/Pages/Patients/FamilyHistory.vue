<script setup>
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";

const props = defineProps({
    familyHistory: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    relationshipOptions: {
        type: Array,
        default: () => [],
    },
    genderOptions: {
        type: Array,
        default: () => [],
    },
});

const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    relationship: props.filters?.relationship || "",
    gender: props.filters?.gender || "",
});

const columns = [
    { label: "Name", key: "name", type: "slot", slot: "name", align: "left" },
    { label: "Relationship", key: "relationship", type: "slot", slot: "relationship", align: "left" },
    { label: "Gender", key: "gender", align: "left" },
    { label: "DOB", key: "dob", align: "left" },
    { label: "Marital Status", key: "marital_status", align: "left" },
    { label: "Medical History", key: "medical_history", type: "slot", slot: "medical_history", align: "left" },
];

const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);

const hasActiveFilters = computed(() => activeFilterCount.value > 0);

const resultSummary = computed(() => {
    const total = props.familyHistory?.total ?? props.familyHistory?.data?.length ?? 0;
    const from = props.familyHistory?.from ?? (total ? 1 : 0);
    const to = props.familyHistory?.to ?? total;

    if (!total) {
        return "No family history records found";
    }

    return `Showing ${from}-${to} of ${total} family history records`;
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
    router.get(route("patient.family_history"), buildQuery({ page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        relationship: "",
        gender: "",
    };

    router.get(route("patient.family_history"), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("patient.family_history"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};
</script>

<template>
    <AuthLayout title="Family History" description="Family History" heading="Family History">
        <div class="users-toolbar card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                    <div>
                        <h3 class="mb-1">Family History</h3>
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
                        <div class="input-group family-history-search-control">
                            <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input
                                v-model="filterForm.keyword"
                                type="search"
                                class="form-control border-start-0"
                                placeholder="Search family history"
                                @keydown.enter.prevent="applyFilters"
                            />
                            <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <label class="form-label text-muted small text-uppercase mb-2">Relationship</label>
                        <select v-model="filterForm.relationship" class="form-select" @change="applyFilters">
                            <option value="">All relationships</option>
                            <option v-for="option in relationshipOptions" :key="option" :value="option">
                                {{ option }}
                            </option>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <label class="form-label text-muted small text-uppercase mb-2">Gender</label>
                        <select v-model="filterForm.gender" class="form-select" @change="applyFilters">
                            <option value="">All gender</option>
                            <option v-for="option in genderOptions" :key="option" :value="option">
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
                        <label for="family-history-per-page" class="text-muted small text-uppercase mb-0">Rows</label>
                        <select
                            id="family-history-per-page"
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

                <Table :columns="columns" :data="familyHistory" :search-show="false" :PageOptions="false">
                    <template #name="{ row }">
                        <div class="text-start">
                            <div class="fw-semibold text-dark">{{ row.name || "-" }}</div>
                        </div>
                    </template>

                    <template #relationship="{ row }">
                        <div class="text-start">
                            <div class="fw-medium text-dark">{{ row.relationship || "-" }}</div>
                            <div v-if="row.living_status" class="text-muted small">{{ row.living_status }}</div>
                        </div>
                    </template>

                    <template #medical_history="{ row }">
                        <div v-if="row.medical_history" class="text-start">
                            <span
                                v-for="(item, index) in (Array.isArray(row.medical_history)
                                    ? row.medical_history
                                    : String(row.medical_history).split(','))"
                                :key="index"
                                class="badge bg-primary me-1 mb-1"
                            >
                                {{ item.trim() }}
                            </span>
                        </div>
                        <span v-else>-</span>
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

.family-history-search-control {
    max-width: 100%;
}

.rows-select-wrap {
    min-width: 118px;
}

.top-page-select {
    width: 84px;
}
</style>
