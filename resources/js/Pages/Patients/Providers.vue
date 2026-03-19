<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import ShareDetailsModal from '@/Pages/Modals/ShareDetailsModal.vue';
import Table from '@/Components/Table/Table.vue';
import ActionButtons from '@/Components/Table/Partials/ActionButtons.vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

defineProps({
    connectedDoctors: Object,
    keyword: String
});

const showShareModal = ref(false);

const openShareModal = () => {
    showShareModal.value = true;
};

const closeShareModal = () => {
    showShareModal.value = false;
};

const removeDoctorAccess = (doctorId) => {
    Swal.fire( confirmSettings('Are you sure you want to remove this provider?')).then((result) => {
      router.delete(route('patient.remove.doctor.access', doctorId), {
        onSuccess: () => {
            // Refresh the page to update the list
            window.location.reload();
        },
        onError: (errors) => {
            console.error('Remove doctor access error:', errors);
            alert('Failed to remove doctor access. Please try again.');
        }
    });  
    })
    
};

const columns = [
    { label: 'Name', key: 'user.name' },
    { label: 'Specialities', key: 'specialities', formatter: (specialities) => specialities.map(s => s.name).join(', ') },
    { label: 'Email', key: 'user.email' },
    { label: 'Phone', key: 'user.mobile' },
];
const buttons = [
    {
        label: 'Invite Provider',
        icon: 'fa fa-refresh',
        function: () => {
            openShareModal();
        }
    }
]
</script>

<template>
    <AuthLayout title="Providers" description="Manage your connected healthcare providers">
        <div class="d-flex align-items-center justify-content-between">
            <h3 class="d-flex align-items-center">Connected Providers</h3>
            <ActionButtons :actionButtons="buttons" />
        </div>

        <Table :columns="columns" :data="connectedDoctors" :search="keyword">
            <template #actions="{ row }">
                <button class="btn btn-danger" @click="removeDoctorAccess(row.id)" data-tooltip="Remove Access">
                    <i class="fa fa-times"></i> Remove Access
                </button>
            </template>
        </Table>
        <!-- Share Details Modal -->
        <ShareDetailsModal v-if="showShareModal" :isOpen="showShareModal" @close="closeShareModal" />
    </AuthLayout>
</template>

<style scoped>
.card {
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
</style>
