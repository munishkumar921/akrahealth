<script setup>
import { ref } from 'vue';

const props = defineProps({
    form: Object,
    field: String,
    btn_text: {
        type: String,
        default: 'Select a photo',
    },
    img_shape: {
        type: String,
        default: 'rounded-full'
    },
    show_label: {
        type: Boolean,
        default: false
    },
    old_banner: {
        type: String,
        default: '/images/avatar.webp'
    }
});

const photoPreview = ref(props.form[props.field]);
const photoInput = ref(null);

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

    props.form[props.field] = photo;
};
</script>

<template>
    <div>
        <div v-if="show_label">{{ btn_text }}</div>
        <div>
            <input id="photo" ref="photoInput" type="file" class="hidden" @change="updatePhotoPreview">
            <div v-show="!photoPreview">
                <img :src="old_banner" style="width:80px;" v-if="old_banner" alt="health-care" :class="`${img_shape} size-20 object-cover`">
                <img src="/images/avatar.webp" style="width:80px;" v-else alt="health-care" :class="`${img_shape} size-20 object-cover`">
            </div>

            <div v-show="photoPreview" class="mt-2 mt-2 overflow-hidden rounded-full">
                <img :src="photoPreview" style="width:80px; height:80px;" />
            </div>

            <div :class="`flex ${show_label ? '' : 'justify-end'} mt-[-30px]`" style="margin-top: -36px;">
                <span class="edit-photo-btn"
                    @click.prevent="selectNewPhoto"
                    title="Edit Photo">
                    <i class="fa fa-pencil"></i>
                </span>
            </div>
            <InputLabel for="photo" value="Photo" />
        </div>
    </div>
</template>
