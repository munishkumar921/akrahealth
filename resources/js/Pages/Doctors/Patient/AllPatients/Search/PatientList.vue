<script setup>
const props = defineProps({
    patients: Array,
    goBack: Function,
});
const emit = defineEmits(["selectPatient"]);

const selectPatient = (patient) => {
    emit("selectPatient", patient);
};
</script>

<template>
    <div class="container">
        <div class="d-flex gap-3 align-items-center mb-3">
            <button @click="goBack" class="btn btn-primary">Back</button>
            <h5 class="mb-0">Results</h5>
        </div>

        <div v-for="patient in patients" :key="patient?.id" class="card mb-3">
            <div
                class="card-body d-flex justify-content-between align-items-center"
            >
                <div>
                    <h5 class="card-title mb-1">
                        {{ patient.first_name }} {{ patient.last_name }}
                    </h5>
                    <small class="text-muted">{{
                        patient.date_of_birth
                    }}</small>
                </div>
                <button
                    class="btn btn-outline-primary btn-sm"
                    @click="selectPatient(patient)"
                >
                    Select
                </button>
            </div>
        </div>
    </div>
</template>
