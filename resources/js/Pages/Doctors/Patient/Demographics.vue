<script setup>
import { computed, ref } from "vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import DemographicsModal from "@/Pages/Modals/DemographicsModal.vue";
import Modal from "@/Components/Common/Modal.vue";
import axios from "axios";
import Swal from "sweetalert2";

const props = defineProps({
    patient: Object,
    countries: Array,
    states: Array,
});

const showEditModal = ref(false);
const processing = ref({});

const calculateAge = (dob) => {
    if (!dob) return null;
    const birthDate = new Date(dob);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthOffset = today.getMonth() - birthDate.getMonth();

    if (monthOffset < 0 || (monthOffset === 0 && today.getDate() < birthDate.getDate())) {
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

const register = async (patientId) => {
    processing.value[patientId] = true;

    try {
        const response = await axios.post(route("doctor.patient.register"), {
            patient_id: patientId,
        });

        Swal.fire({
            toast: true,
            position: "top-end",
            icon: response.data.success ? "success" : "error",
            title: response.data.success || response.data.message || "Something went wrong",
            showConfirmButton: false,
            timer: 3000,
        });
    } catch (error) {
        Swal.fire({
            toast: true,
            position: "top-end",
            icon: "error",
            title: error.response?.data?.message ?? "Something went wrong",
            showConfirmButton: false,
            timer: 3000,
        });
    } finally {
        processing.value[patientId] = false;
    }
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

const fullName = computed(() => {
    const name = [props.patient?.first_name, props.patient?.last_name].filter(Boolean).join(" ");
    return name || props.patient?.name || "Patient Profile";
});

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
                    <h1 class="hero-title">{{ fullName }}</h1>
                    <p class="hero-subtitle">
                        Identity, contact details, and portal-readiness presented in one cleaner clinical summary.
                    </p>

                    <div class="hero-meta">
                        <span class="meta-pill">{{ props.patient?.sex || "Gender not set" }}</span>
                        <span class="meta-pill">{{ ageLabel }}</span>
                        <span class="meta-pill">{{ maritalStatus }}</span>
                    </div>

                    <div class="hero-actions">
                        <button class="btn btn-light hero-action" @click="toggleEdit">
                            <i class="bi bi-pencil-square me-2"></i>Edit demographics
                        </button>
                        <button
                            v-if="!patient?.user"
                            class="btn btn-primary hero-register"
                            @click="register(props.patient.id)"
                        >
                            {{ processing[props.patient.id] ? "Registering..." : "Register to Portal" }}
                        </button>
                    </div>
                </div>

                <div class="hero-photo-card" @click="toggleEdit">
                    <img :src="profileImage" alt="Profile Photo" class="hero-photo" />
                    <div class="photo-caption">
                        <div class="photo-title">Profile photo</div>
                        <div class="photo-subtitle">Tap to update patient image</div>
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
            <DemographicsModal
                :patient="patient"
                :countries="countries"
                :states="states"
                :onClose="handleCloseModal"
            />
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
    grid-template-columns: minmax(0, 1.7fr) minmax(260px, 340px);
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
    max-width: 640px;
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

.hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.85rem;
    margin-top: 1.5rem;
}

.hero-action,
.hero-register {
    border-radius: 14px;
    padding: 0.8rem 1.1rem;
    font-weight: 600;
}

.hero-action {
    border: 1px solid #dbe8f5;
    color: #0f172a;
}

.hero-register {
    box-shadow: 0 14px 24px rgba(37, 99, 235, 0.18);
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
    border: 1px solid #e2e8f0;
}

.detail-label {
    margin-bottom: 0.35rem;
    color: #64748b;
    font-size: 0.83rem;
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
    .hero-card,
    .content-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 575.98px) {
    .hero-card {
        padding: 1.35rem;
    }

    .hero-photo {
        width: 128px;
        height: 128px;
        border-radius: 24px;
    }

    .detail-grid {
        grid-template-columns: 1fr;
    }

    .section-header,
    .hero-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .hero-action,
    .hero-register {
        width: 100%;
    }
}
</style>
