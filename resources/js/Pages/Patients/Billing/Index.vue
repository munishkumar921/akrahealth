<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { computed, ref } from "vue";
import { Link, router } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";

const props = defineProps({
    invoices: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
    summary: {
        type: Object,
        default: () => ({
            records: 0,
            balance: 0,
            charges: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    payment_status: props.filters?.payment_status || "",
});

const paymentStatusOptions = [
    { value: "", label: "All payment status" },
    { value: "pending", label: "Pending" },
    { value: "paid", label: "Paid" },
    { value: "partial", label: "Partial" },
    { value: "failed", label: "Failed" },
];

const columns = [
    { label: "Invoice", key: "invoice_number", type: "slot", slot: "invoice", align: "left" },
    { label: "Doctor", key: "doctor_name", type: "slot", slot: "doctor", align: "left" },
    { label: "Appointment Date", key: "appointment_date", type: "slot", slot: "appointment_date", align: "left" },
    { label: "Fee", key: "fee_amount", type: "slot", slot: "fee", align: "left" },
    { label: "Discount", key: "discount_amount", type: "slot", slot: "discount", align: "left" },
    { label: "Tax", key: "tax_amount_label", type: "slot", slot: "tax", align: "left" },
    { label: "Amount Paid", key: "total_amount", type: "slot", slot: "amount_paid", align: "left" },
    { label: "Payment Status", key: "payment_status", type: "slot", slot: "payment_status", align: "center" },
];

const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);

const hasActiveFilters = computed(() => activeFilterCount.value > 0);

const resultSummary = computed(() => {
    const total = props.invoices?.total ?? props.invoices?.data?.length ?? 0;
    const from = props.invoices?.from ?? (total ? 1 : 0);
    const to = props.invoices?.to ?? total;

    if (!total) {
        return "No billing records found";
    }

    return `Showing ${from}-${to} of ${total} billing records`;
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
    }).format(amount || 0);
};

const totals = computed(() => {
    return {
        records: Number(props.summary?.records || 0),
        balance: Number(props.summary?.balance || 0),
        charges: Number(props.summary?.charges || 0),
    };
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
    router.get(route("patient.billing"), buildQuery({ page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        payment_status: "",
    };

    router.get(route("patient.billing"), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("patient.billing"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const paymentStatusClass = (status) => {
    switch ((status || "").toLowerCase()) {
        case "paid":
            return "status-pill status-pill--paid";
        case "partial":
            return "status-pill status-pill--partial";
        case "failed":
            return "status-pill status-pill--failed";
        default:
            return "status-pill status-pill--pending";
    }
};
</script>

<template>
    <AuthLayout title="Billing" description="Billing" heading="Billing">
        <div class="users-toolbar card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                    <div>
                        <h3 class="mb-1">Billing</h3>
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
                        <div class="input-group billing-search-control">
                            <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input
                                v-model="filterForm.keyword"
                                type="search"
                                class="form-control border-start-0"
                                placeholder="Search billing"
                                @keydown.enter.prevent="applyFilters"
                            />
                            <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <label class="form-label text-muted small text-uppercase mb-2">Payment Status</label>
                        <select v-model="filterForm.payment_status" class="form-select" @change="applyFilters">
                            <option v-for="option in paymentStatusOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
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
                        <h6 class="card-title">Total Billed</h6>
                        <h4 class="text-warning mb-0">{{ formatCurrency(totals.balance) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">Total Fee</h6>
                        <h4 class="text-success mb-0">{{ formatCurrency(totals.charges) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0 p-md-3">
                <div class="d-flex justify-content-end align-items-center px-3 px-md-0 pt-3 pt-md-0 pb-2">
                    <div class="d-flex align-items-center gap-2 rows-select-wrap">
                        <label for="billing-per-page" class="text-muted small text-uppercase mb-0">Rows</label>
                        <select
                            id="billing-per-page"
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

                <Table :columns="columns" :data="invoices" :search-show="false" :PageOptions="false">
                    <template #invoice="{ row }">
                        <div class="text-start">
                            <div class="fw-semibold text-dark">{{ row.invoice_number || "-" }}</div>
                        </div>
                    </template>

                    <template #doctor="{ row }">
                        <div class="text-start">
                            <div class="fw-medium text-dark">{{ row.doctor_name || "-" }}</div>
                        </div>
                    </template>

                    <template #appointment_date="{ row }">
                        <div class="text-start">
                            <div class="fw-medium text-dark">{{ row.appointment_date_label || row.appointment_date || "-" }}</div>
                        </div>
                    </template>

                    <template #fee="{ row }">
                        <span class="fw-medium text-dark">{{ formatCurrency(row.fee_amount) }}</span>
                    </template>

                    <template #discount="{ row }">
                        <span class="fw-medium text-dark">{{ formatCurrency(row.discount_amount) }}</span>
                    </template>

                    <template #tax="{ row }">
                        <span class="fw-medium text-dark">{{ formatCurrency(row.tax_amount_label) }}</span>
                    </template>

                    <template #amount_paid="{ row }">
                        <span class="fw-medium text-dark">{{ formatCurrency(row.total_amount) }}</span>
                    </template>

                    <template #payment_status="{ row }">
                        <div class="d-flex justify-content-center">
                            <span :class="paymentStatusClass(row.payment_status)">{{ row.payment_status_label || row.payment_status || "-" }}</span>
                        </div>
                    </template>

                    <template #actions="{ row }">
                        <div class="d-flex gap-2 justify-content-end">
                            <Link
                                v-if="row?.encounter?.id"
                                :href="route('patient.billing_payment_history', row.encounter.id)"
                                class="btn btn-outline-primary action-btn"
                                title="Payment History"
                            >
                                <i class="fa fa-history"></i>
                            </Link>

                            <a
                                v-if="row.payment_status === 'pending'"
                                :href="route('patient.appointment.payment', row.id)"
                                class="btn btn-outline-success action-btn"
                                title="Add Payment"
                            >
                                <i class="fa fa-usd"></i>
                            </a>
                            <button v-else class="btn btn-outline-secondary action-btn" disabled title="Payment Completed">
                                <i class="fa fa-check"></i>
                            </button>

                            <a
                                v-if="row?.invoice?.invoice_number"
                                class="btn btn-outline-primary action-btn"
                                :href="route('patient.billing.print', { id: row.id })"
                                title="Print"
                                target="_blank"
                            >
                                <i class="fa fa-print"></i>
                            </a>
                        </div>
                    </template>
                </Table>
            </div>
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

.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 90px;
    padding: 0.45rem 0.8rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: capitalize;
}

.status-pill--paid {
    background: #dcfce7;
    color: #166534;
}

.status-pill--partial {
    background: #fef3c7;
    color: #92400e;
}

.status-pill--failed {
    background: #fee2e2;
    color: #991b1b;
}

.status-pill--pending {
    background: #e2e8f0;
    color: #475569;
}
</style>
