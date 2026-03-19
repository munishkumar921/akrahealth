<script setup>
import axios from "axios";
import { useForm, router, usePage } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout2.vue";
import { ref, onMounted } from "vue";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import BaseDatePicker from "@/Components/Common/Input/BaseDatePicker.vue";
import InputError from "@/Components/InputError.vue";
import Modal from '@/Components/Common/Modal.vue';
import AddLab from "@/Pages/Modals/AddLab.vue";
import 'sweetalert2/src/sweetalert2.scss';
import BaseTagsInput from "@/Components/Common/Input/BaseTagsInput.vue";
import Template from './template.vue';

const props = defineProps({
    data: Object,
});

// Template management reactive variables
const showTemplatePanel = ref(false);
const templateData = ref(null);
const templateCategory = ref('');
const templateField = ref('');

const parseIcd = (icd) => {

    if (icd) {
        return JSON.parse(icd);
    }
    return [];
}

const form = useForm({
    patient_id: props.data?.doctorId || "",
    doctor_id: props.data?.doctorId || "",
    encounter_id: props.data?.encounterId || "",
    id: props.data?.order?.id || "",
    insurance_id: props.data?.order?.insurance_id || "",
    referrals: props.data?.order?.referrals || "",
    referrals_icd: parseIcd(props.data?.order?.referrals_icd),
    notes: props.data?.order?.notes || "",
    pending_date: props.data?.order?.pending_date || "",
    pending_action: props.data?.order?.pending_action || "save_only",
    is_completed: props.data?.order?.is_completed || "",
    encounter_provider: props.data?.order?.encounter_provider || "",
});

/* Template Management Functions */
const getDateMeta = (keyword, field = null) => {
    const formData = new FormData();
    formData.append('id', keyword);

    axios.post(route('doctor.get.templates'), formData).then(response => {
        templateData.value = response.data;
        templateCategory.value = keyword;
        templateField.value = field || keyword;
        showTemplatePanel.value = true;
    });
}

// Handle template selection from the template component
const handleTemplateSelected = (templateItem) => {
    if (templateItem.inputType === 'orders_referrals') {
        form.referrals = templateItem.text;
        if (templateItem.orders) {
            form.type = templateItem.orders.specialty;
            form.doctor_id = templateItem.orders.doctor_id;
        }
    } else {
        // Determine which form field to update based on the category
        let fieldToUpdate = '';
        switch (templateCategory.value) {
            case 'orders_referrals':
                fieldToUpdate = 'referrals';
                break;
            case 'orders_notes':
                fieldToUpdate = 'notes';
                break;
            case 'orders_referrals_icd':
                fieldToUpdate = 'referrals_icd';
                break;
            default:
                fieldToUpdate = templateField.value;
        }

        // Update the form field with the selected template
        if (fieldToUpdate && form[fieldToUpdate] !== undefined) {
            let currentValue = form[fieldToUpdate] || '';

            // Apply different formatting based on field type
            if (fieldToUpdate === 'referrals_icd') {
                // For ICD codes, add comma separation
                if (currentValue) {
                    form[fieldToUpdate] = currentValue + ', ' + templateItem.text;
                } else {
                    form[fieldToUpdate] = templateItem.text;
                }
            } else {
                // For other fields, add new line
                if (currentValue !== '') {
                    currentValue += '\n';
                }
                form[fieldToUpdate] = currentValue + templateItem.text;
            }
        }
    }
}

const handleTemplateUpdate = () => {
    // Refresh template data if needed
    if (templateCategory.value) {
        getDateMeta(templateCategory.value);
    }
}

/* Form Submission */
const isValidated = ref(false);
const submitForm = () => {
    isValidated.value = true;
    form.post(route("doctor.orders.store"), {
        onSuccess: () => {
            router.get(route('doctor.orders.index'));
        },
    });
};

/* Try to load main templates on mount */
onMounted(() => {
    // You can pre-load a specific template category if needed
    // getDateMeta('orders_referrals');
});

</script>

<template>
    <AuthLayout title="Orders" description="Patient create Orders Management" heading="Orders">
        <!-- Referrals Order Section -->
        <div class="row">
            <div class="iq-card mb-3 col-md-8 p-0">
                <div class="iq-card-header d-flex justify-content-between align-items-center bg-primary">
                    <h5 class="mb-0 text-white">Add Referrals Order</h5>
                </div>
                <form @submit.prevent="submitForm">
                    <div class="p-4 row">
                        <!-- Referral Details -->
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <BaseInput type="textarea" label="Referral Details"
                                    placeholder="Click the template button or type referral details..."
                                    id="orders_referrals" v-model="form.referrals" required
                                    @click="getDateMeta('orders_referrals')" />
                            </div>
                        </div>

                        <!-- Diagnosis Codes -->
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <div class="d-flex align-items-center mb-2">
                                    <label class="form-label mb-0">Diagnosis Codes</label>

                                </div>
                                <BaseTagsInput id="orders_referrals_icd"
                                    placeholder="Type diagnosis codes or use templates..." v-model="form.referrals_icd"
                                     @click="getDateMeta('orders_referrals_icd')" />
                            </div>
                        </div>
                        <!-- Referral Provider -->
                        <div class="col-md-6 mb-3">
                            <div class="form-group">

                                <BaseSelect v-model="form.encounter_provider" label="Referral Provider"
                                    placeholder="Search for provider" required>
                                    <option v-for="row in data.referral" :key="row.id" :value="row.id">
                                        {{ row.name }}
                                    </option>
                                </BaseSelect>

                            </div>
                        </div>

                        <!-- Order Pending Date -->
                        <div class="col-md-6 mb-3">
                            <BaseDatePicker  label="Order Pending Date" id="orders_pending_date"
                                placeholder="Select date" v-model="form.pending_date" />
                            <InputError :message="form.errors.pending_date" class="mt-2" />
                        </div>
                        <!-- Insurance -->
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <BaseSelect v-model="form.insurance_id" label="Insurance" placeholder="Select Insurance"
                                    required>
                                    <option v-for="insurance in data.insurances" :key="insurance.id"
                                        :value=insurance.id>
                                        {{ insurance.insurance_company }} - {{ insurance.plan_name }}
                                    </option>
                                </BaseSelect>
                            </div>
                        </div>

                        <!-- Notes about Order -->
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <div class="d-flex align-items-center mb-2">
                                    <label class="form-label mb-0">Notes about Order</label>
                                </div>
                                <BaseInput type="textarea" id="orders_notes" placeholder="Add notes or use templates..."
                                    v-model="form.notes" @click="getDateMeta('orders_notes')" />
                            </div>
                        </div>

                        <!-- Action after Saving -->
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <BaseSelect id="nosh_action" name="nosh_action" label="Action after Saving"
                                    v-model="form.pending_action" required>
                                    <option value="save_only">Save Only</option>
                                    <option value="print_action">Print</option>
                                    <option value="print_queue">Add to Print Queue</option>
                                </BaseSelect>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="col-md-12">
                            <div class="form-group">
                                <div class="gap-2 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                        <span v-if="form.processing"
                                            class="spinner-border spinner-border-sm me-2"></span>
                                        {{ form.processing ? 'Saving...' : 'Save' }}
                                    </button>
                                    <button type="button" class="btn btn-danger"
                                        @click="router.get(route('doctor.orders.index'))">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Template Panel -->
            <div class="col-md-4">
                <Template :data="templateData" :category="templateCategory" @template-selected="handleTemplateSelected"
                    @update-template="handleTemplateUpdate" />
            </div>
            <Modal :isOpen="isCardiopulmonaryModalOpen" :title="'Add Referral Entry'" size="xl"
                @close="closeCardiopulmonaryModalOpen">
                <AddLab :labCategory="data.labCategory" @close="closeCardiopulmonaryModalOpen" />
            </Modal>
        </div>
    </AuthLayout>
</template>