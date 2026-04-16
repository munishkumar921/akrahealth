<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import Modal from "@/Components/Common/Modal.vue";
import AddMedicationModal from "@/Pages/Modals/Medication.vue";
import PrescribeMedicationModal from "@/Pages/Modals/PrescribeMedication.vue";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { route } from "ziggy-js";

const props = defineProps({
    encounters: {
        type: Object,
        default: null,
    },
    medications: {
        type: Object,
        default: () => ({}),
    },
    pharmacies: {
        type: Array,
        default: () => [],
    },
    prescriptionStatuses: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    status: props.filters?.status || "",
    prescription_status: props.filters?.prescription_status || "",
});

const selectedPatientId = computed(() => usePage().props.auth?.user?.doctor?.selected_patient_id);
const isAddModalOpen = ref(false);
const isPrescribeModalOpen = ref(false);
const addMedicationRef = ref(null);
const prescribeMedicationRef = ref(null);
const rows = computed(() => props.medications?.data ?? []);
const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);
const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);
const resultSummary = computed(() => {
    const total = props.medications?.total ?? rows.value.length;
    const from = props.medications?.from ?? (rows.value.length ? 1 : 0);
    const to = props.medications?.to ?? rows.value.length;

    if (!total) {
        return "No medications found";
    }

    return `Showing ${from}-${to} of ${total} medications`;
});

const buttons = [
    {
        label: "Add Medication",
        function: () => openAddMedicationModal(),
        icon: "bi bi-plus-circle",
    },
    {
        label: "Prescribe",
        function: () => openPrescribeMedicationModal(),
        icon: "bi bi-pencil-square",
    },
];

const statusOptions = [
    { value: "", label: "All status" },
    { value: "active", label: "Active" },
    { value: "inactive", label: "Inactive" },
];

const columns = [
    { label: "Medication", key: "medication", type: "slot", slot: "medication", align: "left" },
    { label: "Date Active", key: "active_date_label", type: "slot", slot: "dateActive", align: "left" },
    { label: "Date Inactive", key: "inactive_date_label", type: "slot", slot: "dateInactive", align: "left" },
    { label: "Due Date", key: "due_date_label", type: "slot", slot: "dueDate", align: "left" },
    { label: "Prescription Status", key: "prescription_label", type: "slot", slot: "prescription", align: "center" },
    { label: "Refills", key: "refills", type: "slot", slot: "refills", align: "center" },
    { label: "Status", key: "status_label", type: "slot", slot: "status", align: "center" },
];

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
    router.get(route("doctor.medications.index"), buildQuery(), {
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

    router.get(route("doctor.medications.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("doctor.medications.index"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const isMedicationActive = (medication) => medication?.status_label === "Active";

const openAddMedicationModal = () => {
    isAddModalOpen.value = true;
    if (addMedicationRef.value?.resetForm) {
        addMedicationRef.value.resetForm();
    }
};

const closeAddMedicationModal = () => {
    isAddModalOpen.value = false;
};

const openPrescribeMedicationModal = () => {
    isPrescribeModalOpen.value = true;
};

const closePrescribeMedicationModal = () => {
    isPrescribeModalOpen.value = false;
};

const edit = (medication) => {
    isAddModalOpen.value = true;
    setTimeout(() => {
        if (addMedicationRef.value?.update) {
            addMedicationRef.value.update(medication);
        }
    }, 10);
};

const refillMedication = (medication) => {
    isPrescribeModalOpen.value = true;
    setTimeout(() => {
        if (prescribeMedicationRef.value?.update) {
            prescribeMedicationRef.value.update(medication);
        }
    }, 10);
};

const confirmUndoMedication = (id) => {
    Swal.fire({
        toast: true,
        position: "top-end",
        title: "Undo reconciliation?",
        text: "This will revert the medication reconciliation action for this record.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, undo",
        cancelButtonText: "Cancel",
        reverseButtons: true,
        showClass: {
            popup: "swal2-show",
        },
    }).then((result) => {
        if (result.isConfirmed) {
            undoMedication(id);
        }
    });
};

const undoMedication = (id) => {
    router.post(route("doctor.medication.reconcile", { id }), {}, { preserveScroll: true });
};

const medicationStatus = (row) => {
    const nextType = isMedicationActive(row) ? "inactive" : "active";

    router.get(route("doctor.medication.status", { id: row.id, type: nextType }), {}, {
        preserveScroll: true,
        preserveState: false,
        replace: true,
    });
};

const deleteMedication = (id) => {
    Swal.fire(confirmSettings("Are you sure you want to delete this medication?")).then((result) => {
        if (result.isConfirmed) {
            const deleteForm = useForm({});
            deleteForm.delete(route("doctor.medications.destroy", id), {
                preserveScroll: true,
            });
        }
    });
};
</script>

<template>
    <AuthLayout title="Medications" description="Manage medications" heading="Medications">
        <div class="medications-page">
            <div v-if="!selectedPatientId" class="alert alert-warning text-center py-4">
                <h5><i class="bi bi-exclamation-triangle-fill me-2"></i>No Patient Selected</h5>
                <p class="mb-0">Please select a patient from the patient list to view their medications.</p>
            </div>

            <template v-else>
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
                                <button
                                    v-if="hasActiveFilters"
                                    type="button"
                                    class="btn btn-outline-secondary btn-sm"
                                    @click="clearFilters"
                                >
                                    <i class="bi bi-x-circle me-1"></i>Clear filters
                                </button>
                                <ActionButtons :actionButtons="buttons" />
                            </div>
                        </div>

                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-sm-6 col-xl-4">
                                <label class="form-label text-muted small text-uppercase mb-2">Search</label>
                                <div class="input-group medications-search-control">
                                    <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>
                                    <input
                                        v-model="filterForm.keyword"
                                        type="search"
                                        class="form-control border-start-0"
                                        placeholder="Search by medication, dosage, route, reason, or sync state"
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
                                <label class="form-label text-muted small text-uppercase mb-2">Prescription</label>
                                <select v-model="filterForm.prescription_status" class="form-select" @change="applyFilters">
                                    <option value="">All prescription statuses</option>
                                    <option
                                        v-for="status in prescriptionStatuses"
                                        :key="status"
                                        :value="status"
                                    >
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
                                <select
                                    id="medications-per-page"
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

                        <Table :columns="columns" :data="medications" :search-show="false" :PageOptions="false">
                            <template #medication="{ row }">
                                <div class="text-start">
                                    <div class="fw-semibold text-dark">{{ row.medication || "-" }}</div>
                                    <div class="text-muted small">
                                        {{ [row.dosage, row.dosage_unit].filter(Boolean).join(" ") || row.route || "-" }}
                                    </div>
                                </div>
                            </template>

                            <template #dateActive="{ row }">
                                <div class="text-start">
                                    <div class="fw-medium text-dark">{{ row.active_date_label || "-" }}</div>
                                </div>
                            </template>

                            <template #dateInactive="{ row }">
                                <div class="text-start">
                                    <div class="fw-medium text-dark">{{ row.inactive_date_label || "-" }}</div>
                                </div>
                            </template>

                            <template #dueDate="{ row }">
                                <div class="text-start">
                                    <div class="fw-medium text-dark">{{ row.due_date_label || "-" }}</div>
                                </div>
                            </template>

                            <template #prescription="{ row }">
                                <div class="d-flex justify-content-center">
                                    <span class="status-pill" :class="row.prescription_label === 'N/A' ? 'status-pill--inactive' : 'status-pill--active'">
                                        {{ row.prescription_label }}
                                    </span>
                                </div>
                            </template>

                            <template #refills="{ row }">
                                <div class="d-flex gap-2 justify-content-center">
                                    <button class="btn btn-success action-btn" @click="refillMedication(row)" title="Refill medication">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                    <button class="btn btn-warning action-btn" @click="confirmUndoMedication(row.id)" title="Undo reconciliation">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </div>
                            </template>

                            <template #status="{ row }">
                                <div class="d-flex justify-content-center">
                                    <label
                                        class="ah-switch"
                                        :title="isMedicationActive(row) ? 'Mark as inactive' : 'Mark as active'"
                                    >
                                        <input type="checkbox" :checked="isMedicationActive(row)" @change="medicationStatus(row)" />
                                        <span class="ah-slider">
                                            <i class="bi bi-check2 toggle-check-icon"></i>
                                        </span>
                                    </label>
                                </div>
                            </template>

                            <template #actions="{ row }">
                                <div class="table-actions d-flex justify-content-center gap-2">
                                    <button class="btn btn-primary action-btn" @click="edit(row)" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-danger action-btn" @click="deleteMedication(row.id)" title="Delete">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </template>
                        </Table>
                    </div>
                </div>
            </template>
        </div>

        <Modal :isOpen="isAddModalOpen" title="Add Medication" @close="closeAddMedicationModal" size="xl">
            <AddMedicationModal ref="addMedicationRef" :encounters="encounters" @close="closeAddMedicationModal" />
        </Modal>

        <Modal :isOpen="isPrescribeModalOpen" title="Prescribe Medication" @close="closePrescribeMedicationModal" size="xl">
            <PrescribeMedicationModal
                ref="prescribeMedicationRef"
                :encounters="encounters"
                :pharmacies="pharmacies"
                @close="closePrescribeMedicationModal"
            />
        </Modal>
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
    max-width: 720px;
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
    min-width: 100px;
    padding: 0.45rem 0.8rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-pill--active {
    background: #dcfce7;
    color: #166534;
}

.status-pill--inactive {
    background: #e2e8f0;
    color: #475569;
}

.action-btn {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
}

@media (max-width: 767.98px) {
    .medications-search-control {
        max-width: 100%;
    }
}
</style>
