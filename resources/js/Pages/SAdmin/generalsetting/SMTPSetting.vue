<script setup>
import { computed, ref } from "vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Modal from "@/Components/Common/Modal.vue";
import { useForm } from "@inertiajs/vue3";
import Swal from "sweetalert2/dist/sweetalert2.js";

const props = defineProps({
    smtp: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    host: props.smtp?.host || "",
    port: props.smtp?.port || "",
    username: props.smtp?.username || "",
    password: props.smtp?.password || "",
    from_address: props.smtp?.from_address || "",
    from_name: props.smtp?.from_name || "",
    encryption: props.smtp?.encryption || "tls",
});

const testForm = useForm({
    email: "",
    subject: "SMTP Configuration Test",
    message: "This is a test email from Akra Health Super Admin SMTP settings.",
});

const showTestModal = ref(false);

const metricCards = computed(() => [
    {
        label: "Host",
        value: form.host || "Not set",
        helper: "Current outgoing mail server",
        icon: "fa-solid fa-server",
        tone: "tone-blue",
    },
    {
        label: "Port",
        value: form.port || "Not set",
        helper: "SMTP transport port",
        icon: "fa-solid fa-network-wired",
        tone: "tone-amber",
    },
    {
        label: "Encryption",
        value: (form.encryption || "none").toUpperCase(),
        helper: "Transport security mode",
        icon: "fa-solid fa-lock",
        tone: "tone-green",
    },
    {
        label: "Sender",
        value: form.from_name || "Not set",
        helper: form.from_address || "No sender email configured",
        icon: "fa-solid fa-paper-plane",
        tone: "tone-slate",
    },
]);

const submitForm = () => {
    Swal.fire({
        title: "Save SMTP settings?",
        text: "These credentials will be used for outgoing platform emails.",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Save settings",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#0ea5e9",
    }).then((result) => {
        if (result.isConfirmed) {
            form.post(route("superAdmin.smtpsetting.update"), {
                preserveScroll: true,
            });
        }
    });
};

const sendTestMail = () => {
    testForm.post(route("superAdmin.smtpsetting.test"), {
        preserveScroll: true,
        onSuccess: () => {
            showTestModal.value = false;
            testForm.reset("email");
        },
    });
};
</script>

<template>
    <AuthLayout title="SMTP Settings" description="Manage outbound email delivery settings for the platform">
        <section class="settings-page">
            <div class="hero-card card border-0 shadow-sm">
                <div class="card-body hero-flex">
                    <div>
                        <p class="hero-kicker">Mail Infrastructure</p>
                        <h1 class="hero-title">SMTP Settings</h1>
                        <p class="hero-text">
                            Configure the mail transport used for verification emails, system alerts, and all outgoing
                            platform communication.
                        </p>
                    </div>
                    <button type="button" class="btn btn-outline-primary" @click="showTestModal = true">
                        <i class="bi bi-send-check me-2"></i> Send Test Email
                    </button>
                </div>
            </div>

            <div class="metric-grid">
                <article v-for="card in metricCards" :key="card.label" class="metric-card card border-0 shadow-sm" :class="card.tone">
                    <div class="card-body">
                        <div class="metric-icon"><i :class="card.icon"></i></div>
                        <div>
                            <p class="metric-label">{{ card.label }}</p>
                            <h3 class="metric-value">{{ card.value }}</h3>
                            <p class="metric-helper">{{ card.helper }}</p>
                        </div>
                    </div>
                </article>
            </div>

            <form @submit.prevent="submitForm" class="settings-stack">
                <div class="card border-0 shadow-sm settings-card">
                    <div class="card-body">
                        <div class="section-head">
                            <div>
                                <p class="section-kicker">Transport</p>
                                <h3 class="section-title">Server configuration</h3>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-12 col-lg-6">
                                <label class="form-label">SMTP Host</label>
                                <input v-model="form.host" type="text" class="form-control" />
                                <div v-if="form.errors.host" class="text-danger small mt-1">{{ form.errors.host }}</div>
                            </div>
                            <div class="col-12 col-lg-3">
                                <label class="form-label">SMTP Port</label>
                                <input v-model="form.port" type="number" min="1" class="form-control" />
                                <div v-if="form.errors.port" class="text-danger small mt-1">{{ form.errors.port }}</div>
                            </div>
                            <div class="col-12 col-lg-3">
                                <label class="form-label">Encryption</label>
                                <select v-model="form.encryption" class="form-select">
                                    <option value="tls">TLS</option>
                                    <option value="ssl">SSL</option>
                                    <option value="none">None</option>
                                </select>
                                <div v-if="form.errors.encryption" class="text-danger small mt-1">{{ form.errors.encryption }}</div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <label class="form-label">Username</label>
                                <input v-model="form.username" type="text" class="form-control" autocomplete="off" />
                            </div>
                            <div class="col-12 col-lg-6">
                                <label class="form-label">Password</label>
                                <input v-model="form.password" type="password" class="form-control" autocomplete="new-password" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm settings-card">
                    <div class="card-body">
                        <div class="section-head">
                            <div>
                                <p class="section-kicker">Sender</p>
                                <h3 class="section-title">Default from identity</h3>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-12 col-lg-6">
                                <label class="form-label">From Address</label>
                                <input v-model="form.from_address" type="email" class="form-control" />
                                <div v-if="form.errors.from_address" class="text-danger small mt-1">{{ form.errors.from_address }}</div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <label class="form-label">From Name</label>
                                <input v-model="form.from_name" type="text" class="form-control" />
                                <div v-if="form.errors.from_name" class="text-danger small mt-1">{{ form.errors.from_name }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="action-bar">
                    <button type="submit" class="btn btn-primary btn-lg" :disabled="form.processing">
                        <i class="bi bi-save me-2"></i> Save SMTP Settings
                    </button>
                </div>
            </form>

            <Modal :show="showTestModal" max-width="xl" @close="showTestModal = false">
                <div class="test-modal">
                    <div class="modal-head">
                        <div>
                            <p class="section-kicker">Validation</p>
                            <h3 class="section-title">Send test email</h3>
                        </div>
                        <button type="button" class="btn btn-light rounded-circle" @click="showTestModal = false">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <form @submit.prevent="sendTestMail" class="row g-4 mt-1">
                        <div class="col-12">
                            <label class="form-label">Recipient Email</label>
                            <input v-model="testForm.email" type="email" class="form-control" />
                            <div v-if="testForm.errors.email" class="text-danger small mt-1">{{ testForm.errors.email }}</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Subject</label>
                            <input v-model="testForm.subject" type="text" class="form-control" />
                            <div v-if="testForm.errors.subject" class="text-danger small mt-1">{{ testForm.errors.subject }}</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Message</label>
                            <textarea v-model="testForm.message" rows="5" class="form-control"></textarea>
                            <div v-if="testForm.errors.message" class="text-danger small mt-1">{{ testForm.errors.message }}</div>
                        </div>
                        <div class="col-12 d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary" @click="showTestModal = false">Cancel</button>
                            <button type="submit" class="btn btn-primary" :disabled="testForm.processing">
                                <i class="bi bi-send me-2"></i> Send Test Email
                            </button>
                        </div>
                    </form>
                </div>
            </Modal>
        </section>
    </AuthLayout>
</template>

<style scoped>
.settings-page { display: grid; gap: 1.5rem; }
.hero-card {
    border-radius: 28px;
    background:
        radial-gradient(circle at top left, rgba(14, 165, 233, 0.12), transparent 42%),
        linear-gradient(135deg, #ffffff, #f8fbff 55%, #eef7ff);
}
.hero-card .card-body, .settings-card .card-body { padding: 1.5rem; }
.hero-flex, .metric-card .card-body, .section-head, .modal-head, .action-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}
.hero-kicker, .section-kicker {
    margin: 0 0 .35rem;
    text-transform: uppercase;
    letter-spacing: .14em;
    font-size: .72rem;
    font-weight: 700;
    color: #0ea5e9;
}
.hero-title, .section-title { margin: 0; color: #0f172a; }
.hero-title { font-size: clamp(2rem, 3vw, 2.6rem); font-weight: 800; }
.hero-text, .metric-helper { margin: .75rem 0 0; color: #64748b; line-height: 1.7; max-width: 720px; }
.metric-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; }
.metric-card { border-radius: 24px; }
.metric-icon { width: 3.2rem; height: 3.2rem; display: grid; place-items: center; border-radius: 1rem; font-size: 1.1rem; }
.metric-label { margin: 0 0 .35rem; color: #475569; font-weight: 700; }
.metric-value { margin: 0; font-size: 1.5rem; font-weight: 800; color: #0f172a; }
.settings-card { border-radius: 24px; }
.settings-stack { display: grid; gap: 1.5rem; }
.tone-blue .metric-icon { background: rgba(59, 130, 246, .12); color: #2563eb; }
.tone-amber .metric-icon { background: rgba(245, 158, 11, .14); color: #d97706; }
.tone-green .metric-icon { background: rgba(34, 197, 94, .12); color: #16a34a; }
.tone-slate .metric-icon { background: rgba(100, 116, 139, .12); color: #475569; }
.test-modal { padding: 1.5rem; }
.action-bar { justify-content: flex-end; flex-wrap: wrap; }
@media (max-width: 767px) {
    .hero-flex, .metric-card .card-body, .section-head, .modal-head, .action-bar {
        flex-direction: column;
        align-items: flex-start;
    }
    .action-bar .btn { width: 100%; }
}
</style>
