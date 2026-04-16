<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import timezones from "@/Components/Common/timezone.js";
import { useForm } from "@inertiajs/vue3";
import Swal from "sweetalert2/dist/sweetalert2.js";

const props = defineProps({
    settings: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    site_name: props.settings?.site_name || "",
    site_website: props.settings?.site_website || "",
    site_email: props.settings?.site_email || "",
    time_zone: props.settings?.time_zone || "Asia/Kolkata",
    default_user_group: props.settings?.default_user_group || "marketing",
    support_email: Boolean(props.settings?.support_email),
    user_notification: Boolean(props.settings?.user_notification),
    user_support: Boolean(props.settings?.user_support),
    theme: props.settings?.theme || "light",
    enable_live_chat: Boolean(props.settings?.enable_live_chat),
    live_chat_link: props.settings?.live_chat_link || "",
    enable_recaptcha: Boolean(props.settings?.enable_recaptcha),
    recaptcha_site_key: props.settings?.recaptcha_site_key || "",
    recaptcha_secret_key: props.settings?.recaptcha_secret_key || "",
    enable_analytics: Boolean(props.settings?.enable_analytics),
    google_analytics_id: props.settings?.google_analytics_id || "",
    enable_maps: Boolean(props.settings?.enable_maps),
    google_maps_key: props.settings?.google_maps_key || "",
    enable_gdpr: Boolean(props.settings?.enable_gdpr),
});

const metricCards = [
    {
        label: "Timezone",
        value: form.time_zone,
        helper: "Default platform timezone",
        icon: "fa-solid fa-clock",
        tone: "tone-blue",
    },
    {
        label: "Theme",
        value: form.theme.charAt(0).toUpperCase() + form.theme.slice(1),
        helper: "Default application appearance",
        icon: "fa-solid fa-palette",
        tone: "tone-amber",
    },
    {
        label: "Live Chat",
        value: form.enable_live_chat ? "Enabled" : "Disabled",
        helper: "Visitor support status",
        icon: "fa-solid fa-comments",
        tone: "tone-green",
    },
    {
        label: "Security",
        value: form.enable_recaptcha ? "Protected" : "Standard",
        helper: "reCAPTCHA protection status",
        icon: "fa-solid fa-shield-halved",
        tone: "tone-slate",
    },
];

const submitForm = () => {
    Swal.fire({
        title: "Save global settings?",
        text: "These changes will update the platform defaults.",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Save settings",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#0ea5e9",
    }).then((result) => {
        if (result.isConfirmed) {
            form.post(route("superAdmin.globalsetting.update"), {
                preserveScroll: true,
            });
        }
    });
};
</script>

<template>
    <AuthLayout title="Global Settings" description="Configure platform-wide preferences and integrations">
        <section class="settings-page">
            <div class="hero-card card border-0 shadow-sm">
                <div class="card-body">
                    <p class="hero-kicker">General Settings</p>
                    <h1 class="hero-title">Global Settings</h1>
                    <p class="hero-text">
                        Manage the platform identity, support defaults, visual preferences, and third-party service
                        switches from one place.
                    </p>
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
                                <p class="section-kicker">Platform</p>
                                <h3 class="section-title">General details</h3>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-12 col-lg-6">
                                <label class="form-label">Website Name</label>
                                <input v-model="form.site_name" type="text" class="form-control" />
                                <div v-if="form.errors.site_name" class="text-danger small mt-1">{{ form.errors.site_name }}</div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <label class="form-label">Website URL</label>
                                <input v-model="form.site_website" type="url" class="form-control" />
                                <div v-if="form.errors.site_website" class="text-danger small mt-1">{{ form.errors.site_website }}</div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <label class="form-label">Website Email</label>
                                <input v-model="form.site_email" type="email" class="form-control" />
                                <div v-if="form.errors.site_email" class="text-danger small mt-1">{{ form.errors.site_email }}</div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <label class="form-label">Timezone</label>
                                <select v-model="form.time_zone" class="form-select">
                                    <option v-for="timezone in timezones" :key="timezone.value" :value="timezone.value">
                                        {{ timezone.label }}
                                    </option>
                                </select>
                                <div v-if="form.errors.time_zone" class="text-danger small mt-1">{{ form.errors.time_zone }}</div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <label class="form-label">Default User Group</label>
                                <input v-model="form.default_user_group" type="text" class="form-control" />
                            </div>
                            <div class="col-12 col-lg-6">
                                <label class="form-label">Default Theme</label>
                                <select v-model="form.theme" class="form-select">
                                    <option value="light">Light</option>
                                    <option value="dark">Dark</option>
                                    <option value="system">System</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm settings-card">
                    <div class="card-body">
                        <div class="section-head">
                            <div>
                                <p class="section-kicker">Support</p>
                                <h3 class="section-title">Experience controls</h3>
                            </div>
                        </div>

                        <div class="toggle-grid">
                            <label class="toggle-card">
                                <div>
                                    <h4>Support Email</h4>
                                    <p>Show support email availability across the platform.</p>
                                </div>
                                <input v-model="form.support_email" type="checkbox" class="settings-switch" />
                            </label>

                            <label class="toggle-card">
                                <div>
                                    <h4>User Notifications</h4>
                                    <p>Enable platform-level user notification support.</p>
                                </div>
                                <input v-model="form.user_notification" type="checkbox" class="settings-switch" />
                            </label>

                            <label class="toggle-card">
                                <div>
                                    <h4>User Support</h4>
                                    <p>Enable customer support access points for users.</p>
                                </div>
                                <input v-model="form.user_support" type="checkbox" class="settings-switch" />
                            </label>

                            <label class="toggle-card">
                                <div>
                                    <h4>Enable GDPR</h4>
                                    <p>Show GDPR consent and compliance prompts.</p>
                                </div>
                                <input v-model="form.enable_gdpr" type="checkbox" class="settings-switch" />
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-12 col-xl-4">
                        <div class="card border-0 shadow-sm settings-card h-100">
                            <div class="card-body">
                                <div class="integration-head">
                                    <div>
                                        <p class="section-kicker">Integration</p>
                                        <h3 class="section-title">Live Chat</h3>
                                    </div>
                                    <input v-model="form.enable_live_chat" type="checkbox" class="settings-switch" />
                                </div>
                                <p class="integration-text">Enable and manage the default live support entry point.</p>
                                <input
                                    v-model="form.live_chat_link"
                                    type="url"
                                    class="form-control"
                                    placeholder="https://"
                                    :disabled="!form.enable_live_chat"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-xl-4">
                        <div class="card border-0 shadow-sm settings-card h-100">
                            <div class="card-body">
                                <div class="integration-head">
                                    <div>
                                        <p class="section-kicker">Security</p>
                                        <h3 class="section-title">Google reCAPTCHA</h3>
                                    </div>
                                    <input v-model="form.enable_recaptcha" type="checkbox" class="settings-switch" />
                                </div>
                                <p class="integration-text">Protect signup and public forms from spam and abuse.</p>
                                <div class="d-grid gap-3">
                                    <input
                                        v-model="form.recaptcha_site_key"
                                        type="text"
                                        class="form-control"
                                        placeholder="Site key"
                                        :disabled="!form.enable_recaptcha"
                                    />
                                    <input
                                        v-model="form.recaptcha_secret_key"
                                        type="text"
                                        class="form-control"
                                        placeholder="Secret key"
                                        :disabled="!form.enable_recaptcha"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-xl-4">
                        <div class="card border-0 shadow-sm settings-card h-100">
                            <div class="card-body">
                                <div class="integration-head">
                                    <div>
                                        <p class="section-kicker">Tracking & Maps</p>
                                        <h3 class="section-title">External Services</h3>
                                    </div>
                                </div>
                                <div class="mini-toggle mb-3">
                                    <label class="d-flex align-items-center justify-content-between gap-3 w-100">
                                        <span>
                                            <strong>Analytics</strong>
                                            <small class="d-block text-muted">Google Analytics measurement ID</small>
                                        </span>
                                        <input v-model="form.enable_analytics" type="checkbox" class="settings-switch" />
                                    </label>
                                </div>
                                <input
                                    v-model="form.google_analytics_id"
                                    type="text"
                                    class="form-control mb-3"
                                    placeholder="G-XXXXXXXXXX"
                                    :disabled="!form.enable_analytics"
                                />
                                <div class="mini-toggle mb-3">
                                    <label class="d-flex align-items-center justify-content-between gap-3 w-100">
                                        <span>
                                            <strong>Google Maps</strong>
                                            <small class="d-block text-muted">Maps API integration</small>
                                        </span>
                                        <input v-model="form.enable_maps" type="checkbox" class="settings-switch" />
                                    </label>
                                </div>
                                <input
                                    v-model="form.google_maps_key"
                                    type="text"
                                    class="form-control"
                                    placeholder="Maps API key"
                                    :disabled="!form.enable_maps"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="action-bar">
                    <button type="submit" class="btn btn-primary btn-lg" :disabled="form.processing">
                        <i class="bi bi-save me-2"></i> Save Global Settings
                    </button>
                </div>
            </form>
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
.hero-text { margin: .75rem 0 0; max-width: 760px; color: #64748b; line-height: 1.7; }
.metric-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; }
.metric-card { border-radius: 24px; }
.metric-card .card-body { display: flex; align-items: center; gap: 1rem; }
.metric-icon { width: 3.2rem; height: 3.2rem; display: grid; place-items: center; border-radius: 1rem; font-size: 1.1rem; }
.metric-label { margin: 0 0 .35rem; color: #475569; font-weight: 700; }
.metric-value { margin: 0; font-size: 1.5rem; font-weight: 800; color: #0f172a; }
.metric-helper { margin: .35rem 0 0; color: #64748b; font-size: .9rem; }
.tone-blue .metric-icon { background: rgba(59, 130, 246, .12); color: #2563eb; }
.tone-amber .metric-icon { background: rgba(245, 158, 11, .14); color: #d97706; }
.tone-green .metric-icon { background: rgba(34, 197, 94, .12); color: #16a34a; }
.tone-slate .metric-icon { background: rgba(100, 116, 139, .12); color: #475569; }
.settings-card { border-radius: 24px; }
.settings-stack { display: grid; gap: 1.5rem; }
.section-head, .integration-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; }
.toggle-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem; }
.toggle-card {
    padding: 1.1rem 1.15rem;
    border: 1px solid #dbe4f0;
    border-radius: 20px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    background: #f8fbff;
}
.toggle-card h4 { margin: 0 0 .35rem; color: #0f172a; font-size: 1rem; }
.toggle-card p, .integration-text { margin: 0; color: #64748b; line-height: 1.6; }
.mini-toggle { padding: .85rem 1rem; border: 1px solid #dbe4f0; border-radius: 16px; background: #f8fbff; }
.action-bar { display: flex; justify-content: flex-end; }
.settings-switch {
    position: relative;
    flex: 0 0 auto;
    width: 3.2rem;
    height: 1.8rem;
    appearance: none;
    border: 0;
    border-radius: 999px;
    background: #cbd5e1;
    cursor: pointer;
    transition: background-color .2s ease;
    margin: 0;
}
.settings-switch::before {
    content: "";
    position: absolute;
    top: .2rem;
    left: .2rem;
    width: 1.4rem;
    height: 1.4rem;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.18);
    transition: transform .2s ease;
}
.settings-switch:checked {
    background: #0ea5e9;
}
.settings-switch:checked::before {
    transform: translateX(1.4rem);
}
.settings-switch:focus {
    outline: 0;
    box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.18);
}
@media (max-width: 767px) {
    .action-bar { justify-content: stretch; }
    .action-bar .btn { width: 100%; }
}
</style>
