<script setup>
import { computed, ref } from "vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, useForm } from "@inertiajs/vue3";
import Modal from "@/Components/Common/Modal.vue";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseDatePicker from "@/Components/Common/Input/BaseDatePicker.vue";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { route } from "ziggy-js";

const props = defineProps({
    supplements: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({
            keyword: "",
            stock_status: "",
            expiry_status: "",
        }),
    },
});

const showModal = ref(false);
const isEditing = ref(false);

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    stock_status: props.filters?.stock_status || "",
    expiry_status: props.filters?.expiry_status || "",
});

const stockOptions = [
    { value: "", label: "All stock" },
    { value: "in_stock", label: "In stock" },
    { value: "out_of_stock", label: "Out of stock" },
];

const expiryOptions = [
    { value: "", label: "All expiry" },
    { value: "valid", label: "Valid" },
    { value: "expiring_soon", label: "Expiring soon" },
    { value: "expired", label: "Expired" },
];

const form = useForm({
    id: null,
    purchase_date: "",
    sup_description: "",
    sup_strength: "",
    sup_manufacturer: "",
    sup_expiration: "",
    cpt: "",
    charge: "",
    quantity: 0,
    sup_lot: "",
});

const columns = [
    { label: "Purchase Date", key: "purchase_date", type: "slot", slot: "purchaseDate", align: "left" },
    { label: "Description", key: "description", type: "slot", slot: "description", align: "left" },
    { label: "Manufacturer", key: "manufacturer", type: "slot", slot: "manufacturer", align: "left" },
    { label: "Charge", key: "charge", type: "slot", slot: "charge", align: "left" },
    { label: "Quantity", key: "quantity", type: "slot", slot: "quantity", align: "center" },
    { label: "Expiry", key: "expiration", type: "slot", slot: "expiry", align: "left" },
    { label: "Actions", key: "actions", type: "slot", slot: "actions", align: "center" },
];

const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);
const rows = computed(() => props.supplements?.data ?? []);
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

const buttons = [
    {
        label: "Add Supplement",
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
    router.get(route("admin.supplements.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        stock_status: "",
        expiry_status: "",
    };

    router.get(route("admin.supplements.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("admin.supplements.index"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const openAddModal = () => {
    isEditing.value = false;
    showModal.value = true;
    form.reset();
};

const openEditModal = (supplement) => {
    isEditing.value = true;
    showModal.value = true;
    form.id = supplement.id;
    form.purchase_date = supplement.purchase_date;
    form.sup_description = supplement.description;
    form.sup_strength = supplement.strength;
    form.sup_manufacturer = supplement.manufacturer;
    form.sup_expiration = supplement.expiration;
    form.cpt = supplement.cpt;
    form.charge = supplement.charge;
    form.quantity = supplement.quantity;
    form.sup_lot = supplement.sup_lot;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const saveSupplement = () => {
    form.post(route("admin.supplements.store"), {
        onSuccess: () => {
            closeModal();
        },
    });
};

const removeRow = (id) => {
    Swal.fire(
        confirmSettings("Are you sure to delete this data?", "You won't be able to get it back")
    ).then((result) => {
        if (result.isConfirmed) {
            useForm({}).delete(route("admin.supplements.destroy", id));
        }
    });
};

const getStockClass = (status) => (status === "in_stock" ? "status-pill--active" : "status-pill--inactive");

const getExpiryClass = (status) => {
    if (status === "expired") return "status-pill--inactive";
    if (status === "expiring_soon") return "status-pill--pending";

    return "status-pill--active";
};

const formatCharge = (value) => {
    const amount = Number(value || 0).toFixed(2);

    return `₹${amount}`;
};
</script>

<template>
    <AuthLayout title="Supplements" description="Manage your supplements inventory" heading="Supplements">
        <div class="supplements-page">
            <div class="users-toolbar card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div
                        class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                        <div>
                            <h3 class="mb-1">Supplements Inventory</h3>
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
                            <div class="input-group supplements-search-control">
                                <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input
                                    v-model="filterForm.keyword"
                                    type="search"
                                    class="form-control border-start-0"
                                    placeholder="Search by description, manufacturer, CPT, lot, quantity, or dates"
                                    @keydown.enter.prevent="applyFilters"
                                />
                                <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Stock</label>
                            <select v-model="filterForm.stock_status" class="form-select" @change="applyFilters">
                                <option v-for="option in stockOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Expiry</label>
                            <select v-model="filterForm.expiry_status" class="form-select" @change="applyFilters">
                                <option v-for="option in expiryOptions" :key="option.value" :value="option.value">
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
                        <template #purchaseDate="{ row }">
                            <div class="text-start">
                                <div class="fw-semibold text-dark">{{ row.purchase_date || "-" }}</div>
                                <div class="text-muted small">{{ row.sup_lot || "No lot" }}</div>
                            </div>
                        </template>

                        <template #description="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.description || "-" }}</div>
                                <div class="text-muted small">{{ row.strength || "No strength" }}</div>
                            </div>
                        </template>

                        <template #manufacturer="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.manufacturer || "-" }}</div>
                                <div class="text-muted small">CPT: {{ row.cpt || "-" }}</div>
                            </div>
                        </template>

                        <template #charge="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ formatCharge(row.charge) }}</div>
                            </div>
                        </template>

                        <template #quantity="{ row }">
                            <div class="d-flex flex-column align-items-center gap-2">
                                <span class="fw-semibold text-dark">{{ row.quantity ?? 0 }}</span>
                                <span class="status-pill" :class="getStockClass(row.stock_status)">
                                    {{ row.stock_status_label }}
                                </span>
                            </div>
                        </template>

                        <template #expiry="{ row }">
                            <div class="text-start d-flex flex-column gap-2">
                                <div class="fw-medium text-dark">{{ row.expiration || "-" }}</div>
                                <span class="status-pill align-self-start" :class="getExpiryClass(row.expiry_status)">
                                    {{ row.expiry_status_label }}
                                </span>
                            </div>
                        </template>

                        <template #actions="{ row }">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="icon-btn btn btn-primary" @click="openEditModal(row)" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="icon-btn btn btn-danger" @click="removeRow(row.id)" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </template>
                    </Table>
                </div>
            </div>
        </div>

        <Modal :isOpen="showModal" :title="isEditing ? 'Edit Supplement' : 'Add Supplement'" size="xl" @close="closeModal">
            <form @submit.prevent="saveSupplement">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <BaseDatePicker
                                v-model="form.purchase_date"
                                label="Date Purchase"
                                placeholder="Select Date"
                                :error="form.errors.purchase_date"
                                required
                            />
                        </div>
                        <div class="col-md-6 mb-3">
                            <BaseInput
                                v-model="form.sup_description"
                                label="Description"
                                placeholder="Enter description"
                                required
                                :error="form.errors.sup_description"
                            />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <BaseInput
                                v-model="form.sup_strength"
                                label="Strength"
                                placeholder="Enter strength"
                                :error="form.errors.sup_strength"
                            />
                        </div>
                        <div class="col-md-6 mb-3">
                            <BaseInput
                                v-model="form.sup_manufacturer"
                                label="Manufacturer"
                                placeholder="Enter manufacturer"
                                :error="form.errors.sup_manufacturer"
                            />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <BaseDatePicker
                                v-model="form.sup_expiration"
                                label="Expiration"
                                placeholder="Select Date"
                                :error="form.errors.sup_expiration"
                                required
                            />
                        </div>
                        <div class="col-md-6 mb-3">
                            <BaseInput v-model="form.cpt" label="CPT" placeholder="Enter CPT" :error="form.errors.cpt" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <BaseInput
                                v-model="form.charge"
                                label="Charge"
                                placeholder="Enter charge"
                                :error="form.errors.charge"
                            />
                        </div>
                        <div class="col-md-6 mb-3">
                            <BaseInput
                                v-model="form.quantity"
                                label="Quantity"
                                placeholder="Enter quantity"
                                :error="form.errors.quantity"
                            />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <BaseInput
                                v-model="form.sup_lot"
                                label="Lot"
                                placeholder="Enter lot"
                                :error="form.errors.sup_lot"
                            />
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-danger" @click="closeModal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        {{ isEditing ? "Update" : "Save" }}
                    </button>
                </div>
            </form>
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

.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 92px;
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

.supplements-search-control {
    max-width: 720px;
}
</style>
