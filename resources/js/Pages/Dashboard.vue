<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import SAdminDashboard from '@/Pages/SAdmin/Dashboard.vue';
import AdminDashboard from '@/Pages/Admin/Dashboard.vue';
import DoctorDashboard from '@/Pages/Doctors/Dashboard.vue';
import PatientDashboard from '@/Pages/Patients/Dashboard.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const dashboardComponent = computed(() => {
    const role = user.value.role_name;
    if (role === 'SuperAdmin') {
        return SAdminDashboard;
    }
    else if (role === 'Admin') {
        return AdminDashboard;
    }else if (role === 'Doctor' || role === 'Virtual Assistant' || role === 'Laboratory') {
        return DoctorDashboard;
    } else if (role === 'Patient') {
        return PatientDashboard;
    }
    return null; 
});
</script>

<template>
    <div>
        <component :is="dashboardComponent" />
    </div>
</template>
