<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AuthLayout from "@/Layouts/AuthLayout.vue";
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ArrowLeftIcon, DocumentMagnifyingGlassIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    auditLog: Object,
});

const hasOldValues = computed(() => {
    return props.auditLog.old_values && Object.keys(props.auditLog.old_values).length > 0;
});

const hasNewValues = computed(() => {
    return props.auditLog.new_values && Object.keys(props.auditLog.new_values).length > 0;
});

const formatJson = (json) => {
    if (!json) return {};
    if (typeof json === 'string') {
        try {
            json = JSON.parse(json);
        } catch (e) {
            return {};
        }
    }
    return json;
};

const compareValues = computed(() => {
    if (!hasOldValues.value || !hasNewValues.value) return [];
    
    const oldVals = formatJson(props.auditLog.old_values);
    const newVals = formatJson(props.auditLog.new_values);
    const changes = [];
    
    const allKeys = new Set([...Object.keys(oldVals), ...Object.keys(newVals)]);
    
    allKeys.forEach(key => {
        const oldVal = oldVals[key];
        const newVal = newVals[key];
        
        if (JSON.stringify(oldVal) !== JSON.stringify(newVal)) {
            changes.push({
                field: key,
                old: oldVal !== undefined ? oldVal : null,
                new: newVal !== undefined ? newVal : null,
                changed: true
            });
        }
    });
    
    return changes;
});
</script>

<template>
    <AuthLayout :title="`Audit Log Details`" :heading="`Audit Log Details`">
        <div class="content-wrapper">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <div class="title-row">
                        <a :href="route('admin.audit-logs.index')" class="back-link">
                            <ArrowLeftIcon class="h-5 w-5" />
                        </a>
                        <h1>Audit Log Details</h1>
                    </div>
                    <p class="page-subtitle">View detailed information about this audit entry</p>
                </div>
            </div>

            <div class="detail-grid">
                <!-- Main Info Card -->
                <div class="detail-card ">
                    <div class="card-header ">
                        <h2>Activity Information</h2>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Audit ID</label>
                                <span class="info-value">{{ auditLog.id }}</span>
                            </div>
                            <div class="info-item">
                                <label>Module</label>
                                <span class="module-badge">{{ auditLog.module_label }}</span>
                            </div>
                            <div class="info-item">
                                <label>Action</label>
                                <span v-html="auditLog.formatted_action"></span>
                            </div>
                            <div class="info-item full-width">
                                <label>Description</label>
                                <span class="info-value">{{ auditLog.description || 'No description' }}</span>
                            </div>
                            <div class="info-item">
                                <label>Date & Time</label>
                                <span class="info-value">{{ auditLog.formatted_date }}</span>
                            </div>
                            <div class="info-item">
                                <label>IP Address</label>
                                <span class="info-value">{{ auditLog.ip_address || 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Info Card -->
                <div class="detail-card">
                    <div class="card-header">
                        <h2>User Information</h2>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <label>User</label>
                                <span class="info-value">{{ auditLog.user || 'System' }}</span>
                            </div>
                            <div class="info-item">
                                <label>User Email</label>
                                <span class="info-value">{{ auditLog.user_email || 'N/A' }}</span>
                            </div>
                            <div class="info-item">
                                <label>Performed By (Admin)</label>
                                <span class="info-value">{{ auditLog.admin || 'N/A' }}</span>
                            </div>
                            <div class="info-item">
                                <label>Admin Email</label>
                                <span class="info-value">{{ auditLog.admin_email || 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Changes Comparison Card -->
                <div v-if="compareValues.length > 0" class="detail-card full-width">
                    <div class="card-header">
                        <h2>Changes</h2>
                    </div>
                    <div class="card-body">
                        <div class="changes-table">
                            <table>
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
                                        <td class="old-value">{{ change.old !== null ? change.old : '-' }}</td>
                                        <td class="new-value">{{ change.new !== null ? change.new : '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Raw Data Card -->
                <div class="detail-card">
                    <div class="card-header">
                        <h2>Raw Data</h2>
                    </div>
                    <div class="card-body">
                        <div class="raw-data-section">
                            <div class="raw-data-item" v-if="hasNewValues">
                                <label>New Values</label>
                                <pre class="code-block">{{ JSON.stringify(formatJson(auditLog.new_values), null, 2) }}</pre>
                            </div>
                            <div class="raw-data-item" v-if="hasOldValues">
                                <label>Old Values</label>
                                <pre class="code-block">{{ JSON.stringify(formatJson(auditLog.old_values), null, 2) }}</pre>
                            </div>
                            <div v-if="auditLog.query" class="raw-data-item">
                                <label>Query Parameters</label>
                                <pre class="code-block">{{ auditLog.query }}</pre>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Agent Card -->
                <div class="detail-card">
                    <div class="card-header">
                        <h2>Technical Details</h2>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-item full-width">
                                <label>User Agent</label>
                                <span class="info-value small">{{ auditLog.user_agent || 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<style scoped>
.content-wrapper {
    padding: 24px;
    max-width: 1200px;
    margin: 0 auto;
}

.page-header {
    margin-bottom: 24px;
}

.title-row {
    display: flex;
    align-items: center;
    gap: 12px;
}

.back-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    background-color: #f3f4f6;
    color: #374151;
    border-radius: 8px;
    transition: background-color 0.2s;
}

.back-link:hover {
    background-color: #e5e7eb;
}

.page-title h1 {
    font-size: 24px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.page-subtitle {
    font-size: 14px;
    color: #6b7280;
    margin-top: 4px;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
}

.detail-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.detail-card.full-width {
    grid-column: 1 / -1;
}

.card-header {
    background:#09ACFF;
    padding: 16px 20px;
    color: white;
    border-bottom: 1px solid #e5e7eb;
}

.card-header h2 {
    font-size: 16px;
    font-weight: 600;
    color: #eff1f6;
    margin: 0;
}

.card-body {
    padding: 20px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.info-item.full-width {
    grid-column: 1 / -1;
}

.info-item label {
    font-size: 12px;
    font-weight: 500;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.info-value {
    font-size: 14px;
    color: #111827;
    word-break: break-word;
}

.info-value.small {
    font-size: 12px;
    color: #6b7280;
}

.module-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    background-color: #e0e7ff;
    color: #4338ca;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    width: fit-content;
}

.changes-table {
    overflow-x: auto;
}

.changes-table table {
    width: 100%;
    border-collapse: collapse;
}

.changes-table th,
.changes-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.changes-table th {
    background: #f9fafb;
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.changes-table td {
    font-size: 14px;
    color: #374151;
}

.changes-table .field-name {
    font-weight: 500;
    color: #111827;
}

.changes-table .old-value {
    color: #dc2626;
    text-decoration: line-through;
    opacity: 0.7;
}

.changes-table .new-value {
    color: #16a34a;
}

.raw-data-section {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.raw-data-item label {
    display: block;
    font-size: 12px;
    font-weight: 500;
    color: #6b7280;
    margin-bottom: 8px;
}

.code-block {
    background: #1f2937;
    color: #e5e7eb;
    padding: 12px;
    border-radius: 8px;
    font-size: 12px;
    overflow-x: auto;
    white-space: pre-wrap;
    word-break: break-all;
    margin: 0;
}

@media (max-width: 768px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }
}
</style>
