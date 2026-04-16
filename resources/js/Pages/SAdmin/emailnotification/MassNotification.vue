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

const rows = computed(() => props.notifications?.data ?? []);
const selectedNotification = ref(null);
const isDetailModalOpen = ref(false);

const columns = [
    { label: "Notification", key: "title", type: "slot", slot: "notification", align: "left" },
    { label: "Type", key: "notification_type", align: "left" },
    { label: "Channel", key: "channel", align: "left" },
    { label: "Recipient", key: "recipient_name", type: "slot", slot: "recipient", align: "left" },
    { label: "Created", key: "created_at", align: "left" },
    { label: "Status", key: "status", type: "slot", slot: "status" },
];

const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);

const resultSummary = computed(() => {
    const total = props.notifications?.total ?? rows.value.length;
    const from = props.notifications?.from ?? (rows.value.length ? 1 : 0);
    const to = props.notifications?.to ?? rows.value.length;

    if (!total) return "No notifications found";

    return `Showing ${from}-${to} of ${total} notifications`;
});

const metricCards = computed(() => [
    {
        label: "Total Notifications",
        value: Number(props.metrics?.total ?? 0).toLocaleString("en-IN"),
        helper: "All notification records in this view",
        icon: "fa-solid fa-bell",
        tone: "tone-blue",
    },
    {
        label: "Unread",
        value: Number(props.metrics?.unread ?? 0).toLocaleString("en-IN"),
        helper: "Notifications still awaiting review",
        icon: "fa-solid fa-envelope-open-text",
        tone: "tone-amber",
    },
    {
        label: "Read",
        value: Number(props.metrics?.read ?? 0).toLocaleString("en-IN"),
        helper: "Notifications already reviewed",
        icon: "fa-solid fa-circle-check",
        tone: "tone-green",
    },
    {
        label: "Recipients",
        value: Number(props.metrics?.unique_recipients ?? 0).toLocaleString("en-IN"),
        helper: "Unique users reached by these notifications",
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
    router.get(route("superAdmin.massnotification"), buildQuery(), {
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

    router.get(route("superAdmin.massnotification"), {}, {
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
        { scope: "all" },
        {
            preserveScroll: true,
        }
    );
};

const deleteAll = () => {
    Swal.fire({
        title: "Delete all notifications?",
        text: "This will remove all notifications in this view.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Delete all",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#ef4444",
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route("superAdmin.notifications.delete-all"), {
                data: { scope: "all" },
                preserveScroll: true,
            });
        }
    });
};
</script>

<template>
    <AuthLayout title="Mass Notifications" description="Review all in-app notification activity across the platform">
        <section class="notification-page">

            <div class="border-0 shadow-sm filter-card">
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
                                <input
                                    v-model="filterForm.keyword"
                                    type="search"
                                    class="form-control border-start-0"
                                    placeholder="Search title, message, type, or recipient"
                                    @keydown.enter.prevent="applyFilters"
                                />
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
                                <option v-for="channel in channelOptions" :key="channel" :value="channel">{{ channel }}</option>
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
                                <option v-for="role in recipientRoleOptions" :key="role" :value="role">{{ role }}</option>
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

            <div class="border-0 shadow-sm table-shell">
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

                        <template #status="{ row }">
                            <span class="status-pill" :class="row.status === 'Read' ? 'status-read' : 'status-unread'">
                                {{ row.status }}
                            </span>
                        </template>

                        <template #actions="{ row }">
                            <div class="action-row">
                                <button type="button" class="table-action-btn action-view" title="View" @click="openDetail(row)">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" class="table-action-btn action-delete" title="Delete" @click="deleteNotification(row)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </template>
                    </Table>
                </div>
            </div>
        </section>

        <Modal :is-open="isDetailModalOpen" title="Notification Details" size="lg" @close="closeDetail">
            <div v-if="selectedNotification" class="detail-grid">
                <div class="detail-card">
                    <p class="detail-label">Title</p>
                    <p class="detail-value">{{ selectedNotification.title }}</p>
                </div>
                <div class="detail-card">
                    <p class="detail-label">Message</p>
                    <p class="detail-value">{{ selectedNotification.message }}</p>
                </div>
                <div class="detail-card">
                    <p class="detail-label">Type</p>
                    <p class="detail-value">{{ selectedNotification.notification_type }}</p>
                </div>
                <div class="detail-card">
                    <p class="detail-label">Channel</p>
                    <p class="detail-value">{{ selectedNotification.channel }}</p>
                </div>
                <div class="detail-card">
                    <p class="detail-label">Recipient</p>
                    <p class="detail-value">{{ selectedNotification.recipient_name }}</p>
                </div>
                <div class="detail-card">
                    <p class="detail-label">Recipient Email</p>
                    <p class="detail-value">{{ selectedNotification.recipient_email || "N/A" }}</p>
                </div>
                <div class="detail-card">
                    <p class="detail-label">Created</p>
                    <p class="detail-value">{{ selectedNotification.created_at }}</p>
                </div>
                <div class="detail-card">
                    <p class="detail-label">Read On</p>
                    <p class="detail-value">{{ selectedNotification.read_at || "Unread" }}</p>
                </div>
                <div v-if="selectedNotification.action_url" class="detail-card detail-card--full">
                    <p class="detail-label">Action URL</p>
                    <a class="detail-link" :href="selectedNotification.action_url">{{ selectedNotification.action_url }}</a>
                </div>
            </div>
        </Modal>
    </AuthLayout>
</template>

<style scoped>
.notification-page { display:grid; gap:1.5rem; }
.hero-card .card-body, .filter-card .card-body, .table-shell .card-body { padding:1.5rem; }
.hero-kicker, .section-kicker { margin:0 0 .35rem; text-transform:uppercase; letter-spacing:.14em; font-size:.72rem; font-weight:700; color:#0ea5e9; }
.hero-title, .section-title { margin:0; font-size:1.75rem; font-weight:700; color:#0f172a; }
.hero-text, .table-summary { margin:.5rem 0 0; color:#64748b; max-width:760px; }
.metric-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:1rem; }
.metric-card .card-body { display:flex; gap:1rem; align-items:center; }
.metric-icon { width:52px; height:52px; border-radius:16px; display:grid; place-items:center; font-size:1.2rem; background:rgba(255,255,255,.75); }
.metric-label,.recipient-email,.notification-message { margin:0; color:#64748b; }
.metric-value { margin:.25rem 0; font-size:1.6rem; font-weight:700; color:#0f172a; }
.metric-helper { margin:0; color:#64748b; font-size:.82rem; }
.tone-blue { background:linear-gradient(135deg,#eff6ff,#f8fbff); }
.tone-green { background:linear-gradient(135deg,#ecfdf5,#f7fffb); }
.tone-slate { background:linear-gradient(135deg,#f8fafc,#ffffff); }
.tone-amber { background:linear-gradient(135deg,#fff7ed,#fffdf7); }
.filter-header,.table-header { display:flex; justify-content:space-between; gap:1rem; align-items:flex-start; margin-bottom:1rem; }
.filter-tools { display:flex; align-items:center; gap:.75rem; }
.bulk-actions { display:flex; align-items:center; gap:.75rem; flex-wrap:wrap; }
.filter-badge { padding:.45rem .8rem; border-radius:999px; background:#eff6ff; color:#2563eb; font-size:.85rem; font-weight:600; }
.notification-title,.recipient-name { font-weight:700; color:#0f172a; }
.notification-message { max-width:520px; white-space:normal; }
.status-pill { display:inline-flex; align-items:center; justify-content:center; padding:.4rem .8rem; border-radius:999px; font-size:.82rem; font-weight:700; }
.status-read { background:#dcfce7; color:#166534; }
.status-unread { background:#fef3c7; color:#92400e; }
.action-row { display:flex; justify-content:center; gap:.6rem; }
.table-action-btn { width:42px; height:42px; border-radius:14px; border:1px solid #dbe4f0; background:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:1rem; transition:.2s ease; }
.action-view { color:#2563eb; }
.action-view:hover { background:#eff6ff; border-color:#bfdbfe; }
.action-delete { color:#ef4444; }
.action-delete:hover { background:#fef2f2; border-color:#fecaca; }
.detail-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:1rem; }
.detail-card { border:1px solid #e2e8f0; border-radius:18px; padding:1rem; }
.detail-card--full { grid-column:1 / -1; }
.detail-label { margin:0 0 .35rem; font-weight:700; color:#0f172a; }
.detail-value { margin:0; color:#334155; }
.detail-link { color:#2563eb; word-break:break-all; }
@media (max-width:1199px){ .metric-grid{grid-template-columns:repeat(2,minmax(0,1fr));} }
@media (max-width:767px){ .metric-grid,.detail-grid{grid-template-columns:1fr;} .filter-header,.table-header{flex-direction:column;} .hero-title,.section-title{font-size:1.4rem;} }
</style>
