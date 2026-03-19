<script setup>
import { Link } from "@inertiajs/vue3";
import AdminMenu from "@/Partials/AdminMenu.vue";
import DoctorMenu from "@/Partials/DoctorMenu.vue";
import PatientMenu from "@/Partials/PatientMenu.vue";
import { onMounted, onBeforeUnmount } from "vue";

const props = defineProps({
    role_id: {
        type: Number,
        required: true,
    },
});

const handleClickOutside = (event) => {
    const sidebar = document.getElementById("app-sidebar");
    if (sidebar && !sidebar.contains(event.target)) {
        // isSidebarOpen.value = false;
        document.getElementById('closeSidebar').click()
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>

<template>
    <div class="iq-sidebar" id="app-sidebar">
        <div class="iq-sidebar-logo d-flex justify-content-between">
            <Link :href="route('home')" class="logo">
                <div class="iq-light-logo">
                    <img src="/images/logo.webp" class="img-fluid" alt="logo" />
                </div>
                <span>Akrahealth</span>
            </Link>
            <div class="iq-menu-bt-sidebar">
                <div class="iq-menu-bt align-self-center">
                    <div class="wrapper-menu">
                        <div class="hover-circle">
                            <i class="ri-arrow-left-s-line"></i>
                        </div>
                        <div class="main-circle">
                            <i class="ri-arrow-right-s-line" id="closeSidebar"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="sidebar-scrollbar">
            <div class="p-2">
                <AdminMenu v-if="role_id == 1 || role_id == 2" />
                <DoctorMenu v-if="role_id == 3" />
                <PatientMenu v-if="role_id == 4" />
            </div>
        </div>
    </div>
</template>
