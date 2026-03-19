<script setup>
import { defineProps, defineEmits, computed } from 'vue'

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
 
     <!-- ✅ PRINT AREA -->
    <div id="print-area" class="patient-summary-body">
      <div class="d-flex justify-content-between mb-3">
        <strong>Patient Details</strong>
        <button class="btn btn-danger no-print" @click="printSummary">
          <i class="bi bi-printer-fill"></i> Print
        </button>
      </div>

      <div class="card mb-3">
        <div class="card-body row">
          <div class="col-md-6">
            <div><strong>Name:</strong> {{ display(props.patient?.name) }}</div>
            <div v-if="props.patient?.dob">
              <strong>DOB:</strong> {{ display(props.patient.dob) }}
            </div>
            <div v-if="props.patient?.sex">
              <strong>Gender:</strong> {{ display(props.patient.sex) }}
            </div>
          </div>

          <div class="col-md-6">
            <div v-if="props.patient?.mrn"><strong>MRN:</strong> {{ props.patient.mrn }}</div>
            <div v-if="props.patient?.mobile">
              <strong>Phone:</strong> {{ props.patient.mobile }}
            </div>
            <div v-if="props.patient?.email">
              <strong>Email:</strong> {{ props.patient.email }}
            </div>
            <div v-if="props.patient?.address_1">
              <strong>Address:</strong> {{ props.patient.address_1 }} {{ props.patient.address_2 }}
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Left column -->
        <div class="col-md-6">
          <div class="card mb-3">
            <div class="card-header">
              <strong class="text-white">Conditions</strong>
            </div>
             <div class="card-body overflow-auto">
              <ul class="list-group list-group-flush">
                <li v-for="(condition, index) in conditions" :key="`condition-${index}`" class="list-group-item">
                  {{ display(condition?.issue || condition) }}
                </li>
                <li v-if="!conditions.length" class="list-group-item text-muted">No conditions</li>
              </ul>
            </div>
          </div>

          <div class="card mb-3">
            <div class="card-header">
              <strong class="text-white">Medications</strong>
            </div>
            <div class="card-body">
              <!-- {{ medications }} -->
              <ul class="list-group list-group-flush">
                <li v-for="(medication, index) in medications" :key="`medication-${index}`" class="list-group-item">
                  {{ display(medication?.medication || medication) }}
                </li>
                <li v-if="!medications.length" class="list-group-item text-muted">No medications</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Right column -->
        <div class="col-md-6">
          <div class="card mb-3">
            <div class="card-header">
              <strong class="text-white">Supplements</strong>
            </div>
            <div class="card-body">
              <ul class="list-group list-group-flush">
                <li v-for="(supplement, index) in supplements" :key="`supplement-${index}`" class="list-group-item">
                  {{ display(supplement?.supplement || supplement) }}
                </li>
                <li v-if="!supplements.length" class="list-group-item text-muted">No supplements</li>
              </ul>
            </div>
          </div>

          <div class="card mb-3">
            <div class="card-header">
              <strong class="text-white">Allergies</strong>
            </div>
            <div class="card-body">
              <ul class="list-group list-group-flush">
                <li v-for="(allergy, index) in allergies" :key="`allergy-${index}`" class="list-group-item">
                  {{ display(allergy?.allergies_medicine ?? allergy) }}
                </li>
                <li v-if="!allergies.length" class="list-group-item text-muted">No allergies</li>
              </ul>
            </div>
          </div>

          <div class="card mb-3">
            <div class="card-header">
              <strong class="text-white">Immunizations</strong>
            </div>
            <div class="card-body">
              <ul class="list-group list-group-flush">
                <li v-for="(immunization, index) in immunizations" :key="`immunization-${index}`" class="list-group-item">
                  {{ display(immunization?.immunization ?? immunization) }}
                </li>
                <li v-if="!immunizations.length" class="list-group-item text-muted">No immunizations</li>
              </ul>
            </div>
          </div>
          
        </div>
       
      </div>
    </div>
</template>

<style scoped>
 
.patient-sym-list {
  background-color: #fff;
  border-radius: 6px;
  
}

.patient-sym-list strong {
  display: block;
  font-size: 1rem;
  font-weight: 600;
  margin-top: 1rem;
  margin-bottom: 0.5rem;
  color: #000;
}
 
.text-muted {
  color: #6c757d !important;
}
.patient-list {
  list-style: none; /* remove default bullets */
  padding-left: 0;
  margin-bottom: 0rem;
}

.patient-list li {
  position: relative;
  padding-left: 1rem;
  margin-bottom: 0.4rem;
  color: #333;
  line-height: 1.1;
  word-break: break-word;

}

.patient-list li::before {
  content: "•";
  position: absolute;
  left: 0;
  top: 0;
  font-size: 1.2rem;
  line-height: 1rem;
  color: #4a4a4a; /* dark gray, same as your screenshot */
}

@media print {
  .patient-summary-footer {
    display: none;
  }
  .patient-summary-title {
    color: black;
  }
}

</style>