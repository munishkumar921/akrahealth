<script setup>
import { ref } from "vue";
import { useForm } from '@inertiajs/vue3';
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import BaseDatePicker from "@/Components/Common/Input/BaseDatePicker.vue";
 import { routeOptions } from "@/Data/commonData";
import Search from "@/Components/Common/Search.vue";
import inputError from "@/Components/InputError.vue";
import axios from "axios";

const props = defineProps({
    row: {},
    encounters:Object,
    
});
const emit = defineEmits(["close", "submit"]);
 
const searchQuery = ref("");
const searchResult = ref([]);
const loader = ref(false);

const dosageUnits = [
    { value: "mg", label: "mg" },
    { value: "ml", label: "mL" },
    { value: "g", label: "g" },
];
 
const frequencies = [
    { value: "daily", label: "Daily" },
    { value: "twice_daily", label: "Twice Daily" },
    { value: "as_needed", label: "As Needed (PRN)" },
    { value: "every_4_hours", label: "Every 4 Hours" },
    { value: "every_6_hours", label: "Every 6 Hours" },
];
const form = useForm({
    id: props.medicationData?.id || '',
    patient_id: props.encounters?.patient_id || '',
    doctor_id: props.encounters?.doctor_id || '',
    encounter_id: props.encounters?.id || '',
    pharmacy_id: "",
    medication: "",
    dosage: "",
    dosage_unit: "",
    sig: "",
    route: "",
    frequency: "",
    instructions: "",
    reason: "",
    date_active: "",
    errors: {}
    });
 
const submitForm = () => {
  form.post(route('doctor.medications.store'), {
        onSuccess: () => {
             closeModal();
        },
        onError: (errors) => {
            console.log(errors);
          form.errors = errors;
         }
    });    
  
 };

const closeModal = () => {
    emit("close");
};
const search = () => {
    loader.value=true;
    
     axios.post(route('doctor.search.rx', { search: searchQuery.value,  })).then((response) => {
        searchResult.value = response.data;
        loader.value=false;
    }).catch((error) => {
            loader.value=false;
            searchResult.value = [];
            console.error('Error in search request:', error);
        });
};
const close = () => {
    searchQuery.value = '';
    searchResult.value = [];
    // form.reset();
};
const handleClick = (item) => {
    // Close a modal or dropdown
    console.log(item.unit);
    form.medication = item.label;
    form.dosage = item.dosage;  
    form.dosage_unit = item.unit;
    
    close();
    // form.medication = item;
    
};
 

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
    <div class=" mb-4">
        <Search
            v-model="searchQuery"
            :searchResult="searchResult"
            :loader="loader"
            @input="search"
            :placeholder="'Search for RX'"/>
        <div v-if="searchResult.length > 0" class="p-3">
            <div class="search-result-list">
                <div v-for="(item, index) in searchResult" :key="index" class="search-result-item">
                    <div class="search-result-item-title">
                      
                        <span @click="handleClick(item)" class="pointer">{{ item.label }}</span>
                    </div>
                </div>
            </div>
        </div>
      
        <div v-else-if="searchResult.message" class="ml-3">
            <p >{{ searchResult.message }} </p>
        </div>
    </div>
        <!-- ✅ Medication Form -->
    <form @submit.prevent="submitForm">
        <div class="row">
            <div class="col-md-6 ">
                <BaseInput v-model="form.medication" label="Medication" required />
                <inputError :message="form.errors.medication" />
            </div>

            <div class="col-md-6">
                <BaseInput v-model="form.dosage" label="Dosage" required />
                <inputError :message="form.errors.dosage" />
            </div>

            <div class="col-md-6">
                <BaseSelect v-model="form.dosage_unit" label="Dosage Unit" required>
                    <option v-for="option in dosageUnits" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </BaseSelect>
                <inputError :message="form.errors.dosage_unit" />
            </div>

            <div class="col-md-6">
                <BaseInput v-model="form.sig" label="Sig" placeholder="Usage instructions" />
                <inputError :message="form.errors.sig" />
            </div>

            <div class="col-md-6">
                <BaseSelect v-model="form.route" label="Route">
                   <option v-for="route in routeOptions" :key="route" :value="route">
                {{ route }}
            </option>
                </BaseSelect>
                <inputError :message="form.errors.route" />
            </div>

            <div class="col-md-6">
                <BaseSelect v-model="form.frequency" label="Frequency">
                    <option v-for="freq in frequencies" :key="freq.value" :value="freq.value">
                        {{ freq.label }}
                    </option>
                </BaseSelect>
                <inputError :message="form.errors.frequency" />
            </div>

            <div class="col-md-6">
                <BaseInput v-model="form.instructions" label="Special Instructions" />
                <inputError :message="form.errors.instructions" />
            </div>

            <div class="col-md-6">
                <BaseInput v-model="form.reason" label="Reason" />
                <inputError :message="form.errors.reason" />
            </div>

            <div class="col-md-6">
                <BaseDatePicker v-model="form.date_active" type="date"  label="Date Active" />
                <inputError :message="form.errors.date_active" />
            </div>
             
        </div>


        <div class="form-button mt-4 px-3 d-flex justify-content-end gap-3">
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-danger" @click="closeModal">
                Cancel
            </button>
        </div>
    </form>
</template>