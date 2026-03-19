<script setup>
import { ref } from "vue";
import Modal from "@/Components/Common/Modal.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    isModalOpen: Boolean,
    closeModal: Function,
    socialHistory: Object,
});
const isValidated = ref(false);

const form = useForm({
    id: props.socialHistory?.id || '',
    alcohol_use: props.socialHistory?.alcohol_use || "",
    tobacco_use: props.socialHistory?.tobacco_use === true || props.socialHistory?.tobacco_use === 'true' ? true : false,
    tobacco_use_details: props.socialHistory?.tobacco_use_details || "",
    illicit_drug_use: props.socialHistory?.drug_use || "",
});

const fields = [
    {
        key: "tobacco_use_details",
        type: "text",
        placeholder: "Tobacco Use Details",
    },
    {
        key: "illicit_drug_use",
        type: "text",
        placeholder: "Illicit Drug Use",
    },
];
const tobaccoOptions = [
    { value: true, label: 'Yes' },
    { value: false, label: 'No' },
];
const submitForm = () => {
    isValidated.value = true;

    // Submit form without strict validation - allow partial saves
    form.post(route("doctor.socialHistory.store"), {
        onSuccess: () => {
            isValidated.value = false;
            form.reset();
            emit('submit');
            closeModal();
        },
        onError: () => {
            isValidated.value = false;
        },
    });
};
const closeModal = () => {
    emit("close");
};
const emit = defineEmits(["close", "submit"]);
 
</script>

<template>
        <form @submit.prevent="submitForm" novalidate class="needs-validation"
            :class="{ 'was-validated': isValidated }">
            <BaseInput v-model="form.alcohol_use" :name="form.alcohol_use" placeholder="Alcohol Use" type="text"
                label="Alcohol Use" required :error="form.errors.alcohol_use" />

                  <BaseSelect v-model="form.tobacco_use" placeholder="Tobacco Use" label="Tobacco Use" required :error="form.errors.tobacco_use"  >
                <option v-for="option in tobaccoOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                </option>
            </BaseSelect>

            <div v-for="field in fields" :key="field.key">
                <BaseInput v-model="form[field.key]" :name="field.key" :placeholder="field.placeholder"
                    :label="field.placeholder" :type="field.type" required :error="form.errors[field.key]" />
            </div>

            <div class="form-button mt-4 px-3 d-flex justify-content-end gap-3">
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-danger" @click="closeModal">
                    Cancel
                </button>
            </div>
        </form>
 </template>
