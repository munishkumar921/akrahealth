<script setup>
import { ref, computed } from "vue";
import Accordion from "../../../../../../Components/Common/Accordion.vue";
import LifestyleModal from "./LifestyleModal.vue";
import Table from "@/Components/Table/Table.vue";

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
        <h6 class="mb-0 text-white">Lifestyle</h6>
        <button class="btn btn-light btn-sm" @click="openModal">Edit</button>
      </div>
  
      <div class="card-body p-0">
        <table class="table mb-0">
          <tbody>
            <tr>
              <td><strong>Social History:</strong></td>
              <td>{{ formatText(socialHistory?.social_history) }}</td>
            </tr>
            <tr>
              <td><strong>Sexually Active:</strong></td>
              <td>{{ formatBoolean(socialHistory?.sexually_active) }}</td>
            </tr>
            <tr>
              <td><strong>Diet:</strong></td>
              <td>{{ formatText(socialHistory?.diet) }}</td>
            </tr>
            <tr>
              <td><strong>Physical Activity:</strong></td>
              <td>{{ formatText(socialHistory?.physical_activity) }}</td>
            </tr>
            <tr>
              <td><strong>Employment:</strong></td>
              <td>{{ formatText(socialHistory?.employment) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  
    <LifestyleModal :isModalOpen="isModalOpen" :closeModal="closeModal" :socialHistory="socialHistory" />
  </template>
