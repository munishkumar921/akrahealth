<script setup>
import { ref } from "vue";
import BaseInput from "../../../Components/Common/Input/BaseInput.vue";
import BaseDatePicker from "../../../Components/Common/Input/BaseDatePicker.vue";

const props = defineProps({
    form: Object,
});
const emit = defineEmits(["close", "submit"]);

const fields = [
    { key: "description", type: "text", placeholder: "Description" },
    { key: "strength", type: "text", placeholder: "Strength" },
    { key: "manufacturer", type: "text", placeholder: "Manufacturer" },
    { key: "charges", type: "text", placeholder: "Charges" },
    { key: "quantity", type: "number", placeholder: "Quantity" },
    { key: "procedure_code", type: "text", placeholder: "Procedure Code" },
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
            v-model="form.expiration_date"
            :name="form.expiration_date"
            placeholder="Expiration Date"
            label="Expiration Date"
            required
        />
        <BaseDatePicker
            v-model="form.purchase_date"
            :name="form.purchase_date"
            placeholder="Date of Purchase"
            label="Date of Purchase"
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
