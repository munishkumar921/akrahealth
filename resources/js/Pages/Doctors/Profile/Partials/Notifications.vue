<script setup>
import BaseSwitch from '@/Components/Common/Input/BaseSwitch.vue';
import BaseInput from '@/Components/Common/Input/BaseInput.vue';
import { useForm } from '@inertiajs/vue3';

const settingsForm = useForm({
    patient_updates: false,
    ai_recommendations: false,
    appointment_reminders: false,
    critical_alerts: false,
    notification_type: "push",
    push_notification: false,
    email_notification: false,
    sms_notification: false,
    quiet_hour_start: "",
    quiet_hour_end: ""
})

const updateNotificationSettings = () => {
    console.info(settingsForm)
}
</script>

<template>
    <div class="iq-card">
        <div class="iq-card-header d-flex justify-content-between">
            <div class="iq-header-title">
                <h4 class="card-title">Notifications</h4>
            </div>
        </div>
        <div class="iq-card-body">
            <form @submit.prevent="updateNotificationSettings" novalidate>
                <BaseSwitch v-model="settingsForm.patient_updates" label="Patient Updates" />
                <BaseSwitch v-model="settingsForm.ai_recommendations" label="AI Recommendations" />
                <BaseSwitch v-model="settingsForm.appointment_reminders" label="Appointment Reminders" />
                <BaseSwitch v-model="settingsForm.critical_alerts" label="Critical Alerts" />

                <h6 class="pt-2"><strong>
                        Notification Channels
                    </strong></h6>
                <div class="container">
                    <BaseSwitch v-model="settingsForm.push_notification" label="Push" />
                    <BaseSwitch v-model="settingsForm.email_notification" label="Email" />
                    <BaseSwitch v-model="settingsForm.sms_notification" label="SMS" />
                </div>

                <h6 class="pt-2"><strong>
                        Quiet Hours
                    </strong></h6>
                <div class="container">
                    <div class="row">
                        <div class="col-5">
                            <BaseInput v-model="settingsForm.quiet_hour_start" type="time" name="Starts" placeholder=""
                                label="Starts" />
                        </div>
                        <div class="col-2 pt-2">to</div>
                        <div class="col-5">
                            <BaseInput v-model="settingsForm.quiet_hour_end" type="time" name="Ends" placeholder=""
                                label="Ends" />
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Update</button>
            </form>
        </div>
    </div>
</template>
