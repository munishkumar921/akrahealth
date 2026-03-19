<script setup>
import { ref, computed } from 'vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useForm } from "@inertiajs/vue3";
import Modal from "@/Components/Common/Modal.vue";
import BaseInput from '@/Components/Common/Input/BaseInput.vue';
import BaseDatePicker from '@/Components/Common/Input/BaseDatePicker.vue';
import Table from '@/Components/Table/Table.vue';
import Search from '@/Components/Common/Search.vue';
import Swal from 'sweetalert2/dist/sweetalert2.js';
import axios from 'axios';
import { route } from 'ziggy-js';

const props = defineProps({
  vaccines: Object,
  keyword: Object
});

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

// Search states for CPT
const cptLoader = ref(false);
const cptSearchQuery = ref('');
const cptResults = ref([]);

// Search states for Immunization
const immunizationLoader = ref(false);
const immunizationSearchQuery = ref('');
const immunizationResults = ref([]);

// Search states for Vaccine (main search)
const vaccineLoader = ref(false);
const vaccineSearchQuery = ref('');
const vaccineResults = ref([]);

const form = useForm({
  id: '',
  date_purchase: '',
  immunization: '',
  lot: '',
  manufacturer: '',
  expiration_date: '',
  brand: '',
  code: '',
  cpt: '',
  quantity: 0,
});

// CPT Search functions
const searchCPT = () => {
  if (!cptSearchQuery.value.trim()) {
    cptResults.value = [];
    return;
  }
  
  cptLoader.value = true;
  const formData = new FormData();
  formData.append('search_cpt', cptSearchQuery.value);
  
  axios.post(route('admin.search.cpt'), formData)
    .then(response => {
      if (response.data.message && Array.isArray(response.data.message)) {
        cptResults.value = response.data.message;
      } else {
        cptResults.value = [];
      }
      cptLoader.value = false;
    })
    .catch(error => {
      cptLoader.value = false;
      cptResults.value = [];
    });
};

const selectCPT = (row) => {
  form.cpt = row.value;
  cptResults.value = [];
  cptSearchQuery.value = '';
};

// Immunization Search functions
const searchImmunization = () => {
  if (!immunizationSearchQuery.value.trim()) {
    immunizationResults.value = [];
    return;
  }
  
  immunizationLoader.value = true;
  const formData = new FormData();
  formData.append('search_immunization', immunizationSearchQuery.value);
  
  axios.post(route('admin.search.immunization'), formData)
    .then(response => {
      if (response.data.message && Array.isArray(response.data.message)) {
        immunizationResults.value = response.data.message;
      } else {
        immunizationResults.value = [];
      }
      immunizationLoader.value = false;
    })
    .catch(error => {
      immunizationLoader.value = false;
      immunizationResults.value = [];
    });
};

const selectImmunization = (row) => {
  form.immunization = row.value;
  immunizationResults.value = [];
  immunizationSearchQuery.value = '';
};

// Vaccine Search functions (for main search)
const searchVaccine = () => {
  if (!vaccineSearchQuery.value.trim()) {
    vaccineResults.value = [];
    return;
  }
  
  vaccineLoader.value = true;
  const formData = new FormData();
  formData.append('search_vaccine', vaccineSearchQuery.value);
  
  axios.post(route('admin.search.vaccine'), formData)
    .then(response => {
      if (response.data.message && Array.isArray(response.data.message)) {
        vaccineResults.value = response.data.message;
      } else {
        vaccineResults.value = [];
      }
      vaccineLoader.value = false;
    })
    .catch(error => {
      vaccineLoader.value = false;
      vaccineResults.value = [];
    });
};

const selectVaccine = (row) => {
  // Populate form with selected vaccine data
  form.id = row.id;
  form.immunization = row.immunization;
  form.brand = row.brand;
  form.manufacturer = row.manufacturer;
  form.lot = row.lot;
  form.expiration_date = row.expiration_date;
  form.cpt = row.cpt;
  form.code = row.code;
  form.quantity = row.quantity;
  form.date_purchase = row.date_purchase;
  
  // Open edit modal with populated data
  isEditing.value = true;
  showModal.value = true;
  
  // Clear search results
  vaccineResults.value = [];
  vaccineSearchQuery.value = '';
};

const openAddModal = () => {
  isEditing.value = false;
  showModal.value = true;
  form.reset();
  cptSearchQuery.value = '';
  cptResults.value = [];
  immunizationSearchQuery.value = '';
  immunizationResults.value = [];
  vaccineSearchQuery.value = '';
  vaccineResults.value = [];
};

const openEditModal = (vaccine) => {
  isEditing.value = true;
  showModal.value = true;
  form.id = vaccine.id;
  form.date_purchase = vaccine.date_purchase;
  form.immunization = vaccine.immunization;
  form.lot = vaccine.lot;
  form.manufacturer = vaccine.manufacturer;
  form.expiration_date = vaccine.expiration_date;
  form.brand = vaccine.brand;
  form.code = vaccine.code;
  form.cpt = vaccine.cpt;
  form.quantity = vaccine.quantity;
  
  // Clear search fields when editing
  cptSearchQuery.value = '';
  cptResults.value = [];
  immunizationSearchQuery.value = '';
  immunizationResults.value = [];
  vaccineSearchQuery.value = '';
  vaccineResults.value = [];
};

const closeModal = () => {
  showModal.value = false;
  editingId.value = null;
  form.reset();
  cptSearchQuery.value = '';
  cptResults.value = [];
  immunizationSearchQuery.value = '';
  immunizationResults.value = [];
  vaccineSearchQuery.value = '';
  vaccineResults.value = [];
};

const saveVaccine = () => {
  form.post(route('admin.vaccines.store'), {
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
      useForm({}).delete(route("admin.vaccines.destroy", id));
    }
  });
};

const columns = [
  { label: "Date", key: "date_purchase" },
  { label: "Immunization", key: "immunization" },
  { label: "Brand", key: "brand" },
  { label: "Lot", key: "lot" },
  { label: "Manufacturer", key: "manufacturer" },
  { label: "Expiration", key: "expiration" },
  { label: "CPT", key: "cpt" },
  { label: "Code", key: "code" },
  { label: "Quantity", key: "quantity" },
];

</script>

<template>
  <AuthLayout title="Vaccines" description="Manage your vaccines inventory" heading="Vaccines">
    <div class="container-box">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Vaccines Inventory</h5>
        <div class="d-flex gap-2">
          <div class="position-relative">
            <Search v-model="vaccineSearchQuery" :loader="vaccineLoader" @input="searchVaccine" placeholder="Search vaccines..." />
            <template v-for="row in vaccineResults" :key="row.id">
              <p class="p-2 border-bottom bg-light cursor-pointer" @click="selectVaccine(row)">
                {{ row.label }} - {{ row.manufacturer }}
              </p>
            </template>
            <template v-if="vaccineLoader">
              <div class="text-center p-2">
                <span class="spinner-border spinner-border-sm"></span>
              </div>
            </template>
          </div>
          <button class="btn btn-primary" @click="openAddModal">Add Vaccine</button>
        </div>
      </div>
      <Table :columns="columns" :data="vaccines" table="vaccines" :search="keyword">
        <template #actions="{ row }">
          <div class="d-flex gap-2">
            <button class="icon-btn btn btn-primary" @click="openEditModal(row)" title="Edit">
              <i class="bi bi-pencil"></i>
            </button>
            <button class="icon-btn btn btn-danger" @click="removeRow(row.id)" title="Delete">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </template>
      </Table>
    </div>

    <!-- Add/Edit Modal -->
    <Modal :isOpen="showModal" :title="isEditing ? 'Edit Vaccine' : 'Add Vaccine'" size="lg" @close="closeModal">
      <form @submit.prevent="saveVaccine">
        <div class="modal-body">
          <div class="row">
             <div class="col-md-12 mb-3">
               <Search v-model="cptSearchQuery" :loader="cptLoader" @input="searchCPT" placeholder="Search CPT..." />
              <template v-for="row in cptResults" :key="row.value">
                <p class="p-2 border-bottom bg-light cursor-pointer" @click="selectCPT(row)">
                  {{ row.label }}
                </p>
              </template>
              <template v-if="cptLoader">
                <div class="text-center p-2">
                  <span class="spinner-border spinner-border-sm"></span>
                </div>
              </template>              
             </div>
             <div class="col-md-12 mb-3">
               <Search v-model="immunizationSearchQuery" :loader="immunizationLoader" @input="searchImmunization" placeholder="Search immunization..." />
              <template v-for="row in immunizationResults" :key="row.cvx">
                <p class="p-2 border-bottom bg-light cursor-pointer" @click="selectImmunization(row)">
                  {{ row.label }}
                </p>
              </template>
              <template v-if="immunizationLoader">
                <div class="text-center p-2">
                  <span class="spinner-border spinner-border-sm"></span>
                </div>
              </template>
              
              </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <BaseDatePicker v-model="form.date_purchase" label="Date purchase" placeholder="Select Date" :error="form.errors.date_purchase" required />
            </div>
            <div class="col-md-6 mb-3">
              <BaseInput v-model="form.immunization" label="Immunization" placeholder="Enter immunization" :error="form.errors.immunization" required />
            </div>
            
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <BaseInput v-model="form.brand" label="Brand" placeholder="Enter brand" :error="form.errors.brand" />
            </div>
            <div class="col-md-6 mb-3">
              <BaseInput v-model="form.lot" label="Lot" placeholder="Enter lot" :error="form.errors.lot" />
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <BaseInput v-model="form.manufacturer" label="Manufacturer" placeholder="Enter manufacturer" :error="form.errors.manufacturer" />
            </div>
            <div class="col-md-6 mb-3">
              <BaseDatePicker v-model="form.expiration_date" label="Expiration Date" placeholder="Select Date" :error="form.errors.expiration_date" />
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <BaseInput v-model="form.cpt" label="CPT" placeholder="Enter CPT" :error="form.errors.cpt" />
            </div>
            <div class="col-md-6 mb-3">
              <BaseInput v-model="form.code" label="Code" placeholder="Enter code" :error="form.errors.code" />
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <BaseInput v-model="form.quantity" label="Quantity" placeholder="Enter quantity" :error="form.errors.quantity" type="number" />
            </div>
          </div>
        </div>
        <div class="d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-danger" @click="closeModal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            {{ isEditing ? 'Update' : 'Save' }}
          </button>
        </div>
      </form>
    </Modal>

  </AuthLayout>
</template>