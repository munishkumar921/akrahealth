<script setup>
import { computed, ref } from "vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import DemographicsModal from "@/Pages/Modals/DemographicsModal.vue";
import Modal from "@/Components/Common/Modal.vue";

const props = defineProps({
    patient: {
        type: Object,
        default: () => ({}),
    },
});

const showEditModal = ref(false);

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

const toggleEdit = () => {
    showEditModal.value = !showEditModal.value;
};

const handleCloseModal = () => {
    showEditModal.value = false;
};

const maritalStatus = computed(() => {
    const status = props.patient?.marital_status;
    if (!status) return "Not specified";

    const statusMap = {
        1: "Single",
        2: "Married",
        3: "Divorced",
        4: "Widowed",
    };

    return statusMap[status] || "Not specified";
});

const ageLabel = computed(() => {
    const age = calculateAge(props.patient?.dob);
    return age === null ? "Not specified" : `${age} years`;
});

const profileImage = computed(() =>
    props.patient?.photo ? `/storage/${props.patient.photo}` : "/images/default-avatar.png"
);

const identityItems = computed(() => [
    { label: "First name", value: props.patient?.first_name || "Not specified" },
    { label: "Last name", value: props.patient?.last_name || "Not specified" },
    { label: "Date of birth", value: props.patient?.dob || "Not specified" },
    { label: "Age", value: ageLabel.value },
    { label: "Gender", value: props.patient?.sex || "Not specified" },
    { label: "Marital status", value: maritalStatus.value },
    { label: "Ethnicity", value: props.patient?.ethnicity || "Not specified" },
]);

const contactItems = computed(() => [
    { label: "Address", value: props.patient?.address_1 || "Not specified" },
    { label: "City", value: props.patient?.city || "Not specified" },
    { label: "State", value: props.patient?.state || "Not specified" },
    { label: "ZIP", value: props.patient?.zip || "Not specified" },
    { label: "Email", value: props.patient?.email || "Not specified" },
    { label: "Phone", value: props.patient?.mobile || "Not specified" },
]);
</script>

<template>
    <AuthLayout title="Demographics" description="Patient demographic information" heading="Demographics">
        <div class="demographics-page">
            <section class="hero-card">
                <div class="hero-copy">
                    <span class="eyebrow">Patient Demographics</span>
                    <h1 class="hero-title">
                        {{ patient?.name || [patient?.first_name, patient?.last_name].filter(Boolean).join(" ") || "Patient Profile" }}
                    </h1>
                    <p class="hero-subtitle">
                        Core identity and contact details in one place for faster verification and cleaner updates.
                    </p>

                    <div class="hero-meta">
                        <span class="meta-pill">{{ patient?.sex || "Gender not set" }}</span>
                        <span class="meta-pill">{{ ageLabel }}</span>
                        <span class="meta-pill">{{ maritalStatus }}</span>
                    </div>

                    <button class="btn btn-light hero-action" @click="toggleEdit">
                        <i class="bi bi-pencil-square me-2"></i>Edit demographics
                    </button>
                </div>

                <div class="hero-photo-card" @click="toggleEdit">
                    <img :src="profileImage" alt="Profile Photo" class="hero-photo" />
                    <div class="photo-caption">
                        <div class="photo-title">Profile photo</div>
                        <div class="photo-subtitle">Tap to update</div>
                    </div>
                </div>
            </section>

            <div class="content-grid">
                <section class="detail-card">
                    <div class="section-header">
                        <div>
                            <span class="section-kicker">Identity</span>
                            <h2 class="section-title">Name and identity</h2>
                        </div>
                        <button class="btn btn-outline-primary btn-sm" @click="toggleEdit">Edit</button>
                    </div>

                    <div class="detail-grid">
                        <div v-for="item in identityItems" :key="item.label" class="detail-item">
                            <div class="detail-label">{{ item.label }}</div>
                            <div class="detail-value">{{ item.value }}</div>
                        </div>
                    </div>
                </section>

                <section class="detail-card">
                    <div class="section-header">
                        <div>
                            <span class="section-kicker">Contact</span>
                            <h2 class="section-title">Reach and location</h2>
                        </div>
                        <button class="btn btn-outline-primary btn-sm" @click="toggleEdit">Edit</button>
                    </div>

                    <div class="detail-grid">
                        <div v-for="item in contactItems" :key="item.label" class="detail-item">
                            <div class="detail-label">{{ item.label }}</div>
                            <div class="detail-value">{{ item.value }}</div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <Modal :isOpen="showEditModal" title="Edit Demographics" @close="handleCloseModal" size="xl">
            <DemographicsModal :patient="patient" :onClose="handleCloseModal" />
        </Modal>
    </AuthLayout>
</template>

<style scoped>
.demographics-page {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.hero-card {
    display: grid;
    grid-template-columns: minmax(0, 1.6fr) minmax(260px, 340px);
    gap: 1.5rem;
    padding: 2rem;
    border-radius: 28px;
    background:
        radial-gradient(circle at top left, rgba(14, 165, 233, 0.14), transparent 38%),
        linear-gradient(135deg, #f8fbff 0%, #eef6ff 48%, #f5f8fc 100%);
    border: 1px solid #dbe8f5;
    box-shadow: 0 22px 45px rgba(15, 23, 42, 0.08);
}

.hero-copy {
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.eyebrow {
    display: inline-flex;
    align-items: center;
    width: fit-content;
    padding: 0.35rem 0.7rem;
    border-radius: 999px;
    background: rgba(14, 165, 233, 0.12);
    color: #0369a1;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.hero-title {
    margin: 1rem 0 0.5rem;
    font-size: clamp(2rem, 3vw, 3rem);
    line-height: 1.05;
    color: #0f172a;
}

.hero-subtitle {
    max-width: 620px;
    margin: 0;
    color: #475569;
    font-size: 1rem;
}

.hero-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 1.25rem;
}

.meta-pill {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 0.9rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.88);
    border: 1px solid #d9e6f3;
    color: #1e293b;
    font-size: 0.9rem;
    font-weight: 600;
}

.hero-action {
    width: fit-content;
    margin-top: 1.5rem;
    border-radius: 14px;
    padding: 0.8rem 1.1rem;
    border: 1px solid #dbe8f5;
    color: #0f172a;
    font-weight: 600;
}

.hero-photo-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    padding: 1.5rem;
    border-radius: 22px;
    background: rgba(255, 255, 255, 0.82);
    border: 1px solid #dbe8f5;
    cursor: pointer;
}

.hero-photo {
    width: 160px;
    height: 160px;
    border-radius: 28px;
    object-fit: cover;
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.14);
}

.photo-caption {
    text-align: center;
}

.photo-title {
    color: #0f172a;
    font-weight: 700;
}

.photo-subtitle {
    color: #64748b;
    font-size: 0.92rem;
}

.content-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1.5rem;
}

.detail-card {
    padding: 1.5rem;
    border-radius: 24px;
    background: #ffffff;
    border: 1px solid #e6edf5;
    box-shadow: 0 18px 35px rgba(15, 23, 42, 0.05);
}

.section-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.section-kicker {
    display: block;
    margin-bottom: 0.35rem;
    color: #64748b;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.section-title {
    margin: 0;
    color: #0f172a;
    font-size: 1.35rem;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.detail-item {
    padding: 1rem 1.05rem;
    border-radius: 18px;
    background: linear-gradient(180deg, #fbfdff 0%, #f5f8fc 100%);
    border: 1px solid #e6edf5;
}

.detail-label {
    margin-bottom: 0.35rem;
    color: #64748b;
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
}

.detail-value {
    color: #0f172a;
    font-size: 1rem;
    font-weight: 600;
    word-break: break-word;
}

@media (max-width: 991.98px) {
    .hero-card {
        grid-template-columns: 1fr;
    }

    .content-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 575.98px) {
    .hero-card,
    .detail-card {
        padding: 1.1rem;
    }

    .detail-grid {
        grid-template-columns: 1fr;
    }

    .hero-photo {
        width: 120px;
        height: 120px;
        border-radius: 22px;
    }
}
</style>
