<script setup>
import { computed, ref } from "vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { router, usePage, useForm } from "@inertiajs/vue3";
import Swal from "sweetalert2";
import Modal from "@/Components/Common/Modal.vue";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseDatePicker from "@/Components/Common/Input/BaseDatePicker.vue";
import InputError from "@/Components/InputError.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import { route } from "ziggy-js";

const props = defineProps({
    alerts: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const selectedPatientId = computed(() => usePage().props.auth?.user?.doctor?.selected_patient_id);
const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const rows = computed(() => props.alerts?.data ?? []);
const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);
const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);
const resultSummary = computed(() => {
    const total = props.alerts?.total ?? rows.value.length;
    const from = props.alerts?.from ?? (rows.value.length ? 1 : 0);
    const to = props.alerts?.to ?? rows.value.length;

    if (!total) {
        return "No alerts found";
    }

    return `Showing ${from}-${to} of ${total} alerts`;
});

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    type: props.filters?.type || "active",
});

const typeOptions = [
    { value: "", label: "All" },
    { value: "active", label: "Active" },
    { value: "pending", label: "Pending" },
    { value: "results", label: "Pending Results" },
    { value: "completed", label: "Completed" },
    { value: "inactive", label: "Inactive" },
];

const columns = [
    { label: "Date", key: "date", type: "slot", slot: "date", align: "left" },
    { label: "Alert", key: "alert", type: "slot", slot: "alert", align: "left" },
    { label: "Description", key: "description", type: "slot", slot: "description", align: "left" },
    { label: "Status", key: "status_label", type: "slot", slot: "status", align: "center" },
];

const buttons = [
    {
        label: "Add Alert",
        function: () => openAddModal(),
        icon: "bi bi-plus-circle",
    },
];

const createForm = useForm({
    alert: "",
    description: "",
    date_active: "",
});

const editForm = useForm({
    id: "",
    alert: "",
    description: "",
    date_active: "",
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
    router.get(route("doctor.alerts.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        type: "active",
    };

    router.get(route("doctor.alerts.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("doctor.alerts.index"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const openAddModal = () => {
    createForm.reset();
    isAddModalOpen.value = true;
};

const closeAddModal = () => {
    isAddModalOpen.value = false;
    createForm.reset();
};

const storeAlert = () => {
    createForm.post(route("doctor.alerts.store"), {
        onSuccess: () => {
            closeAddModal();
        },
    });
};

const openEditModal = (row) => {
    editForm.id = row.id;
    editForm.alert = row.alert;
    editForm.description = row.description;
    editForm.date_active = row.date_active;
    isEditModalOpen.value = true;
};

const closeEditModal = () => {
    isEditModalOpen.value = false;
    editForm.reset();
};

const updateAlert = () => {
    editForm.put(route("doctor.alerts.update", editForm.id), {
        onSuccess: () => {
            closeEditModal();
        },
    });
};

const deleteAlert = (id) => {
    Swal.fire({
        toast: true,
        position: "top-end",
        title: "Delete alert?",
        text: "This alert will be permanently removed.",
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
            router.delete(route("doctor.alerts.destroy", id));
        }
    });
};

const markInactive = (id) => {
    Swal.fire({
        toast: true,
        position: "top-end",
        title: "Reason for cancellation",
        input: "text",
        inputLabel: "Please provide a reason",
        inputPlaceholder: "Enter reason...",
        showCancelButton: true,
        confirmButtonText: "Submit",
        cancelButtonText: "Cancel",
        reverseButtons: true,
        inputValidator: (value) => {
            if (!value) {
                return "Reason is required!";
            }
        },
        showClass: {
            popup: "swal2-show",
        },
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route("doctor.alert.status"), {
                id,
                type: "inactive",
                why_not_complete: result.value,
            });
        }
    });
};

const markActive = (id) => {
    router.post(route("doctor.alert.status"), {
        type: "active",
        id,
    });
};

const markComplete = (id) => {
    router.post(route("doctor.alert.status"), {
        type: "completed",
        id,
    });
};
</script>

<template>
    <AuthLayout title="Alerts" description="Alerts" heading="Alerts">
        <div class="alerts-page">
            <div v-if="!selectedPatientId" class="alert alert-warning text-center py-4">
                <h5><i class="bi bi-exclamation-triangle-fill me-2"></i>No Patient Selected</h5>
                <p class="mb-0">Please select a patient from the patient list to view their alerts.</p>
            </div>

            <template v-else>
                <div class="users-toolbar card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div
                            class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                            <div>
                                <h3 class="mb-1">Alerts</h3>
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
                                <div class="input-group alerts-search-control">
                                    <span
                                        class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>
                                    <input v-model="filterForm.keyword" type="search"
                                        class="form-control border-start-0" placeholder="Search by alert or description"
                                        @keydown.enter.prevent="applyFilters" />
                                    <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-xl-3">
                                <label class="form-label text-muted small text-uppercase mb-2">Type</label>
                                <select v-model="filterForm.type" class="form-select" @change="applyFilters">
                                    <option v-for="option in typeOptions" :key="option.value" :value="option.value">
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
                                <label for="alerts-per-page" class="text-muted small text-uppercase mb-0">Rows</label>
                                <select id="alerts-per-page" v-model="perPage"
                                    class="form-select form-select-sm top-page-select" @change="updatePerPage">
                                    <option v-for="option in perPageOptions" :key="option" :value="option">
                                        {{ option }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <Table :data="alerts" :columns="columns" :search-show="false" :PageOptions="false">
                            <template #date="{ row }">
                                <div class="text-start">
                                    <div class="fw-medium text-dark">{{ row.date || "-" }}</div>
                                </div>
                            </template>

                            <template #alert="{ row }">
                                <div class="text-start">
                                    <div class="fw-semibold text-dark">{{ row.alert || "-" }}</div>
                                </div>
                            </template>

                            <template #description="{ row }">
                                <div class="text-start">
                                    <div class="fw-medium text-dark">{{ row.description || "-" }}</div>
                                </div>
                            </template>

                            <template #status="{ row }">
                                <div class="d-flex justify-content-center">
                                    <span class="status-pill" :class="{
                                        'status-pill--active': row.status_label === 'Active',
                                        'status-pill--pending': row.status_label === 'Pending',
                                        'status-pill--inactive': ['Completed', 'Inactive'].includes(row.status_label),
                                    }">
                                        {{ row.status_label }}
                                    </span>
                                </div>
                            </template>

                            <template #actions="{ row }">
                                <div class="d-flex justify-content-end gap-2">
                                    <button class="btn btn-primary action-btn" @click="openEditModal(row)" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button v-if="!row.date_complete && !row.why_not_complete"
                                        class="btn btn-success action-btn" @click="markComplete(row.id)"
                                        title="Mark complete">
                                        <i class="bi bi-check-circle"></i>
                                    </button>

                                    <button v-if="!row.date_complete && !row.why_not_complete"
                                        class="btn btn-info action-btn" @click="markInactive(row.id)"
                                        title="Mark inactive">
                                        <i class="fa fa-minus-circle"></i>
                                    </button>
                                    <button
                                        v-if="(!row.date_complete && row.why_not_complete) || (row.date_complete && !row.why_not_complete)"
                                        class="btn btn-secondary action-btn" @click="markActive(row.id)"
                                        title="Reactivate alert">
                                        <i class="fa fa-plus-circle"></i>
                                    </button>
                                    <button class="btn btn-danger action-btn" @click="deleteAlert(row.id)"
                                        title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </template>
                        </Table>
                    </div>
                </div>
            </template>
        </div>

        <Modal :isOpen="isAddModalOpen" title="Add Alert" @close="closeAddModal" size="xl">
            <form @submit.prevent="storeAlert">
                <div class="mb-3">
                    <BaseInput v-model="createForm.alert" label="Alert Title" required />
                    <InputError :message="createForm.errors.alert" />
                </div>
                <div class="mb-3">
                    <BaseInput v-model="createForm.description" label="Description" type="textarea" />
                    <InputError :message="createForm.errors.description" />
                </div>
                <div class="mb-3">
                    <BaseDatePicker v-model="createForm.date_active" label="Active Date" />
                    <InputError :message="createForm.errors.date_active" />
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-danger" @click="closeAddModal">Cancel</button>
                    <button type="submit" class="btn btn-primary" :disabled="createForm.processing">Save</button>
                </div>
            </form>
        </Modal>

        <Modal :isOpen="isEditModalOpen" title="Edit Alert" @close="closeEditModal" size="xl">
            <form @submit.prevent="updateAlert">
                <div class="mb-3">
                    <BaseInput v-model="editForm.alert" label="Alert Title" required />
                </div>
                <div class="mb-3">
                    <BaseInput v-model="editForm.description" label="Description" type="textarea" required />
                </div>
                <div class="mb-3">
                    <BaseDatePicker v-model="editForm.date_active" label="Active Date" required />
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary" :disabled="editForm.processing">Update</button>
                    <button type="button" class="btn btn-danger" @click="closeEditModal">Close</button>
                </div>
            </form>
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

.alerts-search-control {
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

.status-pill--pending {
    background: #fef3c7;
    color: #92400e;
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
    .alerts-search-control {
        max-width: 100%;
    }
}
</style>
