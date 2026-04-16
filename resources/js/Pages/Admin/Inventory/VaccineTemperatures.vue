<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { router, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { computed, ref } from "vue";
import AddVaccineTemperature from "@/Pages/Modals/AddVaccineTemperature.vue";
import Modal from "@/Components/Common/Modal.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";

const props = defineProps({
    temperatures: {
        type: Object,
        default: () => ({}),
    },
    actionOptions: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({
            keyword: "",
            action: "",
        }),
    },
});

const isModalOpen = ref(false);
const modalTitle = ref("Add Vaccine Temperature");
const childComponentRef = ref(null);
const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    action: props.filters?.action || "",
});

const actionOptions = computed(() => [
    { value: "", label: "All actions" },
    ...props.actionOptions.map((value) => ({ value, label: value })),
]);

const columns = [
    { label: "Date", key: "date", type: "slot", slot: "date", align: "left" },
    { label: "Time", key: "time", type: "slot", slot: "time", align: "left" },
    { label: "Temperature", key: "temperature", type: "slot", slot: "temperature", align: "left" },
    { label: "Action", key: "action", type: "slot", slot: "action", align: "left" },
];

const rows = computed(() => props.temperatures?.data ?? []);
const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);
const resultSummary = computed(() => {
    const total = props.temperatures?.total ?? rows.value.length;
    const from = props.temperatures?.from ?? (rows.value.length ? 1 : 0);
    const to = props.temperatures?.to ?? rows.value.length;

    if (!total) {
        return "No vaccine temperatures found";
    }

    return `Showing ${from}-${to} of ${total} vaccine temperature logs`;
});

const buttons = [
    {
        label: "Add Vaccine Temperature",
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
    router.get(route("admin.vaccines.temperature.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        action: "",
    };

    router.get(route("admin.vaccines.temperature.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("admin.vaccines.temperature.index"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const openAddModal = () => {
    modalTitle.value = "Add Vaccine Temperature";
    isModalOpen.value = true;
    setTimeout(() => {
        if (childComponentRef.value) {
            childComponentRef.value.reset();
        }
    }, 100);
};

const openEditModal = (row) => {
    modalTitle.value = "Edit Vaccine Temperature";
    isModalOpen.value = true;
    setTimeout(() => {
        if (childComponentRef.value) {
            childComponentRef.value.update(row);
        }
    }, 100);
};

const closeModal = () => {
    isModalOpen.value = false;
};

const removeRow = (row) => {
    Swal.fire(
        confirmSettings("Are you sure to delete this data?", "You won't be able to get it back")
    ).then((result) => {
        if (result.isConfirmed) {
            useForm({}).delete(route("admin.vaccines.temperature.destroy", row.id));
        }
    });
};
</script>

<template>
    <AuthLayout title="Vaccine Temperatures" description="Manage your vaccine temperatures"
        heading="Vaccine Temperatures">
        <div class="vaccine-temperatures-page">
            <div class="users-toolbar card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div
                        class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                        <div>
                            <h3 class="mb-1">Vaccine Temperatures</h3>
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
                            <div class="input-group vaccine-temperature-search-control">
                                <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input v-model="filterForm.keyword" type="search" class="form-control border-start-0"
                                    placeholder="Search by date, time, temperature, or action"
                                    @keydown.enter.prevent="applyFilters" />
                                <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-3">
                            <label class="form-label text-muted small text-uppercase mb-2">Action</label>
                            <select v-model="filterForm.action" class="form-select" @change="applyFilters">
                                <option v-for="option in actionOptions" :key="option.value" :value="option.value">
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
                            <select id="vaccine-temperatures-per-page" v-model="perPage"
                                class="form-select form-select-sm top-page-select" @change="updatePerPage">
                                <option v-for="option in perPageOptions" :key="option" :value="option">
                                    {{ option }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <Table :columns="columns" :data="temperatures" :search-show="false" :PageOptions="false">
                        <template #date="{ row }">
                            <div class="text-start">
                                <div class="fw-semibold text-dark">{{ row.date || "-" }}</div>
                            </div>
                        </template>

                        <template #time="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.time || "-" }}</div>
                            </div>
                        </template>

                        <template #temperature="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.temperature || "-" }}</div>
                            </div>
                        </template>

                        <template #action="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.action || "-" }}</div>
                            </div>
                        </template>

                        <template #actions="{ row }">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="icon-btn btn btn-primary" @click="openEditModal(row)" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="icon-btn btn btn-danger" @click="removeRow(row)" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </template>
                    </Table>
                </div>
            </div>
        </div>

        <Modal :isOpen="isModalOpen" @close="closeModal" :title="modalTitle" size="xl">
            <AddVaccineTemperature ref="childComponentRef" @close="closeModal" @submit="closeModal" />
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

.vaccine-temperature-search-control {
    max-width: 720px;
}
</style>
