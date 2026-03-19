<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { useForm } from "@inertiajs/vue3";
import ScheduleSetupList from "./ScheduleSetupList.vue";

const scheduleForm = useForm({
    monday_open_at: "",
    monday_close_at: "",
    tuesday_open_at: "",
    tuesday_close_at: "",
    wednesday_open_at: "",
    wednesday_close_at: "",
    thursday_open_at: "",
    thursday_close_at: "",
    friday_open_at: "",
    friday_close_at: "",
    saturday_open_at: "",
    saturday_close_at: "",
    sunday_open_at: "",
    sunday_close_at: "",
});

const addProviderException = () => {};

function goBack() {
    window.history.back();
}
</script>
<template>
    <AuthLayout title="Schedule Setup" description="Schedule Setup" heading="Schedule Setup">
        <ScheduleSetupList name="Schedule Setup" :form="scheduleForm" />
            <div class="form-button mt-4 px-3 d-flex justify-content-end gap-3">
                <button
                    type="button"
                    class="btn btn-primary"
                    @click="addProviderException"
                >
                    Save
                </button>
                <button
                    type="button"
                    class="btn btn-danger"
                    @click="goBack"
                >
                    Cancel
                </button>
            </div>
    </AuthLayout>
</template>
