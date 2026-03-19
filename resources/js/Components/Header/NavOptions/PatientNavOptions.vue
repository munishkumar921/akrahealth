<script setup>
import { ref } from "vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import Modal from "@/Components/Common/Modal.vue";
import PatientSummary from "@/Pages/Common/PatientSummary.vue";

const patientNavOptions = [
  { id: "fa-solid fa-calendar-plus", color: "#1be1b3", label: "Appointments", path: "patient.book.appointment" },
  { id: "fa-solid fa-stethoscope", color: "#06C270", label: "Encounter", path: "patient.encounters" },
];

const showPatientSummary = ref(false);
const isLoadingPatientData = ref(false);
const patientData = ref({});

const handleCardClick = (icon) => {
  router.visit(route(icon.path));
};

const loadPatientSummary = () => {
  isLoadingPatientData.value = true;

  axios
    .get(route("patient.summary"))
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
};
</script>

<template>
  <div class="d-flex gap-4 align-items-center ml-2">
    <div class="nav-card shadow-lg position-relative" @click="loadPatientSummary">
      <div class="nav-card-content" data-tooltip="Patient Summary" data-tooltip-location="bottom">
        <i class="nav-card-icon fa fa-user" style="color: #06C270"></i>
      </div>
    </div>

    <div
      v-for="icon in patientNavOptions"
      :key="icon.id"
      class="nav-card shadow-lg position-relative"
      role="button"
      tabindex="0"
      @click="handleCardClick(icon)"
    >
      <div class="nav-card-content" :data-tooltip="icon.label" data-tooltip-location="bottom">
        <i :class="['nav-card-icon ', icon.id]" :style="{ color: icon.color }"></i>
      </div>
    </div>

    <Modal :isOpen="showPatientSummary" title="Patient Summary" @close="showPatientSummary = false" size="xl">
      <div v-if="isLoadingPatientData" class="p-3">Loading summary...</div>
      <PatientSummary v-else :patient="patientData || {}" />
    </Modal>
  </div>
</template>
