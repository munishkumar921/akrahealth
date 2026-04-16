<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import { computed, ref } from "vue";
import { route } from "ziggy-js";

const props = defineProps({
    transactions: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({
            keyword: "",
            type: "all",
            status: "",
        }),
    },
});

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    type: props.filters?.type || "all",
    status: props.filters?.status || "",
});

const transactionTypes = [
    { value: "all", label: "All types" },
    { value: "subscription", label: "Subscription" },
    { value: "lab_order", label: "Lab Order" },
    { value: "pharmacy_order", label: "Pharmacy Order" },
    { value: "invoice", label: "Invoice" },
    { value: "appointment", label: "Appointment" },
];

const statusOptions = [
    { value: "", label: "All status" },
    { value: "pending", label: "Pending" },
    { value: "completed", label: "Completed" },
    { value: "active", label: "Active" },
    { value: "failed", label: "Failed" },
    { value: "refunded", label: "Refunded" },
    { value: "cancelled", label: "Cancelled" },
    { value: "trial", label: "Trial" },
];

const columns = [
    { label: "Order ID", key: "order_id", type: "slot", slot: "order", align: "left" },
    { label: "Payment ID", key: "payment_id", type: "slot", slot: "paymentId", align: "left" },
    { label: "Type", key: "type_label", type: "slot", slot: "type", align: "left" },
    { label: "User", key: "user", type: "slot", slot: "user", align: "left" },
    { label: "Description", key: "description", type: "slot", slot: "description", align: "left" },
    { label: "Amount", key: "amount", type: "slot", slot: "amount", align: "left" },
    { label: "Status", key: "status", type: "slot", slot: "status", align: "center" },
    { label: "Created", key: "created_at", type: "slot", slot: "created", align: "left" },
];

const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);
const rows = computed(() => props.transactions?.data ?? []);
const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "" && value !== "all").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);
const resultSummary = computed(() => {
    const total = props.transactions?.total ?? rows.value.length;
    const from = props.transactions?.from ?? (rows.value.length ? 1 : 0);
    const to = props.transactions?.to ?? rows.value.length;

    if (!total) {
        return "No transactions found";
    }

    return `Showing ${from}-${to} of ${total} transactions`;
});

const transactionSummary = computed(() => {
    const data = rows.value;

    return {
        total: props.transactions?.total ?? data.length,
        completed: data.filter((item) => ["completed", "active"].includes(String(item.status).toLowerCase())).length,
        pending: data.filter((item) => String(item.status).toLowerCase() === "pending").length,
        failed: data.filter((item) => String(item.status).toLowerCase() === "failed").length,
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
    router.get(route("admin.transaction.list"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        type: "all",
        status: "",
    };

    router.get(route("admin.transaction.list"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("admin.transaction.list"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const getStatusClass = (status) => {
    const value = String(status || "").toLowerCase();

    if (["active", "completed"].includes(value)) return "status-pill--active";
    if (value === "pending" || value === "trial") return "status-pill--pending";

    return "status-pill--inactive";
};

const getTypeClass = (type) => {
    const value = String(type || "").toLowerCase();

    if (value === "subscription") return "badge-soft-primary";
    if (value === "lab_order") return "badge-soft-info";
    if (value === "pharmacy_order") return "badge-soft-warning";
    if (value === "invoice") return "badge-soft-dark";

    return "badge-soft-secondary";
};

const formatAmount = (row) => {
    const amount = Number(row.amount || 0).toFixed(2);
    const symbol = row.currency === "USD" ? "$" : row.currency === "EUR" ? "EUR " : row.currency === "GBP" ? "GBP " : "₹";

    return `${symbol}${amount}`;
};
</script>

<template>
    <AuthLayout title="Razorpay Transactions" description="All Razorpay Payment Transactions"
        heading="Razorpay Transactions">
        <div class="transactions-page">
            <div class="users-toolbar card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div
                        class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                        <div>
                            <h3 class="mb-1">Transactions</h3>
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
                        </div>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-xl-4">
                            <label class="form-label text-muted small text-uppercase mb-2">Search</label>
                            <div class="input-group transaction-search-control">
                                <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input v-model="filterForm.keyword" type="search" class="form-control border-start-0"
                                    placeholder="Search by order ID, payment ID, user, or email"
                                    @keydown.enter.prevent="applyFilters" />
                                <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-3">
                            <label class="form-label text-muted small text-uppercase mb-2">Type</label>
                            <select v-model="filterForm.type" class="form-select" @change="applyFilters">
                                <option v-for="type in transactionTypes" :key="type.value" :value="type.value">
                                    {{ type.label }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Status</label>
                            <select v-model="filterForm.status" class="form-select" @change="applyFilters">
                                <option v-for="status in statusOptions" :key="status.value" :value="status.value">
                                    {{ status.label }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4 g-3">
                <div class="col-md-3">
                    <div class="card summary-card summary-card--primary text-white">
                        <div class="card-body">
                            <h5 class="card-title text-white">Total Transactions</h5>
                            <p class="card-text h3 mb-0 text-white">{{ transactionSummary.total }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card summary-card summary-card--success text-white">
                        <div class="card-body">
                            <h5 class="card-title text-white">Completed</h5>
                            <p class="card-text h3 mb-0 text-white">{{ transactionSummary.completed }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card summary-card summary-card--warning text-white">
                        <div class="card-body">
                            <h5 class="card-title text-white">Pending</h5>
                            <p class="card-text h3 mb-0 text-white">{{ transactionSummary.pending }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card summary-card summary-card--danger text-white">
                        <div class="card-body">
                            <h5 class="card-title text-white">Failed</h5>
                            <p class="card-text h3 mb-0 text-white">{{ transactionSummary.failed }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0 p-md-3">
                    <div class="d-flex justify-content-end align-items-center px-3 px-md-0 pt-3 pt-md-0 pb-2">
                        <div class="d-flex align-items-center gap-2 rows-select-wrap">
                            <select id="transactions-per-page" v-model="perPage"
                                class="form-select form-select-sm top-page-select" @change="updatePerPage">
                                <option v-for="option in perPageOptions" :key="option" :value="option">
                                    {{ option }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <Table :columns="columns" :data="transactions" :search-show="false" :PageOptions="false">
                        <template #order="{ row }">
                            <div class="text-start">
                                <div class="fw-semibold text-dark">{{ row.order_id || "N/A" }}</div>
                            </div>
                        </template>

                        <template #paymentId="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.payment_id || "N/A" }}</div>
                            </div>
                        </template>

                        <template #type="{ row }">
                            <span class="soft-badge" :class="getTypeClass(row.type)">
                                {{ row.type_label || "N/A" }}
                            </span>
                        </template>

                        <template #user="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.user || "N/A" }}</div>
                                <div class="text-muted small">{{ row.user_email || "N/A" }}</div>
                            </div>
                        </template>

                        <template #description="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.description || "N/A" }}</div>
                            </div>
                        </template>

                        <template #amount="{ row }">
                            <div class="text-start">
                                <div class="fw-semibold text-success">{{ formatAmount(row) }}</div>
                                <div class="text-muted small">{{ row.currency || "INR" }}</div>
                            </div>
                        </template>

                        <template #status="{ row }">
                            <span class="status-pill" :class="getStatusClass(row.status)">
                                {{ row.payment_status || "N/A" }}
                            </span>
                        </template>

                        <template #created="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.created_at || "N/A" }}</div>
                            </div>
                        </template>
                    </Table>
                </div>
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

.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 84px;
    padding: 0.35rem 0.7rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: capitalize;
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

.soft-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.35rem 0.7rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
}

.badge-soft-primary {
    background: #dbeafe;
    color: #1d4ed8;
}

.badge-soft-info {
    background: #cffafe;
    color: #0f766e;
}

.badge-soft-warning {
    background: #fef3c7;
    color: #92400e;
}

.badge-soft-dark {
    background: #e2e8f0;
    color: #0f172a;
}

.badge-soft-secondary {
    background: #f1f5f9;
    color: #475569;
}

.summary-card {
    border: 0;
    border-radius: 18px;
}

.summary-card--primary {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
}

.summary-card--success {
    background: linear-gradient(135deg, #16a34a, #15803d);
}

.summary-card--warning {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.summary-card--danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
}

.top-page-select {
    min-width: 74px;
    width: 74px;
}

.rows-select-wrap {
    flex: 0 0 auto;
}

.transaction-search-control {
    max-width: 620px;
}
</style>
