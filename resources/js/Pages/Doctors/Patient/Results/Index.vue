<script setup>
import { computed, ref } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import AuthLayout from "@/Layouts/AuthLayout2.vue";
import Modal from "@/Components/Common/Modal.vue";
import AddResult from "@/Pages/Modals/AddResult.vue";
import ResultReply from "@/Pages/Modals/ResultReply.vue";
import Swal from "sweetalert2/dist/sweetalert2.js";

const props = defineProps({
    results: {
        type: Object,
        default: () => ({}),
    },
    doctors: {
        type: Array,
        default: () => [],
    },
    encounter_vitals: {
        type: Object,
        default: () => ({}),
    },
    tests: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const patientId = computed(() => usePage().props.auth?.user?.doctor?.selected_patient_id ?? null);
const currentTab = ref(props.filters?.tab || "Laboratory");
const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);
const isAddResultModalOpen = ref(false);
const isResultReplyModalOpen = ref(false);
const childComponentRef = ref(null);

const filterForm = ref({
    keyword: props.filters?.keyword || "",
});

const tabs = [
    { key: "Laboratory", label: "Laboratory", iconClass: "icon-success", icon: "fa-solid fa-vial" },
    { key: "Imaging", label: "Imaging", iconClass: "icon-warning", icon: "fa-solid fa-x-ray" },
    { key: "Vital Signs", label: "Vital Signs", iconClass: "icon-secondary", icon: "fa-solid fa-heart-pulse" },
];

const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);

const hasActiveFilters = computed(() => activeFilterCount.value > 0);

const activeData = computed(() => (currentTab.value === "Vital Signs" ? props.encounter_vitals : props.results));

const resultSummary = computed(() => {
    const total = activeData.value?.total ?? activeData.value?.data?.length ?? 0;
    const from = activeData.value?.from ?? (total ? 1 : 0);
    const to = activeData.value?.to ?? total;
    const label = currentTab.value === "Vital Signs" ? "vital entries" : "results";

    if (!total) {
        return `No ${label} found`;
    }

    return `Showing ${from}-${to} of ${total} ${label}`;
});

const resultColumns = [
    { label: "Result", key: "name", type: "slot", slot: "result_name", align: "left" },
    { label: "Value", key: "result", type: "slot", slot: "result_value", align: "left" },
    { label: "Reference", key: "reference", type: "slot", slot: "reference", align: "left" },
    { label: "Date", key: "created_at", type: "slot", slot: "result_date", align: "left" },
];

const vitalColumns = [
    { label: "Date", key: "vital_date", type: "slot", slot: "vital_date", align: "left" },
    { label: "Weight", key: "weight", align: "left" },
    { label: "Height", key: "height", align: "left" },
    { label: "HC", key: "head_circumference", align: "left" },
    { label: "BMI", key: "bmi", align: "left" },
    { label: "Temp", key: "temperature", align: "left" },
    { label: "SBP", key: "bp_systolic", align: "left" },
    { label: "DBP", key: "bp_diastolic", align: "left" },
    { label: "Pulse", key: "pulse", align: "left" },
    { label: "Resp", key: "respirations", align: "left" },
    { label: "O2 Sat", key: "o2_saturation", align: "left" },
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
    router.get(route("doctor.results.index"), buildQuery({ page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value.keyword = "";

    router.get(route("doctor.results.index"), buildQuery({ keyword: undefined, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("doctor.results.index"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const changeTab = (tab) => {
    currentTab.value = tab;
    router.get(route("doctor.results.index"), buildQuery({ tab, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const openAddResultModal = () => {
    isAddResultModalOpen.value = true;
};

const closeAddResultModal = () => {
    isAddResultModalOpen.value = false;
};

const openResultReplyModal = () => {
    isResultReplyModalOpen.value = true;
};

const closeResultReplyModal = () => {
    isResultReplyModalOpen.value = false;
};

const editResult = (result) => {
    setTimeout(() => {
        if (childComponentRef.value?.update) {
            childComponentRef.value.update(result);
        }
    }, 10);

    openAddResultModal();
};

const deleteResult = (id) => {
    Swal.fire({
        toast: true,
        position: "top-end",
        title: "Delete result?",
        text: "This result will be permanently removed.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete",
        cancelButtonText: "Cancel",
        reverseButtons: true,
        showClass: {
            popup: "swal2-show",
        },
    }).then((result) => {
        if (result.isConfirmed) {
            const deleteForm = useForm({});
            deleteForm.delete(route("doctor.results.destroy", id), {
                preserveScroll: true,
            });
        }
    });
};

const showResult = (id) => {
    router.get(route("doctor.results.show", id));
};

const onCellClick = ({ column }) => {
    if (currentTab.value === "Vital Signs" && column.key !== "vital_date") {
        router.get(
            route("doctor.encounterVitalChat", { type: column.key }),
            {},
            {
                preserveState: true,
                replace: true,
            }
        );
    }
};
</script>

<template>
    <AuthLayout title="Results" description="Results" heading="Results">
        <div v-if="!patientId" class="alert alert-warning text-center py-4">
            <h5><i class="bi bi-exclamation-triangle-fill me-2"></i>No Patient Selected</h5>
            <p class="mb-0">Please select a patient from the patient list to view results.</p>
        </div>

        <template v-else>
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
                                    <button v-if="hasActiveFilters" type="button"
                                        class="btn btn-outline-secondary btn-sm" @click="clearFilters">
                                        <i class="bi bi-x-circle me-1"></i>Clear filters
                                    </button>

                                    <div class="btn-group ms-2">
                                        <button type="button" class="btn btn-primary dropdown-toggle"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fa-solid fa-square-plus pointer mr-1"></i> Add
                                        </button>
                                        <div class="dropdown-menu">
                                            <button @click="openAddResultModal" class="dropdown-item pointer">Add
                                                Result</button>
                                            <button @click="openResultReplyModal" class="dropdown-item pointer">
                                                Result Reply To Patient
                                            </button>
                                        </div>
                                    </div>
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
                                        <button type="button" class="btn btn-primary"
                                            @click="applyFilters">Search</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0 p-md-3">
                            <div class="d-flex justify-content-end align-items-center px-3 px-md-0 pt-3 pt-md-0 pb-2">
                                <div class="d-flex align-items-center gap-2 rows-select-wrap">
                                    <label for="results-per-page"
                                        class="text-muted small text-uppercase mb-0">Rows</label>
                                    <select id="results-per-page" v-model="perPage"
                                        class="form-select form-select-sm top-page-select" @change="updatePerPage">
                                        <option v-for="option in perPageOptions" :key="option" :value="option">
                                            {{ option }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <Table v-if="currentTab === 'Laboratory' || currentTab === 'Imaging'"
                                :columns="resultColumns" :data="results" :search-show="false" :PageOptions="false">
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
                                        <div v-if="row.flags" class="text-muted small">{{ row.flags }}</div>
                                    </div>
                                </template>

                                <template #result_date="{ row }">
                                    <div class="text-start">
                                        <div class="fw-medium text-dark">{{ row.created_at || "-" }}</div>
                                        <div v-if="row.time" class="text-muted small">{{ row.time }}</div>
                                    </div>
                                </template>

                                <template #actions="{ row }">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button class="btn btn-primary action-btn" title="Edit"
                                            @click="editResult(row)">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button class="btn btn-success action-btn" title="View"
                                            @click="showResult(row.id)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-danger action-btn" title="Delete"
                                            @click="deleteResult(row.id)">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </template>
                            </Table>

                            <Table v-else :columns="vitalColumns" :data="encounter_vitals" :search-show="false"
                                :PageOptions="false" @cell-click="onCellClick">
                                <template #vital_date="{ row }">
                                    <div class="text-start">
                                        <div class="fw-medium text-dark">{{ row.vital_date || "-" }}</div>
                                    </div>
                                </template>
                            </Table>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <Modal :isOpen="isAddResultModalOpen" title="Add Result" @close="closeAddResultModal" size="xl">
            <AddResult ref="childComponentRef" :doctors="doctors" @close="closeAddResultModal" />
        </Modal>

        <Modal :isOpen="isResultReplyModalOpen" title="Result Reply To Patient" @close="closeResultReplyModal"
            size="xl">
            <ResultReply :tests="tests" @close="closeResultReplyModal" />
        </Modal>
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
    color: #16a34a;
}

.icon-warning {
    color: #d97706;
}

.icon-secondary {
    color: #475569;
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
