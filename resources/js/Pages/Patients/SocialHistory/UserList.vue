<script setup>
import { ref } from "vue";
import LifestyleAccordion from "./Partials/Lifestyle/LifestyleAccordion.vue";
import HabitsAccordion from "./Partials/Habits/HabitsAccordion.vue";
import MentalHealthAccordion from "./Partials/MentalHealth/MentalHealthAccordion.vue";

// Props from SocialHistory.vue
const props = defineProps({
  activeTab: { type: String, default: "lifestyle" },
  socialHistory: { type: Object, default: null },
});
</script>

<template>
  <div>
    <section class="rounded table container-fluid pt-4">
      <div class="">
        <!-- Conditionally render based on activeTab -->
        <LifestyleAccordion
          v-if="props.activeTab === 'lifestyle'"
          :socialHistory="socialHistory"
        />

        <HabitsAccordion
          v-else-if="props.activeTab === 'habits'"
          :socialHistory="socialHistory"
        />

        <MentalHealthAccordion
          v-else-if="props.activeTab === 'mental'"
          :socialHistory="socialHistory"
        />
      </div>
    </section>
  </div>
</template>
