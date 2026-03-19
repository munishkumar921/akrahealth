<script setup>
import { ref } from "vue";
import Accordion from "../../../../../../Components/Common/Accordion.vue";
import HabitsModal from "./HabitsModal.vue";

const props = defineProps({
    socialHistory: Object,
});

const isModalOpen = ref(false);

const openModal = () => {
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

// Helper function to format boolean values
const formatBoolean = (value) => {
    if (value === null || value === undefined) return 'Not specified';
    return value ? 'Yes' : 'No';
};

// Helper function to format text values
const formatText = (value) => {
    return value && value.trim() ? value : 'Not specified';
};
</script>

<template>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0 text-white">Habits</h6>
        <button class="btn btn-light btn-sm" @click="openModal">Edit</button>
      </div>
      <div class="card-body p-0">
        <table class="table mb-0">
          <tbody>
            <tr>
              <td><strong>Alcohol Use:</strong></td>
              <td>{{ formatText(socialHistory?.alcohol_use) }}</td>
            </tr>
            <tr>
              <td><strong>Tobacco Use:</strong></td>
              <td>{{ formatBoolean(socialHistory?.tobacco_use) }}</td>
            </tr>
            <tr>
              <td><strong>Tobacco Use Details:</strong></td>
              <td>{{ formatText(socialHistory?.tobacco_use_details) }}</td>
            </tr> 
            <tr>
              <td><strong>Illicit Drug Use:</strong></td>
              <td>{{ formatText(socialHistory?.drug_use) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
<HabitsModal :isModalOpen="isModalOpen" :closeModal="closeModal" :socialHistory="socialHistory" />
  </template>
