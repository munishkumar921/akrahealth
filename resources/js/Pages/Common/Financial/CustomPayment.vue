<script setup>
import { computed, ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import axios from "axios";
import AuthLayout2 from "@/Layouts/AuthLayout2.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import Tab from "./Tab.vue";

const props = defineProps({
    data: {
        type: Object,
        default: () => ({
            variables: [],
            years: [],
        }),
    },
});

const currentTab = ref("custom_report_by_payment_type");
const isValidated = ref(false);

const form = useForm({
    variables: [],
    year: [],
    type: "payment_type",
    option: "",
});

const selectedVariableCount = computed(() => form.variables?.length || 0);
const selectedYearCount = computed(() => form.year?.length || 0);

const submit = () => {
    isValidated.value = true;
    form.post(route("doctor.finance.financial_queue"));
};

const print = () => {
    form.option = "print";

    axios
        .post(route("doctor.finance.financial_queue"), form)
        .then((response) => {
            form.option = "";

            return axios.post(
                route("doctor.finance.download"),
                { data: response.data },
                {
                    responseType: "blob",
                }
            );
        })
        .then((downloadResponse) => {
            const url = window.URL.createObjectURL(new Blob([downloadResponse.data]));
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute("download", "financial-report.pdf");
            document.body.appendChild(link);
            link.click();
            link.remove();
            form.option = "";
        })
        .catch((error) => {
            form.option = "";
            console.error("Error generating or downloading report:", error);
        });
};
</script>

<template>
    <AuthLayout2 title="Custom Payment Report" description="Generate custom payment reports"
        heading="Custom Payment Report">
        <div class="row g-4">
            <Tab :currentTab="currentTab" />

            <div class="col-lg-9">


                <div class="card border-0 shadow-sm report-form-card">
                    <div class="card-body p-4 p-xl-5">
                        <div class="section-heading mb-4">
                            <span class="section-eyebrow">Report Filters</span>
                            <h3 class="section-title mb-1">Choose the report inputs</h3>
                            <p class="section-text mb-0">
                                Select the payment type variables and year range, then run the report or export it to
                                PDF.
                            </p>
                        </div>

                        <form @submit.prevent="submit" novalidate class="needs-validation"
                            :class="{ 'was-validated': isValidated }">
                            <div class="row g-4">
                                <div class="col-12 col-xl-6">
                                    <div class="filter-panel h-100">
                                        <div class="filter-panel-header">
                                            <span class="filter-panel-icon">
                                                <i class="bi bi-wallet2"></i>
                                            </span>
                                            <div>
                                                <h4 class="filter-panel-title mb-1">Payment Types</h4>
                                                <p class="filter-panel-text mb-0">
                                                    Pick one or more payment categories to include in the report.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <BaseSelect v-model="form.variables" label="Variables (Payment Types)"
                                                placeholder="Select one or more variables" multiple>
                                                <option v-for="variable in data.variables" :key="variable"
                                                    :value="variable">
                                                    {{ variable }}
                                                </option>
                                            </BaseSelect>
                                            <div v-if="form.errors.variables" class="text-danger small mt-2">
                                                {{ form.errors.variables }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-xl-6">
                                    <div class="filter-panel h-100">
                                        <div class="filter-panel-header">
                                            <span class="filter-panel-icon filter-panel-icon-secondary">
                                                <i class="bi bi-calendar3"></i>
                                            </span>
                                            <div>
                                                <h4 class="filter-panel-title mb-1">Year Range</h4>
                                                <p class="filter-panel-text mb-0">
                                                    Select the financial years you want to include in this custom
                                                    output.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <BaseSelect v-model="form.year" label="Year" placeholder="Select Year"
                                                multiple required>
                                                <option v-for="yearOption in data.years" :key="yearOption"
                                                    :value="yearOption">
                                                    {{ yearOption }}
                                                </option>
                                            </BaseSelect>
                                            <div v-if="form.errors.year" class="text-danger small mt-2">
                                                {{ form.errors.year }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="report-actions mt-4">
                                <button type="submit" class="btn btn-primary report-action-btn"
                                    :disabled="form.processing">
                                    <i class="bi bi-bar-chart-line me-2"></i>
                                    {{ form.processing ? "Running..." : "Run Report" }}
                                </button>
                                <button type="button" class="btn btn-outline-primary report-action-btn" @click="print"
                                    :disabled="form.processing">
                                    <i class="bi bi-printer me-2"></i>Print Report
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout2>
</template>

<style scoped>
.report-hero,
.report-form-card {
    border-radius: 24px;
}

.report-hero {
    background:
        radial-gradient(circle at top right, rgba(14, 165, 233, 0.16), transparent 28%),
        linear-gradient(135deg, #f8fbff 0%, #eef6ff 55%, #ffffff 100%);
    overflow: hidden;
}

.hero-copy {
    max-width: 620px;
}

.eyebrow,
.section-eyebrow {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.7rem;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.eyebrow {
    background: rgba(37, 99, 235, 0.1);
    color: #1d4ed8;
    margin-bottom: 1rem;
}

.hero-title {
    font-size: clamp(1.8rem, 3vw, 2.6rem);
    font-weight: 700;
    color: #0f172a;
}

.hero-text,
.section-text,
.filter-panel-text {
    color: #64748b;
    line-height: 1.7;
}

.hero-stats {
    display: grid;
    grid-template-columns: repeat(2, minmax(160px, 1fr));
    gap: 1rem;
    min-width: min(100%, 360px);
}

.hero-stat-card {
    border: 1px solid rgba(148, 163, 184, 0.18);
    border-radius: 22px;
    padding: 1.1rem 1.2rem;
    background: rgba(255, 255, 255, 0.75);
    backdrop-filter: blur(8px);
}

.hero-stat-label {
    display: block;
    font-size: 0.74rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #64748b;
    margin-bottom: 0.5rem;
}

.hero-stat-value {
    display: block;
    font-size: 1.65rem;
    color: #0f172a;
    line-height: 1;
}

.hero-stat-subtext {
    display: block;
    font-size: 0.85rem;
    color: #64748b;
    margin-top: 0.35rem;
}

.section-eyebrow {
    background: #eff6ff;
    color: #1d4ed8;
    margin-bottom: 0.85rem;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0f172a;
}

.filter-panel {
    height: 100%;
    padding: 1.35rem;
    border-radius: 22px;
    border: 1px solid #e2e8f0;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
}

.filter-panel-header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.filter-panel-icon {
    width: 52px;
    height: 52px;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #dbeafe;
    color: #2563eb;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.filter-panel-icon-secondary {
    background: #e0f2fe;
    color: #0284c7;
}

.filter-panel-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #0f172a;
}

.report-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 0.9rem;
    padding-top: 1.4rem;
    margin-top: 1.75rem;
    border-top: 1px solid #e2e8f0;
}

.report-action-btn {
    min-width: 170px;
    border-radius: 14px;
    padding: 0.8rem 1.2rem;
    font-weight: 600;
}

@media (max-width: 991.98px) {
    .hero-stats {
        grid-template-columns: 1fr;
        min-width: 0;
    }

    .report-actions {
        justify-content: stretch;
    }

    .report-action-btn {
        width: 100%;
    }
}
</style>
