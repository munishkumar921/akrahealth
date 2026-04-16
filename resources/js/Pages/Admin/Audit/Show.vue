<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { ArrowLeftIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
    auditLog: {
        type: Object,
        required: true,
    },
});

const normalizeJson = (value) => {
    if (!value) return {};

    if (typeof value === "string") {
        try {
            return JSON.parse(value);
        } catch (error) {
            return {};
        }
    }

    return value;
};

const oldValues = computed(() => normalizeJson(props.auditLog.old_values));
const newValues = computed(() => normalizeJson(props.auditLog.new_values));

const hasOldValues = computed(() => Object.keys(oldValues.value).length > 0);
const hasNewValues = computed(() => Object.keys(newValues.value).length > 0);

const actionText = computed(() => {
    const action = props.auditLog.action || "view";
    return action.charAt(0).toUpperCase() + action.slice(1);
});

const actionClass = computed(() => {
    switch ((props.auditLog.action || "").toLowerCase()) {
        case "create":
            return "status-pill status-pill--success";
        case "update":
            return "status-pill status-pill--warning";
        case "delete":
            return "status-pill status-pill--danger";
        case "login":
            return "status-pill status-pill--info";
        case "logout":
            return "status-pill status-pill--muted";
        default:
            return "status-pill status-pill--default";
    }
});

const summaryCards = computed(() => [
    {
        label: "Module",
        value: props.auditLog.module_label || props.auditLog.module || "-",
    },
    {
        label: "Action",
        value: actionText.value,
    },
    {
        label: "Actor",
        value: props.auditLog.user || "System",
    },
    {
        label: "Date",
        value: props.auditLog.formatted_date || props.auditLog.created_at || "-",
    },
]);

const compareValues = computed(() => {
    const before = oldValues.value;
    const after = newValues.value;

    if (!Object.keys(before).length && !Object.keys(after).length) {
        return [];
    }

    const allKeys = new Set([...Object.keys(before), ...Object.keys(after)]);

    return [...allKeys]
        .map((key) => {
            const oldValue = before[key];
            const newValue = after[key];

            if (JSON.stringify(oldValue) === JSON.stringify(newValue)) {
                return null;
            }

            return {
                field: key,
                old: oldValue,
                new: newValue,
            };
        })
        .filter(Boolean);
});

const formatValue = (value) => {
    if (value === null || value === undefined || value === "") {
        return "-";
    }

    if (typeof value === "object") {
        return JSON.stringify(value, null, 2);
    }

    return String(value);
};

const technicalDetails = computed(() => [
    {
        label: "Audit ID",
        value: props.auditLog.id,
    },
    {
        label: "IP Address",
        value: props.auditLog.ip_address || "N/A",
    },
    {
        label: "User Email",
        value: props.auditLog.user_email || "N/A",
    },
    {
        label: "Admin",
        value: props.auditLog.admin || "N/A",
    },
    {
        label: "Admin Email",
        value: props.auditLog.admin_email || "N/A",
    },
]);
</script>

<template>
    <AuthLayout title="Audit Log Details" heading="">
        <div class="audit-page">
            <section class="audit-hero card border-0 shadow-sm mb-4">
                <div class="card-body p-4 p-xl-5">
                    <div class="d-flex flex-column flex-xl-row justify-content-between gap-4 align-items-xl-start">
                        <div class="d-flex gap-3 align-items-start">
                            <Link :href="route('admin.audit-logs.index')" class="back-button">
                                <ArrowLeftIcon class="h-5 w-5" />
                            </Link>

                            <div>
                                <p class="hero-eyebrow mb-2">Audit Trail</p>
                                <h2 class="hero-title mb-2">Audit Log Details</h2>
                                <p class="hero-copy mb-3">
                                    Review the recorded action, actor details, request context, and any field-level changes captured for this activity.
                                </p>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="hero-chip hero-chip--module">
                                        {{ auditLog.module_label || auditLog.module || "General" }}
                                    </span>
                                    <span :class="actionClass">{{ actionText }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-4">
                        <div v-for="card in summaryCards" :key="card.label" class="col-12 col-sm-6 col-xl-3">
                            <div class="summary-card">
                                <span class="summary-label">{{ card.label }}</span>
                                <strong class="summary-value">{{ card.value }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="row g-4">
                <div class="col-12 col-xl-8">
                    <section class="card border-0 shadow-sm detail-card mb-4">
                        <div class="card-body p-4">
                            <div class="section-header mb-4">
                                <p class="section-eyebrow mb-1">Activity Summary</p>
                                <h4 class="mb-0">What happened</h4>
                            </div>

                            <div class="detail-grid">
                                <div class="detail-block detail-block--full">
                                    <span class="detail-label">Description</span>
                                    <p class="detail-value detail-value--large mb-0">
                                        {{ auditLog.description || "No description available for this entry." }}
                                    </p>
                                </div>

                                <div class="detail-block">
                                    <span class="detail-label">Performed By</span>
                                    <p class="detail-value mb-0">{{ auditLog.user || "System" }}</p>
                                </div>

                                <div class="detail-block">
                                    <span class="detail-label">Recorded At</span>
                                    <p class="detail-value mb-0">{{ auditLog.formatted_date || auditLog.created_at || "-" }}</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section v-if="compareValues.length" class="card border-0 shadow-sm detail-card mb-4">
                        <div class="card-body p-4">
                            <div class="section-header mb-4">
                                <p class="section-eyebrow mb-1">Field Comparison</p>
                                <h4 class="mb-0">Detected changes</h4>
                            </div>

                            <div class="changes-table-wrap">
                                <table class="changes-table">
                                    <thead>
                                        <tr>
                                            <th>Field</th>
                                            <th>Old Value</th>
                                            <th>New Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="change in compareValues" :key="change.field">
                                            <td class="field-name">{{ change.field }}</td>
                                            <td>
                                                <pre class="table-code table-code--old">{{ formatValue(change.old) }}</pre>
                                            </td>
                                            <td>
                                                <pre class="table-code table-code--new">{{ formatValue(change.new) }}</pre>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section class="card border-0 shadow-sm detail-card">
                        <div class="card-body p-4">
                            <div class="section-header mb-4">
                                <p class="section-eyebrow mb-1">Payload Details</p>
                                <h4 class="mb-0">Raw audit data</h4>
                            </div>

                            <div class="payload-grid">
                                <div v-if="hasNewValues" class="payload-panel">
                                    <div class="payload-header">
                                        <span>New Values</span>
                                    </div>
                                    <pre class="payload-code">{{ JSON.stringify(newValues, null, 2) }}</pre>
                                </div>

                                <div v-if="hasOldValues" class="payload-panel">
                                    <div class="payload-header">
                                        <span>Old Values</span>
                                    </div>
                                    <pre class="payload-code">{{ JSON.stringify(oldValues, null, 2) }}</pre>
                                </div>

                                <div v-if="auditLog.query" class="payload-panel payload-panel--full">
                                    <div class="payload-header">
                                        <span>Query Parameters</span>
                                    </div>
                                    <pre class="payload-code">{{ auditLog.query }}</pre>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="col-12 col-xl-4">
                    <section class="card border-0 shadow-sm detail-card mb-4">
                        <div class="card-body p-4">
                            <div class="section-header mb-4">
                                <p class="section-eyebrow mb-1">People</p>
                                <h4 class="mb-0">User context</h4>
                            </div>

                            <div class="person-stack">
                                <div class="person-card">
                                    <span class="person-role">Actor</span>
                                    <strong class="person-name">{{ auditLog.user || "System" }}</strong>
                                    <span class="person-meta">{{ auditLog.user_email || "No email available" }}</span>
                                </div>

                                <div class="person-card">
                                    <span class="person-role">Admin</span>
                                    <strong class="person-name">{{ auditLog.admin || "N/A" }}</strong>
                                    <span class="person-meta">{{ auditLog.admin_email || "No admin email available" }}</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="card border-0 shadow-sm detail-card">
                        <div class="card-body p-4">
                            <div class="section-header mb-4">
                                <p class="section-eyebrow mb-1">Technical</p>
                                <h4 class="mb-0">Request metadata</h4>
                            </div>

                            <div class="meta-list">
                                <div v-for="item in technicalDetails" :key="item.label" class="meta-row">
                                    <span class="meta-label">{{ item.label }}</span>
                                    <span class="meta-value">{{ item.value || "-" }}</span>
                                </div>
                            </div>

                            <div class="agent-panel mt-4">
                                <span class="detail-label">User Agent</span>
                                <p class="agent-value mb-0">{{ auditLog.user_agent || "N/A" }}</p>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<style scoped>
.audit-page {
    padding: 24px;
}

.audit-hero {
    border-radius: 28px;
    background:
        radial-gradient(circle at top right, rgba(56, 189, 248, 0.18), transparent 30%),
        linear-gradient(135deg, #f8fbff 0%, #eef7ff 55%, #ffffff 100%);
    overflow: hidden;
}

.back-button {
    width: 44px;
    height: 44px;
    min-width: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    background: #ffffff;
    color: #334155;
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
    transition: all 0.2s ease;
}

.back-button:hover {
    background: #e0f2fe;
    color: #0369a1;
}

.hero-eyebrow,
.section-eyebrow {
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: #64748b;
}

.hero-title {
    font-size: clamp(2rem, 3vw, 2.75rem);
    line-height: 1.05;
    font-weight: 700;
    color: #0f172a;
}

.hero-copy {
    max-width: 760px;
    font-size: 1rem;
    line-height: 1.75;
    color: #475569;
}

.hero-chip,
.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 38px;
    padding: 0.5rem 0.95rem;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 700;
}

.hero-chip--module {
    background: #dbeafe;
    color: #1d4ed8;
}

.status-pill--success {
    background: #dcfce7;
    color: #166534;
}

.status-pill--warning {
    background: #fef3c7;
    color: #92400e;
}

.status-pill--danger {
    background: #fee2e2;
    color: #991b1b;
}

.status-pill--info {
    background: #e0f2fe;
    color: #0369a1;
}

.status-pill--muted,
.status-pill--default {
    background: #e2e8f0;
    color: #475569;
}

.summary-card {
    height: 100%;
    padding: 1rem 1.15rem;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.82);
    border: 1px solid rgba(148, 163, 184, 0.22);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.45);
}

.summary-label {
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 0.55rem;
}

.summary-value {
    font-size: 1.1rem;
    color: #0f172a;
}

.detail-card {
    border-radius: 24px;
    overflow: hidden;
    background: #ffffff;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.detail-block {
    padding: 1rem 1.1rem;
    border-radius: 18px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}

.detail-block--full {
    grid-column: 1 / -1;
}

.detail-label {
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 0.55rem;
}

.detail-value {
    color: #0f172a;
    line-height: 1.7;
    word-break: break-word;
}

.detail-value--large {
    font-size: 1rem;
}

.changes-table-wrap {
    overflow-x: auto;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
}

.changes-table {
    width: 100%;
    min-width: 760px;
    border-collapse: collapse;
}

.changes-table thead th {
    padding: 1rem 1.1rem;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #64748b;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.changes-table tbody td {
    padding: 1rem 1.1rem;
    vertical-align: top;
    border-top: 1px solid #eef2f7;
}

.field-name {
    font-weight: 700;
    color: #0f172a;
    white-space: nowrap;
}

.table-code,
.payload-code {
    margin: 0;
    white-space: pre-wrap;
    word-break: break-word;
    font-size: 0.82rem;
    line-height: 1.7;
    font-family: "SFMono-Regular", Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}

.table-code {
    padding: 0.85rem;
    border-radius: 14px;
    min-height: 52px;
}

.table-code--old {
    background: #fff7ed;
    color: #9a3412;
}

.table-code--new {
    background: #ecfdf5;
    color: #166534;
}

.payload-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.payload-panel {
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    overflow: hidden;
    background: #ffffff;
}

.payload-panel--full {
    grid-column: 1 / -1;
}

.payload-header {
    padding: 0.95rem 1.1rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #475569;
}

.payload-code {
    padding: 1rem 1.1rem;
    background: #0f172a;
    color: #e2e8f0;
    max-height: 380px;
    overflow: auto;
}

.person-stack {
    display: grid;
    gap: 1rem;
}

.person-card {
    padding: 1rem 1.1rem;
    border-radius: 18px;
    background: linear-gradient(180deg, #f8fbff 0%, #f8fafc 100%);
    border: 1px solid #e2e8f0;
}

.person-role {
    display: inline-flex;
    margin-bottom: 0.55rem;
    padding: 0.3rem 0.65rem;
    border-radius: 999px;
    background: #e0f2fe;
    color: #0369a1;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.person-name {
    display: block;
    color: #0f172a;
    font-size: 1rem;
    margin-bottom: 0.35rem;
}

.person-meta {
    color: #64748b;
    font-size: 0.9rem;
    word-break: break-word;
}

.meta-list {
    display: grid;
    gap: 0.95rem;
}

.meta-row {
    display: grid;
    gap: 0.35rem;
    padding-bottom: 0.95rem;
    border-bottom: 1px solid #eef2f7;
}

.meta-row:last-child {
    padding-bottom: 0;
    border-bottom: 0;
}

.meta-label {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #64748b;
}

.meta-value,
.agent-value {
    color: #0f172a;
    word-break: break-word;
}

.agent-panel {
    padding: 1rem 1.1rem;
    border-radius: 18px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}

@media (max-width: 1199px) {
    .audit-page {
        padding: 18px;
    }
}

@media (max-width: 767px) {
    .audit-page {
        padding: 14px;
    }

    .detail-grid,
    .payload-grid {
        grid-template-columns: 1fr;
    }

    .detail-block--full,
    .payload-panel--full {
        grid-column: auto;
    }

    .hero-title {
        font-size: 1.9rem;
    }

    .changes-table {
        min-width: 680px;
    }
}
</style>
