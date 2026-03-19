<script setup>
import { ref } from "vue";
import Modal from "@/Components/Common/Modal.vue";
import HabitsModal from "./HabitsModal.vue";

defineProps({
  socialHistory: Object,
});

const isModalOpen = ref(false);

const openModal = () => (isModalOpen.value = true);
const closeModal = () => (isModalOpen.value = false);

const handleSubmit = (formData) => {
  // Handle form submission logic here
  console.log('Habits form submitted:', formData);
  closeModal();
};
</script>

<template>
  <div class="card shadow-sm mb-3">
    <!-- Header -->
    <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
      <h6 class="mb-0 text-white">Habits</h6>
      <button class="btn btn-light btn-sm" @click="openModal">Edit</button>
    </div>

    <!-- Body -->
    <div class="card-body p-0">
      <table class="table mb-0">
        <tbody>
          <tr>
            <td><strong>Alcohol Use :</strong></td>
            <td>{{ socialHistory?.alcohol_use || 'No' }}</td>
          </tr>
          <tr>
            <td><strong>Tobacco Use :</strong></td>
            <td>{{ socialHistory?.tobacco_use ? 'Yes' : 'No' }}</td>
          </tr>
          <tr>
            <td><strong>Tobacco Use Details :</strong></td>
            <td>{{ socialHistory?.tobacco_use_details || 'No' }}</td>
          </tr>
          <tr>
            <td><strong>Illicit Drug Use :</strong></td>
            <td>{{ socialHistory?.drug_use || 'No' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal -->
  <Modal title="Edit Habits" :isOpen="isModalOpen" @close="closeModal">
    <HabitsModal :socialHistory="socialHistory" @close="closeModal" @submit="handleSubmit" />
  </Modal>
</template>
