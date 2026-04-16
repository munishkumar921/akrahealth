<script setup>
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import BaseDatePicker from "@/Components/Common/Input/BaseDatePicker.vue";
import InputError from "@/Components/InputError.vue";
import { useForm } from "@inertiajs/vue3";
import timezones from "@/Components/Common/timezone";

const props = defineProps({
    doctors: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(["close", "submit"]);

const form = useForm({
    id: '',
    weekends: '',
    time_zone: '',
    default_open_time: '',
    default_close_time: '',
    open_time: '',
    close_time: '',
    day_of_week: '',
});
const days_of_week = [
    { 'value': 'Monday', 'label': 'Monday' },
    { 'value': 'Tuesday', 'label': 'Tuesday' },
    { 'value': 'Wednesday', 'label': 'Wednesday' },
    { 'value': 'Thursday', 'label': 'Thursday' },
    { 'value': 'Friday', 'label': 'Friday' },
    { 'value': 'Saturday', 'label': 'Saturday' },
    { 'value': 'Sunday', 'label': 'Sunday' }
];

const closeModal = () => {
    emit("close");
};

const normalizeTimeForBackend = (value) => {
    if (!value || typeof value !== "string") {
        return value;
    }

    const trimmed = value.trim();
    const twelveHourMatch = trimmed.match(/^(\d{1,2}):([0-5]\d)\s*([AaPp][Mm])$/);
    if (twelveHourMatch) {
        let hours = Number(twelveHourMatch[1]) % 12;
        const minutes = twelveHourMatch[2];
        const meridiem = twelveHourMatch[3].toUpperCase();

        if (meridiem === "PM") {
            hours += 12;
        }

        return `${String(hours).padStart(2, "0")}:${minutes}`;
    }

    return trimmed;
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        open_time: normalizeTimeForBackend(data.open_time),
        close_time: normalizeTimeForBackend(data.close_time),
        default_open_time: normalizeTimeForBackend(data.default_open_time),
        default_close_time: normalizeTimeForBackend(data.default_close_time),
    }));

    form.post(route('admin.hospital-timing.store'), {
        onSuccess: () => {
            form.transform((data) => data);
            closeModal();
        },
        onError: () => {
            form.transform((data) => data);
        },
    });
};
const update = (data) => {
    console.log(data);
    Object.keys(form).forEach(key => {
        if (data[key] !== undefined) {
            form[key] = data[key];
        }
    });
};

defineExpose({
    update,
    resetForm: () => form.reset(),
});
</script>

<template>
    <form @submit.prevent="submit">
        <div class="row">
            <div class="col-12">
                <div class="mb-3">
                    <BaseSelect v-model="form.weekends" placeholder="Include Weekends in the Schedule"
                        label="Include Weekends in the Schedule" :error="form.errors.weekends">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </BaseSelect>
                </div>
            </div>

            <div class="col-12">
                <div class="mb-3">
                    <BaseSelect v-model="form.time_zone" placeholder="Select Timezone" label="Timezone"
                        :error="form.errors.time_zone">
                        <option v-for="tz in timezones" :key="tz.value" :value="tz.value">
                            {{ tz.label }}
                        </option>
                    </BaseSelect>
                </div>
            </div>

            <div class="col-12">

                <div class="mb-3">
                    <BaseSelect v-model="form.day_of_week" label="Day" placeholder="Select Day"
                        :error="form.errors.day_of_week">
                        <option v-for="day in days_of_week" :key="day.value" :value="day.value">
                            {{ day.label }}
                        </option>
                    </BaseSelect>
                </div>
            </div>
            <div class="col-6 ">
                <div class="mb-3">
                    <BaseDatePicker v-model="form.open_time" label="Open Time" type="time" placeholder="Open time" />
                    <InputError :error="form.errors.open_time" />
                </div>
            </div>
            <div class="col-6">
                <div class="mb-3">
                    <BaseDatePicker v-model="form.close_time" label="Close Time" type="time" placeholder="Close time" />
                    <InputError :error="form.errors.close_time" />
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-end gap-2">

            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                Save
            </button>
            <button type="button" class="btn btn-danger" @click="closeModal">
                Close
            </button>
        </div>
    </form>
</template>
