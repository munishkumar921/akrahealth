<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Common/Modal.vue';
import BaseInput from '@/Components/Common/Input/BaseInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import axios from 'axios';

const props = defineProps({
    isOpen: Boolean,
    onClose: Function,
    patient: Object
});

const doctors = ref([]);
const filteredDoctors = ref([]);
const searchQuery = ref('');

const form = useForm({
    provider_name: '',
    provider_id: '',
    email: '',
    sms: '',
    accept_term_condition: false
});

 
const searchDoctors = async (query) => {
    try {
        const response = await axios.get(route('api.doctors.search', { query }));
        filteredDoctors.value = response.data;
    } catch (error) {
        console.error('Error fetching doctors:', error);
    }
};

const onDoctorSelect = (doctor) => {
    form.provider_name = doctor.name;
    form.provider_id = doctor.id;
    form.email = doctor.email;
    form.sms = doctor.mobile;
    // Clear search query and filtered results
    searchQuery.value = doctor.name;
    filteredDoctors.value = [];
};
 

const submit = () => {
    form.post(route('patient.share.details'), {
        onSuccess: () => {
            props.onClose();
            form.reset();
            filteredDoctors.value = [];
            searchQuery.value = '';
        },
        onError: () => {
            // Handle error if needed
            console.error('Error submitting form');
        }

    });
};

const closeModal = () => {
    form.reset();
    props.onClose();
};

onMounted(() => {
    searchDoctors(searchQuery.value);
});

onUnmounted(() => {
    filteredDoctors.value = [];
}); 


</script>

<template>
    <Modal :isOpen="isOpen" @close="onClose" title="Invite Provider to Access your Chart" size="xl">
        <form @submit.prevent="submit" class="p-2">
            <div class="mb-3">
                <label class="form-label">Provider Name</label>
                <div class="position-relative">
                    <input type="text" class="form-control" v-model="searchQuery" @input="searchDoctors(searchQuery)"
                        placeholder="Search for a provider..." autocomplete="off" />
                    <div v-if="filteredDoctors.length && searchQuery"
                        class="position-absolute w-100 mt-1 shadow bg-white rounded z-index-dropdown doctor-dropdown">
                        <div v-for="doctor in filteredDoctors" :key="doctor.id"
                            class="p-2 cursor-pointer hover-bg-light" @click="onDoctorSelect(doctor)">
                            <div class="fw-bold">{{ doctor.name }}</div>
                            <div class="small text-muted">{{ doctor.email }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <BaseInput v-model="form.email" type="email" label="Email" placeholder="Enter provider email"
                    :error="form.errors.email" required readonly />
            </div>

            <div class="mb-3">
                <BaseInput v-model="form.sms" type="number" label="SMS" placeholder="Enter mobile number"
                    :error="form.errors.sms" readonly />
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

.cursor-pointer {
    cursor: pointer;
}

.hover-bg-light:hover {
    background-color: #f8f9fa;
}
</style>