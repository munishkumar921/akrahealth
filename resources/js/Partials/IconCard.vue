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
  <div class="row gap-y justify-content-center">
    <div v-for="(item, index) in items" :key="index" class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
        <div class="icon-box">
            <div class="mb-2 justify-content-center">
              <img :src="item.icon">
              <!-- <img src="/images/svg/customizable-emr-templates.svg" alt="emr-online-chart" /> -->
            </div>
            <h4 class="title">{{ item.title }}</h4>
            <p class="description">{{ item.description }}</p>
        </div>
    </div>
  </div>
</template>