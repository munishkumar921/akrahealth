<script setup>
import { useForm } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import DatePicker from '@/Components/Common/Input/BaseDatePicker.vue';
import BaseSelect from '@/Components/Common/Input/BaseSelect.vue';
import { ref, onMounted, watch } from 'vue';

const props = defineProps({
    row: {
        type: Object,
        default: () => ({})
    },
    patients: {
        type: Array,
        default: () => []
    },
    doctors: {
        type: Array,
        default: () => []
    },
    isEdit: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close', 'saved']);

const form = useForm({
    patient_id: "",
    subject: "",
    message: "",
    date: new Date().toISOString().split('T')[0],
    to: [],
    cc: [],
    submit_type: "sent",
    id: "",
});

// Initialize form with row data
const initForm = () => {
    form.patient_id = props.row?.patient_id || "";
    form.subject = props.row?.subject || "";
    form.message = props.row?.message || "";
    form.date = props.row?.date ? new Date(props.row.date).toISOString().split('T')[0] : new Date().toISOString().split('T')[0];
    form.t_messages_id = props.row?.t_messages_id || "";

    // Handle to field (convert string to array)
    if (props.row?.to) {
        if (typeof props.row.to === 'string' && props.row.to.includes(';')) {
            form.to = props.row.to.split(';').filter(v => v);
        } else if (Array.isArray(props.row.to)) {
            form.to = props.row.to;
        } else if (props.row.to) {
            form.to = [props.row.to];
        }
    }

    // Handle cc field
    if (props.row?.cc) {
        if (typeof props.row.cc === 'string' && props.row.cc.includes(';')) {
            form.cc = props.row.cc.split(';').filter(v => v);
        } else if (Array.isArray(props.row.cc)) {
            form.cc = props.row.cc;
        } else if (props.row.cc) {
            form.cc = [props.row.cc];
        }
    }
};

// Watch for row changes
watch(() => props.row, () => {
    initForm();
}, { deep: true });

const loading = ref(false);
const Draftloading = ref(false);

// Get patient name helper
const getPatientName = (patient) => {
    return patient?.user?.name || patient?.name || 'Unknown';
};

// Get doctor name helper  
const getDoctorName = (doctor) => {
    return doctor?.name || doctor?.user?.name || 'Unknown';
};

const submit = () => {
    form.submit_type = 'sent';
    loading.value = true;

    if (props.isEdit) {
        // Update existing message
        form.put(route("doctor.messages.update", props.row.id), {
            onSuccess: () => {
                loading.value = false;
                closeModal();
                emit('saved');
            },
            onError: () => {
                loading.value = false;
            }
        });
    } else {
        // Create new message
        form.post(route("doctor.messages.store"), {
            onSuccess: () => {
                loading.value = false;
                closeModal();
                emit('saved');
            },
            onError: () => {
                loading.value = false;
            }
        });
    }
};

const saveDraft = () => {
    form.submit_type = 'draft';
    Draftloading.value = true;

    if (props.isEdit) {
        form.put(route("doctor.messages.update", props.row.id), {
            onSuccess: () => {
                Draftloading.value = false;
                closeModal();
                emit('saved');
            },
            onError: () => {
                Draftloading.value = false;
            }
        });
    } else {
        form.post(route("doctor.messages.store"), {
            onSuccess: () => {
                Draftloading.value = false;
                closeModal();
                emit('saved');
            },
            onError: () => {
                Draftloading.value = false;
            }
        });
    }
};

const closeModal = () => {
    form.reset();
    form.clearErrors();
    emit('close');
};

const update = (msg) => {
    // Update form with message data
    form.patient_id = msg.patient_id || "";
    form.subject = msg.subject || "";
    form.message = msg.message || "";
    form.date = msg.date ? new Date(msg.date).toISOString().split('T')[0] : form.date;
    form.t_messages_id = msg.t_messages_id || "";

    // Handle to field
    if (msg.to) {
        if (typeof msg.to === 'string' && msg.to.includes(';')) {
            form.to = msg.to.split(';').filter(v => v);
        } else if (Array.isArray(msg.to)) {
            form.to = msg.to;
        } else {
            form.to = [msg.to];
        }
    } else {
        form.to = [];
    }

    // Handle cc field
    if (msg.cc) {
        if (typeof msg.cc === 'string' && msg.cc.includes(';')) {
            form.cc = msg.cc.split(';').filter(v => v);
        } else if (Array.isArray(msg.cc)) {
            form.cc = msg.cc;
        } else {
            form.cc = [msg.cc];
        }
    } else {
        form.cc = [];
    }
};

// Initialize on mount
onMounted(() => {
    initForm();
});

defineExpose({
    update,
    resetForm: () => form.reset(),
});
</script>

<template>
    <form @submit.prevent="submit">
        <div class="row mt-3">
            <div class="col">
                <label>Subject <span class="text-danger">*</span></label>
                <input v-model="form.subject" type="text" class="form-control" placeholder="Subject" required />
                <InputError class="mt-2" :message="form.errors.subject" />
            </div>
        </div>
        <div class="row mt-3">
            <div class="col">
                <label>Patient <span class="text-danger">*</span></label>
                <select class="form-control" v-model="form.patient_id" required>
                    <option value="">Select Patient</option>
                    <option v-for="patient in patients" :key="patient.id" :value="patient.id">
                        {{ getPatientName(patient) }}
                    </option>
                </select>
                <InputError class="mt-2" :message="form.errors.patient_id" />
            </div>
        </div>
        <div class="row mt-3">
            <div class="col">
                <label>Message <span class="text-danger">*</span></label>
                <textarea v-model="form.message" class="form-control" placeholder="Message" required
                    rows="3"></textarea>
                <InputError class="mt-2" :message="form.errors.message" />
            </div>
        </div>
        <div class="row mt-3">
            <div class="col">
                <label>Date of Message <span class="text-danger">*</span></label>
                <DatePicker v-model="form.date" type="date" placeholder="Date of Message" required />
                <InputError class="mt-2" :message="form.errors.date" />
            </div>

        </div>
        <div class="row">

            <div class="col">
                <label>Assign To</label>
                <BaseSelect v-model="form.to" multiple>
                    <option v-for="doctor in doctors" :key="doctor.id" :value="doctor.id">
                        {{ getDoctorName(doctor) }}
                    </option>
                </BaseSelect>
                <InputError class="mt-2" :message="form.errors.to" />
            </div>
        </div>

        <div class="row">


            <div class="col">
                <label>CC</label>
                <BaseSelect v-model="form.cc" multiple>
                    <option v-for="doctor in doctors" :key="doctor.id" :value="doctor.id">
                        {{ getDoctorName(doctor) }}
                    </option>
                </BaseSelect>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">

            <button type="button" class="btn btn-secondary" @click="saveDraft" :disabled="Draftloading">
                <span v-if="Draftloading" class="spinner-border spinner-border-sm mr-1"></span>
                Save as Draft
            </button>
            <button type="submit" class="btn btn-primary" :disabled="loading">
                <span v-if="loading" class="spinner-border spinner-border-sm mr-1"></span>
                {{ isEdit ? 'Update' : 'Send Message' }}
            </button>
            <button type="button" class="btn btn-danger close-modal" data-dismiss="modal" @click="closeModal">
                Close
            </button>
        </div>
    </form>
</template>
