<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, useForm } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import { computed, ref } from "vue";
import Swal from "sweetalert2/dist/sweetalert2.js";
import Modal from "@/Components/Common/Modal.vue";
import AddLab from "@/Pages/Modals/AddLab.vue";
import axios from "axios";
import { route } from "ziggy-js";

const props = defineProps({
    labs: Object,
    labCategory: {
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
    verification: props.filters?.verification ?? "",
    category: props.filters?.category || "",
});

const statusOptions = [
    { value: "", label: "All status" },
    { value: "true", label: "Active" },
    { value: "false", label: "Inactive" },
];

const verificationOptions = [
    { value: "", label: "All verification" },
    { value: "true", label: "Verified" },
    { value: "false", label: "Pending" },
];

const columns = [
    { label: "Lab", key: "display_name", type: "slot", slot: "name", align: "left" },
    { label: "Category", key: "categories", type: "slot", slot: "categories", align: "left" },
    { label: "Address", key: "address_sort", type: "slot", slot: "address", align: "left" },
    { label: "Contact", key: "email", type: "slot", slot: "contact", align: "left" },
    { label: "Verified", key: "is_verified", type: "slot", slot: "verification", align: "center" },
    { label: "Status", key: "is_active", type: "slot", slot: "status", align: "center" },
];

const rows = computed(() => props.labs?.data ?? []);
const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);
const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);
const resultSummary = computed(() => {
    const total = props.labs?.total ?? rows.value.length;
    const from = props.labs?.from ?? (rows.value.length ? 1 : 0);
    const to = props.labs?.to ?? rows.value.length;

    if (!total) {
        return "No labs found";
    }

    return `Showing ${from}-${to} of ${total} labs`;
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
    router.get(route("admin.labs.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        status: "",
        verification: "",
        category: "",
    };

    router.get(route("admin.labs.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("admin.labs.index"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const isAddLabModal = ref(false);
const childComponentRef = ref(null);

const openAddModal = () => {
	isAddLabModal.value = true;
};

const buttons = [
    {
        label: "Add Lab",
        function: openAddModal,
        icon: "bi bi-plus-circle",
    },
];

const closeAddModal = () => {
	isAddLabModal.value = false;
};

const openEdit = (row) => {
	setTimeout(() => {
		if (childComponentRef.value) {
			childComponentRef.value.update(row);
		}
	}, 50);
	isAddLabModal.value = true;
};

const removeRow = (row) => {
    Swal.fire(
        confirmSettings("Are you sure to delete this data?", "You won't be able to get it back")
    ).then((result) => {
        if (result.isConfirmed) {
            useForm({}).delete(route("admin.labs.destroy", row.id));
        }
    });
};

const toggleStatus = async (row) => {
    await axios.post(route("update.status"), {
        table: "labs",
        id: row.id,
        is_active: !row.is_active,
    });

    row.is_active = !row.is_active;
    row.status_label = row.is_active ? "Active" : "Inactive";
};
</script>
<template>
    <AuthLayout title="Labs" description="Labs" heading="Labs">
        <div class="labs-directory-page">
            <div class="users-toolbar card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div
                        class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                        <div>
                            <h3 class="mb-1">Labs</h3>
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
                            <div class="input-group lab-search-control">
                                <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input
                                    v-model="filterForm.keyword"
                                    type="search"
                                    class="form-control border-start-0"
                                    placeholder="Search by lab name, email, mobile, or license"
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
                            <label class="form-label text-muted small text-uppercase mb-2">Verification</label>
                            <select v-model="filterForm.verification" class="form-select" @change="applyFilters">
                                <option
                                    v-for="option in verificationOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-xl-3">
                            <label class="form-label text-muted small text-uppercase mb-2">Category</label>
                            <select v-model="filterForm.category" class="form-select" @change="applyFilters">
                                <option value="">All categories</option>
                                <option v-for="category in labCategory" :key="category.id" :value="category.name">
                                    {{ category.name }}
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
                                id="labs-per-page"
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

                    <Table :columns="columns" :data="labs" :search-show="false" :PageOptions="false">
                        <template #name="{ row }">
                            <div class="text-start d-flex align-items-center gap-3">
                                <img
                                    :src="row.banner_url || '/images/avatar.webp'"
                                    alt="Lab"
                                    class="rounded-circle listing-avatar"
                                />
                                <div>
                                    <div class="fw-semibold text-dark">{{ row.display_name || row.name }}</div>
                                    <div class="text-muted small">{{ row.created_label }}</div>
                                </div>
                            </div>
                        </template>

                        <template #categories="{ row }">
                            <div class="text-start">
                                <div v-if="Array.isArray(row.categories) && row.categories.length">
                                    <span class="badge bg-primary-subtle text-primary-emphasis me-1">
                                        {{ row.categories[0] }}
                                    </span>
                                    <span v-if="row.categories.length > 1" class="text-muted small">
                                        +{{ row.categories.length - 1 }} more
                                    </span>
                                </div>
                                <span v-else class="text-muted">N/A</span>
                            </div>
                        </template>

                        <template #address="{ row }">
                            <div v-if="row.user?.address" class="text-start">
                                <div class="fw-medium text-dark">
                                    {{ row.user.address.address_1 || "N/A" }}
                                    <span v-if="row.user.address.address_2">, {{ row.user.address.address_2 }}</span>
                                </div>
                                <div class="text-muted small">
                                    <span v-if="row.user.address.city">{{ row.user.address.city }}</span>
                                    <span v-if="row.user.address.state">, {{ row.user.address.state }}</span>
                                    <span v-if="row.user.address.country">, {{ row.user.address.country }}</span>
                                </div>
                                <div v-if="row.user.address.zip" class="text-muted small">
                                    {{ row.user.address.zip }}
                                </div>
                            </div>
                            <span v-else class="text-muted">N/A</span>
                        </template>

                        <template #contact="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.email || "N/A" }}</div>
                                <div class="text-muted small">{{ row.mobile || "No mobile" }}</div>
                            </div>
                        </template>

                        <template #verification="{ row }">
                            <span
                                class="status-pill"
                                :class="row.is_verified ? 'status-pill--verified' : 'status-pill--pending'"
                            >
                                {{ row.verification_label }}
                            </span>
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
                                <button class="btn btn-light border" @click="openEdit(row)" title="Edit lab">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-light border text-danger" @click="removeRow(row)" title="Delete lab">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </template>
                    </Table>
                </div>
            </div>

            <Modal :isOpen="isAddLabModal" :title="'Lab Details'" @close="closeAddModal" size="xl">
                <AddLab ref="childComponentRef" :labCategory="labCategory" @close="closeAddModal" />
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

.status-pill--verified {
    background: #dbeafe;
    color: #1d4ed8;
}

.status-pill--pending {
    background: #fef3c7;
    color: #92400e;
}

.top-page-select {
    min-width: 74px;
    width: 74px;
}

.rows-select-wrap {
    flex: 0 0 auto;
}

.lab-search-control {
    max-width: 620px;
}

.listing-avatar {
    width: 44px;
    height: 44px;
    object-fit: cover;
    border: 1px solid #e2e8f0;
}
</style>
