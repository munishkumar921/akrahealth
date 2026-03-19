<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseFileInput from "@/Components/Common/Input/BaseFileInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";

const emit = defineEmits(["close", "submit"]);

const isValidated = ref(false);

const form = useForm({
    id: '',
    name: '',
    description: '',
    banner: '',
    is_active: 1,
});

const submitForm = () => {
    isValidated.value = true;
    form.post(route('admin.specialities.store'), {
        onSuccess: () => {
            isValidated.value = false;
            emit("submit");
            closeModal();
        },
        onError: () => {
            isValidated.value = true;
        }
    });
};

const closeModal = () => {
    emit("close");
    form.reset();
    isValidated.value = false;
};
const update = (data) => {
    form.id = data.id;
    form.name = data.name;
    form.description = data.description;
    form.banner = data.banner;
    form.is_active = data.is_active;
}
defineExpose({ update });
const photoPreview = ref(null);

// Handle file upload
const onChangeFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.banner = file;
        // Create preview URL
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};
</script>

<template>
    <form @submit.prevent="submitForm">
        <div class="row">
             <div class="col-md-12">
                <div class="form-group">
                    <div class="custom-file">
                        <BaseFileInput 
                            id="inputFileUpload"
                            @change="onChangeFileUpload($event)" 
                            accept="image/*" 
                        />
                        <InputError class="mt-2" :message="form.errors.profile_photo" />
                    </div>
                </div>
            </div>
            <div class="col-12 mb-3">
                <BaseInput v-model="form.name" label="Name" placeholder="Speciality Name" required :error="form.errors.name" />
            </div>
            <div class="col-12 mb-3">
                <BaseInput v-model="form.description" label="Description" placeholder="Description" :error="form.errors.description" />
            </div>
            <div class="col-12 mb-3">
                <BaseSelect v-model="form.is_active" label="Status" required :error="form.errors.is_active">
                    <option :value="1">Active</option>
                    <option :value="0">Inactive</option>
                </BaseSelect>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-3">
            <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-danger" @click="closeModal">Close</button>

        </div>
    </form>
</template>
