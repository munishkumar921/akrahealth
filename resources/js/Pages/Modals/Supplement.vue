<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";

import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import { routeOptions } from "@/Data/commonData";
import BaseDatePicker from "@/Components/Common/Input/BaseDatePicker.vue";
import Search from "@/Components/Common/Search.vue";
import axios from "axios";
const props = defineProps({
    route: Array,
    keyword: String,
    supplements: Object,
    encounters: Object,
});

const searchQuery = ref("");
const searchResult = ref([]);
const loader = ref(false);
const isValidated = ref(false);
const supplements = ref([]);


const form = useForm({
    id: "",
    patient_id: props.encounters?.patient_id || null,
    doctor_id: props.encounters?.doctor_id || null,
    encounter_id: props.encounters?.id || null,
    date_active: "",
    date_inactive: "",
    date_prescribed: "",
    supplement: "",
    dosage: "",
    dosage_unit: "",
    sig: "",
    route: "",
    frequency: "",
    instructions: "",
    quantity: "",
    reason: "",
    reconcile: "",
    action_after_saving: "",
});


const emit = defineEmits(["close", "submit"]);

const submitForm = () => {
    isValidated.value = true;
    form.post(route('doctor.supplements.store'), {
        onSuccess: () => {
            closeModal();
        },
        onError: () => {
            console.log("Error in form submission");
        },
    });
};

const closeModal = () => {
    emit("close");
};
const selectSupplement = (row) => {

    form.supplement = row.label;
    supplements.value = [];
}

const search = () => {

    loader.value = true;
    const form = new FormData();
    form.append("search_supplement", searchQuery.value);
    axios.post(route('doctor.search.supplement', 'Y'), form).then(response => {
        supplements.value = response.data.message;
        loader.value = false;
    });
}

const update = (medication) => {
    Object.keys(form).forEach(key => {
        if (medication[key] !== undefined) {
            form[key] = medication[key];
        }
    });
};

defineExpose({
    update,
    resetForm: () => form.reset(),
});
</script>

<template>
    <!-- ✅ Supplement Form -->
    <form @submit.prevent="submitForm" novalidate class="needs-validation" :class="{ 'was-validated': isValidated }">
        <div class="row">

            <div class="mb-4">
                <Search v-model="searchQuery" :searchResult="searchResult" :loader="loader" @input="search"
                    :placeholder="'Search for Icd10'" />
                <template v-for="row in supplements" v-if="!loader">
                    <p class="p-2 border-bottom bg-color-white-lilac cursor-pointer" @click="selectSupplement(row)">
                        {{ row.label }}
                    </p>
                </template>
                <template v-else>
                    <div class="text-center p-4">
                        <span v-if="loader" class="spinner-border spinner-border-sm"></span>
                    </div>
                </template>
            </div>

            <div class="col-md-6">
                <BaseInput v-model="form.supplement" label="Supplement" placeholder="Search for supplement" required/>
            </div>
            <div class="col-md-3">
                <BaseInput v-model="form.dosage" type="number" label="Dosage" placeholder="e.g., 100" required />
            </div>
            <div class="col-md-3">
                <BaseInput v-model="form.dosage_unit" type="number" label="Dosage Unit" placeholder="e.g., mg" required/>
            </div>
            <div class="col-md-6">
                <BaseInput v-model="form.sig" label="Sig" placeholder="e.g., Take one daily" />
            </div>
            <div class="col-md-6">
                <BaseSelect v-model="form.route" label="Route" placeholder="Select Route" required>
                    <option v-for="route in routeOptions" :key="route" :value="route">
                        {{ route }}
                    </option>
                </BaseSelect>
            </div>
            <div class="col-md-6">
                <BaseInput v-model="form.frequency" label="Frequency" placeholder="e.g., Daily" />
            </div>
            <div class="col-md-6">
                <BaseInput v-model="form.instructions" label="Special Instructions"
                    placeholder="Enter special instructions" />
            </div>
            <div class="col-md-6">
                <BaseInput v-model="form.reason" label="Reason" placeholder="Enter reason" />
            </div>
            <div class="col-md-6">
                <BaseDatePicker v-model="form.date_active" label="Date Active" placeholder="Select date" />
            </div>
        </div>
        <div class="form-button mt-4 px-3 d-flex justify-content-end gap-3">
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-danger" @click="closeModal">
                Close
            </button>
        </div>
    </form>
</template>