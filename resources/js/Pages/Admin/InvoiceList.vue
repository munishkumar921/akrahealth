<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import { computed, ref } from "vue";
import axios from "axios";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { route } from "ziggy-js";

const props = defineProps({
    invoices: {
        type: [Array, Object],
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({
            keyword: "",
            status: "",
        }),
    },
});

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    status: props.filters?.status || "",
});

const statusOptions = [
    { value: "", label: "All status" },
    { value: "draft", label: "Draft" },
    { value: "sent", label: "Sent" },
    { value: "viewed", label: "Viewed" },
    { value: "partial", label: "Partial" },
    { value: "paid", label: "Paid" },
    { value: "overdue", label: "Overdue" },
    { value: "cancelled", label: "Cancelled" },
    { value: "failed", label: "Failed" },
];

const columns = [
    { label: "Invoice", key: "invoice_number", type: "slot", slot: "invoice", align: "left" },
    { label: "Payment ID", key: "razorpay_payment_id", type: "slot", slot: "payment", align: "left" },
    { label: "Patient", key: "patient_name", type: "slot", slot: "patient", align: "left" },
    { label: "Amount", key: "total_amount", type: "slot", slot: "amount", align: "left" },
    { label: "Currency", key: "currency", type: "slot", slot: "currency", align: "center" },
    { label: "Status", key: "status", type: "slot", slot: "status", align: "center" },
    { label: "Created", key: "created_at", type: "slot", slot: "created", align: "left" },
];

const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);
const rows = computed(() => props.invoices?.data ?? []);
const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);
const resultSummary = computed(() => {
    const total = props.invoices?.total ?? rows.value.length;
    const from = props.invoices?.from ?? (rows.value.length ? 1 : 0);
    const to = props.invoices?.to ?? rows.value.length;

    if (!total) {
        return "No invoices found";
    }

    return `Showing ${from}-${to} of ${total} invoices`;
});

const buildQuery = (overrides = {}) => {
    const params = new URLSearchParams(window.location.search);
    const query = {
        per_page: params.get("per_page") || undefined,
        sort: params.get("sort") || undefined,
        direction: params.get("direction") || undefined,
        ...filterForm.value,
        ...overrides,
    };

    return Object.fromEntries(
        Object.entries(query).filter(([, value]) => value !== "" && value !== null && value !== undefined)
    );
};

const applyFilters = () => {
    router.get(route("admin.invoice.list"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        status: "",
    };

    router.get(route("admin.invoice.list"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("admin.invoice.list"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const downloadInvoice = async (row) => {
    if (!row || !row.id) {
        Swal.fire({
            title: "Error!",
            text: "Invalid invoice data. Please refresh the page and try again.",
            icon: "error",
        });
        return;
    }

    Swal.fire({
        title: "Generating Invoice...",
        text: "Please wait while we prepare your invoice.",
        icon: "info",
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        },
    });

    try {
        const url = route("admin.invoice.download", { id: row.id });
        const response = await axios.get(url, {
            timeout: 30000,
            responseType: "blob",
        });

        const blob = new Blob([response.data], { type: "application/pdf" });
        const downloadUrl = window.URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = downloadUrl;
        link.download = `Invoice-${row.invoice_number || row.id}.pdf`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(downloadUrl);

        Swal.close();
        Swal.fire({
            title: "Success!",
            text: "Invoice downloaded successfully.",
            icon: "success",
            timer: 2000,
            showConfirmButton: false,
        });
    } catch (error) {
        let errorMessage = "Failed to download invoice. Please try again.";

        if (error.code === "ECONNABORTED") {
            errorMessage = "Request timed out. Please try again.";
        } else if (error.response?.status === 404) {
            errorMessage = "Invoice not found.";
        } else if (error.response?.status === 500) {
            errorMessage = "Server error. Please try again later.";
        } else if (error.message) {
            errorMessage = error.message;
        }

        Swal.fire({
            title: "Download Failed",
            text: errorMessage,
            icon: "error",
            showConfirmButton: true,
            confirmButtonText: "Retry",
        }).then((result) => {
            if (result.isConfirmed) {
                downloadInvoice(row);
            }
        });
    }
};

const getStatusClass = (status) => {
    const value = String(status || "").toLowerCase();

    if (value === "paid") return "status-pill--active";
    if (["draft", "sent", "viewed", "partial"].includes(value)) return "status-pill--pending";

    return "status-pill--inactive";
};

const formatAmount = (row) => {
    const amount = Number(row.total_amount || 0).toFixed(2);
    const symbol = row.currency === "USD" ? "$" : row.currency === "EUR" ? "EUR " : "₹";

    return `${symbol}${amount}`;
};
</script>

<template>
    <AuthLayout title="Invoices" description="Invoices" heading="Invoices">
        <div class="invoice-page">
            <div class="users-toolbar card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div
                        class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                        <div>
                            <h3 class="mb-1">Invoices</h3>
                            <p class="text-muted mb-0">{{ resultSummary }}</p>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-lg-end">
                            <span v-if="hasActiveFilters" class="filter-count-badge">
                                {{ activeFilterCount }} filter{{ activeFilterCount > 1 ? "s" : "" }} active
                            </span>
                            <button
                                v-if="hasActiveFilters"
                                type="button"
                                class="btn btn-outline-secondary btn-sm"
                                @click="clearFilters"
                            >
                                <i class="bi bi-x-circle me-1"></i>Clear filters
                            </button>
                        </div>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-xl-4">
                            <label class="form-label text-muted small text-uppercase mb-2">Search</label>
                            <div class="input-group invoice-search-control">
                                <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input
                                    v-model="filterForm.keyword"
                                    type="search"
                                    class="form-control border-start-0"
                                    placeholder="Search by invoice, payment ID, appointment, patient, amount, or status"
                                    @keydown.enter.prevent="applyFilters"
                                />
                                <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-3">
                            <label class="form-label text-muted small text-uppercase mb-2">Status</label>
                            <select v-model="filterForm.status" class="form-select" @change="applyFilters">
                                <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0 p-md-3">
                    <div class="d-flex justify-content-end align-items-center px-3 px-md-0 pt-3 pt-md-0 pb-2">
                        <div class="d-flex align-items-center gap-2 rows-select-wrap">
                            <select
                                id="invoices-per-page"
                                v-model="perPage"
                                class="form-select form-select-sm top-page-select"
                                @change="updatePerPage"
                            >
                                <option v-for="option in perPageOptions" :key="option" :value="option">
                                    {{ option }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <Table :columns="columns" :data="invoices" :search-show="false" :PageOptions="false">
                        <template #invoice="{ row }">
                            <div class="text-start">
                                <div class="fw-semibold text-dark">{{ row.invoice_number || row.id }}</div>
                                <div class="text-muted small">Appointment: {{ row.appointment_id || "N/A" }}</div>
                            </div>
                        </template>

                        <template #payment="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.razorpay_payment_id || "-" }}</div>
                            </div>
                        </template>

                        <template #patient="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.patient_name || "N/A" }}</div>
                            </div>
                        </template>

                        <template #amount="{ row }">
                            <div class="text-start">
                                <div class="fw-semibold text-success">{{ formatAmount(row) }}</div>
                            </div>
                        </template>

                        <template #currency="{ row }">
                            <span class="soft-badge badge-soft-secondary">{{ row.currency || "INR" }}</span>
                        </template>

                        <template #status="{ row }">
                            <span class="status-pill" :class="getStatusClass(row.status)">
                                {{ row.status_label || row.status || "N/A" }}
                            </span>
                        </template>

                        <template #created="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.created_label || row.created_at || "-" }}</div>
                            </div>
                        </template>

                        <template #actions="{ row }">
                            <button
                                class="btn btn-light border"
                                type="button"
                                title="Download invoice"
                                @click="downloadInvoice(row)"
                            >
                                <i class="bi bi-download"></i>
                            </button>
                            <a
                                class="btn btn-light border"
                                :href="route('admin.invoice.print', { id: row.appointment_id })"
                                title="Print"
                                target="_blank"
                            >
                                <i class="fa fa-print"></i>
                            </a>
                        </template>
                    </Table>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<style scoped>
.users-toolbar {
    border-radius: 20px;
}

.filter-count-badge {
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
    min-width: 84px;
    padding: 0.35rem 0.7rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
}

.status-pill--active {
    background: #dcfce7;
    color: #166534;
}

.status-pill--inactive {
    background: #f1f5f9;
    color: #475569;
}

.status-pill--pending {
    background: #fef3c7;
    color: #92400e;
}

.soft-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.35rem 0.7rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
}

.badge-soft-secondary {
    background: #f1f5f9;
    color: #475569;
}

.top-page-select {
    min-width: 74px;
    width: 74px;
}

.rows-select-wrap {
    flex: 0 0 auto;
}

.invoice-search-control {
    max-width: 620px;
}
</style>
