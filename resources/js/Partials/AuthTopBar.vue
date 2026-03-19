<script setup>
import { usePage, router } from "@inertiajs/vue3";
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import AutoComplete from "primevue/autocomplete";
import { route } from "ziggy-js";

import Hamburger from "../Components/Common/Hamburger.vue";
import PatientSelector from "../Components/Header/Patient/PatientSelector.vue";
import DoctorNavOptions from "../Components/Header/NavOptions/DoctorNavOptions.vue";
import VirtualAssistantNavOptions from "../Components/Header/NavOptions/VirtualAssistantNavOptions.vue";
import PatientNavOptions from "../Components/Header/NavOptions/PatientNavOptions.vue";
import NotificationsDropdown from "../Components/Header/Notifications.vue";
import Patient from "../Pages/Modals/Patient.vue";
import ShareDetailsModal from "@/Pages/Modals/ShareDetailsModal.vue";
import Modal from "../Components/Common/Modal.vue";
import BaseSelect from "../Components/Common/Input/BaseSelect.vue";
/* ---------------- PROPS ---------------- */
const props = defineProps({
    windowWidth: Number,
    isMobileView: Boolean,
    toggleMobileMenu: Function,
});

/* ---------------- STATE ---------------- */
const page = usePage();
const role = computed(() => page.props?.auth?.user?.roles?.[0]?.name);
const showMobileDoctorNav = ref(false);
const showMobileVirtualAssistantNav = ref(false);
const showMobilePatientNav = ref(false);
const charts = ref(false);  // Add this line to define charts state
// Check if user is SuperAdmin and if role is switched
const originalRole = computed(() => {

    const user = page.props?.auth?.user;
    const baseRole = role.value;
    if (user?.roles) {
        // Check if user has Admin or SuperAdmin role (even if switched)
        const hasAdmin = user.roles.some(r => r.name === 'Admin');
        return hasAdmin ? 'Admin' : baseRole;
    }
    return baseRole;
});

const switchedRole = computed(() => {
    // Rely on backend session flag shared via props
    return page.props?.switched_role === 'Doctor';
});

// Effective role considering switched view
const effectiveRole = computed(() => {
    return page.props?.switched_role === 'Doctor' ? 'Doctor' : role.value;
});

const toggleDoctorNav = () => {
    showMobileDoctorNav.value = !showMobileDoctorNav.value;
};

const toggleVirtualAssistantNav = () => {
    showMobileVirtualAssistantNav.value = !showMobileVirtualAssistantNav.value;
};

const togglePatientNav = () => {
    showMobilePatientNav.value = !showMobilePatientNav.value;
};

/* ---------------- SEARCH ---------------- */
const patients = ref([]);
const selectedPatient = ref(null);
const filteredPatients = ref([]);

const search = (event) => {
    const searchRoute = role.value === 'Virtual Assistant' ? "assistant.search.patient" : "doctor.search.patient";
    axios.get(route(searchRoute, { q: event.query.toLowerCase() }))
        .then((res) => {
            patients.value = res.data;
            filteredPatients.value = res.data;
        });
};

const onPatientSelect = (event) => {
    const selectRoute = role.value === 'Virtual Assistant' ? "assistant.select.patient" : "doctor.select.patient";
    router.get(route(selectRoute, { id: event.value.id }), {}, {
        onSuccess: (response) => {
            router.reload({ only: ["auth", "flash"] });
        },
        onError: (errors) => {
            console.error("Patient selection failed:", errors);
        }
    });
};

/* ---------------- SHARE MODAL ---------------- */
const isShareDetailsModalOpen = ref(false);
const toggleShareDetailsModal = () => {
    isShareDetailsModalOpen.value = !isShareDetailsModalOpen.value;
};

const switchRole = () => {
    router.post(
        route('switch.role'),
        {},
        {
            preserveState: false,
            preserveScroll: false,
            onError: (errors) => {
                console.error('Failed to switch role:', errors);
            },
        }
    );
};

const navigateToCharts = () => {
    charts.value = true;
 };
 const closeCharts = () => {
    charts.value = false;
 };

 const generateCharts = () => {
    
    axios.post(route('admin.generateCharts'), {
        branch_id: selectedBranch.value,
    }, {
        responseType: 'blob', // Important: tell axios to expect a binary file response
    }).then((res) => {
        // Create a temporary link to trigger the download
        const url = window.URL.createObjectURL(new Blob([res.data]));
        const link = document.createElement('a');
        link.href = url;
        
        // Get filename from Content-Disposition header or generate one
        const contentDisposition = res.headers['content-disposition'];
        let fileName = 'branch_charts_' + Date.now() + '.zip';
        if (contentDisposition) {
            const fileNameMatch = contentDisposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
            if (fileNameMatch && fileNameMatch[1]) {
                fileName = fileNameMatch[1].replace(/['"]/g, '');
            }
        }
        
        link.setAttribute('download', fileName);
        document.body.appendChild(link);
        link.click();
        
        // Cleanup
        link.remove();
        window.URL.revokeObjectURL(url);
        
        // Close the modal after successful download
        closeCharts();
    }).catch((error) => {
        console.error('Download failed:', error);
        // Handle error - could show an alert to the user
    });
  };

 const selectedBranch = ref(null);
 const branches = ref([]);

 const getBranches = () => {
    axios.get(route('admin.getBranches')).then((res) => {
        branches.value = res.data;
    });
 };

 onMounted(() => {
    getBranches();
 });
</script>

<template>
    <div class="iq-top-navbar d-print-none">
        <div class="iq-navbar-custom">
            <nav class="navbar navbar-expand-lg navbar-light px-3">

                <!-- ================= DESKTOP ================= -->
                <div v-if="!isMobileView" class="d-flex align-items-center w-100 gap-3">

                    <!-- SEARCH -->
                    <div class="flex-grow-1">
                        <AutoComplete
                            v-if="(effectiveRole === 'Doctor' || role === 'Virtual Assistant') && !$page.props?.selected_patient?.name"
                            v-model="selectedPatient" optionLabel="name" :suggestions="filteredPatients"
                            @complete="search" @item-select="onPatientSelect" placeholder="Search patient..." />
                        <PatientSelector v-else />
                    </div>

                    <!-- DOCTOR NAV ICONS -->
                    <DoctorNavOptions v-if="effectiveRole === 'Doctor'" :windowWidth="windowWidth" />

                    <!-- VIRTUAL ASSISTANT NAV ICONS -->
                    <VirtualAssistantNavOptions v-if="role === 'Virtual Assistant'" :windowWidth="windowWidth" />

                    <!-- PATIENT NAV ICONS -->
                    <PatientNavOptions v-if="role === 'Patient'" :windowWidth="windowWidth" />

                    <!-- CALENDAR ICON -->
                    <div class="nav-card shadow-lg position-relative" data-tooltip="Appointment"
                        data-tooltip-location="bottom" v-if="effectiveRole === 'Admin'"
                        @click="router.visit(route('admin.schedule.index'))">
                        <div class="nav-card-content">
                            <i class="fa-solid fa-calendar-plus" style="color: #1be1b3;"></i>
                        </div>

                    </div>
                    <div class="nav-card shadow-lg position-relative" data-tooltip="Charts"
                        data-tooltip-location="bottom" v-if="effectiveRole === 'Admin'"
                        @click="navigateToCharts()">    
                        <div class="nav-card-content">
                            <i class="fa-solid fa fa-bar-chart" style="color:orangered;"></i>
                        </div>

                    </div>

                    <!-- RIGHT ICONS -->
                    <div class="d-flex align-items-center gap-2">
                        <button v-if="role === 'Patient'" class="btn btn-link nav-link"
                            @click="toggleShareDetailsModal">
                            <i class="ri-share-line"></i>
                        </button>
                        <button v-if="originalRole === 'Admin'" class="btn btn-outline-primary btn-sm"
                            @click="switchRole"
                            :title="switchedRole ? 'Switch back to Admin' : 'Switch to Doctor view'">
                            <i class="fas fa-exchange-alt me-1"></i>
                            {{ switchedRole ? 'Switch to Admin' : 'Switch to Doctor' }}
                        </button>
                        <NotificationsDropdown />
                    </div>
                </div>

                <!-- ================= MOBILE ================= -->
                <div v-else class="w-100">

                    <!-- ROW 1 -->
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <Hamburger @click="toggleMobileMenu" />
                        <div class="d-flex align-items-center gap-2">
                            <button v-if="originalRole === 'Admin'" class="btn btn-outline-primary btn-sm"
                                @click="switchRole"
                                :title="switchedRole ? 'Switch back to Admin' : 'Switch to Doctor view'">
                                <i class="fas fa-exchange-alt"></i>
                                <span class="d-none d-sm-inline ms-1">{{ switchedRole ? 'Admin' : 'Doctor' }}</span>
                            </button>
                            <NotificationsDropdown />
                        </div>
                    </div>

                    <!-- ROW 2 -->
                    <div class="d-flex align-items-center gap-2">
                        <div class="flex-grow-1">
                            <AutoComplete
                                v-if="(effectiveRole === 'Doctor' || role === 'Virtual Assistant') && !$page.props?.selected_patient?.name"
                                v-model="selectedPatient" optionLabel="name" :suggestions="filteredPatients"
                                @complete="search" @item-select="onPatientSelect" placeholder="Search patient..." />
                            <PatientSelector v-else />
                        </div>

                        <!-- SECOND HAMBURGER -->
                        <button v-if="effectiveRole === 'Doctor'" class="btn btn-light" @click="toggleDoctorNav">
                            <i class="fa-solid fas fa-star-of-life fs-5"></i>
                        </button>

                        <!-- VIRTUAL ASSISTANT HAMBURGER -->
                        <button v-if="role === 'Virtual Assistant'" class="btn btn-light"
                            @click="toggleVirtualAssistantNav">
                            <i class="fa-solid fas fa-star-of-life fs-5"></i>
                        </button>

                        <!-- PATIENT HAMBURGER -->
                        <button v-if="role === 'Patient'" class="btn btn-light" @click="togglePatientNav">
                            <i class="fa-solid fas fa-star-of-life fs-5"></i>
                        </button>
                    </div>

                    <!-- TOGGLED DOCTOR NAV -->
                    <DoctorNavOptions v-if="showMobileDoctorNav && effectiveRole === 'Doctor'"
                        :windowWidth="windowWidth" class="mt-2" />

                    <!-- TOGGLED VIRTUAL ASSISTANT NAV -->
                    <VirtualAssistantNavOptions v-if="showMobileVirtualAssistantNav && role === 'Virtual Assistant'"
                        :windowWidth="windowWidth" class="mt-2" />

                    <!-- TOGGLED PATIENT NAV -->
                    <PatientNavOptions v-if="showMobilePatientNav && role === 'Patient'" :windowWidth="windowWidth"
                        class="mt-2" />
                </div>

            </nav>
        </div>
    </div>

    <Patient />

    <ShareDetailsModal :isOpen="isShareDetailsModalOpen" :onClose="toggleShareDetailsModal"
        :patient="$page.props?.selected_patient" />
     <Modal :isOpen="showPasswordResetModal" title="Change Password" @close="() => (showPasswordResetModal = false)" size="lg">
    <ResetPasswordModal @close="() => (showPasswordResetModal = false)" />
  </Modal>
  <Modal :isOpen="charts" title="Charts" @close="closeCharts" size="lg">
    <form @submit.prevent="generateCharts">
        <div class="row align-items-center">
             <div class="col-md-6 mb-3">
                 <BaseSelect v-model="selectedBranch" label="Select Branch" placeholder="Select Branch">
                    <template v-for="branch in branches" :key="branch.id">
                        <option :value="branch.id">
                            {{ branch.name }}
                            <span class="text-muted">({{ branch.main_branch_id === null ? 'Main' : 'Sub' }})</span>
                        </option>
                    </template>
                </BaseSelect>
            </div>
             <div class="col-md-6">
                <button class="btn btn-primary" type="submit">Generate Charts</button>
            </div>
         </div>
    </form>
  </Modal>
</template>

<style scoped>
.nav-link {
    color: inherit;
    text-decoration: none;
}
</style>
