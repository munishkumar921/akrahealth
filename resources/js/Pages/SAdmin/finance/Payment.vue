<script setup>
import { computed, ref } from "vue";
import { Link, useForm } from "@inertiajs/vue3";
import Swal from "sweetalert2";
import AuthLayout from "@/Layouts/AuthLayout.vue";

const props = defineProps({
    settings: Object,
    currencies: Object,
});

const currencyOptions = props.currencies ?? {
    USD: { name: "US Dollar", symbol: "$" },
    EUR: { name: "Euro", symbol: "€" },
    GBP: { name: "British Pound", symbol: "£" },
    INR: { name: "Indian Rupee", symbol: "₹" },
};

const form = useForm({
    currency: props.settings?.currency ?? "USD",
    tax: props.settings?.tax ?? "10",
    decimal_points: props.settings?.decimal_points ?? "allow",

    enable_paypal: props.settings?.enable_paypal ?? true,
    enable_paypal_subscription: props.settings?.enable_paypal_subscription ?? true,
    paypal_client_id: props.settings?.paypal_client_id ?? "AeL1rj4K7gfGfhKjQqH9Tp",
    paypal_client_secret: props.settings?.paypal_client_secret ?? "EBfHgHgDRgmQqH9Tp",
    paypal_webhook_uri: props.settings?.paypal_webhook_uri ?? "https://yourdomain.com/webhooks/paypal",
    paypal_webhook_id: props.settings?.paypal_webhook_id ?? "WH-5Y123456-ABC",
    paypal_base_uri: props.settings?.paypal_base_uri ?? "https://api-m.paypal.com",

    enable_stripe: props.settings?.enable_stripe ?? true,
    enable_stripe_subscription: props.settings?.enable_stripe_subscription ?? true,
    stripe_key: props.settings?.stripe_key ?? "pk_test_51AbCdEfG",
    stripe_secret_key: props.settings?.stripe_secret_key ?? "sk_test_51AbCdEfG",
    stripe_webhook_uri: props.settings?.stripe_webhook_uri ?? "https://yourdomain.com/webhooks/stripe",
    stripe_webhook_secret: props.settings?.stripe_webhook_secret ?? "whsec_AbCdEfG",
    stripe_base_uri: props.settings?.stripe_base_uri ?? "https://api.stripe.com",

    enable_bank: props.settings?.enable_bank ?? true,
    enable_bank_subscription: props.settings?.enable_bank_subscription ?? true,
    bank_instructions: props.settings?.bank_instructions ?? "Please transfer the amount to our bank account.",
    bank_requisites: props.settings?.bank_requisites ?? "Bank: ABC Bank\nAccount: 123456789\nIFSC: ABC0123456",
});

const activeSection = ref("all");
const sectionKeyword = ref("");

const sectionTabs = [
    { id: "all", label: "All sections" },
    { id: "general", label: "General" },
    { id: "paypal", label: "PayPal" },
    { id: "stripe", label: "Stripe" },
    { id: "bank", label: "Bank transfer" },
];

const matchesSearch = (terms = []) => {
    const search = sectionKeyword.value.trim().toLowerCase();
    if (!search) return true;

    return terms.some((term) => term.toLowerCase().includes(search));
};

const showSection = (id, terms = []) => (activeSection.value === "all" || activeSection.value === id) && matchesSearch(terms);

const enabledGatewayCount = computed(() => [form.enable_paypal, form.enable_stripe, form.enable_bank].filter(Boolean).length);
const subscriptionGatewayCount = computed(
    () => [form.enable_paypal_subscription, form.enable_stripe_subscription, form.enable_bank_subscription].filter(Boolean).length
);

const metricCards = computed(() => [
    {
        label: "Enabled Gateways",
        value: enabledGatewayCount.value,
        helper: "Gateways available for payment collection",
        icon: "fa-solid fa-plug-circle-check",
        tone: "tone-blue",
    },
    {
        label: "Subscription Ready",
        value: subscriptionGatewayCount.value,
        helper: "Gateways enabled for recurring billing",
        icon: "fa-solid fa-repeat",
        tone: "tone-green",
    },
    {
        label: "Default Currency",
        value: form.currency,
        helper: currencyOptions[form.currency]?.name ?? "Configured settlement currency",
        icon: "fa-solid fa-money-bill-wave",
        tone: "tone-indigo",
    },
    {
        label: "Tax Rate",
        value: `${form.tax}%`,
        helper: form.decimal_points === "allow" ? "Decimals allowed in billing" : "Decimals restricted in billing",
        icon: "fa-solid fa-percent",
        tone: "tone-amber",
    },
]);

const submitForm = () => {
    Swal.fire({
        title: "Save payment settings?",
        text: "This will update the configured payment preferences for the platform.",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Yes, save",
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire("Saved", "Settings updated successfully.", "success");
        }
    });
};
</script>

<template>
    <AuthLayout title="Payment Settings" description="Configure payment methods and billing preferences">
        <section class="payment-page">
            <div class="settings-hero">
                <div>
                    <p class="hero-kicker">Payment Operations</p>
                    <h1 class="hero-title">Payment settings</h1>
                    <p class="hero-copy">
                        Configure collection rules, supported gateways, recurring billing readiness, and offline payment
                        instructions from one control surface.
                    </p>
                </div>

                <div class="hero-actions">
                    <Link :href="route('superAdmin.payment')" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i> Reset view
                    </Link>
                    <button type="button" class="btn btn-primary" @click="submitForm">
                        <i class="bi bi-check2-circle me-1"></i> Save settings
                    </button>
                </div>
            </div>

            <div class="metrics-grid">
                <article v-for="card in metricCards" :key="card.label" class="metric-card" :class="card.tone">
                    <div>
                        <p class="metric-label">{{ card.label }}</p>
                        <h3 class="metric-value">{{ card.value }}</h3>
                        <p class="metric-helper">{{ card.helper }}</p>
                    </div>
                    <div class="metric-icon">
                        <i :class="card.icon"></i>
                    </div>
                </article>
            </div>

            <div class="card border-0 shadow-sm filter-card">
                <div class="card-body">
                    <div class="filter-header">
                        <div>
                            <p class="filter-kicker">Filters</p>
                            <h3 class="filter-title">Jump to a payment section</h3>
                        </div>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-xl-5">
                            <label class="form-label text-muted small text-uppercase mb-2">Search settings</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 border rounded-circle-left">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input
                                    v-model="sectionKeyword"
                                    type="search"
                                    class="form-control border-start-0"
                                    placeholder="Search by gateway, webhook, key, bank, or billing setting"
                                />
                            </div>
                        </div>

                        <div class="col-12 col-xl-7">
                            <label class="form-label text-muted small text-uppercase mb-2">Section</label>
                            <div class="section-pills">
                                <button
                                    v-for="tab in sectionTabs"
                                    :key="tab.id"
                                    type="button"
                                    class="section-pill"
                                    :class="{ 'section-pill--active': activeSection === tab.id }"
                                    @click="activeSection = tab.id"
                                >
                                    {{ tab.label }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form class="settings-grid" @submit.prevent="submitForm">
                <article v-if="showSection('general', ['general', 'currency', 'tax', 'decimal'])" class="settings-card settings-card--wide">
                    <div class="settings-card__head">
                        <div>
                            <p class="settings-kicker">General</p>
                            <h3>Core payment defaults</h3>
                        </div>
                        <span class="panel-chip">Platform-wide</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Default Currency</label>
                            <select v-model="form.currency" class="form-select">
                                <option v-for="(currency, code) in currencyOptions" :key="code" :value="code">
                                    {{ currency.name }} - {{ code }} ({{ currency.symbol }})
                                </option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tax Rate (%)</label>
                            <input v-model="form.tax" type="text" class="form-control" placeholder="Tax Rate" />
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Decimal Points</label>
                            <select v-model="form.decimal_points" class="form-select">
                                <option value="allow">Allow</option>
                                <option value="deny">Deny</option>
                            </select>
                        </div>
                    </div>
                </article>

                <article v-if="showSection('paypal', ['paypal', 'webhook', 'client', 'subscription'])" class="settings-card">
                    <div class="settings-card__head">
                        <div>
                            <p class="settings-kicker">Online Gateway</p>
                            <h3>PayPal</h3>
                        </div>
                        <span class="status-pill" :class="form.enable_paypal ? 'status-pill--active' : 'status-pill--inactive'">
                            {{ form.enable_paypal ? "Enabled" : "Disabled" }}
                        </span>
                    </div>

                    <div class="switch-grid">
                        <label class="toggle-card">
                            <span>
                                <strong>Prepaid payments</strong>
                                <small>Allow one-time PayPal checkout</small>
                            </span>
                            <input v-model="form.enable_paypal" type="checkbox" class="toggle-input" />
                        </label>

                        <label class="toggle-card">
                            <span>
                                <strong>Subscription billing</strong>
                                <small>Allow recurring PayPal subscriptions</small>
                            </span>
                            <input v-model="form.enable_paypal_subscription" type="checkbox" class="toggle-input" />
                        </label>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Client ID</label>
                            <input v-model="form.paypal_client_id" type="text" class="form-control" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Client Secret</label>
                            <input v-model="form.paypal_client_secret" type="text" class="form-control" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Webhook URI</label>
                            <input v-model="form.paypal_webhook_uri" type="text" class="form-control" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Webhook ID</label>
                            <input v-model="form.paypal_webhook_id" type="text" class="form-control" />
                        </div>
                        <div class="col-12">
                            <label class="form-label">Base URI</label>
                            <input v-model="form.paypal_base_uri" type="text" class="form-control" />
                        </div>
                    </div>
                </article>

                <article v-if="showSection('stripe', ['stripe', 'webhook', 'secret', 'subscription'])" class="settings-card">
                    <div class="settings-card__head">
                        <div>
                            <p class="settings-kicker">Online Gateway</p>
                            <h3>Stripe</h3>
                        </div>
                        <span class="status-pill" :class="form.enable_stripe ? 'status-pill--active' : 'status-pill--inactive'">
                            {{ form.enable_stripe ? "Enabled" : "Disabled" }}
                        </span>
                    </div>

                    <div class="switch-grid">
                        <label class="toggle-card">
                            <span>
                                <strong>Prepaid payments</strong>
                                <small>Allow one-time Stripe checkout</small>
                            </span>
                            <input v-model="form.enable_stripe" type="checkbox" class="toggle-input" />
                        </label>

                        <label class="toggle-card">
                            <span>
                                <strong>Subscription billing</strong>
                                <small>Allow recurring Stripe subscriptions</small>
                            </span>
                            <input v-model="form.enable_stripe_subscription" type="checkbox" class="toggle-input" />
                        </label>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Publishable Key</label>
                            <input v-model="form.stripe_key" type="text" class="form-control" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Secret Key</label>
                            <input v-model="form.stripe_secret_key" type="text" class="form-control" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Webhook URI</label>
                            <input v-model="form.stripe_webhook_uri" type="text" class="form-control" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Webhook Secret</label>
                            <input v-model="form.stripe_webhook_secret" type="text" class="form-control" />
                        </div>
                        <div class="col-12">
                            <label class="form-label">Base URI</label>
                            <input v-model="form.stripe_base_uri" type="text" class="form-control" />
                        </div>
                    </div>
                </article>

                <article v-if="showSection('bank', ['bank', 'offline', 'transfer', 'instructions'])" class="settings-card settings-card--wide">
                    <div class="settings-card__head">
                        <div>
                            <p class="settings-kicker">Offline Payments</p>
                            <h3>Bank transfer</h3>
                        </div>
                        <span class="status-pill" :class="form.enable_bank ? 'status-pill--active' : 'status-pill--inactive'">
                            {{ form.enable_bank ? "Enabled" : "Disabled" }}
                        </span>
                    </div>

                    <div class="switch-grid">
                        <label class="toggle-card">
                            <span>
                                <strong>Prepaid payments</strong>
                                <small>Allow bank transfer for one-time charges</small>
                            </span>
                            <input v-model="form.enable_bank" type="checkbox" class="toggle-input" />
                        </label>

                        <label class="toggle-card">
                            <span>
                                <strong>Subscription billing</strong>
                                <small>Allow bank transfer for recurring plans</small>
                            </span>
                            <input v-model="form.enable_bank_subscription" type="checkbox" class="toggle-input" />
                        </label>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Customer Instructions</label>
                            <textarea v-model="form.bank_instructions" class="form-control" rows="7"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bank Requisites</label>
                            <textarea v-model="form.bank_requisites" class="form-control" rows="7"></textarea>
                        </div>
                    </div>
                </article>

                <div class="settings-actions">
                    <Link :href="route('superAdmin.payment')" class="btn btn-outline-secondary">Cancel</Link>
                    <button type="submit" class="btn btn-primary">Save settings</button>
                </div>
            </form>
        </section>
    </AuthLayout>
</template>

<style scoped>
.payment-page {
    display: grid;
    gap: 24px;
}

.settings-hero {
    display: flex;
    justify-content: space-between;
    gap: 24px;
    padding: 32px;
    border-radius: 28px;
    background: linear-gradient(135deg, #f8fcff 0%, #eef7ff 52%, #ffffff 100%);
    border: 1px solid rgba(18, 148, 234, 0.1);
}

.hero-kicker,
.filter-kicker,
.settings-kicker {
    margin: 0 0 8px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #1294ea;
}

.hero-title {
    margin: 0 0 10px;
    font-size: 34px;
    font-weight: 700;
    color: #0f172a;
}

.hero-copy {
    margin: 0;
    color: #64748b;
    max-width: 760px;
}

.hero-actions,
.filter-tools {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
}

.metric-card,
.filter-card,
.settings-card {
    border-radius: 24px;
    background: #fff;
    border: 1px solid #e2e8f0;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.metric-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 22px 24px;
}

.metric-label {
    margin: 0 0 8px;
    color: #64748b;
    font-size: 0.82rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.metric-value {
    margin: 0;
    color: #0f172a;
    font-size: 1.9rem;
    font-weight: 700;
}

.metric-helper {
    margin: 8px 0 0;
    color: #64748b;
    font-size: 0.92rem;
}

.metric-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 54px;
    height: 54px;
    border-radius: 18px;
    font-size: 1.35rem;
}

.tone-blue .metric-icon {
    background: #e0f2fe;
    color: #0369a1;
}

.tone-green .metric-icon {
    background: #dcfce7;
    color: #15803d;
}

.tone-indigo .metric-icon {
    background: #e0e7ff;
    color: #4338ca;
}

.tone-amber .metric-icon {
    background: #ffedd5;
    color: #c2410c;
}

.filter-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 18px;
}

.filter-title,
.settings-card__head h3 {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 700;
    color: #0f172a;
}

.section-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.section-pill {
    border: 1px solid #dbe4f0;
    background: #fff;
    color: #475569;
    border-radius: 999px;
    padding: 0.55rem 0.9rem;
    font-weight: 600;
}

.section-pill--active {
    background: #eff6ff;
    border-color: #bfdbfe;
    color: #1d4ed8;
}

.settings-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

.settings-card {
    padding: 24px;
}

.settings-card--wide {
    grid-column: 1 / -1;
}

.settings-card__head {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    align-items: flex-start;
    margin-bottom: 18px;
}

.panel-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.45rem 0.75rem;
    border-radius: 999px;
    background: #eef2ff;
    color: #3730a3;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.38rem 0.75rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
}

.status-pill--active {
    background: #dcfce7;
    color: #166534;
}

.status-pill--inactive {
    background: #fee2e2;
    color: #b91c1c;
}

.switch-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 18px;
}

.toggle-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 18px;
    padding: 14px 16px;
    border-radius: 18px;
    background: #f8fbff;
    border: 1px solid #e2e8f0;
}

.toggle-card strong {
    display: block;
    color: #0f172a;
}

.toggle-card small {
    color: #64748b;
}

.toggle-input {
    width: 46px;
    height: 24px;
    position: relative;
    appearance: none;
    background: #cbd5e1;
    border-radius: 999px;
    outline: none;
    cursor: pointer;
    transition: background 0.2s;
}

.toggle-input::before {
    content: "";
    position: absolute;
    top: 3px;
    left: 3px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #fff;
    transition: transform 0.2s;
}

.toggle-input:checked {
    background: #22c55e;
}

.toggle-input:checked::before {
    transform: translateX(22px);
}

.settings-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    grid-column: 1 / -1;
}

@media (max-width: 1200px) {
    .metrics-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 991px) {
    .settings-hero,
    .filter-header,
    .settings-card__head {
        flex-direction: column;
        align-items: flex-start;
    }

    .hero-actions,
    .filter-tools {
        justify-content: flex-start;
    }

    .settings-grid,
    .switch-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .metrics-grid {
        grid-template-columns: 1fr;
    }

    .hero-title {
        font-size: 28px;
    }
}
</style>
