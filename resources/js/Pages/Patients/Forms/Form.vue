<script setup>
import AuthLayout from '@/Layouts/AuthLayout2.vue'
import { computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
 
// ✅ Props
const props = defineProps({
  DoctorForms: Object,
  formId: [String, Number],
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
  patient_id: props.DoctorForms?.patient_id ?? null,
  questions: questions.value.map(q => ({
    name: q.name,
    label: q.label,
    value: q.value ?? null,
    type: q.type,
    options: q.options && typeof q.options === 'string' ? q.options.split(',').map(o => o.trim()) : (q.section_items || q.select_items || []),
  })),

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
    <div class="patient-form-shell">
      <section class="form-hero-card">
        <div class="form-hero-copy">
          <div class="form-kicker">Patient Form</div>
          <h1 class="form-title">{{ DoctorForms.form_title || 'Complete Form' }}</h1>
          <p class="form-subtitle">
            Review each question carefully and submit the completed form once all required responses are filled in.
          </p>
        </div>

        <div class="form-meta-grid">
          <div class="form-meta-card">
            <span class="form-meta-label">Destination</span>
            <strong class="form-meta-value">{{ form.destination || 'Patient Portal' }}</strong>
          </div>
          <div class="form-meta-card">
            <span class="form-meta-label">Questions</span>
            <strong class="form-meta-value">{{ form.questions.length }}</strong>
          </div>
        </div>
      </section>

      <div class="form-surface-card">
        <form @submit.prevent="submitForm" class="question-stack">
          <section
            v-for="(question, qIndex) in form.questions"
            :key="qIndex"
            class="question-card"
          >
            <div class="question-card-head">
              <div class="question-index">{{ String(qIndex + 1).padStart(2, '0') }}</div>
              <div class="question-copy">
                <label class="question-label mb-0" :for="question.name">{{ question.label }}</label>
                <div class="question-meta">
                  <span class="question-type">{{ question.type }}</span>
                  <span v-if="question.required ?? false" class="question-required">Required</span>
                </div>
              </div>
            </div>

            <div class="question-control">
              <div v-if="question.type === 'radio'" class="option-grid">
                <label
                  v-for="(option, i) in question.options || []"
                  :key="i"
                  class="choice-card"
                  :for="`${question.name}-${i}`"
                >
                  <input
                    class="form-check-input"
                    type="radio"
                    :name="question.name"
                    :id="`${question.name}-${i}`"
                    :value="option"
                    v-model="question.value"
                  />
                  <span>{{ option }}</span>
                </label>
              </div>

              <input
                v-else-if="question.type === 'text'"
                v-model="question.value"
                :id="question.name"
                :name="question.name"
                type="text"
                class="form-control modern-input"
                :required="question.required ?? false"
                placeholder="Enter your answer"
              />

              <select
                v-else-if="question.type === 'select'"
                v-model="question.value"
                :id="question.name"
                :name="question.name"
                class="form-select modern-input"
              >
                <option value="">Select an option</option>
                <option v-for="(option, i) in question.options || []" :key="i" :value="option">
                  {{ option }}
                </option>
              </select>

              <div v-else-if="question.type === 'checkbox'" class="option-grid">
                <label
                  v-for="(option, i) in question.options || []"
                  :key="i"
                  class="choice-card"
                  :for="`${question.name}-${i}`"
                >
                  <input
                    class="form-check-input"
                    type="checkbox"
                    :id="`${question.name}-${i}`"
                    :value="option"
                    v-model="question.value"
                  />
                  <span>{{ option }}</span>
                </label>
              </div>
            </div>
          </section>

          <div class="form-actions">
            <button type="button" class="btn btn-outline-danger" @click="cancelForm">Cancel</button>
            <button type="submit" class="btn btn-primary px-4">Submit Form</button>
          </div>
        </form>
      </div>
    </div>
  </AuthLayout>
</template>

<style scoped>
.patient-form-shell {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-hero-card,
.form-surface-card {
  border: 1px solid #e2e8f0;
  border-radius: 24px;
  background: #fff;
  box-shadow: 0 22px 50px rgba(15, 23, 42, 0.08);
}

.form-hero-card {
  display: grid;
  grid-template-columns: minmax(0, 1.6fr) minmax(260px, 0.8fr);
  gap: 1.5rem;
  padding: 1.75rem;
  background: linear-gradient(135deg, #eff6ff 0%, #ffffff 58%, #eef2ff 100%);
}

.form-kicker {
  margin-bottom: 0.55rem;
  font-size: 0.76rem;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: #2563eb;
}

.form-title {
  margin: 0 0 0.65rem;
  font-size: clamp(1.8rem, 3vw, 2.4rem);
  font-weight: 700;
  line-height: 1.1;
  color: #0f172a;
}

.form-subtitle {
  max-width: 680px;
  margin: 0;
  color: #475569;
}

.form-meta-grid {
  display: grid;
  gap: 1rem;
}

.form-meta-card {
  padding: 1rem 1.1rem;
  border: 1px solid rgba(148, 163, 184, 0.22);
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.78);
}

.form-meta-label {
  display: block;
  margin-bottom: 0.35rem;
  font-size: 0.74rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #64748b;
}

.form-meta-value {
  font-size: 1.02rem;
  color: #0f172a;
}

.form-surface-card {
  padding: 1.5rem;
}

.question-stack {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.question-card {
  padding: 1.2rem;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  background: #f8fafc;
}

.question-card-head {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
}

.question-index {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 42px;
  height: 42px;
  border-radius: 14px;
  background: #dbeafe;
  color: #1d4ed8;
  font-size: 0.9rem;
  font-weight: 700;
}

.question-copy {
  min-width: 0;
}

.question-label {
  display: block;
  font-size: 1.05rem;
  font-weight: 700;
  color: #0f172a;
}

.question-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.55rem;
  margin-top: 0.45rem;
}

.question-type,
.question-required {
  display: inline-flex;
  align-items: center;
  padding: 0.3rem 0.65rem;
  border-radius: 999px;
  font-size: 0.74rem;
  font-weight: 700;
  text-transform: capitalize;
}

.question-type {
  background: #e2e8f0;
  color: #334155;
}

.question-required {
  background: #fee2e2;
  color: #b91c1c;
}

.modern-input {
  min-height: 50px;
  border-radius: 14px;
  border-color: #cbd5e1;
  background: #fff;
}

.option-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 0.75rem;
}

.choice-card {
  display: flex;
  align-items: center;
  gap: 0.7rem;
  padding: 0.9rem 1rem;
  border: 1px solid #dbe4ef;
  border-radius: 16px;
  background: #fff;
  font-weight: 500;
  color: #334155;
  cursor: pointer;
}

.choice-card .form-check-input {
  margin-top: 0;
  flex-shrink: 0;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding-top: 0.5rem;
}

@media (max-width: 991px) {
  .form-hero-card {
    grid-template-columns: 1fr;
  }
}
</style>
