<script setup>
import BaseInput from "@/Components/Common/Input/BaseInput.vue";

const props = defineProps({
    form: Object,
});

const emit = defineEmits(["close", "submit"]);

const closeModal = () => {
    emit("close");
};

const submitForm = () => {
    emit("submit");
};
</script>

<template>
<form @submit.prevent="submitForm">
        <BaseInput 
        type="textarea"
            v-model="form.notes"
            name="notes"
            placeholder="Billing notes here"
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
