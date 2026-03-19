<script setup>
import { ref, computed } from "vue";
import Modal from "@/Components/Common/Modal.vue";
import MentalHealthModal from "./MentalHealthModal.vue";

const props = defineProps({
  socialHistory: Object,
});

const isModalOpen = ref(false);

const openModal = () => (isModalOpen.value = true);
const closeModal = () => (isModalOpen.value = false);

const parsedMentalHealth = computed(() => {
  if (!props.socialHistory?.mental_health_notes) {
    return {
      psychosocial_history: 'No',
      developmental_history: 'No',
      past_medication_trials: 'No'
    };
  }

  const parts = props.socialHistory.mental_health_notes.split(' | ');
  return {
    psychosocial_history: parts[0] || 'No',
    developmental_history: parts[1] || 'No',
    past_medication_trials: parts[2] || 'No'
  };
});

const handleSubmit = (formData) => {
  // Handle form submission logic here
  console.log('Mental Health form submitted:', formData);
  closeModal();
};
</script>

<template>
  <div class="card shadow-sm mb-3">
    <!-- Header -->
    <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
      <h6 class="mb-0 text-white">Mental Health</h6>
      <button class="btn btn-light btn-sm" @click="openModal">Edit</button>
    </div>

    <!-- Body -->
    <div class="card-body p-0">
      <table class="table mb-0">
        <tbody>
          <tr>
            <td><strong>Psychosocial History :</strong></td>
            <td>{{ parsedMentalHealth.psychosocial_history }}</td>
          </tr>
          <tr>
            <td><strong>Developmental History :</strong></td>
            <td>{{ parsedMentalHealth.developmental_history }}</td>
          </tr>
          <tr>
            <td><strong>Past Medication Trials :</strong></td>
            <td>{{ parsedMentalHealth.past_medication_trials }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal -->
  <Modal
    title="Edit Mental Health"
    :isOpen="isModalOpen"
    @close="closeModal"
  >
    <MentalHealthModal
      :socialHistory="socialHistory"
      @close="closeModal"
      @submit="handleSubmit"
    />
  </Modal>
</template>
