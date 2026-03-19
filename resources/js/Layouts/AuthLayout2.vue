<script setup>
import { computed, ref, watch } from "vue";
import { Head, } from "@inertiajs/vue3";
import AuthTopBar from "@/Partials/AuthTopBar.vue";
 
import MobileSidebar from "../Components/Sidebar/MobileSidebar.vue";
import Sidebar from "../Components/Sidebar/Sidebar.vue";
import { usePage } from "@inertiajs/vue3";

const props = defineProps({
    title: String,
    description: String,
    heading: String,
    flash: Object,
    subscription: Object,
    subscriptionPlan: Object,
});

const isToggled = ref(false);
const isMobileMenuOpen = ref(false);
const windowWidth = ref(window.innerWidth);
const activeItem = ref(null);
 
const page = usePage();
 
const isMobileView = computed(() => windowWidth.value < 1300);

const toggleMenu = (index) => {
    activeItem.value = activeItem.value === index ? null : index;
     // isToggled.value = !isToggled.value;
    if (isMobileView.value) {
        isMobileMenuOpen.value = !isMobileMenuOpen.value;
    }
};
  
const notification = (flash) => {
    
    if (flash.success) {
    toast(flash.success, "success");
    flash.success = null;

  }

  if (flash.error) {
    toast(flash.error, "error");
    flash.error = null;

  }
    if (flash.warning) {
        toast(flash.warning, "warning");
        flash.warning = null;
    
    }
};
/* ---------------- AUTH ---------------- */
const role = computed(() => page.props?.auth?.user?.roles?.[0]?.name);

</script>
<template>
    <div class="wrapper">
        <Head>
                <title>{{ title }}</title>
            <meta name="description" :content="description" />
        </Head>

        <!-- Sidebar -->
        <MobileSidebar
            v-if="isMobileView"
            :toggleMobileMenu="toggleMenu"
            :isMobileMenuOpen="isMobileMenuOpen"
        />
         <Sidebar
            v-else
            :isToggled="isToggled"
            :activeItem="activeItem"

        />

<!-- Header -->
        <AuthTopBar
            :isMobileView="isMobileView"
            :toggleMobileMenu="toggleMenu"
        />

        <div id="content-page" class="content-page">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12 px-0">
                        {{ notification($page.props.flash) }}

                        <main>
                            <slot />
                        </main>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
