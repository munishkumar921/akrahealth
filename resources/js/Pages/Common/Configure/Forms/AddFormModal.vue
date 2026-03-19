<script setup>
import { ref } from "vue";
import BaseInput from "../../../../Components/Common/Input/BaseInput.vue";
import BaseSelect from "../../../../Components/Common/Input/BaseSelect.vue";
import {
    ageAssosciationOptions,
    genderOptions,
} from "../../../../Data/commonData";

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
</script>

<template>
    <form
        @submit.prevent="submitForm"
        novalidate
        class="needs-validation"
        :class="{ 'was-validated': isValidated }"
    >
        <BaseInput
            v-model="form.form_title"
            name="Form Title"
            placeholder="Form Title"
            type="text"
            required
        />

        <BaseSelect
            v-model="form.gender_assosciation"
            placeholder="Gender Assosciation"
            required
        >
            <option
                v-for="option in genderOptions"
                :key="option"
                :value="option"
            >
                {{ option }}
            </option>
        </BaseSelect>

        <BaseSelect
            v-model="form.age_assosciation"
            placeholder="Age Assosciation"
            required
        >
            <option
                v-for="option in ageAssosciationOptions"
                :key="option"
                :value="option"
            >
                {{ option }}
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
