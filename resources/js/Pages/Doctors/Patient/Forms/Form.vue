<script setup>
import AuthLayout from '@/Layouts/AuthLayout2.vue'
import { computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
  
// ✅ Props
const props = defineProps({
  DoctorForms: Object,
  formId: [String, Number],
  patientId: [String, Number],
  section: [Object, Array],
  type: String,
})

// ✅ Normalize questions from section (updated to match backend structure)
const questions = computed(() => {
  // Prefer section.questions if present
  if (props.section?.questions) {
    return Array.isArray(props.section.questions)
      ? props.section.questions
      : Object.values(props.section.questions ?? {}).filter(q => typeof q === 'object' && q.type)
  }

  // Fallback for legacy structure (still supports old YAML output)
  if (Array.isArray(props.section)) return props.section
  return Object.values(props.section ?? {}).filter(q => typeof q === 'object' && q.type)
})

// ✅ Inertia form setup
const form = useForm({

  title: props.section?.form_title ?? props.DoctorForms?.form_title ?? null,
  destination: props.section?.form_destination ?? null,
  id: props.formId ?? null,
  doctor_id: props.DoctorForms?.doctor_id ?? null,
  patient_id:props.patientId ?? null,
  questions: questions.value.map(q => ({
    name: q.name,
    label: q.label,
    value: q.type === 'checkbox' ? (Array.isArray(q.value) ? q.value : (typeof q.value === 'string' ? q.value.split(',').map(v => v.trim()) : [])) : (q.value ?? null),
    type: q.type,
    options: q.options && typeof q.options === 'string' ? q.options.split(',').map(o => o.trim()) : (q.section_items || q.select_items || []),
  })),

})

// ✅ Handle submission
const submitForm = () => {
   form.post(route('doctor.form.submit'), {
    preserveScroll: true,
    onSuccess: () => {
      console.log('✅ Form submitted successfully!')},
  })
}

// ✅ Cancel
const cancelForm = () => {
  router.get(route('doctor.forms.index'))}
</script>

<template>
  <AuthLayout title="Complete Form">
    <div class="iq-card">
      <div class="iq-card-header bg-primary">
        <h4 class="text-white">{{ DoctorForms.form_title || 'Complete Form' }}</h4>
      </div>

      <div class="iq-card-body">
        <form @submit.prevent="submitForm">
          <table class="table table-borderless align-middle">
            <tbody v-for="(question, qIndex) in form.questions" :key="qIndex">
               <!-- 🔹 RADIO -->
              <tr v-if="question.type === 'radio'">
                <td class="text-capitalize" style="width: 35%;">
                  {{ question.label }}
                </td>
                <td>

                  <div class="d-flex gap-4 align-items-center flex-wrap">
                    <div v-for="(option, i) in question.options || []" :key="i" class="form-check">
                      <input class="form-check-input" type="radio" :name="question.name" :id="`${question.name}-${i}`"
                        :value="option" v-model="question.value" />
                      <label class="form-check-label" :for="`${question.name}-${i}`">
                        {{ option }}
                      </label>
                    </div>
                  </div>
                </td>
              </tr>

              <!-- 🔹 TEXT -->
              <tr v-else-if="question.type === 'text'">
                <td class="text-capitalize" style="width: 35%;">
                  {{ question.label }}
                </td>
                <td>
                  <input v-model="question.value" :name="question.name" type="text" class="form-control"
                    :required="question.required ?? false" />
                </td>
              </tr>

              <!-- 🔹 SELECT -->
              <tr v-else-if="question.type === 'select'">
                <td class="text-capitalize" style="width: 35%;">
                  {{ question.label }}
                </td>
                <td>
                  <select v-model="question.value" :name="question.name" class="form-select">
                    <option value="">Select an option</option>
                    <option v-for="(option, i) in question.options || []" :key="i" :value="option">
                      {{ option }}
                    </option>
                  </select>
                </td>
              </tr>

              <!-- 🔹 CHECKBOX -->
              <tr v-else-if="question.type === 'checkbox'">
                <td class="text-capitalize" style="width: 35%;">
                  {{ question.label }}
                </td>
                <td>
                  <div class="d-flex gap-4 align-items-center flex-wrap">
                    <div v-for="(option, i) in question.options || []" :key="i" class="form-check">
                      <input class="form-check-input" type="checkbox" :id="`${question.name}-${i}`" :value="option"
                        v-model="question.value" />
                      <label class="form-check-label" :for="`${question.name}-${i}`">
                        {{ option }}
                      </label>
                    </div>
                  </div>
                </td>
              </tr>

            </tbody>
          </table>

          <!-- ✅ Buttons -->
          <div class="d-flex justify-content-end mt-4 gap-2">
            <button type="submit" class="btn btn-primary">Submit Form</button>
            <button type="button" class="btn btn-danger" @click="cancelForm">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </AuthLayout>
</template>
