<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { useForm, Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import Swal from "sweetalert2";

// ---------------- PROPS ----------------
const props = defineProps({
    settings: Object,
    app: Object,
});

// ---------------- FORM ----------------
const form = useForm({
    site_name: props.app?.name ?? "",
    site_website: props.app?.url ?? "",
    site_email: props.app?.email ?? "",
    time_zone: props.app?.timezone ?? "UTC",

    default_user_group: props.settings?.default_user ?? "marketing",
    support_email: props.settings?.support_email ?? "enabled",
    user_notification: props.settings?.user_notification ?? "enabled",
    user_support: props.settings?.user_support ?? "enabled",
    theme: props.settings?.default_theme ?? "light",

    // Live Chat
    enable_live_chat: props.settings?.live_chat === "on",
    live_chat_link: props.settings?.live_chat_link ?? "",

    // Google reCaptcha
    enable_recaptcha: props.settings?.recaptcha_enable === "on",
    recaptcha_site_key: props.settings?.recaptcha_site_key ?? "",
    recaptcha_secret_key: props.settings?.recaptcha_secret_key ?? "",

    // Google Analytics
    enable_analytics: props.settings?.analytics_enable === "on",
    google_analytics_id: props.settings?.analytics_id ?? "",

    // Google Maps
    enable_maps: props.settings?.maps_enable === "on",
    google_maps_key: props.settings?.maps_key ?? "",

    // GDPR
    enable_gdpr: props.settings?.gdpr ?? false,
});

// ---------------- SUBMIT ----------------
const submitForm = () => {
    Swal.fire({
        title: "Save Global Settings?",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Yes, Save",
    }).then((r) => {
        if (r.isConfirmed) {
            form.post(route("admin.settings.global.store"), {
                onSuccess: () => {
                    Swal.fire("Saved!", "Settings updated successfully.", "success");
                },
            });
        }
    });
};
</script>

<template>
<AuthLayout
    title="Global Settings"
    description="Admin - Global Settings"
    heading="Global Settings"
>
    <!-- PAGE HEADER -->
    <div class="page-header mt-5-7 justify-content-center mb-4">
        <div class="page-leftheader text-center">
            <h4 class="page-title mb-0">Global Settings</h4>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <form @submit.prevent="submitForm">

                <!-- GENERAL SETTINGS -->
                <div class="card border-0 special-shadow mb-4">

                    <div class="card-body">
                        <h6 class="fs-16 font-weight-bold mb-4">General Settings</h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6>Website Name</h6>
                                <input v-model="form.site_name" class="form-control" />
                            </div>

                            <div class="col-md-6 mb-3">
                                <h6>Website URL</h6>
                                <input v-model="form.site_website" class="form-control" />
                            </div>

                            <div class="col-md-6 mb-3">
                                <h6>Website Email Address</h6>
                                <input v-model="form.site_email" class="form-control" />
                            </div>

                            <div class="col-md-6 mb-3">
                                <h6>Time Zone</h6>
                                <select v-model="form.time_zone" class="form-select">
                                    <option value="UTC">UTC</option>
                                    <option value="Asia/Kolkata">Asia/Kolkata</option>
                                    <option value="America/Los_Angeles">Los Angeles</option>
                                    <option value="Europe/London">London</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- LIVE CHAT -->
               <div class="card border-0 special-shadow mb-4">
                    <div class="card-body">
                        <h6 class="font-weight-bold mb-4">Live Chat (tawk.to)</h6>

                        <label class="custom-switch mb-3">
                            <input
                                type="checkbox"
                                v-model="form.enable_live_chat"
                                class="custom-switch-input"
                            />
                            <span class="custom-switch-indicator"></span>
                            <span class="custom-switch-description">
                                Enable Live Chat
                            </span>
                        </label>

                        <div v-if="form.enable_live_chat" class="mt-3">
                            <input
                                v-model="form.live_chat_link"
                                class="form-control"
                                placeholder="Direct Chat Link"
                            />
                        </div>
                    </div>
                </div>


                <!-- GOOGLE ANALYTICS -->
                <div class="card border-0 special-shadow mb-4">
                    <div class="card-body">
                        <h6 class="font-weight-bold mb-4">Google Analytics</h6>

                        <label class="custom-switch mb-3">
                            <input
                                type="checkbox"
                                v-model="form.enable_analytics"
                                class="custom-switch-input"
                            />
                            <span class="custom-switch-indicator"></span>
                            <span class="custom-switch-description">
                                Enable Google Analytics
                            </span>
                        </label>

                        <div v-if="form.enable_analytics" class="mt-3">
                            <input
                                v-model="form.google_analytics_id"
                                class="form-control"
                                placeholder="G-XXXXXXXXXX"
                            />
                        </div>
                    </div>
                </div>


                <!-- GOOGLE MAPS -->
               <div class="card border-0 special-shadow mb-4">
                    <div class="card-body">
                        <h6 class="font-weight-bold mb-4">Google Maps</h6>

                        <label class="custom-switch mb-3">
                            <input
                                type="checkbox"
                                v-model="form.enable_maps"
                                class="custom-switch-input"
                            />
                            <span class="custom-switch-indicator"></span>
                            <span class="custom-switch-description">
                                Enable Google Maps
                            </span>
                        </label>

                        <div v-if="form.enable_maps" class="mt-3">
                            <input
                                v-model="form.google_maps_key"
                                class="form-control"
                                placeholder="Google Maps API Key"
                            />
                        </div>
                    </div>
                </div>


                <!-- GDPR -->
               <div class="card border-0 special-shadow mb-4">
                    <div class="card-body">
                        <h6 class="font-weight-bold mb-3">GDPR Policy</h6>

                        <label class="custom-switch">
                            <input
                                type="checkbox"
                                v-model="form.enable_gdpr"
                                class="custom-switch-input"
                            />
                            <span class="custom-switch-indicator"></span>
                            <span class="custom-switch-description">
                                Enable GDPR Consent Popup
                            </span>
                        </label>
                    </div>
                </div>


                <!-- ACTIONS -->
                <div class="text-right">
                    <Link :href="route('superAdmin.globalsetting')" class="btn btn-danger mr-2">
                        Cancel
                    </Link>
                    <button type="submit" class="btn btn-primary">
                        Save
                    </button>
                </div>

            </form>
        </div>
    </div>
</AuthLayout>
</template>
<style scoped>
    .custom-switch {
    display: flex;
    align-items: center;
    cursor: pointer;
}

.custom-switch-input {
    width: 44px;
    height: 24px;
    appearance: none;
    background: #cbd5e0;
    border-radius: 12px;
    position: relative;
    outline: none;
    transition: background 0.3s;
    cursor: pointer;
}

.custom-switch-input:checked {
    background: #3b82f6;
}

.custom-switch-input::before {
    content: "";
    position: absolute;
    width: 20px;
    height: 20px;
    background: #fff;
    border-radius: 50%;
    top: 2px;
    left: 2px;
    transition: transform 0.3s;
}

.custom-switch-input:checked::before {
    transform: translateX(20px);
}

.custom-switch-indicator {
    display: none;
}

.custom-switch-description {
    margin-left: 10px;
    font-size: 0.9rem;
}
</style>
