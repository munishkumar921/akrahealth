<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, useForm } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import Modal from "@/Components/Common/Modal.vue";
import AddPharmacy from "@/Pages/Modals/AddPharmacy.vue";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { computed, ref } from "vue";
import axios from "axios";
import { route } from "ziggy-js";

const props = defineProps({
    pharmacies: Object,
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    status: props.filters?.status ?? "",
    verification: props.filters?.verification ?? "",
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
    { label: "Pharmacy", key: "display_name", type: "slot", slot: "name", align: "left" },
    { label: "Contact", key: "contact_email", type: "slot", slot: "contact", align: "left" },
    { label: "Address", key: "address_sort", type: "slot", slot: "address", align: "left" },
    { label: "Verified", key: "is_verified", type: "slot", slot: "verification", align: "center" },
    { label: "Status", key: "is_active", type: "slot", slot: "status", align: "center" },
];

const rows = computed(() => props.pharmacies?.data ?? []);
const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);
const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);
const resultSummary = computed(() => {
    const total = props.pharmacies?.total ?? rows.value.length;
    const from = props.pharmacies?.from ?? (rows.value.length ? 1 : 0);
    const to = props.pharmacies?.to ?? rows.value.length;

    if (!total) {
        return "No pharmacies found";
    }

    return `Showing ${from}-${to} of ${total} pharmacies`;
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
    router.get(route("admin.pharmacies.index"), buildQuery(), {
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
    };

    router.get(route("admin.pharmacies.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("admin.pharmacies.index"), buildQuery({ per_page: perPage.value, page: 1 }), {
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
            useForm({}).delete(route("admin.pharmacies.destroy", row.id));
        }
    });
};

const isAddPharmacyModal = ref(false);
const childComponentRef = ref(null);

const openAddModal = () => {
    isAddPharmacyModal.value = true;
};

const closeAddModal = () => {
    isAddPharmacyModal.value = false;
};

const openEditModal = (row) => {
    isAddPharmacyModal.value = true;
    setTimeout(() => {
        if (childComponentRef.value) {
            childComponentRef.value.update(row);
        }
    }, 50);
};

const buttons = [
    {
        label: "Add Pharmacy",
        function: openAddModal,
        icon: "bi bi-plus-circle",
    },
];

const toggleStatus = async (row) => {
    await axios.post(route("update.status"), {
        table: "pharmacies",
        id: row.id,
        is_active: !row.is_active,
    });

    row.is_active = !row.is_active;
    row.status_label = row.is_active ? "Active" : "Inactive";
};
</script>

<template>
    <AuthLayout title="Pharmacy" description="Pharmacy" heading="Pharmacy">
        <div class="pharmacy-directory-page">
            <div class="users-toolbar card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div
                        class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                        <div>
                            <h3 class="mb-1">Pharmacy</h3>
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
                            <div class="input-group pharmacy-search-control">
                                <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input
                                    v-model="filterForm.keyword"
                                    type="search"
                                    class="form-control border-start-0"
                                    placeholder="Search by pharmacy name, license, email, mobile, or address"
                                    @keydown.enter.prevent="applyFilters"
                                />
                                <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-4">
                            <label class="form-label text-muted small text-uppercase mb-2">Status</label>
                            <select v-model="filterForm.status" class="form-select" @change="applyFilters">
                                <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-3">
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
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0 p-md-3">
                    <div class="d-flex justify-content-end align-items-center px-3 px-md-0 pt-3 pt-md-0 pb-2">
                        <div class="d-flex align-items-center gap-2 rows-select-wrap">
                            <select
                                id="pharmacies-per-page"
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

                    <Table :columns="columns" :data="pharmacies" :search-show="false" :PageOptions="false">
                        <template #name="{ row }">
                            <div class="text-start d-flex align-items-center gap-3">
                                <img
                                    :src="row.banner_url || '/images/avatar.webp'"
                                    alt="Pharmacy"
                                    class="rounded-circle pharmacy-logo"
                                />
                                <div>
                                    <div class="fw-semibold text-dark">{{ row.display_name || row.name }}</div>
                                    <div class="text-muted small">{{ row.created_label }}</div>
                                </div>
                            </div>
                        </template>

                        <template #contact="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">
                                    {{ row.contact_name || "No contact person" }}
                                </div>
                                <div class="text-muted small">{{ row.contact_email || "N/A" }}</div>
                                <div class="text-muted small">{{ row.contact_mobile || "No mobile" }}</div>
                            </div>
                        </template>

                        <template #address="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.address || "N/A" }}</div>
                                <div class="text-muted small">
                                    <span v-if="row.city">{{ row.city }}</span>
                                    <span v-if="row.state">, {{ row.state }}</span>
                                    <span v-if="row.country">, {{ row.country }}</span>
                                </div>
                                <div v-if="row.pincode" class="text-muted small">{{ row.pincode }}</div>
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
                                <button class="btn btn-light border" @click="openEditModal(row)" title="Edit pharmacy">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button
                                    class="btn btn-light border text-danger"
                                    @click="removeRow(row)"
                                    title="Delete pharmacy"
                                >
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </template>
                    </Table>
                </div>
            </div>

            <Modal :isOpen="isAddPharmacyModal" :title="'Pharmacy Details'" @close="closeAddModal" size="xl">
                <AddPharmacy ref="childComponentRef" @close="closeAddModal" @submit="closeAddModal" />
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

.pharmacy-search-control {
    max-width: 720px;
}

.pharmacy-logo {
    width: 44px;
    height: 44px;
    object-fit: cover;
    border: 1px solid #e2e8f0;
}
</style>
