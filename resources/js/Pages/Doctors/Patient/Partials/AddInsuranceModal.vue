<script setup>
import { ref } from "vue";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";

const props = defineProps({
    form: Object,
});

const fieldsOne = [
    { key: "facility", type: "text", placeholder: "Facility" },
    { key: "phone", type: "text", placeholder: "Phone" },
    { key: "email", type: "email", placeholder: "E-mail Address" },
    { key: "address", type: "text", placeholder: "Address" },
    { key: "city", type: "text", placeholder: "City" },
];

const fieldsTwo = [
    { key: "pincode", type: "text", placeholder: "Pincode" },
    { key: "comments", type: "text", placeholder: "Comments" },
];

const isValidated = ref(false);

const states = [
    { value: "Kerala", label: "Kerala" },
    { value: "Tamil Nadu", label: "Tamil Nadu" },
    { value: "Karnataka", label: "Karnataka" },
];

const emit = defineEmits(["close", "submit"]);

const submitForm = () => {
    isValidated.value = true;
    emit("submit");
};

const closeModal = () => {
    emit("close");
};
</script>

<template>
<form @submit.prevent="submitForm">
        <div v-for="field in fieldsOne" :key="field.key">
            <BaseInput
                v-model="form[field.key]"
                :name="field.key"
                :placeholder="field.placeholder"
                :type="field.type"
                required
            />
        </div>

        <BaseSelect v-model="form.state" placeholder="Select State" required>
            <option
                v-for="state in states"
                :key="state.value"
                :value="state.value"
            >
                {{ state.label }}
            </option>
        </BaseSelect>

        <div v-for="field in fieldsTwo" :key="field.key">
            <BaseInput
                v-model="form[field.key]"
                :name="field.key"
                :placeholder="field.placeholder"
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
