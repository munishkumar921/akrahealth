<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, useForm } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import Modal from "@/Components/Common/Modal.vue";
import AddMedicine from "@/Pages/Modals/AddMedicine.vue";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { computed, ref } from "vue";
import axios from "axios";
import { route } from "ziggy-js";

const props = defineProps({
    medicines: Object,
    filters: {
        type: Object,
        default: () => ({}),
    },
    data: {
        type: Object,
        default: () => ({}),
    },
});

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    status: props.filters?.status ?? "",
    dosage_form: props.filters?.dosage_form || "",
    route_name: props.filters?.route_name || "",
});

const statusOptions = [
    { value: "", label: "All status" },
    { value: "true", label: "Active" },
    { value: "false", label: "Inactive" },
];

const columns = [
    { label: "Medicine", key: "name", type: "slot", slot: "name", align: "left" },
    { label: "Brand", key: "brand_name", type: "slot", slot: "brand", align: "left" },
    { label: "Strength", key: "strength", type: "slot", slot: "strength", align: "left" },
    { label: "Price", key: "price", type: "slot", slot: "price", align: "left" },
    { label: "Expiry", key: "expiry_date", type: "slot", slot: "expiry", align: "left" },
    { label: "Status", key: "is_active", type: "slot", slot: "status", align: "center" },
];

const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);
const rows = computed(() => props.medicines?.data ?? []);
const dosageFormOptions = computed(() => props.data?.dosage_form ?? []);
const routeOptions = computed(() => props.data?.route ?? []);
const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);
const resultSummary = computed(() => {
    const total = props.medicines?.total ?? rows.value.length;
    const from = props.medicines?.from ?? (rows.value.length ? 1 : 0);
    const to = props.medicines?.to ?? rows.value.length;

    if (!total) {
        return "No medicines found";
    }

    return `Showing ${from}-${to} of ${total} medicines`;
});

const childComponentRef = ref(null);
const isAddModalOpen = ref(false);

const openAddModal = () => {
    isAddModalOpen.value = true;
};

const closeAddModal = () => {
    isAddModalOpen.value = false;
};

const openEdit = (row) => {
    setTimeout(() => {
        if (childComponentRef.value) {
            childComponentRef.value.update(row);
        }
    }, 50);
    isAddModalOpen.value = true;
};

const handleFormSubmit = () => {
    router.visit(route("admin.medicines.index"), {
        preserveState: true,
        preserveScroll: true,
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
    router.get(route("admin.medicines.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        status: "",
        dosage_form: "",
        route_name: "",
    };

    router.get(route("admin.medicines.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("admin.medicines.index"), buildQuery({ per_page: perPage.value, page: 1 }), {
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
            useForm({}).delete(route("admin.medicines.destroy", row.id));
        }
    });
};

const buttons = [
    {
        label: "Add Medicine",
        function: openAddModal,
        icon: "bi bi-plus-circle",
    },
];

const toggleStatus = async (row) => {
    await axios.post(route("update.status"), {
        table: "medicines",
        id: row.id,
        is_active: !row.is_active,
    });

    row.is_active = !row.is_active;
    row.status_label = row.is_active ? "Active" : "Inactive";
};
</script>

<template>
    <AuthLayout title="Medicines" description="Medicines" heading="Medicines">
        <div class="medicines-page">
            <div class="users-toolbar card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div
                        class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                        <div>
                            <h3 class="mb-1">Medicines</h3>
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
                            <div class="input-group medicine-search-control">
                                <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input
                                    v-model="filterForm.keyword"
                                    type="search"
                                    class="form-control border-start-0"
                                    placeholder="Search by medicine, brand, generic, strength, or batch"
                                    @keydown.enter.prevent="applyFilters"
                                />
                                <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Status</label>
                            <select v-model="filterForm.status" class="form-select" @change="applyFilters">
                                <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Dosage Form</label>
                            <select v-model="filterForm.dosage_form" class="form-select" @change="applyFilters">
                                <option value="">All dosage forms</option>
                                <option v-for="item in dosageFormOptions" :key="item.id" :value="item.id">
                                    {{ item.name }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Route</label>
                            <select v-model="filterForm.route_name" class="form-select" @change="applyFilters">
                                <option value="">All routes</option>
                                <option v-for="item in routeOptions" :key="item.id" :value="item.id">
                                    {{ item.name }}
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
                                id="medicines-per-page"
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

                    <Table :columns="columns" :data="medicines" :search-show="false" :PageOptions="false">
                        <template #name="{ row }">
                            <div class="text-start">
                                <div class="fw-semibold text-dark">{{ row.name }}</div>
                                <div class="text-muted small">{{ row.created_label }}</div>
                            </div>
                        </template>

                        <template #brand="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.brand_name || "N/A" }}</div>
                                <div class="text-muted small">{{ row.generic_name || "No generic name" }}</div>
                            </div>
                        </template>

                        <template #strength="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.strength || "N/A" }}</div>
                                <div class="text-muted small">
                                    {{ row.dosage_form || "No dosage form" }}
                                    <span v-if="row.route">, {{ row.route }}</span>
                                </div>
                            </div>
                        </template>

                        <template #price="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.price_label }}</div>
                                <div class="text-muted small">
                                    Stock: {{ row.stock_quantity ?? "N/A" }}
                                </div>
                            </div>
                        </template>

                        <template #expiry="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.expiry_date || "N/A" }}</div>
                                <div class="text-muted small">Batch: {{ row.batch_no || "N/A" }}</div>
                            </div>
                        </template>

                        <template #status="{ row }">
                            <div class="d-flex justify-content-center align-items-center gap-2">
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
                            <div class="d-flex gap-2">
                                <button class="btn btn-light border" @click="openEdit(row)" title="Edit medicine">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button
                                    class="btn btn-light border text-danger"
                                    @click="removeRow(row)"
                                    title="Delete medicine"
                                >
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </template>
                    </Table>
                </div>
            </div>

            <Modal :isOpen="isAddModalOpen" title="Add New Medicine" size="xl" @close="closeAddModal">
                <AddMedicine
                    ref="childComponentRef"
                    :dosage-forms="dosageFormOptions"
                    :routes="routeOptions"
                    @close="closeAddModal"
                    @submit="handleFormSubmit"
                />
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
    width: 74px;
}

.rows-select-wrap {
    flex: 0 0 auto;
}

.medicine-search-control {
    max-width: 620px;
}
</style>
