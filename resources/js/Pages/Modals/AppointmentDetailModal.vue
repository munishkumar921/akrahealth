<script setup>
import { computed } from 'vue';

const props = defineProps({
    selectedAppointment: {
        type: Object,
        default: null,
    },
    policy: {
        type: String,
        default: '', // e.g., 'doctor', 'patient'
    },
});

const emit = defineEmits([
    'close',
    'confirmed',
    'reject',
    'reschedule',
    'edit',
    'cancel',
]);

// Helper functions (copied from Dashboard.vue for consistency)

const badgeClasses = {
    status: {
        'scheduled': 'bg-primary',
        'completed': 'bg-success',
        'cancelled': 'bg-danger',
        'no-show': 'bg-warning',
        'confirmed': 'bg-info',
        'pending': 'bg-warning', // Added pending status
        'approved': 'bg-success', // Added approved status
        'rejected': 'bg-danger', // Added rejected status
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

const getBadgeClass = (category, value) => {
    return badgeClasses[category]?.[value?.toLowerCase()] || 'bg-secondary';
};
</script>

<template>
    <div class="p-4" v-if="selectedAppointment">
                <div class="tooltip-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-calendar-event me-2"></i>
                            <h5 class="modal-title">Appointment Details</h5>
                            <span :class="`badge ms-3 ${getBadgeClass('status', selectedAppointment?.status)}`">
                                {{ selectedAppointment.status }}
                            </span>
                            
                        </div>
                         <!-- <button type="button"  v-if="selectedAppointment && $page.props.auth.user.id === selectedAppointment.created_by" class="btn btn-warning" @click="emit('edit', selectedAppointment.id)">
                        <i class="bi bi-pencil"></i> Edit
                    </button> -->
                     </div>
                </div>

                <div class="p-3 appointment-details">
                    <!-- Patient Information Section -->
                    <div class="detail-section mt-4 mb-4">
                        
                        <h6 class="text-primary mb-3">
                            <i class="bi bi-person me-1"></i>Patient Information
                        </h6>
                       
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Patient Name:</strong> {{ selectedAppointment?.patient?.name||selectedAppointment.patient?.user?.name }}</p>
                                <p><strong>Patient ID:</strong> {{ selectedAppointment?.patient?.id }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Contact:</strong> {{ selectedAppointment?.patient?.user.phone || 'N/A' }}</p>
                                <p><strong>Email:</strong> {{ selectedAppointment?.patient?.user?.email || 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Appointment Details Section -->
                    <div class="detail-section mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="bi bi-clock me-1"></i>Appointment Details
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Date:</strong> {{ selectedAppointment.appointment_date }}</p>
                                <p><strong>Time:</strong> {{ selectedAppointment.appointment_time }}</p>
                                <p><strong>Duration:</strong> {{ selectedAppointment.duration || '30 mins' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Type:</strong>
                                    <span :class="`badge ${getBadgeClass('type', selectedAppointment.appointment_type)}`">
                                        {{ selectedAppointment.appointment_type }}
                                    </span>
                                </p>
                                <p><strong>Mode:</strong>
                                    <span :class="`badge ${getBadgeClass('mode', selectedAppointment.appointment_mode)}`">
                                        {{ selectedAppointment.appointment_mode }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="detail-section mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="bi bi-info-circle me-1"></i>Additional Information
                        </h6>
                        <p><strong>Reason for Visit:</strong></p>
                        <div class="reason-box p-3 bg-light rounded">
                            {{ selectedAppointment.reason || 'No reason provided' }}
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p><strong>Created By:</strong> {{ selectedAppointment.created_by_name }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Created On:</strong> {{ selectedAppointment.created_at }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Notes Section (if available) -->
                    <div class="detail-section" v-if="selectedAppointment.notes">
                        <h6 class="text-primary mb-3">
                            <i class="bi bi-sticky me-1"></i>Additional Notes
                        </h6>
                        <div class="notes-box p-3 bg-light rounded">
                            {{ selectedAppointment.notes }}
                        </div>
                    </div>

                    <div v-if="policy === 'doctor' && selectedAppointment?.status === 'pending'" class="d-flex justify-content-center gap-2 border-top pt-3">
                        <button type="button" class="btn btn-success" @click="emit('approve', selectedAppointment.id)">Approve</button>

                        <button type="button" class="btn btn-danger" @click="emit('reject', selectedAppointment.id)">Reject</button>
                    </div>
                </div>

                <div class="p-3 d-flex justify-content-center gap-2 border-top">
                    <!-- <button type="button" v-if="selectedAppointment && $page.props.auth.user.id === selectedAppointment.created_by" class="btn btn-info" @click="emit('reschedule', selectedAppointment.id)">
                        <i class="bi bi-calendar-plus"></i> Reschedule
                    </button> -->
                    <button
                        type="button"
                        v-if="['pending', 'confirmed'].includes(selectedAppointment?.status)"
                        class="btn btn-danger"
                        @click="emit('cancel', selectedAppointment.id)"
                        >
                        <i class="bi bi-x-circle"></i> Cancel Appointment
                        </button>

                     
                </div>
            </div>
</template>