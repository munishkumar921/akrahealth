
<script setup>
import AuthLayout from "@/Layouts/AuthLayout2.vue";
import PatientTimeline from "@/Components/PatientTimeline.vue";
import { defineProps } from "vue";

const props = defineProps({
  patient: {
    type: Object,
    required: false,
  },
  lastVisit: {
    type: String,
    default: "October 18th, 2025",
  },
  timeline: {
    type: Array,
    default: () => [],
  },
});

</script>
<template>
  <AuthLayout title="Patient History" description="Patient timeline and medical history" heading="">
    <PatientTimeline 
      :patient="patient" 
      :lastVisit="lastVisit" 
      :timeline="timeline" 
     />
  </AuthLayout>
</template>

 