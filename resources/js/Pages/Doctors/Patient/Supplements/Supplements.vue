<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { ref, computed } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import Modal from "@/Components/Common/Modal.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import AddSupplementModal from "@/Pages/Modals/Supplement.vue";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { route } from "ziggy-js";

const props = defineProps({
    supplements: {
        type: Object,
        default: () => ({}),
    },
    encounters: {
        type: Object,
        default: null,
    },
    routeOptions: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const selectedPatientId = computed(() => usePage().props.auth?.user?.doctor?.selected_patient_id);
const isAddModalOpen = ref(false);
const childComponentRef = ref(null);

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    status: props.filters?.status || "",
    route_name: props.filters?.route_name || "",
});

const rows = computed(() => props.supplements?.data ?? []);
const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);
const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);
const resultSummary = computed(() => {
    const total = props.supplements?.total ?? rows.value.length;
    const from = props.supplements?.from ?? (rows.value.length ? 1 : 0);
    const to = props.supplements?.to ?? rows.value.length;

    if (!total) {
        return "No supplements found";
    }

    return `Showing ${from}-${to} of ${total} supplements`;
});

const statusOptions = [
    { value: "", label: "All status" },
    { value: "active", label: "Active" },
    { value: "inactive", label: "Inactive" },
];

const buttons = [
    {
        label: "Add Supplement",
        function: () => openAddSupplementModal(),
        icon: "bi bi-plus-circle",
    },
];

const columns = [
    { label: "Supplement", key: "supplement", type: "slot", slot: "supplement", align: "left" },
    { label: "Dosage", key: "dosage", type: "slot", slot: "dosage", align: "left" },
    { label: "Route", key: "route", type: "slot", slot: "route", align: "left" },
    { label: "Frequency", key: "frequency", type: "slot", slot: "frequency", align: "left" },
    { label: "Reason", key: "reason", type: "slot", slot: "reason", align: "left" },
    { label: "Date Active", key: "active_date_label", type: "slot", slot: "dateActive", align: "left" },
    { label: "Date Inactive", key: "inactive_date_label", type: "slot", slot: "dateInactive", align: "left" },
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
    router.get(route("doctor.supplements.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        status: "",
        route_name: "",
    };

    router.get(route("doctor.supplements.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("doctor.supplements.index"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const openAddSupplementModal = () => {
    if (!props.encounters?.id) {
        toast("Please create an encounter before adding a supplement.");
        return;
    }

    isAddModalOpen.value = true;
    if (childComponentRef.value?.resetForm) {
        childComponentRef.value.resetForm();
    }
};

const edit = (supplement) => {
    isAddModalOpen.value = true;
    setTimeout(() => {
        if (childComponentRef.value?.update) {
            childComponentRef.value.update(supplement);
        }
    }, 100);
};

const closeAddSupplementModal = () => {
    isAddModalOpen.value = false;
};

const supplementStatus = (row) => {
    router.get(route("doctor.supplement.status", { id: row.id, type: row.date_inactive ? "active" : "inactive" }), {
        preserveScroll: true,
    });
};

const deleteSupplement = (id) => {
    Swal.fire({
        toast: true,
        position: "top-end",
        title: "Delete supplement?",
        text: "This supplement record will be permanently removed.",
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
            const deleteForm = useForm({});
            deleteForm.delete(route("doctor.supplements.destroy", id), {
                preserveScroll: true,
            });
        }
    });
};
</script>

<template>
    <AuthLayout title="Supplements" description="Manage supplements" heading="Supplements">
        <div class="supplements-page">
            <div v-if="!selectedPatientId" class="alert alert-warning text-center py-4">
                <h5><i class="bi bi-exclamation-triangle-fill me-2"></i>No Patient Selected</h5>
                <p class="mb-0">Please select a patient from the patient list to view their supplements.</p>
            </div>

            <template v-else>
                <div class="users-toolbar card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                            <div>
                                <h3 class="mb-1">Supplements</h3>
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
                            <div class="col-12 col-sm-6 col-xl-4">
                                <label class="form-label text-muted small text-uppercase mb-2">Search</label>
                                <div class="input-group supplements-search-control">
                                    <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>
                                    <input
                                        v-model="filterForm.keyword"
                                        type="search"
                                        class="form-control border-start-0"
                                        placeholder="Search by supplement, dosage, route, frequency, or reason"
                                        @keydown.enter.prevent="applyFilters"
                                    />
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
                                <label class="form-label text-muted small text-uppercase mb-2">Route</label>
                                <select v-model="filterForm.route_name" class="form-select" @change="applyFilters">
                                    <option value="">All routes</option>
                                    <option
                                        v-for="routeName in routeOptions"
                                        :key="routeName"
                                        :value="routeName"
                                    >
                                        {{ routeName }}
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
                                <label for="supplements-per-page" class="text-muted small text-uppercase mb-0">Rows</label>
                                <select
                                    id="supplements-per-page"
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

                        <Table :columns="columns" :data="supplements" :search-show="false" :PageOptions="false">
                            <template #supplement="{ row }">
                                <div class="text-start">
                                    <div class="fw-semibold text-dark">{{ row.supplement || "-" }}</div>
                                </div>
                            </template>

                            <template #dosage="{ row }">
                                <div class="text-start">
                                    <div class="fw-medium text-dark">
                                        {{ [row.dosage, row.dosage_unit].filter(Boolean).join(" ") || "-" }}
                                    </div>
                                </div>
                            </template>

                            <template #route="{ row }">
                                <div class="text-start">
                                    <div class="fw-medium text-dark">{{ row.route || "-" }}</div>
                                </div>
                            </template>

                            <template #frequency="{ row }">
                                <div class="text-start">
                                    <div class="fw-medium text-dark">{{ row.frequency || "-" }}</div>
                                </div>
                            </template>

                            <template #reason="{ row }">
                                <div class="text-start">
                                    <div class="fw-medium text-dark">{{ row.reason || "-" }}</div>
                                </div>
                            </template>

                            <template #dateActive="{ row }">
                                <div class="text-start">
                                    <div class="fw-medium text-dark">{{ row.active_date_label || "-" }}</div>
                                </div>
                            </template>

                            <template #dateInactive="{ row }">
                                <div class="text-start">
                                    <div class="fw-medium text-dark">{{ row.inactive_date_label || "-" }}</div>
                                </div>
                            </template>

                            <template #status="{ row }">
                                <div class="d-flex justify-content-center">
                                    <label
                                        class="ah-switch"
                                        :title="row.date_inactive ? 'Mark as active' : 'Mark as inactive'"
                                    >
                                        <input type="checkbox" :checked="!row.date_inactive" @change="supplementStatus(row)" />
                                        <span class="ah-slider">
                                            <i class="bi bi-check2 toggle-check-icon"></i>
                                        </span>
                                    </label>
                                </div>
                            </template>

                            <template #actions="{ row }">
                                <div class="table-actions d-flex justify-content-center gap-2">
                                    <button class="btn btn-primary action-btn" @click="edit(row)" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-danger action-btn" @click="deleteSupplement(row.id)" title="Delete">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </template>
                        </Table>
                    </div>
                </div>
            </template>
        </div>

        <Modal :isOpen="isAddModalOpen" title="Add Supplement" @close="closeAddSupplementModal" size="xl">
            <AddSupplementModal
                ref="childComponentRef"
                :encounters="props.encounters"
                :route="routeOptions"
                @close="closeAddSupplementModal"
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

.supplements-search-control {
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
    .supplements-search-control {
        max-width: 100%;
    }
}
</style>
