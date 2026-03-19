<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Common/Modal.vue';

const props = defineProps({
    isOpen: Boolean,
    patient: Object,
});

const emit = defineEmits(['close']);

const photoInput = ref(null);
const photoPreview = ref(null);

const form = useForm({
    photo: null,
});

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];

    if (!photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };

    reader.readAsDataURL(photo);
    form.photo = photo;
};

const updateProfilePhoto = () => {
    if (photoInput.value.files[0]) {
        // Replace 'user.profile-photo.update' with your actual route name
        form.post(route('user.profile-photo.update'), {
            errorBag: 'updateProfilePhoto',
            preserveScroll: true,
            onSuccess: () => {
                clearPhotoFileInput();
                emit('close');
            },
        });
    }
};

const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = null;
    }
};

const closeModal = () => {
    emit('close');
    clearPhotoFileInput();
    photoPreview.value = null;
    form.clearErrors();
};
</script>

<template>
    <Modal :isOpen="isOpen" title="Update Profile Photo" @close="closeModal">
        <div class="p-4">
            <input ref="photoInput" type="file" class="d-none" @change="updatePhotoPreview">

            <div class="mt-2 text-center">
                <!-- Current Profile Photo -->
                <div v-show="!photoPreview" class="mt-2">
                    <img :src="patient?.profile_photo_url" alt="Current Profile Photo" class="rounded-circle"
                        style="width: 150px; height: 150px; object-fit: cover;">
                </div>

                <!-- New Profile Photo Preview -->
                <div v-show="photoPreview" class="mt-2">
                    <span class="d-block rounded-circle mx-auto"
                        :style="'width: 150px; height: 150px; background-size: cover; background-repeat: no-repeat; background-position: center center; background-image: url(\'' + photoPreview + '\');'" />
                </div>

                <button class="btn btn-danger mt-3" type="button" @click.prevent="selectNewPhoto">
                    Select A New Photo
                </button>

                <div v-if="form.errors.photo" class="text-danger mt-2">
                    {{ form.errors.photo }}
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-danger" @click="closeModal">Cancel</button>
            <button type="button" class="btn btn-primary" @click="updateProfilePhoto" :disabled="form.processing">
                Save
            </button>
        </div>
    </Modal>
</template>
