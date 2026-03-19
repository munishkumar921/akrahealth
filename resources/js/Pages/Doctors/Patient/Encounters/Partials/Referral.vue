<script setup>
import Multiselect from "@vueform/multiselect";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import BaseDatePicker from "@/Components/Common/Input/BaseDatePicker.vue";
import BaseTagsInput from "@/Components/Common/Input/BaseTagsInput.vue";

import axios from "axios";
import { ref, defineEmits, onMounted, watch } from "vue";
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    form: Object,
    data: Object,
});

watch(() => props.form.orders_referrals, (newValue) => {
    if (newValue) referralForm.detail = newValue;
});
watch(() => props.form.referrals_icd, (newValue) => {
    if (newValue) referralForm.code = newValue;
});
watch(() => props.form.referral_notes, (newValue) => {
    if (newValue) referralForm.note = newValue;
});

const referralForm = useForm({
    id: "",
    encounter_id: props.form.id,
    patient_id: "",
    detail: "",
    code: "",
    specialty: "",
    doctor_id: "",
    pending_date: "",
    insurance_id: "",
    note: "",
    action_after_saving: "",
});

const saveReferral = () => {
    axios.post(route('doctor.upsert.referral'), referralForm).then(response => {
        assignData(response.data);
        props.form.referrals = '';
        props.form.referrals_icd = '';
        props.form.referral_notes = '';
        toast('Referral information saved successfully.');
    }).catch(error => {
        toast('An error occurred while saving referral information.');
    });
}

onMounted(() => {
    axios.get(route('doctor.get.referral', props.form.id)).then(response => {
        assignData(response.data);
        getDoctorsBySpecialty();
    });
});

const assignData = (data) => {
    referralForm.id = data.id || "";
    referralForm.detail = data.detail || "";
    referralForm.code = data.code || "";
    referralForm.specialty = data.specialty || "";
    referralForm.doctor_id = data.doctor_id || "";
    referralForm.pending_date = data.pending_date || "";
    referralForm.insurance_id = data.insurance_id || "";
    referralForm.note = data.note || "";
}

const isValidated = ref(false);

const emit = defineEmits();

const getDateMeta = (keyword) => {

    const form = new FormData();
    form.append('id', keyword);
    axios.post(route('doctor.get.templates'), form)
        .then(response => {
            const payload = {
                data: response.data,
                category: keyword,
                field: keyword
            };
            emit('templateData', payload);
        });
}

const specialtyOptions = ref(props.data.specialties);

const referralProviderOptions = ref([]);
const getDoctorsBySpecialty = () => {
    if (referralForm.specialty) {
        axios.get(route('doctor.get.doctors.by.specialty', referralForm.specialty))
            .then(response => {
                referralProviderOptions.value = response.data;
                // Auto-select the first provider if available
                if (response.data && response.data.length > 0) {
                    referralForm.doctor_id = response.data[0].id;
                } else {
                    referralForm.doctor_id = "";
                }
            });
    } else {
        referralProviderOptions.value = [];
        referralForm.doctor_id = "";
    }
}

</script>

<template>
<form>
        <div class="card">
            <div class="card-header bg-primary">
                <div class="card-title d-flex justify-content-between align-items-center">
                    <h6 class="d-flex justify-content-between text-white ">
                        Referral
                    </h6>
                 
                </div>

            </div>
            <div class="card-body">
                

                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="referral-details" class="form-label">Referral Details</label>
                        <textarea v-model="referralForm.detail" id="referral-details" class="form-control" rows="3"
                            placeholder="Enter referral details" @click="getDateMeta('orders_referrals')"></textarea>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Diagnosis Codes</label>
                        <BaseTagsInput v-model="referralForm.code" placeholder="Type of diagnosis" />
                    </div>
                     <div class="col-md-6 mb-3">
                         <label class="form-label">Select Specialty</label>
                        <select v-model="referralForm.specialty" class="form-select rounded"
                            @change="getDoctorsBySpecialty()">
                            <option value="" disabled selected>Select Specialty</option>
                            <option v-for="specialty in specialtyOptions" :key="specialty.id" :value="specialty.id">
                                {{ specialty.name }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Referral Provider</label>
                        <select v-model="referralForm.doctor_id" class="form-select rounded">
                            <option value="" disabled selected>Select Specialty</option>
                            <option v-for="doctor in referralProviderOptions" :key="doctor.id" :value="doctor.id">
                                {{ doctor.name }}
                            </option>   
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <BaseDatePicker v-model="referralForm.pending_date" label="Order Pending Date" />
                    </div>
                    <div class="col-md-6 mb-3">
                        <BaseSelect v-model="referralForm.insurance_id" label="Insurance" placeholder="Select Insurance"
                            required>
                            <option v-for="insurance in data.insurances" :key="insurance.id" :value="insurance.id">
                                {{ insurance.insurance_company }} - {{ insurance.plan_name }}
                            </option>
                        </BaseSelect>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="referral-notes" class="form-label">Notes about Order</label>
                        <textarea v-model="referralForm.note" id="referral-notes" class="form-control" rows="3"
                            placeholder="Enter notes" @click="getDateMeta('orders_referral_notes')"></textarea>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="button" @click="saveReferral()" class="btn btn-sm btn-primary">Save</button>
                    <button class="btn btn-sm btn-danger">Cancel</button>
                </div>
            </div>
        </div>
    </form>
</template>
