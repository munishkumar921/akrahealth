<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Modal from "@/Components/Common/Modal.vue";
import Table from "@/Components/Table/Table.vue";
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    activities: Object,
    metrics: Object,
    roles: Array,
    modules: Array,
    actions: Array,
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    role: props.filters?.role || "",
    module: props.filters?.module || "",
    action: props.filters?.action || "",
    date_from: props.filters?.date_from || "",
    date_to: props.filters?.date_to || "",
});

const selectedActivity = ref(null);
const isDetailModalOpen = ref(false);

const columns = [
    { label: "Actor", type: "slot", slot: "actor", align: "left" },
    { label: "Role", align: "left" },
    { label: "Module", key: "module", type: "slot", slot: "module", align: "left" },
    { label: "Action", key: "action", type: "slot", slot: "action" },
    { label: "Activity", align: "left" },
    { label: "IP Address", key: "ip_address", align: "left" },
    { label: "Device", type: "slot", slot: "device", align: "left" },
    { label: "Logged At", key: "created_at", type: "slot", slot: "created_at", align: "left" },
];

const rows = computed(() => props.activities?.data ?? []);
const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);
const resultSummary = computed(() => {
    const total = props.activities?.total ?? rows.value.length;
    const from = props.activities?.from ?? (rows.value.length ? 1 : 0);
    const to = props.activities?.to ?? rows.value.length;

    if (!total) return "No activity logs found";

    return `Showing ${from}-${to} of ${total} activity logs`;
});

const metricCards = computed(() => [
    {
        label: "Total Logs",
        value: props.metrics?.total_logs ?? 0,
        tone: "tone-blue",
        icon: "bi bi-journal-text",
    },
    {
        label: "Unique Actors",
        value: props.metrics?.unique_actors ?? 0,
        tone: "tone-slate",
        icon: "bi bi-people-fill",
    },
    {
        label: "Today",
        value: props.metrics?.today_logs ?? 0,
        tone: "tone-green",
        icon: "bi bi-calendar-check",
    },
    {
        label: "Critical Actions",
        value: props.metrics?.critical_actions ?? 0,
        tone: "tone-amber",
        icon: "bi bi-exclamation-triangle",
    },
]);

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
    router.get(route("superAdmin.activitymonitoring"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        role: "",
        module: "",
        action: "",
        date_from: "",
        date_to: "",
    };

    router.get(route("superAdmin.activitymonitoring"), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const openRow = ({ row }) => {
    selectedActivity.value = row;
    isDetailModalOpen.value = true;
};

const closeModal = () => {
    selectedActivity.value = null;
    isDetailModalOpen.value = false;
};
</script>

<template>
    <AuthLayout title="Activity Monitoring" description="Review platform audit activity">
        <div class="sadmin-activity-page">

            <div class="border-0 shadow-sm filter-card">
                <div class="card-body">
                    <div class="filter-header">
                        <div>
                            <p class="filter-kicker">Filters</p>
                            <h3 class="filter-title">Refine the activity feed</h3>
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
                                    placeholder="Search actor, email, module, action, description, or IP"
                                    @keydown.enter.prevent="applyFilters"
                                />
                                <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Role</label>
                            <select v-model="filterForm.role" class="form-select" @change="applyFilters">
                                <option value="">All roles</option>
                                <option v-for="role in roles" :key="role.id" :value="role.name">
                                    {{ role.name }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Module</label>
                            <select v-model="filterForm.module" class="form-select" @change="applyFilters">
                                <option value="">All modules</option>
                                <option v-for="module in modules" :key="module" :value="module">
                                    {{ module }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Action</label>
                            <select v-model="filterForm.action" class="form-select" @change="applyFilters">
                                <option value="">All actions</option>
                                <option v-for="action in actions" :key="action" :value="action">
                                    {{ action }}
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
                    <Table :columns="columns" :data="activities" :search-show="false" table="activity" @cell-click="openRow">
                        <template #actor="{ row }">
                            <div class="user-cell">
                                <img :src="row.profile_photo_url || '/images/avatar.webp'" alt="avatar" class="user-avatar" />
                                <div>
                                    <div class="user-name">{{ row.actor_name || "System" }}</div>
                                    <div class="user-meta">{{ row.actor_email || "No email" }}</div>
                                </div>
                            </div>
                        </template>

                        <template #module="{ row }">
                            <div class="text-start">
                                <div class="fw-semibold text-dark">{{ row.module_label || row.module || "General" }}</div>
                                <div class="text-muted small">{{ row.module || "N/A" }}</div>
                            </div>
                        </template>

                        <template #action="{ row }">
                            <span
                                class="status-pill"
                                :class="{
                                    'status-pill--create': row.action === 'create',
                                    'status-pill--update': row.action === 'update',
                                    'status-pill--delete': row.action === 'delete',
                                    'status-pill--view': row.action === 'view',
                                    'status-pill--other': !['create', 'update', 'delete', 'view'].includes(row.action),
                                }"
                            >
                                {{ row.action_label }}
                            </span>
                        </template>

                        <template #device="{ row }">
                            <div class="text-start">
                                <div class="fw-semibold text-dark">{{ row.user_agent_short || "Unknown device" }}</div>
                                <div class="text-muted small text-truncate activity-agent">{{ row.user_agent || "No user agent" }}</div>
                            </div>
                        </template>

                        <template #created_at="{ row }">
                            <div class="text-start">
                                <div class="fw-semibold text-dark">{{ row.created_human || "N/A" }}</div>
                                <div class="text-muted small">{{ row.created_label || "N/A" }}</div>
                            </div>
                        </template>
                    </Table>
                </div>
            </div>
        </div>

        <Modal :isOpen="isDetailModalOpen" title="Activity Details" @close="closeModal" size="lg">
            <div v-if="selectedActivity" class="detail-grid">
                <div class="detail-item">
                    <label>Actor</label>
                    <span>{{ selectedActivity.actor_name || "System" }}</span>
                </div>
                <div class="detail-item">
                    <label>Email</label>
                    <span>{{ selectedActivity.actor_email || "N/A" }}</span>
                </div>
                <div class="detail-item">
                    <label>Role</label>
                    <span>{{ selectedActivity.role || "N/A" }}</span>
                </div>
                <div class="detail-item">
                    <label>Module</label>
                    <span>{{ selectedActivity.module_label || selectedActivity.module || "N/A" }}</span>
                </div>
                <div class="detail-item">
                    <label>Action</label>
                    <span>{{ selectedActivity.action_label || "N/A" }}</span>
                </div>
                <div class="detail-item">
                    <label>IP Address</label>
                    <span>{{ selectedActivity.ip_address || "N/A" }}</span>
                </div>
                <div class="detail-item detail-item--wide">
                    <label>Description</label>
                    <span>{{ selectedActivity.description || "N/A" }}</span>
                </div>
                <div class="detail-item detail-item--wide">
                    <label>Device</label>
                    <span>{{ selectedActivity.user_agent || "N/A" }}</span>
                </div>
                <div class="detail-item">
                    <label>Logged At</label>
                    <span>{{ selectedActivity.created_label || "N/A" }}</span>
                </div>
            </div>
        </Modal>
    </AuthLayout>
</template>

<style scoped>
.sadmin-activity-page {
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

.hero-kicker {
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

.hero-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
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
    font-size: 2rem;
    font-weight: 700;
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

.tone-amber .metric-icon {
    background: #ffedd5;
    color: #c2410c;
}

.tone-slate .metric-icon {
    background: #e2e8f0;
    color: #334155;
}

.filter-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 18px;
}

.filter-kicker {
    margin: 0 0 6px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #1294ea;
}

.filter-title {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 700;
    color: #0f172a;
}

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

.filter-card,
.table-shell {
    border-radius: 22px;
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
    font-weight: 600;
    color: #0f172a;
}

.user-meta {
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

.status-pill--create {
    background: #dcfce7;
    color: #166534;
}

.status-pill--update {
    background: #fef3c7;
    color: #92400e;
}

.status-pill--delete {
    background: #fee2e2;
    color: #b91c1c;
}

.status-pill--view {
    background: #dbeafe;
    color: #1d4ed8;
}

.status-pill--other {
    background: #f1f5f9;
    color: #475569;
}

.activity-agent {
    max-width: 280px;
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
