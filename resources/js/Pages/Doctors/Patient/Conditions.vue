<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Condition from "../../Modals/Condition.vue";
import { useForm, router, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import Modal from "@/Components/Common/Modal.vue";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { route } from "ziggy-js";

const props = defineProps({
    issues: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    record_type: props.filters?.record_type || "",
    status: props.filters?.status || "",
});

const isAddModalOpen = ref(false);
const childComponentRef = ref(null);

const selectedPatientId = computed(() => usePage().props.auth?.user?.doctor?.selected_patient_id);
const rows = computed(() => props.issues?.data ?? []);
const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);
const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);
const resultSummary = computed(() => {
    const total = props.issues?.total ?? rows.value.length;
    const from = props.issues?.from ?? (rows.value.length ? 1 : 0);
    const to = props.issues?.to ?? rows.value.length;

    if (!total) {
        return "No conditions found";
    }

    return `Showing ${from}-${to} of ${total} conditions`;
});

const typeOptions = [
    { value: "", label: "All types" },
    { value: "problem", label: "Problems" },
    { value: "past", label: "Past Medical History" },
    { value: "surgery", label: "Surgical History" },
];

const statusOptions = [
    { value: "", label: "All status" },
    { value: "active", label: "Active" },
    { value: "inactive", label: "Inactive" },
];

const buttons = [
    {
        label: "Add Condition",
        function: () => openAddMedicationModal(),
        icon: "bi bi-plus-circle",
    },
];

const tableColumns = [
    { label: "Issue", key: "issue", type: "slot", slot: "issue", align: "left" },
    { label: "Type", key: "type_label", type: "slot", slot: "type", align: "left" },
    { label: "Note", key: "notes", type: "slot", slot: "note", align: "left" },
    { label: "Active Date", key: "active_date_label", type: "slot", slot: "activeDate", align: "left" },
    { label: "Inactive Date", key: "inactive_date_label", type: "slot", slot: "inactiveDate", align: "left" },
    { label: "Move To", key: "move_to", type: "slot", slot: "move", align: "center" },
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
    router.get(route("doctor.conditions.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        record_type: "",
        status: "",
    };

    router.get(route("doctor.conditions.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("doctor.conditions.index"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const openAddMedicationModal = () => {
    isAddModalOpen.value = true;
    if (childComponentRef.value?.resetForm) {
        childComponentRef.value.resetForm();
    }
};

const closeAddMedicationModal = () => {
    isAddModalOpen.value = false;
};

const edit = (row) => {
    isAddModalOpen.value = true;
    setTimeout(() => {
        if (childComponentRef.value?.update) {
            childComponentRef.value.update(row);
        }
    }, 100);
};

const del = (id) => {
    Swal.fire(confirmSettings("Are you sure you want to delete this condition?")).then((result) => {
        if (result.isConfirmed) {
            const deleteForm = useForm({});
            deleteForm.delete(route("doctor.conditions.destroy", id), {
                preserveScroll: true,
            });
        }
    });
};

const visit = (url) => {
    router.visit(url);
};
</script>

<template>
    <AuthLayout title="Conditions" description="Manage patient conditions" heading="Conditions">
        <div class="conditions-page">
            <div v-if="!selectedPatientId" class="alert alert-warning text-center py-4">
                <h5><i class="bi bi-exclamation-triangle-fill me-2"></i>No Patient Selected</h5>
                <p class="mb-0">Please select a patient from the patient list to view their conditions.</p>
            </div>

            <template v-else>
                <div class="users-toolbar card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div
                            class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                            <div>
                                <h3 class="mb-1">Conditions</h3>
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
                                <ActionButtons :actionButtons="buttons" />
                            </div>
                        </div>

                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-sm-6 col-xl-4">
                                <label class="form-label text-muted small text-uppercase mb-2">Search</label>
                                <div class="input-group conditions-search-control">
                                    <span
                                        class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>
                                    <input v-model="filterForm.keyword" type="search"
                                        class="form-control border-start-0"
                                        placeholder="Search by issue, type, note, or sync state"
                                        @keydown.enter.prevent="applyFilters" />
                                    <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-xl-3">
                                <label class="form-label text-muted small text-uppercase mb-2">Type</label>
                                <select v-model="filterForm.record_type" class="form-select" @change="applyFilters">
                                    <option v-for="option in typeOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
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
                                <label for="conditions-per-page"
                                    class="text-muted small text-uppercase mb-0">Rows</label>
                                <select id="conditions-per-page" v-model="perPage"
                                    class="form-select form-select-sm top-page-select" @change="updatePerPage">
                                    <option v-for="option in perPageOptions" :key="option" :value="option">
                                        {{ option }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <Table :columns="tableColumns" :data="issues" :search-show="false" :PageOptions="false">
                            <template #issue="{ row }">
                                <div class="text-start">
                                    <div class="fw-semibold text-dark">{{ row.issue || "-" }}</div>
                                </div>
                            </template>

                            <template #type="{ row }">
                                <div class="text-start">
                                    <div class="fw-medium text-dark">{{ row.type_label || "-" }}</div>
                                </div>
                            </template>

                            <template #note="{ row }">
                                <div class="text-start">
                                    <div class="fw-medium text-dark">{{ row.notes || "-" }}</div>
                                </div>
                            </template>

                            <template #activeDate="{ row }">
                                <div class="text-start">
                                    <div class="fw-medium text-dark">{{ row.active_date_label || "-" }}</div>
                                </div>
                            </template>

                            <template #inactiveDate="{ row }">
                                <div class="text-start">
                                    <div class="fw-medium text-dark">{{ row.inactive_date_label || "-" }}</div>
                                </div>
                            </template>

                            <template #move="{ row }">
                                <div class="d-flex gap-2 justify-content-center">
                                    <button v-if="row.can_move_to_problem" type="button" class="btn btn-info action-btn"
                                        @click="visit(route('doctor.move.condition', { id: row.id, type: 'Problem' }))"
                                        title="Move to Problem">
                                        <i class="fa-solid fa-share"></i>
                                    </button>
                                    <button v-if="row.can_move_to_medical_history" type="button"
                                        class="btn btn-secondary action-btn"
                                        @click="visit(route('doctor.move.condition', { id: row.id, type: 'MedicalHistory' }))"
                                        title="Move to Medical History">
                                        <i class="fa-solid fa-share"></i>
                                    </button>
                                    <button v-if="row.can_move_to_surgical_history" type="button"
                                        class="btn btn-dark action-btn"
                                        @click="visit(route('doctor.move.condition', { id: row.id, type: 'SurgicalHistory' }))"
                                        title="Move to Surgical History">
                                        <i class="fa-solid fa-share"></i>
                                    </button>
                                </div>
                            </template>

                            <template #status="{ row }">
                                <div class="d-flex justify-content-center">
                                    <label class="ah-switch"
                                        :title="row.status_label === 'Active' ? 'Mark as inactive' : 'Mark as active'">
                                        <input type="checkbox" :checked="row.status_label === 'Active'"
                                            @change="visit(route('doctor.condition.status', { id: row.id, type: row.status_label === 'Inactive' ? 'active' : 'inactive' }))" />
                                        <span class="ah-slider">
                                            <i class="bi bi-check2 toggle-check-icon"></i>
                                        </span>
                                    </label>
                                </div>
                            </template>

                            <template #actions="{ row }">
                                <div class="table-actions d-flex justify-content-center gap-2">
                                    <button type="button" class="btn btn-primary action-btn" @click="edit(row)"
                                        title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger action-btn" @click="del(row.id)"
                                        title="Delete">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </template>
                        </Table>
                    </div>
                </div>
            </template>
        </div>

        <Modal :isOpen="isAddModalOpen" title="Add Condition" @close="closeAddMedicationModal" size="xl">
            <Condition ref="childComponentRef" @close="closeAddMedicationModal" />
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

.conditions-search-control {
    max-width: 720px;
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

@media (max-width: 767.98px) {
    .conditions-search-control {
        max-width: 100%;
    }
}
</style>
