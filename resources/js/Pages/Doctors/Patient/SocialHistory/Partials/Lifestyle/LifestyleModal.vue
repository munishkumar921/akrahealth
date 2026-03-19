<script setup>
import { ref } from "vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    socialHistory: Object,
});
const isValidated = ref(false);

const form = useForm({
    id: props.socialHistory?.id || '',
    social_history: props.socialHistory?.social_history || "",
    sexually_active: props.socialHistory?.sexually_active === true || props.socialHistory?.sexually_active === 'true' ? true : false,
    diet: props.socialHistory?.diet || "",
    physical_activity: props.socialHistory?.physical_activity || "",
    employment: props.socialHistory?.employment || "",
});

const fields = [
    { key: "diet", type: "text", placeholder: "Diet" },
    {
        key: "physical_activity",
        type: "text",
        placeholder: "Physical Activity",
    },
    { key: "employment", type: "text", placeholder: "Employment/School" },
];

const sexuallyOptions = ref([
    { value: true, label: 'Yes' },
    { value: false, label: 'No' },
]);

const submitForm = () => {
    isValidated.value = true;

    form.post(route("doctor.socialHistory.store"), {
        onSuccess: () => {
            isValidated.value = false;
            form.reset();
            emit("submit");
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

const emit = defineEmits(["submit", "close"]);
</script>

<template>
    <form @submit.prevent="submitForm" novalidate class="needs-validation"
        :class="{ 'was-validated': isValidated }">
        <BaseInput v-model="form.social_history" :name="form.social_history" placeholder="Social History"
            type="text" label="Social History" required :error="form.errors.social_history" />

        <BaseSelect v-model="form.sexually_active" placeholder="Sexually Active" label="Sexually Active" required :error="form.errors.sexually_active">
            <option v-for="option in sexuallyOptions" :key="option.value" :value="option.value">
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
