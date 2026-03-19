<script setup>
import { computed, watch, onMounted, ref } from "vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import AuthTopBar from "@/Partials/AuthTopBar.vue";
import MobileSidebar from "../Components/Sidebar/MobileSidebar.vue";
import Sidebar from "../Components/Sidebar/Sidebar.vue";
import { useSubscriptionDaysLeft } from "@/Composables/useSubscription";

const { createExpiryInfo } = useSubscriptionDaysLeft();

const props = defineProps({
    title: String,
    description: String,
    heading: String,
    flash: Object,
});

const page = usePage();

const isToggled = ref(false);
const isMobileMenuOpen = ref(false);
const windowWidth = ref(window.innerWidth);
const activeItem = ref(null);

const isMobileView = computed(() => windowWidth.value < 1300);

/* ---------------- MENU ---------------- */
const toggleMenu = (index) => {
    activeItem.value = activeItem.value === index ? null : index;
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


/* ---------------- SUBSCRIPTION ---------------- */
const currentSubscription = ref({});
const subscriptionPlan = ref({});
onMounted(() => {
    axios.get(route('admin.subscription.notify'))
        .then(response => {
            currentSubscription.value = response.data.subscription || {};
            subscriptionPlan.value = response.data.subscriptionPlan || {};
        })
        .catch(error => {
            console.error('Error fetching navigation counts:', error);
        });
})

const subscriptionExpiry = computed(() => {
    const sub = currentSubscription.value;
    if (!sub?.end_date) return null;

    return createExpiryInfo(sub.end_date);
});

const displayPlanName = computed(() => {
    const plan = subscriptionPlan.value || currentSubscription.value?.subscription_plan || currentSubscription.value?.plan;
    return plan?.title || plan?.name || "";
});

const subscriptionClass = computed(() => {
    if (!subscriptionExpiry.value) return 'd-none';

    if (subscriptionExpiry.value.isExpired) {
        return 'alert-danger';
    }

    if (subscriptionExpiry.value.isExpiringSoon) {
        return 'alert-warning';
    }

    return 'alert-success';
});

/* ---------------- ROLE SWITCH ---------------- */
const isViewingAsAdmin = computed(() => {
    return page.props?.switched_role !== 'Doctor';
});

</script>
<template>
    <div class="wrapper">

        <Head>
            <title>{{ title }}</title>
            <meta name="description" :content="description" />
        </Head>

        <MobileSidebar v-if="isMobileView" :toggleMobileMenu="toggleMenu" :isMobileMenuOpen="isMobileMenuOpen"
            :role="role" />

        <Sidebar v-else :isToggled="isToggled" :role="role" :activeItem="activeItem" />

        <AuthTopBar :isMobileView="isMobileView" :toggleMobileMenu="toggleMenu" />

        <div id="content-page" class="content-page">
            <div class="container-fluid">
                <div v-if="isViewingAsAdmin && subscriptionExpiry" class="alert alert-dismissible mb-4"
                    :class="subscriptionClass" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div>
                            <template v-if="subscriptionExpiry.isExpired">
                                <strong>Expired!</strong>
                                Your {{ displayPlanName }} subscription has expired. Please renew to continue using all
                                features.
                            </template>
                            <template v-else-if="subscriptionExpiry.isExpiringSoon">
                                <strong>Warning!</strong>
                                Your {{ displayPlanName }} subscription expires in {{ subscriptionExpiry.daysLeft }}
                                days. Please renew to continue using all features.
                            </template>
                            <template v-else>
                                <strong>Success!</strong>
                                Your {{ displayPlanName }} subscription is active and expires in {{
                                    subscriptionExpiry.daysLeft }} days.
                            </template>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-12 px-0">
                        {{ notification($page.props.flash) }}

                        <main class="mt-2 iq-card p-4">
                            <slot />
                        </main>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.iq-card {
    min-height: 81vh !important;
}
</style>
