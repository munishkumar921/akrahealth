<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router } from "@inertiajs/vue3";
import { ref } from "vue";

/* --------- form state --------- */
const form = ref({
    include_weekends: "Yes",
    first_hour: "09:00",
    last_hour: "21:00",
    timezone: "Asia/Kolkata",

    monday_open: "09:00",
    monday_close: "21:00",

    tuesday_open: "09:00",
    tuesday_close: "21:00",

    wednesday_open: "09:00",
    wednesday_close: "21:00",

    thursday_open: "09:00",
    thursday_close: "21:00",

    friday_open: "09:00",
    friday_close: "21:00",

    saturday_open: "09:00",
    saturday_close: "21:00",

    sunday_open: "09:00",
    sunday_close: "21:00",
});

/* --------- dropdown options --------- */
const weekendOptions = [
    { label: "Yes", value: "Yes" },
    { label: "No", value: "No" },
];

/* --------- validation --------- */
const errors = ref({});

const validateForm = () => {
    errors.value = {};

    if (!form.value.first_hour) errors.value.first_hour = "First hour is required";
    if (!form.value.last_hour) errors.value.last_hour = "Last hour is required";
    if (!form.value.timezone.trim()) errors.value.timezone = "Timezone is required";

    return Object.keys(errors.value).length === 0;
};

/* --------- submit --------- */
const submit = () => {
    if (!validateForm()) return;

    console.log("Schedule Setup form:", { ...form.value });
    toast("Schedule saved successfully!");

    // Navigate back to schedule list
    router.visit(route("admin.schedule.list"));
};
</script>

<template>
    <AuthLayout title="Schedule Setup" description="Schedule Setup" heading="Schedule Setup">
        <div class="container-fluid">
            <form @submit.prevent="submit">
                <!-- Include weekends -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Include Weekends in the Schedule</label>
                        <select v-model="form.include_weekends" class="form-select">
                            <option v-for="w in weekendOptions" :key="w.value" :value="w.value">{{ w.label }}</option>
                        </select>
                    </div>
                </div>

                <!-- First and last hour -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">First hour/time that will be displayed on the schedule</label>
                        <input v-model="form.first_hour" type="time" class="form-control" :class="{ 'is-invalid': errors.first_hour }" />
                        <div v-if="errors.first_hour" class="invalid-feedback">{{ errors.first_hour }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Last hour/time that will be displayed on the schedule</label>
                        <input v-model="form.last_hour" type="time" class="form-control" :class="{ 'is-invalid': errors.last_hour }" />
                        <div v-if="errors.last_hour" class="invalid-feedback">{{ errors.last_hour }}</div>
                    </div>
                </div>

                <!-- Timezone -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Timezone</label>
                        <input v-model="form.timezone" type="text" class="form-control" :class="{ 'is-invalid': errors.timezone }" />
                        <div v-if="errors.timezone" class="invalid-feedback">{{ errors.timezone }}</div>
                    </div>
                </div>

                <!-- Weekly schedule -->
                <div class="row">
                    <div class="col-12">
                        <h6 class="fw-semibold mb-3">Weekly Schedule</h6>
                    </div>

                    <template v-for="day in ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday']" :key="day">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ day }} open at</label>
                            <input v-model="form[day.toLowerCase() + '_open']" type="time" class="form-control" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ day }} close at</label>
                            <input v-model="form[day.toLowerCase() + '_close']" type="time" class="form-control" />
                        </div>
                    </template>
                </div>

                <!-- Buttons -->
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-danger" @click="router.visit(route('admin.allAppointments'))">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </AuthLayout>
</template>
