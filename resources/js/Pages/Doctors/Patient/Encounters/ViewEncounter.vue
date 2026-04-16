<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { computed } from "vue";

const props = defineProps({
    data: Object,
});

const encounterForm = useForm({
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
    hpi: props.data?.encounter?.patient_illness_history?.hpi || "",
    forms: props.data?.encounter?.patient_illness_history?.forms || "",
    situation: props.data?.encounter?.patient_illness_history?.situation || "",
    ros: props.data?.encounter?.review_of_system?.ros || "",
    ros_gen: props.data?.encounter?.review_of_system?.ros_gen || "",
    ros_eye: props.data?.encounter?.review_of_system?.ros_eye || "",
    ros_ent: props.data?.encounter?.review_of_system?.ros_ent || "",
    ros_resp: props.data?.encounter?.review_of_system?.ros_resp || "",
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
    pe: props.data?.encounter?.physical_examination?.pe || "",
    assessment_date: props.data?.encounter?.assessment?.assessment_date || "",
    icd: props.data?.encounter?.assessment?.icd || "",
    other: props.data?.encounter?.assessment?.other || "",
    assessment: props.data?.encounter?.assessment?.assessment || "",
    assessment_other: props.data?.encounter?.assessment?.assessment_other || "",
    differential_diagnoses: props.data?.encounter?.assessment?.differential_diagnoses || "",
    assessment_discussion: props.data?.encounter?.assessment?.assessment_discussion || "",
    plan_date: props.data?.encounter?.plan?.plan_date || "",
    plan: props.data?.encounter?.plan?.plan || "",
    duration: props.data?.encounter?.plan?.duration || "",
    followup: props.data?.encounter?.plan?.followup || "",
    goals: props.data?.encounter?.plan?.goals || "",
    tp: props.data?.encounter?.plan?.tp || "",
});

const print = () => {
    window.print();
};

const textOrFallback = (value, fallback = "Not recorded") =>
    value !== null && value !== undefined && String(value).trim() !== "" ? value : fallback;

const encounter = computed(() => props.data?.encounter || {});

const patientName = computed(
    () => encounter.value?.patient?.name || encounter.value?.patient?.user?.name || "Unknown patient"
);

const doctorName = computed(
    () => encounter.value?.doctor?.name || encounter.value?.doctor?.user?.name || "Unknown provider"
);

const encounterLocationName = computed(
    () => props.data?.locations?.[encounter.value?.encounter_location]?.name || "Not recorded"
);

const appointmentSummary = computed(() => {
    if (!encounter.value?.appointment) return "No appointment linked";

    const appointment = encounter.value.appointment;
    const parts = [
        appointment?.patient?.user?.name,
        appointment?.patient?.user?.mobile ? `Mobile: ${appointment.patient.user.mobile}` : null,
        appointment?.appointment_date && appointment?.appointment_time
            ? `${appointment.appointment_date} ${appointment.appointment_time}`
            : null,
    ].filter(Boolean);

    return parts.join(" • ");
});

const summaryCards = computed(() => [
    { label: "Encounter ID", value: textOrFallback(encounterForm.id, "Pending") },
    { label: "Date of Service", value: textOrFallback(encounterForm.encounter_date_of_service) },
    { label: "Patient", value: patientName.value },
    { label: "Provider", value: doctorName.value },
]);

const overviewItems = computed(() => [
    { label: "Chief Complaint", value: textOrFallback(encounterForm.chief_complaint) },
    { label: "Encounter Location", value: encounterLocationName.value },
    { label: "Associated Appointment", value: appointmentSummary.value },
    { label: "Provider Role", value: textOrFallback(encounterForm.encounter_role) },
    { label: "Complexity", value: textOrFallback(encounterForm.complexity_of_encounter) },
    { label: "Referring Provider", value: textOrFallback(encounterForm.referring_provider) },
    { label: "Condition Related To Work", value: textOrFallback(encounter.value?.encounter_condition_work) },
    { label: "Motor Vehicle Accident", value: textOrFallback(encounter.value?.encounter_condition_auto) },
    { label: "Accident State", value: textOrFallback(encounter.value?.encounter_condition_auto_state) },
    { label: "Other Accident", value: textOrFallback(encounter.value?.encounter_condition_other) },
    { label: "Other Condition", value: textOrFallback(encounter.value?.encounter_condition) },
]);

const vitalItems = computed(() => [
    { label: "Date", value: textOrFallback(encounterForm.vital_date) },
    { label: "Age", value: textOrFallback(encounterForm.age) },
    { label: "Weight", value: textOrFallback(encounterForm.weight) },
    { label: "Height", value: textOrFallback(encounterForm.height) },
    { label: "BMI", value: textOrFallback(encounterForm.bmi) },
    {
        label: "Blood Pressure",
        value: `${textOrFallback(encounterForm.bp_systolic, "—")}/${textOrFallback(encounterForm.bp_diastolic, "—")}${
            encounterForm.bp_position ? ` (${encounterForm.bp_position})` : ""
        }`,
    },
    { label: "Pulse", value: textOrFallback(encounterForm.pulse) },
    { label: "Respirations", value: textOrFallback(encounterForm.respirations) },
    { label: "O2 Saturation", value: textOrFallback(encounterForm.o2_saturation) },
    { label: "Temperature", value: textOrFallback(encounterForm.temperature) },
    { label: "Temperature Method", value: textOrFallback(encounterForm.temperature_method) },
    { label: "Other Vitals", value: textOrFallback(encounterForm.vitals_other) },
]);

const billingItems = computed(() => [
    { label: "Procedure Code", value: textOrFallback(encounter.value?.billing_core?.cpt) },
    { label: "Procedure Charge", value: textOrFallback(encounter.value?.billing_core?.cpt_charge) },
    { label: "Units", value: textOrFallback(encounter.value?.billing_core?.unit) },
    { label: "Modifier", value: textOrFallback(encounter.value?.billing_core?.modifier) },
    { label: "Service Start", value: textOrFallback(encounter.value?.billing_core?.service_start) },
    { label: "Service End", value: textOrFallback(encounter.value?.billing_core?.service_end) },
    { label: "Diagnosis Pointer", value: textOrFallback(encounter.value?.billing_core?.icd_pointer) },
    { label: "Primary Insurance", value: textOrFallback(encounter.value?.billing?.insurance_id_1) },
    { label: "Secondary Insurance", value: textOrFallback(encounter.value?.billing?.insurance_id_2) },
]);

const referralItems = computed(() => [
    { label: "Referral Details", value: textOrFallback(encounter.value?.referral?.detail) },
    { label: "Diagnosis Codes", value: textOrFallback(encounter.value?.referral?.code) },
    { label: "Specialty", value: textOrFallback(encounter.value?.referral?.specialty) },
    {
        label: "Referral Provider",
        value: textOrFallback(
            encounter.value?.referral?.doctor?.name || encounter.value?.referral?.doctor?.user?.name || encounter.value?.referral?.doctor
        ),
    },
    { label: "Pending Date", value: textOrFallback(encounter.value?.referral?.pending_date) },
    { label: "Insurance", value: textOrFallback(encounter.value?.referral?.insurance) },
    { label: "Order Notes", value: textOrFallback(encounter.value?.referral?.note) },
]);
</script>

<template>
    <AuthLayout title="View Encounter" description="View patient encounter details" heading="View Encounter">
        <div class="encounter-page">
            <section class="encounter-hero">
                <div class="encounter-hero__copy">
                    <span class="encounter-hero__eyebrow">Encounter Summary</span>
                    <h1 class="encounter-hero__title">{{ textOrFallback(encounterForm.chief_complaint, "Clinical encounter") }}</h1>
                    <p class="encounter-hero__description">
                        Review the full clinical documentation, orders, billing details, and supporting notes for this encounter.
                    </p>
                </div>

                <div class="encounter-hero__actions d-print-none">
                    <p @click="print()" class="cursor-pointer">
                        <i class="bi bi-printer-fill mr-2"></i>
                    </p>
                </div>

                <div class="encounter-hero__stats">
                    <div v-for="card in summaryCards" :key="card.label" class="encounter-stat">
                        <span class="encounter-stat__label">{{ card.label }}</span>
                        <strong class="encounter-stat__value">{{ card.value }}</strong>
                    </div>
                </div>
            </section>

            <section class="encounter-card">
                <div class="encounter-card__header">
                    <div>
                        <span class="encounter-card__eyebrow">Core Details</span>
                        <h2 class="encounter-card__title">Encounter Overview</h2>
                    </div>
                </div>

                <div class="encounter-grid">
                    <div v-for="item in overviewItems" :key="item.label" class="encounter-detail">
                        <span class="encounter-detail__label">{{ item.label }}</span>
                        <strong class="encounter-detail__value">{{ item.value }}</strong>
                    </div>
                </div>
            </section>

            <div class="encounter-layout">
                <div class="encounter-main">
                    <section class="encounter-card">
                        <div class="encounter-card__header">
                            <div>
                                <span class="encounter-card__eyebrow">Clinical Narrative</span>
                                <h2 class="encounter-card__title">History & Examination</h2>
                            </div>
                        </div>

                        <div class="encounter-content-block">
                            <h3>History of Present Illness</h3>
                            <div class="encounter-note">{{ textOrFallback(encounterForm.hpi) }}</div>
                        </div>

                        <div class="encounter-content-block">
                            <h3>Review of Systems</h3>
                            <div class="encounter-note">{{ textOrFallback(encounterForm.ros) }}</div>
                        </div>

                        <div class="encounter-content-block">
                            <h3>Physical Examination</h3>
                            <div class="encounter-note">{{ textOrFallback(encounterForm.pe) }}</div>
                        </div>
                    </section>

                    <section class="encounter-card">
                        <div class="encounter-card__header">
                            <div>
                                <span class="encounter-card__eyebrow">Vitals</span>
                                <h2 class="encounter-card__title">Vital Signs</h2>
                            </div>
                        </div>

                        <div class="encounter-grid encounter-grid--compact">
                            <div v-for="item in vitalItems" :key="item.label" class="encounter-detail">
                                <span class="encounter-detail__label">{{ item.label }}</span>
                                <strong class="encounter-detail__value">{{ item.value }}</strong>
                            </div>
                        </div>
                    </section>

                    <section class="encounter-card">
                        <div class="encounter-card__header">
                            <div>
                                <span class="encounter-card__eyebrow">Assessment & Plan</span>
                                <h2 class="encounter-card__title">Clinical Decision Making</h2>
                            </div>
                        </div>

                        <div class="encounter-content-block">
                            <h3>Additional Diagnoses</h3>
                            <div class="encounter-note">{{ textOrFallback(encounterForm.assessment_other) }}</div>
                        </div>

                        <div class="encounter-content-block">
                            <h3>Differential Diagnoses</h3>
                            <div class="encounter-note">{{ textOrFallback(encounterForm.differential_diagnoses) }}</div>
                        </div>

                        <div class="encounter-content-block">
                            <h3>Assessment Discussion</h3>
                            <div class="encounter-note">{{ textOrFallback(encounterForm.assessment_discussion) }}</div>
                        </div>

                        <div class="encounter-content-block">
                            <h3>Plan Recommendations</h3>
                            <div class="encounter-note">{{ textOrFallback(encounterForm.plan) }}</div>
                        </div>

                        <div class="encounter-grid encounter-grid--compact mt-4">
                            <div class="encounter-detail">
                                <span class="encounter-detail__label">Duration</span>
                                <strong class="encounter-detail__value">
                                    {{ encounterForm.duration ? `${encounterForm.duration} minutes` : "Not recorded" }}
                                </strong>
                            </div>
                            <div class="encounter-detail">
                                <span class="encounter-detail__label">Follow-up</span>
                                <strong class="encounter-detail__value">{{ textOrFallback(encounterForm.followup) }}</strong>
                            </div>
                        </div>
                    </section>

                    <section class="encounter-card">
                        <div class="encounter-card__header">
                            <div>
                                <span class="encounter-card__eyebrow">Medication Orders</span>
                                <h2 class="encounter-card__title">Prescriptions & Supplements</h2>
                            </div>
                        </div>

                        <div class="encounter-table-block">
                            <h3>Prescriptions</h3>
                            <div v-if="!encounter.prescriptions?.length" class="encounter-empty">No data available.</div>
                            <div v-else class="table-responsive">
                                <table class="table encounter-table">
                                    <thead>
                                        <tr>
                                            <th>Medication</th>
                                            <th>Dosage</th>
                                            <th>Dosage Unit</th>
                                            <th>Frequency</th>
                                            <th>Duration</th>
                                            <th>Instructions</th>
                                            <th>Reason</th>
                                            <th>Date Active</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="prescription in encounter.prescriptions" :key="prescription.id">
                                            <td>{{ textOrFallback(prescription.medication) }}</td>
                                            <td>{{ textOrFallback(prescription.dosage) }}</td>
                                            <td>{{ textOrFallback(prescription.dosage_unit) }}</td>
                                            <td>{{ textOrFallback(prescription.frequency) }}</td>
                                            <td>{{ textOrFallback(prescription.duration || prescription.frequency) }}</td>
                                            <td>{{ textOrFallback(prescription.instructions) }}</td>
                                            <td>{{ textOrFallback(prescription.reason) }}</td>
                                            <td>{{ textOrFallback(prescription.date_active) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="encounter-table-block">
                            <h3>Supplements</h3>
                            <div v-if="!encounter.supplements?.length" class="encounter-empty">No data available.</div>
                            <div v-else class="table-responsive">
                                <table class="table encounter-table">
                                    <thead>
                                        <tr>
                                            <th>Supplement</th>
                                            <th>Dosage</th>
                                            <th>Dosage Unit</th>
                                            <th>Frequency</th>
                                            <th>Instructions</th>
                                            <th>Reason</th>
                                            <th>Date Active</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="supplement in encounter.supplements" :key="supplement.id">
                                            <td>{{ textOrFallback(supplement.supplement) }}</td>
                                            <td>{{ textOrFallback(supplement.dosage) }}</td>
                                            <td>{{ textOrFallback(supplement.dosage_unit) }}</td>
                                            <td>{{ textOrFallback(supplement.frequency) }}</td>
                                            <td>{{ textOrFallback(supplement.instructions) }}</td>
                                            <td>{{ textOrFallback(supplement.reason) }}</td>
                                            <td>{{ textOrFallback(supplement.date_active) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section class="encounter-card">
                        <div class="encounter-card__header">
                            <div>
                                <span class="encounter-card__eyebrow">Orders</span>
                                <h2 class="encounter-card__title">Diagnostics & Procedures</h2>
                            </div>
                        </div>

                        <div class="encounter-table-block">
                            <h3>Lab Orders</h3>
                            <div v-if="!encounter.lab_orders?.length" class="encounter-empty">No data available.</div>
                            <div v-else class="table-responsive">
                                <table class="table encounter-table">
                                    <thead>
                                        <tr>
                                            <th>Insurance</th>
                                            <th>Order</th>
                                            <th>Code</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="order in encounter.lab_orders" :key="order.id">
                                            <td>{{ textOrFallback(order.insurance) }}</td>
                                            <td>{{ textOrFallback(order.labs) }}</td>
                                            <td>{{ textOrFallback(order.labs_icd) }}</td>
                                            <td>{{ textOrFallback(order.notes) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="encounter-table-block">
                            <h3>Imaging Orders</h3>
                            <div v-if="!encounter.radiology_orders?.length" class="encounter-empty">No data available.</div>
                            <div v-else class="table-responsive">
                                <table class="table encounter-table">
                                    <thead>
                                        <tr>
                                            <th>Insurance</th>
                                            <th>Order</th>
                                            <th>Code</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="order in encounter.radiology_orders" :key="order.id">
                                            <td>{{ textOrFallback(order.insurance) }}</td>
                                            <td>{{ textOrFallback(order.radiology) }}</td>
                                            <td>{{ textOrFallback(order.radiology_icd) }}</td>
                                            <td>{{ textOrFallback(order.notes) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="encounter-table-block">
                            <h3>Cardiopulmonary Orders</h3>
                            <div v-if="!encounter.card_orders?.length" class="encounter-empty">No data available.</div>
                            <div v-else class="table-responsive">
                                <table class="table encounter-table">
                                    <thead>
                                        <tr>
                                            <th>Insurance</th>
                                            <th>Order</th>
                                            <th>Code</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="order in encounter.card_orders" :key="order.id">
                                            <td>{{ textOrFallback(order.insurance) }}</td>
                                            <td>{{ textOrFallback(order.cp) }}</td>
                                            <td>{{ textOrFallback(order.cp_icd) }}</td>
                                            <td>{{ textOrFallback(order.notes) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="encounter-table-block">
                            <h3>Procedures</h3>
                            <div v-if="!encounter.procedures?.length" class="encounter-empty">No data available.</div>
                            <div v-else class="table-responsive">
                                <table class="table encounter-table">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Code</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="procedure in encounter.procedures" :key="procedure.id">
                                            <td>{{ textOrFallback(procedure.type) }}</td>
                                            <td>{{ textOrFallback(procedure.cpt) }}</td>
                                            <td>{{ textOrFallback(procedure.description) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </div>

                <aside class="encounter-sidebar">
                    <section class="encounter-card">
                        <div class="encounter-card__header">
                            <div>
                                <span class="encounter-card__eyebrow">Billing</span>
                                <h2 class="encounter-card__title">Billing Snapshot</h2>
                            </div>
                        </div>

                        <div class="encounter-grid encounter-grid--single">
                            <div v-for="item in billingItems" :key="item.label" class="encounter-detail">
                                <span class="encounter-detail__label">{{ item.label }}</span>
                                <strong class="encounter-detail__value">{{ item.value }}</strong>
                            </div>
                        </div>
                    </section>

                    <section class="encounter-card">
                        <div class="encounter-card__header">
                            <div>
                                <span class="encounter-card__eyebrow">Referral</span>
                                <h2 class="encounter-card__title">Referral Details</h2>
                            </div>
                        </div>

                        <div class="encounter-grid encounter-grid--single">
                            <div v-for="item in referralItems" :key="item.label" class="encounter-detail">
                                <span class="encounter-detail__label">{{ item.label }}</span>
                                <strong class="encounter-detail__value">{{ item.value }}</strong>
                            </div>
                        </div>
                    </section>

                    <section class="encounter-card">
                        <div class="encounter-card__header">
                            <div>
                                <span class="encounter-card__eyebrow">Media</span>
                                <h2 class="encounter-card__title">Images & Photos</h2>
                            </div>
                        </div>

                        <div class="encounter-media-block">
                            <h3>Anatomical Images</h3>
                            <div v-if="!encounter.images?.length" class="encounter-empty">No data available.</div>
                            <div v-else class="encounter-media-grid">
                                <div v-for="image in encounter.images" :key="image.id" class="encounter-media-card">
                                    <img :src="image.url" :alt="image.description || 'Anatomical image'" />
                                    <p>{{ textOrFallback(image.description) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="encounter-media-block">
                            <h3>Photos</h3>
                            <div v-if="!encounter.photos?.length" class="encounter-empty">No data available.</div>
                            <div v-else class="encounter-media-grid">
                                <div v-for="image in encounter.photos" :key="image.id" class="encounter-media-card">
                                    <img :src="image.url" :alt="image.description || 'Encounter photo'" />
                                    <p>{{ textOrFallback(image.description) }}</p>
                                </div>
                            </div>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </AuthLayout>
</template>

<style scoped>
.encounter-page {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.encounter-hero {
    display: grid;
    grid-template-columns: minmax(0, 1.6fr) auto;
    gap: 1.5rem;
    padding: 2rem;
    border-radius: 28px;
    background:
        radial-gradient(circle at top left, rgba(14, 165, 233, 0.12), transparent 42%),
        linear-gradient(135deg, #fbfdff 0%, #edf7ff 45%, #f8fbff 100%);
    border: 1px solid #dbe8f5;
    box-shadow: 0 22px 44px rgba(15, 23, 42, 0.07);
}

.encounter-hero__copy {
    min-width: 0;
}

.encounter-hero__eyebrow,
.encounter-card__eyebrow {
    display: inline-flex;
    align-items: center;
    width: fit-content;
    padding: 0.35rem 0.75rem;
    border-radius: 999px;
    background: rgba(14, 165, 233, 0.12);
    color: #0369a1;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.encounter-hero__title {
    margin: 0.95rem 0 0.6rem;
    color: #0f172a;
    font-size: clamp(2rem, 3vw, 3rem);
    line-height: 1.05;
}

.encounter-hero__description {
    max-width: 680px;
    margin: 0;
    color: #475569;
    font-size: 1rem;
}

.encounter-hero__actions {
    display: flex;
    justify-content: flex-end;
}

.encounter-print {
    padding: 0.65rem 0.95rem;
    border-radius: 999px;
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: #ffffff;
    font-size: 0.9rem;
    font-weight: 700;
    box-shadow: 0 14px 30px rgba(220, 38, 38, 0.18);
}

.encounter-print:hover {
    color: #ffffff;
}

.encounter-hero__stats {
    display: grid;
    grid-column: 1 / -1;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
}

.encounter-stat,
.encounter-card,
.encounter-detail,
.encounter-note,
.encounter-media-card {
    background: #ffffff;
    border: 1px solid #e6edf5;
    box-shadow: 0 18px 35px rgba(15, 23, 42, 0.05);
}

.encounter-stat {
    padding: 1.1rem 1.2rem;
    border-radius: 20px;
}

.encounter-stat__label,
.encounter-detail__label {
    display: block;
    margin-bottom: 0.35rem;
    color: #64748b;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.encounter-stat__value,
.encounter-detail__value {
    color: #0f172a;
    font-size: 1rem;
    font-weight: 700;
    line-height: 1.5;
}

.encounter-card {
    padding: 1.35rem;
    border-radius: 24px;
}

.encounter-card__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.2rem;
}

.encounter-card__title {
    margin: 0.7rem 0 0;
    color: #0f172a;
    font-size: 1.55rem;
    font-weight: 700;
}

.encounter-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.encounter-grid--compact {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.encounter-grid--single {
    grid-template-columns: 1fr;
}

.encounter-detail {
    padding: 1rem 1.05rem;
    border-radius: 18px;
}

.encounter-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.5fr) minmax(300px, 0.8fr);
    gap: 1.5rem;
}

.encounter-main,
.encounter-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.encounter-content-block + .encounter-content-block,
.encounter-table-block + .encounter-table-block,
.encounter-media-block + .encounter-media-block {
    margin-top: 1.4rem;
}

.encounter-content-block h3,
.encounter-table-block h3,
.encounter-media-block h3 {
    margin: 0 0 0.8rem;
    color: #0f172a;
    font-size: 1rem;
    font-weight: 700;
}

.encounter-note {
    padding: 1rem 1.1rem;
    border-radius: 18px;
    color: #475569;
    line-height: 1.8;
    white-space: pre-wrap;
}

.encounter-table {
    margin: 0;
    overflow: hidden;
}

.encounter-table thead th {
    border-top: 0;
    border-bottom: 1px solid #e6edf5;
    color: #64748b;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.encounter-table tbody td {
    color: #334155;
    vertical-align: top;
    border-color: #eef4fa;
}

.encounter-empty {
    color: #64748b;
    padding: 0.25rem 0;
}

.encounter-media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
}

.encounter-media-card {
    padding: 0.8rem;
    border-radius: 18px;
}

.encounter-media-card img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    border-radius: 14px;
    margin-bottom: 0.7rem;
    background: #f8fafc;
}

.encounter-media-card p {
    margin: 0;
    color: #475569;
    line-height: 1.6;
}

@media (max-width: 1199.98px) {
    .encounter-layout,
    .encounter-grid--compact,
    .encounter-hero__stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 991.98px) {
    .encounter-hero,
    .encounter-layout,
    .encounter-grid,
    .encounter-grid--compact,
    .encounter-hero__stats {
        grid-template-columns: 1fr;
    }

    .encounter-hero__actions {
        justify-content: flex-start;
    }
}

@media print {
    .encounter-hero,
    .encounter-card,
    .encounter-stat,
    .encounter-detail,
    .encounter-note,
    .encounter-media-card {
        box-shadow: none !important;
    }

    .encounter-page {
        gap: 1rem;
    }
}

@media (max-width: 767.98px) {
    .encounter-hero,
    .encounter-card {
        padding: 1.15rem;
    }
}
</style>
