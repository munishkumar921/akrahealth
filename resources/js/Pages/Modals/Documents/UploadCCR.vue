<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import FileUpload from '@/Components/FileUpload.vue';
import InputError from '@/Components/InputError.vue';
import axios from 'axios';

const showModal = ref(false);

const form = useForm({
    file: '',
    description: '',
    type: 'ccr',
});

const closeModal = () => {
    emit("close");
};
const emit = defineEmits(["close", "submit"]);


const submit = () => {
    const formData = new FormData();
    formData.append('type', form.type);
    formData.append('file', form.file); // 👈 must be a File object
    // Add other fields if needed
    // formData.append('description', form.description);

    axios.post(route('doctor.documents.store'), formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
        },
    })
        .then(response => {
            closeModal();
        })
        .catch(error => {
            console.error(error);
        });
}
defineExpose({
    resetForm: () => form.reset(),
});
</script>
<template>
    <form @submit.prevent="submit">
        <div class="iq-card-body">
            <div class="row">
                <label class="mb-0">Select File</label>
                <FileUpload v-model="form.file" />
                <InputError class="mt-2" :message="form.errors.file" />
            </div>

            <div class="row mt-3">
                <div class="col">
                    <label>File Description (Optional)</label>
                    <textarea v-model="form.description" class="form-control" rows="3"
                        placeholder="Enter a description for this file..."></textarea>
                    <InputError class="mt-2" :message="form.errors.description" />
                </div>
            </div>
        </div>

        <div class="iq-card-footer d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-danger" @click="closeModal">Cancel</button>

        </div>
    </form>
</template>