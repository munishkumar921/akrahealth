<script setup>
import { ref } from "vue";
import { useForm } from '@inertiajs/vue3';
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import BaseDatePicker from "@/Components/Common/Input/BaseDatePicker.vue";
import { routeOptions } from "@/Data/commonData";
import axios from "axios";
import Search from "@/Components/Common/Search.vue";

const props = defineProps({
    encounters: Object,
    medicationData: Object,
    pharmacies: Array,

});


const emit = defineEmits(["close", "submit"]);

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

 

const actionOptions = [
    { value: "sign", label: "Electronically Sign" },
    { value: "print", label: "Print Prescription" },
];

const searchQuery = ref("");
const searchResult = ref([]);
const loader = ref(false);

const form = useForm({
  id: props.medicationData?.id ?? '',
  patient_id: props.encounters?.patient_id ?? '',
  doctor_id: props.encounters?.doctor_id ?? '',
  encounter_id: props.encounters?.id ?? '',
  prescriptions: 'pending',
  pharmacy_id: '',
  medication: '',
  dosage: '',
  dosage_unit: '',
  sig: '',
  route: '',
  frequency: '',
  instructions: '',
  reason: '',
  date_active: '',
  days: '',
  quantity: '',
  refills: '',
  notification: '',
  nosh_action: '',
});



const addPrescription = () => {
    form.post(route('doctor.medications.store'), {
        onSuccess: () => {
            closeModal();
        },
        onError: (errors) => {
            console.log(errors);
        }
    });

};

const closeModal = () => {
    emit("close");
};
const search = () => {
    loader.value = true;

    axios.get(route('doctor.search.rx', { search: searchQuery.value })).then((response) => {
        searchResult.value = response.data;
        loader.value = false;
    }).catch((error) => {
        loader.value = false;
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
    <div class="mb-4">
        <Search v-model="searchQuery" :searchResult="searchResult" :loader="loader" @input="search"
            :placeholder="'Search for Medication'" />

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
            <p>{{ searchResult.message }} </p>
        </div>
    </div>

    <form @submit.prevent="addPrescription" class="mt-4">
        <div class="row">
             
            <div class="col-md-6">
                <BaseInput v-model="form.medication" label="Medication" required />
            </div>

            <div class="col-md-6">
                <BaseInput v-model="form.dosage" label="Dosage" required />
            </div>

            <div class="col-md-6">
                <BaseSelect v-model="form.dosage_unit" label="Dosage Unit" required>
                    <option v-for="option in dosageUnits" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </BaseSelect>
            </div>

            <div class="col-md-6">
                <BaseInput v-model="form.sig" label="Sig" placeholder="Usage instructions" />
            </div>

            <div class="col-md-6">
                <BaseSelect v-model="form.route" label="Route">
                   <option v-for="route in routeOptions" :key="route" :value="route">
                {{ route }}
            </option>
                </BaseSelect>
            </div>

            <div class="col-md-6">
                <BaseSelect v-model="form.frequency" label="Frequency">
                    <option v-for="freq in frequencies" :key="freq.value" :value="freq.value">
                        {{ freq.label }}
                    </option>
                </BaseSelect>
            </div>

            <div class="col-md-6">
                <BaseInput v-model="form.instructions" label="Special Instructions" />
            </div>

            <div class="col-md-6">
                <BaseInput v-model="form.reason" label="Reason" />
            </div>

            <div class="col-md-6">
                <BaseDatePicker v-model="form.date_active" label="Date Active" />
            </div>

            <div class="col-md-6">
                <BaseInput v-model="form.days" label="Duration (days)" type="number" />
            </div>

            <div class="col-md-6">
                <BaseInput v-model="form.quantity" label="Quantity" type="number" />
            </div>

            <div class="col-md-6">
                <BaseInput v-model="form.refill" label="Refills" type="number" />
            </div>

            <div class="col-md-6">
                <BaseSelect v-model="form.pharmacy_id" label="Pharmacy to Send">
                    <option v-for="pharmacy in pharmacies" :key="pharmacy.id" :value="pharmacy.id">
                        {{ pharmacy.name }}
                    </option>
                </BaseSelect>
            </div>

            <div class="col-md-6">
                <BaseInput v-model="form.notification" label="Notification To (SMS or Email)" />
            </div>

            <!-- <div class="col-md-6">
                                <BaseSelect v-model="form.action_after_saving" label="Action After Saving">
                                    <option v-for="option in actionOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </BaseSelect>
                            </div> -->
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-primary" @click="addPrescription()">
                Save
            </button>
            <button type="button" class="btn btn-danger" @click="closeModal">
                Close
            </button>
        </div>
    </form>
</template>