<script setup>
import AuthLayout from '@/Layouts/AuthLayout2.vue'
import { useForm, router, Link } from '@inertiajs/vue3'

// ✅ Props definition
const props = defineProps({
  form: Object,
  type: String,
  doctorForms: Object,
})
</script>

<template>
  <AuthLayout title="Complete Form">
    <div class="iq-card">
      <div class="iq-card-header bg-primary">
        <div class="d-flex align-items-center w-100">
          <h4 class="text-white mb-0">
            {{ props.form?.title ?? 'Complete Form' }}
          </h4>
           <Link v-if="form?.title && doctorForms?.id"
            class="btn btn-light btn-sm ms-3"
            :href="route('patient.form.edit', [form?.id, form?.title ])">
            Update Form Response
          </Link>

          <button
            class="btn btn-light btn-sm ms-auto"
            @click="router.get(route('patient.forms'))">
            Back
          </button>
        </div>
      </div>
       <div class="iq-card-body">
        <!-- ✅ Safely render HTML (content_text includes <br /> tags) -->
        <div v-if="props.form?.content_text" v-html="props.form.content_text" style="white-space: pre-wrap;"></div>

        <!-- ✅ Optional: fallback display if no text -->
        <div v-else class="text-muted fst-italic">
          No form summary available.
        </div>
      </div>
    </div>
  </AuthLayout>
</template>
