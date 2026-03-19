<script setup>
import { ref, onBeforeUnmount, computed } from "vue";
 import PatientSummary from "@/Pages/Common/PatientSummary.vue";
import {doctorNavOptions, patientNavOptions } from "../options";
import Modal from "@/Components/Common/Modal.vue";
import { router, usePage } from "@inertiajs/vue3";
import "../header.css";
import Profile from "../Profile.vue";
import axios from "axios";

/* ---------------- PROPS ---------------- */
const props = defineProps({
    windowWidth: Number,
    visible: {
        type: Boolean,
        default: true,
    },
});

/* ---------------- STATE ---------------- */
const showAdditionalOptions = ref({
    office: false,
    configure: false,
});

const showPatientSummary = ref(false);
const isLoadingPatientData = ref(false);

const page = usePage();
const user = page.props.auth?.user;
const role = user?.roles?.[0]?.name;

// Check if user is in doctor mode (either has Doctor role or Admin switched to Doctor)
const isDoctorMode = computed(() => {
    return role === "Doctor" ||
           (role === "Admin" && page.props?.switched_role === 'Doctor');
});

const patientData = ref(
    page.props.patient || page.props.selected_patient || {}
);

/* ---------------- NAV OPTIONS ---------------- */
const currentNavOptions = computed(() => {
    if (!user) return [];
    if (isDoctorMode.value) return doctorNavOptions;
    if (role === "Patient") return patientNavOptions;
    return [];
});
const toggleOptions = (label, path) => {
    showAdditionalOptions.value = { office: false, configure: false };

    if (label === "Office") {
        showAdditionalOptions.value.office = true;
    } else if (label === "Configure") {
        showAdditionalOptions.value.configure = true;
    } else {
        router.visit(route(path));
    }
};
const handleCardClick = (icon) => {
    if (icon?.id === "patient-summary") {
        loadPatientSummary();
        return;
    }
    toggleOptions(icon.label, icon.path);
};

const loadPatientSummary = () => {
    isLoadingPatientData.value = true;

    axios.get(route("doctor.patient.summary")).then((res) => {
            if (res?.data) patientData.value = res.data;
            showPatientSummary.value = true;
        })
        .catch(() => {
            showPatientSummary.value = true;
        })
        .finally(() => {
            isLoadingPatientData.value = false;
        });
};

const handleClickOutside = (event) => {
    const expandedOptions = document.querySelectorAll(".additional-options");
    if (![...expandedOptions].some((el) => el.contains(event.target))) {
        showAdditionalOptions.value = { office: false, configure: false };
    }
};
onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>

<template>
    <div v-if="visible" class="d-flex gap-4 align-items-center ml-2 doctor-nav">
        <!-- PATIENT SUMMARY -->
        <div v-if="$page.props.selected_patient && isDoctorMode"
            class="nav-card shadow-lg position-relative" @click="loadPatientSummary">
            <div class="nav-card-content" data-tooltip="Patient Summary" data-tooltip-location="bottom">
                <i class="nav-card-icon fa fa-user" style="color: #06C270"></i>
            </div>
        </div>

        <!-- NAV ICONS -->
        <div
            v-for="icon in currentNavOptions"
            :key="icon.id"
            class="nav-card shadow-lg position-relative"
            role="button"
            tabindex="0"
            @click.stop="handleCardClick(icon)"
        >
            <div
                class="nav-card-content"
                :data-tooltip="icon.label"
                data-tooltip-location="bottom"
            >
                <i :class="['nav-card-icon', icon.id]" :style="{ color: icon.color }"></i>
                <i
                    v-if="icon.label === 'Office' || icon.label === 'Configure'"
                    class="bi bi-caret-down-fill more-options"
                ></i>
            </div>
        </div>

        <Profile v-if="windowWidth <= 1100" :user="user" />
         <Modal :isOpen="showPatientSummary" title="Patient Summary" @close="showPatientSummary = false" size="xl">
          <PatientSummary :patient="patientData || {}"
         />
        </Modal>
         <!-- <PatientSummary
            :isOpen="showPatientSummary"
            title="Patient Summary"
            :patient="patientData || {}"
            @close="showPatientSummary = false"
        /> -->
    </div>
</template>

<style scoped>
.doctor-nav {
    display: flex;
    align-items: center;
    gap: 14px;
}
</style>
