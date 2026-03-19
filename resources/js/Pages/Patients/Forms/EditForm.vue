<script setup>
import AuthLayout from '@/Layouts/AuthLayout2.vue'
import { useForm, router } from '@inertiajs/vue3'
 
// ✅ Props
const props = defineProps({
  form: Object,
 })

// ✅ Inertia form setup
const form = useForm({
  id: props.form?.id ?? null,
  title: props.form?.title ?? null,
  destination: props.form?.destination ?? null,
  doctor_id: props.form?.doctor_id ?? null,
  patient_id: props.form?.patient_id ?? null,
  questions: props.form?.questions ?? [],
})

// ✅ Handle submission
const submitForm = () => {
  form.post(route('patient.form.show.store'), {
    preserveScroll: true,
    onSuccess: () => {
      console.log('✅ Form submitted successfully!')
    },
  })
}

// ✅ Cancel
const cancelForm = () => {
  router.get(route('patient.forms'))
}
</script>

<template>
  <AuthLayout title="Complete Form">
    <div class="iq-card">
      <div class="iq-card-header bg-primary">
        <h4 class="text-white">{{ form.title || 'Edit Form' }}</h4>
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
                    <option v-for="(option, i) in question.select_items || []" :key="i" :value="option">
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
                    <div v-for="(option, i) in question.section_items || []" :key="i" class="form-check">
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
