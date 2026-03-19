<script setup>
import axios from "axios";
import { useForm, router, usePage } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout2.vue";
import { ref, defineEmits, onMounted } from "vue";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import BaseDatePicker from "@/Components/Common/Input/BaseDatePicker.vue";
import BaseTagsInput from "@/Components/Common/Input/BaseTagsInput.vue";
 import Search from "@/Components/Common/Search.vue";
 import { Country, State } from "country-state-city";
 import 'sweetalert2/src/sweetalert2.scss';
 import Template from './template.vue';
const props = defineProps({
    data: Object,
});

const parseIcd = (value) => {
    if (!value) return [];
    if (Array.isArray(value)) return value;
    try {
        return JSON.parse(value);
    } catch (e) {
        return [];
    }
};

const form = useForm({
    id: props.data?.order?.id || "",
    patient_id: props.data?.patientId || "",
    doctor_id: props.data?.doctorId || "",
    encounter_id: props.data?.encounterId || "",
    type:props.data?.type||"",
    address_id: props.data?.order?.address_id || "",
    encounter_provider: props.data?.order?.encounter_provider || "",
    orders_date: props.data?.order?.orders_date || "",
    insurance_id: props.data?.order?.insurance_id || "",
    referrals: props.data?.order?.referrals || "",
    labs:props.data?.order?.labs||"",
    radiology: props.data?.order?.radiology || "",
    cp: props.data?.order?.cp || "",
    referrals_icd: props.data?.order?.referrals_icd || "",
    labs_icd: parseIcd(props.data?.order?.labs_icd),
    radiology_icd: props.data?.order?.radiology_icd || "",
    cp_icd: props.data?.order?.cp_icd || "",
    labs_obtained: props.data?.order?.labs_obtained || "",
    notes: props.data?.order?.notes || "",
    pending_date: props.data?.order?.pending_date || "",
    is_completed: props.data?.order?.is_completed || "",
    action_after_saving: " ",
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
    axios.post(route('doctor.search.loinc'), form).then(response => {
        tests.value = response.data.message;
        labLoader.value = false;
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

const selectTest = (row) => {
    form.labs = row.value;
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

</script>

<template>
    <AuthLayout title="Orders" description="Patient create Orders Management" heading="Orders">
        <!-- Lab Order Section -->
        <div class="row">
            <div class="iq-card mb-3 col-md-8 p-0">
                <div class="iq-card-header d-flex justify-content-between align-items-center bg-primary">
                    <h5 class="mb-0 text-white">Lab Test Order</h5>
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
                                        <div class="col-10">
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
                                <BaseInput v-model="form.labs" label="Test" type="textarea" placeholder="lab test"
                                    @click="getDateMeta('orders_labs')" />
                            </div>
                            <div class="col-md-12 mt-3">
                                <BaseTagsInput v-model="form.labs_icd" label="Diagnosis Codes" placeholder="Type diagnosis codes"
                                     />
                            </div>
                            <div class="col-md-6 mt-3">
                                <BaseInput v-model="form.encounter_provider" label="Laboratory Provider"
                                    placeholder="Search for provider" />
                            </div>
                            <div class="col-md-6 mt-3">
                                <BaseDatePicker v-model="form.pending_date" label="Order Pending Date" />
                            </div>
                            <div class="col-md-12 mt-3">

                                <BaseSelect v-model="form.insurance_id" label="Insurance"
                                    placeholder="Select Insurance" required>
                                    <option v-for="insurance in data.insurances" :key="insurance.id"
                                        :value="insurance.id">
                                        {{ insurance.insurance_company }} - {{ insurance.plan_name }}
                                    </option>
                                </BaseSelect>
                                <InputError :message="form.errors.insurance_id" class="mt-2" />
                            </div>
                            <div class="col-md-12 mt-3">
                                <BaseInput v-model="form.notes" label="Notes" type="textarea" placeholder="Enter notes"
                                    @click="getDateMeta('orders_notes')" />
                            </div>
                            <div class="col-md-12 mt-3">
                                <BaseSelect v-model="form.action_after_saving" label="Action After Saving"
                                    placeholder="Select an action" required>
                                    <option v-for="action in labActionAfterSaving" :key="action.value"
                                        :value="action.value">
                                        {{ action.label }}
                                    </option>
                                </BaseSelect>
                                <InputError :message="form.errors.action_after_saving" class="mt-2" />
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="submit" class="btn btn-sm btn-primary">Save</button>
                            <button class="btn btn-sm btn-danger">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-4">
                <Template 
                    :data="templateData"
                    :category="templateCategory"
                    @template-selected="handleTemplateSelected"
                    @update-template="handleTemplateUpdate"
                />
            </div>
        </div>
    </AuthLayout>
</template>
