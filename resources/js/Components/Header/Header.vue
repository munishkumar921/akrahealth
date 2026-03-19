<script setup>
import { usePage, router } from "@inertiajs/vue3";
import { computed } from "vue";
 import AdminHeader from "./AdminHeader.vue";
import DoctorHeader from "./DoctorHeader.vue";

const props = defineProps({
    toggleMenu: Function,
    toggleMobileMenu: Function,
    windowWidth: Number,
    isToggled: Boolean,
    isMobileView: Boolean,
    role_id: Number,
});

const page = usePage();

// Calculate effective role considering switched_role
const effectiveRole = computed(() => {
    const user = page.props?.auth?.user;
    if (user?.roles) {
        // Check if user has Admin role (even if switched)
        const hasAdmin = user.roles.some(r => r.name === 'Admin' || r.name === 'SuperAdmin');
        if (hasAdmin && page.props?.switched_role === 'Doctor') {
            return 'Doctor';
        }
    }
    // Return the actual role name
    return user?.roles?.[0]?.name || '';
});
</script>

<template>
    <nav
        class="bg-white header rounded p-4 container-fluid mb-4 shadow-lg"
        id="header"
    >
        <div class="row">
                <AdminHeader
                    :toggleMenu="toggleMenu"
                    :windowWidth="windowWidth"
                    :isToggled="isToggled"
                    :isMobileView="isMobileView"
                    :toggleMobileMenu="toggleMenu"
                    v-if="effectiveRole !== 'Doctor'"
                />
                <DoctorHeader
                    :toggleMenu="toggleMenu"
                    :windowWidth="windowWidth"
                    :isToggled="isToggled"
                    :isMobileView="isMobileView"
                    :toggleMobileMenu="toggleMenu"
                    v-else
                />
         </div>
    </nav>
    <!-- <ExportPatientDetails v-if="role_id === 1" /> -->
</template>

<style scoped>
.header {
    padding: 10px;
}

.expander {
    font-size: 1.5em;
    height: 60px;
    width: 60px;
}

@media (min-width: 768px) {
    #sidebar-wrapper {
        margin-top: 20px;
        left: 250px;
    }
}
</style>
