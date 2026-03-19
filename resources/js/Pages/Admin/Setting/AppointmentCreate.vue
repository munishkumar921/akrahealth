<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router } from "@inertiajs/vue3";
import DatePicker from "@/Components/Common/Input/BaseDatePicker.vue";
import { ref } from "vue";

/* --------- form state --------- */
const form = ref({
    patient: "",
    doctor: "",
    appointment_type: "",
    appointment_mode: "",
    scheduled_date: "",
    scheduled_time: "",
    recurring_type: "",
    fee_amount: "",
    discount: "",
    total_amount: 0,
    payment_method: "",
    payment_status: "",
    appointment_status: "",
    appointment_reason: "",
    note: "",
});

/* --------- dropdown options --------- */
const appointmentTypes = [
    { label: "Consultation", value: "consultation" },
    { label: "Follow-up", value: "followup" },
    { label: "Emergency", value: "emergency" },
];

const appointmentModes = [
    { label: "In-person", value: "inperson" },
    { label: "Online", value: "online" },
];

const recurringTypes = [
    { label: "None", value: "none" },
    { label: "Daily", value: "daily" },
    { label: "Weekly", value: "weekly" },
    { label: "Monthly", value: "monthly" },
];

const paymentMethods = [
    { label: "Cash", value: "cash" },
    { label: "Card", value: "card" },
    { label: "UPI", value: "upi" },
];

const paymentStatuses = [
    { label: "Pending", value: "pending" },
    { label: "Paid", value: "paid" },
    { label: "Failed", value: "failed" },
];

const appointmentStatuses = [
    { label: "Scheduled", value: "scheduled" },
    { label: "Completed", value: "completed" },
    { label: "Cancelled", value: "cancelled" },
];

/* --------- validation --------- */
const errors = ref({});

const validateForm = () => {
    errors.value = {};

    if (!form.value.patient) errors.value.patient = "Patient is required";
    if (!form.value.doctor) errors.value.doctor = "Doctor is required";
    if (!form.value.appointment_type) errors.value.appointment_type = "Appointment type is required";
    if (!form.value.appointment_mode) errors.value.appointment_mode = "Appointment mode is required";
    if (!form.value.scheduled_date) errors.value.scheduled_date = "Scheduled date is required";
    if (!form.value.scheduled_time) errors.value.scheduled_time = "Scheduled time is required";

    return Object.keys(errors.value).length === 0;
};

/* --------- submit --------- */
const submit = () => {
    if (!validateForm()) return;

    console.log("Appointment form:", { ...form.value });
    toast("Appointment saved successfully!");

    // Navigate back to appointment list
    router.visit(route('admin.appointments.list'));
};
</script>

<template>
    <AuthLayout title="Update Appointment" description="Update Appointment" heading="Update Appointment">
        <div class="container-fluid">
            <form @submit.prevent="submit">
                <div class="row">
                    <!-- Patient -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Select patient <span class="text-danger">*</span></label>
                        <input v-model="form.patient" type="text" class="form-control" :class="{ 'is-invalid': errors.patient }" placeholder="Search patient" />
                        <div v-if="errors.patient" class="invalid-feedback">{{ errors.patient }}</div>
                    </div>

                    <!-- Doctor -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Select doctor <span class="text-danger">*</span></label>
                        <input v-model="form.doctor" type="text" class="form-control" :class="{ 'is-invalid': errors.doctor }" placeholder="Search doctor" />
                        <div v-if="errors.doctor" class="invalid-feedback">{{ errors.doctor }}</div>
                    </div>
                </div>

                <div class="row">
                    <!-- Appointment type -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Appointment type <span class="text-danger">*</span></label>
                        <select v-model="form.appointment_type" class="form-select" :class="{ 'is-invalid': errors.appointment_type }">
                            <option value="" disabled>Select type</option>
                            <option v-for="t in appointmentTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                        </select>
                        <div v-if="errors.appointment_type" class="invalid-feedback">{{ errors.appointment_type }}</div>
                    </div>

                    <!-- Appointment mode -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Appointment mode <span class="text-danger">*</span></label>
                        <select v-model="form.appointment_mode" class="form-select" :class="{ 'is-invalid': errors.appointment_mode }">
                            <option value="" disabled>Select mode</option>
                            <option v-for="m in appointmentModes" :key="m.value" :value="m.value">{{ m.label }}</option>
                        </select>
                        <div v-if="errors.appointment_mode" class="invalid-feedback">{{ errors.appointment_mode }}</div>
                    </div>
                </div>

                <div class="row">
                    <!-- Scheduled date -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Scheduled date <span class="text-danger">*</span></label>
                        <DatePicker v-model="form.scheduled_date" :class="{ 'is-invalid': errors.scheduled_date }" />
                        <div v-if="errors.scheduled_date" class="invalid-feedback">{{ errors.scheduled_date }}</div>
                    </div>

                    <!-- Scheduled time -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Scheduled time <span class="text-danger">*</span></label>
                        <input v-model="form.scheduled_time" type="time" class="form-control" :class="{ 'is-invalid': errors.scheduled_time }" />
                        <div v-if="errors.scheduled_time" class="invalid-feedback">{{ errors.scheduled_time }}</div>
                    </div>
                </div>

                <div class="row">
                    <!-- Recurring type -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Recurring type</label>
                        <select v-model="form.recurring_type" class="form-select">
                            <option value="" disabled>Select recurring type</option>
                            <option v-for="r in recurringTypes" :key="r.value" :value="r.value">{{ r.label }}</option>
                        </select>
                    </div>

                    <!-- Fee amount -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Fee amount</label>
                        <input v-model="form.fee_amount" type="number" class="form-control" />
                    </div>
                </div>

                <div class="row">
                    <!-- Discount -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Discount</label>
                        <input v-model="form.discount" type="number" class="form-control" />
                    </div>

                    <!-- Total amount -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Total amount</label>
                        <input v-model="form.total_amount" type="number" class="form-control" readonly />
                    </div>
                </div>

                <div class="row">
                    <!-- Payment method -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Payment method</label>
                        <select v-model="form.payment_method" class="form-select">
                            <option value="" disabled>Select method</option>
                            <option v-for="pm in paymentMethods" :key="pm.value" :value="pm.value">{{ pm.label }}</option>
                        </select>
                    </div>

                    <!-- Payment status -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Payment status</label>
                        <select v-model="form.payment_status" class="form-select">
                            <option value="" disabled>Select status</option>
                            <option v-for="ps in paymentStatuses" :key="ps.value" :value="ps.value">{{ ps.label }}</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <!-- Appointment status -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Appointment status</label>
                        <select v-model="form.appointment_status" class="form-select">
                            <option value="" disabled>Select status</option>
                            <option v-for="as in appointmentStatuses" :key="as.value" :value="as.value">{{ as.label }}</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <!-- Appointment reason -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Appointment reason</label>
                        <textarea v-model="form.appointment_reason" class="form-control" rows="3"></textarea>
                    </div>

                    <!-- Note -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Note</label>
                        <textarea v-model="form.note" class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-danger" @click="router.visit(route('admin.allAppointments'))">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </AuthLayout>
</template>
