<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import BaseFileInput from "@/Components/Common/Input/BaseFileInput.vue";

const emit = defineEmits(["close"]);
const isLoading = ref(false);
const fileError = ref("");

const form = useForm({
    file: null,
});

const handleFileChange = (file) => {
    form.file = file;
    if (file) {
        fileError.value = "";
    }
};

const submitForm = () => {
    if (!form.file) {
        fileError.value = "Please upload a CSV file.";
        return;
    }

    fileError.value = "";
    isLoading.value = true;

    setTimeout(() => {
        isLoading.value = false;
        closeModal();
    }, 2000);
};

const closeModal = () => {
    emit("close");
};
</script>

<template>
    <form @submit.prevent="submitForm" novalidate>
        <BaseFileInput
            id="userData"
            label="Upload User CSV Document"
            :modelValue="form.file"
            @update:modelValue="handleFileChange"
            accept=".csv"
        />

        <div v-if="fileError" class="text-danger mt-2">
            {{ fileError }}
        </div>

        <div v-if="isLoading" class="mt-3 col-12">
            <div class="progress">
                <div
                    class="progress-bar progress-bar-striped progress-bar-animated"
                    style="width: 100%"
                ></div>
            </div>
        </div>

        <div class="form-button mt-4 px-3 d-flex justify-content-end gap-3">
            <button
                type="submit"
                class="btn btn-primary"
                :disabled="isLoading"
            >
                Import
            </button>
            <button
                type="button"
                class="btn btn-danger"
                @click="closeModal"
                :disabled="isLoading"
            >
                Close
            </button>
        </div>
    </form>
</template>
