<script setup>
import { computed, ref } from "vue";
import { Link, useForm } from "@inertiajs/vue3";
import Swal from "sweetalert2";
import AuthLayout from "@/Layouts/AuthLayout.vue";

const props = defineProps({
    invoice: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    invoice_currency: props.invoice?.invoice_currency || "",
    invoice_language: props.invoice?.invoice_language || "",
    invoice_vendor: props.invoice?.invoice_vendor || "",
    invoice_vendor_website: props.invoice?.invoice_vendor_website || "",
    invoice_address: props.invoice?.invoice_address || "",
    invoice_city: props.invoice?.invoice_city || "",
    invoice_state: props.invoice?.invoice_state || "",
    invoice_postal_code: props.invoice?.invoice_postal_code || "",
    invoice_country: props.invoice?.invoice_country || "",
    invoice_phone: props.invoice?.invoice_phone || "",
    invoice_vat_number: props.invoice?.invoice_vat_number || "",
});

const activeSection = ref("all");
const sectionKeyword = ref("");

const sectionTabs = [
    { id: "all", label: "All sections" },
    { id: "defaults", label: "Defaults" },
    { id: "company", label: "Company" },
    { id: "address", label: "Address" },
    { id: "tax", label: "Tax" },
];

const languages = [
    { value: "br", label: "BR" },
    { value: "de", label: "DE" },
    { value: "en", label: "EN" },
    { value: "es", label: "ES" },
    { value: "fr", label: "FR" },
    { value: "it", label: "IT" },
    { value: "nl", label: "NL" },
];

const countries = [
    "United States",
    "United Kingdom",
    "India",
    "Canada",
    "Australia",
    "Germany",
    "France",
    "Netherlands",
    "Brazil",
];

const currencies = {
    USD: "US Dollar",
    EUR: "Euro",
    GBP: "British Pound Sterling",
    INR: "Indian Rupee",
    AUD: "Australian Dollar",
    CAD: "Canadian Dollar",
};

const matchesSearch = (terms = []) => {
    const search = sectionKeyword.value.trim().toLowerCase();
    if (!search) return true;

    return terms.some((term) => term.toLowerCase().includes(search));
};

const showSection = (id, terms = []) => (activeSection.value === "all" || activeSection.value === id) && matchesSearch(terms);

const completionCount = computed(() =>
    [
        form.invoice_currency,
        form.invoice_language,
        form.invoice_vendor,
        form.invoice_vendor_website,
        form.invoice_address,
        form.invoice_city,
        form.invoice_state,
        form.invoice_postal_code,
        form.invoice_country,
        form.invoice_phone,
        form.invoice_vat_number,
    ].filter((value) => String(value || "").trim() !== "").length
);

const metricCards = computed(() => [
    {
        label: "Default Currency",
        value: form.invoice_currency || "Not set",
        helper: form.invoice_currency ? currencies[form.invoice_currency] || "Configured invoice currency" : "No default currency selected",
        icon: "fa-solid fa-money-bill",
        tone: "tone-blue",
    },
    {
        label: "Language",
        value: (form.invoice_language || "Not set").toUpperCase(),
        helper: "Default invoice rendering language",
        icon: "fa-solid fa-language",
        tone: "tone-indigo",
    },
    {
        label: "Vendor",
        value: form.invoice_vendor || "Not set",
        helper: "Primary business name shown on invoices",
        icon: "fa-solid fa-building",
        tone: "tone-green",
    },
    {
        label: "Completion",
        value: `${completionCount.value}/11`,
        helper: "Configured invoice setting fields",
        icon: "fa-solid fa-list-check",
        tone: "tone-amber",
    },
]);

const submitForm = () => {
    Swal.fire({
        title: "Save invoice settings?",
        text: "This will update the invoice defaults for the platform.",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Yes, save",
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire("Saved", "Invoice settings updated successfully.", "success");
        }
    });
};
</script>

<template>
    <AuthLayout title="Invoice Settings" description="Configure invoice defaults and company billing information">
        <section class="invoice-page">

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
                            <h3 class="filter-title">Jump to an invoice section</h3>
                        </div>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-xl-5">
                            <label class="form-label text-muted small text-uppercase mb-2">Search settings</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 border rounded-circle-left">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input v-model="sectionKeyword" type="search" class="form-control border-start-0"
                                    placeholder="Search by currency, language, vendor, VAT, phone, or address" />
                            </div>
                        </div>

                        <div class="col-12 col-xl-7">
                            <label class="form-label text-muted small text-uppercase mb-2">Section</label>
                            <div class="section-pills">
                                <button v-for="tab in sectionTabs" :key="tab.id" type="button" class="section-pill"
                                    :class="{ 'section-pill--active': activeSection === tab.id }"
                                    @click="activeSection = tab.id">
                                    {{ tab.label }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form class="settings-grid" @submit.prevent="submitForm">
                <article v-if="showSection('defaults', ['defaults', 'currency', 'language'])" class="settings-card">
                    <div class="settings-card__head">
                        <div>
                            <p class="settings-kicker">Defaults</p>
                            <h3>Invoice defaults</h3>
                        </div>
                        <span class="panel-chip">Template</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Invoice Currency <span class="text-required">*</span></label>
                            <select v-model="form.invoice_currency" class="form-select">
                                <option v-for="(name, code) in currencies" :key="code" :value="code">{{ name }}</option>
                            </select>
                            <p v-if="form.errors.invoice_currency" class="text-danger small mt-1">{{
                                form.errors.invoice_currency }}</p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Invoice Language <span class="text-required">*</span></label>
                            <select v-model="form.invoice_language" class="form-select">
                                <option v-for="language in languages" :key="language.value" :value="language.value">
                                    {{ language.label }}
                                </option>
                            </select>
                            <p v-if="form.errors.invoice_language" class="text-danger small mt-1">{{
                                form.errors.invoice_language }}</p>
                        </div>
                    </div>
                </article>

                <article v-if="showSection('company', ['company', 'vendor', 'website'])" class="settings-card">
                    <div class="settings-card__head">
                        <div>
                            <p class="settings-kicker">Company</p>
                            <h3>Business identity</h3>
                        </div>
                        <span class="panel-chip">Branding</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Company Name <span class="text-required">*</span></label>
                            <input v-model="form.invoice_vendor" type="text" class="form-control" />
                            <p v-if="form.errors.invoice_vendor" class="text-danger small mt-1">{{
                                form.errors.invoice_vendor }}</p>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Company Website</label>
                            <input v-model="form.invoice_vendor_website" type="text" class="form-control" />
                            <p v-if="form.errors.invoice_vendor_website" class="text-danger small mt-1">{{
                                form.errors.invoice_vendor_website }}</p>
                        </div>
                    </div>
                </article>

                <article v-if="showSection('address', ['address', 'city', 'state', 'postal', 'country', 'phone'])"
                    class="settings-card settings-card--wide">
                    <div class="settings-card__head">
                        <div>
                            <p class="settings-kicker">Address</p>
                            <h3>Billing address and contact</h3>
                        </div>
                        <span class="panel-chip">Location</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Business Address</label>
                            <input v-model="form.invoice_address" type="text" class="form-control" />
                            <p v-if="form.errors.invoice_address" class="text-danger small mt-1">{{
                                form.errors.invoice_address }}</p>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input v-model="form.invoice_city" type="text" class="form-control" />
                            <p v-if="form.errors.invoice_city" class="text-danger small mt-1">{{
                                form.errors.invoice_city }}</p>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">State</label>
                            <input v-model="form.invoice_state" type="text" class="form-control" />
                            <p v-if="form.errors.invoice_state" class="text-danger small mt-1">{{
                                form.errors.invoice_state }}</p>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Postal Code</label>
                            <input v-model="form.invoice_postal_code" type="text" class="form-control" />
                            <p v-if="form.errors.invoice_postal_code" class="text-danger small mt-1">{{
                                form.errors.invoice_postal_code }}</p>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Country</label>
                            <select v-model="form.invoice_country" class="form-select">
                                <option v-for="country in countries" :key="country" :value="country">{{ country }}
                                </option>
                            </select>
                            <p v-if="form.errors.invoice_country" class="text-danger small mt-1">{{
                                form.errors.invoice_country }}</p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input v-model="form.invoice_phone" type="text" class="form-control" />
                            <p v-if="form.errors.invoice_phone" class="text-danger small mt-1">{{
                                form.errors.invoice_phone }}</p>
                        </div>
                    </div>
                </article>

                <article v-if="showSection('tax', ['tax', 'vat'])" class="settings-card">
                    <div class="settings-card__head">
                        <div>
                            <p class="settings-kicker">Tax</p>
                            <h3>Tax references</h3>
                        </div>
                        <span class="panel-chip">Compliance</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">VAT Number</label>
                            <input v-model="form.invoice_vat_number" type="text" class="form-control" />
                            <p v-if="form.errors.invoice_vat_number" class="text-danger small mt-1">{{
                                form.errors.invoice_vat_number }}</p>
                        </div>
                    </div>
                </article>

                <div class="settings-actions">
                    <Link :href="route('superAdmin.invoice')" class="btn btn-outline-secondary">Cancel</Link>
                    <button type="submit" class="btn btn-primary">Save settings</button>
                </div>
            </form>
        </section>
    </AuthLayout>
</template>

<style scoped>
.invoice-page {
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

.text-required {
    color: #dc2626;
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

    .settings-grid {
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
