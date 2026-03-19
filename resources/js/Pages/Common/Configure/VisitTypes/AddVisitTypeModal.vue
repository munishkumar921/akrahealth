<script setup>
import { ref } from "vue";
import BaseInput from "../../../../Components/Common/Input/BaseInput.vue";
import BaseSelect from "../../../../Components/Common/Input/BaseSelect.vue";
import {
    colorOptions,
    durationOptions,
    mockDoctorNames,
} from "../../../../Data/commonData";

const props = defineProps({
    form: Object,
});

const emit = defineEmits(["close", "submit"]);

const fields = [
    { key: "start_time", type: "time", placeholder: "Start Time" },
    { key: "end_time", type: "time", placeholder: "End Time" },
    { key: "title", type: "text", placeholder: "Title" },
    { key: "reason", type: "text", placeholder: "Reason" },
];

const isValidated = ref(false);

const submitForm = () => {
    isValidated.value = true;
    emit("submit");
};

const closeModal = () => {
    emit("close");
};
</script>

<template>
    <form
        @submit.prevent="submitForm"
        novalidate
        class="needs-validation"
        :class="{ 'was-validated': isValidated }"
    >
        <BaseInput
            v-model="form.visit_type"
            name="Visit Type"
            placeholder="Visit Type"
            label="Visit Type"
            type="text"
            required
        />

        <BaseSelect
            v-model="form.duration"
            placeholder="Duration"
            label="Duration"
            required
        >
            <option
                v-for="option in durationOptions"
                :key="option.value"
                :value="option.value"
            >
                {{ option.label }}
            </option>
        </BaseSelect>

        <BaseSelect
            v-model="form.color"
            placeholder="Color"
            label="Color"
            required
        >
            <option
                v-for="option in colorOptions"
                :key="option.value"
                :value="option.value"
            >
                {{ option.label }}
            </option>
        </BaseSelect>

        <BaseSelect
            v-model="form.provider"
            placeholder="Provider"
            label="Provider"
            required
        >
            <option
                v-for="option in mockDoctorNames"
                :key="option.value"
                :value="option.value"
            >
                {{ option.label }}
            </option>
        </BaseSelect>

        <div class="form-button mt-4 px-3 d-flex justify-content-end gap-3">
            <button type="submit" class="btn btn-primary">Save</button>
            <button
                type="button"
                class="btn btn-danger"
                @click="closeModal"
            >
                Cancel
            </button>
        </div>
    </form>
</template>
