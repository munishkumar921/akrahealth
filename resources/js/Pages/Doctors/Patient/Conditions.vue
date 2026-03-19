<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import Condition from '../../Modals/Condition.vue';
import { Link, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import Modal from '@/Components/Common/Modal.vue';
import Table from "@/Components/Table/Table.vue";
import Swal from 'sweetalert2/dist/sweetalert2.js';
import TabSelector from "@/Components/Table/Partials/TabSelector.vue";

const props = defineProps({
    issues: Object,
    keyword: String,
});

const form = useForm({
    keyword: props.keyword,
    record_type: "all",
});

const isAddModalOpen = ref(false);
const childComponentRef = ref();
const currentTab = ref("all");

// Check if a patient is selected
const selectedPatientId = computed(() => usePage().props.auth?.user?.doctor?.selected_patient_id);

const openAddMedicationModal = () => {
    isAddModalOpen.value = true;
    if (childComponentRef.value?.resetForm) {
        childComponentRef.value.resetForm();
    }
};

const closeAddMedicationModal = () => {
    isAddModalOpen.value = false;
};

const buttons = [
    {
        label: "Add Condition",
        function: openAddMedicationModal,
        icon: "bi bi-plus-circle",
    },
];

const tableColumns = [
    { label: "Issue", key: "issue" },
    { label: "Note", key: "notes" },
    { label: "Active Date", key: "date_active" },
    { label: "Inactive Date", key: "date_inactive" },
    { label: "Move to", key: "move_to", type: "slot", slot: "move" },
    { label: "Status", key: "status", type: "slot", slot: "status" },
];

const currentData = computed(() => {
    let list = [];

    if (Array.isArray(props.issues)) {
        list = props.issues;
    } else if (props.issues && Array.isArray(props.issues.data)) {
        list = props.issues.data;
    }

    switch (currentTab.value) {
        case "problem":
            return list.filter(item => item.type === 'Problem');
        case "past":
            return list.filter(item => item.type === 'MedicalHistory');
        case "surgery":
            return list.filter(item => item.type === 'SurgicalHistory');
        default:
            return list;
    }
});

const edit = (row) => {
    isAddModalOpen.value = true;
    setTimeout(() => {
        if (childComponentRef.value?.update) {
            childComponentRef.value.update(row);
        }
    }, 100);
};

const del = (id) => {
    Swal.fire(confirmSettings("Are you sure you want to delete this medication?"))
        .then((result) => {
            if (result.isConfirmed) {
                const deleteForm = useForm({});
                deleteForm.delete(route('doctor.conditions.destroy', id), {
                    preserveScroll: true,
                });
            }
        });
};

const visit = (url) => {
    router.visit(url);
};

const ConditionTabs = [
    { label: "All", value: "all" },
    { label: "Problems", value: "problem" },
    { label: "Past Medical History", value: "past" },
    { label: "Surgical History", value: "surgery" },
];

const updateCurrentTab = (newTab) => {
    currentTab.value = newTab;
    form.record_type = newTab;
};
</script>

<template>
    <AuthLayout title="Conditions" description="Manage patient conditions">
        <div class="conditions-header">
            <h3 class="conditions-title">Conditions</h3>
            <div class="tab-selector-container">
                <TabSelector 
                    :tabs="ConditionTabs" 
                    :currentTab="currentTab" 
                    @update:currentTab="updateCurrentTab"
                    :actionButtons="buttons" 
                />
            </div>
        </div>
         <!-- No patient selected message -->
        <div v-if="!selectedPatientId" class="alert alert-warning text-center py-4">
            <h5><i class="bi bi-exclamation-triangle-fill me-2"></i>No Patient Selected</h5>
            <p class="mb-0">Please select a patient from the patient list to view their conditions.</p>
        </div>

        <Table 
            v-else
            :columns="tableColumns" 
            :data="currentData.data ? currentData : { data: currentData }" 
            :search="keyword"
            class="mt-4"
        >
            <template #move="{ row }">
                <div class="d-flex gap-1 justify-content-center">
                    <template v-if="form.record_type === 'problem'">
                        <button type="button" class="btn btn-info"
                            @click="visit(route('doctor.move.condition', { id: row.id, type: 'MedicalHistory' }))"
                            title="Move to Medical History">
                            <i class="fa-solid fa-share"></i>
                        </button>
                        <button type="button" class="btn btn-dark"
                            @click="visit(route('doctor.move.condition', { id: row.id, type: 'SurgicalHistory' }))"
                            title="Move to Surgical History">
                            <i class="fa-sharp fa-solid fa-share"></i>
                        </button>
                    </template>

                    <template v-if="form.record_type === 'past'">
                        <button type="button" class="btn btn-info"
                            @click="visit(route('doctor.move.condition', { id: row.id, type: 'Problem' }))"
                            title="Move to Problem">
                            <i class="fa-solid fa-share"></i>
                        </button>
                        <button type="button" class="btn btn-dark"
                            @click="visit(route('doctor.move.condition', { id: row.id, type: 'SurgicalHistory' }))"
                            title="Move to Surgical History">
                            <i class="fa-sharp fa-solid fa-share"></i>
                        </button>
                    </template>

                    <template v-if="form.record_type === 'surgery'">
                        <button type="button" class="btn btn-info"
                            @click="visit(route('doctor.move.condition', { id: row.id, type: 'Problem' }))"
                            title="Move to Problem">
                            <i class="fa-solid fa-share"></i>
                        </button>
                        <button type="button" class="btn btn-dark"
                            @click="visit(route('doctor.move.condition', { id: row.id, type: 'MedicalHistory' }))"
                            title="Move to Medical History">
                            <i class="fa-sharp fa-solid fa-share"></i>
                        </button>
                    </template>
                </div>
            </template>

            <template #status="{ row }">
                <label class="ah-switch">
                    <input type="checkbox" :checked="!row.date_inactive"
                        @change="visit(route('doctor.condition.status', { id: row.id, type: row.date_inactive ? 'active' : 'inactive' }))" />
                    <span class="ah-slider">
                        <i class="bi bi-check2 toggle-check-icon"></i>
                    </span>
                </label>
            </template>

            <template #actions="{ row }">
                <button type="button" class="btn btn-primary " @click="edit(row)" title="Edit">
                    <i class="bi bi-pencil-square"></i>
                </button>
                <button type="button" class="btn btn-danger" @click="del(row.id)" title="Delete">
                    <i class="bi bi-trash3"></i>
                </button>
            </template>
        </Table>

        <Modal :isOpen="isAddModalOpen" title="Add Condition" @close="closeAddMedicationModal" size="xl">
            <Condition ref="childComponentRef" @close="closeAddMedicationModal" />
        </Modal>
    </AuthLayout>
</template>

<style scoped>
/* Header Alignment - Mobile Responsive */
.conditions-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
    gap: 1rem;
    flex-wrap: wrap;
}

.conditions-title {
    margin: 0;
    font-size: 1.75rem;
    font-weight: 600;
}

.tab-selector-container {
    display: flex;
    align-items: center;
    flex: 1;
    justify-content: flex-end;
    min-width: 0;
}

/* Tablet Responsiveness */
@media (max-width: 991px) {
    .conditions-header {
        flex-direction: column;
        align-items: stretch;
    }

    .conditions-title {
        font-size: 1.5rem;
        text-align: center;
    }

    .tab-selector-container {
        justify-content: center;
        width: 100%;
    }
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .conditions-header {
        gap: 0.75rem;
    }

    .conditions-title {
        font-size: 1.25rem;
    }
}

/* Extra Small Mobile */
@media (max-width: 480px) {
    .conditions-header {
        gap: 0.5rem;
        padding: 0.5rem 0;
    }

    .conditions-title {
        font-size: 1.1rem;
    }
}

/* Landscape mobile optimization */
@media (max-width: 768px) and (orientation: landscape) {
    .conditions-header {
        flex-direction: row;
        align-items: center;
    }

    .conditions-title {
        font-size: 1rem;
        text-align: left;
    }

    .tab-selector-container {
        justify-content: flex-end;
    }
}
</style>