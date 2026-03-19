<script setup>
import { computed, ref, watch } from "vue";

const props = defineProps({
    id: {
        type: String,
        default: "file-input-" + Math.random().toString(36).slice(2, 7),
    },
    label: {
        type: String,
        default: "Upload File",
    },
    accept: {
        type: String,
        default: "*",
    },
    modelValue: {
        type: [File, String],
        default: null,
    },
});

const emit = defineEmits(["update:modelValue", "change"]);

const file = ref(props.modelValue);

const fileName = computed(() => {
    if (!file.value) return ''

    let name = '';
    // File object
    if (file.value instanceof File) {
        name = file.value.name
    } else if (typeof file.value === 'string') {
        // Path string: get last segment
        name = file.value.split('/').pop()
    }

    // Strip numeric prefix (e.g., 1770196366_filename.pdf -> filename.pdf)
    return name ? name.replace(/^\d+_/, '') : ''
})
 
const fileSize = computed(() =>
    (file.value && file.value instanceof File) 
        ? (file.value.size / 1024 / 1024).toFixed(2) 
        : ""
);

const onFileChange = (event) => {
    const selectedFile = event.target.files[0];

    if (selectedFile) {
        file.value = selectedFile;
        emit("update:modelValue", selectedFile);
        emit("change", event);
    } else {
        file.value = null;
        emit("update:modelValue", null);
        emit("change", event);
    }
};

const removeFile = () => {
    file.value = null;
    emit("update:modelValue", null);
};

watch(
    () => props.modelValue,
    (newVal) => {
        file.value = newVal;
    }
);
</script>
<template>
    <div class="mb-4">
        <label class="form-label" :for="id">{{ label }}</label>

        <div class="input-group">
            <input type="text" class="form-control" :value="fileName" placeholder="Choose file..." readonly />
            <label class="input-group-text cursor-pointer border rounded-right" :for="id">
                
                Browse
            </label>
            <input type="file" class="custom-file-input" :id="id" :accept="accept" @change="onFileChange" />
        </div>

        <div v-if="file" class="d-flex justify-content-between mt-2">
            <p class="mb-0">
                File: {{ fileName }}
                <!-- <span class="text-muted">({{ fileSize }} MB)</span> -->
            </p>

            <button type="button" class="btn btn-danger d-flex align-items-center" @click="removeFile">
             <i class="bi bi-trash"></i> 
            </button>
        </div>
    </div>
</template>
 
