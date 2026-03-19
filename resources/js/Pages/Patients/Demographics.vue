<script setup>
import { ref, computed } from "vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import DemographicsModal from "@/Pages/Modals/DemographicsModal.vue";
import Modal from '@/Components/Common/Modal.vue';
import axios from "axios";
import Swal from "sweetalert2";

const props = defineProps({
    patient: Object,
     keyword: String,
 });

const showEditModal = ref(false);
  
const calculateAge = (dob) => {
    if (!dob) return null;
    const birthDate = new Date(dob);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
};
 
const toggleEdit = () => {
    showEditModal.value = !showEditModal.value;
};
 

const handleCloseModal = () => {
    showEditModal.value = false;
};

const processing = ref({});
 
// ...existing code...
const getMaritalStatus = computed(() => {
    const status = props.patient?.marital_status;
    if (!status) return 'Not specified';
    const statusMap = {
        1: 'Single',
        2: 'Married',
        3: 'Divorced',
        4: 'Widowed'
    };
    return statusMap[status] || 'Not specified';
});
 </script>
<template>
    <AuthLayout title="Demographics" description="Patient demographic information" heading="">
         <!-- Name and Identity Section -->
        <div class="iq-card mb-4">
            <div class="iq-card-header d-flex justify-content-between align-items-center bg-primary text-white">
                <h5 class="mb-0 text-white">Name and Identity</h5>
                <button class="btn btn-light btn-sm" @click="toggleEdit">
                    Edit
                </button>
            </div>
            <div class="iq-card-body" style="border: 1.5px solid var(--iq-light-border)">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-5 col-sm-4  "><strong>First Name :</strong></div>
                            <div class="col-7 col-sm-8">{{patient?.first_name }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-5 col-sm-4  "><strong>Last Name :</strong></div>
                            <div class="col-7 col-sm-8">{{ patient?.last_name }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-5 col-sm-4  "><strong>Date of Birth :</strong></div>
                            <div class="col-7 col-sm-8">
                                {{patient?.dob}}
                                <span v-if="patient?.dob" class="text-muted ml-1">({{ calculateAge(patient?.dob) }} year old)</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-5 col-sm-4  "><strong>Gender :</strong></div>
                            <div class="col-7 col-sm-8">{{ patient?.sex || 'Not specified' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-5 col-sm-4  "><strong>Marital Status :</strong></div>
                            <div class="col-7 col-sm-8">{{ getMaritalStatus }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-5 col-sm-4  "><strong>Ethnicity : </strong></div>
                            <div class="col-7 col-sm-8">{{ patient?.ethnicity || 'Not specified' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contacts Section -->
        <div class="iq-card mb-4">
             <div class="iq-card-header d-flex justify-content-between align-items-center bg-primary text-white">
                <h5 class="mb-0 text-white">Contacts</h5>
                <button class="btn btn-light btn-sm" @click="toggleEdit">
                    Edit
                </button>
            </div>
            <div class="iq-card-body" style="border: 1.5px solid var(--iq-light-border)">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-5 col-sm-4"><strong>Address :</strong></div>
                            <div class="col-7 col-sm-8">{{ patient?.address_1 || 'Not specified' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-5 col-sm-4  "><strong>City :</strong></div>
                            <div class="col-7 col-sm-8">{{ patient?.city || 'Not specified' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-5 col-sm-4  "><strong>State :</strong></div>
                            <div class="col-7 col-sm-8">{{ patient?.state || 'Not specified' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-5 col-sm-4  "><strong>ZIP :</strong></div>
                            <div class="col-7 col-sm-8">{{ patient?.zip || 'Not specified' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-5 col-sm-4  "><strong>Email :</strong></div>
                            <div class="col-7 col-sm-8">{{ patient?.email || 'Not specified' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-5 col-sm-4  "><strong>Home Phone :</strong></div>
                            <div class="col-7 col-sm-8">{{ patient?.mobile || 'Not specified' }}</div>
                        </div>
                    </div>
                </div>
            </div>
         </div>

        <!-- Profile Photo Section -->
        <div class="iq-card mb-4">
            <div class="iq-card-header d-flex justify-content-between align-items-center bg-primary text-white">
                <h5 class="mb-0 text-white">Profile Photo</h5>
                <button class="btn btn-light btn-sm" @click="toggleEdit">
                    Edit
                </button>
            </div>
             <div class="iq-card-body text-center" style="border: 1.5px solid var(--iq-light-border); cursor: pointer;" @click="toggleEdit">
                <img :src="patient?.photo ? '/storage/' + patient.photo : '/images/default-avatar.png'" alt="Profile Photo" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                <p class="mt-2 text-muted">Click to upload a new profile photo</p>
            </div>
        </div>
        <!-- Contacts Section -->
        <Modal :isOpen="showEditModal" title="Edit Demographics" @close="handleCloseModal" size="xl">
        <DemographicsModal :patient="patient"   :onClose="handleCloseModal" />
        </Modal>
     </AuthLayout>
</template>
