<script setup>
import { ref } from "vue";
import BaseInput from "../../Common/Input/BaseInput.vue";
import BaseSelect from "../../Common/Input/BaseSelect.vue";
import BaseDatePicker from "../../Common/Input/BaseDatePicker.vue";

const props = defineProps({
    form: Object,
});

const emit = defineEmits(["close", "submit"]);
const isValidated = ref(false);

const submitForm = () => {
    isValidated.value = true;
    emit("submit");
};

const closeModal = () => {
    emit("close");
};

const fields = [
    { key: "first_name", type: "text", placeholder: "First Name" },
    { key: "last_name", type: "text", placeholder: "Last Name" },
    { key: "email", type: "email", placeholder: "E-mail Address" },
    { key: "mobile", type: "text", placeholder: "Mobile" },
    { key: "address", type: "text", placeholder: "Address" },
];
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
<div class="col-md-6 mb-3">
            <BaseSelect v-model="form.sex" placeholder="Select Gender" label="Gender" required>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </BaseSelect>
        </div>
        <BaseDatePicker
            v-model="form.dob"
            name="dob"
            label="Date of Birth"
            placeholder="Date of birth"
            required
        />

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
