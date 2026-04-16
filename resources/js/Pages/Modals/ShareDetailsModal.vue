<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Common/Modal.vue';
import BaseInput from '@/Components/Common/Input/BaseInput.vue';
import BaseSelect from '@/Components/Common/Input/BaseSelect.vue';
import Checkbox from '@/Components/Checkbox.vue';
import axios from 'axios';

const props = defineProps({
    isOpen: Boolean,
    patient: Object
});

const emit = defineEmits(['close']);

const doctors = ref([]);
const loadingDoctors = ref(false);

const form = useForm({
    provider_name: '',
    provider_id: '',
    email: '',
    sms: '',
    accept_term_condition: false
});


const loadDoctors = async () => {
    loadingDoctors.value = true;
    try {
        const response = await axios.get(route('patient.share.details.providers'));
        doctors.value = response.data || [];
    } catch (error) {
        console.error('Error fetching doctors:', error);
        doctors.value = [];
    } finally {
        loadingDoctors.value = false;
    }
};

const onDoctorSelect = (providerId) => {
    const doctor = doctors.value.find((item) => String(item.id) === String(providerId));

    if (!doctor) {
        form.provider_name = '';
        form.provider_id = '';
        form.email = '';
        form.sms = '';
        return;
    }

    form.provider_name = doctor.name;
    form.provider_id = doctor.id;
    form.email = doctor.email;
    form.sms = doctor.mobile;
};


const submit = () => {
    form.post(route('patient.share.details'), {
        onSuccess: () => {
            form.reset();
            closeModal();
        },
        onError: () => {
            // Handle error if needed
            console.error('Error submitting form');
        }

    });
};

const closeModal = () => {
    form.reset();
    emit('close');
};

watch(
    () => props.isOpen,
    (isOpen) => {
        if (isOpen) {
            form.reset();
            loadDoctors();
        } else {
            doctors.value = [];
        }
    },
    { immediate: true }
);
</script>

<template>
    <Modal :isOpen="isOpen" @close="closeModal" title="Invite Provider to Access your Chart" size="xl">

        <form @submit.prevent="submit" class="p-2">
            <div class="mb-3">
                <BaseSelect v-model="form.provider_id" label="Provider Name" placeholder="Select a provider..."
                    :error="form.errors.provider_id" @update:modelValue="onDoctorSelect">
                    <option v-for="doctor in doctors" :key="doctor.id" :value="doctor.id">
                        {{ doctor.name }}{{ doctor.hospital_name ? ` - ${doctor.hospital_name}` : '' }}
                    </option>
                </BaseSelect>
                <div v-if="!loadingDoctors && !doctors.length" class="small text-muted mt-2">
                    No past-appointment providers available.
                </div>
            </div>

            <div class="mb-3">
                <BaseInput v-model="form.email" type="email" label="Email" placeholder="Enter provider email"
                    :error="form.errors.email" required readonly />
            </div>

            <div class="mb-3">
                <Checkbox v-model="form.accept_term_condition" :label="'You must agree to share data with doctor'"
                    class="me-1" /> Are You Share data with doctor?
            </div>
            <div class="d-flex justify-content-end gap-2">

                <button type="submit" class="btn btn-primary" :disabled="form.processing || !form.provider_id">
                    <i class="bi bi-check2-circle me-1"></i>
                    {{ form.processing ? 'Saving...' : 'Save' }}
                </button>
                <button type="button" class="btn btn-danger" @click="closeModal">
                    Close
                </button>
            </div>
        </form>
    </Modal>
</template>

<style scoped>
.z-index-dropdown {
    z-index: 1050;
}


.hover-bg-light:hover {
    background-color: #f8f9fa;
}
</style>
