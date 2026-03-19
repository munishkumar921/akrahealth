<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { router, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import Swal from 'sweetalert2/dist/sweetalert2.js';
 import { ref } from "vue";
import AddVaccineTemperature from "@/Pages/Modals/AddVaccineTemperature.vue";
 import Modal from "@/Components/Common/Modal.vue";
 const props = defineProps({
  temperatures: Object,
  keyword: String
});

const isModalOpen = ref(false);
const modalTitle = ref("Add Vaccine Temperature");

const openAddModal = () => {
  modalTitle.value = "Add Vaccine Temperature";
  isModalOpen.value = true;
  setTimeout(() => {
    if (childComponentRef.value) {
      childComponentRef.value.reset();
    }
  }, 100);
};

const columns = [
  { label: "Date", key: "date" },
 { label: "Time", key: "time" },
  { label: "Temperature", key: "temperature" },
  { label: "Action", key: "action" },
];

const childComponentRef = ref(null);

const removeRow = (row) => {
   Swal.fire(
    confirmSettings("Are you sure to delete this data?", "You won't be able to get it back")
  ).then((result) => {
    if (result.isConfirmed) {
       useForm({}).delete(route("admin.vaccines.temperature.destroy", row.id));
    }
  });
};

const closeModal = () => {
  isModalOpen.value = false;
};

const openEditModal = (row) => {
  modalTitle.value = "Edit Vaccine Temperature";
  isModalOpen.value = true;
  setTimeout(() => {
    if (childComponentRef.value) {
      childComponentRef.value.update(row);
    }
  }, 100);
};
</script>

<template>
  <AuthLayout title="Vaccine Temperatures" description="Manage your vaccine temperatures" heading="Vaccine Temperatures">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Vaccine Temperatures</h5>
        <button class="btn btn-primary" @click="openAddModal">Add Vaccine Temperature</button>
      </div>
    <Table :columns="columns" :data="temperatures" table="temperatures" :search="keyword">
      <template #actions="{ row }">
        <div class="d-flex gap-2">
          <button class="icon-btn btn btn-primary" @click="openEditModal(row)" title="Edit">
            <i class="bi bi-pencil"></i>
          </button>
          <button class="icon-btn btn btn-danger" @click="removeRow(row)" title="Delete">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      </template>
      </Table>
    <Modal :isOpen="isModalOpen" @close="closeModal" :title="modalTitle" size="lg">
       <AddVaccineTemperature ref="childComponentRef" @close="closeModal" @submit="closeModal" />
    </Modal>
    
  </AuthLayout>
</template>