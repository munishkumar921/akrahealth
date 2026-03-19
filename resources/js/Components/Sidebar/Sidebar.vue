<script setup>
import { computed, ref, watch, onMounted } from "vue";
import { Link } from "@inertiajs/vue3";
import { getNavItemsByRole } from "../../Data/MenuItems";
import Logo from "../Logo.vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";

const props = defineProps({
    isToggled: Boolean,
    role: String,
});

const role = usePage().props?.switched_role || usePage().props?.auth?.user?.roles?.[0]?.name;
const navItems = computed(() => getNavItemsByRole(role));
const expandedSections = ref({});

watch(navItems, (newItems) => {
    newItems.forEach((item) => {
        if (item.isCollapsible) {
            expandedSections.value[item.label] = item.items.some(subItem => route().current(subItem.route));
        }
    });
}, { immediate: true });


const toggleSection = (label) => {
    expandedSections.value[label] = !expandedSections.value[label];
};

const counts = ref({});
onMounted(() => {
    axios.get(route('navigation.counts'))
        .then(response => {
            counts.value = response.data || {};
        })
        .catch(error => {
            console.error('Error fetching navigation counts:', error);
        });
})

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
const formatDob = (dob) => {
    if (!dob) return '';
    return new Date(dob).toLocaleDateString('en-US', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const colors = ['#008FFB', '#00E396', '#FEB019', '#1be1b3', '#775DD0', '#800080', '#06C270'];

const getIconColor = (index) => {
    return colors[index % colors.length];
};

onMounted(() => {
    if (!window.axiosInterceptorSet) {
        window.axiosInterceptorSet = true;
        axios.interceptors.response.use(
            (response) => response,
            (error) => {
                if (error.response && (error.response.status === 401 || error.response.status === 419)) {
                    window.location.href = '/login';
                }
                return Promise.reject(error);
            }
        );
    }
});
</script>

<template>
    <div class="iq-sidebar">
        <div class="iq-sidebar-logo d-flex justify-content-between">
            <Link :href="route('home')" class="logo">
                <Logo :hideTitle="!isToggled" />
            </Link>

            <div class="iq-menu-bt-sidebar">
                <div class="iq-menu-bt align-self-center">
                    <div class="wrapper-menu">
                        <div class="main-circle"><i class="ri-arrow-left-s-line"></i></div>
                        <div class="hover-circle"><i class="ri-arrow-right-s-line"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div id="sidebar-scrollbar">
            <nav class="iq-sidebar-menu">
                <ul id="iq-sidebar-toggle" class="iq-menu">
                    <div class="col-md-12 patient-info mb-3 iq-card bg-color-white-lilac p-2 align-items-center"
                        v-if="role === 'Patient' || $page?.props?.selected_patient">
                        <Link :href="$page?.props?.selected_patient
                            ? route('doctor.patient.history')
                            : route('patient.history')" class="iq-waves-effect text-center align-items-center"
                            data-tooltip="Patient History" data-tooltip-location="bottom">
                            <div>
                                <h6 class="mb-1 text-dark" data-tooltip="Patient Summary">
                                    <b>
                                        {{
                                            $page?.props?.selected_patient?.name
                                            ?? $page?.props?.auth?.user?.patient?.name
                                            ?? $page?.props?.auth?.user?.name
                                        }}
                                    </b>
                                </h6>

                                <div class="mb-1">
                                    <strong>DOB:</strong>
                                    {{
                                        $page?.props?.selected_patient?.dob
                                            ? formatDob($page.props.selected_patient.dob)
                                            : $page?.props?.auth?.user?.patient?.dob
                                                ? formatDob($page.props.auth.user.patient.dob)
                                                : 'N/A'
                                    }}
                                </div>

                                <div class="mb-1">
                                    <strong>Age:</strong>
                                    {{
                                        calculateAge(
                                            $page?.props?.selected_patient?.dob
                                            ?? $page?.props?.auth?.user?.patient?.dob
                                        )
                                    }} year old
                                </div>

                                <div class="mb-0">
                                    <strong>Gender:</strong>
                                    {{
                                        $page?.props?.selected_patient?.sex
                                        ?? $page?.props?.auth?.user?.patient?.sex
                                        ?? 'N/A'
                                    }}
                                </div>
                            </div>
                        </Link>
                    </div>

                    <template v-for="(item, index) in navItems" :key="item.route || item.label">
                        <li v-if="item.isCollapsible">
                            <template v-if="$page?.props?.selected_patient">
                                <a class="iq-waves-effect collapsed" @click="toggleSection(item.label)"
                                    :aria-expanded="expandedSections[item.label]" data-toggle="collapse"
                                    aria-expanded="false">
                                    <i :class="item.icon" aria-hidden="true"
                                        :style="{ color: item.color || getIconColor(index) }"></i>
                                    <span>{{ item.label }}</span>
                                    <i class="ri-arrow-right-s-line iq-arrow-right"></i>
                                </a>
                                <ul v-show="expandedSections[item.label]" role="menu" aria-label="submenu"
                                    class="iq-submenu">
                                    <li v-for="subItem in item.items" :key="subItem.route"
                                        :class="{ 'active': route().current(subItem.route) }">
                                        <Link :href="route(subItem.route)">
                                            <i :class="[subItem.icon, 'ml-3']" aria-hidden="true"
                                                :style="{ color: subItem.color || getIconColor(index) }"></i>
                                            <span>{{ subItem.label }}</span>
                                            <span v-if="counts[subItem.label]" class="badge bg-primary ms-2"
                                                style="min-width: 15px;">
                                                {{ counts[subItem.label] }}
                                            </span>
                                        </Link>
                                    </li>
                                </ul>
                            </template>
                            <template v-else>
                                <p v-if="index === 1 && role == 'Doctor'" class="mt-5 font-bold text-base text-center">
                                    Search and select a patient <br /> to continue</p>
                            </template>
                        </li>
                        <li v-if="item.isCollapsible">
                            <template
                                v-if="role === 'SuperAdmin' || role === 'Admin' || role === 'Patient' || role === 'Lab' || role === 'Pharmacy' || role === 'Biller' || role === 'Virtual Assistant'">
                                <a class="iq-waves-effect collapsed" @click="toggleSection(item.label)"
                                    :aria-expanded="expandedSections[item.label]" data-toggle="collapse"
                                    aria-expanded="false">
                                    <i :class="item.icon" aria-hidden="true"
                                        :style="{ color: item.color || getIconColor(index) }"></i>
                                    <span>{{ item.label }}</span>

                                    <i class="ri-arrow-right-s-line iq-arrow-right"></i>
                                </a>
                                <ul v-show="expandedSections[item.label]" role="menu" aria-label="submenu"
                                    class="iq-submenu">
                                    <li v-for="subItem in item.items" :key="subItem.route"
                                        :class="{ 'active': route().current(subItem.route) }">
                                        <Link :href="route(subItem.route)">
                                            <i :class="[subItem.icon, 'ml-3']" aria-hidden="true"
                                                :style="{ color: subItem.color || getIconColor(index) }"></i>
                                            <span>{{ subItem.label }}</span>
                                            <span v-if="counts[subItem.label]" class="badge bg-primary ms-2"
                                                style="min-width: 15px;">
                                                {{ counts[subItem.label] }}
                                            </span>
                                        </Link>
                                    </li>
                                </ul>
                            </template>
                        </li>

                        <li v-else :class="{ 'active': route().current(item.route) }">
                            <template
                                v-if="$page?.props?.selected_patient || item.label === 'Dashboard' || item.label === 'All Patients' || role === 'Patient' || role === 'SuperAdmin' || role === 'Admin' || role === 'Lab' || role === 'Pharmacy' || role === 'Biller' || role === 'Virtual Assistant'">
                                <Link :href="route(item.route)" class="iq-waves-effect">
                                    <i :class="item.icon" aria-hidden="true"
                                        :style="{ color: item.color || getIconColor(index) }"></i>
                                    <span>{{ item.label }}</span>
                                </Link>
                            </template>
                        </li>
                    </template>
                </ul>
            </nav>
            <div class="p-3"></div>
        </div>
    </div>
</template>
