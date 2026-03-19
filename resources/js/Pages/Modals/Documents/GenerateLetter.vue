<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'
import BaseInput from '@/Components/Common/Input/BaseInput.vue';
import BaseSelect from '@/Components/Common/Input/BaseSelect.vue';

import { useForm } from '@inertiajs/vue3'
import { BadgeDirective } from 'primevue';

const props = defineProps({
    patientId: {
        type: [String, Number],
        default: null
    }
})

const emit = defineEmits(['close', 'saved'])

const showModal = ref(false)
const isLoadingPatientInfo = ref(false)

// UI state
const isSubmitting = ref(false)

// Recipient search
const recipientSearchQuery = ref('')
const recipientSearchResults = ref([])
const selectedRecipient = ref(null)

// Inertia form
const form = useForm({
    letterType: '',
    subject: '',
    to: '',
    recipient_address: '',
    body: '',
    user_id: null,
    patient_id: props.patientId,
    qr_data: ''
})

// ---------------- HELPERS ----------------
const formatDayWithOrdinal = (day) => {
    if (day > 3 && day < 21) return day + 'th'
    switch (day % 10) {
        case 1: return day + 'st'
        case 2: return day + 'nd'
        case 3: return day + 'rd'
        default: return day + 'th'
    }
}

const formatLongDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    if (isNaN(date)) return dateString
    return `${date.toLocaleString(undefined, { month: 'long' })} ${formatDayWithOrdinal(date.getDate())}, ${date.getFullYear()}`
}

// ---------------- RECIPIENT SEARCH ----------------
const searchRecipients = async () => {
    if (recipientSearchQuery.value.length < 2) {
        recipientSearchResults.value = []
        return
    }

    try {
        const res = await axios.get(route('doctor.search.recipients', {
            q: recipientSearchQuery.value
        }))
        recipientSearchResults.value = res.data || []
    } catch {
        recipientSearchResults.value = []
    }
}

const selectRecipient = (recipient) => {
    selectedRecipient.value = recipient
    recipientSearchQuery.value = recipient.name
    recipientSearchResults.value = []
    
    form.to = recipient.name
    form.recipient_address = recipient.street_address_1 || ''
    form.user_id = recipient.id
}

// ---------------- PATIENT INFO ----------------
const fetchPatientInfo = async () => {
    if (!props.patientId) return

    isLoadingPatientInfo.value = true
    try {
        const response = await axios.get(
            route('doctor.documents.patientInfo'),
            { params: { patient_id: props.patientId } }
        )

        if (response.data?.success && response.data.data?.found) {
            const p = response.data.data.patient
            const dob = p?.dob ? formatLongDate(p.dob) : 'N/A'

            form.body =
                `This letter is in regards to ${p.first_name} ${p.last_name} ` +
                `(Date of Birth: ${dob}), who is a patient of mine.`

            if (p.last_encounter_date) {
                form.body += ` ${p.first_name} was last seen by me on ${formatLongDate(p.last_encounter_date)}.`
            }
        }
    } finally {
        isLoadingPatientInfo.value = false
    }
}

// ✅ CORRECT watcher
watch(
    () => props.patientId,
    () => {
        fetchPatientInfo()
    },
    { immediate: true }
)

// ---------------- MODAL ----------------
const closeModal = () => {
    showModal.value = false
    document.body.style.overflow = 'auto'
    emit('close')
}

// ---------------- SUBMIT ----------------
const submit = () => {
    if (isSubmitting.value) return

    isSubmitting.value = true

    form.post(route('doctor.documents.storeLetter'), {
        onSuccess: () => {
            emit('saved')
            closeModal()
        },
        onFinish: () => {
            isSubmitting.value = false
        }
    })
}

// ---------------- CONSTANTS ----------------
const letterTypes = [
    'Referral Letter',
    'Medical Certificate',
    'Discharge Summary',
    'Consultation Report',
    'Prescription Letter',
    'Test Results Letter',
    'Follow-up Letter',
    'Emergency Letter'
]

defineExpose({ closeModal })
</script>

<template>
    <form @submit.prevent="submit" class="letter-generator-container">
        <!-- Success Message -->
         
        <div class="row">
            <div class="col">
                <BaseSelect v-model="form.letterType" label="Letter Type" placeholder="Select a letter type..." :error="form.errors?.letterType">
                      <option v-for="type in letterTypes" :key="type" :value="type">
                        {{ type }}
                    </option>
                 </BaseSelect>
                 
            </div>
        </div>

        <div class="row mt-3">
            <div class="col">
                 <BaseInput v-model="form.subject" label="Subject" placeholder="Enter letter subject..." required/>
            </div>
        </div>
         <div class="row mt-3">
            <div class="col position-relative">
                     <BaseInput v-model="recipientSearchQuery" label="To"  @input="searchRecipients" 
                        placeholder="Search lab, pharmacy, doctor..."  required />
                  <div v-if="recipientSearchResults.length" class="search-results-dropdown list-group shadow-sm">
                    <button v-for="r in recipientSearchResults" :key="r.id" type="button" 
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" 
                        @click="selectRecipient(r)">
                        <span>{{ r.name }}</span>
                        <span class="badge badge-soft-primary text-uppercase" style="font-size: 0.7rem;">{{ r.type }}</span>
                    </button>
                </div>
            </div>
            </div>
 
        <div class="row mt-3">
            <div class="col">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label mb-0">Letter Body</label>
                    <span v-if="isLoadingPatientInfo" class="spinner-border spinner-border-sm text-primary" role="status"></span>
                </div>
                <textarea v-model="form.body" class="form-control" rows="5" placeholder="Enter letter body..." required></textarea>
            </div>
        </div>
 

        <div class="d-flex justify-content-end gap-2 mt-4">
           
            <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                {{ isSubmitting ? 'Saving...' : 'Save' }}
            </button>
             <button type="button" class="btn btn-light" @click="closeModal" :disabled="isSubmitting">
                Close
            </button>
        </div>
    </form>
</template>

<style scoped>
.letter-generator-container {
    padding: 10px;
}

.form-label {
    font-weight: 600;
    color: #495057;
    font-size: 0.9rem;
}

.search-results-dropdown {
    position: absolute;
    width: 100%;
    z-index: 1050;
    max-height: 200px;
    overflow-y: auto;
    margin-top: 2px;
}

.letter-body-textarea {
    font-family: 'Courier New', Courier, monospace;
    line-height: 1.6;
    padding: 15px;
    background-color: #fcfcfc;
    border: 1px solid #ced4da;
}

.badge-soft-primary {
    background-color: #e7f1ff;
    color: #0d6efd;
}
</style>
