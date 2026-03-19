<script setup>
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import {
    bloodPressurePositionOptions,
    tempertatureMethodeOptions,
} from "@/Data/commonData";

const props = defineProps({
    form: Object,
});

const fieldsOne = [
    { key: "weight", type: "number", placeholder: "Weight (kg)" },
    { key: "height", type: "number", placeholder: "Height (cm)" },
    { key: "bmi", type: "text", placeholder: "BMI (kg/m2)" },
    { key: "temperature", type: "text", placeholder: "Temperature" },
];

const fieldsTwo = [
    { key: "bp_systolic", type: "text", placeholder: "Systolic BP (mmHg)" },
    { key: "bp_diastolic", type: "text", placeholder: "Diastolic BP (mmHg)" },
];

const fieldsThree = [
    { key: "pulse", type: "text", placeholder: "Pulse (bpm)" },
    { key: "respirations", type: "text", placeholder: "Respirations (bpm)" },
    { key: "o2_saturation", type: "text", placeholder: "O2 Saturation (percent)" },
];
</script>

<template>
    <div class="col-md-12 mb-3">
        <div v-for="field in fieldsOne" :key="field.key">
            <BaseInput v-model="form[field.key]" :name="field.key" :placeholder="field.placeholder" :type="field.type"
                :label="field.placeholder" required />
        </div>

        <BaseSelect v-model="form.temperature_method" placeholder="Temperature Method" label="Temperature Method"
            required>
            <option v-for="option in tempertatureMethodeOptions" :key="option.value" :value="option.value">
                {{ option.label }}
            </option>
        </BaseSelect>

        <div v-for="field in fieldsTwo" :key="field.key">
            <BaseInput v-model="form[field.key]" :name="field.key" :placeholder="field.placeholder" :type="field.type"
                :label="field.placeholder" required />
        </div>

        <BaseSelect v-model="form.bp_position" placeholder="BP Position" label="BP Position" required>
            <option v-for="option in bloodPressurePositionOptions" :key="option.value" :value="option.value">
                {{ option.label }}
            </option>
        </BaseSelect>

        <div v-for="field in fieldsThree" :key="field.key">
            <BaseInput v-model="form[field.key]" :name="field.key" :placeholder="field.placeholder" :type="field.type"
                :label="field.placeholder" required />
        </div>
    </div>
</template>
