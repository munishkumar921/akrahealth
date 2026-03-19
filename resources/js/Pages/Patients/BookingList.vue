<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import DatePicker from '@/Components/Common/Input/BaseDatePicker.vue';
import Table from '@/Components/Table/Table.vue';

/* --- Props --- */
const props = defineProps({
  appointments: Object,
  keyword: String,
  status: String,
});

/* --- Edit Modal State --- */
const showEditModal = ref(false);
const editBooking = ref({});

const openEditModal = (booking) => {
  editBooking.value = {
    id: booking.id,
    doctorSpeciality: booking.doctor?.specialities?.[0]?.name || '',
    doctorName: booking.doctor?.name || booking.doctor?.user?.name || '',
    visitType: booking.visit_type || 'Consultation',
    appointmentDate: booking.appointment_date,
    reason: booking.reason || 'Regular Checkup'
  };
  showEditModal.value = true;
};

const saveEdit = () => {
  // Here you could implement save logic, but since it's read-only for now, just close
  showEditModal.value = false;
};

const cancelEdit = () => {
  showEditModal.value = false;
};

const goBackToSchedule = () => {
  router.visit(route('patient.book.appointment'));
}

const columns = [
  { label: 'Doctor', key: 'doctor.name' },
  { label: 'Speciality', key: 'doctor.speciality' },
  { label: 'Visit Type', key: 'visit_type.name' },
  { label: 'Appointment Date', key: 'appointment_date' },
  { label: 'Reason', key: 'reason' },
  { label: 'Status', key: 'status' },

]
</script>

<template>
  <AuthLayout title="Booking List" description="Patient Booking Appointments">
    <div class="container-fluid">
      <div class="iq-card">
        <div class="iq-card-header d-flex justify-content-between align-items-center">
          <div class="iq-header-title">
            <h4 class="card-title">Booking Appointments</h4>
          </div>
          <button class="btn btn-danger" @click="goBackToSchedule">Back to Schedule</button>
        </div>
        <div class="iq-card-body">
          <Table :columns="columns" :data="appointments" :searchShow="false" table="bookings">
            <template #actions="{ row }">

              <button :key="row.payment_status" class="btn btn-success"
                @click="router.visit(route('patient.booking.payment', row.id))"
                v-if="row.payment_status == 'pending'"><i class="bi bi-credit-card"></i></button>

              <button :key="row.payment_status" class="btn btn-info"
                @click="router.visit(route('patient.live.consultation', row.id))"
                v-if="row.status === 'confirmed' && row.payment_status && row.payment_status.toLowerCase() === 'paid'"><i
                  class="bi bi-camera-video"></i></button>
            </template>

          </Table>
        </div>
      </div>
    </div>

    <!-- Edit Booking Modal -->
    <!-- <Teleport to="body">
      <div v-if="showEditModal" class="modal-overlay" @click="cancelEdit">
        <div class="modal-container" @click.stop>
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Edit Booking</h5>
              <button type="button" class="close" @click="cancelEdit" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Doctor Speciality</label>
                <input v-model="editBooking.doctorSpeciality" type="text" class="form-control" />
              </div>
              <div class="mb-3">
                <label class="form-label">Doctor Name</label>
                <input v-model="editBooking.doctorName" type="text" class="form-control" />
              </div>
              <div class="mb-3">
                <label class="form-label">Visit Type</label>
                <input v-model="editBooking.visitType" type="text" class="form-control" />
              </div>
              <div class="mb-3">
                <label class="form-label">Appointment Date</label>
                <DatePicker v-model="editBooking.appointmentDate" type="date" />
              </div>
              <div class="mb-3">
                <label class="form-label">Reason</label>
                <input v-model="editBooking.reason" type="text" class="form-control" />
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-danger" @click="cancelEdit">Close</button>
              <button type="button" class="btn btn-primary" @click="saveEdit">Save Changes</button>
            </div>
          </div>
        </div>
      </div>
    </Teleport> -->
  </AuthLayout>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  padding: 20px;
}

.modal-container {
  background: white;
  border-radius: 8px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  width: 100%;
  max-width: 600px;
}

.modal-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #dee2e6;
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #dee2e6;
}

.close {
  background: none;
  border: none;
  font-size: 1.5rem;
  line-height: 1;
  color: #000;
  opacity: .5;
  cursor: pointer;
}

.page-link {
  cursor: pointer;
}
</style>
