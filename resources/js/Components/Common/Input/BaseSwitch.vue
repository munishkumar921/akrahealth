<script setup>
import { defineProps, defineEmits, ref, watch } from "vue";

const props = defineProps({
    modelValue: Boolean,
    required: Boolean,
    label: { type: String, required: true },
    invalidFeedback: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue"]);
const isValidated = ref(false);

watch(
    () => props.modelValue,
    () => {
        isValidated.value = true;
    }
);
</script>

<template>
    <div class="form-switch mb-2">
        <input type="checkbox" class="form-check-input  cursor-pointer" :id="label" :checked="modelValue"
            :required="required" @change="$emit('update:modelValue', $event.target.checked)" />
        <label class="form-check-label  cursor-pointer" :for="label" v-if="label">
            {{ label }}
        </label>
        <div v-if="isValidated && !modelValue" class="invalid-feedback">
            {{ invalidFeedback }}
        </div>
    </div>
</template>
