<script setup>
import { ref,computed } from 'vue';


const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    label: String,
    placeholder: {
        type: String,
        default: "Type and press Enter...",
    },
    id: String,
});

const emit = defineEmits(["update:modelValue"]);

const inputValue = ref("");
const inputEl = ref(null);

const addTag = () => {
    const value = inputValue.value.trim();
    if (value && !tags.value.includes(value)) {
        emit("update:modelValue", [...tags.value, value]);
    }
    inputValue.value = "";
};

const removeTag = (tag) => {
    emit("update:modelValue", tags.value.filter((t) => t !== tag));
};

const handleKeyDown = (event) => {
    if (event.key === "Enter" || event.key === ",") {
        event.preventDefault();
        addTag();
    } else if (event.key === "Backspace" && !inputValue.value) {
        removeTag(tags.value[tags.value.length - 1]);
    }
};

const focusInput = () => {
    if(inputEl.value) {
        inputEl.value.focus();
    }
}
const tags = computed(() => {
    return typeof props.modelValue === 'string'
        ? props.modelValue.split(/\s+/)   // split by space
        : props.modelValue;
});
</script>

<template>
    <label v-if="label" :for="id" class="form-label">{{ label }}</label>
     <div @click="focusInput"
        class="form-control h-auto flex items-center flex-wrap gap-1">
        <span v-for="(tag, index) in tags" :key="index"
            class="tag label label-info">
            {{ tag }}
            <span @click.stop="removeTag(tag)">
                ×
            </span>
            
        </span>

        <input ref="inputEl" v-model="inputValue" :placeholder="placeholder" @keydown="handleKeyDown" class="border-0 outline-none flex-grow p-0" />
    </div>
</template>
<style>
.tag {
    @apply bg-blue-100 text-blue-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded;
}

.tag span { 
    @apply cursor-pointer hover:text-red-500;
}

</style>