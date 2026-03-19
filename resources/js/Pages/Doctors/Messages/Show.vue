<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { computed } from 'vue';

const props = defineProps({
    message: {
        type: Object,
        required: true
    }
});


const goBack = () => {
    window.history.back();
};
</script>

<template>
    <AuthLayout title="View Message" description="View message details" heading="View Message">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Message Details</h5>
                <button class="btn btn-secondary" @click="goBack">
                    <i class="bi bi-arrow-left"></i> Back
                </button>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>Date:</strong>
                    </div>
                    <div class="col-md-9">
                        {{ message.date }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>Subject:</strong>
                    </div>
                    <div class="col-md-9">
                        {{ message.subject }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>Patient:</strong>
                    </div>
                    <div class="col-md-9">
                        {{ message.patient?.name || 'Unknown' }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>From:</strong>
                    </div>
                    <div class="col-md-9">
                        {{ message.doctor?.name || message.doctor?.user?.name  || 'N/A' }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>To:</strong>
                    </div>
                    <div class="col-md-9">
                        {{ message.patient?.user?.name || 'N/A' }}
                    </div>
                </div>
                 <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>Cc:</strong>
                    </div>
                    <div class="col-md-9">
                        {{ message.doctor?.name || message.doctor?.user?.name  || 'N/A' }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>Status:</strong>
                    </div>
                    <div class="col-md-9">
                        <span :class="['badge', message.read ? 'badge-success' : 'badge-warning']">
                            {{ message.read ? 'Read' : 'Unread' }}
                        </span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>Message:</strong>
                    </div>
                    <div class="col-md-9">
                        <div class="p-3 bg-light rounded">
                            {{ message.message }}
                        </div>
                    </div>
                </div>                
            </div>
        </div>
    </AuthLayout>
</template>

