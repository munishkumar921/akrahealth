 <script setup>
import axios from "axios";
import { ref, defineEmits, onMounted, watch, reactive } from "vue";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import BaseDatePicker from "@/Components/Common/Input/BaseDatePicker.vue";
import { routeOptions } from "@/Data/commonData";
 import BaseTagsInput from "@/Components/Common/Input/BaseTagsInput.vue";
import "@vueform/multiselect/themes/default.css";
import Search from "@/Components/Common/Search.vue";
import {useForm } from "@inertiajs/vue3";
import Modal from "@/Components/Common/Modal.vue";
import LabProviderModal from "@/Pages/Modals/AddLab.vue";
 
import Swal from 'sweetalert2/dist/sweetalert2.js';
import { route } from "ziggy-js";

const props = defineProps({
    form: Object,
    data: Object,
});

// Error state for each form section
const labErrors = ref({});
const radiologyErrors = ref({});
const cardiopulmonaryErrors = ref({});
const referralErrors = ref({});
const supplementErrors = ref({});

 

// Helper to clear previous errors
const clearErrors = (errorRef) => {
    Object.keys(errorRef.value).forEach(key => {
        errorRef.value[key] = '';
    });
};
watch(() => props.form.labs, (newValue) => {
    if (newValue) labForm.labs = newValue;
});
watch(() => props.form.labs_icd, (newValue) => {
    if (newValue) labForm.labs_icd = newValue;
});
watch(() => props.form.radiology, (newValue) => {
    if (newValue) radiologyForm.radiology = newValue;
});
watch(() => props.form.radiology_icd, (newValue) => {
    if (newValue) radiologyForm.radiology_icd = newValue;
});
watch(() => props.form.cp, (newValue) => {
    if (newValue) cardiopulmonaryForm.cp = newValue;
});
watch(() => props.form.cp_icd, (newValue) => {
    if (newValue) cardiopulmonaryForm.cp_icd = newValue;
});
watch(() => props.form.lab_notes, (newValue) => {
    if (newValue) labForm.notes = newValue;
});
watch(() => props.form.radiology_notes, (newValue) => {
    if (newValue) radiologyForm.notes = newValue;
});
watch(() => props.form.cp_notes, (newValue) => {
    if (newValue) cardiopulmonaryForm.notes = newValue;
});
watch(() => props.form.orders_referrals, (newValue) => {
    if (newValue) referralForm.referrals = newValue;
});
watch(() => props.form.referrals_icd, (newValue) => {
    if (newValue) referralForm.referrals_icd = newValue;
});
watch(() => props.form.referral_notes, (newValue) => {
    if (newValue) referralForm.notes = newValue;
});

// Separate form instances for each section
const supplementLoading = ref(false);

const supplementForm = useForm({
    supplement_id: "",
    encounter_id: props.form.id,
    date_active: "",
    date_inactive: "",
    date_prescribed: "",
    supplement: "",
    dosage: "",
    dosage_unit: "",
    sig: "",
    route: "",
    frequency: "",
    instructions: "",
    quantity: "",
    reason: "",
    reconcile: "",
    action_after_saving: "save_only",
});

const labForm = useForm({
    encounter_id: props.form.id,
    type: "labs",
    address_id: "",
    encounter_provider: "",
    orders_date: "",
    insurance_id: "",
    labs: "",
    labs_icd: [],
    labs_obtained: "",
    notes: "",
    pending_date: "",
    is_completed: "",
    action_after_saving:'save_only',

});

const radiologyForm = useForm({
    encounter_id: props.form.id,
    type: "radiology",
    address_id: "",
    encounter_provider: "",
    orders_date: "",
    insurance_id: "",
    radiology: "",
    radiology_icd: [],
    notes: "",
    pending_date: "",
    is_completed: "",
    action_after_saving:'save_only',

});

const cardiopulmonaryForm = useForm({
    encounter_id: props.form.id,
    type: "cp",
    address_id: "",
    encounter_provider: "",
    orders_date: "",
    insurance_id: "",
    cp: "",
    cp_icd: [],
    notes: "",
    pending_date: "",
    is_completed: "",
    action_after_saving:'save_only',

});

const referralForm = useForm({
    encounter_id: props.form.id,
    type: "referrals",
    address_id: "",
    encounter_provider: "",
    orders_date: "",
    insurance_id: "",
    referrals: "",
    referrals_icd: [],
    notes: "",
    pending_date: "",
    is_completed: "",
    specialty: "",
    doctor_id: "",
    action_after_saving:"save_only",

});

const advanceToNextCollapse = (currentId, nextId) => {
    const currentCollapse = document.getElementById(currentId);
    const nextCollapse = document.getElementById(nextId);
     if (currentCollapse && nextCollapse) {
        // Using jQuery's collapse method which seems to be in use by the template
        $(currentCollapse).collapse('hide');
        $(nextCollapse).collapse('show');
        const currentHeader = $(`[data-target="#${currentId}"]`);
        currentHeader.addClass('collapsed');
        currentHeader.attr('aria-expanded', 'false');
        const nextHeader = $(`[data-target="#${nextId}"]`);
        nextHeader.removeClass('collapsed');
        nextHeader.attr('aria-expanded', 'true');
    }
};


/* Supplement Order start */
const savedSupplements = ref([]);
const upsertSupplement = () => {
    supplementLoading.value = true;
    supplementForm.encounter_id = props.form.id;
    
    // Clear previous errors
    clearErrors(supplementErrors);

    axios.post(route('doctor.upsert.supplement'), supplementForm).then(response => {
        if(response.data.length > 0) {
        savedSupplements.value = response.data;
         advanceToNextCollapse('order-supplement-collapse', 'lab-order-collapse');
         toast('The supplement order saved successfully!');
          }
    }).catch(error => {
        console.error('Error saving supplement:', error);
        
        // Extract and display validation errors
        if (error.response && error.response.data) {
            if (error.response.data.errors) {
                // Laravel validation errors
                const errors = error.response.data.errors;
                 
                // Store errors for field-level display
                Object.keys(errors).forEach(key => {
                    supplementErrors.value[key] = Array.isArray(errors[key]) ? errors[key][0] : errors[key];
                    toast(config.errorRef.value[key], 'error');

                });
             } else if (error.response.data.message) {
                // Generic error message
                toast(error.response.data.message, 'error');
            } else {
                toast('Failed to save supplement. Please try again.', 'error');
            }
        } else {
            toast('Failed to save supplement. Please try again.', 'error');
        }
    }).finally(() => {
        supplementForm.reset();
        supplementLoading.value = false;
        supplementForm.encounter_id = props.form.id; // Re-assign after reset
    });
}

const deleteSupplement = (id) => {
    Swal.fire(confirmSettings('Are you sure to delete this data?', 'You want be get it back')).then((result) => {
        if (result.isConfirmed) {
            axios.post(route('doctor.delete.supplement', id)).then(response => {
                savedSupplements.value = response.data;
                toast('The supplement deleted successfully!');
            });
        }
    });
}

const getSupplements = () => {
    axios.get(route('doctor.get.supplement', props.form.id ?? 0)).then(response => {
        savedSupplements.value = response.data;
    });
}
/* Supplement Order ends */

/* lab test order start */
const labs = ref([]);
const imageList = ref([]);
const cardList = ref([]);
const referrals = ref([]);

const labLoading = ref(false);
const radiologyLoading = ref(false);
const cardiopulmonaryLoading = ref(false);
const referralLoading = ref(false);

const handleUpsert = (config) => {
    config.loading.value = true;
    config.form.encounter_id = props.form.id;
    
    // Clear previous errors
    clearErrors(config.errorRef);

    axios.post(route('doctor.upsert.encounter.order'), config.form)
        .then(response => {
            if(response.data.length > 0) {
                
          
            config.list.value = response.data;

            toast(`The ${config.name} order saved successfully!`);

            if (config.form.action_after_saving === 'print') {
                const newOrder = response.data[response.data.length - 1];
                if (newOrder) {
                    window.location.href = route('doctor.download.encounter.order', newOrder.id);
                }
            }

            if (config.nextCollapseId) {
                advanceToNextCollapse(config.currentCollapseId, config.nextCollapseId);
            } else {
                emit('save'); // Emit save on the last step
            }
            }
        })
        .catch(error => {
            console.error(`Error saving ${config.name} order:`, error);
            
            // Extract and display validation errors
            if (error.response && error.response.data) {
                if (error.response.data.errors) {
                    // Laravel validation errors
                    const errors = error.response.data.errors;
                     
                    // Store errors for field-level display
                    Object.keys(errors).forEach(key => {
                        config.errorRef.value[key] = Array.isArray(errors[key]) ? errors[key][0] : errors[key];
                        toast(config.errorRef.value[key], 'error');
                    });
                    
                } else if (error.response.data.message) {
                    // Generic error message
                    toast(error.response.data.message, 'error');
                 } else {
                    // Fallback
                    toast(`Failed to save ${config.name} order. Please try again.`, 'error');
                }
            } else {
                toast(`Failed to save ${config.name} order. Please try again.`, 'error');
            }
        })
        .finally(() => {
            // Reset local form
            config.form.reset(...config.resetFields, 'action_after_saving');
            config.form.encounter_id = props.form.id; // Re-assign encounter_id

            // Clear corresponding props from parent
            if (config.propFields) {
                config.propFields.forEach(field => {
                    const propKey = Object.keys(field)[0];
                    const defaultValue = field[propKey];
                    props.form[propKey] = defaultValue;
                });
            }
            
            config.loading.value = false;
        });
};

const upsertLab = () => {
    handleUpsert({ name: 'lab', form: labForm, list: labs, loading: labLoading, currentCollapseId: 'lab-order-collapse', nextCollapseId: 'imaging-order-collapse', resetFields: ['labs', 'labs_icd', 'notes'], propFields: [{ 'labs': '' }, { 'labs_icd': [] }, { 'lab_notes': '' }], errorRef: labErrors });
}

const upsertRadiology = () => {
    handleUpsert({ name: 'radiology', form: radiologyForm, list: imageList, loading: radiologyLoading, currentCollapseId: 'imaging-order-collapse', nextCollapseId: 'cp-order-collapse', resetFields: ['radiology', 'radiology_icd', 'notes'], propFields: [{ 'radiology': '' }, { 'radiology_icd': [] }, { 'radiology_notes': '' }], errorRef: radiologyErrors });
}

const upsertCardiopulmonary = () => {
    handleUpsert({ name: 'cardiopulmonary', form: cardiopulmonaryForm, list: cardList, loading: cardiopulmonaryLoading, currentCollapseId: 'cp-order-collapse', nextCollapseId: 'referral-order-collapse', resetFields: ['cp', 'cp_icd', 'notes'], propFields: [{ 'cp': '' }, { 'cp_icd': [] }, { 'cp_notes': '' }], errorRef: cardiopulmonaryErrors });
}

const upsertReferral = () => {
    handleUpsert({ name: 'referral', form: referralForm, list: referrals, loading: referralLoading, currentCollapseId: 'referral-order-collapse', nextCollapseId: null, resetFields: ['referrals', 'referrals_icd', 'notes', 'specialty', 'doctor_id'], propFields: [{ 'orders_referrals': '' }, { 'referrals_icd': [] }, { 'referral_notes': '' }, { 'action_after_saving': '' }], errorRef: referralErrors });
}

const deleteOrder = (id, field) => {
    Swal.fire(confirmSettings('Are you sure to delete this data?', 'You want be get it back')).then((result) => {
        if (result.isConfirmed) {
            axios.post(route('doctor.delete.encounter.order', [id, field])).then(response => {
                if (field == 'labs') {
                    labs.value = response.data;
                } else if (field == 'radiology') {
                    imageList.value = response.data;
                } else if (field == 'cp') {
                    cardList.value = response.data;
                } else if (field == 'referrals') {
                    referrals.value = response.data;
                }
                toast('The data has been deleted successfully!');
            });
        }
    });
}

const getOrders = (field) => {
    axios.get(route('doctor.get.encounter.order', [props.form.id, field])).then(response => {
        if (field == 'labs') {
            labs.value = response.data;
        } else if (field == 'radiology') {
            imageList.value = response.data;
        } else if (field == 'cp') {
            cardList.value = response.data;
        } else if (field == 'referrals') {
            referrals.value = response.data;
        }
    });
}
/* lab test order end */

const actionOptions = [
    { value: "save_only", label: "Save Only" },
    { value: "print", label: "Print" },
    { value: "add_to_print_queue", label: "Add to Print Queue" }

];

const labActionAfterSaving = [
    { value: "do_nothing", label: "Do Nothing" },
    { value: "print_and_fax", label: "Print and Fax" },
    { value: "send_electronically", label: "Send Electronically" },
];

const imagingActionAfterSaving = [
    { value: "save_only", label: "Save Only" },
    { value: "print", label: "Print" },
    { value: "add_to_print_queue", label: "Add to Print Queue" },
];

const diagnosisOptions = ref([
    { value: 'R51', label: 'R51 - Headache' },
    { value: 'M54.5', label: 'M54.5 - Low back pain' },
]);

const emit = defineEmits(['templateData', 'save', 'close']);

const getDateMeta = (keyword) => {
    const form = new FormData();
    form.append('id', keyword);
    axios.post(route('doctor.get.templates'), form)
        .then(response => {
            response.category = keyword;
            response.field = keyword;
            emit('templateData', response);
        });
}

/* search supplements */
const loader = ref(false);
const searchQuery = ref("");
const supplements = ref([]);
const search = () => {
    loader.value = true;
    const form = new FormData();
    form.append("search_supplement", searchQuery.value);
    axios.post(route('doctor.search.supplement', 'Y'), form).then(response => {
        supplements.value = response.data.message;
        loader.value = false;
    });
}

const selectSupplement = (row) => {
    supplementForm.supplement = row.label;
    supplements.value = [];
}

/* search test */
const labLoader = ref(false);
const labSearchQuery = ref("");
const tests = ref([]);
const labSearch = () => {
    labLoader.value = true;
    const form = new FormData();
    form.append("search", labSearchQuery.value);
    axios.post(route('doctor.search.loinc'), form).then(response => {
        tests.value = response.data.message;
        labLoader.value = false;
    });
}

const selectTest = (row) => {
    labForm.labs = row.value;
    tests.value = [];
}

/* image search */
const imageLoader = ref(false);
const imageSearchQuery = ref("");
const images = ref([]);
const imageSearch = () => {
    imageLoader.value = true;
    const form = new FormData();
    form.append("search_imaging", imageSearchQuery.value);
    axios.post(route('doctor.search.imaging'), form).then(response => {
        images.value = response.data.message;
        imageLoader.value = false;
    });
}

const selectImage = (row) => {
    radiologyForm.radiology = row.value;
    images.value = [];
}

// Helper functions
const toast = (message, icon = 'success') => {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: icon,
        title: message,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}

const confirmSettings = (title, text) => {
    return {
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    };
}

onMounted(() => {
    getSupplements();
    getOrders('labs');
    getOrders('radiology');
    getOrders('cp');
    getOrders('referrals');
});

const isOpenRadiologyProviderModal = ref(false);
const openRadiologyProviderModal = () => {
      isOpenRadiologyProviderModal.value = true;
};
const closeRadiologyProviderModal = () => {
     isOpenRadiologyProviderModal.value = false;
};

const isOpenCardiopulmonaryProviderModal = ref(false);
const openCardiopulmonaryProviderModal = () => {
    isOpenCardiopulmonaryProviderModal.value = true;
};
const isOpenLaboratoryProviderModal = ref(false);
const openLaboratoryProviderModal = () => {
    isOpenLaboratoryProviderModal.value = true
}
const isOpenReferralProviderModal = ref(false);
const openReferralProviderModal = () => {
    isOpenReferralProviderModal.value = true
}
const closeReferralProviderModal = () => {
    isOpenReferralProviderModal.value = false
}
const closeLaboratoryProviderModal = () => {
    isOpenLaboratoryProviderModal.value = false
}
const closeCardiopulmonaryProviderModal = () => {
    isOpenCardiopulmonaryProviderModal.value = false;
};


const specialtyOptions = ref(props.data.specialties);

const referralProviderOptions = ref([]);
const getDoctorsBySpecialty = () => {
    if (referralForm.specialty) {
        axios.get(route('doctor.get.doctors.by.specialty', referralForm.specialty))
            .then(response => {
                referralProviderOptions.value = response.data;
            });
    }
}

</script>

<template>
    <div class="accordion accordion-clean" id="order-accordion">
        <!-- Order Supplement Section -->
        <div class="card mb-3">
            <div class="card-header bg-primary">
                <div class="card-title" data-toggle="collapse" data-target="#order-supplement-collapse">
                    <h6 class="d-flex justify-content-between text-white">
                        Order Supplement
                        <i class="fas fa-angle-right angle d-flex justify-content-end"></i>
                    </h6>
                </div>
            </div>
             <div id="order-supplement-collapse" class="collapse show" data-parent="#order-accordion">
                <div class="card-body">
                    <div class="row">

                        <div class="mb-4">
                            <Search v-model="searchQuery" :searchResult="supplements" :loader="loader" @input="search"
                                :placeholder="'Search for Supplement'" />
                            <template v-if="!loader">
                                <p v-for="row in supplements" :key="row.value"
                                    class="p-2 border-bottom bg-color-white-lilac cursor-pointer"
                                    @click="selectSupplement(row)">
                                    {{ row.label }}
                                </p>
                            </template>
                            <template v-else>
                                <div class="text-center p-4">
                                    <span v-if="loader" class="spinner-border spinner-border-sm"></span>
                                </div>
                            </template>
                        </div>

                        <template v-for="supplement in savedSupplements" :key="supplement.id">
                            <div class="p-2 border-bottom bg-color-white-lilac cursor-pointer mt-2">
                                <div class="row align-items-center">
                                    <div class="col-8">
                                        <p class="mb-0">
                                            <span class="badge mr-2">{{ supplement.supplement }}</span>
                                            <span class="badge mr-2">{{ supplement.dosage }}</span>
                                            <span class="badge mr-2">{{ supplement.dosage_unit }}</span>
                                            <span class="badge mr-2">{{ supplement.frequency }}</span>
                                        </p>
                                    </div>
                                    <div class="col-4 text-end">
                                        <button class="btn btn-danger" type="button" title="Delete"
                                            @click="deleteSupplement(supplement.id)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div class="col-md-6 mt-5">
                            <BaseInput v-model="supplementForm.supplement" label="Supplement" required 
                                placeholder="Search for supplement" :error="supplementErrors.supplement" />
                        </div>
                        <div class="col-md-3 mt-5">
                            <BaseInput v-model="supplementForm.dosage" label="Dosage" placeholder="e.g., 100" :error="supplementErrors.dosage" required />
                        </div>
                        <div class="col-md-3 mt-5">
                            <BaseInput v-model="supplementForm.dosage_unit" label="Dosage Unit" required
                                placeholder="e.g., mg" :error="supplementErrors.dosage_unit" />
                        </div>
                        <div class="col-md-6 mt-3">
                            <BaseInput v-model="supplementForm.sig" label="Sig" placeholder="e.g., Take one daily" :error="supplementErrors.sig" />
                        </div>
                        <div class="col-md-6 mt-3">
                            <BaseSelect v-model="supplementForm.route" label="Route" placeholder="Select Route"
                                required :error="supplementErrors.route">
                                    <option value="" disabled selected>Select Route</option>

                                <option v-for="route in routeOptions" :key="route" :value="route">
                                    {{ route }}
                                </option>
                            </BaseSelect>
                        </div>
                        <div class="col-md-6 mt-3">
                            <BaseInput v-model="supplementForm.frequency" label="Frequency" placeholder="e.g., Daily" :error="supplementErrors.frequency" />
                        </div>
                        <div class="col-md-6 mt-3">
                            <BaseInput v-model="supplementForm.instructions" label="Special Instructions"
                                placeholder="Enter special instructions" :error="supplementErrors.instructions" />
                        </div>
                        <div class="col-md-6 mt-3">
                            <BaseInput v-model="supplementForm.reason" label="Reason" placeholder="Enter reason" :error="supplementErrors.reason" />
                        </div>
                        <div class="col-md-6 mt-3">
                            <BaseDatePicker v-model="supplementForm.date_active" label="Date Active"
                                placeholder="Select date" :error="supplementErrors.date_active" />
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" @click="upsertSupplement()" class="btn btn-sm btn-primary" :disabled="supplementLoading">
                            <span v-if="supplementLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            {{ supplementLoading ? 'Saving...' : 'Save' }}
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" :disabled="supplementLoading">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lab Order Section -->
        <div class="card mb-3">
            <div class="card-header bg-primary">
                <div class="card-title collapsed" data-toggle="collapse" data-target="#lab-order-collapse">
                    <h6 class="d-flex justify-content-between text-white">
                        Lab Order
                        <i class="fas fa-angle-right angle d-flex justify-content-end"></i>
                    </h6>
                </div>
            </div>
            <div id="lab-order-collapse" class="collapse" data-parent="#order-accordion">
                <div class="card-body">
                    <div class="row">
                        <div class="mb-4">
                            <Search v-model="labSearchQuery" :searchResult="tests" :loader="labLoader"
                                @input="labSearch" :placeholder="'Search lab test'" />
                            <template v-if="!labLoader">
                                <p v-for="row in tests" :key="row.value"
                                    class="p-2 border-bottom bg-color-white-lilac cursor-pointer"
                                    @click="selectTest(row)">
                                    {{ row.label }}
                                </p>
                            </template>
                            <template v-else>
                                <div class="text-center p-4">
                                    <span v-if="labLoader" class="spinner-border spinner-border-sm"></span>
                                </div>
                            </template>
                        </div>

                        <div class="overflow-auto mb-3" style="max-height: 300px;">
                            <template v-for="lab in labs" :key="lab.id">
                                <div class="p-2 border-bottom bg-color-white-lilac cursor-pointer mt-2 ">
                                    <div class="row align-items-center">
                                        <div class="col-8">
                                            <p class="mb-0">
                                                <span class="badge mr-2">{{ lab.labs }}</span>
                                            </p>
                                        </div>
                                        <div class="col-4 text-end">
                                            <button class="btn btn-danger" type="button" title="Delete"
                                                @click="deleteOrder(lab.id, 'labs')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="col-md-12">
                            <BaseInput v-model="labForm.labs" label="Test" type="textarea" placeholder="lab test" required
                                @click="getDateMeta('orders_labs')" :error="labErrors.labs" />
                        </div>
                        <div class="col-md-12 mt-3">
                            <BaseTagsInput v-model="labForm.labs_icd" label="Diagnosis Codes" placeholder="Type diagnosis codes" :error="labErrors.labs_icd" />
                        </div>
                        <div class="col-md-6 mt-3">
                            <BaseSelect v-model="labForm.encounter_provider" label="Laboratory Provider" placeholder="Select Laboratory Provider" required :error="labErrors.encounter_provider">
                                 <option v-for="lab in data.labs" :key="lab.id" :value="lab.id">
                                    {{ lab?.name }}

                                </option>
                            </BaseSelect>
                            <p class="text-primary cursor-pointer" @click="openLaboratoryProviderModal"><i class="bi bi-plus-circle mr-1"></i>Add Laboratory Provider</p>
                        </div>
                        <div class="col-md-6 mt-3">
                            <BaseDatePicker v-model="labForm.pending_date" label="Order Pending Date" placeholder="Select Date" :error="labErrors.pending_date"  />
                        </div>
                        <div class="col-md-12 mt-3">
                            <BaseSelect v-model="labForm.insurance_id" label="Insurance" placeholder="Select Insurance"
                                required :error="labErrors.insurance_id">
                                 <option v-for="insurance in data.insurances" :key="insurance.id" :value="insurance.id">
                                    {{ insurance.insurance_company }} - {{ insurance.plan_name }}
                                </option>
                            </BaseSelect>
                        </div>
                        <div class="col-md-12 mt-3">
                            <BaseInput v-model="labForm.notes" label="Notes" type="textarea" placeholder="Enter notes"
                                @click="getDateMeta('orders_labs_notes')" :error="labErrors.notes" />
                        </div>
                        <div class="col-md-12 mt-3">
                            <BaseSelect v-model="labForm.action_after_saving" placeholder="Select an action" label="Action After Saving" :error="labErrors.action_after_saving">
                                 <option v-for="option in actionOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </BaseSelect>
                        </div>

                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" @click="upsertLab()" class="btn btn-sm btn-primary" :disabled="labLoading">
                            <span v-if="labLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            {{ labLoading ? 'Saving...' : 'Save' }}
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" :disabled="labLoading">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Imaging Order Section -->
        <div class="card mb-3">
            <div class="card-header bg-primary">
                <div class="card-title collapsed" data-toggle="collapse" data-target="#imaging-order-collapse">
                    <h6 class="d-flex justify-content-between text-white">
                        Imaging Order
                        <i class="fas fa-angle-right angle d-flex justify-content-end"></i>
                    </h6>
                </div>
            </div>
            <div id="imaging-order-collapse" class="collapse" data-parent="#order-accordion">
                <div class="card-body">
                    <div class="row">

                        <div class="mb-4">
                            <Search v-model="imageSearchQuery" :searchResult="images" :loader="imageLoader"
                                @input="imageSearch" :placeholder="'Search image'" />
                            <template v-if="!imageLoader">
                                <p v-for="row in images" :key="row.value"
                                    class="p-2 border-bottom bg-color-white-lilac cursor-pointer"
                                    @click="selectImage(row)">
                                    {{ row.label }}
                                </p>
                            </template>
                            <template v-else>
                                <div class="text-center p-4">
                                    <span v-if="imageLoader" class="spinner-border spinner-border-sm"></span>
                                </div>
                            </template>
                        </div>
                        <div class="overflow-auto" style="max-height: 300px;">
                            <template v-for="image in imageList" :key="image.id">
                                <div class="p-2 border-bottom bg-color-white-lilac cursor-pointer mt-2">
                                    <div class="row align-items-center">
                                        <div class="col-8">
                                            <p class="mb-0">
                                                <span class="badge mr-2">{{ image.radiology }}</span>
                                            </p>
                                        </div>
                                        <div class="col-4 text-end">
                                            <button class="btn btn-danger" type="button" title="Delete"
                                                @click="deleteOrder(image.id, 'radiology')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="col-md-12 mt-3">
                            <BaseInput v-model="radiologyForm.radiology" label="Imaging Test(s)" type="textarea" required
                                placeholder="Search for imaging test" @click="getDateMeta('orders_radiology')" :error="radiologyErrors.radiology" />
                        </div> 
                        <div class="col-md-12 mt-3">
                            <BaseTagsInput v-model="radiologyForm.radiology_icd" label="Diagnosis Codes"
                                placeholder="Enter diagnosis codes" :error="radiologyErrors.radiology_icd" />
                        </div>
                        <div class="col-md-6 mt-3">
                            <BaseSelect v-model="radiologyForm.encounter_provider" label="Radiology Provider" placeholder="Select Radiology Provider" required :error="radiologyErrors.encounter_provider">
                                 <option v-for="radiology in data.radiologies" :key="radiology.id" :value="radiology.id">
                                    {{ radiology?.name }}
                                </option>
                            </BaseSelect>
                            <a  class="text-primary cursor-pointer " @click="openRadiologyProviderModal"><i class="bi bi-plus-circle mr-1"></i>Add radiology Provider</a>

                        </div>
                        <div class="col-md-6 mt-3">
                            <BaseDatePicker v-model="radiologyForm.pending_date" label="Order Pending Date" placeholder="Select Date" :error="radiologyErrors.pending_date" />
                        </div>
                        <div class="col-md-12 mt-3">
                            <BaseSelect v-model="radiologyForm.insurance_id" label="Insurance"
                                placeholder="Select Insurance" required :error="radiologyErrors.insurance_id">
                                 <option v-for="insurance in data.insurances" :key="insurance.id" :value="insurance.id">
                                    {{ insurance.insurance_company }} - {{ insurance.plan_name }}
                                </option>
                            </BaseSelect>
                        </div>
                        <div class="col-md-12 mt-3">
                            <BaseInput v-model="radiologyForm.notes" label="Notes about Order" type="textarea"
                                placeholder="Enter notes" @click="getDateMeta('orders_radiology_notes')" :error="radiologyErrors.notes" />
                        </div>
                        <div class="col-md-12 mt-3">
                            <BaseSelect v-model="radiologyForm.action_after_saving" placeholder="Select an action" label="Action After Saving" :error="radiologyErrors.action_after_saving">
                                 <option v-for="option in actionOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </BaseSelect>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" @click="upsertRadiology()" class="btn btn-sm btn-primary" :disabled="radiologyLoading">
                            <span v-if="radiologyLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            {{ radiologyLoading ? 'Saving...' : 'Save' }}
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" :disabled="radiologyLoading">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cardiopulmonary Order Section -->
        <div class="card mb-3">
            <div class="card-header bg-primary">
                <div class="card-title collapsed" data-toggle="collapse" data-target="#cp-order-collapse">
                    <h6 class="d-flex justify-content-between text-white">
                        Cardiopulmonary Order
                        <i class="fas fa-angle-right angle d-flex justify-content-end"></i>
                    </h6>
                </div>
            </div>
            <div id="cp-order-collapse" class="collapse" data-parent="#order-accordion">
                <div class="card-body">
                    <div class="row">

                        <div class="overflow-auto" style="max-height: 300px;">
                            <template v-for="card in cardList" :key="card.id">
                                <div class="p-2 border-bottom bg-color-white-lilac cursor-pointer mt-2">
                                    <div class="row align-items-center">
                                        <div class="col-8">
                                            <p class="mb-0">
                                                <span class="badge mr-2">{{ card.cp }}</span>
                                            </p>
                                        </div>
                                        <div class="col-4 text-end">
                                            <button class="btn btn-danger" type="button" title="Delete"
                                                @click="deleteOrder(card.id, 'cp')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="col-md-12">
                            <BaseInput v-model="cardiopulmonaryForm.cp" label="Test" type="textarea"
                                @click="getDateMeta('orders_cp')" placeholder="Search for cardiopulmonary test" required :error="cardiopulmonaryErrors.cp" />
                        </div>
                        <div class="col-md-12 mt-3">
                            <BaseTagsInput v-model="cardiopulmonaryForm.cp_icd" label="Diagnosis Codes" 
                                placeholder="Type diagnosis codes" :error="cardiopulmonaryErrors.cp_icd" />
                        </div>
 
                        <div class="col-md-12 mt-3">
                               <BaseSelect v-model="cardiopulmonaryForm.encounter_provider" label="Cardiopulmonary Provider"  placeholder="Select Cardiopulmonary Provider" required :error="cardiopulmonaryErrors.encounter_provider">
                                 <option v-for="radiology in data.cardiopulmonary" :key="radiology.id" :value="radiology.id">
                                    {{ radiology?.name }}
                                </option>
                            </BaseSelect>
                            <a class="text-primary cursor-pointer " @click="openCardiopulmonaryProviderModal"><i class="bi bi-plus-circle mr-1"></i>Add Cardiopulmonary Provider</a>

                        </div>
                        <div class="col-md-6 mt-3">
                            <BaseDatePicker v-model="cardiopulmonaryForm.pending_date" label="Order Pending Date" placeholder="Select Date"  required :error="cardiopulmonaryErrors.pending_date" />
                        </div>
                        <div class="col-md-6 mt-3">
                            <BaseSelect v-model="cardiopulmonaryForm.insurance_id" label="Insurance"
                                placeholder="Select Insurance" required :error="cardiopulmonaryErrors.insurance_id">
                                 <option v-for="insurance in data.insurances" :key="insurance.id" :value="insurance.id">
                                    {{ insurance.insurance_company }} - {{ insurance.plan_name }}
                                </option>
                            </BaseSelect>
                        </div>
                        <div class="col-md-12 mt-3">
                            <BaseInput v-model="cardiopulmonaryForm.notes" label="Notes about Order" type="textarea"
                                placeholder="Enter notes" @click="getDateMeta('orders_cp_notes')" :error="cardiopulmonaryErrors.notes" />
                        </div>
                        <div class="col-md-12 mt-3">
                            <BaseSelect v-model="cardiopulmonaryForm.action_after_saving" placeholder="Select an action" label="Action After Saving" :error="cardiopulmonaryErrors.action_after_saving">
                                 <option v-for="option in actionOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </BaseSelect>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" @click="upsertCardiopulmonary()" class="btn btn-sm btn-primary" :disabled="cardiopulmonaryLoading">
                            <span v-if="cardiopulmonaryLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            {{ cardiopulmonaryLoading ? 'Saving...' : 'Save' }}
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" :disabled="cardiopulmonaryLoading">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Referral Order Section -->
        <div class="card mb-3">
            <div class="card-header bg-primary">
                <div class="card-title collapsed" data-toggle="collapse" data-target="#referral-order-collapse">
                    <h6 class="d-flex justify-content-between text-white">
                        Referral Order
                        <i class="fas fa-angle-right angle d-flex justify-content-end"></i>
                    </h6>
                </div>
            </div>
            <div id="referral-order-collapse" class="collapse" data-parent="#order-accordion">
                <div class="card-body">
                    <div class="row">

                        <template v-for="referral in referrals" :key="referral.id">
                            <div class="p-2 border-bottom bg-color-white-lilac cursor-pointer mt-2">
                                <div class="row align-items-center">
                                    <div class="col-8">
                                        <p class="mb-0">
                                            <span class="badge mr-2">{{ referral.referrals }}</span>
                                        </p>
                                    </div>
                                    <div class="col-4 text-end">
                                        <button class="btn btn-danger" type="button" title="Delete"
                                            @click="deleteOrder(referral.id, 'referrals')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div class="col-md-12 mb-3">
                            <BaseInput v-model="referralForm.referrals" label="Referral" type="textarea"
                                @click="getDateMeta('orders_referrals')" placeholder="Search for referral" required :error="referralErrors.referrals" />
                        </div>
                        <div class="col-md-12 mt-3">
                            <BaseTagsInput v-model="referralForm.referrals_icd" label="Diagnosis Codes"
                                placeholder="Type diagnosis codes" :error="referralErrors.referrals_icd" />
                        </div>

                    <div class="col-md-6 mb-3 mt-3">
                          <BaseSelect v-model="cardiopulmonaryForm.encounter_provider" label="Referral Provider"  placeholder="Select Provider" :error="referralErrors.encounter_provider" required>
                                 <option v-for="radiology in data.cardiopulmonary" :key="radiology.id" :value="radiology.id">
                                    {{ radiology?.name }}
                                </option>
                            </BaseSelect>
                        <a class="text-primary cursor-pointer " @click="openReferralProviderModal"><i class="bi bi-plus-circle mr-1"></i>Add Referral Provider</a>

                    </div>
                        <div class="col-md-6 mt-3">
                            <BaseDatePicker v-model="referralForm.pending_date" label="Order Pending Date" placeholder="Select Date" :error="referralErrors.pending_date" />
                        </div>
                        <div class="col-md-6 mt-3">
                            <BaseSelect v-model="referralForm.insurance_id" label="Insurance"
                                placeholder="Select Insurance" required :error="referralErrors.insurance_id">
                                 <option v-for="insurance in data.insurances" :key="insurance.id" :value="insurance.id">
                                    {{ insurance.insurance_company }} - {{ insurance.plan_name }}
                                </option>
                            </BaseSelect>
                        </div>
                        <div class="col-md-12 mt-3">
                            <BaseInput v-model="referralForm.notes" label="Notes about Order" type="textarea"
                                placeholder="Enter notes" @click="getDateMeta('orders_referral_notes')" :error="referralErrors.notes" />
                        </div>
                        <div class="col-md-12 mt-3">
                            <BaseSelect v-model="referralForm.action_after_saving" label="Action After Saving" :error="referralErrors.action_after_saving">
                                <option value="" disabled selected>Select Action</option>
                                <option v-for="option in actionOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </BaseSelect>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" @click="upsertReferral()" class="btn btn-sm btn-primary" :disabled="referralLoading">
                            <span v-if="referralLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            {{ referralLoading ? 'Saving...' : 'Save' }}
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" :disabled="referralLoading">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
     <Modal :isOpen="isOpenRadiologyProviderModal" title="Add Radiology Provider" size="xl" @close="closeRadiologyProviderModal">
        <LabProviderModal :labCategory="data.LabCategories" @close="closeRadiologyProviderModal"/>
    </Modal>
    <Modal :isOpen="isOpenCardiopulmonaryProviderModal" title="Add Cardiopulmonary Provider" size="xl" @close="closeCardiopulmonaryProviderModal">
        <LabProviderModal :labCategory="data.LabCategories" @close="closeCardiopulmonaryProviderModal"/>
    </Modal>
      <Modal :isOpen="isOpenLaboratoryProviderModal" title="Add Laboratory Provider" size="xl" @close="closeLaboratoryProviderModal" >
            <LabProviderModal  :labCategory="data.LabCategories"  @close="closeLaboratoryProviderModal"/>
        </Modal>
        <Modal :isOpen="isOpenReferralProviderModal" title="Add Referral Provider" size="xl" @close="closeReferralProviderModal">
        <LabProviderModal :labCategory="data.LabCategories"  @close="closeReferralProviderModal"/>

        </Modal>
</template>
