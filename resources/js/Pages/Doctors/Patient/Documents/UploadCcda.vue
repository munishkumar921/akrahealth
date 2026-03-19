<script setup>
import { ref, computed, onMounted } from "vue";
import { router } from '@inertiajs/vue3';
import AuthLayout from "@/Layouts/AuthLayout2.vue";
import axios from "axios";

const props = defineProps({
    data: Object,
    document: Object,
    mode: {
        type: String,
        default: 'reconciliation' // 'reconciliation' or 'comparison'
    }
});

const loading = ref(false);
const type = ref(props.data?.type || 'issues');
const selectedRecords = ref([]);
const selectAll = ref(false);
const showBulkImportModal = ref(false);
const importProgress = ref({ imported: 0, skipped: 0, errors: 0 });

const dropdown = [
    { label: "Conditions", value: "issues" },
    { label: "Medications", value: "medications" },
    { label: "Immunizations", value: "immunizations" },
    { label: "Allergies", value: "allergies" }
];

const columns = computed(() => {
    if (props.mode === 'comparison') {
        return [
            { label: "C-CDA Record", key: "ccda_record" },
            { label: "Match Status", key: "match_status" },
            { label: "Match Score", key: "match_score" },
            { label: "Existing Record", key: "existing_record" },
            { label: "Action", key: "action" }
        ];
    }
    return [
        { label: "Name", key: "name" },
        { label: "Reconciled", key: "reconciled" }
    ];
});

// Get records based on mode
const records = computed(() => {
    if (props.mode === 'comparison') {
        return props.data?.comparison || [];
    }
    return props.data?.records || [];
});

// Summary statistics
const summary = computed(() => {
    if (props.mode === 'comparison') {
        return props.data?.summary || { total_ccda: 0, duplicates: 0, new_records: 0 };
    }
    return {
        total: records.value.length,
        reconciled: records.value.filter(r => r.reconciled).length
    };
});

const selectType = (newType) => {
    type.value = newType;
    loading.value = true;
    GetUploadCcdaView(newType);
};

const GetUploadCcdaView = async (selectedType) => {
    try {
        const response = await axios.get(route('doctor.documents.uploadCcdaView', {
            id: props.document?.id || props.data?.document?.id,
            type: selectedType
        }));
        if (response.data) {
            router.reload({ only: ['data'] });
        }
    } catch (error) {
        console.error('Error fetching CCDA view:', error);
    } finally {
        loading.value = false;
    }
};

// Reconcile a single record
const reconcile = async (record, index) => {
    try {
        loading.value = true;
        await router.post(route('doctor.documents.setCcdaData', {
            patient: props.document?.patient_id || props.data?.document?.patient_id,
            document: props.document?.id || props.data?.document?.id,
            type: type.value,
            record_id: index,
            record_data: record,
            action: 'create'
        }), {}, {
            onSuccess: () => {
                // Refresh the view
                GetUploadCcdaView(type.value);
            }
        });
    } catch (error) {
        console.error('Error reconciling record:', error);
    } finally {
        loading.value = false;
    }
};

// Toggle record selection for bulk import
const toggleRecordSelection = (index) => {
    const pos = selectedRecords.value.indexOf(index);
    if (pos === -1) {
        selectedRecords.value.push(index);
    } else {
        selectedRecords.value.splice(pos, 1);
    }
};

// Toggle select all
const toggleSelectAll = () => {
    if (selectAll.value) {
        selectedRecords.value = records.value.map((_, index) => index);
    } else {
        selectedRecords.value = [];
    }
};

// Bulk import selected records
const bulkImport = async () => {
    if (selectedRecords.value.length === 0) {
        alert('Please select at least one record to import.');
        return;
    }

    try {
        loading.value = true;
        showBulkImportModal.value = true;

        const selectedData = selectedRecords.value.map(index => records.value[index]);

        const response = await axios.post(route('doctor.documents.bulkImportCcda'), {
            document: props.document?.id || props.data?.document?.id,
            type: type.value,
            records: selectedData
        });

        importProgress.value = {
            imported: response.data.imported || 0,
            skipped: response.data.skipped || 0,
            errors: response.data.errors || 0
        };

        // Refresh the view after successful import
        setTimeout(() => {
            GetUploadCcdaView(type.value);
            selectedRecords.value = [];
            selectAll.value = false;
        }, 2000);

    } catch (error) {
        console.error('Error during bulk import:', error);
        alert('Bulk import failed. Please try again.');
    } finally {
        loading.value = false;
    }
};

// Navigate to comparison view
const showComparison = () => {
    router.get(route('doctor.documents.compareCcdaRecords', {
        id: props.document?.id || props.data?.document?.id,
        type: type.value
    }));
};

// Format record for display
const formatRecord = (record) => {
    if (props.mode === 'comparison') {
        return formatCcdaRecord(record.ccda_record);
    }
    return formatCcdaRecord(record);
};

// Format C-CDA record for display
const formatCcdaRecord = (record) => {
    if (!record) return '—';

    // Issues/Conditions
    if (record.name || record.issue) {
        return record.name || record.issue;
    }

    // Medications
    if (record.medication || record.name) {
        let text = record.medication || record.name;
        if (record.dose) text += ` ${record.dose} ${record.unit || ''}`;
        if (record.sig) text += `, ${record.sig}`;
        if (record.route) text += `, ${record.route}`;
        if (record.frequency) text += `, ${record.frequency}`;
        if (record.reason) text += ` for ${record.reason}`;
        return text;
    }

    // Immunizations
    if (record.vaccine || record.immunization) {
        let text = record.vaccine || record.immunization;
        if (record.sequence) text += ` (#${record.sequence})`;
        return text;
    }

    // Allergies
    if (record.allergen || record.allergies_medicine) {
        let text = record.allergen || record.allergies_medicine;
        if (record.reaction) text += ` - ${record.reaction}`;
        return text;
    }

    // Fallback
    return record.label || JSON.stringify(record);
};

// Get match status class
const getMatchStatusClass = (isDuplicate) => {
    return isDuplicate ? 'badge-warning' : 'badge-success';
};

// Get match status text
const getMatchStatusText = (isDuplicate) => {
    return isDuplicate ? 'Duplicate Found' : 'New Record';
};

// Check if record is selected
const isSelected = (index) => {
    return selectedRecords.value.includes(index);
};

// Lifecycle
onMounted(() => {
    if (!props.data?.records && !props.data?.comparison) {
        // Initial load
        GetUploadCcdaView(type.value);
    }
});
</script>

<template>
    <AuthLayout title="C-CDA Reconciliation" description="C-CDA Reconciliation" heading="C-CDA Reconciliation">
        <div class="card p-4">
            <!-- Header Section -->
            <div class="d-flex justify-content-between items-center mb-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">
                        {{ mode === 'comparison' ? 'C-CDA Comparison' : 'C-CDA Reconciliation' }}
                    </h2>
                    <p class="text-muted small mb-0">
                        Document: {{ document?.description || 'Unknown' }} |
                        From: {{ document?.from || 'Unknown' }} |
                        Date: {{ document?.date || 'Unknown' }}
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-info" @click="showComparison" v-if="mode !== 'comparison'">
                        <i class="fa-solid fa-code-compare mr-1"></i> Compare with DB
                    </button>
                    <div class="dropdown">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            <i class="fa-solid fa-bars pointer mr-1"></i>
                            {{ dropdown.find(item => item.value === type)?.label || 'Select Type' }}
                        </button>
                        <div class="dropdown-menu">
                            <a v-for="item in dropdown" :key="item.value" class="dropdown-item pointer"
                                @click="selectType(item.value)">
                                {{ item.label }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Statistics -->
            <div class="row mb-4" v-if="mode === 'comparison'">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ summary.total_ccda }}</h3>
                            <small>Total C-CDA Records</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ summary.duplicates }}</h3>
                            <small>Potential Duplicates</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ summary.new_records }}</h3>
                            <small>New Records</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bulk Import Actions -->
            <div class="d-flex justify-content-between align-items-center mb-3" v-if="mode !== 'comparison'">
                <div>
                    <h5 class="text-success mb-1">
                        <i class="fa-solid fa-circle-info mr-2"></i>
                        {{ mode === 'comparison' ? 'Review and import records' : 'Rows in red come from the upload and need to be reconciled. Click on the row to accept.' }}
                    </h5>
                </div>
                <div class="d-flex gap-2" v-if="records.length > 0">
                    <button type="button" class="btn btn-outline-secondary" @click="toggleSelectAll">
                        {{ selectAll ? 'Deselect All' : 'Select All' }}
                    </button>
                    <button type="button" class="btn btn-success" @click="bulkImport" :disabled="selectedRecords.length === 0 || loading">
                        <i class="fa-solid fa-file-import mr-1"></i>
                        Import Selected ({{ selectedRecords.length }})
                    </button>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Processing...</p>
            </div>

            <!-- Records Table -->
            <div class="table-responsive" v-else>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th v-if="mode !== 'comparison'" class="text-center" style="width: 50px;">
                                <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" />
                            </th>
                            <th v-if="mode === 'comparison'" class="text-center" style="width: 50px;">#</th>
                            <th>{{ mode === 'comparison' ? 'C-CDA Record' : 'Record Details' }}</th>
                            <th v-if="mode === 'comparison'" class="text-center">Status</th>
                            <th v-if="mode === 'comparison'" class="text-center">Match Score</th>
                            <th v-if="mode === 'comparison'">Existing Record</th>
                            <th v-if="mode === 'comparison'" class="text-center">Action</th>
                            <th v-else class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(record, index) in records" :key="index"
                            :class="{ 'table-warning': isSelected(index), 'bg-light': record.is_duplicate }">
                            <!-- Checkbox for bulk selection -->
                            <td v-if="mode !== 'comparison'" class="text-center">
                                <input type="checkbox" :checked="isSelected(index)"
                                    @change="toggleRecordSelection(index)" />
                            </td>

                            <!-- Index -->
                            <td v-if="mode === 'comparison'" class="text-center text-muted">
                                {{ index + 1 }}
                            </td>

                            <!-- Record Details -->
                            <td>
                                <template v-if="mode === 'comparison'">
                                    <strong>{{ formatCcdaRecord(record.ccda_record) }}</strong>
                                    <div class="small text-muted" v-if="record.ccda_record?.code">
                                        Code: {{ record.ccda_record.code }}
                                    </div>
                                </template>
                                <template v-else>
                                    <span :class="{ 'text-danger font-weight-bold': !record.label }">
                                        {{ formatCcdaRecord(record) }}
                                    </span>
                                </template>
                            </td>

                            <!-- Match Status (comparison mode) -->
                            <td v-if="mode === 'comparison'" class="text-center">
                                <span class="badge" :class="getMatchStatusClass(record.is_duplicate)">
                                    {{ getMatchStatusText(record.is_duplicate) }}
                                </span>
                            </td>

                            <!-- Match Score (comparison mode) -->
                            <td v-if="mode === 'comparison'" class="text-center">
                                <div class="progress" style="height: 20px; min-width: 100px;">
                                    <div class="progress-bar" :class="{
                                        'bg-success': record.match_score >= 80,
                                        'bg-warning': record.match_score >= 50 && record.match_score < 80,
                                        'bg-danger': record.match_score < 50
                                    }" :style="{ width: record.match_score + '%' }">
                                        {{ record.match_score }}%
                                    </div>
                                </div>
                            </td>

                            <!-- Existing Record (comparison mode) -->
                            <td v-if="mode === 'comparison'">
                                <template v-if="record.is_duplicate && record.existing_record">
                                    <span class="text-muted">
                                        {{ formatCcdaRecord(record.existing_record) }}
                                    </span>
                                </template>
                                <template v-else>
                                    <span class="text-muted fst-italic">No match found</span>
                                </template>
                            </td>

                            <!-- Actions -->
                            <td v-if="mode === 'comparison'" class="text-center">
                                <button v-if="!record.is_duplicate" class="btn btn-sm btn-success"
                                    @click="reconcile(record.ccda_record, index)" :disabled="loading">
                                    <i class="fa-solid fa-plus mr-1"></i> Import
                                </button>
                                <span v-else class="text-muted small">
                                    <i class="fa-solid fa-check-circle text-success"></i> Exists
                                </span>
                            </td>
                            <td v-else class="text-center">
                                <button class="btn btn-sm btn-outline-primary" @click="reconcile(record, index)"
                                    :disabled="loading || record.label" :title="record.label ? 'Already in database' : 'Click to import'">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="!records || !records.length">
                            <td :colspan="mode === 'comparison' ? 6 : 3" class="text-center text-muted py-5">
                                <i class="fa-solid fa-folder-open fa-3x mb-3 text-gray-300"></i>
                                <p class="mb-0">No records found for this type.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Bulk Import Progress Modal -->
            <div class="modal fade" :class="{ show: showBulkImportModal }" tabindex="-1" v-if="showBulkImportModal">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Bulk Import Progress</h5>
                            <button type="button" class="close" @click="showBulkImportModal = false">&times;</button>
                        </div>
                        <div class="modal-body text-center py-4">
                            <div v-if="loading" class="mb-3">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Importing...</span>
                                </div>
                                <p class="mt-2">Importing records...</p>
                            </div>
                            <div v-else>
                                <div class="alert alert-success">
                                    <i class="fa-solid fa-check-circle fa-2x mb-2"></i>
                                    <h5>Import Complete!</h5>
                                </div>
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="h4 text-success mb-0">{{ importProgress.imported }}</div>
                                        <small>Imported</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="h4 text-warning mb-0">{{ importProgress.skipped }}</div>
                                        <small>Skipped</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="h4 text-danger mb-0">{{ importProgress.errors }}</div>
                                        <small>Errors</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="showBulkImportModal = false">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<style scoped>
.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.badge {
    padding: 0.5em 0.75em;
}

.table th {
    font-weight: 600;
    color: #4a5568;
    border-top: none;
}

.table td {
    vertical-align: middle;
}

.btn:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

.modal.show {
    display: block;
    background-color: rgba(0,0,0,0.5);
}

.dropdown-item.active,
.dropdown-item:active {
    background-color: #4a90d9;
}

.progress {
    font-size: 0.75rem;
}
</style>

