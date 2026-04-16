<script setup>
import { computed, ref } from "vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import MentalHealthModal from "./Partials/MentalHealth/MentalHealthModal.vue";
import LifestyleModal from "./Partials/Lifestyle/LifestyleModal.vue";
import HabitModal from "./Partials/Habits/HabitsModal.vue";
import Modal from "@/Components/Common/Modal.vue";

const props = defineProps({
    socialHistory: Object,
});

const isMentalHealthModalOpen = ref(false);
const isLifestyleOpen = ref(false);
const isHobitsOpen = ref(false);

const parseMentalHealthNotes = (notes) => {
    if (!notes || !notes.trim()) {
        return {
            psychological_history: "",
            devolepmental_history: "",
            past_medication_trials: "",
        };
    }

    const parts = notes.split(" | ");

    return {
        psychological_history: parts[0] || "",
        devolepmental_history: parts[1] || "",
        past_medication_trials: parts[2] || "",
    };
};

const existingData = computed(() => parseMentalHealthNotes(props.socialHistory?.mental_health_notes));

const yesNo = (value) => (value === true ? "Yes" : value === false ? "No" : "N/A");
const textOrFallback = (value, fallback = "Not recorded") =>
    value !== null && value !== undefined && String(value).trim() !== "" ? value : fallback;

const summaryStats = computed(() => [
    {
        label: "Diet",
        value: textOrFallback(props.socialHistory?.diet, "No diet preference"),
    },
    {
        label: "Physical Activity",
        value: textOrFallback(props.socialHistory?.physical_activity, "No activity recorded"),
    },
    {
        label: "Alcohol Use",
        value: textOrFallback(props.socialHistory?.alcohol_use, "Not documented"),
    },
    {
        label: "Tobacco Use",
        value: yesNo(props.socialHistory?.tobacco_use),
    },
]);

const lifestyleItems = computed(() => [
    { label: "Social History", value: textOrFallback(props.socialHistory?.social_history) },
    { label: "Sexually Active", value: yesNo(props.socialHistory?.sexually_active) },
    { label: "Diet", value: textOrFallback(props.socialHistory?.diet) },
    { label: "Physical Activity", value: textOrFallback(props.socialHistory?.physical_activity) },
    { label: "Employment", value: textOrFallback(props.socialHistory?.employment) },
]);

const habitItems = computed(() => [
    { label: "Alcohol Use", value: textOrFallback(props.socialHistory?.alcohol_use) },
    { label: "Tobacco Use", value: yesNo(props.socialHistory?.tobacco_use) },
    { label: "Tobacco Details", value: textOrFallback(props.socialHistory?.tobacco_use_details) },
    { label: "Illicit Drug Use", value: textOrFallback(props.socialHistory?.drug_use) },
]);

const mentalHealthItems = computed(() => [
    { label: "Psychosocial History", value: textOrFallback(existingData.value?.psychological_history) },
    { label: "Developmental History", value: textOrFallback(existingData.value?.devolepmental_history) },
    { label: "Past Medication Trials", value: textOrFallback(existingData.value?.past_medication_trials) },
]);
</script>

<template>
    <AuthLayout
        title="Social History"
        description="Manage patient social history and lifestyle information"
        heading="Social History"
    >
        <div class="social-history-page">
            <section class="social-hero">
                <div class="social-hero__copy">
                    <span class="social-hero__eyebrow">Patient Social History</span>
                    <h1 class="social-hero__title">Lifestyle, habits, and behavioral context</h1>
                    <p class="social-hero__description">
                        Review the patient’s social profile in one place, then update each section without
                        leaving the summary.
                    </p>

                    <div class="social-hero__chips">
                        <span class="social-chip">Lifestyle</span>
                        <span class="social-chip">Habits</span>
                        <span class="social-chip">Mental Health</span>
                    </div>
                </div>

                <div class="social-hero__stats">
                    <div v-for="stat in summaryStats" :key="stat.label" class="social-stat-card">
                        <span class="social-stat-label">{{ stat.label }}</span>
                        <strong class="social-stat-value">{{ stat.value }}</strong>
                    </div>
                </div>
            </section>

            <section class="social-sections">
                <article class="social-panel">
                    <div class="social-panel__header">
                        <div>
                            <span class="social-panel__eyebrow">Daily patterns</span>
                            <h2 class="social-panel__title">Lifestyle</h2>
                        </div>
                        <button class="btn social-panel__action" @click="isLifestyleOpen = true">
                            Edit
                        </button>
                    </div>

                    <div class="social-grid">
                        <div v-for="item in lifestyleItems" :key="item.label" class="social-field">
                            <span class="social-field__label">{{ item.label }}</span>
                            <strong class="social-field__value">{{ item.value }}</strong>
                        </div>
                    </div>
                </article>

                <article class="social-panel">
                    <div class="social-panel__header">
                        <div>
                            <span class="social-panel__eyebrow">Behavioral factors</span>
                            <h2 class="social-panel__title">Habits</h2>
                        </div>
                        <button class="btn social-panel__action" @click="isHobitsOpen = true">
                            Edit
                        </button>
                    </div>

                    <div class="social-grid">
                        <div v-for="item in habitItems" :key="item.label" class="social-field">
                            <span class="social-field__label">{{ item.label }}</span>
                            <strong class="social-field__value">{{ item.value }}</strong>
                        </div>
                    </div>
                </article>

                <article class="social-panel social-panel--wide">
                    <div class="social-panel__header">
                        <div>
                            <span class="social-panel__eyebrow">Emotional wellbeing</span>
                            <h2 class="social-panel__title">Mental Health</h2>
                        </div>
                        <button class="btn social-panel__action" @click="isMentalHealthModalOpen = true">
                            Edit
                        </button>
                    </div>

                    <div class="social-grid social-grid--mental">
                        <div v-for="item in mentalHealthItems" :key="item.label" class="social-field social-field--tall">
                            <span class="social-field__label">{{ item.label }}</span>
                            <strong class="social-field__value social-field__value--multiline">{{ item.value }}</strong>
                        </div>
                    </div>
                </article>
            </section>
        </div>

        <Modal :isOpen="isMentalHealthModalOpen" title="Edit Mental Health" @close="isMentalHealthModalOpen = false" size="lg">
            <MentalHealthModal @close="isMentalHealthModalOpen = false" :socialHistory="socialHistory" />
        </Modal>

        <Modal :isOpen="isLifestyleOpen" title="Edit Lifestyle" @close="isLifestyleOpen = false" size="lg">
            <LifestyleModal @close="isLifestyleOpen = false" :socialHistory="socialHistory" />
        </Modal>

        <Modal :isOpen="isHobitsOpen" title="Edit Habits" @close="isHobitsOpen = false" size="lg">
            <HabitModal @close="isHobitsOpen = false" :socialHistory="socialHistory" />
        </Modal>
    </AuthLayout>
</template>

<style scoped>
.social-history-page {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.social-hero {
    display: grid;
    grid-template-columns: minmax(0, 1.65fr) minmax(280px, 360px);
    gap: 1.5rem;
    padding: 2rem;
    border-radius: 28px;
    background:
        radial-gradient(circle at top left, rgba(14, 165, 233, 0.12), transparent 42%),
        linear-gradient(135deg, #fbfdff 0%, #edf7ff 42%, #f8fbff 100%);
    border: 1px solid #dbe8f5;
    box-shadow: 0 22px 44px rgba(15, 23, 42, 0.07);
}

.social-hero__copy {
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.social-hero__eyebrow,
.social-panel__eyebrow {
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

.social-hero__title {
    margin: 0.95rem 0 0.6rem;
    color: #0f172a;
    font-size: clamp(2rem, 3vw, 3rem);
    line-height: 1.05;
}

.social-hero__description {
    max-width: 680px;
    margin: 0;
    color: #475569;
    font-size: 1rem;
}

.social-hero__chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 1.4rem;
}

.social-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.6rem 0.95rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.86);
    border: 1px solid #dbe8f5;
    color: #334155;
    font-size: 0.88rem;
    font-weight: 700;
}

.social-hero__stats {
    display: grid;
    gap: 1rem;
}

.social-stat-card,
.social-panel,
.social-field {
    background: #ffffff;
    border: 1px solid #e6edf5;
    box-shadow: 0 18px 35px rgba(15, 23, 42, 0.05);
}

.social-stat-card {
    padding: 1.1rem 1.2rem;
    border-radius: 20px;
}

.social-stat-label,
.social-field__label {
    display: block;
    margin-bottom: 0.35rem;
    color: #64748b;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.social-stat-value {
    color: #0f172a;
    font-size: 1rem;
    font-weight: 700;
}

.social-sections {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1.5rem;
}

.social-panel {
    padding: 1.35rem;
    border-radius: 24px;
}

.social-panel--wide {
    grid-column: 1 / -1;
}

.social-panel__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.2rem;
}

.social-panel__title {
    margin: 0.7rem 0 0;
    color: #0f172a;
    font-size: 1.55rem;
    font-weight: 700;
}

.social-panel__action {
    min-width: 96px;
    padding: 0.75rem 1.05rem;
    border-radius: 999px;
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    color: #ffffff;
    font-size: 0.92rem;
    font-weight: 700;
    box-shadow: 0 14px 30px rgba(2, 132, 199, 0.22);
}

.social-panel__action:hover {
    color: #ffffff;
}

.social-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.social-grid--mental {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.social-field {
    padding: 1rem 1.05rem;
    border-radius: 18px;
}

.social-field--tall {
    min-height: 140px;
}

.social-field__value {
    color: #0f172a;
    font-size: 1rem;
    font-weight: 700;
    line-height: 1.5;
}

.social-field__value--multiline {
    white-space: pre-wrap;
}

@media (max-width: 1199.98px) {
    .social-hero,
    .social-sections,
    .social-grid--mental {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 767.98px) {
    .social-hero,
    .social-panel {
        padding: 1.15rem;
    }

    .social-grid {
        grid-template-columns: 1fr;
    }

    .social-panel__header {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>
