<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, useForm } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import Modal from "@/Components/Common/Modal.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import AddPatient from "./AddPatient.vue";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { computed, ref } from "vue";
import axios from "axios";
import { route } from "ziggy-js";

const props = defineProps({
    patients: Object,
    doctors: {
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
    status: props.filters?.status ?? "",
    doctor_id: props.filters?.doctor_id || "",
});

const statusOptions = [
    { value: "", label: "All status" },
    { value: "true", label: "Active" },
    { value: "false", label: "Inactive" },
];

const columns = [
    { label: "Patient", key: "display_name", type: "slot", slot: "name", align: "left" },
    { label: "Doctor", key: "doctor_name", type: "slot", slot: "doctor", align: "left" },
    { label: "Branch", key: "branch_name", type: "slot", slot: "branch", align: "left" },
    { label: "Contact", key: "email", type: "slot", slot: "contact", align: "left" },
    { label: "Status", key: "is_active", type: "slot", slot: "status", align: "center" },
];

const rows = computed(() => props.patients?.data ?? []);
const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);
const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);
const resultSummary = computed(() => {
    const total = props.patients?.total ?? rows.value.length;
    const from = props.patients?.from ?? (rows.value.length ? 1 : 0);
    const to = props.patients?.to ?? rows.value.length;

    if (!total) {
        return "No patients found";
    }

    return `Showing ${from}-${to} of ${total} patients`;
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
    router.get(route("admin.patients.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        status: "",
        doctor_id: "",
    };

    router.get(route("admin.patients.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("admin.patients.index"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const removeRow = (row) => {
    Swal.fire(
        confirmSettings("Are you sure to delete this data?", "You won't be able to get it back")
    ).then((result) => {
        if (result.isConfirmed) {
            useForm({}).delete(route("admin.patients.destroy", row.id));
        }
    });
};

const isAddPatientModal = ref(false);
const childComponentRef = ref(null);

const goToAddPatient = () => {
    isAddPatientModal.value = true;
};

const closeAddModal = () => {
    isAddPatientModal.value = false;
};

const openEditModal = (row) => {
    setTimeout(() => {
        if (childComponentRef.value) {
            childComponentRef.value.update(row);
        }
    }, 50);
    isAddPatientModal.value = true;
};

const buttons = [
    {
        label: "Add Patient",
        function: goToAddPatient,
        icon: "bi bi-plus-circle",
    },
];

const toggleStatus = async (row) => {
    await axios.post(route("update.status"), {
        table: "patients",
        id: row.id,
        is_active: !row.is_active,
    });

    row.is_active = !row.is_active;
    row.status_label = row.is_active ? "Active" : "Inactive";
};
</script>

<template>
    <AuthLayout title="Patients" description="Patients" heading="Patients">
        <div class="patient-directory-page">
            <div class="users-toolbar card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div
                        class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                        <div>
                            <h3 class="mb-1">Patients</h3>
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
                        <div class="col-12 col-xl-4">
                            <label class="form-label text-muted small text-uppercase mb-2">Search</label>
                            <div class="input-group patient-search-control">
                                <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input v-model="filterForm.keyword" type="search" class="form-control border-start-0"
                                    placeholder="Search by patient name, email, or mobile"
                                    @keydown.enter.prevent="applyFilters" />
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

                        <div class="col-12 col-sm-6 col-xl-4">
                            <label class="form-label text-muted small text-uppercase mb-2">Doctor</label>
                            <select v-model="filterForm.doctor_id" class="form-select" @change="applyFilters">
                                <option value="">All doctors</option>
                                <option v-for="doctor in doctors" :key="doctor.id" :value="doctor.id">
                                    {{ doctor.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0 p-md-3">
                    <div class="d-flex justify-content-end align-items-center px-3 px-md-0 pt-3 pt-md-0 pb-2">
                        <div class="d-flex align-items-center gap-2">
                            <select id="patients-per-page" v-model="perPage"
                                class="form-select form-select-sm top-page-select" @change="updatePerPage">
                                <option v-for="option in perPageOptions" :key="option" :value="option">
                                    {{ option }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <Table :columns="columns" :data="patients" :search-show="false" :PageOptions="false">
                        <template #name="{ row }">
                            <div class="text-start d-flex align-items-center gap-3">
                                <img
                                    :src="row.avatar_url || '/images/avatar.webp'"
                                    alt="Patient"
                                    class="rounded-circle listing-avatar"
                                />
                                <div>
                                    <div class="fw-semibold text-dark">{{ row.display_name || row.name }}</div>
                                    <div class="text-muted small">{{ row.created_label }}</div>
                                </div>
                            </div>
                        </template>

                        <template #doctor="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.doctor_name || "Not assigned" }}</div>
                            </div>
                        </template>

                        <template #branch="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.branch_name || "N/A" }}</div>
                                <div v-if="row.branch_type" class="text-muted small">{{ row.branch_type }}</div>
                            </div>
                        </template>

                        <template #contact="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.email || "N/A" }}</div>
                                <div class="text-muted small">{{ row.mobile || "No mobile" }}</div>
                            </div>
                        </template>

                        <template #status="{ row }">
                            <div class="d-flex justify-content-center align-items-center gap-2">
                                <span class="status-pill"
                                    :class="row.is_active ? 'status-pill--active' : 'status-pill--inactive'">
                                    {{ row.status_label }}
                                </span>
                                <label class="ah-switch">
                                    <input type="checkbox" :checked="!!row.is_active" @change="toggleStatus(row)" />
                                    <span class="ah-slider">
                                        <i class="bi bi-check2"></i>
                                    </span>
                                </label>
                            </div>
                        </template>

                        <template #actions="{ row }">
                            <div class="d-flex gap-2">
                                <button class="btn btn-light border" @click="openEditModal(row)" title="Edit patient">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-light border text-danger" @click="removeRow(row)"
                                    title="Delete patient">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </template>
                    </Table>
                </div>
            </div>

            <Modal :isOpen="isAddPatientModal" :title="'Patient Details'" @close="closeAddModal" size="xl">
                <AddPatient ref="childComponentRef" :doctors="doctors" @close="closeAddModal" />
            </Modal>
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

.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 74px;
    padding: 0.35rem 0.7rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
}

.status-pill--active {
    background: #dcfce7;
    color: #166534;
}

.status-pill--inactive {
    background: #f1f5f9;
    color: #475569;
}

.top-page-select {
    min-width: 74px;
}

.patient-search-control {
    max-width: 620px;
}

.listing-avatar {
    width: 44px;
    height: 44px;
    object-fit: cover;
    border: 1px solid #e2e8f0;
}
</style>
