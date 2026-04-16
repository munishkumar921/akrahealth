<script setup>
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Modal from "@/Components/Common/Modal.vue";
import Table from "@/Components/Table/Table.vue";
import AddsubscriptionPlan from "@/Pages/Modals/SubscriptionPlan.vue";

const props = defineProps({
    subscriptionPlans: Object,
    filters: {
        type: Object,
        default: () => ({}),
    },
    countries: Array,
    permissions: Array,
    metrics: Object,
    planForOptions: Array,
    currencyOptions: Array,
    frequencyOptions: Array,
});

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    plan_for: props.filters?.plan_for || "",
    currency: props.filters?.currency || "",
    country_id: props.filters?.country_id || "",
    frequency: props.filters?.frequency || "",
    status: props.filters?.status || "",
});

const openModal = ref(false);
const childComponentRef = ref(null);

const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);

const rows = computed(() => props.subscriptionPlans?.data ?? []);

const resultSummary = computed(() => {
    const total = props.subscriptionPlans?.total ?? rows.value.length;
    const from = props.subscriptionPlans?.from ?? (rows.value.length ? 1 : 0);
    const to = props.subscriptionPlans?.to ?? rows.value.length;

    if (!total) return "No subscription plans found";

    return `Showing ${from}-${to} of ${total} subscription plans`;
});

const metricCards = computed(() => [
    {
        label: "Total Plans",
        value: props.metrics?.total_plans ?? 0,
        helper: "Plans in the filtered result set",
        icon: "fa-solid fa-layer-group",
        tone: "tone-blue",
    },
    {
        label: "Active Plans",
        value: props.metrics?.active_plans ?? 0,
        helper: "Plans currently available",
        icon: "fa-solid fa-toggle-on",
        tone: "tone-green",
    },
    {
        label: "Inactive Plans",
        value: props.metrics?.inactive_plans ?? 0,
        helper: "Plans currently hidden",
        icon: "fa-solid fa-toggle-off",
        tone: "tone-slate",
    },
    {
        label: "Monthly Plans",
        value: props.metrics?.monthly_plans ?? 0,
        helper: "Recurring monthly plans",
        icon: "fa-solid fa-calendar-days",
        tone: "tone-amber",
    },
]);

const columns = [
    { label: "Plan", key: "title", type: "slot", slot: "plan", align: "left" },
    { label: "Plan For", key: "plan_for", align: "left" },
    { label: "Frequency", key: "frequency" },
    { label: "Price", key: "price", type: "slot", slot: "price", align: "left" },
    { label: "Country", key: "country_id", type: "slot", slot: "country", align: "left" },
    { label: "Status", key: "status", type: "slot", slot: "status" },
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
    router.get(route("superAdmin.subcriptionPlan"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        plan_for: "",
        currency: "",
        country_id: "",
        frequency: "",
        status: "",
    };

    router.get(route("superAdmin.subcriptionPlan"), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const addPlan = () => {
    openModal.value = true;
    setTimeout(() => {
        childComponentRef.value?.resetForm?.();
    }, 10);
};

const editPlan = (plan) => {
    openModal.value = true;
    setTimeout(() => {
        childComponentRef.value?.resetForm?.();
        childComponentRef.value?.update?.(plan);
    }, 10);
};

const closeModal = () => {
    openModal.value = false;
};

const removeRow = (row) => {
    if (confirm(`Are you sure you want to delete "${row.title}"?`)) {
        router.delete(route("superAdmin.subcriptionPlan.destroy", row.id), {
            preserveScroll: true,
        });
    }
};

const toggleStatus = (row) => {
    axios
        .post(route("superAdmin.toggle-active", row.id))
        .then(() => {
            row.status = !row.status;
            toast(row.status ? `${row.title} is now Active` : `${row.title} is now Inactive`, row.status ? "success" : "warning", 2000);
        })
        .catch(() => {
            toast("Unable to update plan status right now.", "error", 2000);
        });
};

const resolveCountryName = (countryId) => props.countries.find((country) => country.id === countryId)?.name || "Global";
</script>

<template>
    <AuthLayout title="Subscription Plans" description="Manage platform subscription plans">
        <section class="plan-page">

            <div class="border-0 shadow-sm filter-card">
                <div class="card-body">
                    <div class="filter-header">
                        <div>
                            <p class="filter-kicker">Filters</p>
                            <h3 class="filter-title">Refine the plan catalogue</h3>
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
                                    placeholder="Search title, audience, price, or currency"
                                    @keydown.enter.prevent="applyFilters"
                                />
                                <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Plan for</label>
                            <select v-model="filterForm.plan_for" class="form-select" @change="applyFilters">
                                <option value="">All audiences</option>
                                <option v-for="planFor in planForOptions" :key="planFor" :value="planFor">
                                    {{ planFor }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Frequency</label>
                            <select v-model="filterForm.frequency" class="form-select" @change="applyFilters">
                                <option value="">All frequencies</option>
                                <option v-for="frequency in frequencyOptions" :key="frequency" :value="frequency">
                                    {{ frequency }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Currency</label>
                            <select v-model="filterForm.currency" class="form-select" @change="applyFilters">
                                <option value="">All currencies</option>
                                <option v-for="currency in currencyOptions" :key="currency" :value="currency">
                                    {{ currency }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Country</label>
                            <select v-model="filterForm.country_id" class="form-select" @change="applyFilters">
                                <option value="">All countries</option>
                                <option v-for="country in countries" :key="country.id" :value="country.id">
                                    {{ country.name }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Status</label>
                            <select v-model="filterForm.status" class="form-select" @change="applyFilters">
                                <option value="">All statuses</option>
                                <option value="true">Active</option>
                                <option value="false">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm table-shell">
                <div class="card-body p-0 p-md-3">
                    <Table :columns="columns" :data="subscriptionPlans" table="superadmin-subscription-plans" :search-show="false">
                        <template #plan="{ row }">
                            <div class="plan-cell">
                                <div class="plan-avatar">
                                    <i class="fa-solid fa-box-open"></i>
                                </div>
                                <div>
                                    <div class="plan-title">{{ row.title }}</div>
                                    <div class="plan-meta">{{ row.plan_for }}</div>
                                </div>
                            </div>
                        </template>

                        <template #price="{ row }">
                            <div class="text-start">
                                <div class="fw-semibold text-dark">{{ row.currency }} {{ Number(row.price || 0).toFixed(2) }}</div>
                                <div class="text-muted small">{{ row.frequency }}</div>
                            </div>
                        </template>

                        <template #country="{ row }">
                            <div class="text-start">
                                <div class="fw-semibold text-dark">{{ resolveCountryName(row.country_id) }}</div>
                                <div class="text-muted small">{{ row.currency }}</div>
                            </div>
                        </template>

                        <template #status="{ row }">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <span class="status-pill" :class="row.status ? 'status-pill--active' : 'status-pill--inactive'">
                                    {{ row.status ? "Active" : "Inactive" }}
                                </span>
                                <label class="switch">
                                    <input :checked="row.status" type="checkbox" @change="toggleStatus(row)" />
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </template>

                        <template #actions="{ row }">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="icon-btn icon-btn--edit" @click="editPlan(row)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="icon-btn icon-btn--delete" @click="removeRow(row)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </template>
                    </Table>
                </div>
            </div>
        </section>

        <Modal :isOpen="openModal" @close="closeModal" title="Subscription Plan" size="xl">
            <AddsubscriptionPlan ref="childComponentRef" :countries="countries" :permissions="permissions" @close="closeModal" />
        </Modal>
    </AuthLayout>
</template>

<style scoped>
.plan-page {
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

.tone-slate .metric-icon {
    background: #e2e8f0;
    color: #334155;
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

.plan-cell {
    display: flex;
    align-items: center;
    gap: 14px;
    text-align: left;
}

.plan-avatar {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    background: linear-gradient(135deg, #dbeafe, #e0f2fe);
    color: #0f172a;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

.plan-title {
    font-weight: 700;
    color: #0f172a;
}

.plan-meta {
    color: #475569;
    font-size: 0.85rem;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.38rem 0.75rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
}

.status-pill--active {
    background: #dcfce7;
    color: #166534;
}

.status-pill--inactive {
    background: #fee2e2;
    color: #b91c1c;
}

.icon-btn {
    width: 34px;
    height: 34px;
    border-radius: 12px;
    border: 1px solid #dbe4f0;
    background: #fff;
}

.icon-btn--edit {
    color: #2563eb;
}

.icon-btn--delete {
    color: #dc2626;
}

.switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    inset: 0;
    background-color: #cbd5e1;
    transition: 0.2s;
    border-radius: 999px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    top: 3px;
    background-color: white;
    transition: 0.2s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: #22c55e;
}

input:checked + .slider:before {
    transform: translateX(20px);
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
    .metrics-grid {
        grid-template-columns: 1fr;
    }
}
</style>
