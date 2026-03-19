<script setup>
import { ref } from "vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    socialHistory: Object,
 });

const emit = defineEmits(['close', 'submit']);

const isValidated = ref(false);

const form = useForm({
    alcohol_use: props.socialHistory?.alcohol_use || "",
    tobacco_use: props.socialHistory?.tobacco_use || false,
    tobacco_use_details: props.socialHistory?.tobacco_use_details || "",
    drug_use: props.socialHistory?.drug_use || "",
});

const fields = [
    {
        key: "tobacco_use_details",
        type: "text",
        placeholder: "Tobacco Use Details",
    },
    {
        key: "drug_use",
        type: "text",
        placeholder: "Illicit Drug Use",
    },
];

const submitForm = () => {
    console.log('Habits form submitting...', form.data());
    isValidated.value = true;
    form.post(route("patient.social-history.store"), {
        onSuccess: (response) => {
            console.log('Habits form success:', response);
            isValidated.value = false;
            form.reset();
            closeModal();
            // Reload the page to refresh the data
            window.location.reload();
        },
        onError: (errors) => {
            console.log('Habits form error:', errors);
            isValidated.value = false;
        }
    });
};
 const closeModal = () => {
    emit("close");
 }
</script>

<template>
    <form
        @submit.prevent="submitForm"
        novalidate
        class="needs-validation"
        :class="{ 'was-validated': isValidated }"
    >
        <BaseInput
            v-model="form.alcohol_use"
            name="alcohol_use"
            placeholder="Alcohol Use"
            type="text"
            label="Alcohol Use"
        />

        <BaseSelect
            v-model="form.tobacco_use"
            name="tobacco_use"
            placeholder="Tobacco Use"
            label="Tobacco Use"
        >
            <option :value="false">No</option>
            <option :value="true">Yes</option>
        </BaseSelect>

        <div v-for="field in fields" :key="field.key">
            <BaseInput
                v-model="form[field.key]"
                :name="field.key"
                :placeholder="field.placeholder"
                :label="field.placeholder"
                :type="field.type"
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
