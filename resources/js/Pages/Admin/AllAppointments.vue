<script setup>
import AuthLayout from '@/Layouts/AuthLayout2.vue';
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';

import Table from "@/Components/Table/Table.vue";
import TabSelector from "@/Components/Table/Partials/TabSelector.vue";

// Props from backend
const props = defineProps({
  doctors: {
    type: Array,
    default: () => []
  },
  appointments: {
    type: Object,
    default: () => ({ data: [] })
  },
  calendarEvents: {
    type: Array,
    default: () => []
  },
  filters: {
    type: Object,
    default: () => ({ doctor_id: null, status: null })
  },
  search: {
    type: String,
    default: ''
  }

});

/* -------- Doctor Filter -------- */
const selectedDoctor = ref(props.filters.doctor_id || '');

const filterByDoctor = () => {
  router.visit(route('admin.allAppointments'), {
    data: {
      doctor_id: selectedDoctor.value || null,
      status: props.filters.status
    },
    preserveState: true,
    preserveScroll: true
  });
};

watch(selectedDoctor, () => {
  filterByDoctor();
});
 
/* -------- Table + Tabs -------- */
// Initialize currentTab from filters (tab takes precedence, then status, then default to 'all')
const currentTab = ref(props.filters.tab || props.filters.status || "all");
const isInitialLoad = ref(true);

const tabs = [
  { value: "all", label: "All Appointments" },
  { value: "completed", label: "Completed" },
  { value: "upcoming", label: "Upcoming" },
  { value: "cancelled", label: "Cancelled" },
];

/* -------- TABLE COLUMNS ---------- */
const columns = [
  { label: "Patient", key: "patient.name" },
  { label: "Doctor", key: "doctor.name" },
  { label: "Type", key: "tappointment_type" },
  { label: "Mode", key: "appointment_mode" },
  { label: "Created By", key: "created_by.name" },
  { label: "Payment Status", key: "payment" },
  { label: "Appointment Status", key: "status", type: "badge" },
];
 
const updateCurrentTab = (newTab) => {
  currentTab.value = newTab;
  fetchTabData(newTab);
};

// Function to fetch data based on tab selection
const fetchTabData = (tab) => {
  let statusFilter = null;
  
  // Map tab values to status values
  if (tab === 'completed') {
    statusFilter = 'completed';
  } else if (tab === 'cancelled') {
    statusFilter = 'cancelled';
  } else if (tab === 'upcoming') {
    // For upcoming, we need to handle differently - no status filter
    statusFilter = null;
  }
  // 'all' tab has no status filter
  
  router.visit(route('admin.allAppointments'), {
    data: {
      doctor_id: selectedDoctor.value || null,
      status: statusFilter,
      tab: tab // Pass the tab to help with upcoming filter
    },
    preserveState: true,
    preserveScroll: true
  });
};

// Watch for tab changes to fetch data
watch(currentTab, (newTab) => {
  if (isInitialLoad.value) {
    isInitialLoad.value = false;
    return;
  }
  fetchTabData(newTab);
});
 
</script>

<template>
  <AuthLayout title="Appointments" description="Manage appointments" heading="">
    <div class="container-fluid">
      <!-- Doctor Filter Section -->
      <div class="mt-4 iq-card p-3 mb-3">
        <div class="row align-items-center">
          <div class="col-md-4">
            <label for="doctorFilter" class="form-label fw-bold">Filter by Doctor:</label>
            <select id="doctorFilter" class="form-select" v-model="selectedDoctor">
              <option value="">All Doctors</option>
              <option v-for="doctor in doctors" :key="doctor.id" :value="doctor.id">
                Dr. {{ doctor.name }} - {{ doctor.speciality }}
              </option>
            </select>
          </div>

        </div>
      </div>

      <!-- Tabs Section -->
      <div class="iq-card p-3">
        <div class="d-flex justify-content-between align-items-center">
          <h3>All Appointments</h3>
        <TabSelector :tabs="tabs" :currentTab="currentTab" @update:currentTab="updateCurrentTab" />
        </div>
        <!-- Appointment Table View -->
           <Table :columns="columns" :data="appointments" table="appointments"
            :search="search">
          </Table>
       </div>
 
    </div>
  </AuthLayout>
</template>
 
