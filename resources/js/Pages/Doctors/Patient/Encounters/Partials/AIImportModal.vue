<script setup>
import { mockDoctorNames } from "@/Data/commonData";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import BaseFileInput from "@/Components/Common/Input/BaseFileInput.vue";

const props = defineProps({
    form: Object,
});

const emit = defineEmits(["close", "submit"]);

const closeModal = () => {
    emit("close");
};

function submitForm() {
    if (props.form.audio_file) {
        const formData = new FormData();
        formData.append("audio_file", props.form.audio_file);
        for (const key in props.form) {
            formData.append(key, props.form[key]);
        }
        // API Call
        emit("submit");
    }
}
</script>

<template>
    <BaseFileInput
        id="audioFile"
        label="Upload Audio File"
        :modelValue="form.audio_file"
        @update:modelValue="(file) => (form.audio_file = file)"
        accept="audio/*"
    />

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
        <button type="button" class="btn btn-primary" @click="submitForm">
            Import
        </button>
        <button type="button" class="btn btn-danger" @click="closeModal">
            Cancel
        </button>
    </div>
</template>
