<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import InputError from "@/Components/InputError.vue";
import Search from "@/Components/Common/Search.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import DatePicker from "@/Components/Common/Input/BaseDatePicker.vue";
import axios from "axios";

const props = defineProps({
    doctors: Array,
 });

const showModal = ref(false);
const searchQuery = ref("");
const searchResult = ref([]);
const loader = ref(false);

const form = useForm({
    id: "",
    patient_id: "",
    hospital_id: "",
    doctor_id: "",
    type: "",
    testName: "",
    result: "",
    result_units: "",
    normal_reference_range: "",
    flag: "",
    date: "",
    location: "",
    loinc_code: "",
});

const openModal = () => {
    showModal.value = true;
    // Prevent body scroll when modal is open
    document.body.style.overflow = 'hidden';
};

const emit = defineEmits(["close", "submit"]);

const closeModal = () => {
    emit("close");
};
const search = async () => {
    loader.value = true;
    try {
        const response = await axios.get(route("doctor.search.loinc", { search: searchQuery.value }));
        searchResult.value = response.data.message || [];
    } catch (error) {
        console.error("Error in search request:", error);
        searchResult.value = { message: "No results found." };
    } finally {
        loader.value = false;
    }
};
const handleClick = (item) => {
    form.testName = item.label;
    form.loinc_code = item.id;
    close();
};

const close = () => {
    searchQuery.value = '';
    searchResult.value = [];
    // form.reset();
};
const isValidated = ref(false);

const optionType = [
    { value: "Laboratory", label: "Laboratory" },
    { value: "Imaging", label: "Imaging" },

];
const flags = [
    { value: "below_low_normal", label: "Below low normal" },
    { value: "above_high_normal", label: "Above high normal" },
    { value: "below_low_panic_limits", label: "Below low panic limits" },
    { value: "above_high_panic_limits", label: "Above high panic limits" },
    { value: "below_absolute_low", label: "Below absolute low-off instrument scale" },
    { value: "above_absolute_high", label: "Above absolute high-off instrument scale" },
    { value: "normal", label: "Normal" },
    { value: "abnormal", label: "Abnormal" },
    { value: "very_abnormal", label: "Very abnormal" },
    { value: "significant_change_up", label: "Significant change up" },
    { value: "significant_change_down", label: "Significant change down" },
    { value: "better", label: "Better" },
    { value: "worse", label: "Worse" },
    { value: "susceptible", label: "Susceptible" },
    { value: "resistant", label: "Resistant" },
    { value: "intermediate", label: "Intermediate" },
    { value: "moderately_susceptible", label: "Moderately susceptible" },
    { value: "very_susceptible", label: "Very susceptible" }
];

const submit = () => {
    isValidated.value = true;

    form.post(route("doctor.results.store"), {
        onSuccess: () => {
            closeModal();
            form.reset();
            emit("submit");
            
        },

    });
};

const update = (result) => {
    form.id = result.id || '';
    form.patient_id = result.patient_id || '';
    form.hospital_id = result.hospital_id || '';
    form.doctor_id = result.doctor_id || '';

    // Update form fields
    form.type = result.type || '';
    form.testName = result.name || '';
    form.result = result.result || '';
    form.result_units = result.units || '';
    form.normal_reference_range = result.reference || '';
    form.flag = result.flags || '';
    form.date = result.time || '';
    form.location = result.location || '';
    form.loinc_code = result.code || '';
    form.doctor_id = result.doctor_id || '';

};

// Expose methods to parent component
defineExpose({
    update,
    openModal,
    closeModal,
    resetForm: () => form.reset(),

});
</script>
<template>
    <div class="mb-4">
        <!-- Search Input Component -->
        <Search v-model="searchQuery" :searchResult="searchResult" :loader="loader" @input="search"
            placeholder="Search Loinc tests" />

        <!-- Search Results -->
        <div v-if="Array.isArray(searchResult) && searchResult.length" class="p-3">
            <div class="search-result-list">
                <div v-for="(item, index) in searchResult" :key="index" class="search-result-item">
                    <div class="search-result-item-title">
                        <span @click="handleClick(item)" class="pointer">
                            {{ item.label }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State or Message -->
        <div v-else-if="searchResult && searchResult.message" class="ml-3">
            <p>{{ searchResult.message }}</p>
        </div>
    </div>

    <form @submit.prevent="submit" novalidate class="needs-validation" :class="{ 'was-validated': isValidated }">

        <div class="row mt-4">
            <div class="mb-3">
                <label for="test_type"> Test type</label>
                <BaseSelect v-model="form.type" placeholder="Select Test Type" required>
                    <option v-for="type in optionType" :key="route" :value="type.value">
                        {{ type.label }}
                    </option>
                </BaseSelect>
            </div>

            <div class="mb-3">
                <label>Test Name</label>
                <BaseInput v-model="form.testName" type="text" placeholder="Test Name" required />
                <InputError class="mt-2" :message="form.errors.testName" />
            </div>
        </div>
        <div class="row mt-3">
            <div class="col">
                <label>Result</label>
                <BaseInput v-model="form.result" placeholder="Result" required />
                <InputError class="mt-2" :message="form.errors.result" />
            </div>
            <div class="col">
                <label>Result Units</label>
                <BaseInput v-model="form.result_units" placeholder="Result Units   " />
                <InputError class="mt-2" :message="form.errors.result_units" />
            </div>
        </div>

        <div class="row mt-3">
            <div class="col">
                <label>Normal Reference Range</label>
                <BaseInput v-model="form.normal_reference_range" type="text" placeholder="Normal Reference Range"
                    required />
                <InputError class="mt-2" :message="form.errors.normal_reference_range" />
            </div>

            <div class="col">
                <label>Flag</label>
                <BaseSelect id="message_alert" v-model="form.flag">
                    <option value="">Select Flag</option>
                    <option v-for="flag in flags" :key="flag" :value="flag.value">
                        {{ flag.label }}
                    </option>
                </BaseSelect>
                <InputError class="mt-2" :message="form.errors.flag" />
            </div>
        </div>

        <div class="row mt-3">
            <div class="col">
                <label>Date of Test</label>
                <DatePicker v-model="form.date" placeholder="Date of Test" type="date" />
                <InputError class="mt-2" :message="form.errors.date" />
            </div>

            <div class="col">
                 <BaseInput v-model="form.location" type="text" label="Location" placeholder="Location" />
                <InputError class="mt-2" :message="form.errors.location" />
            </div>

        </div>

        <div class="row mt-3">
            <div class="col">
                <label>LOINC Code</label>
                <BaseInput v-model="form.loinc_code" type="text" placeholder="LOINC Code" />
                <InputError class="mt-2" :message="form.errors.loinc_code" />
            </div>
            <div class="col">
                <label>Provider</label>
                <BaseSelect v-model="form.doctor_id">
                    <option value="">Select Provider</option>
                    <option v-for="provider in doctors" :key="provider" :value="provider.id">
                        {{ provider?.user?.name }}

                    </option>
                </BaseSelect>
                <InputError class="mt-2" :message="form.errors.provider" />
            </div>
        </div>

        <div class="modal-footer">
            
            <button type="submit" class="btn btn-primary">
                Save
            </button>
            <button type="button" class="btn btn-danger" @click="closeModal">
                Close
            </button>
        </div>
    </form>

</template>