<script setup>
import { computed, nextTick, ref } from "vue";
import AuthLayout from "../../../Layouts/AuthLayout.vue";
import DoctorProfileEdit from "../../Modals/DoctorProfileEdit.vue";
import Modal from "../../../Components/Common/Modal.vue";

const props = defineProps({
    doctor: Object,
    specialties: Object,
    activities: Array,
});

const childComponentRef = ref();
const showProfileEditModal = ref(false);
const activePanel = ref("overview");

const closeProfileEditModal = () => {
    showProfileEditModal.value = false;
};

const edit = async (doctor) => {
    showProfileEditModal.value = true;
    await nextTick();
    if (childComponentRef.value) {
        childComponentRef.value.update(doctor);
    }
};

const textOrFallback = (value, fallback = "Not provided") =>
    value !== null && value !== undefined && String(value).trim() !== "" ? value : fallback;

const formatAddress = (address) =>
    [
        address?.address_1,
        address?.address_2,
        address?.city,
        address?.state,
        address?.country,
        address?.zip,
    ]
        .filter(Boolean)
        .join(", ");

const formatTime = (time) => {
    if (!time) return "";
    const [hours, minutes] = time.split(":");
    const date = new Date();
    date.setHours(Number(hours));
    date.setMinutes(Number(minutes));
    return date.toLocaleTimeString([], { hour: "numeric", minute: "2-digit", hour12: true });
};

const formatActivityDate = (value) => {
    if (!value) return "";
    return new Date(value).toLocaleString([], {
        month: "short",
        day: "numeric",
        year: "numeric",
        hour: "numeric",
        minute: "2-digit",
    });
};

const doctorName = computed(() =>
    [props.doctor?.first_name, props.doctor?.last_name].filter(Boolean).join(" ") || "Doctor Profile"
);

const doctorAvatar = computed(() => {
    if (props.doctor?.profile_photo_url) {
        return props.doctor.profile_photo_url;
    }

    if (props.doctor?.sex === "Female") {
        return "/images/doctor_f_avtar.svg";
    }

    return "/images/doctor_m_avtar.svg";
});

const doctorAddress = computed(() => formatAddress(props.doctor?.user?.address));

const hospitalTimings = computed(() => {
    const timings = props.doctor?.hospital?.timings || props.doctor?.timings;
    return Array.isArray(timings) ? timings : [];
});

const statCards = computed(() => [
    {
        label: "Experience",
        value: props.doctor?.experience ? `${props.doctor.experience} years` : "Not added",
    },
    {
        label: "Specialities",
        value: props.doctor?.specialities?.length ? props.doctor.specialities.length : "0",
    },
    {
        label: "Gender",
        value: textOrFallback(props.doctor?.sex || props.doctor?.user?.sex, "Unspecified"),
    },
    {
        label: "Hospital",
        value: textOrFallback(props.doctor?.hospital?.name, "Not linked"),
    },
]);

const overviewDetails = computed(() => [
    { label: "Email", value: textOrFallback(props.doctor?.user?.email) },
    { label: "Mobile", value: textOrFallback(props.doctor?.user?.mobile) },
    { label: "Certification", value: textOrFallback(props.doctor?.certification) },
    { label: "Address", value: textOrFallback(doctorAddress.value) },
]);
</script>

<template>
    <AuthLayout title="Doctor Profile" description="View and manage your doctor profile" heading="Doctor Profile">
        <div class="doctor-profile-page">
            <section class="doctor-hero">
                <div class="doctor-hero__main">
                    <div class="doctor-hero__avatar-shell">
                        <img :src="doctorAvatar" :alt="doctorName" class="doctor-hero__avatar" />
                    </div>

                    <div class="doctor-hero__copy">
                        <span class="doctor-hero__eyebrow">Doctor Profile</span>
                        <h1 class="doctor-hero__title">{{ doctorName }}</h1>
                        <p class="doctor-hero__subtitle">
                            {{ textOrFallback(doctor?.hospital?.name, "No hospital assigned yet") }}
                        </p>

                        <div class="doctor-hero__chips">
                            <span
                                v-for="speciality in doctor?.specialities || []"
                                :key="speciality?.id || speciality?.name"
                                class="doctor-chip"
                            >
                                {{ speciality?.name }}
                            </span>
                            <span v-if="!(doctor?.specialities || []).length" class="doctor-chip doctor-chip--muted">
                                No speciality added
                            </span>
                        </div>
                    </div>
                </div>

                <div class="doctor-hero__aside">
                    <button class="btn doctor-hero__edit" @click="edit(doctor)">
                        <i class="ri-pencil-line mr-2"></i>
                        Edit Profile
                    </button>

                    <div class="doctor-hero__stats">
                        <div v-for="stat in statCards" :key="stat.label" class="doctor-stat">
                            <span class="doctor-stat__label">{{ stat.label }}</span>
                            <strong class="doctor-stat__value">{{ stat.value }}</strong>
                        </div>
                    </div>
                </div>
            </section>

            <section class="doctor-tabs">
                <button
                    type="button"
                    class="doctor-tab"
                    :class="{ 'doctor-tab--active': activePanel === 'overview' }"
                    @click="activePanel = 'overview'"
                >
                    Profile
                </button>
                <button
                    type="button"
                    class="doctor-tab"
                    :class="{ 'doctor-tab--active': activePanel === 'activity' }"
                    @click="activePanel = 'activity'"
                >
                    Activity
                </button>
            </section>

            <div v-if="activePanel === 'overview'" class="doctor-grid">
                <section class="doctor-card doctor-card--primary">
                    <div class="doctor-card__header">
                        <div>
                            <span class="doctor-card__eyebrow">Professional Summary</span>
                            <h2 class="doctor-card__title">About Me</h2>
                        </div>
                    </div>

                    <div class="doctor-detail-grid">
                        <div v-for="item in overviewDetails" :key="item.label" class="doctor-detail">
                            <span class="doctor-detail__label">{{ item.label }}</span>
                            <strong class="doctor-detail__value">{{ item.value }}</strong>
                        </div>
                    </div>

                    <div class="doctor-about">
                        <span class="doctor-detail__label">About</span>
                        <p class="doctor-about__text">{{ textOrFallback(doctor?.about, "No description added yet.") }}</p>
                    </div>
                </section>

                <section class="doctor-card">
                    <div class="doctor-card__header">
                        <div>
                            <span class="doctor-card__eyebrow">Contact</span>
                            <h2 class="doctor-card__title">Reachability</h2>
                        </div>
                    </div>

                    <div class="doctor-stack">
                        <div class="doctor-contact-row">
                            <i class="ri-mail-fill"></i>
                            <span>{{ textOrFallback(doctor?.user?.email) }}</span>
                        </div>
                        <div class="doctor-contact-row">
                            <i class="ri-smartphone-fill"></i>
                            <span>{{ textOrFallback(doctor?.user?.mobile) }}</span>
                        </div>
                        <div class="doctor-contact-row">
                            <i class="ri-map-pin-2-fill"></i>
                            <span>{{ textOrFallback(doctorAddress, "No address available") }}</span>
                        </div>
                    </div>
                </section>

                <section class="doctor-card">
                    <div class="doctor-card__header">
                        <div>
                            <span class="doctor-card__eyebrow">Availability</span>
                            <h2 class="doctor-card__title">Schedule</h2>
                        </div>
                    </div>

                    <div class="doctor-stack">
                        <div
                            v-for="timing in hospitalTimings"
                            :key="timing?.id || `${timing?.day_of_week}-${timing?.open_time}`"
                            class="doctor-schedule-row"
                        >
                            <span class="doctor-schedule-row__day text-capitalize">{{ timing?.day_of_week }}</span>
                            <span v-if="!timing?.is_closed && timing?.open_time" class="doctor-schedule-row__time">
                                {{ formatTime(timing?.open_time) }} - {{ formatTime(timing?.close_time) }}
                            </span>
                            <span v-else class="doctor-schedule-row__closed">Closed</span>
                        </div>
                        <div v-if="!hospitalTimings.length" class="doctor-empty">No schedule available</div>
                    </div>
                </section>

                <section class="doctor-card">
                    <div class="doctor-card__header">
                        <div>
                            <span class="doctor-card__eyebrow">Expertise</span>
                            <h2 class="doctor-card__title">Specialities</h2>
                        </div>
                    </div>

                    <div class="doctor-hero__chips">
                        <span
                            v-for="speciality in doctor?.specialities || []"
                            :key="speciality?.id || speciality?.name"
                            class="doctor-chip"
                        >
                            {{ speciality?.name }}
                        </span>
                        <div v-if="!(doctor?.specialities || []).length" class="doctor-empty">No specialities added</div>
                    </div>
                </section>
            </div>

            <section v-else class="doctor-card">
                <div class="doctor-card__header">
                    <div>
                        <span class="doctor-card__eyebrow">Recent activity</span>
                        <h2 class="doctor-card__title">Activity Timeline</h2>
                    </div>
                </div>

                <div class="doctor-timeline">
                    <div v-for="activity in activities || []" :key="`${activity.id}-${activity.type}`" class="doctor-timeline__item">
                        <div class="doctor-timeline__icon" :class="`doctor-timeline__icon--${activity.color || 'primary'}`">
                            <i :class="activity.icon || 'ri-time-line'"></i>
                        </div>
                        <div class="doctor-timeline__content">
                            <div class="doctor-timeline__head">
                                <h3>{{ activity.title }}</h3>
                                <span>{{ formatActivityDate(activity.date) }}</span>
                            </div>
                            <p>{{ activity.description }}</p>
                        </div>
                    </div>

                    <div v-if="!(activities || []).length" class="doctor-empty">No activities to display.</div>
                </div>
            </section>
        </div>

        <Modal :isOpen="showProfileEditModal" title="Edit Profile" @close="closeProfileEditModal" size="xl">
            <DoctorProfileEdit
                ref="childComponentRef"
                :doctor="doctor"
                :specialties="specialties"
                @close="closeProfileEditModal"
            />
        </Modal>
    </AuthLayout>
</template>

<style scoped>
.doctor-profile-page {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.doctor-hero {
    display: grid;
    grid-template-columns: minmax(0, 1.7fr) minmax(280px, 360px);
    gap: 1.5rem;
    padding: 2rem;
    border-radius: 28px;
    background:
        radial-gradient(circle at top left, rgba(14, 165, 233, 0.12), transparent 42%),
        linear-gradient(135deg, #fbfdff 0%, #edf7ff 45%, #f8fbff 100%);
    border: 1px solid #dbe8f5;
    box-shadow: 0 22px 44px rgba(15, 23, 42, 0.07);
}

.doctor-hero__main {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.doctor-hero__avatar-shell {
    flex-shrink: 0;
    width: 132px;
    height: 132px;
    padding: 0.45rem;
    border-radius: 32px;
    background: rgba(255, 255, 255, 0.92);
    border: 1px solid #dbe8f5;
    box-shadow: 0 18px 35px rgba(15, 23, 42, 0.08);
}

.doctor-hero__avatar {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 26px;
}

.doctor-hero__copy {
    min-width: 0;
}

.doctor-hero__eyebrow,
.doctor-card__eyebrow {
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

.doctor-hero__title {
    margin: 0.95rem 0 0.45rem;
    color: #0f172a;
    font-size: clamp(2rem, 3vw, 3rem);
    line-height: 1.05;
}

.doctor-hero__subtitle {
    margin: 0;
    color: #475569;
    font-size: 1rem;
}

.doctor-hero__chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 1.2rem;
}

.doctor-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.55rem 0.9rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.88);
    border: 1px solid #dbe8f5;
    color: #334155;
    font-size: 0.88rem;
    font-weight: 700;
}

.doctor-chip--muted {
    color: #64748b;
}

.doctor-hero__aside {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.doctor-hero__edit {
    align-self: flex-end;
    padding: 0.85rem 1.2rem;
    border-radius: 999px;
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    color: #ffffff;
    font-weight: 700;
    box-shadow: 0 14px 30px rgba(2, 132, 199, 0.22);
}

.doctor-hero__edit:hover {
    color: #ffffff;
}

.doctor-hero__stats {
    display: grid;
    gap: 1rem;
}

.doctor-stat,
.doctor-card,
.doctor-detail,
.doctor-timeline__content {
    background: #ffffff;
    border: 1px solid #e6edf5;
    box-shadow: 0 18px 35px rgba(15, 23, 42, 0.05);
}

.doctor-stat {
    padding: 1.1rem 1.2rem;
    border-radius: 20px;
}

.doctor-stat__label,
.doctor-detail__label {
    display: block;
    margin-bottom: 0.35rem;
    color: #64748b;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.doctor-stat__value,
.doctor-detail__value {
    color: #0f172a;
    font-size: 1rem;
    font-weight: 700;
    line-height: 1.5;
}

.doctor-tabs {
    display: flex;
    gap: 1rem;
}

.doctor-tab {
    padding: 0.85rem 1.25rem;
    border-radius: 16px;
    border: 1px solid #dbe8f5;
    background: #ffffff;
    color: #475569;
    font-weight: 700;
    transition: all 0.2s ease;
}

.doctor-tab--active {
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    border-color: transparent;
    box-shadow: 0 16px 30px rgba(2, 132, 199, 0.24);
    color: #ffffff;
}

.doctor-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.55fr) minmax(0, 1fr);
    gap: 1.5rem;
}

.doctor-card {
    padding: 1.35rem;
    border-radius: 24px;
}

.doctor-card--primary {
    grid-row: span 2;
}

.doctor-card__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.2rem;
}

.doctor-card__title {
    margin: 0.7rem 0 0;
    color: #0f172a;
    font-size: 1.55rem;
    font-weight: 700;
}

.doctor-detail-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.doctor-detail {
    padding: 1rem 1.05rem;
    border-radius: 18px;
}

.doctor-about {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e6edf5;
}

.doctor-about__text {
    margin: 0;
    color: #475569;
    line-height: 1.8;
}

.doctor-stack {
    display: flex;
    flex-direction: column;
    gap: 0.9rem;
}

.doctor-contact-row,
.doctor-schedule-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.9rem;
    padding: 0.95rem 1rem;
    border-radius: 16px;
    background: #f8fbff;
    border: 1px solid #e6edf5;
    color: #334155;
}

.doctor-contact-row i {
    margin-top: 0.1rem;
    color: #0284c7;
}

.doctor-contact-row span {
    flex: 1;
}

.doctor-schedule-row__day {
    color: #0f172a;
    font-weight: 700;
}

.doctor-schedule-row__time {
    color: #0284c7;
    font-weight: 700;
}

.doctor-schedule-row__closed {
    color: #dc2626;
    font-weight: 700;
}

.doctor-timeline {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.doctor-timeline__item {
    display: grid;
    grid-template-columns: 56px minmax(0, 1fr);
    gap: 1rem;
    align-items: start;
}

.doctor-timeline__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 56px;
    height: 56px;
    border-radius: 18px;
    color: #ffffff;
    box-shadow: 0 14px 28px rgba(15, 23, 42, 0.12);
}

.doctor-timeline__icon--primary {
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
}

.doctor-timeline__icon--success {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
}

.doctor-timeline__icon--warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.doctor-timeline__content {
    padding: 1rem 1.1rem;
    border-radius: 18px;
}

.doctor-timeline__head {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 0.35rem;
}

.doctor-timeline__head h3 {
    margin: 0;
    color: #0f172a;
    font-size: 1rem;
    font-weight: 700;
}

.doctor-timeline__head span,
.doctor-timeline__content p,
.doctor-empty {
    color: #64748b;
}

.doctor-timeline__content p {
    margin: 0;
    line-height: 1.7;
}

.doctor-empty {
    padding: 1rem 0;
}

@media (max-width: 1199.98px) {
    .doctor-hero,
    .doctor-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 767.98px) {
    .doctor-hero,
    .doctor-card {
        padding: 1.15rem;
    }

    .doctor-hero__main,
    .doctor-detail-grid,
    .doctor-tabs,
    .doctor-timeline__item {
        grid-template-columns: 1fr;
        flex-direction: column;
    }

    .doctor-hero__edit {
        align-self: stretch;
    }

    .doctor-contact-row,
    .doctor-schedule-row,
    .doctor-timeline__head {
        flex-direction: column;
    }
}
</style>
