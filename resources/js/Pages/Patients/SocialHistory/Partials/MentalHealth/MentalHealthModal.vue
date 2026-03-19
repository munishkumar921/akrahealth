<script setup>
import { ref } from "vue";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    socialHistory: Object,
 });

const emit = defineEmits(['close', 'submit']);

const isValidated = ref(false);

const parseMentalHealthNotes = (notes) => {
    if (!notes || !notes.trim()) {
        return {
            psychological_history: "",
            devolepmental_history: "",
            past_medication_trials: ""
        };
    }

    const parts = notes.split(' | ');
    return {
        psychological_history: parts[0] || "",
        devolepmental_history: parts[1] || "",
        past_medication_trials: parts[2] || ""
    };
};

const existingData = parseMentalHealthNotes(props.socialHistory?.mental_health_notes);

const form = useForm({
    psychological_history: existingData.psychological_history,
    devolepmental_history: existingData.devolepmental_history,
    past_medication_trials: existingData.past_medication_trials,
});

const fields = [
    {
        key: "psychological_history",
        type: "text",
        placeholder: "Psychosocial History",
    },
    {
        key: "devolepmental_history",
        type: "text",
        placeholder: "Developmental History",
    },
    {
        key: "past_medication_trials",
        type: "text",
        placeholder: "Past Medication Trials",
    },
];

const submitForm = () => {
    isValidated.value = true;

    // Transform the mental health data into the expected format
    const transformedData = {
        mental_health_notes: [
            form.psychological_history,
            form.devolepmental_history,
            form.past_medication_trials
        ].filter(item => item && item.trim()).join(' | ')
    };

    form.transform(() => transformedData).post(route("patient.social-history.store"), {
        onSuccess: () => {
            isValidated.value = false;
            form.reset();
            closeModal();
        },
        onError: () => {
            isValidated.value = false;
        }
    });
};
 const closeModal = () => {
    emit("close");
 }
</script>

<template>
    <form @submit.prevent="submitForm" novalidate class="needs-validation" :class="{ 'was-validated': isValidated }">
        <div v-for="field in fields" :key="field.key">
            <BaseInput v-model="form[field.key]" :name="field.key" :placeholder="field.placeholder"
                :label="field.placeholder" :type="field.type" required />
        </div>

        <div class="form-button mt-4 px-3 d-flex justify-content-end gap-3">
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-danger" @click="$emit('close')">
                Cancel
            </button>
        </div>
    </form>
</template>
