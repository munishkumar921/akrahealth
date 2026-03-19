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
  <div class="row gap-y">
      <div v-for="(item, index) in items" :key="index" class="col-lg-6">
          <div class="rounded bg-contrast shadow-box p-3 d-flex align-items-center gap-2">
              <div class="w-20">
                <img class="img-fluid" :src=item.icon  />
              </div>
              <div class="w-80">
              <h5 class="bold fs-20">{{ item.title }}</h5>
              <p class="mb-0">{{ item.description }}</p>
              </div>
          </div>
      </div>
    </div>
</template>