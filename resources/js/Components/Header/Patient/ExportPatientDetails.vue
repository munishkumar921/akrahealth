<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import Accordion from "../../Common/Accordion.vue";
import axios from "axios";
import ChipsInput from "../../Common/Input/ChipsInput.vue";

const searchForm = useForm({
    name: "",
});

const patients = ref([]);
const selectedPatients = ref([]);

const exportPatientDetails = () => {
    console.info(selectedPatients.value);
};

const searchPatient = (name) => {
    if (name.trim()) {
        axios.defaults.headers = {
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
        };
        axios.post(route("admin.patient.list"), { name }).then((response) => {
            const selectedSlugs = selectedPatients.value.map((patient) => patient.slug);
            patients.value = response.data.filter(
                (patient) => !selectedSlugs.includes(patient.slug)
            );
        });
    } else {
        patients.value = [];
    }
};

const updateSelectedPatients = (items) => {
    selectedPatients.value = items;
};

const selectPatient = (patient) => {
    selectedPatients.value.push(patient);
    searchForm.name = "";
};
</script>

<template>
    <div class="position-relative">
        <!-- <div class="d-flex gap-2 align-items-center">
            <ChipsInput :formValue="searchForm" :selectedItems="selectedPatients" name="name"
                placeholder="Search Patient..." @add="searchPatient" @update:selectedItems="updateSelectedPatients" />

            <button class="btn btn-primary" data-tooltip="Export patient details" data-tooltip-location="bottom"
                @click="exportPatientDetails">
                <i class="bi bi-file-earmark-arrow-down h6"></i>
            </button>
        </div>

        <div v-if="patients.length" class="position-absolute w-100 p-2">
            <div v-for="(patient, index) in patients" :key="patient.id" class="mt-2 bg-transparent shadow-lg rounded">
                <Accordion :title="patient.name" :content="patient.name" :index="index" :toggleItem="() => { }">
                    <template #header-right>
                        <div @click="selectPatient(patient)" class="btn btn-primary">
                            <i class="bi bi-plus"></i>
                        </div>
                    </template>
                </Accordion>
            </div>
        </div> -->
    </div>
</template>

<style scoped>
.position-absolute {
    z-index: 3;
}
</style>