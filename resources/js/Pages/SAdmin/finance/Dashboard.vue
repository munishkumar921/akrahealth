<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from "vue";
import { router } from "@inertiajs/vue3";
import Chart from "chart.js/auto";
import AuthLayout from "@/Layouts/AuthLayout.vue";

const props = defineProps({
    metrics: Object,
    charts: Object,
    topPlans: Array,
    recentTransactions: Array,
    filters: {
        type: Object,
        default: () => ({}),
    },
    currencies: Array,
    frequencies: Array,
    statuses: Array,
    paymentStatuses: Array,
    rangeLabel: String,
});

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    currency: props.filters?.currency || "",
    frequency: props.filters?.frequency || "",
    status: props.filters?.status || "",
    payment_status: props.filters?.payment_status || "",
    date_from: props.filters?.date_from || "",
    date_to: props.filters?.date_to || "",
});

const formatNumber = (value) => Number(value || 0).toLocaleString("en-IN");

const currencyFormatter = (value, currency = "INR") =>
    new Intl.NumberFormat("en-IN", {
        style: "currency",
        currency: currency || "INR",
        maximumFractionDigits: 2,
    }).format(Number(value || 0));

const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);

const hasActiveFilters = computed(() => activeFilterCount.value > 0);

const summaryCards = computed(() => [
    {
        label: "Revenue",
        value: currencyFormatter(props.metrics?.total_revenue ?? 0),
        helper: `Paid subscriptions for ${props.rangeLabel || "selected range"}`,
        icon: "fa-solid fa-wallet",
        tone: "tone-blue",
    },
    {
        label: "Successful Payments",
        value: formatNumber(props.metrics?.successful_payments ?? 0),
        helper: "Completed collections in the filtered period",
        icon: "fa-solid fa-circle-check",
        tone: "tone-green",
    },
    {
        label: "Active Subscribers",
        value: formatNumber(props.metrics?.active_subscribers ?? 0),
        helper: "Subscribers currently marked active",
        icon: "fa-solid fa-user-group",
        tone: "tone-indigo",
    },
    {
        label: "Pending Collections",
        value: formatNumber(props.metrics?.pending_collections ?? 0),
        helper: "Subscriptions waiting for payment capture",
        icon: "fa-solid fa-hourglass-half",
        tone: "tone-amber",
    },
]);

const signalCards = computed(() => [
    {
        label: "Trial Subscribers",
        value: formatNumber(props.metrics?.trial_subscribers ?? 0),
        helper: "Active trial accounts awaiting first charge",
    },
    {
        label: "Cancelled Subscribers",
        value: formatNumber(props.metrics?.cancelled_subscribers ?? 0),
        helper: "Subscriptions marked cancelled",
    },
]);

const buildQuery = () =>
    Object.fromEntries(
        Object.entries(filterForm.value).filter(([, value]) => value !== "" && value !== null && value !== undefined)
    );

const applyFilters = () => {
    router.get(route("superAdmin.financedashboard"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        currency: "",
        frequency: "",
        status: "",
        payment_status: "",
        date_from: "",
        date_to: "",
    };

    router.get(route("superAdmin.financedashboard"), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

let revenueChart;
let subscriberChart;
let paymentMixChart;

const destroyCharts = () => {
    revenueChart?.destroy();
    subscriberChart?.destroy();
    paymentMixChart?.destroy();
};

onMounted(async () => {
    await nextTick();

    const revenueCanvas = document.getElementById("finance-revenue-chart");
    const subscriberCanvas = document.getElementById("finance-subscriber-chart");
    const paymentMixCanvas = document.getElementById("finance-payment-mix-chart");

    if (revenueCanvas) {
        revenueChart = new Chart(revenueCanvas, {
            type: "line",
            data: {
                labels: props.charts?.labels || [],
                datasets: [
                    {
                        label: "Revenue",
                        data: props.charts?.revenue || [],
                        borderColor: "#1294ea",
                        backgroundColor: "rgba(18, 148, 234, 0.14)",
                        fill: true,
                        tension: 0.34,
                        borderWidth: 3,
                        pointRadius: 0,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: "rgba(148, 163, 184, 0.16)" },
                    },
                    x: {
                        grid: { display: false },
                    },
                },
            },
        });
    }

    if (subscriberCanvas) {
        subscriberChart = new Chart(subscriberCanvas, {
            type: "bar",
            data: {
                labels: props.charts?.labels || [],
                datasets: [
                    {
                        label: "Subscribers",
                        data: props.charts?.subscribers || [],
                        backgroundColor: "#0f172a",
                        borderRadius: 16,
                        barThickness: 18,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: "rgba(148, 163, 184, 0.16)" },
                    },
                    x: {
                        grid: { display: false },
                    },
                },
            },
        });
    }

    if (paymentMixCanvas) {
        paymentMixChart = new Chart(paymentMixCanvas, {
            type: "doughnut",
            data: {
                labels: props.charts?.payment_mix_labels || [],
                datasets: [
                    {
                        data: props.charts?.payment_mix_values || [],
                        backgroundColor: ["#1294ea", "#22c55e", "#f59e0b", "#ef4444", "#8b5cf6", "#0f172a"],
                        borderWidth: 0,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "bottom",
                        labels: {
                            usePointStyle: true,
                            boxWidth: 10,
                        },
                    },
                },
                cutout: "68%",
            },
        });
    }
});

onBeforeUnmount(() => {
    destroyCharts();
});
</script>

<template>
    <AuthLayout title="Finance Dashboard" description="Platform revenue and subscriber analytics">
        <section class="finance-dashboard">
            <div class="dashboard-hero">
                <div>
                    <p class="dashboard-kicker">Finance Intelligence</p>
                    <h1 class="dashboard-title">Revenue, subscription health, and collection visibility</h1>
                    <p class="dashboard-copy">
                        Review revenue performance, subscription movement, payment mix, and the latest platform
                        transactions from one filtered dashboard.
                    </p>
                    <p class="dashboard-copy dashboard-copy--muted">
                        {{ rangeLabel || "Current reporting period" }}
                    </p>
                </div>

                <div class="hero-actions">
                    <span v-if="hasActiveFilters" class="filter-badge">
                        {{ activeFilterCount }} filter{{ activeFilterCount > 1 ? "s" : "" }} active
                    </span>
                    <button type="button" class="btn btn-outline-secondary" @click="applyFilters">
                        <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                    </button>
                </div>
            </div>

            <div class="stat-grid">
                <article v-for="card in summaryCards" :key="card.label" class="stat-card" :class="card.tone">
                    <div class="stat-card__top">
                        <div>
                            <p class="stat-label">{{ card.label }}</p>
                            <h3 class="stat-value">{{ card.value }}</h3>
                            <p class="stat-helper">{{ card.helper }}</p>
                        </div>
                        <div class="stat-icon">
                            <i :class="card.icon"></i>
                        </div>
                    </div>
                </article>
            </div>

            <div class="signal-grid">
                <article v-for="card in signalCards" :key="card.label" class="signal-card">
                    <div>
                        <p class="signal-label">{{ card.label }}</p>
                        <h3 class="signal-value">{{ card.value }}</h3>
                    </div>
                    <p class="signal-helper">{{ card.helper }}</p>
                </article>
            </div>

            <div class="card border-0 shadow-sm filter-card">
                <div class="card-body">
                    <div class="filter-header">
                        <div>
                            <p class="filter-kicker">Filters</p>
                            <h3 class="filter-title">Refine the finance view</h3>
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
                                    placeholder="Search user, email, plan, or order reference"
                                    @keydown.enter.prevent="applyFilters"
                                />
                                <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                            </div>
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
                            <label class="form-label text-muted small text-uppercase mb-2">Subscription status</label>
                            <select v-model="filterForm.status" class="form-select" @change="applyFilters">
                                <option value="">All statuses</option>
                                <option v-for="status in statuses" :key="status" :value="status">
                                    {{ status }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Payment status</label>
                            <select v-model="filterForm.payment_status" class="form-select" @change="applyFilters">
                                <option value="">All payments</option>
                                <option v-for="status in paymentStatuses" :key="status" :value="status">
                                    {{ status }}
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

            <div class="dashboard-grid">
                <article class="panel-card panel-card--wide">
                    <div class="panel-head">
                        <div>
                            <p class="panel-kicker">Revenue Trend</p>
                            <h3>Revenue performance over time</h3>
                        </div>
                        <span class="panel-chip">{{ rangeLabel }}</span>
                    </div>
                    <div class="chart-wrap">
                        <canvas id="finance-revenue-chart"></canvas>
                    </div>
                </article>

                <article class="panel-card">
                    <div class="panel-head">
                        <div>
                            <p class="panel-kicker">Payment Mix</p>
                            <h3>Payment status distribution</h3>
                        </div>
                    </div>
                    <div class="chart-wrap chart-wrap--small">
                        <canvas id="finance-payment-mix-chart"></canvas>
                    </div>
                </article>

                <article class="panel-card">
                    <div class="panel-head">
                        <div>
                            <p class="panel-kicker">Subscription Trend</p>
                            <h3>New subscriptions</h3>
                        </div>
                    </div>
                    <div class="chart-wrap chart-wrap--small">
                        <canvas id="finance-subscriber-chart"></canvas>
                    </div>
                </article>

                <article class="panel-card">
                    <div class="panel-head">
                        <div>
                            <p class="panel-kicker">Top Plans</p>
                            <h3>Highest revenue plans</h3>
                        </div>
                    </div>
                    <div class="plan-list">
                        <div v-for="plan in topPlans" :key="plan.id" class="plan-row">
                            <div>
                                <strong>{{ plan.title }}</strong>
                                <p>{{ plan.frequency }} plan</p>
                            </div>
                            <div class="plan-meta">
                                <span>{{ formatNumber(plan.subscribers) }} subscribers</span>
                                <strong>{{ currencyFormatter(plan.revenue, plan.currency) }}</strong>
                            </div>
                        </div>
                        <div v-if="!topPlans?.length" class="empty-state">No plan performance data found for the selected filters.</div>
                    </div>
                </article>

                <article class="panel-card panel-card--wide">
                    <div class="panel-head">
                        <div>
                            <p class="panel-kicker">Recent Activity</p>
                            <h3>Latest transactions</h3>
                        </div>
                    </div>
                    <div class="transaction-list">
                        <div v-for="transaction in recentTransactions" :key="transaction.id" class="transaction-row">
                            <div class="transaction-row__identity">
                                <div class="transaction-avatar">
                                    <i class="fa-solid fa-receipt"></i>
                                </div>
                                <div>
                                    <strong>{{ transaction.user }}</strong>
                                    <p>{{ transaction.email }}</p>
                                    <small>{{ transaction.plan_name }}</small>
                                </div>
                            </div>

                            <div class="transaction-row__meta">
                                <span class="status-chip" :class="transaction.payment_status === 'Paid' ? 'status-chip--active' : 'status-chip--inactive'">
                                    {{ transaction.payment_status }}
                                </span>
                                <span class="gateway-chip">{{ transaction.gateway }}</span>
                                <strong>{{ currencyFormatter(transaction.amount, transaction.currency) }}</strong>
                                <small>{{ transaction.created_label }}</small>
                            </div>
                        </div>

                        <div v-if="!recentTransactions?.length" class="empty-state">
                            No transaction records found for the selected filters.
                        </div>
                    </div>
                </article>
            </div>
        </section>
    </AuthLayout>
</template>

<style scoped>
.finance-dashboard {
    display: grid;
    gap: 24px;
}

.dashboard-hero {
    display: flex;
    justify-content: space-between;
    gap: 24px;
    padding: 32px;
    border-radius: 28px;
    background: linear-gradient(135deg, #f8fcff 0%, #eef7ff 52%, #ffffff 100%);
    border: 1px solid rgba(18, 148, 234, 0.1);
}

.dashboard-kicker,
.filter-kicker,
.panel-kicker {
    margin: 0 0 8px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #1294ea;
}

.dashboard-title {
    margin: 0 0 10px;
    font-size: 34px;
    font-weight: 700;
    color: #0f172a;
}

.dashboard-copy {
    margin: 0;
    color: #64748b;
    max-width: 760px;
}

.dashboard-copy--muted {
    margin-top: 10px;
}

.hero-actions,
.filter-tools {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.filter-badge,
.panel-chip,
.gateway-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.45rem 0.75rem;
    border-radius: 999px;
    background: #eef2ff;
    color: #3730a3;
    font-size: 0.8rem;
    font-weight: 600;
}

.stat-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
}

.stat-card,
.signal-card,
.filter-card,
.panel-card {
    border-radius: 24px;
    background: #fff;
    border: 1px solid #e2e8f0;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.stat-card {
    padding: 22px 24px;
}

.stat-card__top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
}

.stat-label,
.signal-label {
    margin: 0 0 8px;
    color: #64748b;
    font-size: 0.82rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.stat-value,
.signal-value {
    margin: 0;
    color: #0f172a;
    font-size: 2rem;
    font-weight: 700;
}

.stat-helper,
.signal-helper {
    margin: 8px 0 0;
    color: #64748b;
    font-size: 0.92rem;
}

.stat-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 54px;
    height: 54px;
    border-radius: 18px;
    font-size: 1.35rem;
}

.tone-blue .stat-icon {
    background: #e0f2fe;
    color: #0369a1;
}

.tone-green .stat-icon {
    background: #dcfce7;
    color: #15803d;
}

.tone-indigo .stat-icon {
    background: #e0e7ff;
    color: #4338ca;
}

.tone-amber .stat-icon {
    background: #ffedd5;
    color: #c2410c;
}

.signal-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

.signal-card {
    padding: 22px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
}

.filter-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 18px;
}

.filter-title,
.panel-head h3 {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 700;
    color: #0f172a;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(320px, 0.85fr);
    gap: 18px;
}

.panel-card {
    padding: 24px;
}

.panel-card--wide {
    grid-column: span 1;
}

.panel-head {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    align-items: flex-start;
    margin-bottom: 18px;
}

.chart-wrap {
    position: relative;
    min-height: 320px;
}

.chart-wrap--small {
    min-height: 280px;
}

.plan-list,
.transaction-list {
    display: grid;
    gap: 12px;
}

.plan-row,
.transaction-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 14px 16px;
    border-radius: 18px;
    background: #f8fbff;
    border: 1px solid #e2e8f0;
}

.plan-row p,
.transaction-row p {
    margin: 4px 0 0;
    color: #64748b;
}

.plan-meta,
.transaction-row__meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
    text-align: right;
}

.transaction-row__identity {
    display: flex;
    align-items: center;
    gap: 14px;
}

.transaction-avatar {
    width: 46px;
    height: 46px;
    border-radius: 16px;
    background: linear-gradient(135deg, #dbeafe, #e0f2fe);
    color: #0f172a;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

.status-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.36rem 0.72rem;
    border-radius: 999px;
    font-size: 0.76rem;
    font-weight: 700;
}

.status-chip--active {
    background: #dcfce7;
    color: #166534;
}

.status-chip--inactive {
    background: #fee2e2;
    color: #b91c1c;
}

.empty-state {
    padding: 24px;
    border-radius: 18px;
    background: #f8fafc;
    color: #64748b;
    text-align: center;
}

@media (max-width: 1200px) {
    .stat-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 991px) {
    .dashboard-hero,
    .filter-header,
    .panel-head,
    .signal-card,
    .plan-row,
    .transaction-row {
        flex-direction: column;
        align-items: flex-start;
    }

    .hero-actions,
    .filter-tools,
    .plan-meta,
    .transaction-row__meta {
        justify-content: flex-start;
        align-items: flex-start;
        text-align: left;
    }
}

@media (max-width: 640px) {
    .stat-grid,
    .signal-grid {
        grid-template-columns: 1fr;
    }

    .dashboard-title {
        font-size: 28px;
    }
}
</style>
