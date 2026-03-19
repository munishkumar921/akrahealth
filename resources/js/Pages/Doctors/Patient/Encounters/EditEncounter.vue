<script setup>
import { useForm, router, usePage } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { ref, watch } from "vue";
import Tabs from "@/Components/Common/Tabs.vue";
import Modal from "@/Components/Common/Modal.vue";
import EncounterDetails from "./Partials/EncounterDetails.vue";
import Subjective from "./Partials/SOAP/Subjective.vue";
import Objective from "./Partials/SOAP/Objective.vue";
import Assessment from "./Partials/SOAP/Assessment.vue";
import Plan from "./Partials/SOAP/Plan.vue";
import OrderForm from "./Partials/OrderForm.vue";
import PrescriptionForm from "./Partials/PrescriptionForm.vue";
import Billing from "./Partials/Billing.vue";
import AIImportModal from "./Partials/AIImportModal.vue";
import EncounterSummary from "./Partials/EncounterSummary.vue";
import AddAddressModal from "@/Pages/Common/Configure/AddressBook/AddAddressModal.vue";
import "@vueform/multiselect/themes/default.css";
import Documents from "./Partials/Documents.vue";
import Referral from "./Partials/Referral.vue";
import TemplateSelector from '@/Pages/Common/template.vue';

const props = defineProps({
    data: Object,
});

const mainTab = ref("Details");
const isAIImportModalOpen = ref(false);
const isEncounterSummaryModalOpen = ref(false);
const isAddReferralProviderModalOpen = ref(false);

// Template management variables
const templateData = ref(null);
const templateCategory = ref('');
const templateField = ref('');

watch(mainTab, (newTab, oldTab) => {
    if (newTab !== oldTab) {
        templateData.value = null;
        templateCategory.value = '';
        templateField.value = '';
    }
});


const openAIModal = () => (isAIImportModalOpen.value = true);
const closeAIModal = () => (isAIImportModalOpen.value = false);
const importTranscript = () => {
    closeAIModal();
};

const mainTabs = ref([
    { label: "Details", value: "Details" },
    { label: "SOAP", value: "SOAP" },
    { label: "Prescription", value: "Prescription" },
    { label: "Order", value: "Order" },
    { label: "Documents", value: "Documents" },
    { label: "Billing", value: "Billing" },
    // { label: "Referral", value: "Referral" },
]);

const encounterForm = useForm({
    /* encounters */
    id: props.data?.encounter?.id ?? null,
    patient_id: props.data?.encounter?.patient_id||usePage().props?.selected_patient?.id,
    chief_complaint: props.data?.encounter?.chief_complaint,
    doctor_id: props.data?.encounter?.doctor_id,
    hospital_id: props.data?.encounter?.hospital_id,
    encounter_date_of_service: props.data?.encounter?.encounter_date_of_service,
    appointment_id: props.data?.encounter?.appointment_id,
    encounter_type: props.data?.encounter?.encounter_type,
    encounter_location: props.data?.encounter?.encounter_location,
    encounter_condition: props.data?.encounter?.encounter_condition,
    encounter_condition_work: props.data?.encounter?.encounter_condition_work,
    encounter_condition_auto: props.data?.encounter?.encounter_condition_auto,
    encounter_condition_auto_state: props.data?.encounter?.encounter_condition_auto_state,
    encounter_condition_other: props.data?.encounter?.encounter_condition_other,
    complexity_of_encounter: props.data?.encounter?.complexity_of_encounter,
    referring_provider: props.data?.encounter?.referring_provider,
    encounter_role: props.data?.encounter?.encounter_role,

    /* Patient illness histories */
    hpi: props.data?.encounter?.patient_illness_history?.hpi || "",
    forms: props.data?.encounter?.patient_illness_history?.forms || "",
    situation: props.data?.encounter?.patient_illness_history?.situation || "",

    /* Review of systems */
    ros: props.data?.encounter?.review_of_system?.ros || "",
    ros_gen: props.data?.encounter?.review_of_system?.ros_gen || "",
    ros_eye: props.data?.encounter?.review_of_system?.ros_eye || "",
    ros_ent: props.data?.encounter?.review_of_system?.ros_ent || "",
    ros_resp: props.data?.encounter?.review_of_system?.ros_resp || "",

    /* Vital Signs */
    vital_date: props.data?.encounter?.vital?.vital_date || "",
    age: props.data?.encounter?.vital?.age || "",
    passage: props.data?.encounter?.vital?.passage || "",
    weight: props.data?.encounter?.vital?.weight || "",
    height: props.data?.encounter?.vital?.height || "",
    head_circumference: props.data?.encounter?.vital?.head_circumference || "",
    bmi: props.data?.encounter?.vital?.bmi || "",
    temperature: props.data?.encounter?.vital?.temperature || "",
    temperature_method: props.data?.encounter?.vital?.temperature_method || "",
    bp_systolic: props.data?.encounter?.vital?.bp_systolic || "",
    bp_diastolic: props.data?.encounter?.vital?.bp_diastolic || "",
    bp_position: props.data?.encounter?.vital?.bp_position || "",
    pulse: props.data?.encounter?.vital?.pulse || "",
    respirations: props.data?.encounter?.vital?.respirations || "",
    o2_saturation: props.data?.encounter?.vital?.o2_saturation || "",
    vitals_other: props.data?.encounter?.vital?.vitals_other || "",
    wt_percentile: props.data?.encounter?.vital?.wt_percentile || "",
    ht_percentile: props.data?.encounter?.vital?.ht_percentile || "",
    hc_percentile: props.data?.encounter?.vital?.hc_percentile || "",
    wt_ht_percentile: props.data?.encounter?.vital?.wt_ht_percentile || "",
    bmi_percentile: props.data?.encounter?.vital?.bmi_percentile || "",

    /* physical examination */
    pe: props.data?.encounter?.physical_examination?.pe || "",

    /* assessments */
    assessment_date: props.data?.encounter?.assessment?.assessment_date || "",
    icd: props.data?.encounter?.assessment?.icd || "",
    other: props.data?.encounter?.assessment?.other || "",
    assessment: props.data?.encounter?.assessment?.assessment || "",
    assessment_other: props.data?.encounter?.assessment?.assessment_other || "",
    differential_diagnoses: props.data?.encounter?.assessment?.differential_diagnoses || "",
    assessment_discussion: props.data?.encounter?.assessment?.assessment_discussion || "",

    /* plans */
    plan_date: props.data?.encounter?.plan?.plan_date || "",
    plan: props.data?.encounter?.plan?.plan || "",
    duration: props.data?.encounter?.plan?.duration || "",
    followup: props.data?.encounter?.plan?.followup || "",
    goals: props.data?.encounter?.plan?.goals || "",
    tp: props.data?.encounter?.plan?.tp || "",

    /* orders */
    labs: "",
    labs_icd: "",
    radiology: "",
    radiology_icd: "",
    cp: "",
    cp_icd: "",
    lab_notes: "",
    radiology_notes: "",
    cp_notes: "",

    /* referrals */
    orders_referrals: "",
    referrals_icd: "",
    referral_notes: "",

    /* sign form */
    date: "",
    signed: "",
    date_signed: "",
    encounter_age: "",
    location: "",
    activity: "",
    cc: "",

    /* billing form */
    bill_submitted: "",
    addendum: "",
    addendum_eid: "",
    encounter_template: "",
    bill_complex: "",

    /* annotate */
    annotate_image: "", 
});
const page = usePage();

watch(
  () => page.props.flash?.encounter_id,
  (id) => {
    if (id) {
      encounterForm.id = id;
    }
  }
);

const saveEncounter = () => {
    encounterForm.put(route('doctor.encounters.update', encounterForm.id), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {

            if (mainTab.value === 'Details') {
                $('#soap-accordion')?.collapse('show');
            }

            else if (mainTab.value === 'SOAP') {
                mainTab.value = 'Billing';
            }

            else if (mainTab.value === 'Billing') {
                closeEncounterSummary();
            }
        },
        onError: (errors) => {
            console.error("Validation failed:", errors);
        },
    });
};

const saveAndProceed = (nextTab) => {
    encounterForm.put(route('doctor.encounters.update', encounterForm.id), {
         preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            if (nextTab) {
                mainTab.value = nextTab;
            }
        },
        onError: (errors) => {
            console.error("Validation failed:", errors);
        }
    });
};

const saveAndGoToNextTab = (current) => {
    encounterForm.put(route('doctor.encounters.update', encounterForm.id), {
 preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            if (current) {
                const currentCollapse = document.getElementById(current);
                if (currentCollapse) {
                    $(currentCollapse).collapse('hide');
                }
            }
            mainTab.value = 'Prescription'; // Go to the next tab
        }
    });
};

const saveAndNext = (current, next) => {
    encounterForm.put(route('doctor.encounters.update', encounterForm.id), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            if (current && next) {
                const currentCollapse = document.getElementById(current);
                const nextCollapse = document.getElementById(next);
                if (currentCollapse && nextCollapse) {
                    $(currentCollapse).collapse('hide');
                    $(nextCollapse).collapse('show');
                }
            }
        },
        onError: (errors) => {
            console.error("Validation failed:", errors);
        }
    });
};

const newReferralProviderForm = useForm({
    first_name: "",
    last_name: "",
    prefix: "",
    suffix: "",
    facility: " ",
    speciality: "",
    address: "",
    city: " ",
    state: " ",
    pin_code: "",
    phone: " ",
    email: " ",
    comments: " ",
});
 
const closeEncounterSummary = () => {
    isEncounterSummaryModalOpen.value = false;
};

const closeAddReferralProviderModal = () => {
    isAddReferralProviderModalOpen.value = false;
};

const saveNewReferralProvider = () => {
     closeAddReferralProviderModal();
};

// Template functions
const handleTemplateData = (payload) => {
     templateField.value = payload.field;
    templateCategory.value = payload.category;
    templateData.value = payload.data;
};

const handleTemplateSelected = (templateItem) => {
       
    // Determine which form field to update based on the category
    let fieldToUpdate = '';
    switch (templateCategory.value) {
        case 'hpi':
            fieldToUpdate = 'hpi';
            break;
        case 'ros':
            fieldToUpdate = 'ros';
            break;
        case 'pe':
            fieldToUpdate = 'pe';
            break;
        case 'assessment':
            fieldToUpdate = 'assessment';
            break;
        case 'plan':
            fieldToUpdate = 'plan';
            break;
        case 'orders_labs':
            fieldToUpdate = 'labs';
            break;
        case 'orders_labs_icd':
            fieldToUpdate = 'labs_icd';
            break;
        case 'orders_radiology':
            fieldToUpdate = 'radiology';
            break;
        case 'orders_radiology_icd':
            fieldToUpdate = 'radiology_icd';
            break;
        case 'orders_cp':
            fieldToUpdate = 'cp';
            break;
        case 'orders_cp_icd':
            fieldToUpdate = 'cp_icd';
            break;
        case 'orders_notes':
        case 'orders_labs_notes':
            fieldToUpdate = 'lab_notes';
            break;
        case 'orders_radiology_notes':
            fieldToUpdate = 'radiology_notes';
            break;
        case 'orders_cp_notes':
            fieldToUpdate = 'cp_notes';
            break;
        case 'orders_referral_notes':
            fieldToUpdate = 'referral_notes';
            break;
        case 'orders_referrals':
            fieldToUpdate = 'orders_referrals';
            break;
        case 'referrals_icd':
            fieldToUpdate = 'referrals_icd';
            break;
        default:
            fieldToUpdate = templateField.value;
    }
     // Update the form field with the selected template
    if (fieldToUpdate && encounterForm[fieldToUpdate] !== undefined) {
        let currentValue = encounterForm[fieldToUpdate] || '';
        
        // Apply different formatting based on field type
        if (fieldToUpdate.includes('_icd')) {
            // For ICD codes, add comma separation
            if (currentValue) {
                encounterForm[fieldToUpdate] = currentValue + ', ' + templateItem.text;
            } else {
                encounterForm[fieldToUpdate] = templateItem.text;
            }
        } else {
            // For other fields, add new line
            if (currentValue !== '') {
                currentValue += '\n';
            }
            encounterForm[fieldToUpdate] = currentValue + templateItem.text;
        }
    }
};

const handleTemplateUpdate = () => {
    // Refresh template data if needed
    if (templateCategory.value) {
        // You can implement refresh logic here if needed
    }
};

const signature = (id) => {
    router.post(route('doctor.encounter.signature'), {
        id: id
    });
};

</script>

<template>
<AuthLayout title="Edit Encounter" description="Edit patient encounter details" heading="Edit Encounter">
        <div class="my-4">
            <div class="d-flex justify-content-end gap-2">
                 <button v-if="route().current('doctor.encounters.edit')" class="btn btn-info px-4 d-flex gap-2 align-items-center" @click="signature(data?.encounter?.id)">
                    <i class="bi bi-pen-fill"></i>
                    Signature
                </button>
                <a v-if="route().current('doctor.encounters.edit')" :href="route('doctor.encounters.show', data?.encounter?.id)" target="_blank">
                    <button class="btn btn-danger px-4 d-flex gap-2 align-items-center">
                        <i class="bi bi-eye-fill"></i>
                        Preview
                    </button>
                </a>
                <button class="btn btn-primary px-4 d-flex gap-2 align-items-center" @click="openAIModal">
                    <i class="bi bi-stars"></i>
                    Autofill with AI
                </button>
            </div>
        </div>
        
        <Tabs :tabs="mainTabs" :currentTab="mainTab" @update:currentTab="mainTab = $event">
            <template #content>
                <form @submit.prevent="saveEncounter">
                     <div class="mt-4">
                        <!-- Details Tab -->
                        <div class="card mb-3" v-if="mainTab === 'Details'">
                            <div class="card-header bg-primary">
                                <div class="card-title">
                                    <h6 class="d-flex justify-content-between text-white">New Encounter Details</h6>
                                </div>
                            </div>
                            <div class="card-body">
                                <EncounterDetails :encounterForm="encounterForm" :data="data" :isSaving="isSaving" @save="saveAndProceed('SOAP')" />
                            </div>
                        </div>

                        <!-- SOAP Tab -->
                        <div v-if="mainTab === 'SOAP'" class="accordion accordion-clean" id="soap-accordion">
                             <div class="row">
                                <div class="col-md-8">
                                    <!-- Subjective -->
                                    <div class="card mb-3">
                                        <div class="card-header bg-primary">
                                            <div class="card-title" data-toggle="collapse" data-target="#subjective-collapse">
                                                <h6 class="d-flex justify-content-between text-white">Subjective
                                                    <i class="fas fa-angle-right d-flex justify-content-end"></i>
                                                </h6>
                                            </div>
                                        </div>
                                        <div id="subjective-collapse" class="collapse show" data-parent="#soap-accordion">
                                            <div class="card-body">
                                                <Subjective :form="encounterForm" @templateData="handleTemplateData" />
                                                <div class="d-flex justify-content-end gap-2 mt-3">
                                                    <button type="button" @click="saveAndNext('subjective-collapse', 'objective-collapse')" class="btn btn-sm btn-primary">Save</button>
                                                    <button class="btn btn-sm btn-danger">Cancel</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Objective -->
                                    <div class="card mb-3">
                                        <div class="card-header bg-primary">
                                            <div class="card-title collapsed" data-toggle="collapse" data-target="#objective-collapse">
                                                <h6 class="d-flex justify-content-between text-white">Objective
                                                    <i class="fas fa-angle-right d-flex justify-content-end"></i>
                                                </h6>
                                            </div>
                                        </div>
                                        <div id="objective-collapse" class="collapse" data-parent="#soap-accordion">
                                            <div class="card-body">
                                                <Objective :form="encounterForm" @templateData="handleTemplateData" />
                                                <div class="d-flex justify-content-end gap-2 mt-3">
                                                    <button type="button" @click="saveAndNext('objective-collapse', 'assessment-collapse')" class="btn btn-sm btn-primary">Save</button>
                                                    <button type="button" class="btn btn-sm btn-danger">Cancel</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Assessment -->
                                    <div class="card mb-3">
                                        <div class="card-header bg-primary">
                                            <div class="card-title collapsed" data-toggle="collapse" data-target="#assessment-collapse">
                                                <h6 class="d-flex justify-content-between text-white">Assessment
                                                    <i class="fas fa-angle-right d-flex justify-content-end"></i>
                                                </h6>
                                            </div>
                                        </div>
                                        <div id="assessment-collapse" class="collapse" data-parent="#soap-accordion">
                                            <div class="card-body col-md-12 mb-3">
                                                <Assessment :form="encounterForm" :data="data" @templateData="handleTemplateData" />
                                                <div class="d-flex justify-content-end gap-2 mt-3">
                                                    <button type="button" @click="saveAndNext('assessment-collapse', 'plan-collapse')" class="btn btn-sm btn-primary">Save</button>
                                                    <button type="button" class="btn btn-sm btn-danger">Cancel</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Plan -->
                                    <div class="card mb-3">
                                        <div class="card-header bg-primary">
                                            <div class="card-title collapsed" data-toggle="collapse" data-target="#plan-collapse">
                                                <h6 class="d-flex justify-content-between text-white">Plan
                                                    <i class="fas fa-angle-right d-flex justify-content-end"></i>
                                                </h6>
                                            </div>
                                        </div>
                                        <div id="plan-collapse" class="collapse" data-parent="#soap-accordion">
                                            <div class="card-body">
                                                <Plan :form="encounterForm" @templateData="handleTemplateData" />
                                                <div class="d-flex justify-content-end gap-2 mt-3">
                                                    <button type="button" @click="saveAndGoToNextTab('plan-collapse')" class="btn btn-sm btn-primary">Save</button>
                                                    <button type="button" class="btn btn-sm btn-danger">Cancel</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Template Panel for SOAP -->
                                <div class="col-md-4">
                                    <TemplateSelector 
                                        :data="templateData"
                                        :category="templateCategory"
                                        @template-selected="handleTemplateSelected"
                                        @update-template="handleTemplateUpdate"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Prescription Tab -->
                         <PrescriptionForm v-if="mainTab === 'Prescription'" :mainForm="encounterForm" :data="data" :weight="encounterForm.weight" @save="saveAndProceed('Order')" />

                        <!-- Order Tab -->
                        <div class="row" v-if="mainTab === 'Order'">
                            <div class="col-md-8">
                                <OrderForm 
                                    :form="encounterForm" 
                                    :encounterForm="encounterForm" 
                                    :data="data" 
                                    @template-data="handleTemplateData"
                                    @save="saveAndProceed('Documents')"
                                />
                            </div>
                            <div class="col-md-4">
                                <TemplateSelector 
                                    :data="templateData"
                                    :category="templateCategory"
                                    @template-selected="handleTemplateSelected"
                                    @update-template="handleTemplateUpdate"
                                />
                            </div>
                        </div>

                        <!-- Documents Tab -->
                        <div v-if="mainTab === 'Documents'" class="row">
                            <div class="col-md-8">
                                <Documents :form="encounterForm" @templateData="handleTemplateData" @save="saveAndProceed('Billing')" />
                            </div>
                            <div class="col-md-4">
                                <TemplateSelector 
                                    :data="templateData"
                                    :category="templateCategory"
                                    @template-selected="handleTemplateSelected"
                                    @update-template="handleTemplateUpdate"
                                />
                            </div>
                        </div>

                        <!-- Billing Tab -->
                        <Billing v-if="mainTab === 'Billing'" :form="encounterForm" :insurances="data.insurances" @save="saveAndProceed(null)" />
                        
                        <!-- Referral Tab -->
                        <div v-if="mainTab === 'Referral'" class="row">
                            <div class="col-md-8">
                                <Referral :form="encounterForm" @templateData="handleTemplateData" :data="data" />
                            </div>
                            <div class="col-md-4">
                                <TemplateSelector 
                                    :data="templateData"
                                    :category="templateCategory"
                                    @template-selected="handleTemplateSelected"
                                    @update-template="handleTemplateUpdate"
                                />
                            </div>
                        </div>
                    </div>
                </form>
            </template>
        </Tabs>

        <!-- Modals -->
        <Modal :isOpen="isAIImportModalOpen" title="AI Transcript" @close="closeAIModal" size="lg">
            <AIImportModal @close="closeAIModal" @submit="importTranscript" :form="encounterForm" />
        </Modal>

        <Modal :isOpen="isEncounterSummaryModalOpen" title="Summary" @close="closeEncounterSummary" size="xl">
            <EncounterSummary :form="encounterForm" @close="closeEncounterSummary" @submit="saveEncounter" />
        </Modal>

        <Modal :isOpen="isAddReferralProviderModalOpen" title="Add New Referral Provider"
            @close="closeAddReferralProviderModal" size="lg">
            <AddAddressModal @close="closeAddReferralProviderModal" :form="newReferralProviderForm"
                @submit="saveNewReferralProvider" />
        </Modal>
         
    </AuthLayout>
</template>