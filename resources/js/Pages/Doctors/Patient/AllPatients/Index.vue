<script setup>
import { computed, ref } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import Modal from "@/Components/Common/Modal.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import AddPatient from "./AddPatient.vue";
import PatientSummary from "@/Pages/Common/PatientSummary.vue";
import axios from "axios";
import Swal from "sweetalert2";
import { route } from "ziggy-js";

const props = defineProps({
    patients: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    countries: Array,
    states: Array,
});

const page = usePage();
const processing = ref({});
const isLoadingPatientData = ref(false);
const showPatientSummary = ref(false);
const patientData = ref({});
const isAddUserModalOpen = ref(false);

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    status: props.filters?.status ?? "",
    portal_status: props.filters?.portal_status || "",
});

const statusOptions = [
    { value: "", label: "All status" },
    { value: "true", label: "Active" },
    { value: "false", label: "Inactive" },
];

const portalOptions = [
    { value: "", label: "All portal status" },
    { value: "registered", label: "Registered" },
    { value: "not_registered", label: "Not registered" },
];

const columns = [
    { label: "Patient", key: "display_name", type: "slot", slot: "patient", align: "left" },
    { label: "Contact", key: "email", type: "slot", slot: "contact", align: "left" },
    { label: "Portal", key: "portal_status_label", type: "slot", slot: "portal", align: "center" },
    { label: "Status", key: "status_label", type: "slot", slot: "status", align: "center" },
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

const buttons = [
    {
        label: "Add Patient",
        function: () => openAddUserModal(),
        icon: "bi bi-plus-circle",
    },
];

const extraButtons = [
    { title: "Sync Patients" },
    {
        label: "DrChrono",
        function: () => window.open("https://www.drchrono.com/", "_blank"),
    },
    {
        label: "Practice Fusion",
        function: () => window.open("https://www.practicefusion.com/", "_blank"),
    },
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
    router.get(route("doctor.patients"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        status: "",
        portal_status: "",
    };

    router.get(route("doctor.patients"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("doctor.patients"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const openAddUserModal = () => {
    isAddUserModalOpen.value = true;
};

const closeAddUserModal = () => {
    isAddUserModalOpen.value = false;
};

const register = async (patientId) => {
    processing.value[patientId] = true;

    try {
        const response = await axios.post(route("doctor.patient.register"), { patient_id: patientId });

        if (response.data.success) {
            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "success",
                title: response.data.success,
                showConfirmButton: false,
                timer: 3000,
            });
        } else {
            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "error",
                title: response.data.message,
                showConfirmButton: false,
                timer: 3000,
            });
        }
    } catch (error) {
        Swal.fire({
            toast: true,
            position: "top-end",
            icon: "error",
            title: error.response?.data?.message ?? "Something went wrong",
            showConfirmButton: false,
            timer: 3000,
        });
    } finally {
        processing.value[patientId] = false;
    }
};

const patientDetails = (id) => {
    const selectedPatientId = page?.props?.selected_patient?.id ?? null;

    if (selectedPatientId && selectedPatientId === id) {
        router.get(route("doctor.demographics"));
        return;
    }

    Swal.fire({
        toast: true,
        position: "top-end",
        icon: "info",
        title: "Please select a patient first",
        showConfirmButton: false,
        timer: 3000,
    });
};

const patientSummary = (id) => {
    const selectedPatientId = page?.props?.selected_patient?.id ?? null;

    if (selectedPatientId && selectedPatientId === id) {
        isLoadingPatientData.value = true;

        axios
            .get(route("doctor.patient.summary"))
            .then((res) => {
                if (res?.data) patientData.value = res.data;
                showPatientSummary.value = true;
            })
            .catch(() => {
                showPatientSummary.value = true;
            })
            .finally(() => {
                isLoadingPatientData.value = false;
            });

        return;
    }

    Swal.fire({
        toast: true,
        position: "top-end",
        icon: "info",
        title: "Please select a patient first",
        showConfirmButton: false,
        timer: 3000,
    });
};

const patientHistory = (id) => {
    const selectedPatientId = page?.props?.selected_patient?.id ?? null;

    if (selectedPatientId && selectedPatientId === id) {
        router.get(route("doctor.patient.history"));
        return;
    }

    Swal.fire({
        toast: true,
        position: "top-end",
        icon: "info",
        title: "Please select a patient first",
        showConfirmButton: false,
        timer: 3000,
    });
};

const releasePatient = () => {
    Swal.fire({
        toast: true,
        position: "top-end",
        title: "Release selected patient?",
        text: "You will need to select the patient again to access their summary, history, and details.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, release",
        cancelButtonText: "Cancel",
        reverseButtons: true,
        showClass: {
            popup: "swal2-show",
        },
    }).then((result) => {
        if (result.isConfirmed) {
            router.get(route("doctor.select.patient", "empty"));
        }
    });
};
</script>

<template>
    <AuthLayout title="All Patients" description="All Patients" heading="All Patients">
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
                            <ActionButtons :actionButtons="buttons" :extraButtons="extraButtons" />
                        </div>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-sm-6 col-xl-4">
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

                        <div class="col-12 col-sm-6 col-xl-3">
                            <label class="form-label text-muted small text-uppercase mb-2">Portal</label>
                            <select v-model="filterForm.portal_status" class="form-select" @change="applyFilters">
                                <option v-for="option in portalOptions" :key="option.value" :value="option.value">
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
                            <label for="patients-per-page" class="text-muted small text-uppercase mb-0">Rows</label>
                            <select id="patients-per-page" v-model="perPage"
                                class="form-select form-select-sm top-page-select" @change="updatePerPage">
                                <option v-for="option in perPageOptions" :key="option" :value="option">
                                    {{ option }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <Table :columns="columns" :data="patients" :search-show="false" :PageOptions="false">
                        <template #patient="{ row }">
                            <div class="d-flex align-items-center gap-3 text-start cursor-pointer"
                                @click="row.id && patientDetails(row.id)">
                                <img :src="row.avatar_url || '/images/avatar.webp'" alt="Patient avatar"
                                    class="patient-avatar" />
                                <div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <div class="fw-semibold text-dark">{{ row.display_name || "-" }}</div>
                                        <span v-if="row.is_selected" class="selected-pill">Selected</span>
                                    </div>
                                    <div class="text-muted small">{{ row.created_label || "-" }}</div>
                                </div>
                            </div>
                        </template>

                        <template #contact="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.email || "N/A" }}</div>
                                <div class="text-muted small">{{ row.phone || "N/A" }}</div>
                            </div>
                        </template>

                        <template #portal="{ row }">
                            <div class="d-flex justify-content-center">
                                <span v-if="!row.register_to_portal" class="status-pill status-pill--active">
                                    Registered
                                </span>
                                <button v-else class="btn btn-outline-danger btn-sm" @click="register(row.id)"
                                    :disabled="processing[row.id]">
                                    {{ processing[row.id] ? "Sending..." : "Register" }}
                                </button>
                            </div>
                        </template>

                        <template #status="{ row }">
                            <div class="d-flex justify-content-center">
                                <span class="status-pill"
                                    :class="row.is_active ? 'status-pill--active' : 'status-pill--inactive'">
                                    {{ row.status_label }}
                                </span>
                            </div>
                        </template>

                        <template #actions="{ row }">
                            <div class="table-actions d-flex justify-content-center gap-2">
                                <Link v-if="row.id && !row.is_selected" class="btn btn-success action-btn"
                                    :href="route('doctor.select.patient', row.id)" title="Select patient">
                                    <i class="bi bi-check2-circle"></i>
                                </Link>

                                <Link v-else class="btn btn-warning action-btn" as="button" href="#"
                                    @click.prevent="releasePatient" title="Release patient">
                                    <i class="bi bi-x-circle"></i>
                                </Link>

                                <button class="btn iq-bg-info action-btn" @click="row.id && patientSummary(row.id)"
                                    :disabled="!row.id" title="Patient summary">
                                    <i class="bi bi-journal-text"></i>
                                </button>

                                <button class="btn iq-bg-primary action-btn" @click="row.id && patientHistory(row.id)"
                                    :disabled="!row.id" title="Patient history">
                                    <i class="bi bi-clock-history"></i>
                                </button>
                            </div>
                        </template>
                    </Table>
                </div>
            </div>
        </div>

        <Modal :isOpen="isAddUserModalOpen" title="Add Patient" @close="closeAddUserModal" size="xl">
            <AddPatient @close="closeAddUserModal" />
        </Modal>

        <Modal :isOpen="showPatientSummary" title="Patient Summary" @close="showPatientSummary = false" size="xl">
            <PatientSummary :patient="patientData || {}" />
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

.patient-search-control {
    max-width: 720px;
}

.rows-select-wrap {
    min-width: 118px;
}

.top-page-select {
    width: 84px;
}

.patient-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e2e8f0;
    background: #ffffff;
}

.selected-pill {
    display: inline-flex;
    align-items: center;
    padding: 0.22rem 0.55rem;
    border-radius: 999px;
    background: #dbeafe;
    color: #1d4ed8;
    font-size: 0.72rem;
    font-weight: 700;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 110px;
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
    .patient-search-control {
        max-width: 100%;
    }
}
</style>
