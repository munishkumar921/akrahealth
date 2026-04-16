<script setup>
import { ref } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

const props = defineProps({
    patient: {
        type: Object,
        required: false,
    },
    lastVisit: {
        type: String,
    },
    timeline: {
        type: Array,
        default: () => [],
    },
    readMore: {
        type: Function,
        required: false,
    },
});

const page = usePage();

const items = ref([
    { label: "New Encounter", href: route("doctor.encounters.create"), icon: "fa fa-plus fa-fw" },
    { label: "New Telephone Message", href: "#", icon: "fa fa-phone fa-fw" },
    { label: "New Letter", href: "#", icon: "fa fa-envelope fa-fw" },
    { label: "New Test Results", href: "#", icon: "fa fa-flask fa-fw" },
    { label: "New Alert", href: route("doctor.alerts.index"), icon: "fa fa-exclamation-triangle fa-fw" },
    { label: "New Document", href: route("doctor.documents.index"), icon: "fa fa-file fa-fw" },
    { divider: true },
    { label: "New Message to Patient", href: "#", icon: "fa fa-comment fa-fw" },
    { label: "New Coordination of Care Transaction", href: "#", icon: "fa fa-handshake fa-fw" },
]);

const calculateAge = (dob) => {
    if (!dob) return null;
    const birthDate = new Date(dob);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
};

const isDoctor = page.props?.auth?.user?.role === "doctor";
</script>

<template>
    <div class="patient-timeline-page">
        <div class="timeline-shell">
            <div v-if="patient" class="patient-summary-card">
                <div class="patient-summary-copy">
                    <span class="patient-summary-eyebrow">Patient Timeline</span>
                    <h2 class="patient-summary-name">
                        {{ patient?.name }}
                    </h2>
                    <p class="patient-summary-meta">
                        {{ calculateAge(patient?.dob) ?? "Age not set" }} years old
                        <span class="meta-divider">•</span>
                        {{ patient?.sex || "Gender not set" }}
                    </p>
                </div>

                <div class="patient-summary-actions">
                    <div class="summary-chip">{{ patient?.dob || "DOB not set" }}</div>

                    <div class="relative" v-if="isDoctor">
                        <button
                            class="btn btn-light patient-summary-menu"
                            type="button"
                            id="dropdownMenuButton"
                            data-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false"
                        >
                            <i class="fa fa-bars"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton" style="min-width: 220px;">
                            <li v-for="(item, index) in items" :key="index">
                                <hr v-if="item.divider" class="dropdown-divider" />
                                <a v-else :href="item.href" class="dropdown-item">
                                    <i :class="`${item.icon} mr-1`"></i> {{ item.label }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div v-if="lastVisit" class="last-visit-banner">
                <div class="last-visit-icon">
                    <i class="fa-regular fa-calendar"></i>
                </div>
                <div>
                    <div class="last-visit-label">Last visit with your practice</div>
                    <div class="last-visit-value">{{ lastVisit }}</div>
                </div>
            </div>

            <div class="timeline-wrapper">
                <div class="timeline-line"></div>

                <div v-for="(item, index) in timeline" :key="index" class="timeline-item">
                    <div
                        class="timeline-date"
                        :class="{ 'timeline-date-left': index % 2 === 0, 'timeline-date-right': index % 2 !== 0 }"
                    >
                        {{ item.date }}
                    </div>

                    <div class="timeline-icon">
                        <div :class="['timeline-icon-circle', item.iconColor]">
                            <i :class="item.icon"></i>
                        </div>
                    </div>

                    <div class="timeline-content" :class="{ right: index % 2 === 0, left: index % 2 !== 0 }">
                        <div class="timeline-card">
                            <div class="timeline-card-accent"></div>
                            <h3 class="timeline-title">{{ item.title }}</h3>
                            <p v-if="item.description" class="timeline-description preserve-whitespace">
                                {{ item.description }}
                            </p>

                            <Link :href="item.url" class="btn btn-outline-primary btn-sm timeline-link" v-if="item?.url">
                                Read more
                            </Link>
                        </div>
                        <div class="timeline-connector"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.patient-timeline-page {
    padding: 0.25rem;
}

.timeline-shell {
    display: flex;
    flex-direction: column;
    gap: 1.35rem;
    padding: 1.5rem;
    border-radius: 28px;
    background:
        radial-gradient(circle at top left, rgba(14, 165, 233, 0.08), transparent 28%),
        linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    border: 1px solid #e5eef6;
    box-shadow: 0 22px 48px rgba(15, 23, 42, 0.07);
}

.patient-summary-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.35rem 1.5rem;
    border-radius: 22px;
    background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
    color: #ffffff;
}

.patient-summary-copy {
    display: flex;
    flex-direction: column;
}

.patient-summary-eyebrow {
    display: inline-flex;
    width: fit-content;
    padding: 0.3rem 0.65rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.18);
    font-size: 0.76rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.patient-summary-name {
    margin: 0.75rem 0 0.25rem;
    font-size: 1.8rem;
    line-height: 1.1;
    color: #ffffff;
}

.patient-summary-meta {
    margin: 0;
    color: rgba(255, 255, 255, 0.9);
    font-size: 1rem;
}

.meta-divider {
    margin: 0 0.45rem;
}

.patient-summary-actions {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}

.summary-chip {
    padding: 0.55rem 0.85rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.18);
    font-size: 0.9rem;
    font-weight: 600;
    white-space: nowrap;
}

.patient-summary-menu {
    border-radius: 14px;
    width: 42px;
    height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #0f172a;
}

.last-visit-banner {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    padding: 1rem 1.15rem;
    border-radius: 18px;
    background: #ecfdf5;
    border: 1px solid #ccefdc;
}

.last-visit-icon {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    background: #d1fae5;
    color: #047857;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.last-visit-label {
    color: #065f46;
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.last-visit-value {
    color: #064e3b;
    font-size: 1rem;
    font-weight: 700;
}

.timeline-wrapper {
    position: relative;
    padding: 1rem 1rem 0;
}

.timeline-line {
    position: absolute;
    left: 50%;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(180deg, #bae6fd 0%, #dbeafe 50%, #e2e8f0 100%);
    transform: translateX(-50%);
}

.timeline-item {
    position: relative;
    margin-bottom: 4rem;
    display: flex;
    align-items: flex-start;
    width: 100%;
}

.timeline-icon {
    position: absolute;
    left: 50%;
    top: 0.4rem;
    transform: translateX(-50%);
    z-index: 10;
}

.timeline-icon-circle {
    width: 54px;
    height: 54px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 5px solid #ffffff;
    box-shadow: 0 10px 22px rgba(15, 23, 42, 0.14);
    color: #ffffff;
    font-size: 1.15rem;
}

.timeline-content {
    width: 45%;
    margin-top: 0.15rem;
    position: relative;
}

.timeline-content.left {
    margin-right: 55%;
    padding-right: 1.5rem;
}

.timeline-content.right {
    margin-left: 55%;
    padding-left: 1.5rem;
}

.timeline-card {
    position: relative;
    padding: 1.15rem 1.2rem 1.15rem 1.25rem;
    border-radius: 22px;
    background: #ffffff;
    border: 1px solid #e5eef6;
    box-shadow: 0 18px 35px rgba(15, 23, 42, 0.06);
}

.timeline-card-accent {
    position: absolute;
    left: 0;
    top: 1rem;
    bottom: 1rem;
    width: 4px;
    border-radius: 999px;
    background: linear-gradient(180deg, #38bdf8 0%, #0284c7 100%);
}

.timeline-title {
    margin: 0 0 0.45rem;
    color: #0f172a;
    font-size: 1.08rem;
    font-weight: 700;
}

.timeline-description {
    margin: 0.8rem 0 0;
    color: #475569;
    font-size: 0.92rem;
    line-height: 1.55rem;
}

.timeline-description.preserve-whitespace {
    white-space: pre-line;
}

.timeline-link {
    margin-top: 1rem;
    border-radius: 12px;
    font-weight: 600;
}

.timeline-connector {
    margin-top: 1.55rem;
    height: 2px;
    background: linear-gradient(90deg, #dbeafe 0%, #d1d5db 100%);
    width: 100%;
    transform: translateX(1%);
}

.timeline-date {
    position: absolute;
    top: 1.75rem;
    transform: translateY(-50%);
    width: fit-content;
    font-size: 0.78rem;
    color: #64748b;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    white-space: nowrap;
}

.timeline-date-left {
    right: 55%;
    margin-right: 1rem;
}

.timeline-date-right {
    left: 55%;
    margin-left: 1rem;
}

.timeline-icon .bg-green-500 {
    background-color: #10b981;
}

.timeline-icon .bg-yellow-500,
.timeline-icon .bg-orange-500 {
    background-color: #f59e0b;
}

.timeline-icon .bg-primary {
    background-color: #0ea5e9;
}

.timeline-icon .bg-red-500 {
    background-color: #c03b44;
}

.timeline-icon .bg-indigo-500 {
    background-color: #6366f1;
}

@media (max-width: 991.98px) {
    .patient-summary-card {
        flex-direction: column;
        align-items: flex-start;
    }

    .patient-summary-actions {
        width: 100%;
        justify-content: space-between;
    }
}

@media (max-width: 768px) {
    .timeline-shell {
        padding: 1rem;
    }

    .timeline-wrapper {
        padding: 0.5rem 0 0;
    }

    .timeline-line {
        left: 24px;
        transform: translateX(0);
    }

    .timeline-icon {
        left: 24px;
        transform: translateX(0);
    }

    .timeline-item {
        margin-bottom: 2.5rem;
    }

    .timeline-content.left,
    .timeline-content.right {
        width: auto;
        margin-left: 76px !important;
        margin-right: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .timeline-date-left,
    .timeline-date-right {
        left: 76px !important;
        right: auto !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        top: -0.4rem;
    }

    .timeline-connector {
        display: none;
    }
}

@media (max-width: 575.98px) {
    .patient-summary-name {
        font-size: 1.45rem;
    }

    .patient-summary-actions {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
