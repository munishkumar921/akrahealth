<script setup>
const props = defineProps({
    form: Object,
});

const emit = defineEmits(["close", "submit"]);

const submitForm = () => {
    emit("submit");
};

const closeModal = () => {
    emit("close");
};
</script>

<template>
    <div class="mb-4">
        <h5>Encounter Summary</h5>
        <p>
            <strong>Provider:</strong> {{ form.provider }} ({{
                form.provider_role
            }}) at {{ form.encounter_location }}.
        </p>
        <p>
            <strong>Encounter Type:</strong> {{ form.encounter_type }},
            scheduled on {{ form.date_of_service }}.
        </p>
        <p><strong>Chief Complaint:</strong> {{ form.chief_complaint }}</p>
        <p>
            <strong>Associated Appointment:</strong>
            {{ form.assosciated_appointment }}
        </p>
    </div>

    <!-- Subjective Section -->
    <div class="mb-4">
        <h5>Subjective</h5>
        <p><strong>History:</strong> {{ form.history }}</p>
        <p><strong>Review:</strong> {{ form.review }}</p>
    </div>

    <!-- Objective Section -->
    <div class="mb-4">
        <h5>Objective</h5>
        <p>
            <strong>Vitals:</strong> Weight - {{ form.wieght }} kg, Height -
            {{ form.hieght }} cm, BMI - {{ form.bmi }}, Temperature -
            {{ form.temperature }}° ({{ form.temperature_methode }}).
        </p>
        <p>
            <strong>Blood Pressure:</strong> {{ form.systolic_bp }}/{{
                form.diastolic_bp
            }}
            ({{ form.bp_position }})
        </p>
        <p>
            <strong>Pulse:</strong> {{ form.pulse }},
            <strong>Respiration:</strong> {{ form.respiration }},
            <strong>Oxygen Saturation:</strong> {{ form.oxygen_saturation }}%
        </p>
        <p>
            <strong>Physical Exam Notes:</strong> {{ form.physical_exam_notes }}
        </p>
    </div>

    <!-- Assessment Section -->
    <div class="mb-4">
        <h5>Assessment</h5>
        <p><strong>ICD-10:</strong> {{ form.icd10 }}</p>
        <p>
            <strong>Additional Diagnoses:</strong>
            {{ form.additional_diagnoses }}
        </p>
        <p>
            <strong>Differential Diagnoses:</strong>
            {{ form.differential_diagnoses }}
        </p>
        <p>
            <strong>Assessment Discussion:</strong>
            {{ form.assesment_discussion }}
        </p>
    </div>

    <!-- Plan Section -->
    <div class="mb-4">
        <h5>Plan</h5>
        <p><strong>Recommendations:</strong> {{ form.recommendations }}</p>
        <p><strong>Follow-Up:</strong> {{ form.follow_up }}</p>
        <p><strong>Total Time Spent:</strong> {{ form.total_time }} minutes</p>
    </div>

    <div class="form-button mt-4 px-3 d-flex justify-content-end gap-3">
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-danger" @click="closeModal">
            Back
        </button>
    </div>
</template>
