<script setup>
import { Head, Link, usePage } from "@inertiajs/vue3";
import { pharmacyNavItems } from "../../Data/MenuItems";
import Logo from "../Logo.vue";

const props = defineProps({
    isMobileView: Boolean,
});

// new: try to read counts from Inertia page props (supports several common names)
const page = usePage();
const counts = page.props.value?.counts || {};

// helper to get a count for a nav item (tries key, route, or normalized label)
const getCount = (nav) => {
    if (!counts) return 0;
    if (nav.key && counts[nav.key] !== undefined) return counts[nav.key];
    if (nav.route && counts[nav.route] !== undefined) return counts[nav.route];
    const normalized = (nav.label || '').toLowerCase().replace(/\s+/g, '_');
    return counts[normalized] !== undefined ? counts[normalized] : 0;
};
</script>

<template>
    <div class="iq-sidebar" v-if="!isMobileView">
        <div class="iq-sidebar-logo d-flex justify-content-between">
            <Link :href="route('home')" class="logo">
                <Logo :hideTitle="true" />
            </Link>
        </div>
        <div id="sidebar-scrollbar">
            <div class="p-2">
                <nav class="iq-sidebar-menu">
                    <ul id="iq-sidebar-toggle" class="iq-menu">
                        <li
                            :class="`${
                                route().current(`${nav.route}`) ? 'active' : ''
                            }`"
                            v-for="nav in pharmacyNavItems"
                            :key="nav.label">
                            <Link
                                :href="route(`${nav.route}`)"
                                class="iq-waves-effect"
                            >
                                <i :class="nav.icon"></i>
                                <span>{{ nav.label }}</span>
                                <!-- show count badge when > 0 -->
                                <span v-if="getCount(nav) > 0" class="badge bg-primary ms-2">
                                    {{ getCount(nav) }}
                                </span>
                             </Link>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</template>