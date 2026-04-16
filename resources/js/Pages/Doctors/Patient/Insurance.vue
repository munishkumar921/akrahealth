<script setup>
import { computed, ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Modal from "@/Components/Common/Modal.vue";
import AddInsuranceModal from "@/Pages/Modals/AddInsurance.vue";
import Table from "@/Components/Table/Table.vue";

const props = defineProps({
    states: {
        type: Array,
        default: () => [],
    },
    countries: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    insurances: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
});

const selectedPatientId = computed(() => usePage().props.auth?.user?.doctor?.selected_patient_id ?? null);
const isAddModalOpen = ref(false);
const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    state: props.filters?.state || "",
});

const columns = [
    { label: "Insurance Company", key: "insurance_company", type: "slot", slot: "company", align: "left" },
    { label: "Address", key: "address_1", type: "slot", slot: "address", align: "left" },
    { label: "Phone", key: "phone", type: "slot", slot: "phone", align: "left" },
    { label: "Email", key: "email", type: "slot", slot: "email", align: "left" },
    { label: "Comments", key: "comment", type: "slot", slot: "comment", align: "left" },
];

const stateOptions = computed(() =>
    props.states
        .map((state) => state.name || state.state_name || state.state || "")
        .filter(Boolean)
);

const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);

const hasActiveFilters = computed(() => activeFilterCount.value > 0);

const resultSummary = computed(() => {
    const total = props.insurances?.total ?? props.insurances?.data?.length ?? 0;
    const from = props.insurances?.from ?? (total ? 1 : 0);
    const to = props.insurances?.to ?? total;

    if (!total) {
        return "No insurance records found";
    }

    return `Showing ${from}-${to} of ${total} insurance records`;
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
    router.get(route("doctor.insurance.index"), buildQuery({ page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        state: "",
    };

    router.get(route("doctor.insurance.index"), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("doctor.insurance.index"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const openAddInsuranceModal = () => {
    isAddModalOpen.value = true;
};

const closeAddInsuranceModal = () => {
    isAddModalOpen.value = false;
};
</script>

<template>
    <AuthLayout title="Insurance" description="Insurance" heading="Insurance">
        <div v-if="!selectedPatientId" class="alert alert-warning text-center py-4">
            <h5><i class="bi bi-exclamation-triangle-fill me-2"></i>No Patient Selected</h5>
            <p class="mb-0">Please select a patient from the patient list to view insurance records.</p>
        </div>

        <template v-else>
            <div class="users-toolbar card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div
                        class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                        <div>
                            <h3 class="mb-1">Insurance</h3>
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
                            <button type="button" class="btn btn-primary" @click="openAddInsuranceModal">
                                <i class="bi bi-plus-circle me-1"></i>Add insurance
                            </button>
                        </div>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-sm-6 col-xl-4">
                            <label class="form-label text-muted small text-uppercase mb-2">Search</label>
                            <div class="input-group insurance-search-control">
                                <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input v-model="filterForm.keyword" type="search" class="form-control border-start-0"
                                    placeholder="Search insurance" @keydown.enter.prevent="applyFilters" />
                                <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-3">
                            <label class="form-label text-muted small text-uppercase mb-2">State</label>
                            <select v-model="filterForm.state" class="form-select" @change="applyFilters">
                                <option value="">All states</option>
                                <option v-for="state in stateOptions" :key="state" :value="state">
                                    {{ state }}
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
                            <label for="insurance-per-page" class="text-muted small text-uppercase mb-0">Rows</label>
                            <select id="insurance-per-page" v-model="perPage"
                                class="form-select form-select-sm top-page-select" @change="updatePerPage">
                                <option v-for="option in perPageOptions" :key="option" :value="option">
                                    {{ option }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <Table :columns="columns" :data="insurances" :search-show="false" :PageOptions="false">
                        <template #facility="{ row }">
                            <div class="text-start">
                                <div class="fw-semibold text-dark">{{ row.plan_name || "-" }}</div>
                            </div>
                        </template>

                        <template #company="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.insurance_company || "-" }}</div>
                            </div>
                        </template>

                        <template #address="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.address_1 || "-" }}</div>
                                <div class="text-muted small">
                                    {{ [row.city, row.state, row.zip].filter(Boolean).join(", ") || "-" }}
                                </div>
                            </div>
                        </template>

                        <template #phone="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.phone || "-" }}</div>
                            </div>
                        </template>

                        <template #email="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.email || "-" }}</div>
                            </div>
                        </template>

                        <template #comment="{ row }">
                            <div class="text-start insurance-comment-cell">
                                <div class="fw-medium text-dark">{{ row.comment || "-" }}</div>
                            </div>
                        </template>
                    </Table>
                </div>
            </div>

            <Modal :isOpen="isAddModalOpen" title="Add Insurance Details" @close="closeAddInsuranceModal" size="lg">
                <AddInsuranceModal :states="states" :countries="countries" @close="closeAddInsuranceModal" />
            </Modal>
        </template>
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

.insurance-search-control {
    max-width: 100%;
}

.rows-select-wrap {
    min-width: 118px;
}

.top-page-select {
    width: 84px;
}

.insurance-comment-cell {
    max-width: 260px;
    white-space: normal;
    word-break: break-word;
}
</style>
