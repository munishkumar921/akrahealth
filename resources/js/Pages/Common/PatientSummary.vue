<script setup>
import { computed } from 'vue'

/* ---------------- PROPS ---------------- */
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
  patient: {
    type: Object,
    default: () => ({}),
  },
})

/* ---------------- EMITS ---------------- */
const emit = defineEmits(['close'])

const close = () => {
  emit('close')
}

/* ---------------- PRINT SUMMARY ---------------- */
const printSummary = () => {
  const printArea = document.getElementById('print-area')
  if (!printArea) return

  const printWindow = window.open(
    '',
    '_blank',
    'width=1000,height=800,toolbar=0,location=0,menubar=0'
  )

  if (!printWindow) {
    alert('Please allow popups to print the summary.')
    return
  }

  const styles = Array.from(
    document.querySelectorAll('link[rel="stylesheet"], style')
  )
    .map(node => node.outerHTML)
    .join('\n')

  const html = `<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Patient Summary</title>
  ${styles}
  <style>
    .patient-summary-title { color: #000 !important; }
    .no-print { display: none !important; }
    .text-white { color: #000 !important; }
    .card-header { border: 1px solid #ddd; }
  </style>
</head>
<body>
  ${printArea.innerHTML}
</body>
</html>`

  printWindow.document.open()
  printWindow.document.write(html)
  printWindow.document.close()

  printWindow.onload = () => {
    setTimeout(() => {
      printWindow.focus()
      printWindow.print()
      printWindow.close()
    }, 500)
  }
}

/* ---------------- DISPLAY HELPER ---------------- */
const display = (item) => {
  if (!item) return ''

  if (typeof item === 'string') return item

  if (typeof item === 'object') {
    return (
      item.name ??
      item.title ??
      item.label ??
      item.value ??
      item.issue ??
      item.medication ??
      item.supplement ??
      item.immunization ??
      item.note ??
      ''
    )
  }

  return String(item)
}

/* ---------------- COMPUTED DATA ---------------- */
const conditions = computed(() =>
  (props.patient?.conditions ?? []).slice(0, 50)
)

const medications = computed(() =>
  (props.patient?.medications ?? []).slice(0, 50)
)

const supplements = computed(() =>
  (props.patient?.supplements ?? []).slice(0, 50)
)

const allergies = computed(() =>
  (props.patient?.allergies ?? []).slice(0, 50)
)

const immunizations = computed(() =>
  (props.patient?.immunizations ?? []).slice(0, 50)
)
</script>

<template>
  <div id="print-area" class="patient-summary-shell">
    <section class="patient-summary-hero">
      <div class="patient-summary-hero__content">
        <div class="patient-summary-kicker">Patient Snapshot</div>
        <h2 class="patient-summary-title mb-2">{{ display(props.patient?.name) || 'Patient Summary' }}</h2>
        <p class="patient-summary-subtitle mb-0">
          A compact overview of demographics, active clinical items, and supporting history.
        </p>

        <div class="patient-summary-chips">
          <span v-if="props.patient?.sex" class="summary-chip">{{ display(props.patient.sex) }}</span>
          <span v-if="props.patient?.dob" class="summary-chip">{{ display(props.patient.dob) }}</span>
          <span v-if="props.patient?.mrn" class="summary-chip">MRN {{ props.patient.mrn }}</span>
        </div>
      </div>

      <div class="patient-summary-hero__actions no-print">
        <button class="btn btn-primary btn-sm patient-summary-print" @click="printSummary">
          <i class="bi bi-printer-fill me-2"></i>Print Summary
        </button>
      </div>
    </section>

    <section class="patient-profile-grid">
      <div class="profile-detail-card">
        <div class="profile-detail-label">Phone</div>
        <div class="profile-detail-value">{{ props.patient?.mobile || 'Not available' }}</div>
      </div>
      <div class="profile-detail-card">
        <div class="profile-detail-label">Email</div>
        <div class="profile-detail-value">{{ props.patient?.email || 'Not available' }}</div>
      </div>
      <div class="profile-detail-card">
        <div class="profile-detail-label">Address</div>
        <div class="profile-detail-value">
          {{ props.patient?.address_1 ? `${props.patient.address_1} ${props.patient.address_2 || ''}`.trim() : 'Not available' }}
        </div>
      </div>
    </section>

    <div class="row g-4">
      <div class="col-12 col-xl-6">
        <section class="summary-section-card">
          <div class="summary-section-head">
            <span class="summary-section-icon section-icon-red"><i class="bi bi-clipboard2-pulse"></i></span>
            <div>
              <h3>Conditions</h3>
              <p>{{ conditions.length }} item{{ conditions.length === 1 ? '' : 's' }}</p>
            </div>
          </div>
          <ul class="summary-list">
            <li v-for="(condition, index) in conditions" :key="`condition-${index}`" class="summary-list-item">
              {{ display(condition?.issue || condition) }}
            </li>
            <li v-if="!conditions.length" class="summary-empty">No conditions</li>
          </ul>
        </section>

        <section class="summary-section-card">
          <div class="summary-section-head">
            <span class="summary-section-icon section-icon-blue"><i class="bi bi-capsule-pill"></i></span>
            <div>
              <h3>Medications</h3>
              <p>{{ medications.length }} item{{ medications.length === 1 ? '' : 's' }}</p>
            </div>
          </div>
          <ul class="summary-list">
            <li v-for="(medication, index) in medications" :key="`medication-${index}`" class="summary-list-item">
              {{ display(medication?.medication || medication) }}
            </li>
            <li v-if="!medications.length" class="summary-empty">No medications</li>
          </ul>
        </section>
      </div>

      <div class="col-12 col-xl-6">
        <section class="summary-section-card">
          <div class="summary-section-head">
            <span class="summary-section-icon section-icon-green"><i class="bi bi-flower1"></i></span>
            <div>
              <h3>Supplements</h3>
              <p>{{ supplements.length }} item{{ supplements.length === 1 ? '' : 's' }}</p>
            </div>
          </div>
          <ul class="summary-list">
            <li v-for="(supplement, index) in supplements" :key="`supplement-${index}`" class="summary-list-item">
              {{ display(supplement?.supplement || supplement) }}
            </li>
            <li v-if="!supplements.length" class="summary-empty">No supplements</li>
          </ul>
        </section>

        <section class="summary-section-card">
          <div class="summary-section-head">
            <span class="summary-section-icon section-icon-amber"><i class="bi bi-exclamation-triangle"></i></span>
            <div>
              <h3>Allergies</h3>
              <p>{{ allergies.length }} item{{ allergies.length === 1 ? '' : 's' }}</p>
            </div>
          </div>
          <ul class="summary-list">
            <li v-for="(allergy, index) in allergies" :key="`allergy-${index}`" class="summary-list-item">
              {{ display(allergy?.allergies_medicine ?? allergy) }}
            </li>
            <li v-if="!allergies.length" class="summary-empty">No allergies</li>
          </ul>
        </section>

        <section class="summary-section-card">
          <div class="summary-section-head">
            <span class="summary-section-icon section-icon-purple"><i class="bi bi-shield-check"></i></span>
            <div>
              <h3>Immunizations</h3>
              <p>{{ immunizations.length }} item{{ immunizations.length === 1 ? '' : 's' }}</p>
            </div>
          </div>
          <ul class="summary-list">
            <li v-for="(immunization, index) in immunizations" :key="`immunization-${index}`" class="summary-list-item">
              {{ display(immunization?.immunization ?? immunization) }}
            </li>
            <li v-if="!immunizations.length" class="summary-empty">No immunizations</li>
          </ul>
        </section>
      </div>
    </div>
  </div>
</template>

<style scoped>
.patient-summary-shell {
  padding: 0.25rem;
  color: #0f172a;
}

.patient-summary-hero {
  display: flex;
  justify-content: space-between;
  gap: 1.5rem;
  padding: 1.5rem 1.75rem;
  margin-bottom: 1.25rem;
  border: 1px solid #dbeafe;
  border-radius: 24px;
  background: linear-gradient(135deg, #eff6ff 0%, #f8fbff 55%, #eef2ff 100%);
}

.patient-summary-kicker {
  margin-bottom: 0.5rem;
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: #2563eb;
}

.patient-summary-title {
  font-size: clamp(1.7rem, 3vw, 2.3rem);
  font-weight: 700;
  line-height: 1.1;
  color: #0f172a;
}

.patient-summary-subtitle {
  max-width: 560px;
  color: #475569;
}

.patient-summary-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 0.65rem;
  margin-top: 1rem;
}

.summary-chip {
  display: inline-flex;
  align-items: center;
  padding: 0.5rem 0.9rem;
  border-radius: 999px;
  background: rgba(37, 99, 235, 0.08);
  color: #1d4ed8;
  font-size: 0.85rem;
  font-weight: 600;
}

.patient-summary-hero__actions {
  display: flex;
  align-items: flex-start;
}

.patient-summary-print {
  border-radius: 12px;
  padding: 0.7rem 1rem;
  box-shadow: 0 14px 24px rgba(37, 99, 235, 0.16);
}

.patient-profile-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1rem;
  margin-bottom: 1.25rem;
}

.profile-detail-card,
.summary-section-card {
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  background: #fff;
  box-shadow: 0 18px 45px rgba(15, 23, 42, 0.06);
}

.profile-detail-card {
  padding: 1rem 1.15rem;
}

.profile-detail-label {
  margin-bottom: 0.35rem;
  font-size: 0.76rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #64748b;
}

.profile-detail-value {
  font-size: 1rem;
  font-weight: 600;
  color: #0f172a;
  word-break: break-word;
}

.summary-section-card {
  padding: 1.1rem;
  margin-bottom: 1rem;
}

.summary-section-head {
  display: flex;
  align-items: center;
  gap: 0.9rem;
  margin-bottom: 1rem;
}

.summary-section-head h3 {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 700;
  color: #0f172a;
}

.summary-section-head p {
  margin: 0.2rem 0 0;
  font-size: 0.88rem;
  color: #64748b;
}

.summary-section-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 44px;
  height: 44px;
  border-radius: 14px;
  font-size: 1.05rem;
}

.section-icon-red {
  background: #fee2e2;
  color: #dc2626;
}

.section-icon-blue {
  background: #dbeafe;
  color: #2563eb;
}

.section-icon-green {
  background: #dcfce7;
  color: #16a34a;
}

.section-icon-amber {
  background: #fef3c7;
  color: #d97706;
}

.section-icon-purple {
  background: #ede9fe;
  color: #7c3aed;
}

.summary-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.summary-list-item,
.summary-empty {
  padding: 0.8rem 0.95rem;
  border: 1px solid #eef2f7;
  border-radius: 14px;
  background: #f8fafc;
  font-size: 0.95rem;
}

.summary-list-item + .summary-list-item,
.summary-empty + .summary-list-item,
.summary-list-item + .summary-empty {
  margin-top: 0.65rem;
}

.summary-empty {
  color: #64748b;
}

@media print {
  .patient-summary-shell {
    padding: 0;
  }

  .patient-summary-hero {
    box-shadow: none;
    background: #fff !important;
    border-color: #d1d5db;
  }

  .profile-detail-card,
  .summary-section-card,
  .summary-list-item,
  .summary-empty {
    box-shadow: none;
    background: #fff !important;
  }
}

@media (max-width: 991px) {
  .patient-summary-hero {
    flex-direction: column;
  }

  .patient-profile-grid {
    grid-template-columns: 1fr;
  }
}
</style>
