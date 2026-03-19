<script setup>
import { reactive, ref, computed, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import listPlugin from '@fullcalendar/list'
import interactionPlugin from '@fullcalendar/interaction'
import axios from 'axios'

import Modal from "@/Components/Common/Modal.vue";
import Checkbox from "@/Components/Checkbox.vue";
import Swal from 'sweetalert2'
import DatePicker from "@/Components/Common/Input/BaseDatePicker.vue";

const props = defineProps({
  data: Object,
})

const emit = defineEmits(["close"])

/* ---------- State ---------- */
const showModal = ref(false)
const loading = ref(false)
const tooltipState = ref({
  visible: false,
  x: 0,
  y: 0,
  data: {}
})

const getFormattedDateTime = () => {
    const now = new Date();

    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');

    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');

    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

const form = useForm({
  id: null,
  doctor_id: '',
  appointment_date: '',
  appointment_type: '',
  reason: '',
  appointment_time: '',
  accept_term_condition: false,
  duration_minutes: '',
  fee_amount: '',
  currency: '',
  createdAt: getFormattedDateTime(),

})

/* ---------- Submit ---------- */
const submit = () => {
  loading.value = true;
  const url = route('patient.store.appointment');

  const method = 'post';

  form.submit(method, url, {
    onSuccess: () => {
      showModal.value = false;
      form.reset();
      window.location.reload();
      loading.value = false;
    },
    onError: (errors) => {
      loading.value = false;
      console.error("Error submitting form:", errors);
      // Optionally keep modal open to show errors
    }
  });
}

const getStatusColor = (status) => {
  const colorMapping = {
    'pending': '#f0ad4e', // Orange
    'confirmed': '#5cb85c', // Green
    'cancelled': '#d9534f', // Red
    'completed': '#337ab7', // Blue
    'rejected': '#777777', // Gray
  };
  return colorMapping[status] || '#777';
}

/* ---------- FullCalendar Options ---------- */
const options = reactive({
  plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
  initialView: 'dayGridMonth',
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,dayGridWeek,listDay'
  },
  events: [],
  displayEventTime: false,

  eventTimeFormat: {
    hour: 'numeric',
    minute: '2-digit',
    meridiem: 'short',
    hour12: true
  },
  // events: events.value,
  eventClick: (info) => {

    console.log("Event clicked:", info.event);
    info.jsEvent.preventDefault()
    eventDetail(info.event);
  },
  selectable: true,
  datesSet: (info) => {
    const firstOfMonth = info.view.currentStart;
    const month = firstOfMonth.getMonth() + 1;
    const year = firstOfMonth.getFullYear();

    axios.post(route('patient.booked.appointments', { month: month, year: year }))
      .then(response => {
        const eventsWithDetails = response.data.map(event => {
          const status = event.extendedProps?.status || event.status;
          const colorMapping = {
            'pending': '#f0ad4e', // Orange
            'confirmed': '#5cb85c', // Green
            'cancelled': '#d9534f', // Red
            'completed': '#337ab7', // Blue
            'rejected': '#777777', // Gray
          };
          const date = new Date(event.start);
          const timeOptionsForTitle = {
            hour: 'numeric',
            hour12: true,
          };
          if (date.getMinutes() !== 0) {
            timeOptionsForTitle.minute = '2-digit';
          }
          const timeForTitle = date.toLocaleTimeString('en-US', timeOptionsForTitle).replace(/\s/g, '');

          const timeForDisplay = date.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true,
          });

          const doctorName = event.doctor_name;

          const tooltipLines = [];
          if (doctorName) {
            tooltipLines.push(`Doctor: ${doctorName}`);
          }
          if (event.appointment_date) {
            tooltipLines.push(`When: ${event.appointment_date} ${timeForDisplay}`);
          }
          if (status) {
            tooltipLines.push(`Status: ${status}`);
          }
          const tooltip = tooltipLines.join('\n');

          return {
            ...event,
            appointment_time: timeForDisplay,
            color: colorMapping[status] || '#777',
            title: doctorName ? `Appointment at ${timeForTitle} with ${doctorName}` : `Appointment at ${timeForTitle}`,
            tooltip,
          };
        });
        options.events = eventsWithDetails;

        // Also fetch provider exceptions
        fetchProviderExceptions(month, year);
      })
      .catch(error => {
        console.error("There was an error fetching the booked appointments!", error);
      });
  },
  dateClick: (info) => {

    const today = new Date()
    today.setHours(0, 0, 0, 0)
    const selectedDate = new Date(info.dateStr)

    if (selectedDate < today) {
      toast('You cannot book an appointment on a past date.', 'error', 5000)
      return
    }

    form.reset()
    form.appointment_date = info.dateStr
    showModal.value = true
  },
  eventDidMount: (info) => {
    // Tooltip handled by custom UI
  },
  eventMouseEnter: (info) => {
    const rect = info.el.getBoundingClientRect();
    tooltipState.value = {
      visible: true,
      x: rect.left + (rect.width / 2),
      y: rect.top,
      data: { ...info.event.extendedProps, title: info.event.title }
    };
  },
  eventMouseLeave: () => {
    tooltipState.value.visible = false;
  },
})

const isModalOpen = ref(false);
const appointmentDetail = ref({});
const eventDetail = (event) => {



  isModalOpen.value = true;
  appointmentDetail.value = event;
}

const closeModal = () => {
  form.reset();
  speciality_id.value = '';
  getDoctors();
  form.doctor_id = '';
  form.appointment_type = '';
  form.reason = '';
  form.accept_term_condition = false;
  form.appointment_date = '';
  form.appointment_time = '';
  slots.value = [];
  showModal.value = false;
  isModalOpen.value = false;
  loading.value = false;
  emit("close");
}

const speciality_id = ref("");
const doctors = ref([]);
const visitTypes = ref();
const durations = ref([]);

const getDoctors = () => {
  // Use the patient doctors list route so the dropdown
  // is correctly populated based on selected speciality.
  return axios.get(route('patient.doctors.list', { speciality: speciality_id.value || null }))
    .then(response => {
      doctors.value = response.data;
    })
    .catch(error => {
      console.error("There was an error fetching the doctors!", error);
    });
}

const getDoctorVisitTypes = () => {
  // Don't call API if doctor is not selected
  if (!form.doctor_id) {
    visitTypes.value = [];
    return Promise.resolve();
  }
  return axios.get(route('patient.doctor.visit.types', form.doctor_id))
    .then(response => {
      visitTypes.value = response.data;
    })
}
const getDuration = () => {
  // Don't call API if doctor or appointment type is not selected
  if (!form.doctor_id || !form.appointment_type) {
    durations.value = [];
    return Promise.resolve();
  }
  return axios.get(route('patient.doctor.visit.duration', {
    type: form.appointment_type,
    doctorId: form.doctor_id
  }))
    .then(response => {
      durations.value = response.data;
    })
    .catch(error => {
      console.error(error);
    });
}
const selectedPrice = computed(() => {
  const selectedDuration = durations.value.find(d => d.duration == form.duration_minutes);
  form.fee_amount = selectedDuration?.price;
  form.currency = selectedDuration?.currency;

  return selectedDuration?.price ?? '0';
});
const selectedPriceCurrancy = computed(() => {
  const selectedDuration = durations.value.find(d => d.duration == form.duration_minutes);
  form.currency = selectedDuration?.currency;

  return selectedDuration?.currency ?? 'USD';
});

const currencySymbols = {
  'USD': '$',
  'EUR': '€',
  'GBP': '£',
  'JPY': '¥',
  'CAD': 'C$',
  'AUD': 'A$',
  'CHF': 'CHF',
  'CNY': '¥',
  'INR': '₹',
  'KRW': '₩',
  // Add more as needed
};

const selectedPriceSymbol = computed(() => {
  const currency = selectedPriceCurrancy.value;
  return currencySymbols[currency] || currency;
});

watch(selectedPrice, (val) => {
  form.fee_amount = val;
});

const setSlot = (slot, idx) => {
  form.appointment_time = slot;

  document.querySelectorAll('.available-badges.bg-warning-gradient').forEach(el => {
    el.classList.remove('bg-warning-gradient');
  });

  document.getElementById(`slot-${idx}`).classList.add('bg-warning-gradient');
}

// Check if a slot is the currently selected one (for edit mode)
const isCurrentSlot = (slotValue) => {
  return form.appointment_time === slotValue;
};

const slots = ref([]);
const getSlots = (showError = false) => {

  if (!form.doctor_id || !form.appointment_date) {
    // Don't show error - form validation handles required fields
    slots.value = [];
    return Promise.resolve();
  }

  // If duration is not selected yet, show message
  if (!form.duration_minutes) {
    slots.value = [];
    return Promise.resolve();
  }

  return axios.post(route('patient.doctors.slots', { doctor_id: form.doctor_id, date: form.appointment_date, duration: form.duration_minutes }))
    .then(response => {
      slots.value = response.data;
      form.appointment_time = '';
    })
    .catch(error => {
      form.appointment_time = '';
    });
}

/**
 * Fetch provider exceptions for the calendar
 * Shows red exception events for doctors who are not available on certain days
 */
const fetchProviderExceptions = (month, year) => {
  axios.post(route('patient.provider.exceptions', { month: month, year: year }))
    .then(response => {
      // Add exception events to the calendar
      const currentEvents = options.events || [];
      const exceptionEvents = response.data.filter(event =>
        !currentEvents.some(e => e.id === event.id)
      );
      options.events = [...currentEvents, ...exceptionEvents];
    })
    .catch(error => {
      console.error("There was an error fetching provider exceptions!", error);
    });
};

const canEditOrCancel = computed(() => {
  const status = appointmentDetail.value?.extendedProps?.status || appointmentDetail.value?.status;
  return ['pending', 'confirmed'].includes(status);
});

const cancelAppointment = async (appointmentDetail) => {
  isModalOpen.value = false;

  const result = await Swal.fire(
    confirmSettings("Are you sure you want to cancel this appointment?")
  );

  if (result.isConfirmed) {
    console.log("Cancelling appointment ID:", appointmentDetail.id);

    await router.post(
      route("appointments.updateStatus", { id: appointmentDetail.id }), // ✅ correct route param
      { status: "cancelled" }, // ✅ request body
      { id: appointmentDetail.id },
      { preserveScroll: true } // ✅ options
    );
    window.location.reload();
  }
};

const editAppointment = (appointment) => {
  console.log("Editing appointment ID:", appointment);
  isModalOpen.value = false; // Close detail modal

  // Populate form with existing data
  form.id = appointment.id;
  speciality_id.value = appointment.extendedProps.speciality_id;
  getDoctors(); // Fetch doctors for the speciality
  form.doctor_id = appointment.extendedProps.doctor_id;
  form.appointment_date = appointment.extendedProps.appointment_date;
  form.appointment_type = appointment.extendedProps.appointment_type;
  form.duration_minutes = appointment.extendedProps.duration_minutes;
  form.currency = appointment.extendedProps.currency;
  form.fee_amount = appointment.extendedProps.fee_amount;
  getSlots(); // Fetch slots for the selected doctor and date
  form.appointment_time = appointment.extendedProps.appointment_time;
  form.reason = appointment.extendedProps.reason;
  form.accept_term_condition = appointment.extendedProps.accept_term_condition;

  // Fetch visit types and durations for the selected doctor
  getDoctorVisitTypes();
  getDuration();

  showModal.value = true; // Open booking/edit modal
};

const rescheduleAppointment = (appointment) => {
  isModalOpen.value = false; // Close detail modal

  // Populate form but clear date/time for rescheduling
  form.id = appointment.id;
  speciality_id.value = appointment.extendedProps.speciality_id;

  // Fetch doctors first, then populate the rest
  getDoctors().then(() => {
    form.doctor_id = appointment.extendedProps.doctor_id;
    form.appointment_type = appointment.extendedProps.appointment_type;
    form.reason = appointment.extendedProps.reason;
    form.accept_term_condition = appointment.extendedProps.accept_term_condition;

    // Get visit types and durations
    getDoctorVisitTypes().then(() => {
      form.duration_minutes = appointment.extendedProps.duration_minutes;

      // After setting duration, get slots
      getSlots();
    });
  });

  // Keep existing date/time for reference but allow user to change
  form.appointment_date = appointment.extendedProps.appointment_date;
  form.appointment_time = appointment.extendedProps.appointment_time;
  slots.value = [];

  showModal.value = true; // Open booking/edit modal
};

const checkTwoHours = (dateTimeString) => {
  if (!dateTimeString) return false;

  // Split date and time
  const parts = dateTimeString.split(' ');
  if (parts.length !== 3) return false;

  const [datePart, timePart, ampm] = parts;

  const [day, month, year] = datePart.split('-').map(Number);
  let [hour, minute] = timePart.split(':').map(Number);

  // Convert to 24-hour format
  if (ampm === 'PM' && hour !== 12) hour += 12;
  if (ampm === 'AM' && hour === 12) hour = 0;

  const appointmentTime = new Date(year, month - 1, day, hour, minute);

  if (isNaN(appointmentTime.getTime())) return false;

  const now = new Date();
  const twoHoursLater = new Date(appointmentTime.getTime() + 2 * 60 * 60 * 1000);

  console.log('---->', dateTimeString);
  console.log(appointmentTime, now, twoHoursLater);
  return now >= appointmentTime && now <= twoHoursLater;
}


const isWithinJoinWindow = (dateTimeString) => {
  if (!dateTimeString) return false;

  const parts = dateTimeString.split(' ');
  if (parts.length !== 3) return false;

  const [datePart, timePart, ampm] = parts;

  const [day, month, year] = datePart.split('-').map(Number);
  let [hour, minute] = timePart.split(':').map(Number);

  // Convert to 24-hour format
  if (ampm === 'PM' && hour !== 12) hour += 12;
  if (ampm === 'AM' && hour === 12) hour = 0;

  const appointmentTime = new Date(year, month - 1, day, hour, minute);

  if (isNaN(appointmentTime.getTime())) return false;

  const now = new Date();

  const fiveMinutesBefore = new Date(appointmentTime.getTime() - 5 * 60 * 1000);
  const tenMinutesAfter = new Date(appointmentTime.getTime() + 10 * 60 * 1000);

  return now >= fiveMinutesBefore && now <= tenMinutesAfter;
}
</script>

<template>
  <div>
    <!-- Calendar -->
    <FullCalendar :options="options" />

    <!-- Custom Tooltip -->
    <div v-if="tooltipState.visible" class="calendar-tooltip"
      :style="{ top: tooltipState.y + 'px', left: tooltipState.x + 'px' }">
      <template v-if="tooltipState.data.doctor_name">
        <div><strong>Doctor:</strong> {{ tooltipState.data.doctor_name }}</div>
        <div v-if="tooltipState.data.appointment_date && tooltipState.data.appointment_time">
          <strong>When:</strong> {{ tooltipState.data.appointment_date }} {{ tooltipState.data.appointment_time }}
        </div>
        <div v-if="tooltipState.data.status">
          <strong>Status:</strong>
          <span class="badge ms-1" :style="{ backgroundColor: getStatusColor(tooltipState.data.status) }">
            {{ tooltipState.data.status }}
          </span>
        </div>
      </template>
      <template v-else>
        <div style="white-space: pre-line;">{{ tooltipState.data.tooltip || tooltipState.data.title }}</div>
      </template>
    </div>

    <!-- Appointment Modal -->
    <div v-if="showModal" class="modal fade show d-block" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-primary">
            <h5 class="modal-title text-white font-bold">Make appointment</h5>
            <button type="button" class="close text-white" @click="closeModal">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <form @submit.prevent="submit">
            <div class="modal-body">
              <div class="row overflow-auto" style="max-height: 60vh; overflow-y: auto;">
                <!-- Speciality -->
                <div class="col-md-6 mb-3">
                  <label>Select Speciality</label>
                  <select v-model="speciality_id" @change="getDoctors()" class="form-control" required>
                    <option disabled value="">Select Speciality</option>
                    <option v-for="speciality in data.specialities" :key="speciality.id" :value="speciality.id">
                      {{ speciality.name }}
                    </option>
                  </select>
                </div>
                <!-- Doctor -->
                <div class="col-md-6 mb-3">
                  <label>Select Doctor</label>
                  <select v-model="form.doctor_id" @change="getDoctorVisitTypes(); getSlots()" class="form-control"
                    required :disabled="!speciality_id">
                    <option value="">Select doctor</option>
                    <option v-for="doctor in doctors" :key="doctor.id" :value="doctor.id">{{
                      doctor?.name || doctor.user?.name
                      }}
                    </option>
                  </select>
                  <InputError class="mt-2" :message="form.errors.doctor_id" />
                </div>

                <!-- Visit Type -->
                <div class="col-md-6 mb-3">
                  <label>Visit Type</label>
                  <select v-model="form.appointment_type" @change="getDuration()" class="form-control" required
                    :disabled="!form.doctor_id">
                    <option value="">Select Visit Type</option>
                    <option v-for="type in visitTypes" :key="type" :value="type">
                      {{ type }}
                    </option>
                  </select>
                </div>
                <!-- Duration -->
                <div class="col-md-6 mb-3">
                  <label>Duration</label>
                  <select v-model="form.duration_minutes" @change="getDuration(); getSlots()" class="form-control"
                    required :disabled="!form.doctor_id">
                    <option value="">Select Visit duration</option>
                    <option v-for="row in durations" :key="row" :value="row.duration">
                      {{ row.duration }} min
                    </option>
                  </select>
                  <div v-if="form.duration_minutes" class="bold mt-2">Doctor duration amount:{{ selectedPriceSymbol }}
                    {{
                      selectedPrice }}</div>
                </div>

                <div class="col-md-6 mb-3">
                  <label>Appointment Date</label>
                  <DatePicker @change="getSlots()" v-model="form.appointment_date" />
                  <InputError class="mt-2" :message="form.errors.date" />
                </div>

                <div class="col-md-6 mb-3">
                  <label>Appointment Time</label>
                  <input type="text" readonly v-model="form.appointment_time" class="form-control" />
                  <InputError class="mt-2" :message="form.errors.appointment_time" />
                </div>

                <div class="mb-3 mt-3">
                  <label class="font-bold">Available Slots</label>
                  <div v-if="!form.appointment_type">
                    <p class="text-danger">Please select visit type to see available slots.</p>
                  </div>
                  <div v-if="slots.length === 0 && form.appointment_type">
                    <p class="text-danger">No slots available for the selected date. Please choose another date.</p>
                  </div>
                  <div class="d-flex flex-wrap gap-2">
                    <template v-for="(row, idx) in slots" :key="idx">
                      <span :id="`slot-${idx}`" class="badge available-badges bg-primary cursor-pointer"
                        v-if="row.status === 'available' || isCurrentSlot(row.value)" @click="setSlot(row.value, idx)">
                        {{ row.slot }}
                      </span>
                      <span v-else class="badge bg-gray">
                        {{ row.slot }}
                      </span>
                    </template>
                  </div>
                </div>

                <!-- Reason -->
                <div class="col-md-12 mb-3">
                  <label>Reason</label>
                  <textarea v-model="form.reason" class="form-control" placeholder="Enter notes..."></textarea>
                  <InputError class="mt-2" :message="form.errors.notes" />
                </div>
                <div class="col-md-12 mb-3">
                  <Checkbox v-model="form.accept_term_condition" :label="'You must agree to share data with doctor'"
                    class="me-1" /> Are You Share data with doctor?
                  <InputError class="mt-2" :message="form.errors.terms" />
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-danger" @click="closeModal" :disabled="loading">Close</button>
              <button type="submit" class="btn btn-primary" :disabled="loading">
                <span v-if="loading" role="status" aria-hidden="true"></span>
                {{ loading ? 'Booking...' : 'Make appointment' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal backdrop -->
    <div v-if="showModal" class="modal-backdrop fade show"></div>
  </div>

  <Modal :isOpen="isModalOpen" title="Appointment" @close="closeModal" size="lg">
    <div class="p-4">
      <h5 class="font-bold mb-3">Appointment Details</h5>
      <div class="row mb-3">
        <div class="col-md-6">
          <p><strong>Doctor:</strong> {{ appointmentDetail.extendedProps?.doctor_name }}</p>
          <p><strong>Speciality:</strong> {{ appointmentDetail.extendedProps?.speciality_name }}</p>
          <p><strong>Date:</strong> {{ appointmentDetail.extendedProps?.appointment_date }}</p>
          <p><strong>Time:</strong> {{ appointmentDetail.extendedProps?.appointment_time }}</p>
        </div>
        <div class="col-md-6">
          <p><strong>Status:</strong> <span class="badge"
              :style="{ backgroundColor: appointmentDetail.backgroundColor }">{{ appointmentDetail.extendedProps?.status
              }}</span></p>
          <p><strong>Payment Status:</strong> {{ appointmentDetail.extendedProps?.payment_status }}</p>
          <p v-if="appointmentDetail.extendedProps?.reason"><strong>Reason:</strong> {{
            appointmentDetail.extendedProps.reason }}</p>
          <p
            v-if="appointmentDetail.extendedProps?.status === 'rejected' && appointmentDetail.extendedProps?.reject_reason">
            <strong>Rejection Reason:</strong> {{ appointmentDetail.extendedProps.reject_reason }}
          </p>
        </div>
      </div>

      <hr />

      <!-- Status-specific actions/messages -->
      <div
        v-if="appointmentDetail.extendedProps?.status === 'confirmed' && appointmentDetail.extendedProps?.payment_status === 'paid'">
        <p class="text-success">Your appointment is confirmed. You can join the call at the scheduled time.</p>
        <a v-if="appointmentDetail.extendedProps.call_link && isWithinJoinWindow(appointmentDetail?.extendedProps?.call_start_at)"
          :href="appointmentDetail.extendedProps.call_link" target="_blank" class="btn btn-success">
          Go to Call
        </a>
        <a href="#" class="btn btn-info">
          Go to Call
        </a>
      </div>

      <div
        v-else-if="appointmentDetail.extendedProps?.status === 'confirmed' && appointmentDetail.extendedProps?.payment_status !== 'paid'">
        <p class="text-warning">Your appointment is confirmed, but payment is pending.</p>
        <a :href="route('patient.appointment.payment', appointmentDetail.id)" target="_blank" class="btn btn-primary">
          Pay Now
        </a>
      </div>

      <div v-else-if="appointmentDetail.extendedProps?.status === 'pending'">
        <p class="text-info">Your appointment is pending approval from the doctor.</p>
      </div>

      <div v-else-if="appointmentDetail.extendedProps?.status === 'cancelled'">
        <p class="text-danger">This appointment has been cancelled.</p>
      </div>

      <div v-else-if="appointmentDetail.extendedProps?.status === 'rejected'">
        <p class="text-danger">This appointment has been rejected.</p>
      </div>
    </div>

    <div class="modal-footer">

      <template v-if="canEditOrCancel && checkTwoHours(appointmentDetail?.extendedProps?.created_at)">
        <button type="button" class="btn btn-danger" @click="cancelAppointment(appointmentDetail)">
          Cancel Appointment
        </button>
        <!-- Add Reschedule and Edit buttons here when their functionality is ready -->
        <button type="button" class="btn btn-warning"
          @click="rescheduleAppointment(appointmentDetail)">Reschedule</button>
        <button type="button" class="btn btn-info" @click="editAppointment(appointmentDetail)">Edit</button>
      </template>

      <template v-if="!checkTwoHours(appointmentDetail?.extendedProps?.created_at)">
        <button type="button" class="btn btn-danger" @click="closeModal">
          Close
        </button>
      </template>

    </div>
  </Modal>
</template>

<style>
.fc-event {
  cursor: pointer;
}

.fc-daygrid-day-top {
  cursor: pointer;
}

/* Exception event styling */
.fc-event.exception-event {
  background-color: #f35353 !important;
  border-color: #f35353 !important;
  color: #ffffff !important;
  opacity: 0.9;
}

.fc-event.exception-event:hover {
  opacity: 1;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.calendar-tooltip {
  position: fixed;
  z-index: 10000;
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  pointer-events: none;
  transform: translate(-50%, -100%);
  margin-top: -8px;
  font-size: 0.9rem;
  color: #333;
  min-width: 200px;
}

.calendar-tooltip::after {
  content: "";
  position: absolute;
  top: 100%;
  left: 50%;
  margin-left: -6px;
  border-width: 6px;
  border-style: solid;
  border-color: white transparent transparent transparent;
}
</style>
