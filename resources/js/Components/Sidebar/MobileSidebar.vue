<script setup>
import { computed, ref, watchEffect } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { getNavItemsByRole } from "../../Data/MenuItems";
import Logo from "../Logo.vue";

const props = defineProps({
    isMobileMenuOpen: Boolean,
    toggleMobileMenu: Function,
});

const role = usePage().props?.auth?.switched_role || usePage().props?.auth?.user?.roles?.[0]?.name;
const getIconSize = (isMobileMenuOpen) => (isMobileMenuOpen ? "18px" : "15px");
const navItems = computed(() => getNavItemsByRole(role));
const expandedSections = ref({});

watchEffect(() => {
    navItems.value.forEach((item) => {
        if (item.isCollapsible && !(item.label in expandedSections.value)) {
            expandedSections.value[item.label] = true;
        }
    });
});

const toggleSection = (label) => {
    expandedSections.value[label] = !expandedSections.value[label];
};
</script>

<template>
    <div>
        <div class="overlay" :class="{ active: isMobileMenuOpen }" @click="toggleMobileMenu"></div>

        <div :class="['sidebar', { active: isMobileMenuOpen }]">
            <div class="dismiss" @click="toggleMobileMenu">
                <span v-if="isMobileMenuOpen"> &times; </span>
            </div>
            <div id="sidebar-wrapper" class="bg-light border-right shadow-lg">
                <div class="sidebar-header d-flex justify-content-center align-items-center mb-4 mt-4">
                    <Logo :hideTitle="isMobileMenuOpen" />
                </div>
                <!-- <hr class="w-100 text-primary mb-4" /> -->
                <ul class="list-unstyled ps-3 overflow-auto no-scrollbar pr-2" style="height: calc(95% - 80px)">
                    <li v-for="(item, index) in navItems" :key="item.route || item.label">
                        <template v-if="item.isCollapsible">
                            <div class="d-block py-2 px-3 rounded link-hover d-flex align-items-center mb-3 cursor-pointer text-dark"
                                @click="toggleSection(item.label)">
                                <i :class="item.icon" :style="{
                                    fontSize: getIconSize(isToggled),
                                }" aria-hidden="true"></i>
                                <span class="ms-2" v-if="!isToggled">{{
                                    item.label
                                    }}</span>
                                <i :class="expandedSections[item.label]
                                        ? 'fa-solid fa-chevron-up'
                                        : 'fa-solid fa-chevron-down'
                                    " class="ms-auto"></i>
                            </div>
                            <transition name="expand-collapse">
                                <ul v-show="expandedSections[item.label]" class="list-unstyled ms-4">
                                    <li v-for="(subItem, index) in item.items" :key="subItem.route">
                                        <Link :href="route(subItem.route)" :class="{
                                            'active text-white bg-primary':
                                                route().current(
                                                    subItem.route
                                                ),
                                            'text-dark': !route().current(
                                                subItem.route
                                            ),
                                        }"
                                            class="d-block py-2 px-3 rounded link-hover d-flex align-items-center mb-2">
                                        <i :class="subItem.icon" :style="{
                                            fontSize:
                                                getIconSize(isToggled),
                                        }" aria-hidden="true"></i>
                                        <span class="ms-2" v-if="!isToggled">{{ subItem.label }}</span>
                                        </Link>
                                    </li>
                                </ul>
                            </transition>
                        </template>
                        <template v-else>
                            <Link :href="route(item.route)" :class="{
                                'active text-white bg-primary':
                                    route().current(item.route),
                                'text-dark': !route().current(item.route),
                            }" class="d-block py-2 px-3 rounded link-hover d-flex align-items-center mb-3">
                            <i :class="item.icon" :style="{
                                fontSize: getIconSize(isToggled),
                            }" aria-hidden="true"></i>
                            <span class="ms-2" v-if="!isToggled">{{
                                item.label
                            }}</span>
                            </Link>
                        </template>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<style scoped>
.overlay {
    display: none;
    position: fixed;
    margin-left: -20px;
    width: 1000vw;
    height: 100vh;
    background: rgba(51, 51, 51, 0.7);
    z-index: 998;
    opacity: 0;
    transition: all 0.5s ease-in-out;
}

.overlay.active {
    display: block;
    opacity: 1;
}

.ms-4 {
    margin-left: 1.5rem;
}

.expand-collapse-enter-active,
.expand-collapse-leave-active {
    transition: height 0.3s ease, opacity 0.3s ease;
}

.expand-collapse-enter-from,
.expand-collapse-leave-to {
    height: 0;
    opacity: 0;
    overflow: hidden;
}

.expand-collapse-enter-to,
.expand-collapse-leave-from {
    height: auto;
    opacity: 1;
}

.sidebar-header {
    height: 80px;
}

.overflow-auto {
    overflow-y: auto;
    height: calc(100% - 80px);
}

.link-hover {
    transition: background-color 0.3s, color 0.3s, box-shadow 0.3s;
}

.link-hover:hover {
    background-color: #09acff !important;
    color: white !important;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.active {
    font-weight: bold;
}

.sidebar {
    width: 280px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: -255px;
    z-index: 999;
    transition: all 0.3s;
    text-align: left;
    margin-left: -30px;
}

.sidebar.active {
    left: 0;
    margin-left: 0;
}

.dismiss {
    width: 35px;
    height: 35px;
    position: absolute;
    top: 10px;
    right: 1px;
    background: #09acff;
    border-radius: 40px;
    text-align: center;
    line-height: 35px;
    cursor: pointer;
    color: #fff;
}

#sidebar-wrapper {
    margin: 20px;
    z-index: 1000;
    left: 250px;
    width: 250px;
    height: 95%;
    overflow: hidden;
    transition: all 0.5s ease;
    background-color: #ffffff !important;
    border-radius: 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}
</style>
