<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import Modal from "@/Components/Common/Modal.vue";
import HospitalTimeModal from "@/Pages/Modals/HospitalTimeModal.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import { router, useForm } from "@inertiajs/vue3";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { computed, ref } from "vue";
import { route } from "ziggy-js";

const props = defineProps({
    hospitalTime: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({
            keyword: "",
            day_of_week: "",
        }),
    },
});

const isModalOpen = ref(false);
const childComponentRef = ref(null);
const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    day_of_week: props.filters?.day_of_week || "",
});

const dayOptions = [
    { value: "", label: "All days" },
    { value: "Monday", label: "Monday" },
    { value: "Tuesday", label: "Tuesday" },
    { value: "Wednesday", label: "Wednesday" },
    { value: "Thursday", label: "Thursday" },
    { value: "Friday", label: "Friday" },
    { value: "Saturday", label: "Saturday" },
    { value: "Sunday", label: "Sunday" },
];

const columns = [
    { key: "day_of_week", type: "slot", slot: "day", label: "Day Of Week", align: "left" },
    { key: "time_zone", type: "slot", slot: "timezone", label: "Time Zone", align: "left" },
    { key: "open_time", type: "slot", slot: "open", label: "Open Time", align: "left" },
    { key: "close_time", type: "slot", slot: "close", label: "Close Time", align: "left" },
];

const rows = computed(() => props.hospitalTime?.data ?? []);
const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);
const resultSummary = computed(() => {
    const total = props.hospitalTime?.total ?? rows.value.length;
    const from = props.hospitalTime?.from ?? (rows.value.length ? 1 : 0);
    const to = props.hospitalTime?.to ?? rows.value.length;

    if (!total) {
        return "No schedules found";
    }

    return `Showing ${from}-${to} of ${total} schedules`;
});

const buttons = [
    {
        label: "Add Schedule",
        function: () => openAddModal(),
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
    router.get(route("admin.hospital-timing"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        day_of_week: "",
    };

    router.get(route("admin.hospital-timing"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("admin.hospital-timing"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const openAddModal = () => {
    isModalOpen.value = true;
    setTimeout(() => {
        if (childComponentRef.value) {
            childComponentRef.value.resetForm();
        }
    }, 100);
};

const openEditModal = (row) => {
    isModalOpen.value = true;
    setTimeout(() => {
        if (childComponentRef.value) {
            childComponentRef.value.update(row);
        }
    }, 100);
};

const removeRow = (row) => {
    Swal.fire(
        confirmSettings("Are you sure to delete this data?", "You won't be able to get it back")
    ).then((result) => {
        if (result.isConfirmed) {
            useForm({}).delete(route("admin.hospital-timing.destroy", row.id));
        }
    });
};

const closeModal = () => {
    isModalOpen.value = false;
};

const handleSubmit = () => {
    closeModal();
};
</script>

<template>
    <AuthLayout title="Schedule Setup" description="Manage your schedule setup" heading="Schedule Setup">
        <div class="schedule-setup-page">
            <div class="users-toolbar card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div
                        class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                        <div>
                            <h3 class="mb-1">Schedule Setup</h3>
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
                            <div class="input-group schedule-search-control">
                                <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input
                                    v-model="filterForm.keyword"
                                    type="search"
                                    class="form-control border-start-0"
                                    placeholder="Search by day, timezone, open time, or close time"
                                    @keydown.enter.prevent="applyFilters"
                                />
                                <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-3">
                            <label class="form-label text-muted small text-uppercase mb-2">Day</label>
                            <select v-model="filterForm.day_of_week" class="form-select" @change="applyFilters">
                                <option v-for="option in dayOptions" :key="option.value" :value="option.value">
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
                                id="schedule-setup-per-page"
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

                    <Table :columns="columns" :data="hospitalTime" :search-show="false" :PageOptions="false">
                        <template #day="{ row }">
                            <div class="text-start">
                                <div class="fw-semibold text-dark">{{ row.day_of_week || "-" }}</div>
                                <div class="text-muted small">Weekends: {{ row.weekends_label || "-" }}</div>
                            </div>
                        </template>

                        <template #timezone="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.time_zone || "-" }}</div>
                            </div>
                        </template>

                        <template #open="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.open_time || "-" }}</div>
                            </div>
                        </template>

                        <template #close="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.close_time || "-" }}</div>
                            </div>
                        </template>

                        <template #actions="{ row }">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-primary icon-btn" @click="openEditModal(row)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-danger icon-btn" @click="removeRow(row)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </template>
                    </Table>
                </div>
            </div>
        </div>

        <Modal :isOpen="isModalOpen" @close="closeModal" title="Clinic Time Setup" size="xl">
            <HospitalTimeModal ref="childComponentRef" @close="closeModal" @submit="handleSubmit" />
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

.top-page-select {
    min-width: 74px;
    width: 74px;
}

.rows-select-wrap {
    flex: 0 0 auto;
}

.schedule-search-control {
    max-width: 720px;
}
</style>
