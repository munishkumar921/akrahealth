<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import { examplePatients } from "@/Data/MockData/patients";
import SearchPatient from "./Search/SearchPatient.vue";
import PatientList from "./Search/PatientList.vue";

const emit = defineEmits(["close"]);

const currentView = ref("Search");
const patientResults = ref([]);

const form = useForm({
    first_name: "",
    last_name: "",
    date_of_birth: "",
    phone_number: "",
    email: "",
    gender: "",
    mrn: "", // Medical Record Number (optional)
    ssn: "", // Social Security Number (optional)
});

const goToSearchView = () => {
    currentView.value = "Search";
};

const handleSelectedPatient = (patient) => {
    console.log("Selected patient:", patient);
};

const submitForm = () => {
    patientResults.value = examplePatients;
    currentView.value = "Result";
};

const closeModal = () => {
    emit("close");
};
</script>

<template>
    <SearchPatient
        @close="closeModal"
        @submit="submitForm"
        :form="form"
        v-if="currentView === 'Search'"
    />
    <PatientList
        :patients="patientResults"
        :goBack="goToSearchView"
        @selectPatient="handleSelectedPatient"
        v-else
    />
</template>
