<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import axios from 'axios';
import { useForm,router } from '@inertiajs/vue3';
import DatePicker from '@/Components/Common/Input/BaseDatePicker.vue';

import Table from "@/Components/Table/Table.vue";
import { Link } from '@inertiajs/vue3';
import Modal from '@/Components/Common/Modal.vue'; // Assuming Modal component is available
import AppointmentDetailModal from '../Modals/AppointmentDetailModal.vue';
    
import Swal from 'sweetalert2'

const props = defineProps({
    dashboardData: Object,
});

const selectedAppointment = ref(null);
const keyword = ref(''); // Added missing keyword ref

function openAppointmentModal(appointment) {
    selectedAppointment.value = appointment;
    showPendingAppointmentModal.value = false; 
    showAppointmentModal.value = true;
}

const appointments = computed(() => props.dashboardData?.appointments ?? []);
const showAppointmentModal = ref(false);
const invoices = computed(() => props.dashboardData?.invoices ?? []);

// Fixed appointmentsByDate computation
const appointmentsByDate = computed(() => {
    const map = new Map();
    if (!appointments.value.data) return map;

    for (const appt of appointments.value.data) {
        const date = new Date(appt.appointment_date);
        const key = `${date.getFullYear()}-${date.getMonth()}-${date.getDate()}`; // Using day of the month
        if (!map.has(key)) {
            map.set(key, []);
        }
        map.get(key).push(appt);
    }
    return map;
});

// Calendar functionality
const today = new Date();
const month = ref(today.getMonth());
const year = ref(today.getFullYear());

const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

function startOfMonth(y, m) {
    return new Date(y, m, 1);
}

function daysInMonth(y, m) {
    return new Date(y, m + 1, 0).getDate();
}

const days = computed(() => {
    const y = year.value, m = month.value;
    const first = startOfMonth(y, m);
    const total = daysInMonth(y, m);
    const startWeekday = first.getDay();
    const cells = [];

    // Fill leading blanks
    for (let i = 0; i < startWeekday; i++) cells.push({ empty: true });

    for (let d = 1; d <= total; d++) {
        const isToday = y === today.getFullYear() && m === today.getMonth() && d === today.getDate();
        const key = `${y}-${m}-${d}`; // Key matches appointmentsByDate
        const events = appointmentsByDate.value?.get(key) || [];
        cells.push({
            day: d,
            isToday,
            hasEvent: events.length > 0,
            empty: false
        });
    }

    // Ensure full weeks (optional trailing blanks)
    while (cells.length % 7 !== 0) cells.push({ empty: true });
    return cells;
});

function getAriaLabelForDay(day) {
    if (day.empty) return 'Empty day';
    const date = new Date(year.value, month.value, day.day);
    const label = date.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    const details = [];
    if (day.isToday) details.push('Today');
    const events = appointmentsByDate.value?.get(`${year.value}-${month.value}-${day.day}`) || [];
    if (events.length > 0) details.push(`${events.length} appointment${events.length > 1 ? 's' : ''}`);
    return `${label}${details.length > 0 ? `, ${details.join(', ')}` : ''}`;
}

function prevMonth() {
    if (month.value === 0) {
        month.value = 11;
        year.value -= 1;
    } else {
        month.value -= 1;
    }
}

function nextMonth() {
    if (month.value === 11) {
        month.value = 0;
        year.value += 1;
    } else {
        month.value += 1;
    }
}

const selectedDate = ref(null);
const showAppointmentsListModal = ref(false);
const showPendingAppointmentModal = ref(false); // New state for pending appointment modal
const selectedPendingAppointment = ref(null); // New state for selected pending appointment
const selectedAppointmentForApproval = ref(null);
const focusedDate = ref(new Date(today.getFullYear(), today.getMonth(), today.getDate()));

function selectDate(day) {
     if (day.empty) {
        selectedDate.value = null;
        return;
    }
    const y = year.value;
    const m = month.value;
    const d = day.day;
    selectedDate.value = new Date(y, m, d);
    focusedDate.value = new Date(y, m, d);

    const key = `${y}-${m}-${d}`; // Key matches appointmentsByDate
    const events = appointmentsByDate.value?.get(key) || [];
     if (events.length === 1) {

        if (events[0].status === 'pending') { 
            openPendingAppointmentModal(events[0]);
        } else { 

            openAppointmentModal(events[0]);
        }
    } else if (events.length > 1) {
       openAppointmentModal(events);
    } else {

        openCreateAppointmentModal(new Date(y, m, d));
    }
}

function setSelectedAppointmentForApproval(appointment) {
    selectedAppointmentForApproval.value = appointment;
    openPendingAppointmentModal(appointment);
}

function isSelected(day) {
    if (!selectedDate.value || day.empty) return false;
    const d = day.day;
    return selectedDate.value.getFullYear() === year.value &&
        selectedDate.value.getMonth() === month.value &&
        selectedDate.value.getDate() === d;
}

const selectedDateAppointments = computed(() => {
    if (!selectedDate.value) return [];
    const key = `${selectedDate.value.getFullYear()}-${selectedDate.value.getMonth()}-${selectedDate.value.getDate()}`;
    return appointmentsByDate.value?.get(key) || [];

});

function openPendingAppointmentModal(appointment) {
    selectedPendingAppointment.value = appointment;
    showPendingAppointmentModal.value = true;
}

const calendarGrid = ref(null);

watch([month, year], async () => {
    await nextTick();
    focusCell();
});

function focusCell() {
    const day = focusedDate.value.getDate();
    const cell = calendarGrid.value?.querySelector(`[data-day="${day}"]`);
    cell?.focus();
}

function handleCalendarKeyDown(e) {
    let newFocusedDate = new Date(focusedDate.value);
    let needsFocusUpdate = true;

    switch (e.key) {
        case 'ArrowRight':
            newFocusedDate.setDate(newFocusedDate.getDate() + 1);
            break;
        case 'ArrowLeft':
            newFocusedDate.setDate(newFocusedDate.getDate() - 1);
            break;
        case 'ArrowDown':
            newFocusedDate.setDate(newFocusedDate.getDate() + 7);
            break;
        case 'ArrowUp':
            newFocusedDate.setDate(newFocusedDate.getDate() - 7);
            break;
        case 'PageDown':
            e.preventDefault();
            nextMonth();
            newFocusedDate.setMonth(newFocusedDate.getMonth() + 1);
            break;
        case 'PageUp':
            e.preventDefault();
            prevMonth();
            newFocusedDate.setMonth(newFocusedDate.getMonth() - 1);
            break;
        case 'Home':
            e.preventDefault();
            newFocusedDate.setDate(newFocusedDate.getDate() - newFocusedDate.getDay());
            break;
        case 'End':
            e.preventDefault();
            newFocusedDate.setDate(newFocusedDate.getDate() + (6 - newFocusedDate.getDay()));
            break;
        case 'Enter':
        case ' ':
            e.preventDefault();
            const dayObject = days.value.find(d => !d.empty && d.day === focusedDate.value.getDate());
            if (dayObject) selectDate(dayObject);
            needsFocusUpdate = false;
            break;
        default:
            needsFocusUpdate = false;
    }

    if (needsFocusUpdate) {
        e.preventDefault();
        focusedDate.value = newFocusedDate;
        if (newFocusedDate.getMonth() !== month.value) {
            month.value = newFocusedDate.getMonth();
            year.value = newFocusedDate.getFullYear();
        } else {
            focusCell();
        }
    }
}

// Table columns
const columns = [
    { label: "Patient Name", key: "patient.name" },
    { label: "Date", key: "appointment_date" },
    { label: "Reason", key: "reason" },
    { label: "Type", key: "appointment_type" },
    { label: "Status", key: "status", slot:"status"},
    { label: "Created By", key: "created_by_name" },
];

// Helper function for styling
const badgeClasses = {
    status: {
        'scheduled': 'bg-primary',
        'completed': 'bg-success',
        'cancelled': 'bg-danger',
        'no-show': 'bg-warning',
        'confirmed': 'bg-info'
    },
    type: {
        'consultation': 'bg-info text-dark',
        'follow-up': 'bg-success',
        'checkup': 'bg-primary',
        'emergency': 'bg-danger'
    },
    mode: {
        'in-person': 'bg-primary',
        'virtual': 'bg-info',
        'phone': 'bg-warning text-dark'
    }
};

const getBadgeClass = (category, value ) => {
    return badgeClasses[category]?.[value?.toLowerCase()] || 'bg-secondary';
};

// Action methods
const rescheduleAppointment = () => {
    // Implement reschedule logic
};

const cancelAppointment = async () => {
    showAppointmentModal.value=false;
  const result = await Swal.fire(
    confirmSettings("Are you sure you want to cancel this appointment?")
  );

  if (result.isConfirmed) {
    await router.post(
      route("appointments.updateStatus", { id: selectedAppointment.value.id }), 
      { status: "cancelled" }, 
      {
        preserveScroll: true
      }
    );

    window.location.reload();
  }
};


const editAppointment = () => {
    // Implement edit logic
};

const approveAppointment = async (appointmentId) => {
    try {
        await axios.post(route('appointments.updateStatus', { id: appointmentId }), { status: 'confirmed' });

        // Refresh appointments or update status locally
        // For now, let's just close the modal and suggest a full page reload or data re-fetch
        closePendingAppointmentModal();
        // You might want to emit an event to the parent or re-fetch dashboardData
        window.location.reload(); // Simple reload for demonstration
        // Patient notification is triggered by the backend
    } catch (error) {
        console.error('Error approving appointment:', error);
        // Handle error (e.g., show a toast message)
    }
};

const rejectAppointment = async (appointmentId) => {
    if (!confirm('Are you sure you want to reject this appointment?')) return;
    try {
        await axios.post(route('appointments.updateStatus', { id: appointmentId }), { status: 'rejected' });
        // Refresh appointments or update status locally
        closePendingAppointmentModal();
        window.location.reload(); // Simple reload for demonstration
        // Patient notification is triggered by the backend
    } catch (error) {
        console.error('Error rejecting appointment:', error);
        // Handle error (e.g., show a toast message)
    }
};

function closePendingAppointmentModal() {
    selectedAppointmentForApproval.value = null;
    showPendingAppointmentModal.value = false;
}
// Create Appointment Modal Logic
const showCreateAppointmentModal = ref(false);
const newAppointmentForm = useForm({
    patient_id: null,
    appointment_date: '',
    appointment_time: '09:00',
    reason: '',
    appointment_type: 'consultation',
    appointment_mode: 'in-person',
});
const patientSearchQuery = ref('');
const patientSearchResults = ref([]);
const isSearching = ref(false);

function openCreateAppointmentModal(date) {
    newAppointmentForm.reset();
    newAppointmentForm.appointment_date = date.toISOString().split('T')[0];
    showCreateAppointmentModal.value = true;
}

function closeCreateAppointmentModal() {
    showCreateAppointmentModal.value = false;
}

function searchPatients() {
    axios.get(route('doctor.search.patient', { q: patientSearchQuery.value })).then((response) => {
        patientSearchResults.value = response.data;
        isSearching.value = false;
    });
};

function selectPatient(patient) {
    newAppointmentForm.patient_id = patient.id;
    patientSearchQuery.value = patient.user.name;
    patientSearchResults.value = [];
}

function submitNewAppointment() {
    const dateTime = `${newAppointmentForm.appointment_date} ${newAppointmentForm.appointment_time}`;
    newAppointmentForm
                .transform(data => ({
                    ...data,
                    appointment_date: dateTime,
                }))
                .post(route('doctor.appointments.store'), { // Ensure this route exists
                    onSuccess: () => {
                        closeCreateAppointmentModal();
                        // Optionally, you can show a success toast message here
                    },
                    onError: () => {
                        // Handle errors, e.g., show a toast message
                    }
                });
}

function closeAppointmentModal() {
    showAppointmentModal.value = false;
}
</script>
<template>
<AuthLayout title="Dashboard" description="Doctor dashboard overview and statistics" heading="">
        <div class="row">

            <div class="col-md-6 col-lg-3">
                <Link :href="route('doctor.messages.index')" class="iq-card iq-card-block iq-card-stretch iq-card-height">
                     <div class="iq-card-body">
                        <div class="text-center"><span>Messages</span></div>
                        <div class="d-flex justify-content-between align-items-center m13">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter fs-34">{{ dashboardData.messages }}</span></h2>
                                
                            </div>
                            <div class="iq-iconbox alert-info">
                                <i class="fa fa-envelope"></i>
                            </div>
                        </div>
                        
                 </div>
                </Link>
            </div>
            <div class="col-md-6 col-lg-3">
                <Link :href="route('doctor.encounters.index')" class="iq-card iq-card-block iq-card-stretch iq-card-height">
                     <div class="iq-card-body">
                        <div class="text-center"><span>Encounters to Complete</span></div>
                        <div class="d-flex justify-content-between align-items-center m-1">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter fs-34">{{ dashboardData.encounters.total }}</span></h2>
                                
                            </div>
                            <div class="iq-iconbox iq-bg-info">
                                <i class="fa fa-stethoscope"></i>
                            </div>
                        </div>
                        
                 </div>
                </Link>
            </div>
            <div class="col-md-6 col-lg-3">
                <Link :href="route('doctor.patients')" class="iq-card iq-card-block iq-card-stretch iq-card-height">
                     <div class="iq-card-body">
                        <div class="text-center"><span>Patients</span></div>
                        <div class="d-flex justify-content-between align-items-center m-1">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter fs-34">{{ dashboardData.patients?.total }}</span></h2>
                                <div class="small text-muted">Active: {{ dashboardData.patients?.active }} | Inactive: {{ dashboardData.patients?.inactive }}</div>
                            </div>
                            <div class="iq-iconbox iq-bg-success">
                                <i class="fa fa-user"></i>
                            </div>
                        </div>
                        
                 </div>
                </Link>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <Link :href="route('doctor.schedule.index')" class="iq-card iq-card-block iq-card-stretch iq-card-height">
                     <div class="iq-card-body">
                        <div class="text-center"><span>Calendar</span></div>
                        <div class="d-flex justify-content-between align-items-center m-1">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter fs-34">{{ dashboardData?.calendars?.total??0 }}</span></h2>
                                 <div class="small text-muted">Pending: {{ dashboardData.calendars?.pending??0 }} | Completed: {{ dashboardData.calendars?.completed??0 }}</div>
                            </div>
                            <div class="iq-iconbox iq-spring-green ">
                                <i class="fa fa-calendar"></i>
                            </div>
                        </div>
                        
                 </div>
                </Link>
            </div>
            <div class="col-md-6 col-lg-3">
                <Link :href="route('doctor.alerts.index')" class="iq-card iq-card-block iq-card-stretch iq-card-height">
                     <div class="iq-card-body">
                        <div class="text-center"><span>Reminders</span></div>
                        <div class="d-flex justify-content-between align-items-center m-1">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter fs-34">{{ dashboardData?.reminders }}</span></h2>
                                
                            </div>
                            <div class="iq-iconbox iq-pink">
                                <i class="fa fa-bell"></i>
                            </div>
                         
                    </div>
                </div>
                </Link>
            </div>
            <div class="col-md-6 col-lg-3">
                <Link :href="route('doctor.documents.index')" class="iq-card iq-card-block iq-card-stretch iq-card-height">
                     <div class="iq-card-body">
                        <div class="text-center"><span>Documents</span></div>
                        <div class="d-flex justify-content-between align-items-center m-1">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter fs-34">{{ dashboardData?.documents?.total}}</span></h2>
                                
                            </div>
                            <div class="iq-iconbox iq-warning">
                                <i class="fa fa-file-o"></i>
                            </div>
                        </div>
                        
                 </div>
                </Link>
            </div>
            <div class="col-md-6 col-lg-3">
                <Link :href="route('doctor.finance.bills_to_submit')" class="iq-card iq-card-block iq-card-stretch iq-card-height">
                     
                    <div class="iq-card-body">
                        <div class="text-center"><span>Bills to Process</span></div>
                        <div class="d-flex justify-content-between align-items-center m-1">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter fs-34">{{ dashboardData?.bills_to_process }}</span>
                                </h2>
                               
                            </div>
                           <div class="iq-iconbox iq-purple"><i class="fa fa-money"></i></div>
                        </div>
                        
                    </div>
                 </Link>
            </div>
            <div class="col-md-6 col-lg-3">
                <Link :href="route('doctor.results.index')" class="iq-card iq-card-block iq-card-stretch iq-card-height">
                     <div class="iq-card-body">
                        <div class="text-center"><span>Test Results to Review</span></div>
                        <div class="d-flex justify-content-between align-items-center m-1">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter fs-34">{{ dashboardData.test_results_to_review
                                        }}</span></h2>
                               
                            </div>
                             <div class="iq-iconbox iq-bg-primary"><i class="fa fa-flask"></i></div>
                        </div>
                        
                    </div>
                 </Link>
            </div>
            <div class="row">
                <!-- Your dashboard cards remain the same -->

                <div class="col-12 col-lg-7">
                    <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
                        <div class="iq-card-body smaill-calender-home">
                            <div class="small-calendar">
                                <div class="cal-header d-flex justify-content-between align-items-center">
                                    <button class="btn btn-sm btn-light" @click="prevMonth">‹</button>
                                    <div class="cal-title text-center">
                                        <strong>{{ monthNames[month] }} {{ year }}</strong>
                                    </div>
                                    <button class="btn btn-sm btn-light" @click="nextMonth">›</button>
                                </div>

                                <div class="cal-grid mt-2" role="grid" aria-labelledby="cal-title" ref="calendarGrid" @keydown="handleCalendarKeyDown">
                                    <div role="row" class="cal-weekday-row">
                                        <div class="cal-weekday" role="columnheader" v-for="wd in weekDays" :key="wd">{{ wd }}</div>
                                    </div>
                                    <div class="cal-cell" v-for="(c, idx) in days" :key="idx"
                                        role="gridcell"
                                        :data-day="c.day"
                                        :aria-selected="isSelected(c)"
                                        :aria-disabled="c.empty"
                                        :aria-label="getAriaLabelForDay(c) "
                                        :tabindex="!c.empty && focusedDate.getDate() === c.day && focusedDate.getMonth() === month && focusedDate.getFullYear() === year ? 0 : -1"
                                        @click="selectDate(c)" 
                                        :class="{
                                            'empty': c.empty,
                                            'today': c.isToday,
                                            'has-event': c.hasEvent,
                                            'pending-event': c.hasEvent && appointmentsByDate.value?.get(`${year.value}-${month.value}-${c.day}`)?.some(appt => appt.status === 'pending'),
                                            'selected': isSelected(c),
                                            'focused': !c.empty && focusedDate.getDate() === c.day && focusedDate.getMonth() === month && focusedDate.getFullYear() === year
                                        }">
                                        <span v-if="!c.empty">{{ c.day }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
                        <div class="iq-card-header d-flex justify-content-between">
                            <div class="iq-header-title">
                                <h4 class="card-title fs-22">Appointment List</h4>
                            </div>
                        </div>
                        <div class="iq-list-card-body">

                        <!-- Desktop Table -->
                        <div class="d-none d-md-block p-3">
                            <ul class="list-inline m-0 p-0">
                                <li v-for="appt in appointments.data?.slice(0, 5)" :key="appt.id" class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                                    <div class="d-flex align-items-center">
                                        <div class="icon iq-icon-box rounded-circle iq-bg-primary mr-3">
                                            <i class="ri-user-fill"></i>
                                        </div>
                                        <div class="media-body">
                                            <h6 class="mb-0">{{ appt.patient?.name || appt.patient?.user?.name || '-' }}</h6>
                                            <p class="mb-0 text-muted font-size-12">
                                                <i class="ri-calendar-line mr-1"></i>{{ appt.appointment_date }}
                                            </p>
                                            <p class="mb-0 text-muted font-size-12" v-if="appt.reason">
                                                <i class="ri-information-line mr-1"></i>{{ appt.reason }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                <span
                                    class="badge"
                                    :class="getBadgeClass('status', appt.status)"
                                >
                                    {{ appt.status }}
                                </span>
                                    </div>
                                </li>
                                <li v-if="!appointments.data?.length" class="text-center text-muted">
                                    No appointments found
                                </li>
                            </ul>
                        </div>

                        <!-- Mobile Cards -->
                      <div class="d-block d-md-none appointment-mobile-list">

                      <div
                            v-for="appt in appointments.data?.slice(0, 5)"
                            :key="appt.id"
                            class="appointment-card"
                            @click="openAppointmentModal(appt)"
                        >

                            <div class="mobile-row">
                                <span class="label">Patient Name</span>
                                <span class="value">{{ appt.patient?.name ||appt.patient?.user?.name || '-' }}</span>
                            </div>

                            <div class="mobile-row">
                                <span class="label">Date</span>
                                <span class="value">{{ appt.appointment_date }}</span>
                            </div>

                            <div class="mobile-row">
                                <span class="label">Reason</span>
                                <span class="value">{{ appt.reason || '-' }}</span>
                            </div>

                            <div class="mobile-row">
                                <span class="label">Type</span>
                                <span class="value">{{ appt.appointment_type || '-' }}</span>
                            </div>

                            <div class="mobile-row">
                                <span class="label">Mode</span>
                                <span class="value">{{ appt.appointment_mode || '-' }}</span>
                            </div>

                            <div class="mobile-row">
                                <span class="label">Status</span>
                                <span
                                    class="badge"
                                    :class="getBadgeClass('status', appt.status)"
                                >
                                    {{ appt.status }}
                                </span>
                            </div>

                            <div class="mobile-row">
                                <span class="label">Created By</span>
                                <span class="value">{{ appt.created_by_name || '-' }}</span>
                            </div>

                        </div>

                        <div v-if="!appointments.data?.length" class="text-center text-muted py-3">
                            No appointments found
                        </div>

                    </div>


                    </div>

                    </div>

                </div>
 
</div>
            <div class="col-12 order-2 order-lg-3">
                <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
                    <div class="iq-card-header d-flex justify-content-between">
                        <div class="iq-header-title">
                            <h4 class="card-title fs-22">Open Invoices</h4>
                        </div>
                        <div class="iq-card-header-toolbar d-flex align-items-center">
                            <div class="dropdown">
                                <span class="dropdown-toggle text-primary" id="dropdownMenuButton5"
                                    data-toggle="dropdown">
                                    <i class="ri-more-fill"></i>
                                </span>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton5">
                                    <Link class="dropdown-item" :href="route('doctor.billing.index')"><i
                                        class="ri-eye-fill mr-2"></i>View</Link>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="iq-card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-borderless">
                                <thead>
                                    <tr>
                                        <th scope="col">Patient Name</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Due Date</th>
                                        <th scope="col">Invoice</th>
                                        <th scope="col" class="text-right">Amount</th>
                                        <th scope="col" class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(invoice, idx) in invoices" :key="invoice.id ?? idx">
                                        <td>{{ invoice.patient.name ?? '-' }}</td>
                                        <td>{{ invoice.date }}</td>
                                        <td>{{ invoice.due_date }}</td>
                                        <td>{{ invoice.invoice_no ?? invoice.id }}</td>
                                        <td class="text-right">${{ invoice.amount?.toFixed(2) ?? '0.00' }}</td>
                                        <td class="text-center">
                                            <div class="badge badge-pill" :class="{
                                                'iq-bg-success': (invoice.status || '').toLowerCase() === 'paid',
                                                'iq-bg-danger': (invoice.status || '').toLowerCase() === 'past due'
                                            }">
                                                {{ invoice.status ?? 'N/A' }}
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!invoices.length">
                                        <td colspan="7" class="text-center text-muted">No open invoices</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
         <!-- Appointment Details Modal -->
        <Modal :isOpen="showAppointmentModal" @close="closeAppointmentModal" size="xl">
            <AppointmentDetailModal title="Appointment Details"
                :selected-appointment="selectedAppointment"
                policy="doctor"
                @close="closeAppointmentModal"
                @approve="approveAppointment"
                @reject="rejectAppointment"
                @reschedule="rescheduleAppointment"
                @edit="editAppointment"
                @cancel="cancelAppointment" />
        </Modal>

        <!-- Pending Appointment Modal -->
        <Modal :isOpen="showPendingAppointmentModal" @close="closePendingAppointmentModal" size="xl">
            <AppointmentDetailModal
                :selected-appointment="selectedPendingAppointment"
                policy="doctor"
                @close="closePendingAppointmentModal"
                @approve="approveAppointment"
                @reject="rejectAppointment"
                @reschedule="rescheduleAppointment"
                @edit="editAppointment"
                @cancel="cancelAppointment" />
        </Modal>
        <!-- Create Appointment Modal -->
        <Modal :show="showCreateAppointmentModal" @close="closeCreateAppointmentModal" maxWidth="xl">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Create New Appointment
                </h2>

                <form @submit.prevent="submitNewAppointment" class="mt-6 space-y-6">
                    <!-- Patient Search -->
                    <div class="relative">
                        <label for="patient-search" class="form-label">Patient</label>
                        <input id="patient-search" type="text" class="form-control" v-model="patientSearchQuery" @input="searchPatients" placeholder="Search for a patient..." autocomplete="off">
                        <div v-if="isSearching" class="p-2">Searching...</div>
                        <ul v-if="patientSearchResults.length" class="list-group position-absolute w-100" style="z-index: 1000;">
                            <li v-for="patient in patientSearchResults" :key="patient.id" @click="selectPatient(patient)" class="list-group-item list-group-item-action cursor-pointer">
                                {{ patient.user.name }} (ID: {{ patient.id }})
                            </li>
                        </ul>
                        <div v-if="newAppointmentForm.errors.patient_id" class="text-danger mt-1">{{ newAppointmentForm.errors.patient_id }}</div>
                    </div>

                    <!-- Date and Time -->
                    <div class="row">
                        <div class="col-md-6">
                            <label for="appt-date" class="form-label">Date</label>
                            <DatePicker id="appt-date"   v-model="newAppointmentForm.appointment_date" required/>
                             <div v-if="newAppointmentForm.errors.appointment_date" class="text-danger mt-1">{{ newAppointmentForm.errors.appointment_date }}</div>
                        </div>
                        <div class="col-md-6">
                            <label for="appt-time" class="form-label">Time</label>
                            <DatePicker id="appt-time" type="time"  v-model="newAppointmentForm.appointment_time" required/>
                        </div>
                    </div>

                    <!-- Appointment Type and Mode -->
                    <div class="row">
                        <div class="col-md-6">
                            <label for="appt-type" class="form-label">Type</label>
                            <select id="appt-type" class="form-select" v-model="newAppointmentForm.appointment_type">
                                <option value="consultation">Consultation</option>
                                <option value="follow-up">Follow-up</option>
                                <option value="checkup">Checkup</option>
                                <option value="emergency">Emergency</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="appt-mode" class="form-label">Mode</label>
                            <select id="appt-mode" class="form-select" v-model="newAppointmentForm.appointment_mode">
                                <option value="in-person">In-Person</option>
                                <option value="virtual">Virtual</option>
                                <option value="phone">Phone</option>
                            </select>
                        </div>
                    </div>

                    <!-- Reason for Visit -->
                    <div>
                        <label for="reason" class="form-label">Reason for Visit</label>
                        <textarea id="reason" class="form-control" v-model="newAppointmentForm.reason" rows="3" placeholder="Enter reason for the appointment"></textarea>
                        <div v-if="newAppointmentForm.errors.reason" class="text-danger mt-1">{{ newAppointmentForm.errors.reason }}</div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-danger" @click="closeCreateAppointmentModal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" :disabled="newAppointmentForm.processing">
                            <span v-if="newAppointmentForm.processing" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Create Appointment
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
     </AuthLayout>
</template>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}

.small-calendar {
    max-width: 100%;
    user-select: none;
    padding: 0.5rem;
}

.cal-header {
    gap: .5rem;
}

.cal-title {
    font-size: 0.95rem;
}

.cal-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
    margin-top: 6px;
}
.cal-weekday-row {
    display: contents; /* Allows children to be direct grid items */
}


.cal-weekday {
    font-size: 0.75rem;
    text-align: center;
    color: #6c757d;
    padding: 4px 0;
}

.cal-cell {
    min-height: 34px;
    padding: 6px 4px;
    text-align: center;
    border-radius: 6px;
    font-size: 0.9rem;
    position: relative;
}

.cal-cell.empty {
    background: transparent;
    color: transparent;
    pointer-events: none;
}

.cal-cell.has-event {
    background-color: rgba(27, 225, 179, 0.1);
    color: #1be1b3;
}

.cal-cell.today {
    background: #0d6efd;
    color: #fff;
    font-weight: 600;
}
.cal-cell:focus, .cal-cell.focused {
    outline: 2px solid #1be1b3;
    outline-offset: 2px;
}

.cal-cell.selected {
    background-color: #1be1b3;
    color: white;
}

.cal-cell.selected.today {
    background-color: #1be1b3;
}

.appointments-list {
    padding: 0 0.5rem;
}

.appointments-list h6 {
    font-size: 0.9rem;
    font-weight: bold;
}

.appointments-list ul {
    margin-top: 10px;
    max-height: 150px;
    /* Or whatever height fits */
    overflow-y: auto;
}

.appointments-list li {
    font-size: 0.85rem;
    line-height: 1.4;
}

.cal-cell.has-event::after {
    content: '';
    position: absolute;
    bottom: 6px;
    left: 50%;
    transform: translateX(-50%);
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background-color: #1be1b3;
}

.cal-cell.selected.has-event::after {
    background-color: #fff;
}

.cal-cell.pending-event::after {
    background-color: #ffc107; /* Yellow for pending */
}

.cal-cell.today.has-event::after {
    background-color: white;
}

.btn-sm {
    padding: .25rem .5rem;
    font-size: .85rem;
}

.appointment-details .detail-section {
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 1rem;
}

.appointment-details .detail-section:last-child {
    border-bottom: none;
}

.appointment-details .section-title {
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.reason-box,
.notes-box {
    min-height: 80px;
    white-space: pre-wrap;
    font-style: italic;
}

.detail-section p {
    margin-bottom: 0.5rem;
}

.detail-section strong {
    color: #495057;
    min-width: 120px;
    display: inline-block;
}

.tooltip-header {
    background-color: #f8f9fa;
    padding: 8px 12px;
    border-bottom: 1px solid #dee2e6;
    font-weight: 600;
    color: #212529;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}

.status-tag {
    display: inline-block;
    padding: 1px 6px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 500;
    margin-left: 4px;
}
.appointment-mobile-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
    padding: 8px;
}

.appointment-card {
    background: #fff;
    border-radius: 12px;
    padding: 14px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.06);
    border-left: 4px solid #0d6efd;
}

.mobile-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
    padding: 4px 0;
    font-size: 0.9rem;
}

.mobile-row .label {
    font-weight: 600;
    color: #6c757d;
    white-space: nowrap;
}

.mobile-row .value {
    color: #212529;
    text-align: right;
    word-break: break-word;
    max-width: 65%;
}

.badge {
    font-size: 0.75rem;
    padding: 4px 10px;
    border-radius: 12px;
    text-transform: capitalize;
}
/* 🔥 FIX GRID BREAK ON MOBILE */
@media (max-width: 768px) {

  .appointment-responsive-layout {
    margin: 0;
  }

  .iq-card {
    width: 100%;
    overflow-x: hidden;
  }

  .iq-list-card-body {
    max-width: 100%;
    overflow-x: auto;
  }

  .appointment-mobile-list {
    padding: 8px 4px;
  }

  .appointment-card {
    width: 100%;
  }
}
</style>
