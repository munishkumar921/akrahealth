<script setup>
import { computed, onMounted, ref } from "vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import UserList from "./UserList.vue";

const props = defineProps({
    socialHistory: {
        type: Object,
        default: null,
    },
});

const activeTab = ref("lifestyle");

const tabConfig = [
    {
        key: "lifestyle",
        label: "Lifestyle",
        eyebrow: "Daily patterns",
        description: "Food, activity, sleep, and everyday routines that shape long-term health.",
    },
    {
        key: "habits",
        label: "Habits",
        eyebrow: "Behavioral factors",
        description: "Smoking, alcohol, substance use, and other behaviors that may affect care decisions.",
    },
    {
        key: "mental",
        label: "Mental Health",
        eyebrow: "Emotional wellbeing",
        description: "Mental health history, mood-related details, and relevant psychosocial context.",
    },
];

const activeTabMeta = computed(
    () => tabConfig.find((tab) => tab.key === activeTab.value) || tabConfig[0]
);

const setTab = (tab) => {
    activeTab.value = tab;
    localStorage.setItem("socialHistoryActiveTab", tab);
};

onMounted(() => {
    const storedTab = localStorage.getItem("socialHistoryActiveTab");
    if (storedTab && ["lifestyle", "habits", "mental"].includes(storedTab)) {
        activeTab.value = storedTab;
    }
});
</script>

<template>
    <AuthLayout title="Social History" description="Manage patient social history" heading="Social History">
        <div class="social-history-page">
            <section class="social-hero">
                <div class="social-hero__copy">
                    <span class="social-hero__eyebrow">Patient Social History</span>
                    <h1 class="social-hero__title">{{ activeTabMeta.label }}</h1>
                    <p class="social-hero__description">{{ activeTabMeta.description }}</p>
                </div>

                <div class="social-hero__stats">
                    <div class="social-stat-card">
                        <span class="social-stat-label">Current section</span>
                        <strong class="social-stat-value">{{ activeTabMeta.eyebrow }}</strong>
                    </div>
                    <div class="social-stat-card">
                        <span class="social-stat-label">View</span>
                        <strong class="social-stat-value">Patient-reported overview</strong>
                    </div>
                </div>
            </section>

            <section class="social-tabs-shell">
                <div class="social-tabs" role="tablist" aria-label="Social history sections">
                    <button
                        v-for="tab in tabConfig"
                        :key="tab.key"
                        type="button"
                        class="social-tab"
                        :class="{ 'social-tab--active': activeTab === tab.key }"
                        :aria-selected="activeTab === tab.key"
                        @click="setTab(tab.key)"
                    >
                        <span class="social-tab__title">{{ tab.label }}</span>
                        <span class="social-tab__meta">{{ tab.eyebrow }}</span>
                    </button>
                </div>

                <div class="social-content-card">
                    <UserList :activeTab="activeTab" :socialHistory="socialHistory" />
                </div>
            </section>
        </div>
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
    grid-template-columns: minmax(0, 1.7fr) minmax(260px, 360px);
    gap: 1.5rem;
    padding: 2rem;
    border-radius: 28px;
    background:
        radial-gradient(circle at top left, rgba(14, 165, 233, 0.14), transparent 40%),
        linear-gradient(135deg, #fbfdff 0%, #eef7ff 45%, #f8fbff 100%);
    border: 1px solid #dbe8f5;
    box-shadow: 0 22px 44px rgba(15, 23, 42, 0.07);
}

.social-hero__copy {
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.social-hero__eyebrow {
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
    margin: 0.95rem 0 0.45rem;
    color: #0f172a;
    font-size: clamp(2rem, 3vw, 3rem);
    line-height: 1.05;
}

.social-hero__description {
    max-width: 640px;
    margin: 0;
    color: #475569;
    font-size: 1rem;
}

.social-hero__stats {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.social-stat-card {
    padding: 1.15rem 1.2rem;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.88);
    border: 1px solid #dbe8f5;
}

.social-stat-label {
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

.social-tabs-shell {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.social-tabs {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
}

.social-tab {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.25rem;
    padding: 1rem 1.15rem;
    border-radius: 20px;
    border: 1px solid #dbe8f5;
    background: #ffffff;
    color: #334155;
    text-align: left;
    transition: all 0.2s ease;
}

.social-tab:hover {
    border-color: #93c5fd;
    background: #f8fbff;
}

.social-tab--active {
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    border-color: transparent;
    box-shadow: 0 18px 35px rgba(2, 132, 199, 0.28);
    color: #ffffff;
}

.social-tab__title {
    font-size: 1rem;
    font-weight: 700;
}

.social-tab__meta {
    font-size: 0.84rem;
    opacity: 0.85;
}

.social-content-card {
    padding: 1.1rem;
    border-radius: 24px;
    background: #ffffff;
    border: 1px solid #e6edf5;
    box-shadow: 0 18px 35px rgba(15, 23, 42, 0.05);
}

@media (max-width: 991.98px) {
    .social-hero {
        grid-template-columns: 1fr;
    }

    .social-tabs {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 575.98px) {
    .social-hero,
    .social-content-card {
        padding: 1rem;
    }
}
</style>
