<script setup>
import axios from "axios";
import { useForm, router } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout2.vue";
import { ref, defineEmits} from "vue";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import BaseTagsInput from "@/Components/Common/Input/BaseTagsInput.vue";
import BaseDatePicker from "@/Components/Common/Input/BaseDatePicker.vue";
import InputError from "@/Components/InputError.vue";
import 'sweetalert2/src/sweetalert2.scss';
import Modal from '@/Components/Common/Modal.vue';
import Template from './template.vue';
import AddLab from "@/Pages/Modals/AddLab.vue";
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
    cp: props.data?.order?.cp || "",
    cp_icd: parseIcd(props.data?.order?.cp_icd),
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
const isCardiopulmonaryModalOpen = ref(false);
const openCardiopulmonaryProviderModal = () => {
    isCardiopulmonaryModalOpen.value = true;
}
const closeCardiopulmonaryModalOpen = () => {
    isCardiopulmonaryModalOpen.value = false;
}
</script>

<template>
    <AuthLayout title="Orders" description="Patient create Orders Management" heading="Orders">
        <!-- Lab Order Section -->
        <div class="row">
            <div class="iq-card mb-3 col-md-8 p-0">
                <div class="iq-card-header d-flex justify-content-between align-items-center bg-primary">
                    <h5 class="mb-0 text-white">Add Cardiopulmonary Order</h5>
                    <button class="btn btn-primary bg-white text-primary" @click="Cardiopulmonary">
                        Add Cardiopulmonary Entry
                    </button>
                </div>
<form @submit.prevent="submitForm">

                    <div class="iq-card-body p-4">
                        <div class="row">
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
                                <BaseInput type="textarea" v-model="form.cp" label="Cardiopulmonary Test(s)"
                                    :type="'textarea'" placeholder="Cardiopulmonary Test(s)" required
                                    @click="getDateMeta('orders_cp')" />
                            </div>
                            <div class="col-md-12 mt-3">
                                <BaseTagsInput v-model="form.cp_icd" label="Diagnosis Codes"
                                    placeholder="Type diagnosis codes"  />
                            </div>
                            <div class="col-md-6 mt-3">
                                <BaseSelect v-model="form.encounter_provider" label="Cardiopulmonary Provider" placeholder="Select Cardiopulmonary Provider"
                                    required :error="form.errors.encounter_provider" >
                                    <option v-for="provider in data.cardiopulmonary" :key="provider.id"
                                        :value="provider.id">
                                        {{ provider.name }}
                                    </option>
                                </BaseSelect>
                                <div class="text-primary cursor-pointer" @click="openCardiopulmonaryProviderModal"><i
                                        class="bi bi-plus-circle mr-1"></i>Add Cardiopulmonary Provider</div>


                            </div>
                            <div class="col-md-6 mt-3">
                                <BaseDatePicker v-model="form.pending_date" label="Order Pending Date" placeholder="Select Date" />
                            </div>
                            <div class="col-md-12 mt-3">

                                <BaseSelect v-model="form.insurance_id" label="Insurance" placeholder="Select Insurance"
                                    required>
                                    <option v-for="insurance in data.insurances" :key="insurance.id"
                                        :value="insurance.id">
                                        {{ insurance.insurance_company }} - {{ insurance.plan_name }}
                                    </option>
                                </BaseSelect>
                                <InputError :message="form.errors.insurance_id" class="mt-2" />
                            </div>
                            <div class="col-md-12 mt-3">
                                <BaseInput v-model="form.notes" label="Notes about Order" type="textarea"
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
                                <InputError :message="form.errors.action_after_saving" class="mt-2" />
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <button class="btn  btn-danger">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
            <!-- Right side - Template Panel -->
            <div class="col-md-4">
                <Template :data="templateData" :category="templateCategory" @template-selected="handleTemplateSelected"
                    @update-template="handleTemplateUpdate" />
            </div>
        </div>
        <Modal :isOpen="isCardiopulmonaryModalOpen" :title="'Add Cardiopulmonary Entry'" size="xl"
            @close="closeCardiopulmonaryModalOpen">
            <AddLab :labCategory="data.labCategory" @close="closeCardiopulmonaryModalOpen" />
        </Modal>
    </AuthLayout>
</template>
