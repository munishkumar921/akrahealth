<script setup>
import { ref, watch } from "vue";
import { Link, useForm } from "@inertiajs/vue3";
 import Accordion from "../../Common/Accordion.vue";
import CloseButton from "@/Components/Common/Buttons/CloseButton.vue";
import axios from "axios";
import "../header.css";
 import AddPatientModal from "./AddPatientModal.vue";
import Modal from "../../Common/Modal.vue";
import { randomPassword } from "@/utils/password";

const password = randomPassword();

const searchForm = useForm({
    name: "",
});
const patientForm = useForm({
    role_id: 4,
    first_name: "",
    last_name: "",
    email: "",
    mobile: "",
    dob: "",
    sex: "",
    address: "",
    password: password,
    password_confirmation: password,
    type: "Patient",
});

const patients = ref([]);
const showAddPatientModal = ref(false);
const searchPatientError = ref(false);
let debounceTimer = null;
 

const closeAddPersonModal = () => {
    showAddPatientModal.value = false;
};

const searchPatient = () => {
    if (searchForm.name.trim()) {
        searchPatientError.value = false;
        axios
            .post(route("doctor.patient.list"), searchForm)
            .then((response) => {
                patients.value = response.data;
            })
            .catch((err) => {
                console.error("Error while retrieving patient list: ", err);
                searchPatientError.value = true;
            });
    } else {
        patients.value = [];
    }
};

const addPatient = () => {
    patientForm.post(route("signup"));
};

watch(
    () => searchForm.name,
    (newVal) => {
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }
        debounceTimer = setTimeout(() => {
            searchPatient();
        }, 300);
    }
);

</script>

<template>
       <div class="position-relative" v-if="!$page.props.selected_patient">
        <div v-if="patients.length" class="position-absolute w-100 p-2">
            <div v-for="(patient, index) in patients" :key="patient.id" class="mt-2  shadow-lg rounded">
                <Link :href="route('doctor.select.patient', patient.id)">
                    <Accordion :title="patient.name" :content="patient.name" :index="index" :toggleItem="() => { }">
                    </Accordion>
                </Link>
            </div>
        </div>
    </div>
    <div v-else class="patient-selector-card nav-card bg-primary text-white shadow-lg position-relative">
        <div class="patient-selector-content">
            <p class="patient-selector-name mb-0 text-white">
                {{ $page.props.selected_patient?.name }}
            </p>
            <Link :href="route('doctor.select.patient', 'empty')" class="patient-selector-close">
                <CloseButton />
            </Link>
        </div>
    </div>
     <Modal :isOpen="showAddPatientModal" title="Add Patient Details" @close="closeAddPersonModal" size="lg">
        <AddPatientModal @close="closeAddPersonModal" :form="patientForm" @submit="addPatient" />
    </Modal>
 
</template>

<style scoped>
.position-absolute {
    z-index: 3;
}

.patient-selector-card {
    width: min(100%, 420px);
    border-radius: 14px;
    padding: 0.55rem 0.75rem 0.55rem 1rem;
    display: flex;
    align-items: center;
}

.patient-selector-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    width: 100%;
    min-height: 42px;
}

.patient-selector-name {
    flex: 1;
    min-width: 0;
    display: flex;
    align-items: center;
    font-size: 1rem;
    font-weight: 600;
    line-height: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.patient-selector-close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-left: auto;
}

.expanded-view {
    bottom: -80px;
    left: 0;
}
</style>
