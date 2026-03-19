<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    appointment: Object,
});

const goBack = () => {
    router.get(route('doctor.schedule.index'));
};
</script>

<template>
    <AuthLayout title="Appointment Details" description="View appointment details" heading="Appointment Details">
        <div class="card">
            <div class="card-body">
                <div v-if="appointment">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Patient Name:</strong> {{ appointment.patient?.name }}</p>
                            <p><strong>Doctor Name:</strong> {{ appointment.doctor?.user?.name }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Appointment Date:</strong> {{ appointment.appointment_date }}</p>
                            <p><strong>Appointment Time:</strong> {{ appointment.appointment_time }}</p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Status:</strong> 
                                <span :class="{
                                    'badge bg-success': appointment.status === 'completed',
                                    'badge bg-warning': appointment.status === 'pending',
                                    'badge bg-primary': appointment.status === 'confirmed',
                                    'badge bg-danger': appointment.status === 'rejected' || appointment.status === 'cancelled',
                                }">
                                    {{ appointment.status }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Reason:</strong> {{ appointment.reason }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Fee Amount:</strong> ${{ appointment.fee_amount }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Payment Status:</strong> {{ appointment.payment_status }}</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button class="btn btn-primary" @click="goBack">Back to Schedule</button>
                    </div>
                </div>
                <div v-else class="alert alert-warning">
                    Appointment not found.
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

