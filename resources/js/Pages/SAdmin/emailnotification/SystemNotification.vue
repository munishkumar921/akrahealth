<script setup>
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import Swal from "sweetalert2/dist/sweetalert2.js";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Modal from "@/Components/Common/Modal.vue";
import Table from "@/Components/Table/Table.vue";

const props = defineProps({
    notifications: Object,
    filters: {
        type: Object,
        default: () => ({}),
    },
    metrics: {
        type: Object,
        default: () => ({}),
    },
    typeOptions: {
        type: Array,
        default: () => [],
    },
    channelOptions: {
        type: Array,
        default: () => [],
    },
    recipientRoleOptions: {
        type: Array,
        default: () => [],
    },
});

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    type: props.filters?.type || "",
    channel: props.filters?.channel || "",
    status: props.filters?.status || "",
    recipient_role: props.filters?.recipient_role || "",
    date_from: props.filters?.date_from || "",
    date_to: props.filters?.date_to || "",
});

const selectedNotification = ref(null);
const isDetailModalOpen = ref(false);

const columns = [
    { label: "Notification", key: "title", type: "slot", slot: "notification", align: "left" },
    { label: "Channel", key: "channel", align: "left" },
    { label: "Recipient", key: "recipient_name", type: "slot", slot: "recipient", align: "left" },
    { label: "Created", key: "created_at", align: "left" },
    { label: "Read On", key: "read_at", type: "slot", slot: "readAt", align: "left" },
    { label: "Status", key: "status", type: "slot", slot: "status" },
];

const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);

const resultSummary = computed(() => {
    const total = props.notifications?.total ?? 0;
    const from = props.notifications?.from ?? 0;
    const to = props.notifications?.to ?? 0;

    if (!total) return "No system notifications found";

    return `Showing ${from}-${to} of ${total} system notifications`;
});

const metricCards = computed(() => [
    {
        label: "Total Notifications",
        value: Number(props.metrics?.total ?? 0).toLocaleString("en-IN"),
        helper: "System and in-app records in this view",
        icon: "fa-solid fa-bell",
        tone: "tone-blue",
    },
    {
        label: "Unread",
        value: Number(props.metrics?.unread ?? 0).toLocaleString("en-IN"),
        helper: "Still pending review",
        icon: "fa-solid fa-bell-concierge",
        tone: "tone-amber",
    },
    {
        label: "Read",
        value: Number(props.metrics?.read ?? 0).toLocaleString("en-IN"),
        helper: "Already acknowledged",
        icon: "fa-solid fa-circle-check",
        tone: "tone-green",
    },
    {
        label: "Recipients",
        value: Number(props.metrics?.unique_recipients ?? 0).toLocaleString("en-IN"),
        helper: "Unique users affected",
        icon: "fa-solid fa-users",
        tone: "tone-slate",
    },
]);

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
    router.get(route("superAdmin.systemnotification"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        type: "",
        channel: "",
        status: "",
        recipient_role: "",
        date_from: "",
        date_to: "",
    };

    router.get(route("superAdmin.systemnotification"), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const openDetail = (row) => {
    selectedNotification.value = row;
    isDetailModalOpen.value = true;
};

const closeDetail = () => {
    selectedNotification.value = null;
    isDetailModalOpen.value = false;
};

const deleteNotification = (row) => {
    Swal.fire({
        title: "Delete notification?",
        text: "This notification will be permanently removed.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Delete",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#ef4444",
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route("superAdmin.notifications.destroy", row.id), {
                preserveScroll: true,
            });
        }
    });
};

const markAllAsRead = () => {
    router.post(
        route("superAdmin.notifications.mark-all-read"),
        { scope: "system" },
        {
            preserveScroll: true,
        }
    );
};

const deleteAll = () => {
    Swal.fire({
        title: "Delete all system notifications?",
        text: "This will remove all notifications in this view.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Delete all",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#ef4444",
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route("superAdmin.notifications.delete-all"), {
                data: { scope: "system" },
                preserveScroll: true,
            });
        }
    });
};
</script>

<template>
    <AuthLayout title="System Notifications" description="Monitor platform-level in-app notification activity">
        <section class="notification-page">

            <div class="card border-0 shadow-sm filter-card">
                <div class="card-body">
                    <div class="filter-header">
                        <div>
                            <p class="section-kicker">Filters</p>
                            <h3 class="section-title">Refine notifications</h3>
                        </div>
                        <div v-if="hasActiveFilters" class="filter-tools">
                            <span class="filter-badge">{{ activeFilterCount }} active</span>
                            <button type="button" class="btn btn-outline-secondary btn-sm" @click="clearFilters">
                                <i class="bi bi-x-circle me-1"></i> Clear filters
                            </button>
                        </div>
                    </div>

                    <div class="bulk-actions mb-3">
                        <button type="button" class="btn btn-primary btn-sm" @click="markAllAsRead">
                            <i class="bi bi-check2-all me-1"></i> Mark All As Read
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" @click="deleteAll">
                            <i class="bi bi-trash me-1"></i> Delete All Notifications
                        </button>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-xl-4">
                            <label class="form-label text-muted small text-uppercase mb-2">Search</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 border rounded-circle-left">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input v-model="filterForm.keyword" type="search" class="form-control border-start-0"
                                    placeholder="Search title, message, type, or recipient"
                                    @keydown.enter.prevent="applyFilters" />
                                <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Type</label>
                            <select v-model="filterForm.type" class="form-select" @change="applyFilters">
                                <option value="">All types</option>
                                <option v-for="type in typeOptions" :key="type" :value="type">{{ type }}</option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Channel</label>
                            <select v-model="filterForm.channel" class="form-select" @change="applyFilters">
                                <option value="">All channels</option>
                                <option v-for="channel in channelOptions" :key="channel" :value="channel">{{ channel }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Status</label>
                            <select v-model="filterForm.status" class="form-select" @change="applyFilters">
                                <option value="">All statuses</option>
                                <option value="read">Read</option>
                                <option value="unread">Unread</option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Recipient role</label>
                            <select v-model="filterForm.recipient_role" class="form-select" @change="applyFilters">
                                <option value="">All roles</option>
                                <option v-for="role in recipientRoleOptions" :key="role" :value="role">{{ role }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">From date</label>
                            <input v-model="filterForm.date_from" type="date" class="form-control"
                                @change="applyFilters" />
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">To date</label>
                            <input v-model="filterForm.date_to" type="date" class="form-control"
                                @change="applyFilters" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm table-shell">
                <div class="card-body">
                    <Table :columns="columns" :data="notifications" table="notifications" :search-show="false">
                        <template #notification="{ row }">
                            <div class="notification-cell">
                                <div class="notification-title">{{ row.title }}</div>
                                <div class="notification-message">{{ row.message }}</div>
                            </div>
                        </template>

                        <template #recipient="{ row }">
                            <div class="recipient-cell">
                                <div class="recipient-name">{{ row.recipient_name }}</div>
                                <div class="recipient-email">{{ row.recipient_email || row.recipient_role }}</div>
                            </div>
                        </template>

                        <template #readAt="{ row }">
                            <span class="read-meta">{{ row.read_at || "Not read yet" }}</span>
                        </template>

                        <template #status="{ row }">
                            <span class="status-pill" :class="row.status === 'Read' ? 'status-read' : 'status-unread'">
                                {{ row.status }}
                            </span>
                        </template>

                        <template #actions="{ row }">
                            <div class="action-row">
                                <button type="button" class="table-action-btn action-view" title="View"
                                    @click="openDetail(row)">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" class="table-action-btn action-delete" title="Delete"
                                    @click="deleteNotification(row)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </template>
                    </Table>
                </div>
            </div>

            <Modal :show="isDetailModalOpen" max-width="2xl" @close="closeDetail">
                <div v-if="selectedNotification" class="notification-modal">
                    <div class="modal-head">
                        <div>
                            <p class="section-kicker mb-2">Notification detail</p>
                            <h3 class="modal-title">{{ selectedNotification.title }}</h3>
                        </div>
                        <button type="button" class="btn btn-light rounded-circle" @click="closeDetail">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="detail-grid">
                        <article class="detail-card">
                            <span class="detail-label">Message</span>
                            <p class="detail-text">{{ selectedNotification.message }}</p>
                        </article>
                        <article class="detail-card">
                            <span class="detail-label">Type</span>
                            <p class="detail-text">{{ selectedNotification.notification_type }}</p>
                        </article>
                        <article class="detail-card">
                            <span class="detail-label">Channel</span>
                            <p class="detail-text">{{ selectedNotification.channel }}</p>
                        </article>
                        <article class="detail-card">
                            <span class="detail-label">Recipient</span>
                            <p class="detail-text">
                                {{ selectedNotification.recipient_name }}
                                <span v-if="selectedNotification.recipient_email" class="detail-subtext">
                                    {{ selectedNotification.recipient_email }}
                                </span>
                            </p>
                        </article>
                        <article class="detail-card">
                            <span class="detail-label">Status</span>
                            <p class="detail-text">{{ selectedNotification.status }}</p>
                        </article>
                        <article class="detail-card">
                            <span class="detail-label">Created</span>
                            <p class="detail-text">{{ selectedNotification.created_at }}</p>
                        </article>
                    </div>

                    <div v-if="selectedNotification.action_url" class="detail-footer">
                        <a :href="selectedNotification.action_url" class="btn btn-primary">
                            <i class="bi bi-box-arrow-up-right me-2"></i> Open related record
                        </a>
                    </div>
                </div>
            </Modal>
        </section>
    </AuthLayout>
</template>

<style scoped>
.notification-page {
    display: grid;
    gap: 1.5rem;
}

.hero-card {
    border-radius: 28px;
    background:
        radial-gradient(circle at top left, rgba(34, 197, 94, 0.12), transparent 42%),
        linear-gradient(135deg, #ffffff, #f8fbff 55%, #eef7ff);
}

.hero-flex,
.metric-card .card-body,
.filter-header,
.table-header,
.modal-head,
.detail-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.hero-card .card-body,
.filter-card .card-body,
.table-shell .card-body {
    padding: 1.5rem;
}

.hero-tools {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.hero-kicker,
.section-kicker {
    margin: 0;
    font-size: 0.76rem;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: #0284c7;
}

.hero-title,
.section-title,
.modal-title {
    margin: 0;
    color: #0f172a;
}

.hero-title {
    font-size: clamp(2rem, 3vw, 2.6rem);
    font-weight: 800;
}

.hero-text,
.table-summary,
.metric-helper,
.notification-message,
.recipient-email,
.read-meta,
.detail-subtext {
    color: #64748b;
}

.hero-text {
    max-width: 720px;
    margin: 0.75rem 0 0;
    font-size: 1rem;
    line-height: 1.7;
}

.metric-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
}

.metric-card {
    border-radius: 24px;
}

.metric-icon {
    width: 3.2rem;
    height: 3.2rem;
    display: grid;
    place-items: center;
    border-radius: 1rem;
    font-size: 1.1rem;
}

.metric-label {
    margin: 0 0 0.35rem;
    color: #475569;
    font-weight: 700;
}

.metric-value {
    margin: 0;
    font-size: 1.8rem;
    font-weight: 800;
    color: #0f172a;
}

.metric-helper {
    margin: 0.35rem 0 0;
    font-size: 0.9rem;
}

.tone-blue .metric-icon {
    background: rgba(59, 130, 246, 0.12);
    color: #2563eb;
}

.tone-amber .metric-icon {
    background: rgba(245, 158, 11, 0.14);
    color: #d97706;
}

.tone-green .metric-icon {
    background: rgba(34, 197, 94, 0.12);
    color: #16a34a;
}

.tone-slate .metric-icon {
    background: rgba(100, 116, 139, 0.12);
    color: #475569;
}

.filter-card,
.table-shell {
    border-radius: 24px;
}

.filter-tools {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.bulk-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.filter-badge {
    padding: 0.35rem 0.75rem;
    border-radius: 999px;
    background: #eff6ff;
    color: #2563eb;
    font-size: 0.82rem;
    font-weight: 700;
}

.notification-cell,
.recipient-cell {
    display: grid;
    gap: 0.25rem;
}

.notification-title,
.recipient-name {
    color: #0f172a;
    font-weight: 700;
}

.notification-message {
    max-width: 28rem;
    line-height: 1.45;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.45rem 0.85rem;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 700;
}

.status-read {
    background: rgba(34, 197, 94, 0.15);
    color: #15803d;
}

.status-unread {
    background: rgba(245, 158, 11, 0.16);
    color: #c2410c;
}

.action-row {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.table-action-btn {
    width: 2.35rem;
    height: 2.35rem;
    border: 1px solid #d9e2f1;
    border-radius: 0.9rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    transition: all 0.2s ease;
}

.table-action-btn i {
    font-size: 0.95rem;
}

.action-view {
    color: #2563eb;
}

.action-delete {
    color: #ef4444;
}

.table-action-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
}

.notification-modal {
    padding: 1.5rem;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
    margin-top: 1.25rem;
}

.detail-card {
    padding: 1rem 1.1rem;
    border-radius: 1rem;
    background: #f8fbff;
    border: 1px solid #e2e8f0;
}

.detail-label {
    display: block;
    margin-bottom: 0.4rem;
    color: #64748b;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.detail-text {
    margin: 0;
    color: #0f172a;
    font-weight: 600;
    line-height: 1.55;
}

.detail-subtext {
    display: block;
    margin-top: 0.35rem;
    font-weight: 500;
}

.detail-footer {
    margin-top: 1.25rem;
    justify-content: flex-end;
}

@media (max-width: 991.98px) {

    .hero-flex,
    .filter-header,
    .table-header,
    .modal-head,
    .detail-footer {
        flex-direction: column;
        align-items: flex-start;
    }

    .detail-grid {
        grid-template-columns: 1fr;
    }
}
</style>
