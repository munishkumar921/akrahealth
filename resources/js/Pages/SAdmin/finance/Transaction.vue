<script setup>
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import jsPDF from "jspdf";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Modal from "@/Components/Common/Modal.vue";
import Table from "@/Components/Table/Table.vue";

const props = defineProps({
    transactions: Object,
    metrics: Object,
    filters: {
        type: Object,
        default: () => ({}),
    },
    currencies: Array,
    plans: Array,
    frequencies: Array,
    statuses: Array,
    paymentStatuses: Array,
});

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    gateway: props.filters?.gateway || "",
    status: props.filters?.status || "",
    payment_status: props.filters?.payment_status || "",
    currency: props.filters?.currency || "",
    plan_id: props.filters?.plan_id || "",
    frequency: props.filters?.frequency || "",
    date_from: props.filters?.date_from || "",
    date_to: props.filters?.date_to || "",
});

const selectedTransaction = ref(null);
const isDetailModalOpen = ref(false);

const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);
const rows = computed(() => props.transactions?.data ?? []);

const resultSummary = computed(() => {
    const total = props.transactions?.total ?? rows.value.length;
    const from = props.transactions?.from ?? (rows.value.length ? 1 : 0);
    const to = props.transactions?.to ?? rows.value.length;

    if (!total) return "No transactions found";

    return `Showing ${from}-${to} of ${total} transactions`;
});

const formatNumber = (value) => Number(value || 0).toLocaleString("en-IN");

const metricCards = computed(() => [
    {
        label: "Transactions",
        value: formatNumber(props.metrics?.total_transactions ?? 0),
        helper: "All filtered transactions",
        icon: "fa-solid fa-receipt",
        tone: "tone-blue",
    },
    {
        label: "Successful",
        value: formatNumber(props.metrics?.successful_transactions ?? 0),
        helper: "Paid transactions in the result set",
        icon: "fa-solid fa-circle-check",
        tone: "tone-green",
    },
    {
        label: "Revenue",
        value: Number(props.metrics?.total_revenue ?? 0).toLocaleString("en-IN", {
            style: "currency",
            currency: "INR",
            maximumFractionDigits: 2,
        }),
        helper: "Total paid amount",
        icon: "fa-solid fa-wallet",
        tone: "tone-indigo",
    },
    {
        label: "Average Ticket",
        value: Number(props.metrics?.average_ticket ?? 0).toLocaleString("en-IN", {
            style: "currency",
            currency: "INR",
            maximumFractionDigits: 2,
        }),
        helper: "Average value of paid transactions",
        icon: "fa-solid fa-chart-line",
        tone: "tone-amber",
    },
]);

const columns = [
    { label: "User", key: "user", type: "slot", slot: "user", align: "left" },
    { label: "Plan", key: "plan_name", align: "left" },
    { label: "Gateway", key: "gateway", type: "slot", slot: "gateway" },
    { label: "Amount", key: "amount", type: "slot", slot: "amount", align: "left" },
    { label: "Status", key: "status", type: "slot", slot: "status" },
    { label: "Payment", key: "payment_status", type: "slot", slot: "payment_status" },
    { label: "Created", key: "created_on", type: "slot", slot: "created_on", align: "left" },
];

const buildQuery = () => {
    const params = new URLSearchParams(window.location.search);

    return Object.fromEntries(
        Object.entries({
            per_page: params.get("per_page") || undefined,
            sort: params.get("sort") || undefined,
            direction: params.get("direction") || undefined,
            ...filterForm.value,
        }).filter(([, value]) => value !== "" && value !== null && value !== undefined)
    );
};

const applyFilters = () => {
    router.get(route("superAdmin.transaction"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        gateway: "",
        status: "",
        payment_status: "",
        currency: "",
        plan_id: "",
        frequency: "",
        date_from: "",
        date_to: "",
    };

    router.get(route("superAdmin.transaction"), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const openTransaction = (row) => {
    selectedTransaction.value = row;
    isDetailModalOpen.value = true;
};

const closeModal = () => {
    selectedTransaction.value = null;
    isDetailModalOpen.value = false;
};

const downloadTransactionPDF = () => {
    if (!selectedTransaction.value) return;

    const doc = new jsPDF();
    let y = 20;

    doc.setFontSize(18);
    doc.setFont(undefined, "bold");
    doc.text("Transaction Summary", 105, y, { align: "center" });
    y += 14;

    doc.setDrawColor(220);
    doc.line(16, y, 194, y);
    y += 12;

    const details = [
        ["User", selectedTransaction.value.user],
        ["Email", selectedTransaction.value.email],
        ["Mobile", selectedTransaction.value.mobile],
        ["Plan", selectedTransaction.value.plan_name],
        ["Amount", selectedTransaction.value.amount_label],
        ["Gateway", selectedTransaction.value.gateway_label],
        ["Subscription Status", selectedTransaction.value.status],
        ["Payment Status", selectedTransaction.value.payment_status],
        ["Reference", selectedTransaction.value.order_id],
        ["Created", selectedTransaction.value.created_label],
        ["Subscribed On", selectedTransaction.value.subscribed_on],
        ["Frequency", selectedTransaction.value.frequency],
    ];

    doc.setFontSize(11);
    details.forEach(([label, value]) => {
        doc.setFont(undefined, "bold");
        doc.text(`${label}:`, 20, y);
        doc.setFont(undefined, "normal");
        doc.text(String(value || "N/A"), 72, y);
        y += 8;
    });

    doc.save(`transaction_${selectedTransaction.value.order_id}.pdf`);
};
</script>

<template>
    <AuthLayout title="Transactions" description="Platform transaction management">
        <section class="transaction-page">

            <div class="border-0 shadow-sm filter-card">
                <div class="card-body">
                    <div class="filter-header">
                        <div>
                            <p class="filter-kicker">Filters</p>
                            <h3 class="filter-title">Refine transaction activity</h3>
                        </div>
                        <div v-if="hasActiveFilters" class="filter-tools">
                            <span class="filter-badge">
                                {{ activeFilterCount }} filter{{ activeFilterCount > 1 ? "s" : "" }} active
                            </span>
                            <button type="button" class="btn btn-outline-secondary btn-sm" @click="clearFilters">
                                <i class="bi bi-x-circle me-1"></i> Clear filters
                            </button>
                        </div>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-xl-4">
                            <label class="form-label text-muted small text-uppercase mb-2">Search</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 border rounded-circle-left">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input
                                    v-model="filterForm.keyword"
                                    type="search"
                                    class="form-control border-start-0"
                                    placeholder="Search user, email, plan, or reference"
                                    @keydown.enter.prevent="applyFilters"
                                />
                                <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Gateway</label>
                            <select v-model="filterForm.gateway" class="form-select" @change="applyFilters">
                                <option value="">All gateways</option>
                                <option value="razorpay">Razorpay</option>
                                <option value="manual">Manual</option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Status</label>
                            <select v-model="filterForm.status" class="form-select" @change="applyFilters">
                                <option value="">All statuses</option>
                                <option v-for="status in statuses" :key="status" :value="status">
                                    {{ status }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Payment</label>
                            <select v-model="filterForm.payment_status" class="form-select" @change="applyFilters">
                                <option value="">All payment states</option>
                                <option v-for="status in paymentStatuses" :key="status" :value="status">
                                    {{ status }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Currency</label>
                            <select v-model="filterForm.currency" class="form-select" @change="applyFilters">
                                <option value="">All currencies</option>
                                <option v-for="currency in currencies" :key="currency" :value="currency">
                                    {{ currency }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-3">
                            <label class="form-label text-muted small text-uppercase mb-2">Plan</label>
                            <select v-model="filterForm.plan_id" class="form-select" @change="applyFilters">
                                <option value="">All plans</option>
                                <option v-for="plan in plans" :key="plan.id" :value="plan.id">
                                    {{ plan.title }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Frequency</label>
                            <select v-model="filterForm.frequency" class="form-select" @change="applyFilters">
                                <option value="">All frequencies</option>
                                <option v-for="frequency in frequencies" :key="frequency" :value="frequency">
                                    {{ frequency }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">From date</label>
                            <input v-model="filterForm.date_from" type="date" class="form-control" @change="applyFilters" />
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">To date</label>
                            <input v-model="filterForm.date_to" type="date" class="form-control" @change="applyFilters" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm table-shell">
                <div class="card-body p-0 p-md-3">
                    <Table :columns="columns" :data="transactions" table="superadmin-transactions" :search-show="false">
                        <template #user="{ row }">
                            <div class="user-cell">
                                <img :src="row.profile_photo_url || '/images/avatar.webp'" alt="user" class="user-avatar" />
                                <div>
                                    <div class="user-name">{{ row.user }}</div>
                                    <div class="user-meta">{{ row.email }}</div>
                                </div>
                            </div>
                        </template>

                        <template #gateway="{ row }">
                            <span class="pill pill--gateway">{{ row.gateway_label }}</span>
                        </template>

                        <template #amount="{ row }">
                            <div class="text-start">
                                <div class="fw-semibold text-dark">{{ row.amount_label }}</div>
                                <div class="text-muted small">{{ row.frequency }} plan</div>
                            </div>
                        </template>

                        <template #status="{ row }">
                            <span
                                class="pill"
                                :class="row.status === 'Active' ? 'pill--success' : row.status === 'Cancelled' ? 'pill--danger' : 'pill--neutral'"
                            >
                                {{ row.status }}
                            </span>
                        </template>

                        <template #payment_status="{ row }">
                            <span
                                class="pill"
                                :class="row.payment_status === 'Paid' ? 'pill--success' : row.payment_status === 'Pending' ? 'pill--warning' : 'pill--neutral'"
                            >
                                {{ row.payment_status }}
                            </span>
                        </template>

                        <template #created_on="{ row }">
                            <div class="text-start">
                                <div class="fw-semibold text-dark">{{ row.created_label }}</div>
                                <div class="text-muted small">{{ row.order_id }}</div>
                            </div>
                        </template>

                        <template #actions="{ row }">
                            <div class="d-flex justify-content-center">
                                <button class="icon-btn icon-btn--view" @click="openTransaction(row)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </template>
                    </Table>
                </div>
            </div>
        </section>

        <Modal :isOpen="isDetailModalOpen" title="Transaction Details" @close="closeModal" size="lg">
            <div v-if="selectedTransaction" class="detail-grid">
                <div class="detail-item">
                    <label>User</label>
                    <span>{{ selectedTransaction.user }}</span>
                </div>
                <div class="detail-item">
                    <label>Email</label>
                    <span>{{ selectedTransaction.email }}</span>
                </div>
                <div class="detail-item">
                    <label>Mobile</label>
                    <span>{{ selectedTransaction.mobile }}</span>
                </div>
                <div class="detail-item">
                    <label>Plan</label>
                    <span>{{ selectedTransaction.plan_name }}</span>
                </div>
                <div class="detail-item">
                    <label>Amount</label>
                    <span>{{ selectedTransaction.amount_label }}</span>
                </div>
                <div class="detail-item">
                    <label>Gateway</label>
                    <span>{{ selectedTransaction.gateway_label }}</span>
                </div>
                <div class="detail-item">
                    <label>Subscription Status</label>
                    <span>{{ selectedTransaction.status }}</span>
                </div>
                <div class="detail-item">
                    <label>Payment Status</label>
                    <span>{{ selectedTransaction.payment_status }}</span>
                </div>
                <div class="detail-item detail-item--wide">
                    <label>Reference</label>
                    <span>{{ selectedTransaction.order_id }}</span>
                </div>
                <div class="detail-item">
                    <label>Created</label>
                    <span>{{ selectedTransaction.created_label }}</span>
                </div>
                <div class="detail-item">
                    <label>Subscribed On</label>
                    <span>{{ selectedTransaction.subscribed_on }}</span>
                </div>
                <div class="detail-item">
                    <label>Frequency</label>
                    <span>{{ selectedTransaction.frequency }}</span>
                </div>
            </div>

            <template #footer>
                <button type="button" class="btn btn-success" @click="downloadTransactionPDF">
                    <i class="bi bi-download me-1"></i> Download PDF
                </button>
                <button type="button" class="btn btn-danger" @click="closeModal">Close</button>
            </template>
        </Modal>
    </AuthLayout>
</template>

<style scoped>
.transaction-page {
    display: grid;
    gap: 20px;
}

.list-hero {
    display: flex;
    justify-content: space-between;
    gap: 18px;
    align-items: flex-start;
    padding: 28px;
    border-radius: 24px;
    background: linear-gradient(135deg, #f8fcff 0%, #eef7ff 48%, #ffffff 100%);
    border: 1px solid rgba(18, 148, 234, 0.1);
}

.hero-kicker,
.filter-kicker {
    margin: 0 0 8px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #1294ea;
}

.hero-title {
    margin: 0 0 10px;
    font-size: 34px;
    font-weight: 700;
    color: #0f172a;
}

.hero-copy {
    margin: 0;
    color: #64748b;
}

.hero-copy--muted {
    margin-top: 8px;
}

.hero-actions,
.filter-tools {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.filter-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.45rem 0.75rem;
    border-radius: 999px;
    background: #eef2ff;
    color: #3730a3;
    font-size: 0.8rem;
    font-weight: 600;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
}

.metric-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 22px 24px;
    border-radius: 22px;
    background: #fff;
    border: 1px solid #e2e8f0;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.metric-label {
    margin: 0 0 8px;
    color: #64748b;
    font-size: 0.82rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.metric-value {
    margin: 0;
    color: #0f172a;
    font-size: 1.9rem;
    font-weight: 700;
}

.metric-helper {
    margin: 8px 0 0;
    color: #64748b;
    font-size: 0.92rem;
}

.metric-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 54px;
    height: 54px;
    border-radius: 18px;
    font-size: 1.35rem;
}

.tone-blue .metric-icon {
    background: #e0f2fe;
    color: #0369a1;
}

.tone-green .metric-icon {
    background: #dcfce7;
    color: #15803d;
}

.tone-indigo .metric-icon {
    background: #e0e7ff;
    color: #4338ca;
}

.tone-amber .metric-icon {
    background: #ffedd5;
    color: #c2410c;
}

.filter-card,
.table-shell {
    border-radius: 22px;
}

.filter-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 18px;
}

.filter-title {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 700;
    color: #0f172a;
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 14px;
    text-align: left;
}

.user-avatar {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    object-fit: cover;
    border: 1px solid #e5e7eb;
    background: #f8fafc;
}

.user-name {
    font-weight: 700;
    color: #0f172a;
}

.user-meta {
    color: #475569;
    font-size: 0.85rem;
}

.pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.38rem 0.75rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
}

.pill--success {
    background: #dcfce7;
    color: #166534;
}

.pill--warning {
    background: #fef3c7;
    color: #92400e;
}

.pill--danger {
    background: #fee2e2;
    color: #b91c1c;
}

.pill--neutral,
.pill--gateway {
    background: #f1f5f9;
    color: #475569;
}

.icon-btn {
    width: 34px;
    height: 34px;
    border-radius: 12px;
    border: 1px solid #dbe4f0;
    background: #fff;
    color: #334155;
}

.icon-btn--view:hover {
    background: #eff6ff;
    color: #1d4ed8;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.detail-item {
    padding: 14px 16px;
    border-radius: 16px;
    background: #f8fbff;
    border: 1px solid #e2e8f0;
}

.detail-item--wide {
    grid-column: 1 / -1;
}

.detail-item label {
    display: block;
    margin-bottom: 6px;
    color: #64748b;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.detail-item span {
    color: #0f172a;
    font-weight: 500;
}

@media (max-width: 991px) {
    .list-hero,
    .filter-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .hero-actions,
    .filter-tools {
        justify-content: flex-start;
    }

    .metrics-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    .metrics-grid,
    .detail-grid {
        grid-template-columns: 1fr;
    }
}
</style>
