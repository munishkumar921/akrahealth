<script setup>
defineProps({
  value: {
    type: String,
    default: '',
  },
  required: {
    type: Boolean,
    default: false,
  },
})
</script>

<template>
  <label class="form-label">
    <span v-if="value">
      {{ value }}
      <span v-if="required" class="text-danger">*</span>
    </span>

    <span v-else>
      <slot />
      <span v-if="required" class="text-danger">*</span>
    </span>
  </label>
</template>
