<script setup>
import { Head, Link, usePage } from "@inertiajs/vue3";
import { labNavItems } from "../../Data/MenuItems";
import Logo from "../Logo.vue";

const props = defineProps({
    isMobileView: Boolean,
});
 
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
                        {{ labNavItems }}
                        <li
                            :class="`${
                                route().current(`${nav.route}`) ? 'active' : ''
                            }`"
                             v-for="nav in labNavItems"
                            :key="nav.label">
                            <Link
                                :href="route(`${nav.route}`)"
                                class="iq-waves-effect"
                            >
                                <i :class="nav.icon"></i>
                                <span>{{ nav.label }}</span>
                                <!-- show count badge when > 0 -->
 
                             </Link>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</template>