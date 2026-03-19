<script setup>
import { ref, onMounted } from 'vue';
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import Search from "@/Components/Common/Search.vue";
import DatePicker from "@/Components/Common/Input/BaseDatePicker.vue";
import axios from 'axios';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { data } from 'jquery';

const props = defineProps({
    form: Object,
    insurances: Object,
});

const billingForm = useForm({
    id: "",
    hospital_id: props.form.hospital_id,
    encounter_id: props.form.id,
    cpt: '',
    cpt_charge: '',
    unit: 1,
    modifier: '',
    service_start: '',
    service_end: '',
    icd_pointer: '',
    insurance_id_1: '',
    insurance_id_2: '',
});

const saveBilling = () => {

    axios.post(route('doctor.upsert.billing.code'), billingForm).then(response => {
        billingData(response.data);
        toast('Billing information saved successfully.');
    }).catch(error => {
        toast('An error occurred while saving billing information.');
    });
}

const billingData = (data) => {

    let billingCore = data.billingCode || {};
    let billing = data.billing || {};
    billingForm.hospital_id = billingCore.hospital_id || props.form.hospital_id;
    billingForm.encounter_id = billingCore.encounter_id || props.form.id;
    billingForm.id = billingCore.id || "";
    billingForm.cpt = billingCore.cpt || '';
    billingForm.cpt_charge = billingCore.cpt_charge || '';
    billingForm.unit = billingCore.unit || 1;
    billingForm.modifier = billingCore.modifier || '';
    billingForm.service_start = billingCore.service_start || '';
    billingForm.service_end = billingCore.service_end || '';
    billingForm.icd_pointer = billingCore.icd_pointer || '';
    billingForm.insurance_id_1 = billing.insurance_id_1 || '';
    billingForm.insurance_id_2 = billing.insurance_id_2 || '';
}

onMounted(() => {
    axios.get(route('doctor.get.billing.code', props.form.id)).then(response => {
        billingData(response.data);
    });
});


const loader = ref(false);
const searchQuery = ref("");
const cpt = ref([]);
const search = () => {

    loader.value = true;
    const form = new FormData();
    form.append("search_cpt", searchQuery.value);
    axios.post(route('doctor.search.cpt'), form).then(response => {
        cpt.value = response.data.message
        loader.value = false;

    }).catch(error => {
        loader.value = false;
    });
};

const selectCPT = (row) => {
    billingForm.cpt = row.value;
    cpt.value = [];
    searchQuery.value = "";
}
</script>

<template>
    <div class="accordion accordion-clean" id="billing-accordion">
        <!-- What Section -->
        <div class="card mb-3">
            <div class="card-header bg-primary">
                <div class="card-title" data-toggle="collapse" data-target="#billing-what-collapse">
                    <h6 class="d-flex justify-content-between text-white">
                        What
                        <i class="fas fa-angle-right angle d-flex justify-content-end"></i>
                    </h6>
                </div>
            </div>
             <div id="billing-what-collapse" class="collapse show" data-parent="#billing-accordion">
                <div class="card-body">
                    <div class="row">

                        <div class="col-md-12 mb-5 ml-3 pr-5">
                            <Search v-model="searchQuery" :searchResult="searchResult" :loader="loader" @input="search"
                                :placeholder="'Search CPT'" />
                            <template v-for="row in cpt" v-if="!loader">
                                <p class="p-2 border-bottom bg-color-white-lilac cursor-pointer"
                                    @click="selectCPT(row)">
                                    {{ row.label }} [{{ row.value }}]
                                </p>
                            </template>
                            <template v-else>
                                <div class="text-center p-4">
                                    <span v-if="loader" class="spinner-border spinner-border-sm"></span>
                                </div>
                            </template>
                        </div>

                        <!-- Procedure Code -->
                        <div class="col-md-12">
                            <BaseInput v-model="billingForm.cpt" label="Procedure Code" placeholder="CPT code" />
                        </div>

                        <!-- Procedure Charge -->
                        <div class="col-md-6">
                            <BaseInput v-model="billingForm.cpt_charge" label="Procedure Charge" type="number"
                                placeholder="Enter charge" />
                        </div>

                        <!-- Units -->
                        <div class="col-md-3">
                            <BaseInput v-model="billingForm.unit" label="Unit(s)" type="number" placeholder="1" />
                        </div>

                        <!-- Modifier -->
                        <div class="col-md-3">
                            <BaseInput v-model="billingForm.modifier" label="Modifier" placeholder="e.g., 25" />
                        </div>

                        <!-- Date of Service From -->
                        <div class="col-md-6 mt-3">
                            <DatePicker v-model="billingForm.service_start" label="Date of Service From" placholder="Select Date" type="date" />
                        </div>

                        <!-- Date of Service To -->
                        <div class="col-md-6 mt-3">
                            <DatePicker v-model="billingForm.service_end" label="Date of Service To" placholder="Select Date" type="date" />
                        </div>

                        <!-- Diagnosis Pointer -->
                        <div class="col-md-12 mt-3">
                            <BaseSelect v-model="billingForm.icd_pointer" label="Diagnosis Pointer">
                                <option disabled value="">Nothing selected</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </BaseSelect>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" @click="saveBilling()" class="btn btn-sm btn-primary">Save</button>
                        <button class="btn btn-sm btn-danger">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Who Section -->
        <div class="card mb-3">
            <div class="card-header bg-primary">
                <div class="card-title collapsed" data-toggle="collapse" data-target="#billing-who-collapse">
                    <h6 class="d-flex justify-content-between text-white">
                        Who
                        <i class="fas fa-angle-right angle d-flex justify-content-end"></i>
                    </h6>
                </div>
            </div>
            <div id="billing-who-collapse" class="collapse" data-parent="#billing-accordion">
                <div class="card-body">
                    <div class="row">
                        <!-- Primary Insurance -->
                        <div class="col-md-6">
                            <BaseSelect v-model="billingForm.insurance_id_1" label="Primary Insurance"
                                placeholder="Select Insurance" required>
                                <option v-for="insurance in insurances" :key="insurance.id" :value="insurance.id">
                                    {{ insurance.insurance_company }} - {{ insurance.plan_name }}
                                </option>
                            </BaseSelect>
                        </div>

                        <!-- Secondary Insurance -->
                        <div class="col-md-6">
                            <BaseSelect v-model="billingForm.insurance_id_2" label="Secondary Insurance"
                                placeholder="Select Insurance" required>
                                <option v-for="insurance in insurances" :key="insurance.id" :value="insurance.id">
                                    {{ insurance.insurance_company }} - {{ insurance.plan_name }}
                                </option>
                            </BaseSelect>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" @click="saveBilling()" class="btn btn-sm btn-primary">Save</button>
                        <button class="btn btn-sm btn-danger">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
