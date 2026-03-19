<script setup>
 import ExportPatientDetails from "./Patient/ExportPatientDetails.vue";
 import Profile from "./Profile.vue";
import Notification from "./Notification.vue";

const props = defineProps({
    toggleMenu: Function,
    toggleMobileMenu: Function,
    windowWidth: Number,
    isToggled: Boolean,
    isMobileView: Boolean,
});
</script>

<template>
    <!-- Desktop view -->
    <div class="row align-items-center" v-if="windowWidth >= 1100">
        <aside class="col-6">
            <ExportPatientDetails />
        </aside>
        <aside class="col-5 d-flex justify-content-end align-items-center gap-3">
            <!-- Bell always first -->
            <Notification />
            <!-- Profile always after -->
            <div class="profile-wrapper">
                 <Profile :user="$page.props.auth?.user" />
            </div>
        </aside>
    </div>

    <!-- Mobile view -->
    <div class="row align-items-center" v-else>
        <aside class="col-6" v-if="isMobileView">
            <button
                type="button"
                class="btn btn-primary btn-md expander"
                @click="toggleMobileMenu"
            >
                <i class="bi bi-list"></i>
            </button>
        </aside>
        <aside
            class="col-6 col-md-12 d-flex justify-content-end align-items-center gap-3"
        >
            <Notification />
            <Profile :user="$page.props.auth?.user" class="order-2" />
        </aside>
        <aside class="col-md-11 col-12 mt-4">
            <ExportPatientDetails />
        </aside>
    </div>
</template>

<style scoped>
.expander {
    font-size: 1.5em;
    height: 60px;
    width: 60px;
}
.profile-wrapper {
  display: flex;
  align-items: center;
}
@media (min-width: 768px) {
    #sidebar-wrapper {
        margin-top: 20px;
        left: 250px;
    }
}
</style>
