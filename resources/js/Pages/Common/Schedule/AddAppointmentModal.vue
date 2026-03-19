<script setup>
import { ref, watch, onMounted } from "vue";
import { useForm } from "@inertiajs/vue3";
import axios from "axios";
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
        type: Array,
        default: () => [],
    },
    selectedDate: String,
    selectedStartTime: String,
    selectedEndTime: String,
});

const emit = defineEmits(["close"]);

const fieldsTwo = [
    { key: "reason", type: "text", placeholder: "Reason" },
    { key: "notes", type: "textarea", placeholder: "Notes / Tasks" },
];

// 🔹 Inertia form
const form = useForm({
    patient_id: "",
    doctor_id: props.doctor?.id ?? "",
    start_date: props.selectedDate ?? "",
    start_time: props.selectedStartTime ?? "",
    end_time: props.selectedEndTime ?? "",
    visit_type_id: "",
    reason: "",
    notes: "",
    status: "pending",
    duration_minutes: 0,
    fee_amount: 0,
    currancy: "USD",
});

// 🔹 Visit types
const visitTypes = ref([]);

// 🔹 Fetch visit types
const getVisitTypes = async () => {
    if (!form.doctor_id) {
        visitTypes.value = [];
        return;
    }

    try {
        const { data } = await axios.get(
            route("doctor.appointments.visit.types", { doctorId: form.doctor_id })
        );
        visitTypes.value = data;
    } catch (error) {
        console.error("Failed to fetch visit types", error);
        visitTypes.value = [];
    }
};

// 🔹 Sync props → form
watch(() => props.selectedDate, v => v && (form.start_date = v), { immediate: true });
watch(() => props.selectedStartTime, v => v && (form.start_time = v), { immediate: true });
watch(() => props.selectedEndTime, v => v && (form.end_time = v), { immediate: true });

// 🔹 Doctor change → reload visit types
watch(() => form.doctor_id, () => {
    form.visit_type_id = "";
    form.duration_minutes = 0;
    form.fee_amount = 0;
    form.currancy = "USD";
    getVisitTypes();
});

// 🔹 Visit type change → auto-fill duration, amount, and currency
watch(() => form.visit_type_id, (visitTypeId) => {
    if (!visitTypeId) {
        form.duration_minutes = 0;
        form.fee_amount = 0;
        form.currancy = "USD";
        return;
    }

    const selectedVisitType = visitTypes.value.find(vt => vt.id === visitTypeId);
    if (selectedVisitType) {
        form.duration_minutes = selectedVisitType.duration || 0;
        form.fee_amount = selectedVisitType.price || 0;
        form.currancy = selectedVisitType.currency || "USD";
    }
});

// 🔹 Initial fetch
onMounted(() => {
    if (form.doctor_id) {
        getVisitTypes();
    }
});

// 🔹 Close modal
const closeModal = () => {
    form.reset();
    form.clearErrors();
    visitTypes.value = [];
    emit("close");
};

// 🔹 Submit
const submitForm = () => {
    if (!form.patient_id) {
        form.setError("patient_id", "Please select a patient.");
        return;
    }

    form
        .transform(data => ({
            ...data,
            appointment_date: data.start_date,
            appointment_time: data.start_time,
        }))
        .post(route("appointments.store"), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                window.location.reload(); // calendar refresh
            },
        });
};
</script>

<template>
    <form @submit.prevent="submitForm" novalidate>
        <!-- Patient -->
        <div class="form-group">
            <BaseSelect v-model="form.patient_id" placeholder="Select Patient" label="Patient">
                <option v-for="row in patient" :key="row.id" :value="row.patient?.id">
                    {{ row.patient?.name || `${row.patient?.first_name} ${row.patient?.last_name}` }}
                </option>
            </BaseSelect>
            <span class="text-danger" v-if="form.errors.patient_id">
                {{ form.errors.patient_id }}
            </span>
        </div>

        <!-- Date & Time -->
        <BaseDatePicker v-model="form.start_date" label="Start Date" required />
        <BaseDatePicker v-model="form.start_time" type="time" label="Start Time" required />
        <BaseDatePicker v-model="form.end_time" type="time" label="End Time" required />

        <!-- Visit Type -->
        <BaseSelect v-model="form.visit_type_id" label="Visit Type" placeholder="Select Visit Type" required>
            <option v-for="(row, key) in visitTypes" :key="key" :value="row.id">
                {{ row?.name }}{{ row?.duration ? ` (${row?.duration} min)` : "" }} {{ row.price }} {{ row.currency }}
            </option>
        </BaseSelect>

        <!-- Duration, Amount, Currency -->
        <div class="d-flex gap-3 d-none">
            <BaseInput v-model.number="form.duration_minutes" type="number" label="Duration (min)"
                placeholder="Duration" />
            <BaseInput v-model.number="form.fee_amount" type="number" label="Amount" placeholder="Amount" />
            <BaseSelect v-model="form.currancy" label="Currency" placeholder="Currency">
                <option value="USD">USD</option>
                <option value="INR">INR</option>
            </BaseSelect>
        </div>

        <!-- Reason / Notes -->
        <div v-for="field in fieldsTwo" :key="field.key">
            <BaseInput v-model="form[field.key]" :type="field.type" :label="field.placeholder"
                :placeholder="field.placeholder" :errors="form.errors[field.key]" />
        </div>



        <!-- Actions -->
        <div class="mt-4 d-flex justify-content-end gap-3">
            <template v-for="error in Object.values(form.errors)">
                <span class="text-danger font-l">{{ error }}</span>
            </template>
            <button class="btn btn-primary" type="submit" :disabled="form.processing">
                {{ form.processing ? "Saving..." : "Save" }}
            </button>
            <button class="btn btn-danger" type="button" @click="closeModal">
                Cancel
            </button>
        </div>
    </form>
</template>