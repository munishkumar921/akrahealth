<script setup>
import { ref } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import Modal from "@/Components/Common/Modal.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import AddPatient from "./AddPatient.vue";
import PatientSummary from "@/Pages/Common/PatientSummary.vue";

import axios from "axios";
import Swal from "sweetalert2";
import { route } from "ziggy-js";


const props = defineProps({
    patients: Array,
    search: Object,
    countries: Array,
    states: Array,
});

const page = usePage();

const isAddUserModalOpen = ref(false);

const columns = [
    { label: "User Name", key: "name" },
    { label: "Email", key: "email" },
    { label: "Phone", key: "phone" },
    { label: "Created At", key: "created_at" },
     { label: "Register to Portal", key: "register_to_portal", type: "slot", slot: 'register_to_portal' },
];

const openAddUserModal = () => {
    isAddUserModalOpen.value = true;
};

const closeAddUserModal = () => {
    isAddUserModalOpen.value = false;
};

const buttons = [
    {
        label: "Add",
        function: openAddUserModal,
        icon: "bi bi-plus-circle",
    },
];

const extraButtons = [
    { title: "Sync Patients" },
    {
        label: "DrChrono",
        function: () => window.open("https://www.drchrono.com/", "_blank"),
    },
    {
        label: "Practice Fusion",
        function: () => window.open("https://www.practicefusion.com/", "_blank"),
    },
];
const processing = ref({});

const register = async (patientId) => {
    processing.value[patientId] = true;

    try {
        const response = await axios.post(
            route("doctor.patient.register"),
            { patient_id: patientId }
        );

        if (response.data.success) {
            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "success",
                title: response.data.success,
                showConfirmButton: false,
                timer: 3000,
            });
        } else {
            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "error",
                title: response.data.message,
                showConfirmButton: false,
                timer: 3000,
            });
        }
    } catch (error) {
        Swal.fire({
            toast: true,
            position: "top-end",
            icon: "error",
            title:
                error.response?.data?.message ??
                "Something went wrong",
            showConfirmButton: false,
            timer: 3000,
        });
    } finally {
        processing.value[patientId] = false;
    }
};
const patientDetails = (id) => {
    const selectedPatientId = page?.props?.selected_patient?.id ?? null;

    if (selectedPatientId && selectedPatientId === id) {
        router.get(route("doctor.demographics"));
    } else {
        Swal.fire({
            toast: true,
            position: "top-end",
            icon: "info",
            title: "Please select a patient first",
            showConfirmButton: false,
            timer: 3000,
        });
    }
};
const isLoadingPatientData = ref(false);
const showPatientSummary = ref(false);
const patientData = ref({});

const patientSummary = (id) => {
    const selectedPatientId = page?.props?.selected_patient?.id ?? null;

    if (selectedPatientId && selectedPatientId === id) {
          isLoadingPatientData.value = true;

    axios
        .get(route("doctor.patient.summary"))
        .then((res) => {
            if (res?.data) patientData.value = res.data;
            showPatientSummary.value = true;
        })
        .catch(() => {
            showPatientSummary.value = true;
        })
        .finally(() => {
            isLoadingPatientData.value = false;
        });
     } else {
        Swal.fire({
            toast: true,
            position: "top-end",
            icon: "info",
            title: "Please select a patient first",
            showConfirmButton: false,
            timer: 3000,
        });
    }
};

const patientHistory = (id) => {
    const selectedPatientId = page?.props?.selected_patient?.id ?? null;

    if (selectedPatientId && selectedPatientId === id) {
        router.get(route("doctor.patient.history"));
    } else {
        Swal.fire({
            toast: true,
            position: "top-end",
            icon: "info",
            title: "Please select a patient first",
            showConfirmButton: false,
            timer: 3000,
        });
    }
};

</script>

<template>
    <AuthLayout title="All Patients" description="All Patients" heading="All">

        <!-- ================= HEADER ROW ================= -->
        <div class="d-flex align-items-center justify-content-between">
            <h3 class="d-flex align-items-center">All Patients</h3>

                 <ActionButtons :actionButtons="buttons" :extraButtons="extraButtons" />
         </div>

        <!-- ================= TABLE ================= -->
             <Table :columns="columns" :data="patients" :search="search">
                <template #register_to_portal="{ row }">
                    <span v-if="row.register_to_portal === false" class="badge bg-success">
                        Registered
                    </span>

                    <span v-else class="badge bg-secondary">
                        <button class="btn btn-danger btn-sm" @click="register(row.id)"
                            :disabled="processing[row.id]">
                            {{ processing[row.id]
                                ? "Sending Code..."
                            : "Not registered" }}
                        </button>
                    </span>
                </template>


                <template #actions="{ row }">
                    <div class="table-actions d-flex gap-1">
                        <Link v-if="$page?.props?.selected_patient?.id !== row?.id" class="btn btn-success"
                            :href="route('doctor.select.patient', row?.id)">
                            <i class="bi bi-check2-circle"></i>
                        </Link>

                        <Link v-else class="btn btn-warning" :href="route('doctor.select.patient', 'empty')">
                            <i class="bi bi-x-circle"></i>
                        </Link>
                        
                        <button class="btn btn- iq-bg-info" @click="patientSummary(row?.id)" title="Patient Summary">
                            <i class="bi bi-journal-text"></i>
                        </button>

                        <button class="btn btn- iq-bg-primary" @click="patientHistory(row?.id)" title="History">
                            <i class="bi bi-clock-history"></i>
                        </button>

                        <button class="btn btn- iq-bg-success" @click="patientDetails(row?.id)">
                         <i class="bi bi-eye"></i>
                       </button>
                         
                    </div>
                </template>

            </Table>
 
        <!-- ================= MODAL ================= -->
        <Modal :isOpen="isAddUserModalOpen" title="Add Patient" @close="closeAddUserModal" size="xl">
            <AddPatient @close="closeAddUserModal" />
        </Modal>
         <Modal :isOpen="showPatientSummary" title="Patient Summary" @close="showPatientSummary = false" size="xl">
          <PatientSummary
            :patient="patientData || {}"
         />
        </Modal>

    </AuthLayout>
</template>
 
