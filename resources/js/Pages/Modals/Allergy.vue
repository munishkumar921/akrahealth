<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
 import BaseInput from '@/Components/Common/Input/BaseInput.vue';
import BaseSelect from '@/Components/Common/Input/BaseSelect.vue';
import BaseDatePicker from '@/Components/Common/Input/BaseDatePicker.vue';
const props = defineProps({
     encounters: Object,
});
const form = useForm({
    id: '',
    patient_id: props.encounters?.patient_id || null,
    doctor_id: props.encounters?.doctor_id || null,
    encounter_id: props.encounters?.id || null,
    allergies_medicine: '',
    allergies_reaction: '',
    allergies_severity: '',
    notes: '',
    date_active: '',
});
const closeModal = () => {
    emit("close");
};
const emit = defineEmits(["close", "submit"]);

const isValidated = ref(false);
const submitForm = () => {
    isValidated.value = true;

    form.post(route('doctor.allergies.store'), {
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    });
};
const fieldsOne = [
    {
        key: 'allergies_medicine',
        label: 'Substance or Medication',
        type: 'text',
        placeholder: 'Enter allergy substance or medication',
    },
    {
        key: 'allergies_reaction',
        label: 'Reaction',
        type: 'text',
        placeholder: 'Describe the reaction',
    },
];
const fieldsTwo = [
    {
        key: 'notes',
        label: 'Notes',
        type: 'textarea',
        placeholder: 'Enter additional notes (optional)',
    },
];
const severityOptions = [
    { value: 'mild', label: 'Mild' },
    { value: 'moderate', label: 'Moderate' },
    { value: 'severe', label: 'Severe' },
];


const update = (allergy) => {

  Object.keys(form).forEach(key => {
    if (allergy[key] !== undefined) {
      form[key] = allergy[key];
    }
  });
};

defineExpose({
  update,
  resetForm: () => form.reset(),
});
</script>
<template>

    <form @submit.prevent="submitForm" novalidate class="needs-validation" :class="{ 'was-validated': isValidated }">

        <!-- 🔹 Group 1 -->
        <div v-for="field in fieldsOne" :key="field.key" class="mb-3">
            <label :for="field.key">{{ field.label }}</label>
            <BaseInput v-model="form[field.key]" :name="field.key" :id="field.key" :placeholder="field.placeholder"
                :type="field.type" required />
        </div>

        <!-- 🔹 Severity -->
        <div class="mb-3">
            <label for="allergies_severity">Severity</label>
            <BaseSelect v-model="form.allergies_severity" id="allergies_severity" required>
                <option value="">Select Severity</option>
                <option v-for="opt in severityOptions" :key="opt.value" :value="opt.value">
                    {{ opt.label }}
                </option>
            </BaseSelect>
        </div>

        <!-- 🔹 Date Active -->
        <div class="mb-3">
            <label for="date_active">Date Active</label>
            <BaseDatePicker v-model="form.date_active" id="date_active" name="date_active"
                placeholder="Select Date Active" required />
        </div>

        <!-- 🔹 Group 2 -->
        <div v-for="field in fieldsTwo" :key="field.key" class="mb-3">
            <label :for="field.key">{{ field.label }}</label>
            <BaseInput v-model="form[field.key]" :name="field.key"  :id="field.key" :placeholder="field.placeholder"
                :type="field.type" />
        </div>

        <!-- 🔹 Buttons -->
        <div class="form-button mt-4 d-flex justify-content-end gap-3">
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-danger" @click="closeModal">Close</button>
        </div>

    </form>


</template>
