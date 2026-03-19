<script setup>
import { ref, onMounted, watch } from "vue";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import BaseDatePicker from "@/Components/Common/Input/BaseDatePicker.vue";
import { useForm } from "@inertiajs/vue3";
import axios from "axios";
import Swal from 'sweetalert2/dist/sweetalert2.js';

const props = defineProps({
    mainForm: Object,
    data: Object,
    weight: Number,
});

const form = useForm({
    id: "",
    encounter_id: props.data.encounter.id ?? props.data.encounter.id ?? null,
    pharmacy_id: props.data?.pharmacies?.[0]?.id || "",
    rx: "",
    supplements: "",
    immunizations: "",
    orders_summary: "",
    supplements_orders_summary: "",
    prescription: "",
    medication: "",
    dosage: "",
    dosage_unit: "",
    sig: "",
    route: "",
    frequency: "",
    instructions: "",
    quantity: "",
    refill: "",
    reason: "",
    date_inactive: "",
    date_active: "",
    date_old: "",
    provider: "",
    dea: "",
    daw: "",
    license: "",
    days: "",
    due_date: "",
    rcopia_sync: "",
    national_drug_code: "",
    reconcile: "",
    json: "",
    transaction: "",
    notification: "",
    action_after_saving: "sign",
});

const dosageCalc = ref({
    dosage: "",
    dose_unit: "mg/kg/d",
    weight: "",
    frequency: "daily",
    amount: "",
    amount_unit: "",
    per_volume: "",
    per_volume_unit: "",
    dose: "",
    dose_calc_unit: "",
    liquid_dose: "",
    liquid_dose_unit: "",
});

const prescriptions = ref([]);
const addPrescription = () => {

    axios.post(route('doctor.upsert.prescription'), form).then(response => {
        prescriptions.value = response.data;
        const lastPrescription = prescriptions.value[prescriptions.value.length - 1];

        if (form.action_after_saving == 'print') {

            window.location.href = route('doctor.downloadPrescriptionPdf', lastPrescription.id);
        }
        toast('the prescription has been saved successfully!');
        form.reset();
    });
};

const dosageUnits = [
    { value: "mg", label: "mg" },
    { value: "ml", label: "mL" },
    { value: "g", label: "g" },
];

const routes = [
    { value: "oral", label: "Oral route" },
    { value: "iv", label: "IV" },
    { value: "im", label: "IM" },
];

const frequencies = [
    { value: "daily", label: "Daily", selected: true },
    { value: "twice_daily", label: "Twice Daily" },
    { value: "as_needed", label: "As Needed (PRN)" },
    { value: "every_4_hours", label: "Every 4 Hours" },
    { value: "every_6_hours", label: "Every 6 Hours" },
];

const frequencyMap = {
    daily: 1,
    twice_daily: 2,
    every_4_hours: 6,
    every_6_hours: 4,
    as_needed: 0, // or handle separately
};

const pharmacies = [
    { value: "ortho", label: "Ortho Medicines" },
    { value: "city", label: "City Pharmacy" },
];

const actionOptions = [
    { value: "sign", label: "Electronically Sign" },
    { value: "print", label: "Print Prescription" },
    { value: "print_queue", label: "Add to Print Queue" }
];

const deletePrescription = (id) => {

    Swal.fire(confirmSettings('Are you sure to delete this data?', 'You want be get it back')).then((result) => {
        if (result.isConfirmed) {
            axios.post(route('doctor.delete.prescription', id)).then(response => {
                prescriptions.value = response.data;
                toast('the prescription has been delete successfully!');
            });
        }
    });
}

watch(() => form.pharmacy_id, (newPharmacyId) => {
    if (newPharmacyId) {
        const pharmacy = props.data.pharmacies.find(p => p.id == newPharmacyId);
        if (pharmacy && pharmacy.email) {
            form.notification = pharmacy.email;
        }
    }
});

onMounted(() => {
    if (props.mainForm.id) {
        axios.get(route('doctor.get.prescriptions', props.mainForm.id)).then(response => {
            prescriptions.value = response.data;
        });
    }
    dosageCalc.value.weight = props.weight || '';
})


function parseUnit(str) {
    if (!str) return { multiplier: 1, offset: 0, label: "" };
    const p = str.split("|");
    return {
        multiplier: parseFloat(p[0]) || 1,
        offset: parseFloat(p[1]) || 0,
        label: p[2] || ""
    };
}
// --- MAIN DOSAGE CALCULATION ---
function calculateDosage() {
    let doCalc = true;

    // Check for commas in dosage
    if (dosageCalc.value.dosage && dosageCalc.value.dosage.toString().indexOf(',') >= 0) {
        toast('Please avoid using commas in the dosage field.');
        dosageCalc.value.dosage = '';
        doCalc = false;
    }

    // Calculate Dosage
    let param_value = parseFloat(dosageCalc.value.dosage);
    if (isNaN(param_value)) {
        param_value = '';
        doCalc = false;
    }

    // Parse dosage unit (format: multiplier|offset|label)
    let unit_parts = parseUnit(dosageCalc.value.dose_unit);
    let Dosage = param_value * unit_parts.multiplier + unit_parts.offset;

    // Check weight
    if (!props.data?.recent_weight || props.data.recent_weight === '') {
        toast('Please provide a valid weight.');
        doCalc = false;
    }

    // Calculate Weight in kg
    let weightParam = parseFloat(props.data?.recent_weight || 0);
    let unit = 1;
    if (props.data?.weight_unit !== 'kg') {
        unit = 0.45359237; // lbs to kg conversion
    }
    let Weight = weightParam * unit;

    // Check medication amount for commas
    if (dosageCalc.value.amount && dosageCalc.value.amount.toString().indexOf(',') >= 0) {
        toast('Please avoid using commas in the medication amount field.');
        dosageCalc.value.amount = '';
        doCalc = false;
    }

    param_value = parseFloat(dosageCalc.value.amount);
    unit_parts = parseUnit(dosageCalc.value.amount_unit);
    let Med_Amount = param_value * unit_parts.multiplier + unit_parts.offset;

    // Check medication volume for commas
    if (dosageCalc.value.per_volume && dosageCalc.value.per_volume.toString().indexOf(',') >= 0) {
        toast('Please avoid using commas in the medication volume field.');
        dosageCalc.value.per_volume = '';
        doCalc = false;
    }

    param_value = parseFloat(dosageCalc.value.per_volume);
    unit_parts = parseUnit(dosageCalc.value.per_volume_unit);
    let Per_Volume = param_value * unit_parts.multiplier + unit_parts.offset;

    // Get frequency (map to numeric value)
    let Frequency = frequencyMap[dosageCalc.value.frequency] || 1;
    if (Frequency <= 0) {
        doCalc = false;
    }

    // Calculate dose
    let Dose = Weight * Dosage / Frequency;
    unit_parts = parseUnit(dosageCalc.value.dose_calc_unit);

    if (doCalc && !isNaN(Dose) && isFinite(Dose)) {
        dosageCalc.value.dose = ((Dose - unit_parts.offset) / unit_parts.multiplier).toFixed(2);
    } else {
        dosageCalc.value.dose = '';
    }

    // Calculate liquid dose
    let Liquid_Dose = Dose * Per_Volume / Med_Amount;
    unit_parts = parseUnit(dosageCalc.value.liquid_dose_unit);

    if (doCalc && !isNaN(Liquid_Dose) && isFinite(Liquid_Dose)) {
        dosageCalc.value.liquid_dose = ((Liquid_Dose - unit_parts.offset) / unit_parts.multiplier).toFixed(2);
    } else {
        dosageCalc.value.liquid_dose = '';
    }

    // Final cleanup
    if (isNaN(dosageCalc.value.liquid_dose)) {
        dosageCalc.value.liquid_dose = '';
    }
    if (isNaN(dosageCalc.value.dose)) {
        dosageCalc.value.dose = '';
    }
}

/* --------------------------------------------------
   Watchers = replace your jQuery ".docalc" and ".docalcblur"
--------------------------------------------------- */
watch(
    () => ({
        d: dosageCalc.value.dosage,
        du: dosageCalc.value.dose_unit,
        w: dosageCalc.value.weight,
        f: dosageCalc.value.frequency,
        a: dosageCalc.value.amount,
        au: dosageCalc.value.amount_unit,
        pv: dosageCalc.value.per_volume,
        pvu: dosageCalc.value.per_volume_unit,
    }),
    () => calculateDosage()
);
     

/* --------------------------------------------------
   Equivalent of #dosage_ok click
--------------------------------------------------- */
function applyDosageToForm() {
    // prefer liquid dose when available
    if (dosageCalc.value.liquid_dose) {
        const u = parseUnit(dosageCalc.value.liquid_dose_unit);
        form.sig = `${dosageCalc.value.liquid_dose} ${u.label}`;
    } else {
        const u = parseUnit(dosageCalc.value.dose_calc_unit);
        form.sig = `${dosageCalc.value.dose} ${u.label}`;
    }

    // frequency label
    const freq = frequencies.find(f => f.value === dosageCalc.value.frequency);
    form.frequency = freq ? freq.label : "";
}


</script>

<template>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title text-white">New Prescription</h6>
                  </div>
                <div class="card-body">
                     <template v-for="prescription in prescriptions">

                        <div class="p-2 border-bottom bg-color-white-lilac cursor-pointer mt-2">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <div class="mb-0">
                                        <span class="badge mr-2">{{ prescription.medication }}</span>
                                        <span class="badge mr-2">{{ prescription.dosage }}</span>
                                        <span class="badge mr-2">{{ prescription.dosage_unit }}</span>
                                        <span class="badge mr-2">{{ prescription.frequency }}</span>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <button class="btn btn-danger" type="button" title="Delete"
                                        @click="deletePrescription(prescription.id)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <form @submit.prevent="addPrescription" class="mt-4">
                        <div class="row">

                            <div class="col-md-6">
                                <BaseInput v-model="form.medication" label="Medication" required />
                            </div>

                            <div class="col-md-6">
                                <BaseInput v-model="form.dosage" label="Dosage" required />
                            </div>

                            <div class="col-md-6">
                                <BaseSelect v-model="form.dosage_unit" label="Dosage Unit" required>
                                    <option v-for="option in dosageUnits" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </BaseSelect>
                            </div>

                            <div class="col-md-6">
                                <BaseInput v-model="form.sig" label="Sig" placeholder="Usage instructions" />
                            </div>

                            <div class="col-md-6">
                                <BaseSelect v-model="form.route" label="Route">
                                    <option v-for="option in routes" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </BaseSelect>
                            </div>

                            <div class="col-md-6">
                                <BaseSelect v-model="form.frequency" label="Frequency">
                                    <option v-for="freq in frequencies" :key="freq.value" :value="freq.value"
                                        :selected="freq.selected">
                                        {{ freq.label }}
                                    </option>
                                </BaseSelect>
                            </div>

                            <div class="col-md-6">
                                <BaseInput v-model="form.instructions" label="Special Instructions" />
                            </div>

                            <div class="col-md-6">
                                <BaseInput v-model="form.reason" label="Reason" />
                            </div>

                            <div class="col-md-6">
                                <BaseDatePicker v-model="form.date_active" label="Date Active" />
                            </div>

                            <div class="col-md-6">
                                <BaseInput v-model="form.days" label="Duration (days)" type="number" />
                            </div>

                            <div class="col-md-6">
                                <BaseInput v-model="form.quantity" label="Quantity" type="number" />
                            </div>

                            <div class="col-md-6">
                                <BaseInput v-model="form.refill" label="Refills" type="number" />
                            </div>

                            <div class="col-md-6">
                                <BaseSelect v-model="form.pharmacy_id" label="Pharmacy to Send">
                                    <option v-for="pharmacy in data.pharmacies" :key="pharmacy.id" :value="pharmacy.id">
                                        {{ pharmacy.name }}
                                    </option>
                                </BaseSelect>
                            </div>

                            <div class="col-md-6">
                                <BaseInput v-model="form.notification" label="Notification To (SMS or Email)" />
                            </div>

                            <div class="col-md-6">
                                <BaseSelect v-model="form.action_after_saving" label="Action After Saving">
                                    <option v-for="option in actionOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </BaseSelect>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-primary" @click="addPrescription()" :disabled="!form.encounter_id">
                                Save
                            </button>
                            <button type="button" class="btn btn-danger" @click="form.reset()">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Dosage Calculator -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h6 class="text-white">Dosage Calculator</h6>
                    <button class="btn btn-sm btn-success" @click="applyDosageToForm">OK</button>
                </div>
                <div class="card-body">

                    <!-- Dosage -->
                    <BaseInput v-model="dosageCalc.dosage" label="Dosage" id="calc_dosage" class="docalcblur" />

                    <!-- Dosage Unit -->
                    <BaseSelect v-model="dosageCalc.dose_unit" label="Dose Unit" id="calc_dosage_unit" class="docalc">
                        <option value="gm/kg/d">gm/kg/d</option>
                        <option value="mcg/kg/d">mcg/kg/d</option>
                        <option value="mg/kg/d">mg/kg/d</option>
                    </BaseSelect>

                    <!-- Weight Display -->
                    <div class="form-group d-flex align-items-center gap-2">
                        <label class="form-label my-3">Weight:</label>
                        <p class="form-control-static">
                            <span id="calc_weight_span">
                                {{recent_weight }} {{weight_unit }}
                            </span>
                        </p>
                    </div>

                    <!-- Frequency -->
                    <BaseSelect v-model="dosageCalc.frequency" label="Frequency" id="calc_frequency" class="docalc">
                        <option v-for="freq in frequencies" :key="freq.value" :value="freq.value">
                            {{ freq.label }}
                        </option>
                    </BaseSelect>

                    <!-- Medication Amount -->
                    <BaseInput v-model="dosageCalc.amount" label="Amount" id="calc_med_amount" class="docalcblur" />

                    <BaseSelect v-model="dosageCalc.amount_unit" label="Amount Unit" id="calc_med_amount_unit"
                        class="docalc">
                        <option value="1000|0|gm">gm</option>
                        <option value="0.001|0|mcg">mcg</option>
                        <option value="1|0|mg">mg</option>
                    </BaseSelect>

                    <!-- Per Volume -->
                    <BaseInput v-model="dosageCalc.per_volume" id="calc_med_volume" label="Per Volume"
                        class="docalcblur" />

                    <BaseSelect v-model="dosageCalc.per_volume_unit" label="Per Volume Unit" id="calc_med_volume_unit"
                        class="docalc">
                        <option value="L">L</option>
                        <option value="mL">mL</option>
                    </BaseSelect>

                    <!-- Dose (Final Calculated Dose) -->
                    <BaseInput v-model="dosageCalc.dose" label="Dose" id="calc_dose" readonly />

                    <BaseSelect v-model="dosageCalc.dose_calc_unit" label="Dose Unit" id="calc_dose_unit"
                        class="docalc">
                        <option value="gm">gm</option>
                        <option value="mcg">mcg</option>
                        <option value="mg">mg</option>
                    </BaseSelect>

                    <!-- Liquid Dose -->
                    <BaseInput v-model="dosageCalc.liquid_dose" label="Liquid Dose" id="calc_liquid_dose" />

                    <BaseSelect v-model="dosageCalc.liquid_dose_unit" label="Liquid Dose Unit"
                        id="calc_liquid_dose_unit" class="docalc">
                        <option value="L">L</option>
                        <option value="mL">mL</option>
                    </BaseSelect>

                </div>

            </div>
        </div>
    </div>
</template>
