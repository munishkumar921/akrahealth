<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

const parseOrderText = (data) => {
    if (!data) return [];

    if (Array.isArray(data)) {
        return data.map((item) => {
            if (typeof item === "string") return item;
            if (item?.name) return item.name;
            if (item?.text) return item.text;
            return JSON.stringify(item);
        });
    }

    if (typeof data === "string") {
        if (data.trim() === "") return [];

        try {
            const parsed = JSON.parse(data);
            if (Array.isArray(parsed)) {
                return parsed.map((item) => {
                    if (typeof item === "string") return item;
                    if (item?.name) return item.name;
                    if (item?.text) return item.text;
                    return JSON.stringify(item);
                });
            }

            if (typeof parsed === "object" && parsed !== null) {
                return [parsed.name || parsed.text || JSON.stringify(parsed)];
            }

            return [String(parsed)];
        } catch (e) {
            return [data];
        }
    }

    if (typeof data === "object" && data !== null) {
        return [data.name || data.text || JSON.stringify(data)];
    }

    return [String(data)];
};

const orderType = computed(() => {
    const order = props.order;
    if (order.labs) return "Laboratory";
    if (order.radiology) return "Imaging";
    if (order.cp) return "Cardiopulmonary";
    if (order.referrals) return "Referral";
    return "Order";
});

const orderTypeMeta = computed(() => {
    switch (orderType.value) {
        case "Laboratory":
            return {
                icon: "fa-flask",
                accentClass: "theme-success",
                badgeClass: "badge-soft-success",
                chipClass: "chip-success",
                title: "Laboratory Order",
            };
        case "Imaging":
            return {
                icon: "fa-x-ray",
                accentClass: "theme-warning",
                badgeClass: "badge-soft-warning",
                chipClass: "chip-warning",
                title: "Imaging Order",
            };
        case "Cardiopulmonary":
            return {
                icon: "fa-heartbeat",
                accentClass: "theme-primary",
                badgeClass: "badge-soft-primary",
                chipClass: "chip-primary",
                title: "Cardiopulmonary Order",
            };
        case "Referral":
            return {
                icon: "fa-user-md",
                accentClass: "theme-secondary",
                badgeClass: "badge-soft-secondary",
                chipClass: "chip-secondary",
                title: "Referral Order",
            };
        default:
            return {
                icon: "fa-file-medical",
                accentClass: "theme-info",
                badgeClass: "badge-soft-info",
                chipClass: "chip-info",
                title: "Order",
            };
    }
});

const statusLabel = computed(() =>
    props.order.is_completed === 1 || props.order.is_completed === true || props.order.is_completed === "completed"
        ? "Completed"
        : "Pending"
);

const statusClass = computed(() =>
    statusLabel.value === "Completed" ? "badge-soft-success" : "badge-soft-warning"
);

const orderItems = computed(() => {
    const order = props.order;
    if (order.labs) return parseOrderText(order.labs);
    if (order.radiology) return parseOrderText(order.radiology);
    if (order.cp) return parseOrderText(order.cp);
    if (order.referrals) return parseOrderText(order.referrals);
    return [];
});

const icdCodes = computed(() => {
    const order = props.order;
    if (order.labs && order.labs_icd) return parseOrderText(order.labs_icd);
    if (order.radiology && order.radiology_icd) return parseOrderText(order.radiology_icd);
    if (order.cp && order.cp_icd) return parseOrderText(order.cp_icd);
    if (order.referrals && order.referrals_icd) return parseOrderText(order.referrals_icd);
    return [];
});

const providerName = computed(() => props.order.doctor?.name || props.order.doctor?.user?.name || "Unknown Provider");

const summaryCards = computed(() => [
    {
        label: "Status",
        value: statusLabel.value,
    },
    {
        label: "Order Date",
        value: props.order.orders_date || props.order.created_at || "-",
    },
    {
        label: "Provider",
        value: providerName.value,
    },
    {
        label: "Encounter",
        value: props.order.encounter_id ? `#${String(props.order.encounter_id).slice(0, 8)}` : "Not linked",
    },
]);
</script>

<template>
    <AuthLayout :title="`${orderType} Order Details`" :description="`View details of your ${orderType.toLowerCase()} order`" :heading="`${orderType} Order Details`">
        <div class="order-details-page">
            <section class="order-hero card border-0 shadow-sm mb-4" :class="orderTypeMeta.accentClass">
                <div class="card-body p-4 p-xl-5">
                    <div class="d-flex flex-column flex-xl-row justify-content-between gap-4 align-items-xl-start">
                        <div class="d-flex gap-3 align-items-start">
                            <div class="hero-icon">
                                <i class="fa-solid" :class="orderTypeMeta.icon"></i>
                            </div>
                            <div>
                                <p class="eyebrow mb-2">Order Summary</p>
                                <h2 class="hero-title mb-2">{{ orderTypeMeta.title }}</h2>
                                <p class="hero-copy mb-3">
                                    Review the requested items, associated provider, and any supporting notes tied to this order.
                                </p>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="hero-chip" :class="orderTypeMeta.chipClass">{{ orderType }}</span>
                                    <span class="hero-chip" :class="statusClass">{{ statusLabel }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 justify-content-xl-end">
                            <Link :href="route('patient.orders')" class="btn btn-light hero-btn">
                                <i class="fa fa-arrow-left me-2"></i>Back to Orders
                            </Link>
                            <Link
                                v-if="order.encounter_id"
                                :href="route('patient.encounters.show', order.encounter_id)"
                                class="btn btn-outline-light hero-btn"
                            >
                                <i class="fa fa-file-medical me-2"></i>View Encounter
                            </Link>
                        </div>
                    </div>

                    <div class="row g-3 mt-4">
                        <div v-for="card in summaryCards" :key="card.label" class="col-12 col-sm-6 col-xl-3">
                            <div class="summary-card">
                                <span class="summary-label">{{ card.label }}</span>
                                <strong class="summary-value">{{ card.value }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="row g-4">
                <div class="col-12 col-xl-8">
                    <section class="card border-0 shadow-sm detail-card">
                        <div class="card-body p-4">
                            <div class="section-header mb-4">
                                <p class="section-eyebrow mb-1">Order Items</p>
                                <h4 class="mb-0">{{ orderType }} Requests</h4>
                            </div>

                            <div v-if="orderItems.length" class="items-grid">
                                <article v-for="(item, index) in orderItems" :key="index" class="item-card">
                                    <div class="item-bullet">
                                        <i class="fa-solid fa-check"></i>
                                    </div>
                                    <div class="item-content">
                                        <p class="item-index">Item {{ index + 1 }}</p>
                                        <p class="item-text mb-0">{{ item }}</p>
                                    </div>
                                </article>
                            </div>
                            <div v-else class="empty-block">
                                No order items were attached to this record.
                            </div>
                        </div>
                    </section>

                    <section v-if="order.notes" class="card border-0 shadow-sm detail-card mt-4">
                        <div class="card-body p-4">
                            <div class="section-header mb-3">
                                <p class="section-eyebrow mb-1">Notes</p>
                                <h4 class="mb-0">Provider Notes</h4>
                            </div>
                            <div class="note-surface">
                                {{ order.notes }}
                            </div>
                        </div>
                    </section>

                    <section v-if="order.labs_obtained" class="card border-0 shadow-sm detail-card mt-4">
                        <div class="card-body p-4">
                            <div class="section-header mb-3">
                                <p class="section-eyebrow mb-1">Lab Collection</p>
                                <h4 class="mb-0">Labs Obtained</h4>
                            </div>
                            <div class="note-surface note-surface--success">
                                {{ order.labs_obtained }}
                            </div>
                        </div>
                    </section>
                </div>

                <div class="col-12 col-xl-4">
                    <section class="card border-0 shadow-sm detail-card">
                        <div class="card-body p-4">
                            <div class="section-header mb-3">
                                <p class="section-eyebrow mb-1">Order Metadata</p>
                                <h4 class="mb-0">Details</h4>
                            </div>

                            <div class="meta-list">
                                <div class="meta-row">
                                    <span>Order ID</span>
                                    <strong class="text-break">{{ order.id }}</strong>
                                </div>
                                <div class="meta-row">
                                    <span>Order Date</span>
                                    <strong>{{ order.orders_date || order.created_at || "-" }}</strong>
                                </div>
                                <div class="meta-row">
                                    <span>Pending Date</span>
                                    <strong>{{ order.pending_date || "-" }}</strong>
                                </div>
                                <div class="meta-row">
                                    <span>Ordering Provider</span>
                                    <strong>{{ providerName }}</strong>
                                </div>
                                <div class="meta-row">
                                    <span>Encounter</span>
                                    <strong>{{ order.encounter_id || "Not linked" }}</strong>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section v-if="icdCodes.length" class="card border-0 shadow-sm detail-card mt-4">
                        <div class="card-body p-4">
                            <div class="section-header mb-3">
                                <p class="section-eyebrow mb-1">Clinical Coding</p>
                                <h4 class="mb-0">ICD Codes</h4>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <span v-for="(code, index) in icdCodes" :key="index" class="code-chip">
                                    {{ code }}
                                </span>
                            </div>
                        </div>
                    </section>

                    <section v-if="orderType === 'Laboratory'" class="card border-0 shadow-sm detail-card mt-4">
                        <div class="card-body p-4">
                            <div class="section-header mb-3">
                                <p class="section-eyebrow mb-1">Next Step</p>
                                <h4 class="mb-0">Related Results</h4>
                            </div>
                            <div class="empty-block">
                                Test results will appear here once they are available.
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<style scoped>
.order-hero {
    overflow: hidden;
    border-radius: 28px;
}

.theme-success {
    background:
        radial-gradient(circle at top right, rgba(59, 130, 246, 0.14), transparent 26%),
        linear-gradient(135deg, #f8fbff 0%, #edf7ff 48%, #e5f0ff 100%);
}

.theme-warning {
    background:
        radial-gradient(circle at top right, rgba(245, 158, 11, 0.12), transparent 26%),
        linear-gradient(135deg, #fffaf0 0%, #fff4df 48%, #ffedd5 100%);
}

.theme-primary {
    background:
        radial-gradient(circle at top right, rgba(59, 130, 246, 0.16), transparent 26%),
        linear-gradient(135deg, #f8fbff 0%, #eef4ff 48%, #dbeafe 100%);
}

.theme-secondary {
    background:
        radial-gradient(circle at top right, rgba(139, 92, 246, 0.14), transparent 26%),
        linear-gradient(135deg, #fcfbff 0%, #f4f0ff 48%, #ede9fe 100%);
}

.theme-info {
    background:
        radial-gradient(circle at top right, rgba(14, 165, 233, 0.14), transparent 26%),
        linear-gradient(135deg, #f6fdff 0%, #ecfeff 48%, #d9f9ff 100%);
}

.hero-icon {
    width: 74px;
    height: 74px;
    border-radius: 22px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.16);
    color: #2563eb;
    font-size: 1.75rem;
    backdrop-filter: blur(8px);
    border: 1px solid rgba(148, 163, 184, 0.2);
}

.eyebrow,
.section-eyebrow,
.summary-label {
    text-transform: uppercase;
    letter-spacing: 0.12em;
    font-size: 0.76rem;
    font-weight: 700;
}

.hero-title {
    font-size: clamp(2rem, 4vw, 2.75rem);
    line-height: 1;
    font-weight: 700;
    color: #1e293b;
}

.hero-copy {
    max-width: 650px;
    color: #475569;
}

.eyebrow {
    color: #64748b;
}

.hero-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.55rem 0.9rem;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 600;
    color: #0f172a;
    background: rgba(255, 255, 255, 0.82);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(148, 163, 184, 0.16);
}

.hero-btn {
    border-radius: 12px;
    padding-inline: 1rem;
}

.btn-outline-light.hero-btn {
    color: #2563eb;
    border-color: rgba(37, 99, 235, 0.28);
    background: rgba(255, 255, 255, 0.7);
}

.btn-outline-light.hero-btn:hover {
    color: #1d4ed8;
    border-color: rgba(37, 99, 235, 0.42);
    background: rgba(255, 255, 255, 0.92);
}

.summary-card {
    padding: 1rem 1.1rem;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.72);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(148, 163, 184, 0.16);
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    color: #1e293b;
}

.summary-value {
    font-size: 1.05rem;
}

.detail-card {
    border-radius: 24px;
}

.section-eyebrow {
    color: #64748b;
}

.items-grid {
    display: grid;
    gap: 1rem;
}

.item-card {
    display: grid;
    grid-template-columns: 42px 1fr;
    gap: 1rem;
    padding: 1rem 1.1rem;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
}

.item-bullet {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    background: #dcfce7;
    color: #166534;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.item-index {
    margin-bottom: 0.25rem;
    color: #64748b;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-weight: 700;
}

.item-text {
    color: #0f172a;
    font-weight: 600;
}

.note-surface {
    padding: 1rem 1.1rem;
    border-radius: 18px;
    background: #fff7ed;
    color: #7c2d12;
    border: 1px solid #fed7aa;
    line-height: 1.7;
}

.note-surface--success {
    background: #ecfdf5;
    color: #166534;
    border-color: #bbf7d0;
}

.meta-list {
    display: grid;
    gap: 0.95rem;
}

.meta-row {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid #e2e8f0;
    color: #475569;
}

.meta-row:last-child {
    border-bottom: 0;
    padding-bottom: 0;
}

.code-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 0.8rem;
    border-radius: 999px;
    background: #e0f2fe;
    color: #075985;
    font-weight: 600;
    font-size: 0.82rem;
}

.empty-block {
    padding: 1rem 1.1rem;
    border-radius: 18px;
    border: 1px dashed #cbd5e1;
    background: #f8fafc;
    color: #64748b;
}

.badge-soft-success,
.badge-soft-warning,
.badge-soft-primary,
.badge-soft-secondary,
.badge-soft-info {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.55rem 0.9rem;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 600;
}

.badge-soft-success {
    background: #dcfce7;
    color: #166534;
}

.badge-soft-warning {
    background: #fef3c7;
    color: #92400e;
}

.badge-soft-primary {
    background: #dbeafe;
    color: #1d4ed8;
}

.badge-soft-secondary {
    background: #ede9fe;
    color: #6d28d9;
}

.badge-soft-info {
    background: #e0f2fe;
    color: #075985;
}

.chip-success {
    background: #dcfce7;
    color: #166534;
}

.chip-warning {
    background: #fef3c7;
    color: #92400e;
}

.chip-primary {
    background: #dbeafe;
    color: #1d4ed8;
}

.chip-secondary {
    background: #ede9fe;
    color: #6d28d9;
}

.chip-info {
    background: #e0f2fe;
    color: #075985;
}

@media (max-width: 767.98px) {
    .hero-icon {
        width: 60px;
        height: 60px;
        border-radius: 18px;
        font-size: 1.4rem;
    }

    .item-card {
        grid-template-columns: 1fr;
    }

    .item-bullet {
        width: 36px;
        height: 36px;
        border-radius: 12px;
    }

    .meta-row {
        flex-direction: column;
    }
}
</style>
