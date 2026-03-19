<script setup>
import { ref } from "vue";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import { genderOptions } from "@/Data/commonData";

const props = defineProps({
    form: Object,
});

const emit = defineEmits(["close", "submit"]);

const isValidated = ref(false);

const fieldsOne = [
    { key: "first_name", type: "text", placeholder: "First Name" },
    { key: "last_name", type: "text", placeholder: "Last Name" },
    { key: "date_of_birth", type: "date", placeholder: "Date of Birth" },
    { key: "phone_number", type: "text", placeholder: "Phone Number" },
    { key: "email", type: "email", placeholder: "Email" },
];

const filedsTwo = [
    { key: "mrn", type: "text", placeholder: "Medical Record Number" },
    { key: "ssn", type: "text", placeholder: "Social Security Number" },
];

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
        <div v-for="field in fieldsOne" :key="field.key">
            <BaseInput
                v-model="form[field.key]"
                :name="field.key"
                :placeholder="field.placeholder"
                :label="field.placeholder"
                :type="field.type"
                required
            />
        </div>

        <BaseSelect
            v-model="form.gender"
            placeholder="Gender"
            name="Gender"
            label="Gender"
            required
            class="mt-2"
        >
            <option
                v-for="gender in genderOptions"
                :key="gender"
                :value="gender"
            >
                {{ gender }}
            </option>
        </BaseSelect>

        <div v-for="field in filedsTwo" :key="field.key">
            <BaseInput
                v-model="form[field.key]"
                :name="field.key"
                :placeholder="field.placeholder"
                :label="field.placeholder"
                :type="field.type"
                required
            />
        </div>

        <div class="form-button mt-4 px-3 d-flex justify-content-end gap-3">
            <button type="submit" class="btn btn-primary">Search</button>
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
