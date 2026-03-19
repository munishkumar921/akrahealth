<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from "vue";
import AdditionalOptions from "./AdditionalOptions.vue";
import PatientSummary from "@/Pages/Common/PatientSummary.vue";
import { configOptions, assistantNavOptions, officeOptions, patientNavOptions } from "../options";
import Modal from "@/Components/Common/Modal.vue";
import { router, usePage } from "@inertiajs/vue3";
import "../header.css";
import Profile from "../Profile.vue";
import axios from "axios";
import AutoComplete from "primevue/autocomplete";
import PatientSelector from "../Patient/PatientSelector.vue";

/* ---------------- PROPS ---------------- */
const props = defineProps({
    windowWidth: Number,
    visible: {
        type: Boolean,
        default: true,
    },
});

/* ---------------- STATE ---------------- */
const showAdditionalOptions = ref({
    office: false,
    configure: false,
});

const showPatientSummary = ref(false);
const isLoadingPatientData = ref(false);

// Search state
const patients = ref([]);
const selectedPatient = ref(null);
const filteredPatients = ref([]);

const page = usePage();
const user = page.props.auth?.user;
const role = user?.roles?.[0]?.name;

// Check if user is in virtual assistant mode
const isVirtualAssistantMode = computed(() => {
    return role === "Virtual Assistant";
});

const patientData = ref(
    page.props.patient || page.props.selected_patient || {}
);

/* ---------------- NAV OPTIONS ---------------- */
const currentNavOptions = computed(() => {
    if (!user) return [];
    if (isVirtualAssistantMode.value) return assistantNavOptions;
    if (role === "Patient") return patientNavOptions;
    return [];
});

/* ---------------- METHODS ---------------- */
const toggleOptions = (label, path) => {
    showAdditionalOptions.value = { office: false, configure: false };

    if (label === "Office") {
        showAdditionalOptions.value.office = true;
    } else if (label === "Configure") {
        showAdditionalOptions.value.configure = true;
    } else {
        router.visit(route(path));
    }
};

const handleCardClick = (icon) => {
    if (icon?.id === "patient-summary") {
        loadPatientSummary();
        return;
    }
    toggleOptions(icon.label, icon.path);
};

const loadPatientSummary = () => {
    isLoadingPatientData.value = true;

    axios
        .get(route("assistant.patient.summary"))
        .then((res) => {
            if (res?.data) patientData.value = res.data;
            showPatientSummary.value = true;
        })
        .catch(() => {
            showPatientSummary.value = true;
        })
        .finally(() => {
            isLoadingPatientData.value = false;
        });
};

const handleClickOutside = (event) => {
    const expandedOptions = document.querySelectorAll(".additional-options");
    if (![...expandedOptions].some((el) => el.contains(event.target))) {
        showAdditionalOptions.value = { office: false, configure: false };
    }
};

// Search methods
const search = (event) => {
    axios.get(route("assistant.search.patient", { q: event.query.toLowerCase() }))
        .then((res) => {
            patients.value = res.data;
            filteredPatients.value = res.data;
        });
};

const onPatientSelect = (event) => {
    router.get(route("assistant.select.patient", { id: event.value.id }), {}, {
        onSuccess: (response) => {
            router.reload({ only: ["auth", "flash"] });
         },
        onError: (errors) => {
            console.error("Patient selection failed:", errors);
        }
    });
};

/* ---------------- LIFECYCLE ---------------- */
onMounted(() => {
    document.addEventListener("click", handleClickOutside);

    if (isVirtualAssistantMode.value) {
        axios.get(route("assistant.patient.summary")).then((res) => {
            if (res?.data) patientData.value = res.data;
        });
    }
});

onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>

<template>
    <div v-if="visible" class="d-flex gap-4 align-items-center ml-2 doctor-nav">
        <!-- PATIENT SUMMARY -->
        <div
             v-if="$page.props.selected_patient && isVirtualAssistantMode"
            class="nav-card shadow-lg position-relative"
            role="button"
            tabindex="0"
            @click.stop="showPatientSummary = true"
        >
            <div class="nav-card-content" data-tooltip="Patient Summary">
                <i class="nav-card-icon fa fa-user"></i>
            </div>
        </div>

        <!-- NAV ICONS -->
        <div
            v-for="icon in currentNavOptions"
            :key="icon.id"
            class="nav-card shadow-lg position-relative"
            role="button"
            tabindex="0"
            @click.stop="handleCardClick(icon)"
        >
            <div
                class="nav-card-content"
                :data-tooltip="icon.label"
                data-tooltip-location="bottom"
            >
                <i :class="['nav-card-icon', icon.id]"></i>
                <i
                    v-if="icon.label === 'Office' || icon.label === 'Configure'"
                    class="bi bi-caret-down-fill more-options"
                ></i>
            </div>

            <AdditionalOptions
                v-if="icon.label === 'Office'"
                :options="officeOptions"
                label="Office"
                :isVisible="showAdditionalOptions.office"
            />

            <AdditionalOptions
                v-if="icon.label === 'Configure'"
                :options="configOptions"
                label="Configure"
                :isVisible="showAdditionalOptions.configure"
            />
        </div>

        <Profile v-if="windowWidth <= 1100" :user="user" />
         <Modal :isOpen="showPatientSummary" title="Patient Summary" @close="showPatientSummary = false" size="xl">
          <PatientSummary
            :patient="patientData || {}"
         />
        </Modal>
         <!-- <PatientSummary
            :isOpen="showPatientSummary"
            title="Patient Summary"
            :patient="patientData || {}"
            @close="showPatientSummary = false"
        /> -->
    </div>
</template>

<style scoped>
.doctor-nav {
    display: flex;
    align-items: center;
    gap: 14px;
}
</style>