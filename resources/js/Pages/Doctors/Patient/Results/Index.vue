<script setup>
import { ref, computed } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import Table from "@/Components/Table/Table.vue";
import AuthLayout from "@/Layouts/AuthLayout2.vue";
import Modal from '@/Components/Common/Modal.vue';
import AddResult from '@/Pages/Modals/AddResult.vue';
import ResultReply from '@/Pages/Modals/ResultReply.vue';
import Swal from 'sweetalert2/dist/sweetalert2.js';

import { defineProps } from "vue";

const props = defineProps({
    results: Object,
    serach: Object,
    doctors: Array,
    encounter_vitals:Object,
    tests: Object,
});

const isChartModalOpen = ref(false);
const selectedVital = ref(null);
const patientId = computed(() => usePage().props.auth.user.doctor.selected_patient_id);
 
const onCellClick = ({ row, column }) => {
    // Debug: see which column was clicked
    console.log('Clicked column:', column.key);

    // Only trigger if we're on the Vital Signs tab and NOT clicking the date
    if (currentTab.value === 'Vital Signs' && column.key !== 'vital_date') {
        selectedVital.value = column.key;

        // ✅ Use route() helper to generate the URL correctly
        router.get(route('doctor.encounterVitalChat', { type: column.key }), {}, {
            preserveState: true,  // keeps your table & UI intact
            replace: true,        // prevents full page reload
        });
    }
};

const currentTab = ref('Laboratory')

const tabs = [
    { key: 'Laboratory', label: 'Laboratory', iconClass: 'icon-success', icon: 'fa-solid fa-vial' },
    { key: 'Imaging', label: 'Imaging', iconClass: 'icon-warning', icon: 'fa-solid fa-x-ray' },
    { key: 'Vital Signs', label: 'Vital Signs', iconClass: 'icon-secondary', icon: 'fa-solid fa-heart-pulse' },
];


// Modal refs
const isAddResultModalOpen = ref(false);
const isResultReplyModalOpen = ref(false);
const childComponentRef = ref(null);


// Modal open functions
const openAddResultModal = () => {
    isAddResultModalOpen.value = true;
};

const openResultReplyModal = () => {
    isResultReplyModalOpen.value = true;
};
// Modal close functions
const closeAddResultModal = () => {
    isAddResultModalOpen.value = false;
};

const closeResultReplyModal = () => {
    isResultReplyModalOpen.value = false;
};
const editResult = (result) => {

    setTimeout(() => {
        if (childComponentRef.value?.update) {
            childComponentRef.value.update(result);
        }
    }, 10);
    openAddResultModal();
};
const Search = () => {

}

const del = (id) => {
    Swal.fire(confirmSettings("Are you sure you want to delete this medication?"))
        .then((result) => {
            if (result.isConfirmed) {
                const deleteForm = useForm({});
                deleteForm.delete(route('doctor.results.destroy', id), {
                    preserveScroll: true,
                });
            }
        });
};
const showResult = (id) => {
    router.get(route('doctor.results.show', id));
}

const columns = [
    { label: "name", key: "name" },
     { label: 'Date', key: 'created_at'},
];


// new: normalized results array (handles array or { data: [...] } shapes)
const resultsArray = computed(() => {
    if (!props.results) return [];
    if (Array.isArray(props.results)) return props.results;
    if (props.results.data && Array.isArray(props.results.data)) return props.results.data;
    if (props.results.message && Array.isArray(props.results.message)) return props.results.message;
    // fallback: try to extract enumerable values
    return Object.values(props.results).flat().filter(Boolean);
});
// ...existing code...
const currentData = computed(() => {
    const list = resultsArray.value || [];

    switch (currentTab.value) {
        case 'Laboratory':
            return list.filter(item => item && item.type === 'Laboratory');

        case 'Imaging':
            return list.filter(item => item && item.type === 'Imaging');
        default:
            return [];
    }
});
const columnsTwo = [
  { label: 'Date', key: 'vital_date'},
  { label: 'Weight', key: 'weight' },
  { label: 'Height', key: 'height' },
  { label: 'HC', key: 'hc' }, // Head Circumference
  { label: 'BMI', key: 'bmi' },
  { label: 'Temp', key: 'temp' },
  { label: 'SBP', key: 'sbp' }, // Systolic Blood Pressure
  { label: 'DBP', key: 'dbp' }, // Diastolic Blood Pressure
  { label: 'Pulse', key: 'pulse' },
  { label: 'Resp', key: 'resp' }, // Respiratory Rate
  { label: 'O2 Sat', key: 'o2_sat' } // Oxygen Saturation
];

</script>
<template>
    <AuthLayout title="Results" description="Results" heading="">
        <div class="row">
            <div class="col-lg-3">
                <div class="iq-card">
                    <div class="iq-card-body">
                        <div class="finance-menu">
                            <button v-for="tab in tabs" :key="tab.key" type="button" class="menu-item"
                                :class="{ active: currentTab === tab.key }" @click="currentTab = tab.key">
                                <i :class="tab.icon + ' ' + tab.iconClass"></i>

                                <span class="label">{{ tab.label }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card col-sm-9 p-3">
                <div class="align-items-center d-flex justify-content-between">
                    <div class="todo-date d-flex mr-3">
                        <h4 class="card-title">Results</h4>
                      
                    </div>
                    <div class="todo-notification d-flex align-items-center">
                        <div class="notification-icon position-relative d-flex align-items-center mr-3"></div>
                        <div class="btn-group ms-2">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <i class="fa-solid fa-square-plus pointer mr-1 "></i>
                            </button>
                            <div class="dropdown-menu">
                                <button @click="openAddResultModal" class="dropdown-item pointer">Add Result</button>
                                <button @click="openResultReplyModal" class="dropdown-item pointer">Result Reply To
                                    Patient</button>

                            </div>

                        </div>
                    </div>

                </div>
                  <div class="iq-card-body mt-3">
                    <Table v-if="currentTab ==='Laboratory' || currentTab === 'Imaging'" :columns="columns" :data="{ data: currentData }" :search="serach"> <template #actions="{ row }">
                            <div class="d-flex gap-1 justify-content-end">
                                <button class="btn btn-primary" data-placement="top" title="Edit"
                                    @click="editResult(row)">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-success" data-placement="top" title="view"
                                    @click="showResult(row.id)">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-danger" data-placement="top" title="Delete" @click="del(row.id)">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </template>
                    </Table>
                     <Table v-if="currentTab=='Vital Signs'" :columns="columnsTwo" :data="encounter_vitals" :search="serach" @cell-click="onCellClick">                        
                    </Table>

                </div>
            </div>
        </div>

        <Modal :isOpen="isAddResultModalOpen" title="Add Result" @close="closeAddResultModal" size="lg">
            <AddResult ref="childComponentRef" :doctors="doctors" @close="closeAddResultModal" />
        </Modal>
        <Modal :isOpen="isResultReplyModalOpen"  title="Result Reply To Patient" @close="closeResultReplyModal"
            size="lg">
            <ResultReply :tests="tests" ref="childComponentRef" @close="closeResultReplyModal" />
        </Modal>
        
    </AuthLayout>
</template>

<style scoped>
.finance-menu {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    background: #fff;
    border: 1px solid #eef0f4;
    border-radius: 12px;
    padding: 12px 14px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, .04);
    cursor: pointer;
    transition: .2s;
}

.menu-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 18px rgba(0, 0, 0, .06);
}

.menu-item.active {
    border-color: #6f42c1;
    box-shadow: 0 10px 20px rgba(111, 66, 193, .15);
}

.icon {
    height: 10px;
    width: 10px;
    border-radius: 50%;
}

.icon-success {
    color: #28a745;
}

.icon-warning {
    color: #ffc107;
}

.icon-secondary {
    color: #ff7b29;
}

.icon-primary {
    color: #0d6efd;
}

.icon-dark {
    color: #2e2138;
}

.label {
    font-size: 14px;
    color: #2b2b2b;
    font-weight: 600;
}
</style>
