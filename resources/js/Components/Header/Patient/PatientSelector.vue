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
    <div v-else class="d-flex gap-3 align-items-center rounded p-2 nav-card bg-primary text-white shadow-lg w-auto position-relative">
        <div class="nav-card-content justify-content-start">
            <p class="px-4 rounded h6 col-10 text-white">
                  {{ $page.props.selected_patient?.name }}
            </p>
            <Link :href="route('doctor.select.patient', 'empty')" class="col-2">
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

.expanded-view {
    bottom: -80px;
    left: 0;
}
</style>
