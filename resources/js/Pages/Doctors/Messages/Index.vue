<script setup>
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import TelephoneVisit from "@/Pages/Modals/TelephoneVisit.vue";
import Table from "@/Components/Table/Table.vue";
import Modal from "@/Components/Common/Modal.vue";
import Swal from "sweetalert2/dist/sweetalert2.js";

const props = defineProps({
    filters: {
        type: Object,
        default: () => ({}),
    },
    messages: {
        type: Object,
        default: () => ({ data: [], links: [], meta: {} }),
    },
    patients: {
        type: Array,
        default: () => [],
    },
    doctors: {
        type: Array,
        default: () => [],
    },
});

const columns = [
    { label: "Date", key: "date", type: "slot", slot: "date", align: "left" },
    { label: "Patient", key: "patient_name", type: "slot", slot: "patient", align: "left" },
    { label: "Doctor", key: "doctor_name", type: "slot", slot: "doctor", align: "left" },
    { label: "Subject", key: "subject", type: "slot", slot: "subject", align: "left" },
    { label: "Message", key: "message", type: "slot", slot: "message", align: "left" },
    { label: "Status", key: "status", type: "slot", slot: "status", align: "center" },
];

const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 15);
const openModal = ref(false);
const isEditMode = ref(false);
const selectedMessage = ref({});

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    status: props.filters?.status || "",
    patient_id: props.filters?.patient_id || "",
});

const patientOptions = computed(() =>
    props.patients.map((patient) => ({
        value: patient.id,
        label: patient.name || patient.user?.name || "Unknown patient",
    }))
);

const statusOptions = computed(() => {
    const statuses = [...new Set((props.messages?.data || []).map((message) => message.status).filter(Boolean))];
    return statuses.sort((a, b) => a.localeCompare(b));
});

const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);

const hasActiveFilters = computed(() => activeFilterCount.value > 0);

const resultSummary = computed(() => {
    const total = props.messages?.total ?? props.messages?.data?.length ?? 0;
    const from = props.messages?.from ?? (total ? 1 : 0);
    const to = props.messages?.to ?? total;

    if (!total) {
        return "No messages found";
    }

    return `Showing ${from}-${to} of ${total} messages`;
});

const closeModal = () => {
    openModal.value = false;
    isEditMode.value = false;
    selectedMessage.value = {};
};

const openModalTelephoneVisit = () => {
    openModal.value = true;
};

const editMessage = (msg) => {
    selectedMessage.value = msg;
    openModalTelephoneVisit();
    isEditMode.value = true;
};

const viewMessage = (msg) => {
    router.get(route("doctor.messages.show", msg.id));
};

const deleteMessage = (msg) => {
    Swal.fire({
        toast: true,
        position: "top-end",
        title: "Delete message?",
        text: "This message will be permanently removed.",
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
            router.delete(route("doctor.messages.destroy", msg.id));
        }
    });
};

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
    router.get(route("doctor.messages.index"), buildQuery({ page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        status: "",
        patient_id: "",
    };

    router.get(route("doctor.messages.index"), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("doctor.messages.index"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const modalTitle = computed(() => (isEditMode.value ? "Edit Message" : "New Message"));
</script>

<template>
    <AuthLayout title="Messages" description="Manage patient messages" heading="Messages">
        <div class="users-toolbar card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                    <div>
                        <h3 class="mb-1">Messages</h3>
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
                        <button type="button" class="btn btn-primary" @click="openModalTelephoneVisit">
                            <i class="bi bi-plus-circle me-1"></i>Add Message
                        </button>
                    </div>
                </div>

                <div class="row g-3 align-items-end">
                    <div class="col-12 col-sm-6 col-xl-4">
                        <label class="form-label text-muted small text-uppercase mb-2">Search</label>
                        <div class="input-group messages-search-control">
                            <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input
                                v-model="filterForm.keyword"
                                type="search"
                                class="form-control border-start-0"
                                placeholder="Search messages"
                                @keydown.enter.prevent="applyFilters"
                            />
                            <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <label class="form-label text-muted small text-uppercase mb-2">Patient</label>
                        <select v-model="filterForm.patient_id" class="form-select" @change="applyFilters">
                            <option value="">All patients</option>
                            <option v-for="option in patientOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <label class="form-label text-muted small text-uppercase mb-2">Status</label>
                        <select v-model="filterForm.status" class="form-select" @change="applyFilters">
                            <option value="">All status</option>
                            <option v-for="status in statusOptions" :key="status" :value="status">
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
                        <label for="messages-per-page" class="text-muted small text-uppercase mb-0">Rows</label>
                        <select
                            id="messages-per-page"
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

                <Table :columns="columns" :data="messages" :search-show="false" :PageOptions="false">
                    <template #date="{ row }">
                        <div class="text-start">
                            <div class="fw-medium text-dark">{{ row.date || "-" }}</div>
                        </div>
                    </template>

                    <template #patient="{ row }">
                        <div class="text-start">
                            <div class="fw-semibold text-dark">{{ row.patient_name || "-" }}</div>
                        </div>
                    </template>

                    <template #doctor="{ row }">
                        <div class="text-start">
                            <div class="fw-medium text-dark">{{ row.doctor_name || "-" }}</div>
                        </div>
                    </template>

                    <template #subject="{ row }">
                        <div class="text-start">
                            <div class="fw-semibold text-dark">{{ row.subject || "-" }}</div>
                        </div>
                    </template>

                    <template #message="{ row }">
                        <div class="text-start message-preview">
                            {{ row.message || "-" }}
                        </div>
                    </template>

                    <template #status="{ row }">
                        <div class="d-flex justify-content-center">
                            <span
                                class="status-pill"
                                :class="{
                                    'status-pill--sent': row.status === 'Sent',
                                    'status-pill--draft': row.status === 'Draft',
                                }"
                            >
                                {{ row.status || "N/A" }}
                            </span>
                        </div>
                    </template>

                    <template #actions="{ row }">
                        <div class="d-flex gap-2 justify-content-end">
                            <button class="btn btn-success action-btn" title="View" @click="viewMessage(row)">
                                <i class="ri-eye-line"></i>
                            </button>
                            <button class="btn btn-info action-btn" title="Edit" @click="editMessage(row)">
                                <i class="ri-pencil-line"></i>
                            </button>
                            <button class="btn btn-danger action-btn" title="Delete" @click="deleteMessage(row)">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                    </template>
                </Table>
            </div>
        </div>

        <Modal :isOpen="openModal" @close="closeModal" :title="modalTitle" size="xl">
            <TelephoneVisit
                :patients="patients"
                :doctors="doctors"
                :row="selectedMessage"
                :isEdit="isEditMode"
                @close="closeModal"
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

.messages-search-control {
    max-width: 100%;
}

.rows-select-wrap {
    min-width: 118px;
}

.top-page-select {
    width: 84px;
}

.message-preview {
    max-width: 320px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
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
    background: #e2e8f0;
    color: #475569;
}

.status-pill--sent {
    background: #dcfce7;
    color: #166534;
}

.status-pill--draft {
    background: #fef3c7;
    color: #92400e;
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
