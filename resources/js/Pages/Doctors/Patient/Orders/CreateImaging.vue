<script setup>
import axios from "axios";
import { useForm, router, usePage } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout2.vue";
import { ref, defineEmits, onMounted } from "vue";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import BaseDatePicker from "@/Components/Common/Input/BaseDatePicker.vue";
import BaseTagsInput from "@/Components/Common/Input/BaseTagsInput.vue";
import { routeOptions } from "@/Data/commonData";
import Search from "@/Components/Common/Search.vue";
import Swal from 'sweetalert2/dist/sweetalert2.js';
import 'sweetalert2/src/sweetalert2.scss';
import Modal from '@/Components/Common/Modal.vue';
import AddLab from '@/Pages/Modals/AddLab.vue'
import Template from './template.vue';
const props = defineProps({
     data: Object,
     
});
const parseIcd = (icd) => {
    if (icd) {
        return JSON.parse(icd);
    }
    return [];
}
const form = useForm({
    id: props.data?.order?.id || "",
    patient_id: props.data?.doctorId || "",
    doctor_id: props.data?.doctorId || "",
    encounter_id: props.data?.encounterId || "",
    address_id: props.data?.order?.address_id || "",
    encounter_provider: props.data?.order?.encounter_provider || "",
    insurance_id: props.data?.order?.insurance_id || "",
    radiology: props.data?.order?.radiology || "",
    radiology_icd: parseIcd(props.data?.order?.radiology_icd),
    notes: props.data?.order?.notes || "",
    pending_date: props.data?.order?.pending_date || "",
    is_completed: props.data?.order?.is_completed || "",
});

/* lab test order start */
const labs = ref([]);


const labActionAfterSaving = [
    { value: " ", label: "Only Save" },
    { value: "print", label: "Print" },
    { value: "print_queue", label: "Add to Print Queue" },
];


const emit = defineEmits();
/* search test */
const labLoader = ref(false);
const labSearchQuery = ref("");
const tests = ref([]);
const labSearch = () => {

    labLoader.value = true;
    const form = new FormData();
    form.append("search", labSearchQuery.value);
    axios.post(route('doctor.search.imaging'), form).then(response => {
        tests.value = response.data.message;
        labLoader.value = false;
    });
}

const selectTest = (row) => {
    form.radiology = row.value;
    tests.value = [];
}
  
// Add reactive variables for template management
const showTemplatePanel = ref(false);
const templateData = ref(null);
const templateCategory = ref('');
const templateField = ref('');
const handleTemplateUpdate = () => {
    // Refresh template data if needed
    if (templateCategory.value) {
        getDateMeta(templateCategory.value);
    }
}
// Handle template selection from the template component
const handleTemplateSelected = (templateItem) => {
    console.log('Template selected:', templateItem);
    
    // Determine which form field to update based on the category
    let fieldToUpdate = '';
    switch (templateCategory.value) {
        case 'orders_labs':
            fieldToUpdate = 'labs';
            break;
        case 'orders_notes':
            fieldToUpdate = 'notes';
            break;
        case 'orders_labs_icd':
            fieldToUpdate = 'labs_icd';
            break;
        // Add more cases as needed
        default:
            fieldToUpdate = templateField.value;
    }
    
    // Update the form field with the selected template
    if (fieldToUpdate && form[fieldToUpdate] !== undefined) {
        let currentValue = form[fieldToUpdate] || '';
        if (currentValue !== '') {
            currentValue += '\n';
        }
        form[fieldToUpdate] = currentValue + templateItem.text;
    }
}
const getDateMeta = (keyword) => {
    const form = new FormData();
    form.append('id', keyword);
    axios.post(route('doctor.get.templates'), form).then(response => {
        // Store the response data and show template panel
        templateData.value = response.data;
        templateCategory.value = keyword;
        templateField.value = keyword;
        showTemplatePanel.value = true;
    });
}
/* lab test order end */
const isValidated = ref(false);
const submitForm = () => {
    isValidated.value = true;
    form.post(route("doctor.orders.store"), {
        onSuccess: () => {
            router.get(route('doctor.orders.index'));
        },
    });
};
const isOpenLaboratoryProviderModal = ref(false);
const openLaboratoryProviderModal = () => {
      isOpenLaboratoryProviderModal.value = true
}
const closeLaboratoryProviderModal = () => {
    isOpenLaboratoryProviderModal.value = false
}
</script>

<template>
    <AuthLayout title="Orders" description="Patient create Orders Management" heading="Orders">
        <!-- Lab Order Section -->
        <div class="row">
            <div class="iq-card mb-3 col-md-8 p-0">
                <div class="iq-card-header d-flex justify-content-between align-items-center bg-primary">
                    <h5 class="mb-0 text-white">Imaging Order</h5>
                </div>
<form @submit.prevent="submitForm">

                        <div class="iq-card-body p-4">
                            <div class="row">

                                <div class="mb-4">
                                    <Search v-model="labSearchQuery" :searchResult="searchResult" :loader="labLoader"
                                        @input="labSearch" :placeholder="'Search lab test'" />
                                    <template v-for="row in tests" v-if="!labLoader">
                                        <p class="p-2 border-bottom bg-color-white-lilac cursor-pointer"
                                            @click="selectTest(row)">
                                            {{ row.label }}
                                        </p>
                                    </template>
                                    <template v-else>
                                        <div class="text-center p-4">
                                            <span v-if="labLoader" class="spinner-border spinner-border-sm"></span>
                                        </div>
                                    </template>
                                </div>
 
                                <template v-for="lab in labs">
                                    <div class="p-2 border-bottom bg-color-white-lilac cursor-pointer mt-2">
                                        <div class="row align-items-center">
                                            <div class="col-8">
                                                <p class="mb-0">
                                                    <span class="badge mr-2">{{ lab.labs }}</span>
                                                </p>
                                            </div>
                                            <div class="col-4 text-end">
                                                <button class="btn btn-danger" type="button" title="Delete"
                                                    @click="deleteLab(lab.id, 'labs')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <div class="col-md-12">
                                    <BaseInput v-model="form.radiology" label="Imaging Test" type="textarea" placeholder="Image test" required
                                        @click="getDateMeta('orders_labs')" />
                                </div>
                                <div class="col-md-12 mt-3">
                                    <BaseTagsInput v-model="form.radiology_icd" label="Diagnosis Codes" placeholder="Type diagnosis codes"
                                     />
                                </div>
                                <div class="col-md-6 mt-3">
                                    <BaseSelect v-model="form.encounter_provider" label="Laboratory Provider"
                                        placeholder="Search for provider"  :error="form.errors.encounter_provider" required>
                                        <option v-for="row in data.imaging" :key="row.id"
                                            :value="row.id">
                                            {{ row.name }}
                                        </option>
                                    </BaseSelect>
                                      <div class="text-primary cursor-pointer" @click="openLaboratoryProviderModal"><i class="bi bi-plus-circle mr-1"></i>Add Radiology Provider</div>

                                </div>
                                <div class="col-md-6 mt-3">
                                    <BaseDatePicker v-model="form.pending_date" label="Order Pending Date" placeholder="Select Date" />
                                </div>
                                <div class="col-md-12 mt-3">

                                    <BaseSelect v-model="form.insurance_id" label="Insurance"
                                        placeholder="Select Insurance" required>
                                        <option v-for="insurance in data.insurances" :key="insurance.id"
                                            :value="insurance.id">
                                            {{ insurance.insurance_company }} - {{ insurance.plan_name }}
                                        </option>
                                    </BaseSelect>
                                </div>
                                <div class="col-md-12 mt-3">
                                    <BaseInput v-model="form.notes" label="Notes" type="textarea"
                                        placeholder="Enter notes" @click="getDateMeta('orders_notes')" />
                                </div>
                                <div class="col-md-12 mt-3">
                                    <BaseSelect v-model="form.action_after_saving" label="Action After Saving"
                                        placeholder="Select an action" required>
                                        <option v-for="action in labActionAfterSaving" :key="action.value"
                                            :value="action.value">
                                            {{ action.label }}
                                        </option>
                                    </BaseSelect>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <button type="submit" class="btn  btn-primary">Save</button>
                                <button class="btn  btn-danger">Cancel</button>
                            </div>
                        </div>
                </form>
         </div>
        <!-- Right side - Template Panel -->
        <div class="col-md-4">
                <Template 
                    :data="templateData"
                    :category="templateCategory"
                    @template-selected="handleTemplateSelected"
                    @update-template="handleTemplateUpdate"
                />
            </div>
        </div>
          <Modal  :isOpen="isOpenLaboratoryProviderModal" :title="'Add Radiology Entry'" size="xl" @close="closeLaboratoryProviderModal" >
            <AddLab :labCategory="data.labCategory"  @close="closeLaboratoryProviderModal"/>
        </Modal>
    </AuthLayout>
</template>
