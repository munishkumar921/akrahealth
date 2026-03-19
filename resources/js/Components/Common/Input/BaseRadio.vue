<script setup>
import { defineProps, defineEmits, ref, watch } from "vue";

const props = defineProps({
    modelValue: String,
    options: Array,
    required: Boolean,
    label: { type: String, required: true },
    validFeedback: {
        type: String,
        default: "",
    },
    invalidFeedback: {
        type: String,
        default: "Please select an option!",
    },
      error: { type: String, default: "" },

});

const emit = defineEmits();
const isValidated = ref(false);

watch(
    () => props.modelValue,
    () => {
        isValidated.value = true;
    }
);
</script>

<template>
    <div class="col-md-12 mb-3">
        <label :for="name" class="form-label" v-if="label">{{ label }}</label>
        <div v-for="option in options" :key="option.value">
            <input
                type="radio"
                :value="option.value"
                :id="option.value"
                class="btn-check"
                :name="label"
                :required="required"
                @change="$emit('update:modelValue', option.value)"
            />
            <label
                class="btn btn-sm btn-outline-secondary"
                :for="option.value"
                >{{ option.label }}</label
            >
        </div>
        <div v-if="isValidated && !modelValue" class="invalid-feedback">
            {{ invalidFeedback }}
        </div>
        <div v-else class="valid-feedback">{{ validFeedback }}</div>
    </div>
</template>
