<script setup>
import { ref, computed } from "vue";
import Accordion from "@/Components/Common/Accordion.vue";
import MentalHealthModal from "./MentalHealthModal.vue";

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

// Helper function to format text values
const formatText = (value) => {
    return value && value.trim() ? value : 'Not specified';
};

// Parse mental health notes to extract individual fields
const parseMentalHealthNotes = (notes) => {
    if (!notes || !notes.trim()) {
        return {
            psychological_history: 'Not specified',
            devolepmental_history: 'Not specified',
            past_medication_trials: 'Not specified'
        };
    }
    
    const parts = notes.split(' | ');
    return {
        psychological_history: formatText(parts[0]),
        devolepmental_history: formatText(parts[1]),
        past_medication_trials: formatText(parts[2])
    };
};

const mentalHealthData = computed(() => {
    return parseMentalHealthNotes(props.socialHistory?.mental_health_notes);
});
</script>

<template>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0 text-white">Mental Health</h6>
        <button class="btn btn-light btn-sm" @click="openModal">Edit</button>
      </div>
      <div class="card-body p-0">
        <table class="table mb-0">
          <tbody>
            <tr>
              <td><strong>Psychosocial History:</strong></td>
              <td>{{ mentalHealthData.psychological_history }}</td>
            </tr>
            <tr>
              <td><strong>Developmental History:</strong></td>
              <td>{{ mentalHealthData.devolepmental_history }}</td>
            </tr>
            <tr>
              <td><strong>Past Medication Trials:</strong></td>
              <td>{{ mentalHealthData.past_medication_trials }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
   <MentalHealthModal :isModalOpen="isModalOpen" :closeModal="closeModal" :socialHistory="socialHistory" />
  </template>
