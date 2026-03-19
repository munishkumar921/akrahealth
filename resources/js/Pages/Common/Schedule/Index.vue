<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { ref, reactive, watch, computed } from "vue";
import Calender from "@fullcalendar/vue3";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import listPlugin from "@fullcalendar/list";
import interactionPlugin from "@fullcalendar/interaction";
import Modal from "../../../Components/Common/Modal.vue";
import AddAppointmentModal from "./AddAppointmentModal.vue";
import AddOtherAppointmentModal from "./AddOtherAppointmentModal.vue";
import { router, usePage } from "@inertiajs/vue3";
const props = defineProps({
    policy: String,
    patient: Object,
    doctor: Object,
    events: Object,
});

const page = usePage();
const inertiaProps = computed(() => page.props ?? {});
const userRole = computed(
    () => inertiaProps.value?.switched_role || inertiaProps.value?.auth?.user?.roles?.[0]?.name || ""
);

const showAppointmentModal = ref(false);
const showAppointmentDetailsModal = ref(false);
const selectedEvent = ref(null);
const loading = ref(false);
const rejectionReason = ref("");
const selectedPopup = ref("patient_appointment");
const RejectLoading = ref(false);
const ApproveLoading = ref(false);
const DeleteLoading = ref(false);
const CancelLoading = ref(false);
const EncounterLoading = ref(false);
const selectedDate = ref("");
const selectedStartTime = ref("");
const selectedEndTime = ref("");

const tooltipState = ref({
    visible: false,
    x: 0,
    y: 0,
    data: {}
});

// Helper function to get default times
const getDefaultTimes = () => {
    const now = new Date();
    const pad = (n) => String(n).padStart(2, "0");

    // Default start time: current time rounded to next 30 minutes
    const currentMinutes = now.getMinutes();
    const roundedMinutes = Math.ceil(currentMinutes / 30) * 30;
    const startTime = new Date(now);
    startTime.setMinutes(roundedMinutes);
    startTime.setSeconds(0);

    // Default end time: start time + 30 minutes
    const endTime = new Date(startTime);
    endTime.setMinutes(endTime.getMinutes() + 30);

    return {
        start: `${pad(startTime.getHours())}:${pad(startTime.getMinutes())}`,
        end: `${pad(endTime.getHours())}:${pad(endTime.getMinutes())}`
    };
};

const getStatusColor = (status) => {
    const colorMapping = {
        'pending': '#f0ad4e', // Orange
        'confirmed': '#5cb85c', // Green
        'cancelled': '#d9534f', // Red
        'completed': '#337ab7', // Blue
        'rejected': '#777777', // Gray
    };
    return colorMapping[status] || '#777';
};

const options = reactive({
    plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
    initialView: "dayGridMonth",
    displayEventTime: false,
    headerToolbar: {
        left: "prev,next today",
        center: "title",
        right: "dayGridMonth,dayGridWeek,listDay",
    },
    events: (props.events?.data || props.events).map(event => {
        const date = new Date(event.start);
        const options = {
            hour: 'numeric',
            hour12: true,
        };
        if (date.getMinutes() !== 0) {
            options.minute = '2-digit';
        }
        const time = date.toLocaleTimeString('en-US', options).replace(/\s/g, '');

        const tooltipLines = [];
        if (event.patient_name) {
            tooltipLines.push(`Patient: ${event.patient_name}`);
        }
        if (event.doctor_name) {
            tooltipLines.push(`Doctor: ${event.doctor_name}`);
        }

        const formattedTime = new Intl.DateTimeFormat('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true,
        }).format(date);

        if (event.appointment_date) {
            tooltipLines.push(`When: ${event.appointment_date} ${formattedTime}`);
        }

        if (event.status) {
            tooltipLines.push(`Status: ${event.status}`);
        }
        const tooltip = tooltipLines.join('\n');

        return {
            ...event,
            appointment_time: formattedTime,
            title: `Appointment at ${time}`,
            tooltip,
        };
    }),
    editable: true,
    selectable: true,
    navLinks: true,
    weekends: true,
    droppable: true,
    selectMirror: true,
    dayMaxEvents: true,
    height: "auto",
    contentHeight: "auto",

    dateClick(info) {
        
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    const selectedDate = new Date(info.dateStr)

    if (selectedDate < today) {
      toast('You cannot book an appointment on a past date.', 'error', 5000)
      return
    }
        // Set default times
        const defaultTimes = getDefaultTimes();
        selectedStartTime.value = defaultTimes.start;
        selectedEndTime.value = defaultTimes.end;

        showAppointmentModal.value = true
    },

    dayCellDidMount(info) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const cellDate = new Date(info.date);
        cellDate.setHours(0, 0, 0, 0);

        if (cellDate < today) {
            info.el.classList.add('past-date');
        }

        if (cellDate.getTime() === today.getTime()) {
            info.el.classList.add('today');
        }
    },

    eventClick(info) {
         selectedEvent.value = {
            id: info.event.id,
            title: info.event.title,
            start: info.event.start,
            ...info.event.extendedProps,
        };
        showAppointmentDetailsModal.value = true;
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
});

const isAppointmentToday = computed(() => {
    if (!selectedEvent.value?.start) return false;
    const appointmentDate = new Date(selectedEvent.value.start);
    const today = new Date();
    return appointmentDate.getFullYear() === today.getFullYear() &&
        appointmentDate.getMonth() === today.getMonth() &&
        appointmentDate.getDate() === today.getDate();
});

const selectedEventTime = computed(() => {
    if (!selectedEvent.value?.start) return '';
    return new Intl.DateTimeFormat('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        meridiem: 'short'
    }).format(new Date(selectedEvent.value.start));
});

const isDoctor = computed(() =>
    userRole.value === "Doctor" ||
    (userRole.value === "Admin" && inertiaProps.value?.switched_role === "Doctor")
);

const encounter = (appointment_id, patient_id) => {
    router.post(route('doctor.schedule.encounter.create', {
        appointment_id, patient_id
    }));

    router.get(route("doctor.select.patient", { id: patient_id }), {}, {
        onSuccess: (response) => {
            router.reload({ only: ["auth", "flash"] });
        },
        onError: (errors) => {
            console.error("Patient selection failed:", errors);
        }
    });
};

const deleteAppointment = () => {
    DeleteLoading.value = true;
    router.delete(route('appointments.destroy', { appointment: selectedEvent.value.id }), {
        onSuccess: () => {
            DeleteLoading.value = false;
            closeAppointmentDetailsModal();
        },
        onError: () => {
            DeleteLoading.value = false;
        },
    });
}

const updateAppointmentStatus = (status) => {
    const data = { status };
    if (status === 'rejected') {
        data.reject_reason = rejectionReason.value;
        RejectLoading.value = true;
    }
    if (status === 'confirmed') {
        ApproveLoading.value = true;
    }
    if (status === 'cancelled') {
        CancelLoading.value = true;
    }
    router.post(route("appointments.updateStatus", { id: selectedEvent.value.id }),
        data,
        {
            onSuccess: () => {
                RejectLoading.value = false;
                ApproveLoading.value = false;
                CancelLoading.value = false;
                closeAppointmentDetailsModal();
            },
        }
    );
};

const closeAppointmentModal = () => {
    loading.value = false;
    showAppointmentModal.value = false;
    selectedDate.value = "";
    selectedStartTime.value = "";
    selectedEndTime.value = "";
};

const closeAppointmentDetailsModal = () => {
    showAppointmentDetailsModal.value = false;
    selectedEvent.value = null;
    rejectionReason.value = "";
    loading.value = false;
};
</script>

<template>
    <AuthLayout title="Schedule" description="View and manage your calendar" heading="Schedule">
        <div class="calendar-container">
             <Calender v-bind:options="options" />

            <!-- Custom Tooltip -->
            <div v-if="tooltipState.visible" class="calendar-tooltip" :style="{ top: tooltipState.y + 'px', left: tooltipState.x + 'px' }">
                <div v-if="tooltipState.data.patient_name"><strong>Patient:</strong> {{ tooltipState.data.patient_name }}</div>
                <div v-if="tooltipState.data.doctor_name"><strong>Doctor:</strong> {{ tooltipState.data.doctor_name }}</div>
                <div v-if="tooltipState.data.appointment_date && tooltipState.data.appointment_time">
                    <strong>When:</strong> {{ tooltipState.data.appointment_date }} {{ tooltipState.data.appointment_time }}
                </div>
                <div v-if="tooltipState.data.status">
                    <strong>Status:</strong> 
                    <span class="badge ms-1" :style="{ backgroundColor: getStatusColor(tooltipState.data.status) }">
                        {{ tooltipState.data.status }}
                    </span>
                </div>
                <div v-if="!tooltipState.data.patient_name && !tooltipState.data.doctor_name">
                     {{ tooltipState.data.title }}
                </div>
            </div>

            <Modal :isOpen="showAppointmentModal" v-if="isDoctor" title="Add Appointment" @close="closeAppointmentModal"
                size="lg">
                <nav class="tab-nav mb-5">
                    <div :class="{
                        'active-tab':
                            selectedPopup === 'patient_appointment',
                    }" @click="selectedPopup = 'patient_appointment'" style="cursor: pointer">
                        Patient Appointment
                    </div>
                    <div :class="{ 'active-tab': selectedPopup === 'other' }" @click="selectedPopup = 'other'"
                        style="cursor: pointer">
                        Other Event
                    </div>
                    <div class="tab-underline" :style="{
                        transform:
                            selectedPopup === 'patient_appointment'
                                ? 'translateX(0%)'
                                : 'translateX(100%)',
                    }">
                        <div class="justify-content-center text-white d-flex align-items-center bg-primary rounded">
                            {{
                                selectedPopup === "patient_appointment"
                                    ? "Patient Appointment"
                                    : "Other Event"
                            }}
                        </div>
                    </div>
                </nav>

                <transition name="fade" mode="out-in">
                    <AddAppointmentModal v-if="selectedPopup === 'patient_appointment'" key="patient_appointment"
                        @close="closeAppointmentModal" :doctor="doctor" :loading="loading" :patient="patient"
                        :selected-date="selectedDate" :selected-start-time="selectedStartTime"
                        :selected-end-time="selectedEndTime" />
                    <AddOtherAppointmentModal v-else key="other_event" @close="closeAppointmentModal" :doctor="doctor"
                        :loading="loading" :patient="patient" :selected-date="selectedDate" />
                </transition>
            </Modal>

            <Modal :isOpen="showAppointmentDetailsModal" title="Appointment Details"
                @close="closeAppointmentDetailsModal" size="lg">
                <div v-if="selectedEvent" class="p-2">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase fw-bold mb-1">Title</label>
                            <div class="fs-6 text-dark">{{ selectedEvent.title }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase fw-bold mb-1">Patient</label>
                            <div class="fs-6 text-dark">{{ selectedEvent.patient_name }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase fw-bold mb-1">Provider</label>
                            <div class="fs-6 text-dark">{{ selectedEvent.doctor_name }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase fw-bold mb-1">Date</label>
                            <div class="fs-6 text-dark">
                                {{ selectedEvent.appointment_date }}

                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase fw-bold mb-1">Status</label>
                            <div class="d-flex gap-2">
                                <span class="badge rounded-pill" :class="{
                                    'bg-warning text-dark': selectedEvent.status === 'pending',
                                    'bg-success': selectedEvent.status === 'confirmed',
                                    'bg-danger': ['cancelled', 'rejected'].includes(selectedEvent.status)
                                }">{{ selectedEvent.status }}</span>

                                <span class="badge rounded-pill" :class="{
                                    'bg-success': selectedEvent.payment_status === 'paid',
                                    'bg-warning text-dark': selectedEvent.payment_status !== 'paid'
                                }">Payment: {{ selectedEvent.payment_status || 'Pending' }}</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small text-uppercase fw-bold mb-1">Reason</label>
                            <div class="bg-light p-3 rounded border">{{ selectedEvent.reason || 'No reason provided' }}
                            </div>
                        </div>
                    </div>

                    <div v-if="selectedEvent.status === 'rejected' && selectedEvent.reject_reason"
                        class="alert alert-danger">
                        <strong>Rejection Reason:</strong> {{ selectedEvent.reject_reason }}
                    </div>

                    <div v-if="selectedEvent.status === 'pending'"
                        class="mt-4 d-flex flex-wrap gap-2 justify-content-end border-top pt-3">
                        <button class="btn btn-danger" :disabled="loading" @click="updateAppointmentStatus('rejected')">
                            <span v-if="RejectLoading">Reject...</span>
                            <span v-else>Reject</span>
                        </button>
                        <button class="btn btn-success" :disabled="loading"
                            @click="updateAppointmentStatus('confirmed')">
                            <span v-if="ApproveLoading">Approve...</span>
                            <span v-else>Approve</span>
                        </button>
                    </div>

                    <div v-if="selectedEvent.status === 'confirmed'"
                        class="mt-4 d-flex flex-wrap gap-2 justify-content-end border-top pt-3">
                        <button v-if="isAppointmentToday" class="btn btn-success" :disabled="loading"
                            @click="encounter(selectedEvent?.id, selectedEvent?.patient_id)">
                            <span v-if="EncounterLoading">Encounter...</span>
                            <span v-else>Encounter</span>
                        </button>
                        <button class="btn btn-danger" :disabled="loading"
                            @click="updateAppointmentStatus('cancelled')">
                            <span v-if="CancelLoading">Cancel...</span>
                            <span v-else>Cancel</span>
                        </button>
                        <button class="btn btn-danger" :disabled="loading" @click="deleteAppointment('delete')">
                            <span v-if="DeleteLoading">Delete...</span>
                            <span v-else>Delete</span>
                        </button>
                    </div>
                </div>
            </Modal>
        </div>
    </AuthLayout>
</template>

<style scoped>
/* Calendar Container */
.calendar-container {
    width: 100%;
    padding: 1rem;
    max-width: 100%;
    overflow-x: hidden;
}

/* Tab Navigation */
.tab-nav {
    display: flex;
    position: relative;
    align-items: center;
    justify-content: space-around;
    margin-bottom: 1rem;
}

.tab-nav div {
    padding: 0.5rem 1rem;
    font-weight: 500;
    cursor: pointer;
    color: #333;
}

.active-tab {
    color: #007bff;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.tab-underline {
    position: absolute;
    bottom: -10px;
    left: 0;
    height: 60px !important;
    overflow: hidden;
    width: 50%;
    transition: transform 0.3s ease;
}

/* Calendar Styling */
:deep(.fc) {
    max-width: 100%;
}

:deep(.fc .fc-toolbar) {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
}

:deep(.fc .fc-toolbar-title) {
    font-size: 1.25rem;
    margin: 0;
}

:deep(.fc .fc-button) {
    padding: 0.4rem 0.8rem;
    font-size: 0.875rem;
}

:deep(.fc .fc-toolbar-chunk) {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

/* Fix for prev/next button alignment */
:deep(.fc-direction-ltr .fc-toolbar > * > :not(:first-child)) {
    margin-left: 0.25rem;
}

:deep(.fc .fc-button-group) {
    display: flex;
    gap: 0.25rem;
}

/* Past dates */
/* .fc-day-past,
:deep(.past-date) {
    background-color: #0aadff !important;
} */

.fc-day-past .fc-daygrid-day-number,
:deep(.past-date .fc-daygrid-day-number) {
    color: #6c757d !important;
}

.fc-day-past .fc-event,
:deep(.past-date .fc-event) {
    opacity: 0.5;
}

/* Today's date */
.fc-day-today,
:deep(.today) {
    background-color: #e7f3ff !important;
}

/* Future dates */
:deep(.fc-day-future) {
    background-color: white !important;
}

/* Mobile Responsive Styles */
@media (max-width: 768px) {
    .calendar-container {
        padding: 0.5rem;
    }

    :deep(.fc .fc-toolbar) {
        flex-direction: column;
        gap: 0.75rem;
    }

    :deep(.fc .fc-toolbar-chunk) {
        width: 100%;
        justify-content: center;
    }

    :deep(.fc .fc-toolbar-title) {
        font-size: 1rem;
        text-align: center;
    }

    :deep(.fc .fc-button) {
        padding: 0.35rem 0.6rem;
        font-size: 0.75rem;
    }

    :deep(.fc-header-toolbar) {
        margin-bottom: 1rem !important;
    }

    /* Stack buttons vertically on very small screens */
    :deep(.fc-toolbar-chunk:last-child .fc-button-group) {
        flex-wrap: wrap;
        justify-content: center;
    }

    /* Make day cells more touch-friendly */
    :deep(.fc-daygrid-day) {
        min-height: 3rem;
    }

    :deep(.fc-daygrid-day-number) {
        font-size: 0.875rem;
        padding: 0.25rem;
    }

    /* Make event text smaller on mobile */
    :deep(.fc-event-title) {
        font-size: 0.75rem;
    }

    /* Tab navigation mobile */
    .tab-nav div {
        font-size: 0.875rem;
    }

    /* Button spacing in modals */
    .d-flex.gap-2 {
        gap: 0.5rem !important;
    }

    .btn {
        font-size: 0.875rem;
        padding: 0.5rem 1rem;
    }
}

@media (max-width: 480px) {
    :deep(.fc .fc-toolbar-title) {
        font-size: 0.9rem;
    }

    :deep(.fc .fc-button) {
        padding: 0.3rem 0.5rem;
        font-size: 0.7rem;
    }

    :deep(.fc-daygrid-day-number) {
        font-size: 0.75rem;
    }

    .tab-nav div {
        font-size: 0.8rem;
    }

    /* Full width buttons on very small screens */
    .d-flex.flex-wrap.gap-2 {
        flex-direction: column;
    }

    .d-flex.flex-wrap.gap-2 .btn {
        width: 100%;
    }
}

/* Landscape mobile optimization */
@media (max-width: 768px) and (orientation: landscape) {
    :deep(.fc) {
        height: 70vh;
    }
}

.calendar-tooltip {
    position: fixed;
    z-index: 10000;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
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