<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { ref } from "vue";

const props = defineProps({
    data: Object,
});

  
const encounterForm = useForm({

    /* encounters */
    id: props.data?.encounter?.id,
    patient_id: usePage().props?.selected_patient?.id,
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

const print = () => {
    window.print();
};
</script>

<template>
<AuthLayout title="View Encounter" description="View patient encounter details" heading="View Encounter">
        <div class="mb-2 d-print-none">
            <div class="d-flex justify-content-end gap-2">
                <button @click="print()" class="btn btn-danger px-4 d-flex gap-2 align-items-center">
                    <i class="bi bi-printer-fill"></i>
                    Print
                </button>
            </div>
        </div>

        <div class="container-fluid-bkp">
            <div class="card mb-3">
                <div class="card-body">
                   
                    <h4 class="mb-3">Encounter Print Preview</h4>

                    <div class="row mb-2">
                        <div class="col-md-6"><strong>Encounter #:</strong>
                            {{ encounterForm.id ?? '—' }}
                        </div>
                        <div class="col-md-6"><strong>Date of Service:</strong>
                            {{ encounterForm.encounter_date_of_service ?? '—' }}
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-6"><strong>Patient Name:</strong>
                            {{ data.encounter?.patient?.name ?? data.encounter?.patient?.user?.name ?? '—' }}
                        </div>
                        <div class="col-md-6"><strong>Doctor Name:</strong>
                            {{ data.encounter?.doctor?.name ?? data.encounter?.doctor?.user?.name ?? '—' }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Chief Complaint:</strong>
                            {{ encounterForm.chief_complaint ?? '—' }}
                        </div>
                        <div class="col-md-6"><strong>Encounter Location:</strong>
                            {{ data.locations[data.encounter.encounter_location]?.name ?? '—' }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Associated Appointment:</strong>
                            <template v-if="data.encounter?.appointment">
                                {{ data.encounter?.appointment?.patient?.user?.name }},
                                Mobile: {{ data.encounter?.appointment?.patient?.user?.mobile }},
                                Time:{{ data.encounter?.appointment?.appointment_date }} -
                                {{ data.encounter?.appointment?.appointment_time }}
                            </template><template v-else>-</template>
                        </div>
                        <div class="col-md-6"><strong>Provider Role:</strong>
                            {{ data.encounter.encounter_role ?? '—' }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Complexity of Encounter:</strong>
                            {{ data.encounter.complexity_of_encounter ?? '—' }}
                        </div>
                        <div class="col-md-6"><strong>Referring Provider:</strong>
                            {{ data.encounter.referring_provider ?? '—' }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Condition Related To Work:</strong>
                            {{ data.encounter.encounter_condition_work ?? '—' }}
                        </div>
                        <div class="col-md-6"><strong>Condition Related To Motor Vehicle Accident:</strong>
                            {{ data.encounter.encounter_condition_auto ?? '—' }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6"><strong>State Where Motor Vehicle Accident Occurred:</strong>
                            {{ data.encounter.encounter_condition_auto_state ?? '—' }}
                        </div>
                        <div class="col-md-6"><strong>Condition Related To Other Accident:</strong>
                            {{ data.encounter.encounter_condition_other ?? '—' }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12"><strong>Other Condition:</strong>
                            <span class="bg-light pl-2 pr-2">{{ data.encounter.encounter_condition ?? '—' }}</span>
                        </div>
                    </div>
                    <hr />

                    <h5 class="mb-2">History of Present Illness (HPI)</h5>
                    <pre class="p-2 bg-light">{{ encounterForm.hpi ?? '—' }}</pre>

                    <h5 class="mt-3 mb-2">Review of Systems</h5>
                    <pre class="p-2 bg-light">
                        {{ encounterForm.ros ?? '—' }}
                    </pre>

                    <h5 class="mt-3 mb-2">Vital Signs</h5>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-borderless d-none d-md-table">
                            <tbody>
                                <tr>
                                    <td><strong>Date</strong></td>
                                    <td>{{ encounterForm.vital_date ?? '—' }}</td>
                                    <td><strong>Age</strong></td>
                                    <td>{{ encounterForm.age ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Weight</strong></td>
                                    <td>{{ encounterForm.weight ?? '—' }}</td>
                                    <td><strong>Height</strong></td>
                                    <td>{{ encounterForm.height ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>BMI</strong></td>
                                    <td>{{ encounterForm.bmi ?? '—' }}</td>
                                    <td><strong>BP</strong></td>
                                    <td>
                                        {{ encounterForm.bp_systolic ?? '—' }}/{{ encounterForm.bp_diastolic ?? '—' }}
                                        ({{ encounterForm.bp_position ?? '' }})
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Pulse</strong></td>
                                    <td>{{ encounterForm.pulse ?? '—' }}</td>
                                    <td><strong>Respirations</strong></td>
                                    <td>{{ encounterForm.respirations ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>O2 Sat</strong></td>
                                    <td>{{ encounterForm.o2_saturation ?? '—' }}</td>
                                    <td><strong>Other Vitals</strong></td>
                                    <td>{{ encounterForm.vitals_other ?? '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- Mobile Card View -->
                        <div class="mobile-table d-md-none">
                            <div class="mobile-row-card">
                                <div class="mobile-row-item">
                                    <span class="mobile-label">Date</span>
                                    <span class="mobile-value">{{ encounterForm.vital_date ?? '—' }}</span>
                                </div>
                                <div class="mobile-row-item">
                                    <span class="mobile-label">Age</span>
                                    <span class="mobile-value">{{ encounterForm.age ?? '—' }}</span>
                                </div>
                                <div class="mobile-row-item">
                                    <span class="mobile-label">Weight</span>
                                    <span class="mobile-value">{{ encounterForm.weight ?? '—' }}</span>
                                </div>
                                <div class="mobile-row-item">
                                    <span class="mobile-label">Height</span>
                                    <span class="mobile-value">{{ encounterForm.height ?? '—' }}</span>
                                </div>
                                <div class="mobile-row-item">
                                    <span class="mobile-label">BMI</span>
                                    <span class="mobile-value">{{ encounterForm.bmi ?? '—' }}</span>
                                </div>
                                <div class="mobile-row-item">
                                    <span class="mobile-label">BP</span>
                                    <span class="mobile-value">
                                        {{ encounterForm.bp_systolic ?? '—' }}/{{ encounterForm.bp_diastolic ?? '—' }}
                                        ({{ encounterForm.bp_position ?? '' }})
                                    </span>
                                </div>
                                <div class="mobile-row-item">
                                    <span class="mobile-label">Pulse</span>
                                    <span class="mobile-value">{{ encounterForm.pulse ?? '—' }}</span>
                                </div>
                                <div class="mobile-row-item">
                                    <span class="mobile-label">Respirations</span>
                                    <span class="mobile-value">{{ encounterForm.respirations ?? '—' }}</span>
                                </div>
                                <div class="mobile-row-item">
                                    <span class="mobile-label">O2 Sat</span>
                                    <span class="mobile-value">{{ encounterForm.o2_saturation ?? '—' }}</span>
                                </div>
                                <div class="mobile-row-item">
                                    <span class="mobile-label">Other Vitals</span>
                                    <span class="mobile-value">{{ encounterForm.vitals_other ?? '—' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h5 class="mt-3 mb-2">Physical Examination</h5>
                    <pre class="p-2 bg-light">{{ encounterForm.pe ?? '—' }}</pre>

                    <h5 class="mt-3 mb-2">Assessment</h5>
                    <div class="mb-2">
                        <div class="mt-2"><strong>Additional Diagnoses:</strong></div>
                        <div class="p-2 bg-light">
                            {{ encounterForm.assessment_other ?? '—' }}
                        </div>

                        <div class="mt-2"><strong>Differential Diagnoses:</strong></div>
                        <div class="p-2 bg-light">
                            {{ encounterForm.differential_diagnoses ?? '—' }}
                        </div>

                        <div class="mt-2"><strong>Assessment Discussion:</strong></div>
                        <div class="p-2 bg-light">
                            {{ encounterForm.assessment_discussion ?? '—' }}
                        </div>
                    </div>

                    <h5 class="mt-3 mb-2">Plan</h5>
                    <div class="mb-2">
                        <!-- <div><strong>Date:</strong> {{ encounterForm.plan_date ?? '—' }}</div> -->
                        <div class="mt-2"><strong>Plan Recommendations:</strong></div>
                        <pre class="p-2 bg-light">{{ encounterForm.plan ?? '—' }}</pre>
                        <div class="mt-2">
                            <div class="ms-2">
                                <div><strong>Duration:</strong>
                                    <span class="bg-light pl-2 pr-2">{{ encounterForm.duration ?? '—' }} minutes</span>
                                </div>
                                <div><strong>Follow-up:</strong>
                                    <span class="bg-light pl-2 pr-2">{{ encounterForm.followup ?? '—' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr />

                    <h5 class="mt-3 mb-2">Prescriptions</h5>
                    <div class="mb-2">
                        {{ data.encounter.prescriptions.length > 0 ? '' : 'No data available.' }}
                        <div class="mb-3">
                            <table class="table table-sm mb-2">
                                <tbody>
                                    <tr>
                                        <th>Medication Name</th>
                                        <th>Dosage</th>
                                        <th>Dosage Unit</th>
                                        <th>Frequency</th>
                                        <th>Duration</th>
                                        <th>Instructions</th>
                                        <th>Reason</th>
                                        <th>Date active</th>
                                    </tr>
                                    <tr v-for="prescription in data.encounter.prescriptions" :key="prescription.id">
                                        <td>{{ prescription.medication ?? '—' }}</td>
                                        <td>{{ prescription.dosage ?? '—' }}</td>
                                        <td>{{ prescription.dosage_unit ?? '—' }}</td>
                                        <td>{{ prescription.frequency ?? '—' }}</td>
                                        <td>{{ prescription.frequency ?? '—' }}</td>
                                        <td>{{ prescription.instructions ?? '—' }}</td>
                                        <td>{{ prescription.reason ?? '—' }}</td>
                                        <td>{{ prescription.date_active ?? '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <hr />

                    <h5 class="mt-3 mb-2">Supplements</h5>
                    <div class="mb-2">
                        {{ data.encounter.supplements.length > 0 ? '' : 'No data available.' }}
                        <div class="mb-3">
                            <table class="table table-sm mb-2">
                                <tbody>
                                    <tr>
                                        <th>Supplement</th>
                                        <th>Dosage</th>
                                        <th>Dosage Unit</th>
                                        <th>Frequency</th>
                                        <th>Instructions</th>
                                        <th>Reason</th>
                                        <th>Date active</th>
                                    </tr>
                                    <tr v-for="supplement in data.encounter.supplements" :key="supplement.id">
                                        <td>{{ supplement.supplement ?? '—' }}</td>
                                        <td>{{ supplement.dosage ?? '—' }}</td>
                                        <td>{{ supplement.dosage_unit ?? '—' }}</td>
                                        <td>{{ supplement.frequency ?? '—' }}</td>
                                        <td>{{ supplement.instructions ?? '—' }}</td>
                                        <td>{{ supplement.reason ?? '—' }}</td>
                                        <td>{{ supplement.date_active ?? '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <hr />

                    <h5 class="mt-3 mb-2">Lab orders</h5>
                    <div class="mb-2">
                        {{ data.encounter.lab_orders.length > 0 ? '' : 'No data available.' }}
                        <div class="mb-3">
                            <table class="table table-sm mb-2">
                                <tbody>
                                    <tr>
                                        <th>Insurance</th>
                                        <th>Order</th>
                                        <th>Code</th>
                                        <th>Note</th>
                                    </tr>
                                    <tr v-for="supplement in data.encounter.lab_orders" :key="supplement.id">
                                        <td>{{ supplement.insurance ?? '—' }}</td>
                                        <td>{{ supplement.labs ?? '—' }}</td>
                                        <td>{{ supplement.labs_icd ?? '—' }}</td>
                                        <td>{{ supplement.notes ?? '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <hr />

                    <h5 class="mt-3 mb-2">Imaging orders</h5>
                    <div class="mb-2">
                        {{ data.encounter.radiology_orders.length > 0 ? '' : 'No data available.' }}
                        <div class="mb-3">
                            <table class="table table-sm mb-2">
                                <tbody>
                                    <tr>
                                        <th>Insurance</th>
                                        <th>Order</th>
                                        <th>Code</th>
                                        <th>Note</th>
                                    </tr>
                                    <tr v-for="order in data.encounter.radiology_orders" :key="data.id">
                                        <td>{{ order.insurance ?? '—' }}</td>
                                        <td>{{ order.radiology ?? '—' }}</td>
                                        <td>{{ order.radiology_icd ?? '—' }}</td>
                                        <td>{{ order.notes ?? '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <hr />

                    <h5 class="mt-3 mb-2">Cardiopulmonary orders</h5>
                    <div class="mb-2">
                        {{ data.encounter.card_orders.length > 0 ? '' : 'No data available.' }}
                        <div class="mb-3">
                            <table class="table table-sm mb-2">
                                <tbody>
                                    <tr>
                                        <th>Insurance</th>
                                        <th>Order</th>
                                        <th>Code</th>
                                        <th>Note</th>
                                    </tr>
                                    <tr v-for="order in data.encounter.card_orders" :key="order.id">
                                        <td>{{ order.insurance ?? '—' }}</td>
                                        <td>{{ order.cp ?? '—' }}</td>
                                        <td>{{ order.cp_icd ?? '—' }}</td>
                                        <td>{{ order.notes ?? '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <hr />

                    <h5 class="mt-3 mb-2">Anatomical Image</h5>
                    <div class="mb-2">
                        {{ data.encounter.images.length > 0 ? '' : 'No data available.' }}
                        <div class="mb-3">
                            <table class="table table-sm mb-2">
                                <tbody>
                                    <tr>
                                        <th>description</th>
                                        <th class="text-right">Image</th>
                                    </tr>
                                    <tr v-for="image in data.encounter.images" :key="image.id">
                                        <td>{{ image.description ?? '—' }}</td>
                                        <td class="float-end">
                                            <img :src="image.url" alt="Anatomical Image" width="100" />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <hr />

                    <h5 class="mt-3 mb-2">Photos</h5>
                    <div class="mb-2">
                        {{ data.encounter.photos.length > 0 ? '' : 'No data available.' }}
                        <div class="mb-3">
                            <table class="table table-sm mb-2">
                                <tbody>
                                    <tr>
                                        <th>description</th>
                                        <th class="text-right">Photo</th>
                                    </tr>
                                    <tr v-for="image in data.encounter.photos" :key="image.id">
                                        <td>{{ image.description ?? '—' }}</td>
                                        <td class="float-end">
                                            <img :src="image.url" alt="Anatomical Image" width="100" />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <hr />

                    <h5 class="mt-3 mb-2">Procedure</h5>
                    <div class="mb-2">
                        {{ data.encounter.procedures.length > 0 ? '' : 'No data available.' }}
                        <div class="mb-3">
                            <table class="table table-sm mb-2">
                                <tbody>
                                    <tr>
                                        <th style="width:30%">Type</th>
                                        <th style="width:30%">Code</th>
                                        <th style="width:40%">Description</th>
                                    </tr>
                                    <tr v-for="procedure in data.encounter.procedures" :key="procedure.id">
                                        <td>{{ procedure.type ?? '—' }}</td>
                                        <td>{{ procedure?.cpt ?? '—' }}</td>
                                        <td>{{ procedure.description ?? '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <hr />

                    <h5 class="mt-3 mb-2">Billing</h5>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-borderless d-none d-md-table">
                            <tbody>
                                <tr>
                                    <td><strong>Procedure Code</strong></td>
                                    <td>{{ data.encounter.billing_core?.cpt ?? '—' }}</td>
                                    <td><strong>Procedure Charge</strong></td>
                                    <td>{{ data.encounter.billing_core?.cpt_charge ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Unit(s)</strong></td>
                                    <td>{{ data.encounter.billing_core?.unit ?? '—' }}</td>
                                    <td><strong>Modifier</strong></td>
                                    <td>{{ data.encounter.billing_core?.modifier ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Date of Service From</strong></td>
                                    <td>{{ data.encounter.billing_core?.service_start ?? '—' }}</td>
                                    <td><strong>Date of Service To</strong></td>
                                    <td>{{ data.encounter.billing_core?.service_end ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Diagnosis Pointer</strong></td>
                                    <td>{{ data.encounter.billing_core?.icd_pointer ?? '—' }}</td>
                                    <td><strong></strong></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td><strong>Primary Insurance</strong></td>
                                    <td>{{ data.encounter.billing?.insurance_id_1 ?? '—' }}</td>
                                    <td><strong>Secondary Insurance</strong></td>
                                    <td>{{ data.encounter.billing?.insurance_id_2 ?? '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- Mobile Card View -->
                        <div class="mobile-table d-md-none">
                            <div class="mobile-row-card">
                                <div class="mobile-row-item">
                                    <span class="mobile-label">Procedure Code</span>
                                    <span class="mobile-value">{{ data.encounter.billing_core?.cpt ?? '—' }}</span>
                                </div>
                                <div class="mobile-row-item">
                                    <span class="mobile-label">Procedure Charge</span>
                                    <span class="mobile-value">{{ data.encounter.billing_core?.cpt_charge ?? '—' }}</span>
                                </div>
                                <div class="mobile-row-item">
                                    <span class="mobile-label">Unit(s)</span>
                                    <span class="mobile-value">{{ data.encounter.billing_core?.unit ?? '—' }}</span>
                                </div>
                                <div class="mobile-row-item">
                                    <span class="mobile-label">Modifier</span>
                                    <span class="mobile-value">{{ data.encounter.billing_core?.modifier ?? '—' }}</span>
                                </div>
                                <div class="mobile-row-item">
                                    <span class="mobile-label">Date of Service From</span>
                                    <span class="mobile-value">{{ data.encounter.billing_core?.service_start ?? '—' }}</span>
                                </div>
                                <div class="mobile-row-item">
                                    <span class="mobile-label">Date of Service To</span>
                                    <span class="mobile-value">{{ data.encounter.billing_core?.service_end ?? '—' }}</span>
                                </div>
                                <div class="mobile-row-item">
                                    <span class="mobile-label">Diagnosis Pointer</span>
                                    <span class="mobile-value">{{ data.encounter.billing_core?.icd_pointer ?? '—' }}</span>
                                </div>
                                <div class="mobile-row-item">
                                    <span class="mobile-label">Primary Insurance</span>
                                    <span class="mobile-value">{{ data.encounter.billing?.insurance_id_1 ?? '—' }}</span>
                                </div>
                                <div class="mobile-row-item">
                                    <span class="mobile-label">Secondary Insurance</span>
                                    <span class="mobile-value">{{ data.encounter.billing?.insurance_id_2 ?? '—' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr />

                    <h5 class="mt-3 mb-2">Referral</h5>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-borderless d-none d-md-table">
                            <tbody>
                                <tr>
                                    <td><strong>Referral Details</strong></td>
                                    <td>{{ data.encounter.referral?.detail ?? '—' }}</td>
                                    <td><strong>Diagnosis Codes</strong></td>
                                    <td>{{ data.encounter.referral?.code ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Select Specialty</strong></td>
                                    <td>{{ data.encounter.referral?.specialty ?? '—' }}</td>
                                    <td><strong>Referral Provider</strong></td>
                                    <td>{{ data.encounter.referral?.doctor?.name ?? data.encounter.referral?.doctor ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Order Pending Date</strong></td>
                                    <td>{{ data.encounter.referral?.pending_date ?? '—' }}</td>
                                    <td><strong>Insurance</strong></td>
                                    <td>{{ data.encounter.referral?.insurance ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Notes about Order</strong></td>
                                    <td>{{ data.encounter.referral?.note ?? '—' }}</td>
                                    <td><strong></strong></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- Mobile Card View -->
                        <div class="mobile-table d-md-none">
                            <div class="mobile-row-card">
                                <div class="mobile-row-item">
                                    <span class="mobile-label">Referral Details</span>
                                    <span class="mobile-value">{{ data.encounter.referral?.detail ?? '—' }}</span>
                                </div>
                                <div class="mobile-row-item">
                                    <span class="mobile-label">Diagnosis Codes</span>
                                    <span class="mobile-value">{{ data.encounter.referral?.code ?? '—' }}</span>
                                </div>
                                <div class="mobile-row-item">
                                    <span class="mobile-label">Select Specialty</span>
                                    <span class="mobile-value">{{ data.encounter.referral?.specialty ?? '—' }}</span>
                                </div>
                                <div class="mobile-row-item">
                                    <span class="mobile-label">Referral Provider</span>
                                    <span class="mobile-value">{{ data.encounter.referral?.doctor?.name ?? data.encounter.referral?.doctor?.user?.name ?? '—' }}</span>
                                </div>
                                <div class="mobile-row-item">
                                    <span class="mobile-label">Order Pending Date</span>
                                    <span class="mobile-value">{{ data.encounter.referral?.pending_date ?? '—' }}</span>
                                </div>
                                <div class="mobile-row-item">
                                    <span class="mobile-label">Insurance</span>
                                    <span class="mobile-value">{{ data.encounter.referral?.insurance ?? '—' }}</span>
                                </div>
                                <div class="mobile-row-item">
                                    <span class="mobile-label">Notes about Order</span>
                                    <span class="mobile-value">{{ data.encounter.referral?.note ?? '—' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<style scoped>
/* Mobile Card View Styles */
@media (max-width: 1024px) {
    .mobile-table {
        display: block;
    }

    .mobile-row-card {
        background: #fff;
        border-radius: 12px;
        padding: 12px;
        margin-bottom: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .mobile-row-item {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        padding: 6px 0;
        border-bottom: 1px dashed #eee;
    }

    .mobile-row-item:last-child {
        border-bottom: none;
    }

    .mobile-label {
        font-weight: 600;
        color: #6c757d;
    }

    .mobile-value {
        text-align: right;
        word-break: break-word;
        max-width: 60%;
    }
}
</style>
