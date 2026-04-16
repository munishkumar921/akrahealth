<script setup>
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import AuthLayout2 from "@/Layouts/AuthLayout2.vue";
import Table from "@/Components/Table/Table.vue";
import Modal from "@/Components/Common/Modal.vue";
import AddPaymentModal from "@/Pages/Modals/AddBillingPayment.vue";
import Tab from "./Tab.vue";

const props = defineProps({
    bills: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const currentTab = ref("processed_bills");
const isOpenModal = ref(false);
const selectedRow = ref(null);
const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(
    Number(new URLSearchParams(window.location.search).get("per_page")) || Number(props.bills?.per_page) || 10
);
const filterForm = ref({
    keyword: props.filters?.keyword || "",
});

const columns = [
    { label: "Date", key: "date_of_service_label", type: "slot", slot: "dateOfService", align: "left" },
    { label: "Patient", key: "patient_name", type: "slot", slot: "patientName", align: "left" },
    { label: "Complaint", key: "chief_complaint", type: "slot", slot: "chiefComplaint", align: "left" },
    { label: "Charges", key: "charges_label", type: "slot", slot: "charges", align: "left" },
    { label: "Balance", key: "total_balance_label", type: "slot", slot: "totalBalance", align: "left" },
    { label: "Processed", key: "date_processed_label", type: "slot", slot: "dateProcessed", align: "left" },
];

const openAddPaymentModal = (row) => {
    selectedRow.value = row;
    isOpenModal.value = true;
};

const closeAddPaymentModal = () => {
    isOpenModal.value = false;
    selectedRow.value = null;
};

const buildQuery = (overrides = {}) => {
    const currentRoute = route().current();
    const query = {
        ...filterForm.value,
        per_page: perPage.value,
        ...overrides,
    };

    return {
        currentRoute,
        params: Object.fromEntries(
            Object.entries(query).filter(([, value]) => value !== "" && value !== null && value !== undefined)
        ),
    };
};

const applyFilters = () => {
    const { currentRoute, params } = buildQuery({ page: 1 });
    router.get(route(currentRoute), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
    };

    const currentRoute = route().current();
    router.get(route(currentRoute), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    const { currentRoute, params } = buildQuery({ per_page: perPage.value, page: 1 });
    router.get(route(currentRoute), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);

const hasActiveFilters = computed(() => activeFilterCount.value > 0);

const resultSummary = computed(() => {
    const total = props.bills?.total ?? props.bills?.data?.length ?? 0;
    const from = props.bills?.from ?? (total ? 1 : 0);
    const to = props.bills?.to ?? total;

    if (!total) {
        return "No processed bills found";
    }

    return `Showing ${from}-${to} of ${total} processed bills`;
});
</script>

<template>
    <AuthLayout2 title="Processed Bills" description="View processed bills" heading="Processed Bills">
        <div class="row g-4">
            <Tab :currentTab="currentTab" />

            <div class="col-lg-9">
                <div class="users-toolbar card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                            <div>
                                <h3 class="mb-1">Processed Bills</h3>
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
                            </div>
                        </div>

                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-sm-6 col-xl-4">
                                <label class="form-label text-muted small text-uppercase mb-2">Search</label>
                                <div class="input-group processed-search-control">
                                    <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>
                                    <input
                                        v-model="filterForm.keyword"
                                        type="search"
                                        class="form-control border-start-0"
                                        placeholder="Search patient, complaint, date"
                                        @keydown.enter.prevent="applyFilters"
                                    />
                                    <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0 p-md-3">
                        <div class="d-flex justify-content-end align-items-center px-3 px-md-0 pt-3 pt-md-0 pb-2">
                            <div class="d-flex align-items-center gap-2 rows-select-wrap">
                                <label for="processed-bills-per-page" class="text-muted small text-uppercase mb-0">Rows</label>
                                <select
                                    id="processed-bills-per-page"
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

                        <Table :columns="columns" :data="bills" :search-show="false" :PageOptions="false">
                            <template #dateOfService="{ row }">
                                <div class="text-start">
                                    <div class="fw-medium text-dark">{{ row.date_of_service_label || "-" }}</div>
                                </div>
                            </template>

                            <template #patientName="{ row }">
                                <div class="text-start">
                                    <div class="fw-semibold text-dark">{{ row.patient_name || "-" }}</div>
                                </div>
                            </template>

                            <template #chiefComplaint="{ row }">
                                <div class="text-start">
                                    <div class="fw-medium text-dark">{{ row.chief_complaint || "-" }}</div>
                                </div>
                            </template>

                            <template #charges="{ row }">
                                <div class="text-start">
                                    <div class="fw-medium text-dark">{{ row.charges_label || "-" }}</div>
                                </div>
                            </template>

                            <template #totalBalance="{ row }">
                                <div class="text-start">
                                    <span class="balance-pill" :class="Number(row.total_balance || 0) > 0 ? 'balance-due' : 'balance-clear'">
                                        {{ row.total_balance_label || "-" }}
                                    </span>
                                </div>
                            </template>

                            <template #dateProcessed="{ row }">
                                <div class="text-start">
                                    <div class="fw-medium text-dark">{{ row.date_processed_label || "-" }}</div>
                                </div>
                            </template>

                            <template #actions="{ row }">
                                <div class="d-flex gap-2 justify-content-end">
                                    <button
                                        class="btn btn-primary action-btn"
                                        title="Payment history"
                                        @click="router.get(route('doctor.billing_payment_history', row.id))"
                                    >
                                        <i class="fa fa-history"></i>
                                    </button>
                                    <button
                                        class="btn btn-danger action-btn"
                                        title="Make payment"
                                        @click="openAddPaymentModal(row)"
                                    >
                                        <i class="fa fa-usd"></i>
                                    </button>
                                    <button
                                        class="btn btn-warning action-btn"
                                        title="Resubmit bill"
                                        @click="router.get(route('doctor.finance.financial_resubmit', { id: row.encounter_id }))"
                                    >
                                        <i class="fa fa-repeat"></i>
                                    </button>
                                </div>
                            </template>
                        </Table>
                    </div>
                </div>
            </div>
        </div>

        <Modal :isOpen="isOpenModal" @close="closeAddPaymentModal" title="Add Payment" size="xl">
            <AddPaymentModal
                :billingData="selectedRow"
                :record-type="currentTab"
                @close="closeAddPaymentModal"
                @success="closeAddPaymentModal"
            />
        </Modal>
    </AuthLayout2>
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

.processed-search-control {
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

.balance-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.42rem 0.85rem;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 700;
}

.balance-due {
    background: #fef3c7;
    color: #92400e;
}

.balance-clear {
    background: #dcfce7;
    color: #166534;
}

:deep(.table-responsive) {
    overflow-x: hidden;
}

:deep(.table-responsive table) {
    width: 100%;
    table-layout: fixed;
}

:deep(.table-responsive th),
:deep(.table-responsive td) {
    white-space: normal !important;
    word-break: break-word;
}

:deep(.table-responsive td:last-child) {
    white-space: nowrap !important;
}
</style>
