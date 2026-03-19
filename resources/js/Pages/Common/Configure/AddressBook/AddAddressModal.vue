<script setup>
import { ref } from "vue";
import BaseInput from "../../../../Components/Common/Input/BaseInput.vue";
import BaseSelect from "../../../../Components/Common/Input/BaseSelect.vue";
import { indianStates } from "../../../../Data/commonData";

const props = defineProps({
    form: Object,
});

const emit = defineEmits(["close", "submit"]);

const fieldsOne = [
    { key: "first_name", type: "text", placeholder: "First Name" },
    { key: "last_name", type: "text", placeholder: "Last Name" },
    { key: "prefix", type: "text", placeholder: "Prefix" },
    { key: "suffix", type: "text", placeholder: "Suffix" },
    { key: "facility", type: "text", placeholder: "Facility" },
    { key: "speciality", type: "text", placeholder: "Specialty" },
    { key: "address", type: "text", placeholder: "Address" },
    { key: "city", type: "text", placeholder: "City" },
];

const fieldsTwo = [
    { key: "pin_code", type: "text", placeholder: "Pin code" },
    { key: "phone", type: "text", placeholder: "Phone" },
    { key: "email", type: "email", placeholder: "Email" },
    { key: "comments", type: "text", placeholder: "Comments" },
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

        <BaseSelect v-model="form.state" placeholder="State" label="State" required>
            <option
                v-for="option in indianStates"
                :key="option"
                :value="option"
            >
                {{ option }}
            </option>
        </BaseSelect>

        <div v-for="field in fieldsTwo" :key="field.key">
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
