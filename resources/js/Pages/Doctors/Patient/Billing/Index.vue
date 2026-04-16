<script setup>
import { computed, ref } from "vue";
import { useForm, router, Link, usePage } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout2.vue";
import Modal from "@/Components/Common/Modal.vue";
import EditBillingNotesModal from "../Partials/EditBillingNotesModal.vue";
import Table from "@/Components/Table/Table.vue";
import AddPaymentModal from "@/Pages/Modals/AddBillingPayment.vue";

const props = defineProps({
    billingData: {
        type: Object,
        default: () => ({
            records: { data: [], links: [] },
            type: "encounters",
            totals: {
                records: 0,
                balance: 0,
                charges: 0,
            },
            error: "",
        }),
    },
    notes: {
        type: Object,
        default: null,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const selectedPatientId = computed(() => usePage().props.auth?.user?.doctor?.selected_patient_id ?? null);
const currentTab = ref(props.billingData.type || props.filters?.type || "encounters");
const isEditModalOpen = ref(false);
const isAddPaymentModal = ref(false);
const selectedRow = ref(null);
const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);

const filterForm = ref({
    keyword: props.filters?.keyword || "",
});

const form = useForm({
    notes: props.notes?.billing_note || "",
    id: props.notes?.id || "",
});

const tabs = [
    { value: "encounters", label: "Encounters", dotClass: "dot-success" },
    { value: "misc", label: "Miscellaneous Bills", dotClass: "dot-warning" },
];

const columns = [
    { label: "Date", key: "date", type: "slot", slot: "date", align: "left" },
    { label: "Reason", key: "reason", type: "slot", slot: "reason", align: "left" },
    { label: "Balance", key: "balance", type: "slot", slot: "balance", align: "left" },
    { label: "Charges", key: "charges", type: "slot", slot: "charges", align: "left" },
];

const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);

const hasActiveFilters = computed(() => activeFilterCount.value > 0);

const resultSummary = computed(() => {
    const total = props.billingData?.records?.total ?? props.billingData?.records?.data?.length ?? 0;
    const from = props.billingData?.records?.from ?? (total ? 1 : 0);
    const to = props.billingData?.records?.to ?? total;

    if (!total) {
        return "No billing records found";
    }

    return `Showing ${from}-${to} of ${total} billing records`;
});

const totals = computed(() => props.billingData?.totals || { records: 0, balance: 0, charges: 0 });

const buildQuery = (overrides = {}) => {
    const params = new URLSearchParams(window.location.search);
    const query = {
        per_page: params.get("per_page") || undefined,
        sort: params.get("sort") || undefined,
        direction: params.get("direction") || undefined,
        type: currentTab.value,
        ...filterForm.value,
        ...overrides,
    };

    return Object.fromEntries(
        Object.entries(query).filter(([, value]) => value !== "" && value !== null && value !== undefined)
    );
};

const applyFilters = () => {
    router.get(route("doctor.billing.index"), buildQuery({ page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value.keyword = "";

    router.get(route("doctor.billing.index"), buildQuery({ keyword: undefined, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("doctor.billing.index"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updateCurrentTab = (newTab) => {
    currentTab.value = newTab;
    router.get(route("doctor.billing.index"), buildQuery({ type: newTab, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const openEditBillingNotesModal = () => {
    isEditModalOpen.value = true;
};

const closeEditBillingNotesModal = () => {
    isEditModalOpen.value = false;
};

const editBillingNotes = () => {
    form.post(route("doctor.billing.notes.update"), {
        onSuccess: () => closeEditBillingNotesModal(),
    });
};

const openAddPaymentModal = (row) => {
    selectedRow.value = row;
    isAddPaymentModal.value = true;
};

const closeAddPaymentModal = () => {
    isAddPaymentModal.value = false;
    selectedRow.value = null;
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
    }).format(amount || 0);
};
</script>

<template>
    <AuthLayout title="Billing" description="Billing" heading="Billing">
        <div v-if="!selectedPatientId || billingData.error" class="alert alert-warning text-center py-4">
            <h5><i class="bi bi-exclamation-triangle-fill me-2"></i>No Patient Selected</h5>
            <p class="mb-0">Please select a patient from the patient list to view billing records.</p>
        </div>

        <template v-else>
            <div class="row g-4">
                <div class="col-12 col-xl-3">
                    <div class="card border-0 shadow-sm billing-side-card">
                        <div class="card-body">
                            <div class="finance-menu">
                                <button v-for="tab in tabs" :key="tab.value" type="button" class="menu-item"
                                    :class="{ active: currentTab === tab.value }" @click="updateCurrentTab(tab.value)">
                                    <span class="dot" :class="tab.dotClass"></span>
                                    <span class="label">{{ tab.label }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-9">
                    <div class="users-toolbar card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <div
                                class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                                <div>
                                    <h3 class="mb-1">Billing</h3>
                                    <p class="text-muted mb-0">{{ resultSummary }}</p>
                                </div>

                                <div class="d-flex align-items-center gap-2 flex-wrap justify-content-lg-end">
                                    <span v-if="hasActiveFilters" class="filter-count-badge">
                                        {{ activeFilterCount }} filter{{ activeFilterCount > 1 ? "s" : "" }} active
                                    </span>
                                    <button v-if="hasActiveFilters" type="button"
                                        class="btn btn-outline-secondary btn-sm" @click="clearFilters">
                                        <i class="bi bi-x-circle me-1"></i>Clear filters
                                    </button>

                                    <button class="btn btn-primary" @click="openEditBillingNotesModal">
                                        <i class="fa-solid fa-pen-to-square me-1"></i>Edit Billing Notes
                                    </button>
                                </div>
                            </div>

                            <div class="row g-3 align-items-end">
                                <div class="col-12 col-sm-6 col-xl-4">
                                    <label class="form-label text-muted small text-uppercase mb-2">Search</label>
                                    <div class="input-group billing-search-control">
                                        <span
                                            class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                            <i class="bi bi-search text-muted"></i>
                                        </span>
                                        <input v-model="filterForm.keyword" type="search"
                                            class="form-control border-start-0" placeholder="Search billing"
                                            @keydown.enter.prevent="applyFilters" />
                                        <button type="button" class="btn btn-primary"
                                            @click="applyFilters">Search</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light border-0 shadow-sm">
                                <div class="card-body">
                                    <h6 class="card-title">Total Records</h6>
                                    <h4 class="text-primary mb-0">{{ totals.records }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light border-0 shadow-sm">
                                <div class="card-body">
                                    <h6 class="card-title">Total Balance</h6>
                                    <h4 class="text-warning mb-0">{{ formatCurrency(totals.balance) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light border-0 shadow-sm">
                                <div class="card-body">
                                    <h6 class="card-title">Total Charges</h6>
                                    <h4 class="text-success mb-0">{{ formatCurrency(totals.charges) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="notes?.billing_note" class="alert alert-success rounded shadow-sm">
                        <strong>Billing Notes:</strong> {{ notes.billing_note }}
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0 p-md-3">
                            <div class="d-flex justify-content-end align-items-center px-3 px-md-0 pt-3 pt-md-0 pb-2">
                                <div class="d-flex align-items-center gap-2 rows-select-wrap">
                                    <label for="billing-per-page"
                                        class="text-muted small text-uppercase mb-0">Rows</label>
                                    <select id="billing-per-page" v-model="perPage"
                                        class="form-select form-select-sm top-page-select" @change="updatePerPage">
                                        <option v-for="option in perPageOptions" :key="option" :value="option">
                                            {{ option }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <Table :columns="columns" :data="billingData.records" :search-show="false"
                                :PageOptions="false">
                                <template #date="{ row }">
                                    <div class="text-start">
                                        <div class="fw-medium text-dark">{{ row.date || "-" }}</div>
                                    </div>
                                </template>

                                <template #reason="{ row }">
                                    <div class="text-start">
                                        <div class="fw-semibold text-dark">{{ row.reason || "-" }}</div>
                                        <div v-if="row.provider" class="text-muted small">{{ row.provider }}</div>
                                    </div>
                                </template>

                                <template #balance="{ row }">
                                    <span :class="{ 'text-danger': row.balance < 0, 'text-success': row.balance > 0 }">
                                        {{ formatCurrency(row.balance) }}
                                    </span>
                                </template>

                                <template #charges="{ row }">
                                    <span class="text-success">
                                        {{ formatCurrency(row.charges) }}
                                    </span>
                                </template>

                                <template #actions="{ row }">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <Link
                                            :href="route('doctor.billing_payment_history', { id: row.encounter_id || row.id })"
                                            class="btn btn-outline-primary action-btn" title="Payment History">
                                            <i class="fa fa-history"></i>
                                        </Link>

                                        <button class="btn btn-outline-success action-btn"
                                            @click="openAddPaymentModal(row)" title="Add Payment">
                                            <i class="fa fa-usd"></i>
                                        </button>
                                    </div>
                                </template>
                            </Table>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <Modal :isOpen="isAddPaymentModal" title="Add Payment" @close="closeAddPaymentModal" size="lg">
            <AddPaymentModal v-if="isAddPaymentModal" :billingData="selectedRow" :record-type="currentTab"
                @close="closeAddPaymentModal" @success="closeAddPaymentModal" />
        </Modal>

        <Modal :isOpen="isEditModalOpen" title="Edit Billing Notes" @close="closeEditBillingNotesModal">
            <EditBillingNotesModal @close="closeEditBillingNotesModal" :form="form" @submit="editBillingNotes" />
        </Modal>
    </AuthLayout>
</template>

<style scoped>
.users-toolbar,
.billing-side-card {
    border-radius: 20px;
}

.finance-menu {
    display: flex;
    flex-direction: column;
    gap: 0.625rem;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    width: 100%;
    padding: 0.85rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    background: #fff;
    color: #334155;
    text-align: left;
    transition: all 0.2s ease;
}

.menu-item.active {
    background: #eff6ff;
    border-color: #93c5fd;
    color: #1d4ed8;
}

.label {
    font-weight: 600;
    font-size: 0.95rem;
}

.dot {
    width: 10px;
    height: 10px;
    border-radius: 999px;
    flex-shrink: 0;
}

.dot-success {
    background: #22c55e;
}

.dot-warning {
    background: #f59e0b;
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

.billing-search-control {
    max-width: 100%;
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
</style>
