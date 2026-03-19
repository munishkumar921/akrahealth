<script setup>
import DoctorNavOptions from "./NavOptions/DoctorNavOptions.vue";
import PatientSelector from "./Patient/PatientSelector.vue";
import Profile from "./Profile.vue";

const props = defineProps({
    toggleMenu: Function,
    toggleMobileMenu: Function,
    windowWidth: Number,
    isToggled: Boolean,
    isMobileView: Boolean,
});
</script>

<template>
     <div class="row align-items-center" v-if="windowWidth >= 1100">
        <aside class="col-3 d-flex justify-content-start align-items-center">
            <PatientSelector :selectedPatient="$page.props.selected_patient" />
        </aside>
        <aside class="col-7 d-flex justify-content-center">
            <DoctorNavOptions />
        </aside>
        <aside class="col-2 d-flex justify-content-end">
            <Profile :user="$page.props.auth?.user" />
        </aside>
    </div>
    <div class="row align-items-center" v-else>
        <aside class="col-2" v-if="isMobileView">
            <button
                type="button"
                class="btn btn-primary btn-md expander"
                @click="toggleMobileMenu"
            >
                <i class="bi bi-list"></i>
            </button>
        </aside>
        <aside
            class="col-10 col-md-12 d-md-flex align-items-center justify-content-center"
        >
             <PatientSelector :selectedPatient="$page.props.selected_patient" />
        </aside>
        <aside class="col-12 mt-4 d-flex justify-content-center">
            <DoctorNavOptions :windowWidth="windowWidth" />
        </aside>
    </div>
</template>

<style scoped>
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
