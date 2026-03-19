<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import Swal from "sweetalert2";
import { ref } from "vue";

// ---------------- PROPS ----------------
const props = defineProps({
    smtp: Object,
});

// ---------------- SMTP FORM ----------------
const form = useForm({
    host: props.smtp?.host ?? "",
    port: props.smtp?.port ?? "",
    username: props.smtp?.username ?? "",
    password: props.smtp?.password ?? "",
    from_address: props.smtp?.from_address ?? "",
    from_name: props.smtp?.from_name ?? "",
    encryption: props.smtp?.encryption ?? "tls",
});

// ---------------- TEST EMAIL FORM ----------------
const testForm = useForm({
    email: "",
    subject: "",
    message: "",
});

// ---------------- MODAL STATE ----------------
const showTestModal = ref(false);

// ---------------- SUBMIT SMTP ----------------
const submitForm = () => {
    Swal.fire({
        title: "Save SMTP Settings?",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Yes, Save",
    }).then((r) => {
        if (r.isConfirmed) {
            form.post(route("admin.settings.smtp.store"), {
                onSuccess: () => {
                    Swal.fire("Saved!", "SMTP settings updated.", "success");
                },
            });
        }
    });
};

// ---------------- SEND TEST MAIL ----------------
const sendTestMail = () => {
    testForm.post(route("admin.settings.smtp.test"), {
        onSuccess: () => {
            Swal.fire("Sent!", "Test email sent successfully.", "success");
            testForm.reset();
            showTestModal.value = false;
        },
    });
};
</script>

<template>
<AuthLayout
    title="SMTP Settings"
    description="Admin - SMTP Settings"
    heading="SMTP Settings"
>
    <!-- PAGE HEADER -->
    <div class="page-header mt-5-7 justify-content-center mb-4">
        <div class="page-leftheader text-center">
            <h4 class="page-title mb-0">SMTP Settings</h4>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <form @submit.prevent="submitForm">
                <div class="card border-0 special-shadow">
                    <div class="card-body">
                      <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6>SMTP Host</h6>
                                <input v-model="form.host" class="form-control" />
                            </div>

                            <div class="col-md-6 mb-3">
                                <h6>SMTP Port</h6>
                                <input v-model="form.port" class="form-control" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6>SMTP Username</h6>
                                <input v-model="form.username" class="form-control" />
                            </div>

                            <div class="col-md-6">
                                <h6>SMTP Password</h6>
                                <input v-model="form.password" type="password" class="form-control" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6>Sender Email Address</h6>
                                <input v-model="form.from_address" class="form-control" />
                            </div>

                            <div class="col-md-6">
                                <h6>Sender Name</h6>
                                <input v-model="form.from_name" class="form-control" />
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6>SMTP Encryption</h6>
                            <select v-model="form.encryption" class="form-select">
                                <option value="tls">TLS</option>
                                <option value="ssl">SSL</option>
                            </select>
                        </div>

                        <!-- ACTIONS -->
                        <div class="text-right">
                            <button
                                type="button"
                                class="btn btn-info mr-2"
                                @click="showTestModal = true"
                            >
                                Test
                            </button>
                            <button type="submit" class="btn btn-primary">
                                Save
                            </button>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- PURE VUE MODAL -->
    <transition name="fade">
        <div v-if="showTestModal" class="vue-modal-overlay">
            <div class="vue-modal">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="fa fa-envelope"></i> Send Test Email
                    </h4>
                    <button class="btn-close" @click="showTestModal = false"></button>
                </div>

                <form @submit.prevent="sendTestMail">
                    <div class="modal-body">

                        <div class="mb-3">
                            <h6>To Email Address</h6>
                            <input v-model="testForm.email" class="form-control" required />
                        </div>

                        <div class="mb-3">
                            <h6>Email Subject</h6>
                            <input v-model="testForm.subject" class="form-control" required />
                        </div>

                        <div class="mb-3">
                            <h6>Email Message</h6>
                            <textarea
                                v-model="testForm.message"
                                rows="4"
                                class="form-control"
                                required
                            ></textarea>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-danger"
                            @click="showTestModal = false"
                        >
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Send
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </transition>
</AuthLayout>
</template>

<style scoped>
/* ---------- PURE VUE MODAL ---------- */
.vue-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.55);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.vue-modal {
    background: #fff;
    width: 100%;
    max-width: 520px;
    border-radius: 8px;
    overflow: hidden;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
