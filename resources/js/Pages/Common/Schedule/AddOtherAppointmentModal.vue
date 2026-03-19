<script setup>
import { ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import BaseDatePicker from "@/Components/Common/Input/BaseDatePicker.vue";

const props = defineProps({
    doctor: {
        type: Object,
        required: true,
    },
    loading: Boolean,
    patient: {
        type: Array, // 👈 this is an array
        default: () => [],
    },
    selectedDate: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["close"]);

// 🔹 Inertia form
const form = useForm({
    patient_id: "",
    doctor_id: props.doctor?.id ?? "",
    start_date: props.selectedDate ?? "",
    start_time: "",
    end_time: "",
    reason: "",
    notes: "",
});

// 🔹 Sync selected date
watch(
    () => props.selectedDate,
    (newDate) => {
        if (newDate) {
            form.start_date = newDate;
        }
    },
    { immediate: true }
);

// 🔹 Submit
const submitForm = () => {
    if (!form.patient_id) {
        form.setError("patient_id", "Please select a patient.");
        return;
    }

    form
        .transform((data) => ({
            ...data,
            appointment_date: data.start_date,
            appointment_time: data.start_time,
        }))
        .post(route("appointments.store"), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
};

// 🔹 Close modal
const closeModal = () => {
    form.reset();
    form.clearErrors();
    emit("close");
};
</script>

<template>
    <form @submit.prevent="submitForm" novalidate>
        <!-- Patient -->
        <div class="form-group">
             <BaseSelect v-model="form.patient_id" placeholder="Select Patient" label="Patient">
                <option value="">Select Patient</option>
                <option v-for="row in patient" :key="row.id" :value="row.patient?.id">
                    {{ row.patient?.name || `${row.patient?.first_name} ${row.patient?.last_name}` }}
                </option>
            </BaseSelect>
            <span class="text-danger" v-if="form.errors.patient_id">
                {{ form.errors.patient_id }}
            </span>
        </div>

        <!-- Date -->
        <div class="form-group">
            <label class="form-label">Appointment Date</label>
            <BaseDatePicker type="date" v-model="form.start_date" required :errors="form.errors.start_date" />
        </div>

        <!-- Start Time -->
        <div class="form-group">
            <label class="form-label">Start Time</label>
            <BaseDatePicker type="time" v-model="form.start_time" required :errors="form.errors.start_time" />
        </div>

        <!-- End Time -->
        <div class="form-group">
            <label class="form-label">End Time</label>
            <BaseDatePicker type="time" v-model="form.end_time" required :errors="form.errors.end_time" />
        </div>

        <!-- Reason -->
        <div class="form-group">
            <label class="form-label">Reason</label>
            <BaseInput type="text" v-model="form.reason" placeholder="Reason for appointment"
                :errors="form.errors.reason" />
        </div>

        <!-- Notes -->
        <div class="form-group">
            <label class="form-label">Notes</label>
            <BaseInput type="textarea" v-model="form.notes" placeholder="Notes" :errors="form.errors.notes" />
        </div>

        <!-- Actions -->
        <div class="mt-4 d-flex justify-content-end gap-3">
            <button class="btn btn-primary" type="submit" :disabled="form.processing">
                {{ form.processing ? "Saving..." : "Save" }}
            </button>
            <button class="btn btn-danger" type="button" @click="closeModal">
                Cancel
            </button>
        </div>
    </form>
</template>