<script setup>
import { ref } from "vue";
import BaseInput from "../../../../Components/Common/Input/BaseInput.vue";
import BaseSelect from "../../../../Components/Common/Input/BaseSelect.vue";
import BaseDatePicker from "../../../../Components/Common/Input/BaseDatePicker.vue";

const props = defineProps({
    form: Object,
});

const emit = defineEmits(["close", "submit"]);

const fields = [
    { key: "alert", type: "text", placeholder: "Alert" },
    { key: "user", type: "text", placeholder: "User or Provider to Alert" },
    { key: "description", type: "text", placeholder: "Description" },
];

const options = [
    { value: "yes", label: "Yes" },
    { value: "no", label: "No" },
    { value: "message_sent", label: "Message Sent" },
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
        <div v-for="field in fields" :key="field.key">
            <BaseInput
                v-model="form[field.key]"
                :name="field.key"
                :placeholder="field.placeholder"
                :type="field.type"
                required
            />
        </div>

        <BaseDatePicker
            label="Due Date"
            v-model="form.due_date"
            :name="form.due_date"
            placeholder="Due Date"
            required
        />

        <BaseSelect
            v-model="form.message_to_patient"
            placeholder="Message to Patient about Alert"
            required
        >
            <option
                v-for="option in options"
                :key="option"
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
                Close
            </button>
        </div>
    </form>
</template>
