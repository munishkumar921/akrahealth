<script setup>
import { ref, computed } from 'vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useForm } from "@inertiajs/vue3";
import Modal from "@/Components/Common/Modal.vue";
 import BaseInput from '@/Components/Common/Input/BaseInput.vue';
import BaseDatePicker from '@/Components/Common/Input/BaseDatePicker.vue';
import Table from '@/Components/Table/Table.vue';
import Swal from 'sweetalert2/dist/sweetalert2.js';

 const props = defineProps({
  supplements:Object,
  keyword:Object
});

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
 
const form = useForm({
  id:null,
  purchase_date: '',
  sup_description: '',
  sup_strength: '',
  sup_manufacturer: '',
  sup_expiration: '',
  cpt: '',
  charge: '',
  quantity: 0,
  sup_lot: '',
 });


const openAddModal = () => {
  isEditing.value = false;
   showModal.value = true;
  form.reset();
};

const openEditModal = (supplement) => {
  isEditing.value = true;
  showModal.value = true;
  form.id = supplement.id;
  form.purchase_date = supplement.purchase_date;
  form.sup_description = supplement.description;
  form.sup_strength = supplement.strength;
  form.sup_manufacturer = supplement.manufacturer;
  form.sup_expiration = supplement.expiration;
  form.cpt = supplement.cpt;
  form.charge = supplement.charge;
  form.quantity = supplement.quantity;
  form.sup_lot = supplement.sup_lot;
 };

const closeModal = () => {
  showModal.value = false;
  editingId.value = null;
};

const saveSupplement = () => {
 
  form.post(route('admin.supplements.store'), {
     onSuccess: () => {
      closeModal();
     },
     onError: () => {
       
     }
  });
};

const removeRow = (id) => {
     Swal.fire(
        confirmSettings("Are you sure to delete this data?", "You won't be able to get it back")
    ).then((result) => {
        if (result.isConfirmed) {
            useForm({}).delete(route("admin.supplements.destroy", id));
        }
    });
};

const columns = [
  { label: "Date Purchase", key: "date_purchase" },
  { label: "Description", key: "description" },
  { label: "Strength", key: "strength" },
  { label: "Manufacturer", key: "manufacturer" },
  { label: "Expiration", key: "expiration" },
  { label: "CPT", key: "cpt" },
  { label: "Charge", key: "charge" },
  { label: "Quantity", key: "quantity" },
  { label: "Lot", key: "sup_lot" },
 ];

</script>

<template>
  <AuthLayout title="Supplements" description="Manage your supplements inventory" heading="Supplements">
    <div class="container-box">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Supplements Inventory</h5>
        <div class="d-flex gap-2">
          <button class="btn btn-primary" @click="openAddModal">Add Supplement</button>
          </div>
       </div>
    <Table :columns="columns" :data="supplements" table="supplements" :search="keyword" >
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
 
    </div>

    <!-- Add/Edit Modal -->
      <Modal :isOpen="showModal" :title="isEditing ? 'Edit Supplement' : 'Add Supplement'" size="lg" @close="closeModal">  
      <form @submit.prevent="saveSupplement">
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                 <BaseDatePicker  v-model="form.purchase_date" label="Date Purchase" placeholder="Select Date" :error="form.errors.purchase_date" required />
              </div>
              <div class="col-md-6 mb-3">
                <BaseInput v-model="form.sup_description" label="Description" placeholder="Enter description" required :error="form.errors.sup_description" />
               </div>
            </div>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <BaseInput v-model="form.sup_strength" label="Strength" placeholder="Enter strength" :error="form.errors.sup_strength" />
               </div>
              <div class="col-md-6 mb-3">
                <BaseInput v-model="form.sup_manufacturer" label="Manufacturer" placeholder="Enter manufacturer" :error="form.errors.sup_manufacturer" />
                </div>
            </div>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <BaseDatePicker v-model="form.sup_expiration" label="Expiration" placeholder="Select Date" :error="form.errors.sup_expiration" required />
               </div>
              <div class="col-md-6 mb-3">
                <BaseInput v-model="form.cpt" label="CPT" placeholder="Enter CPT" :error="form.errors.cpt" />
               </div>
            </div>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <BaseInput v-model="form.charge" label="Charge" placeholder="Enter charge" :error="form.errors.charge"/>
               </div>
              <div class="col-md-6 mb-3">
                <BaseInput v-model="form.quantity" label="Quantity" placeholder="Enter quantity" :error="form.errors.quantity"/>
               </div>
            </div>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <BaseInput v-model="form.sup_lot" label="Lot" placeholder="Enter lot" :error="form.errors.sup_lot"/>
               </div>
              
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2" >
            <button type="button" class="btn btn-danger" @click="closeModal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              {{ isEditing ? 'Update' : 'Save' }}
            </button>
          </div>
        </form>
     </Modal>
    
  </AuthLayout>
</template>

<style scoped>
.card-heading {
  padding: 12px 10px;
  border-bottom: 1px solid transparent;
  border-top-right-radius: 3px;
  border-top-left-radius: 3px;
  color: #555555;
  background-color: #e8f1f8;
  border-color: #dddddd;
}

.container-box {
  background: white;
  border-radius: 8px;
  padding: 10px;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1050;
}

.modal-content {
  background: white;
  border-radius: 8px;
  width: 90%;
  max-width: 700px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  padding: 15px 20px;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
}

.modal-body {
  padding: 20px;
}

.modal-footer {
  padding: 15px 20px;
  border-top: 1px solid #eee;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}

.btn-info {
  color: white;
}
</style>

