<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { Link } from "@inertiajs/vue3";
import { ref } from "vue";
import Swal from "sweetalert2";

// ---- PROPS ----
const props = defineProps({
    settings: Object,
    currencies: Object,
});

// ---- CURRENCIES FIX (remove duplicate variable name) ----
const currencyOptions = props.currencies ?? {
    USD: { name: "US Dollar", symbol: "$" },
    EUR: { name: "Euro", symbol: "€" },
    GBP: { name: "British Pound", symbol: "£" },
    INR: { name: "Indian Rupee", symbol: "₹" },
};

// ---- FORM ----
const form = useForm({
    currency: "USD",
    tax: "10",
    decimal_points: "allow",

    enable_paypal: true,
    enable_paypal_subscription: true,
    paypal_client_id: "AeL1rj4K7gfGfhKjQqH9Tp",
    paypal_client_secret: "EBfHgHgDRgmQqH9Tp",
    paypal_webhook_uri: "https://yourdomain.com/webhooks/paypal",
    paypal_webhook_id: "WH-5Y123456-ABC",
    paypal_base_uri: "https://api-m.paypal.com",

    enable_stripe: true,
    enable_stripe_subscription: true,
    stripe_key: "pk_test_51AbCdEfG",
    stripe_secret_key: "sk_test_51AbCdEfG",
    stripe_webhook_uri: "https://yourdomain.com/webhooks/stripe",
    stripe_webhook_secret: "whsec_AbCdEfG",
    stripe_base_uri: "https://api.stripe.com",

    enable_bank: true,
    enable_bank_subscription: true,
    bank_instructions: "Please transfer the amount to our bank account.",
    bank_requisites: "Bank: ABC Bank\nAccount: 123456789\nIFSC: ABC0123456",
});

// ---- SUBMIT ----
const submitForm = () => {
    Swal.fire({
        title: "Save Payment Settings?",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Yes, Save",
    }).then((r) => {
        if (r.isConfirmed) {
            Swal.fire("Saved!", "Settings updated (demo).", "success");
        }
    });
};
</script>

<template>
    <AuthLayout title="Payment Settings" description="Finance - Payment Settings" heading="Payment Settings">
       <div class="d-flex align-items-center justify-content-between  mb-4">
            <h3 class="text-xl mb-0">Payment Settings</h3>
        </div>
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <form @submit.prevent="submitForm">

                    <!-- GENERAL SETTINGS -->
                    <div class="card border-0 mb-4">
                        <div class="card-header">
                            <h3 class="card-title text-white">Setup Payment Settings</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6 mb-3">
                                    <h6>Default Currency</h6>
                                    <select v-model="form.currency" class="form-select">
                                        <option
                                            v-for="(c, k) in currencyOptions"
                                            :key="k"
                                            :value="k"
                                        >
                                            {{ c.name }} - {{ k }} ({{ c.symbol }})
                                        </option>
                                    </select>
                                </div>

                                <div class="col-lg-6 mb-3">
                                    <h6>Tax Rate (%)</h6>
                                    <input
                                        type="text"
                                        v-model="form.tax"
                                        class="form-control"
                                        placeholder="Tax Rate"
                                    />
                                </div>

                                <div class="col-lg-6 mb-3">
                                    <h6>Decimal Points</h6>
                                    <select v-model="form.decimal_points" class="form-select">
                                        <option value="allow">Allow</option>
                                        <option value="deny">Deny</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- === ONLINE PAYMENT SECTION (PayPal & Stripe will now SHOW) === -->
                    <div class="card border-0 mb-4">
                        <div class="card-header">
                            <h3 class="card-title text-white">Online Payment</h3>
                        </div>
                        <div class="card-body">

                            <!-- PayPal -->
                            <div class="card border-0 special-shadow mb-4">
                                <div class="card-body">
                                    <h6 class="font-weight-bold mb-4">
                                        <i class="fa-brands fa-cc-paypal mr-2"></i> PayPal Gateway
                                    </h6>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="custom-switch">
                                                <input
                                                    type="checkbox"
                                                    v-model="form.enable_paypal"
                                                    class="custom-switch-input"
                                                />
                                                <span class="custom-switch-indicator"></span>
                                                <span class="custom-switch-description">Use Prepaid</span>
                                            </label>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="custom-switch">
                                                <input
                                                    type="checkbox"
                                                    v-model="form.enable_paypal_subscription"
                                                    class="custom-switch-input"
                                                />
                                                <span class="custom-switch-indicator"></span>
                                                <span class="custom-switch-description">Use Subscription</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6 mb-3">
                                            <h6>Client ID</h6>
                                            <input type="text" v-model="form.paypal_client_id" class="form-control" />
                                        </div>

                                        <div class="col-lg-6 mb-3">
                                            <h6>Client Secret</h6>
                                            <input type="text" v-model="form.paypal_client_secret" class="form-control" />
                                        </div>

                                        <div class="col-lg-6 mb-3">
                                            <h6>Webhook URI</h6>
                                            <input type="text" v-model="form.paypal_webhook_uri" class="form-control" />
                                        </div>

                                        <div class="col-lg-6 mb-3">
                                            <h6>Webhook ID</h6>
                                            <input type="text" v-model="form.paypal_webhook_id" class="form-control" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Stripe -->
                            <div class="card border-0 special-shadow mb-4">
                                <div class="card-body">
                                    <h6 class="font-weight-bold mb-4">
                                        <i class="fa-brands fa-cc-stripe mr-2"></i> Stripe Gateway
                                    </h6>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="custom-switch">
                                                <input
                                                    type="checkbox"
                                                    v-model="form.enable_stripe"
                                                    class="custom-switch-input"
                                                />
                                                <span class="custom-switch-indicator"></span>
                                                <span class="custom-switch-description">Use Prepaid</span>
                                            </label>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="custom-switch">
                                                <input
                                                    type="checkbox"
                                                    v-model="form.enable_stripe_subscription"
                                                    class="custom-switch-input"
                                                />
                                                <span class="custom-switch-indicator"></span>
                                                <span class="custom-switch-description">Use Subscription</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6 mb-3">
                                            <h6>Key</h6>
                                            <input type="text" v-model="form.stripe_key" class="form-control" />
                                        </div>

                                        <div class="col-lg-6 mb-3">
                                            <h6>Secret Key</h6>
                                            <input type="text" v-model="form.stripe_secret_key" class="form-control" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- OFFLINE PAYMENT -->
                    <div class="card border-0 mb-4">
                        <div class="card-header">
                            <h3 class="card-title text-white">Offline Payment</h3>
                        </div>

                        <div class="card-body">
                            <div class="card border-0 special-shadow mb-4">
                                <div class="card-body">

                                    <h6 class="font-weight-bold mb-4">
                                        <i class="fa-solid fa-money-check-dollar-pen mr-2"></i>
                                        Bank Transfer
                                    </h6>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="custom-switch">
                                                <input
                                                    type="checkbox"
                                                    v-model="form.enable_bank"
                                                    class="custom-switch-input"
                                                />
                                                <span class="custom-switch-indicator"></span>
                                                <span class="custom-switch-description">Use Prepaid</span>
                                            </label>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="custom-switch">
                                                <input
                                                    type="checkbox"
                                                    v-model="form.enable_bank_subscription"
                                                    class="custom-switch-input"
                                                />
                                                <span class="custom-switch-indicator"></span>
                                                <span class="custom-switch-description">Use Subscription</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6 mb-3">
                                            <h6>Customer Instructions</h6>
                                            <textarea
                                                v-model="form.bank_instructions"
                                                class="form-control"
                                                rows="5"
                                            ></textarea>
                                        </div>

                                        <div class="col-lg-6 mb-3">
                                            <h6>Bank Requisites</h6>
                                            <textarea
                                                v-model="form.bank_requisites"
                                                class="form-control"
                                                rows="5"
                                            ></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <Link :href="route('superAdmin.payment')" class="btn btn-cancel mr-2">
                                    Cancel
                                </Link>
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </AuthLayout>
</template>


<style scoped>
.breadcrumb {
    background: transparent;
    padding: 0;
    margin: 0;
}
.breadcrumb-item a {
    color: #3b82f6;
    text-decoration: none;
}
.breadcrumb-item.active {
    color: #6b7280;
}
.card {
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}
.special-shadow {
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.custom-switch {
    display: flex;
    align-items: center;
    cursor: pointer;
}
.custom-switch-input {
    width: 44px;
    height: 24px;
    position: relative;
    appearance: none;
    background: #cbd5e0;
    border-radius: 12px;
    outline: none;
    cursor: pointer;
    transition: background 0.3s;
}
.custom-switch-input:checked {
    background: #3b82f6;
}
.custom-switch-input::before {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: white;
    top: 2px;
    left: 2px;
    transition: transform 0.3s;
}
.custom-switch-input:checked::before {
    transform: translateX(20px);
}
.custom-switch-description {
    margin-left: 10px;
    font-size: 0.9rem;
}
h6 {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}
.text-required {
    color: #ef4444;
}
.text-info {
    color: #3b82f6;
}
.text-primary {
    color: #3b82f6;
}
.text-muted {
    color: #6b7280;
}
.btn-cancel {
    background: #6b7280;
    color: white;
    border: none;
    padding: 0.5rem 1.5rem;
    border-radius: 8px;
}
.btn-cancel:hover {
    background: #4b5563;
}
.btn-primary {
    background: #3b82f6;
    color: white;
    border: none;
    padding: 0.5rem 1.5rem;
    border-radius: 8px;
}
.btn-primary:hover {
    background: #2563eb;
}
</style>