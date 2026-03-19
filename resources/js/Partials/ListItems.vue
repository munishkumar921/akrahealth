<script setup>
import { defineProps } from 'vue';

defineProps({
  items: {
    type: Array,
    required: true,
  },
});
</script>

<template>
  <ul class="list-unstyled">
    <li v-for="(item, index) in items" :key="index" class="d-flex flex-md-row align-items-center mb-3 gap-3">
      <div class="bg-light p-3 rounded d-flex align-items-center justify-content-center mx-auto">
        <i :class="['fa-solid', item.icon || 'fa-check', 'fa-2x', 'text-primary']"></i>
      </div>
      <div class="media-body mt-md-0">
        <h5 class="bold mt-0 mb-1">{{ item.title }}</h5>
        <p class="m-0">{{ item.description }}</p>
      </div>
    </li>
  </ul>
</template>