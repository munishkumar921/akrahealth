<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import Table from "@/Components/Table/Table.vue";
import Modal from "@/Components/Common/Modal.vue";
import AddFamilyModal from "@/Pages/Modals/FamilyHistory.vue";
import Swal from 'sweetalert2/dist/sweetalert2.js';

const props = defineProps({
  familyHistory: Object,
  keyword: String,
});

const isAddModalOpen = ref(false);
const childComponentRef = ref(null);

const addHistory = () => {
  isAddModalOpen.value = true;
  if (childComponentRef.value?.resetForm) {
    childComponentRef.value.resetForm();
  }
}
const closeAddFamilyModal = () => {
  isAddModalOpen.value = false;
  isAddModalOpen.value = false;
}
const edit = (row) => {
  isAddModalOpen.value = true;
  setTimeout(() => {
    if (childComponentRef.value?.update) {
      childComponentRef.value.update(row);
    }
  }, 100);
}


// Table Columns
const columns = [
  { label: "Name", key: "name" },
  { label: "Relationship", key: "relationship" },
  { label: "Gender", key: "gender" },
  { label: "DOB", key: "dob" },
  { label: "Marital Status", key: "marital_status" },
  { label: "Medical History", key: "medical_history", type: "slot", slot: "medical_history" },
 ];


// Normalize keys (convert all to lowercase & consistent naming)
function normalize(item) {
  const map = {
    Name: "name",
    Relationship: "relationship",
    Status: "living_status",
    Gender: "gender",
    "Date of Birth": "dob",
    "Marital Status": "marital_status",
    Mother: "mother",
    Father: "father",
    Medical: "medical_history",
  };

  const normalized = {};
  for (const [key, value] of Object.entries(item)) {
    normalized[map[key] || key] = value;
  }
  return normalized;
}

// Flatten and normalize
const flatData = computed(() => {
  const items = props.familyHistory?.data || [];
  return items.flatMap(item =>
    (item.oh_fh || []).map((fh, index) => ({
      id: item.id,
      index: index,
      ...normalize(fh),
    }))
  );
});

const del=(row)=>{
   Swal.fire(confirmSettings("Are you sure you want to delete this family history record?"))
        .then((result) => {
            if (result.isConfirmed) {
                const deleteForm = useForm({});
                deleteForm.delete(route('doctor.family-history.destroy', row.id), {
                    preserveScroll: true,
                });
            }
        });
 }
</script>

<template>
  <AuthLayout title="Family History" description="Family History" heading="Family History">
    <div class="d-flex align-items-center justify-content-between">
      <h3 class="d-flex align-items-center">Family History</h3>
      <button class="btn btn-primary" @click="addHistory">
        Add Family History
      </button>
    </div>
    <Table :columns="columns" :data="{ data: flatData }" :search="props.keyword">
      <template #actions="{ row }">
        <button type="button" class="btn btn-primary " @click="edit(row)" title="Edit">
          <i class="bi bi-pencil-square"></i>
        </button>
      </template>
      <template #medical_history="{ row }">
            <div v-if="row.medical_history">
                 <span v-for="(item, index) in (Array.isArray(row.medical_history) ? row.medical_history : String(row.medical_history).split(','))" 
                      :key="index" 
                      class="badge bg-primary me-1">
                      {{}}
                    {{ item.trim() }}
                </span>
            </div>
           </template>
    </Table>
     <Modal :isOpen="isAddModalOpen" title="Family History" @close="closeAddFamilyModal" size="lg">
      <AddFamilyModal ref="childComponentRef" @close="closeAddFamilyModal" />
    </Modal>
  </AuthLayout>
</template>
