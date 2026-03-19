<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue'
import EventCalender from '@/Components/EventCalender.vue';
import { router, useForm } from '@inertiajs/vue3';

const props = defineProps({
  data: Object,
});


// Get today's appointments from the data prop
const todaysAppointments = props.data?.todaysAppointments || [];

const goToBookingList = () => {
  router.get(route('patient.booking.list'));
};

const buttons = [
  {
    label: "Booking List",
    function: goToBookingList,
    icon: "bi bi-plus-circle",
  },
];

</script>

<template>
  <AuthLayout title="Book Appointment" description="Book your appointment" heading="Book Appointment">
    <div class="row row-eq-height">
      <!-- Left Column -->
      <div class="col-md-3">


        <div class="iq-card">
          <div class="iq-card-header d-flex justify-content-between">
            <div class="iq-header-title">
              <h4 class="card-title">Today's Schedule</h4>
            </div>
          </div>
          <div class="iq-card-body">
            <ul class="m-0 p-0 today-schedule" v-if="todaysAppointments.length > 0">
              <li class="d-flex" v-for="appointment in todaysAppointments" :key="appointment.id">
                <div class="schedule-icon">
                  <i class="ri-checkbox-blank-circle-fill text-primary"></i>
                </div>
                <div class="schedule-text">
                  <span>{{ appointment.doctor?.user?.name || 'Doctor' }}</span>
                  <span>{{ appointment.appointment_time }}</span>
                </div>
              </li>
            </ul>
            <div v-else class="text-muted text-center py-3">
              <p class="mb-0">No appointments today</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column -->
      <div class="col-md-9">
        <div class="iq-card p-2">
          <div class="iq-card-header d-flex justify-content-between">
            <div class="iq-header-title">
              <h4 class="card-title">Book Appointment</h4>
            </div>
          </div>
          <div class="iq-card-body">
            <EventCalender :data="data" />
          </div>
        </div>
      </div>
    </div>
  </AuthLayout>
</template>
<style scoped></style>
