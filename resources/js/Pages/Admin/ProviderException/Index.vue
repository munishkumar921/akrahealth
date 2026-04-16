<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import ExceptionModal from "@/Pages/Modals/ExceptionModal.vue";
import Modal from "@/Components/Common/Modal.vue";
import { computed, ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import Swal from "sweetalert2/dist/sweetalert2.js";
import axios from "axios";
import { route } from "ziggy-js";

const props = defineProps({
    data: {
        type: Object,
        default: () => ({}),
    },
    doctors: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({
            keyword: "",
            doctor_id: "",
            status: "",
        }),
    },
});

const isOpenModal = ref(false);
const childComponentRef = ref(null);
const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    doctor_id: props.filters?.doctor_id || "",
    status: props.filters?.status || "",
});

const statusOptions = [
    { value: "", label: "All status" },
    { value: "true", label: "Active" },
    { value: "false", label: "Inactive" },
];

const columns = [
    { label: "Date", key: "exception_date", type: "slot", slot: "date", align: "left" },
    { label: "Time", key: "start_time", type: "slot", slot: "time", align: "left" },
    { label: "Title", key: "title", type: "slot", slot: "title", align: "left" },
    { label: "Reason", key: "reason", type: "slot", slot: "reason", align: "left" },
    { label: "Provider", key: "doctor_name", type: "slot", slot: "provider", align: "left" },
    { label: "Status", key: "is_active", type: "slot", slot: "status", align: "center" },
];

const rows = computed(() => props.data?.data ?? []);
const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);
const resultSummary = computed(() => {
    const total = props.data?.total ?? rows.value.length;
    const from = props.data?.from ?? (rows.value.length ? 1 : 0);
    const to = props.data?.to ?? rows.value.length;

    if (!total) {
        return "No provider exceptions found";
    }

    return `Showing ${from}-${to} of ${total} provider exceptions`;
});

const buttons = [
    {
        label: "Add Provider Exception",
        function: () => goToAddException(),
        icon: "bi bi-plus-circle",
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
    router.get(route("admin.provider-exception.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        doctor_id: "",
        status: "",
    };

    router.get(route("admin.provider-exception.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("admin.provider-exception.index"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const closeModal = () => {
    isOpenModal.value = false;
};

const openEdit = (row) => {
    isOpenModal.value = true;
    setTimeout(() => {
        if (childComponentRef.value) {
            childComponentRef.value.update(row);
        }
    }, 100);
};

const toggleStatus = async (row) => {
    await axios.post(route("admin.provider-exception.toggle", row.id), {
        is_active: !row.is_active,
    });

    row.is_active = !row.is_active;
    row.status_label = row.is_active ? "Active" : "Inactive";
};

const goToAddException = () => {
    isOpenModal.value = true;
};

const removeRow = (row) => {
    Swal.fire(
        confirmSettings("Are you sure to delete this data?", "You won't be able to get it back")
    ).then((result) => {
        if (result.isConfirmed) {
            useForm({}).delete(route("admin.provider-exception.destroy", row.id));
        }
    });
};
</script>

<template>
    <AuthLayout
        title="Provider Exceptions for Schedule"
        description="Provider Exceptions for Schedule"
        heading="Provider Exceptions for Schedule"
    >
        <div class="provider-exception-page">
            <div class="users-toolbar card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div
                        class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                        <div>
                            <h3 class="mb-1">Exceptions</h3>
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
                        <div class="col-12 col-xl-4">
                            <label class="form-label text-muted small text-uppercase mb-2">Search</label>
                            <div class="input-group provider-exception-search-control">
                                <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input
                                    v-model="filterForm.keyword"
                                    type="search"
                                    class="form-control border-start-0"
                                    placeholder="Search by provider, title, reason, or date"
                                    @keydown.enter.prevent="applyFilters"
                                />
                                <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-3">
                            <label class="form-label text-muted small text-uppercase mb-2">Provider</label>
                            <select v-model="filterForm.doctor_id" class="form-select" @change="applyFilters">
                                <option value="">All providers</option>
                                <option v-for="doctor in doctors" :key="doctor.id" :value="doctor.id">
                                    {{ doctor.name }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
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
                            <select
                                id="provider-exceptions-per-page"
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

                    <Table :columns="columns" :data="data" :search-show="false" :PageOptions="false">
                        <template #date="{ row }">
                            <div class="text-start">
                                <div class="fw-semibold text-dark">{{ row.exception_date || "-" }}</div>
                            </div>
                        </template>

                        <template #time="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.start_time || "-" }} - {{ row.end_time || "-" }}</div>
                            </div>
                        </template>

                        <template #title="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.title || "-" }}</div>
                            </div>
                        </template>

                        <template #reason="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.reason || "No reason provided" }}</div>
                            </div>
                        </template>

                        <template #provider="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.doctor_name || "-" }}</div>
                            </div>
                        </template>

                        <template #status="{ row }">
                            <div class="d-flex justify-content-center align-items-center gap-2 w-100">
                                <span
                                    class="status-pill"
                                    :class="row.is_active ? 'status-pill--active' : 'status-pill--inactive'"
                                >
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
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-primary icon-btn" @click="openEdit(row)" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-danger icon-btn" @click="removeRow(row)" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </template>
                    </Table>
                </div>
            </div>

            <Modal :isOpen="isOpenModal" @close="closeModal" title="Provider Exceptions for Schedule" size="xl">
                <ExceptionModal ref="childComponentRef" @close="closeModal" :doctors="doctors" />
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
    min-width: 84px;
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
    width: 74px;
}

.rows-select-wrap {
    flex: 0 0 auto;
}

.provider-exception-search-control {
    max-width: 720px;
}

.ah-switch {
    position: relative;
    display: inline-flex;
    width: 48px;
    height: 28px;
}

.ah-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.ah-slider {
    position: absolute;
    inset: 0;
    cursor: pointer;
    background: #e2e8f0;
    transition: 0.2s ease;
    border-radius: 999px;
}

.ah-slider i {
    position: absolute;
    top: 50%;
    left: 7px;
    transform: translateY(-50%);
    color: #64748b;
    font-size: 0.9rem;
    transition: 0.2s ease;
}

.ah-switch input:checked + .ah-slider {
    background: #10b981;
}

.ah-switch input:checked + .ah-slider i {
    left: 26px;
    color: #ffffff;
}
</style>
