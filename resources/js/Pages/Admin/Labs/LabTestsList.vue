<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, useForm } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import Modal from "@/Components/Common/Modal.vue";
import AddLabTest from "@/Pages/Modals/AddLabTest.vue";
import { computed, ref } from "vue";
import Swal from "sweetalert2/dist/sweetalert2.js";
import axios from "axios";
import { route } from "ziggy-js";

const props = defineProps({
    tests: Object,
    categories: {
        type: Array,
        default: () => [],
    },
    sampleTypes: {
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
    category_id: props.filters?.category_id || "",
    sample_type: props.filters?.sample_type || "",
});

const statusOptions = [
    { value: "", label: "All status" },
    { value: "true", label: "Active" },
    { value: "false", label: "Inactive" },
];

const columns = [
    { label: "Test", key: "name", type: "slot", slot: "name", align: "left" },
    { label: "Category", key: "category_name", type: "slot", slot: "category", align: "left" },
    { label: "Sample", key: "sample_type", type: "slot", slot: "sample", align: "left" },
    { label: "Report Time", key: "report_time", type: "slot", slot: "reportTime", align: "left" },
    { label: "Price", key: "final_price", type: "slot", slot: "price", align: "left" },
    { label: "Status", key: "is_active", type: "slot", slot: "status", align: "center" },
];

const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);
const rows = computed(() => props.tests?.data ?? []);
const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);
const resultSummary = computed(() => {
    const total = props.tests?.total ?? rows.value.length;
    const from = props.tests?.from ?? (rows.value.length ? 1 : 0);
    const to = props.tests?.to ?? rows.value.length;

    if (!total) {
        return "No lab tests found";
    }

    return `Showing ${from}-${to} of ${total} lab tests`;
});

const isAddModalOpen = ref(false);
const childComponentRef = ref(null);

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
    router.visit(route("admin.lab-tests.index"), {
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
    router.get(route("admin.lab-tests.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        status: "",
        category_id: "",
        sample_type: "",
    };

    router.get(route("admin.lab-tests.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("admin.lab-tests.index"), buildQuery({ per_page: perPage.value, page: 1 }), {
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
            useForm({}).delete(route("admin.lab-tests.destroy", row.id));
        }
    });
};

const buttons = [
    {
        label: "Add Lab Test",
        function: openAddModal,
        icon: "bi bi-plus-circle",
    },
];

const toggleStatus = async (row) => {
    await axios.post(route("update.status"), {
        table: "lab_tests",
        id: row.id,
        is_active: !row.is_active,
    });

    row.is_active = !row.is_active;
    row.status_label = row.is_active ? "Active" : "Inactive";
};
</script>

<template>
    <AuthLayout title="Lab Tests" description="Manage lab tests" heading="Lab Tests">
        <div class="lab-tests-page">
            <div class="users-toolbar card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div
                        class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                        <div>
                            <h3 class="mb-1">Lab Tests</h3>
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
                            <div class="input-group test-search-control">
                                <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input
                                    v-model="filterForm.keyword"
                                    type="search"
                                    class="form-control border-start-0"
                                    placeholder="Search by test, sample type, report time, or category"
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

                        <div class="col-12 col-sm-6 col-xl-3">
                            <label class="form-label text-muted small text-uppercase mb-2">Category</label>
                            <select v-model="filterForm.category_id" class="form-select" @change="applyFilters">
                                <option value="">All categories</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-xl-3">
                            <label class="form-label text-muted small text-uppercase mb-2">Sample Type</label>
                            <select v-model="filterForm.sample_type" class="form-select" @change="applyFilters">
                                <option value="">All sample types</option>
                                <option v-for="type in sampleTypes" :key="type.id" :value="type.id">
                                    {{ type.name }}
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
                                id="lab-tests-per-page"
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

                    <Table :columns="columns" :data="tests" :search-show="false" :PageOptions="false">
                        <template #name="{ row }">
                            <div class="text-start">
                                <div class="fw-semibold text-dark">{{ row.name }}</div>
                                <div class="text-muted small">{{ row.created_label }}</div>
                            </div>
                        </template>

                        <template #category="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.category_name || "N/A" }}</div>
                            </div>
                        </template>

                        <template #sample="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.sample_type || "N/A" }}</div>
                                <div class="text-muted small">
                                    Fasting required: {{ row.fasting_label }}
                                </div>
                            </div>
                        </template>

                        <template #reportTime="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.report_time || "N/A" }}</div>
                                <div class="text-muted small">
                                    Home collection: {{ row.home_collection_label }}
                                </div>
                            </div>
                        </template>

                        <template #price="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.price_label }}</div>
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
                                <button class="btn btn-light border" @click="openEdit(row)" title="Edit test">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-light border text-danger" @click="removeRow(row)" title="Delete test">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </template>
                    </Table>
                </div>
            </div>

            <Modal :isOpen="isAddModalOpen" title="Lab Test Details" size="xl" @close="closeAddModal">
                <AddLabTest
                    ref="childComponentRef"
                    :categories="categories"
                    :sample-types="sampleTypes"
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

.test-search-control {
    max-width: 560px;
}
</style>
