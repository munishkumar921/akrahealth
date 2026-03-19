<script setup>
import { defineProps, defineEmits, ref, watch } from "vue";

const props = defineProps({
  modelValue: {
    type: Boolean,
    default:false
   },
  required: Boolean,
  label: { type: String, required: false },
  invalidFeedback: { type: String, default: "" },
});

const emit = defineEmits(["update:modelValue"]);
const isValidated = ref(false);


watch(
  () => props.modelValue,
  () => {
    isValidated.value = false;
  }
);
</script>

<template>
   <div class="form-check mb-2">
    <input
      type="checkbox"
      class="form-check-input"
      :id="label"
      :checked="modelValue"
      :required="required"
      @change="$emit('update:modelValue', $event.target.checked)"
    />
    <label class="form-check-label" :for="label">{{ label }}</label>
     <div v-if="isValidated && !modelValue" class="invalid-feedback">
      {{ invalidFeedback }}
    </div>
  </div>
</template>
