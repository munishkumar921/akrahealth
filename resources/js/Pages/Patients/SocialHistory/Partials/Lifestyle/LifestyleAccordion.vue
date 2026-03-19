<script setup>
import { ref } from "vue";
import Modal from "@/Components/Common/Modal.vue";
import LifestyleModal from "./LifestyleModal.vue";

defineProps({
  socialHistory: Object,
});

const isModalOpen = ref(false);

const openModal = () => (isModalOpen.value = true);
const closeModal = () => (isModalOpen.value = false);

const handleSubmit = (formData) => {
  // Handle form submission logic here
  console.log('Lifestyle form submitted:', formData);
  closeModal();
};
</script>

<template>
  <div class="card shadow-sm mb-3">
    <!-- Header -->
    <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
      <h6 class="mb-0 text-white">Lifestyle</h6>
      <button class="btn btn-light btn-sm" @click="openModal">Edit</button>
    </div>

    <!-- Body -->
    <div class="card-body p-0">
      <table class="table mb-0">
        <tbody>
          <tr>
            <td><strong>Social History :</strong></td>
            <td>{{ socialHistory?.social_history || 'None' }}</td>
          </tr>
          <tr>
            <td><strong>Sexually Active :</strong></td>
            <td>{{ socialHistory?.sexually_active ? 'Yes' : 'No' }}</td>
          </tr>
          <tr>
            <td><strong>Diet:</strong></td>
            <td>{{ socialHistory?.diet || 'None' }}</td>
          </tr>
          <tr>
            <td><strong>Physical Activity :</strong></td>
            <td>{{ socialHistory?.physical_activity || 'None' }}</td>
          </tr>
          <tr>
            <td><strong>Employment :</strong></td>
            <td>{{ socialHistory?.employment || 'None' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal -->
  <Modal
    title="Edit Lifestyle"
    :isOpen="isModalOpen"
    @close="closeModal"
  >
    <LifestyleModal
      :socialHistory="socialHistory"
      @close="closeModal"
      @submit="handleSubmit"
    />
  </Modal>
</template>
