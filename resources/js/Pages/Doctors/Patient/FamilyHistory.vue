<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import Table from "@/Components/Table/Table.vue";
import Modal from "@/Components/Common/Modal.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import AddFamilyModal from "@/Pages/Modals/FamilyHistory.vue";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { route } from "ziggy-js";

const props = defineProps({
    familyHistory: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    relationshipOptions: {
        type: Array,
        default: () => [],
    },
    genderOptions: {
        type: Array,
        default: () => [],
    },
});

const isAddModalOpen = ref(false);
const childComponentRef = ref(null);

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    relationship: props.filters?.relationship || "",
    gender: props.filters?.gender || "",
});

const columns = [
    { label: "Name", key: "name", type: "slot", slot: "name", align: "left" },
    { label: "Relationship", key: "relationship", type: "slot", slot: "relationship", align: "left" },
    { label: "Gender", key: "gender", type: "slot", slot: "gender", align: "left" },
    { label: "DOB", key: "dob", type: "slot", slot: "dob", align: "left" },
    { label: "Marital Status", key: "marital_status", type: "slot", slot: "maritalStatus", align: "left" },
    { label: "Medical History", key: "medical_history", type: "slot", slot: "medicalHistory", align: "left" },
];

const rows = computed(() => props.familyHistory?.data ?? []);
const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);
const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);
const resultSummary = computed(() => {
    const total = props.familyHistory?.total ?? rows.value.length;
    const from = props.familyHistory?.from ?? (rows.value.length ? 1 : 0);
    const to = props.familyHistory?.to ?? rows.value.length;

    if (!total) {
        return "No family history records found";
    }

    return `Showing ${from}-${to} of ${total} family history records`;
});

const buttons = [
    {
        label: "Add Family History",
        function: () => addHistory(),
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
    router.get(route("doctor.family-history.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        relationship: "",
        gender: "",
    };

    router.get(route("doctor.family-history.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("doctor.family-history.index"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const addHistory = () => {
    isAddModalOpen.value = true;
    if (childComponentRef.value?.resetForm) {
        childComponentRef.value.resetForm();
    }
};

const closeAddFamilyModal = () => {
    isAddModalOpen.value = false;
};

const edit = (row) => {
    isAddModalOpen.value = true;
    setTimeout(() => {
        if (childComponentRef.value?.update) {
            childComponentRef.value.update(row);
        }
    }, 100);
};

const del = (row) => {
    Swal.fire(confirmSettings("Are you sure you want to delete this family history record?")).then((result) => {
        if (result.isConfirmed) {
            const deleteForm = useForm({});
            deleteForm.delete(route("doctor.family-history.destroy", row.id), {
                preserveScroll: true,
            });
        }
    });
};
</script>

<template>
    <AuthLayout title="Family History" description="Family History" heading="Family History">
        <div class="family-history-page">
            <div class="users-toolbar card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div
                        class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                        <div>
                            <h3 class="mb-1">Family History</h3>
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
                            <div class="input-group family-history-search-control">
                                <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input v-model="filterForm.keyword" type="search" class="form-control border-start-0"
                                    placeholder="Search by name, relationship, gender, or medical history"
                                    @keydown.enter.prevent="applyFilters" />
                                <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-3">
                            <label class="form-label text-muted small text-uppercase mb-2">Relationship</label>
                            <select v-model="filterForm.relationship" class="form-select" @change="applyFilters">
                                <option value="">All relationships</option>
                                <option v-for="option in relationshipOptions" :key="option" :value="option">
                                    {{ option }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-3">
                            <label class="form-label text-muted small text-uppercase mb-2">Gender</label>
                            <select v-model="filterForm.gender" class="form-select" @change="applyFilters">
                                <option value="">All genders</option>
                                <option v-for="option in genderOptions" :key="option" :value="option">
                                    {{ option }}
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
                            <label for="family-history-per-page"
                                class="text-muted small text-uppercase mb-0">Rows</label>
                            <select id="family-history-per-page" v-model="perPage"
                                class="form-select form-select-sm top-page-select" @change="updatePerPage">
                                <option v-for="option in perPageOptions" :key="option" :value="option">
                                    {{ option }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <Table :columns="columns" :data="familyHistory" :search-show="false" :PageOptions="false">
                        <template #name="{ row }">
                            <div class="text-start">
                                <div class="fw-semibold text-dark">{{ row.name || "-" }}</div>
                                <div class="text-muted small">{{ row.created_label || "-" }}</div>
                            </div>
                        </template>

                        <template #relationship="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.relationship || "-" }}</div>
                            </div>
                        </template>

                        <template #gender="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.gender || "-" }}</div>
                            </div>
                        </template>

                        <template #dob="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.dob || "-" }}</div>
                            </div>
                        </template>

                        <template #maritalStatus="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.marital_status || "-" }}</div>
                            </div>
                        </template>

                        <template #medicalHistory="{ row }">
                            <div v-if="row.medical_history?.length" class="d-flex flex-wrap gap-1">
                                <span v-for="(item, index) in row.medical_history"
                                    :key="`${row.row_id}-medical-${index}`" class="badge bg-primary">
                                    {{ item }}
                                </span>
                            </div>
                            <span v-else class="text-muted">-</span>
                        </template>

                        <template #actions="{ row }">
                            <div class="table-actions d-flex justify-content-center gap-2">
                                <button type="button" class="btn btn-primary action-btn" @click="edit(row)"
                                    title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button type="button" class="btn btn-danger action-btn" @click="del(row)"
                                    title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </template>
                    </Table>
                </div>
            </div>
        </div>

        <Modal :isOpen="isAddModalOpen" title="Family History" @close="closeAddFamilyModal" size="xl">
            <AddFamilyModal ref="childComponentRef" @close="closeAddFamilyModal" />
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

.family-history-search-control {
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
    .family-history-search-control {
        max-width: 100%;
    }
}
</style>
