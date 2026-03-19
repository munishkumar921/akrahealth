<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";

import {
    medicationTabs,
} from "@/Data/MockData/medications";

import Modal from "@/Components/Common/Modal.vue";
import AddMedicationModal from "@/Pages/Modals/Medication.vue";
import PrescribeMedicationModal from "@/Pages/Modals/PrescribeMedication.vue";
import Table from "@/Components/Table/Table.vue";
import TabSelector from "@/Components/Table/Partials/TabSelector.vue";
import Swal from 'sweetalert2/dist/sweetalert2.js';
 
const props = defineProps({
     encounters: Array,
     medications: Object,
     keyword: String,
     pharmacies: Array,
});

const currentTab = ref("active");
const isAddModalOpen = ref(false);
const isPrescribeModalOpen = ref(false);
const childComponentRef = ref(null);

const medicationRows = computed(() => {
    const meds = props.medications;
    if (Array.isArray(meds)) return meds;
    if (meds && Array.isArray(meds.data)) return meds.data;
    return [];
});

const isMedicationActive = (medication) => !medication?.date_inactive;

const filteredMedicationRows = computed(() => {
    if (!medicationRows.value.length) return [];

    if (currentTab.value === "active") {
        return medicationRows.value.filter((m) => isMedicationActive(m));
    }

    if (currentTab.value === "inactive") {
        return medicationRows.value.filter((m) => !isMedicationActive(m));
    }

    return medicationRows.value;
});

const tableData = computed(() => {
    if (Array.isArray(props.medications)) {
        return { data: filteredMedicationRows.value };
    }

    return {
        ...(props.medications || {}),
        data: filteredMedicationRows.value,
    };
});

const updateCurrentTab = (newTab) => {
    currentTab.value = newTab;
};

const openAddMedicationModal = () => {
    isAddModalOpen.value = true;
    if (childComponentRef.value?.resetForm) {
        childComponentRef.value.resetForm();
    }
};

const edit = (medication) => {
    isAddModalOpen.value = true;
    setTimeout(() => {
        if (childComponentRef.value?.update) {
            childComponentRef.value.update(medication);
        }
    }, 10);
};

const closeAddMedicationModal = () => {
    isAddModalOpen.value = false;
};

const openPrescribeMedicationModal = () => {
    isPrescribeModalOpen.value = true;
};

const closePrescribeMedicationModal = () => {
    isPrescribeModalOpen.value = false;
};

const refillMedication = (medication) => {
    isPrescribeModalOpen.value = true;
    setTimeout(() => {
        if (childComponentRef.value?.update) {
            childComponentRef.value.update(medication);
        }
    }, 10);
};

const undoMedication = (id) => {
    router.post(route("doctor.medication.reconcile", { id }), {}, {
        preserveScroll: true,
    });
};

const MedicationStatus = (row) => {
    const nextType = isMedicationActive(row) ? 'inactive' : 'active';

    router.get(route("doctor.medication.status", { id: row.id, type: nextType }), {}, {
        preserveScroll: true,
        preserveState: false,
        replace: true,
    });
};

const deleteMedication = (id) => {
    Swal.fire(confirmSettings("Are you sure you want to delete this medication?"))
        .then((result) => {
            if (result.isConfirmed) {
                const deleteForm = useForm({});
                deleteForm.delete(route('doctor.medications.destroy', id), {
                    preserveScroll: true,
                });
            }
        });
};

const buttons = [
    {
        label: "Add",
        function: openAddMedicationModal,
        icon: "bi bi-plus-circle",
    },
    {
        label: "Prescribe",
        function: openPrescribeMedicationModal,
        icon: "bi bi-pencil-square",
    },
];

const columns = [
    { label: "Medication", key: "medication" },
    { label: "Date Active", key: "date_active" },
    { label: "Date Inactive", key: "date_inactive" },
    { label:"Due Date", key:"due_date" },
    { label: "Prescription Status", type:"status", key: "prescription" },
    { label: "Refills", key: "refills",type: "slot", slot: "refills"  },
    { label: "Status", key: "status", type: "slot", slot: "status" },
];
</script>

<template>
    <AuthLayout title="Medications" description="Manage medications" heading="Medications">
        <div class="medications-header">
            <h3 class="medications-title">Medications</h3>
            <div class="tab-selector-container">
                <TabSelector 
                    :tabs="medicationTabs" 
                    :currentTab="currentTab" 
                    @update:currentTab="updateCurrentTab"
                    :actionButtons="buttons" 
                />
            </div>
        </div>
        
        <Table 
            :columns="columns" 
            :search="keyword"
            :data="tableData"
        >
            <template #refills="{ row }">
                <div class="d-flex gap-1 justify-content-center">
                    <button class="btn btn-success" @click="refillMedication(row)"   data-tooltip="Refill">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                    <button class="btn btn-warning" @click="undoMedication(row.id)" data-tooltip="Undo" >
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </template>
            
            <template #status="{ row }">
                <label class="ah-switch">
                    <input type="checkbox" :checked="isMedicationActive(row)"
                        @change="MedicationStatus(row)" />
                    <span class="ah-slider">
                        <i class="bi bi-check2 toggle-check-icon"></i>
                    </span>
                </label>
            </template>
            
            <template #actions="{ row }">
                <button class="btn btn-primary" @click="edit(row)" data-tooltip="Edit">
                    <i class="bi bi-pencil-square"></i>
                </button>
                <button class="btn btn-danger" @click="deleteMedication(row.id)" data-tooltip="Delete">
                    <i class="bi bi-trash3"></i>
                </button>
            </template>
        </Table>

        <Modal :isOpen="isAddModalOpen" title="Add Medication" @close="closeAddMedicationModal" size="xl">
            <AddMedicationModal ref="childComponentRef" :encounters="encounters" @close="closeAddMedicationModal" />
        </Modal>

        <Modal :isOpen="isPrescribeModalOpen" title="Prescribe Medication" @close="closePrescribeMedicationModal"
            size="xl">
            <PrescribeMedicationModal ref="childComponentRef" :encounters="encounters" :pharmacies="pharmacies" @close="closePrescribeMedicationModal" />
        </Modal>
    </AuthLayout>
</template>

<style scoped>
/* Header Alignment - Mobile Responsive */
.medications-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
    gap: 1rem;
    flex-wrap: wrap;
}

.medications-title {
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
    .medications-header {
        flex-direction: column;
        align-items: stretch;
    }

    .medications-title {
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
    .medications-header {
        gap: 0.75rem;
    }

    .medications-title {
        font-size: 1.25rem;
    }
}

/* Extra Small Mobile */
@media (max-width: 480px) {
    .medications-header {
        gap: 0.5rem;
        padding: 0.5rem 0;
    }

    .medications-title {
        font-size: 1.1rem;
    }
}

/* Landscape mobile optimization */
@media (max-width: 768px) and (orientation: landscape) {
    .medications-header {
        flex-direction: row;
        align-items: center;
    }

    .medications-title {
        font-size: 1rem;
        text-align: left;
    }

    .tab-selector-container {
        justify-content: flex-end;
    }
}
</style>