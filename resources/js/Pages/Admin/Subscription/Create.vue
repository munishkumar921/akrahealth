@ -1,106 +0,0 @@
<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router } from "@inertiajs/vue3";
import { ref } from "vue";

/* --------- form state (UI only) --------- */
const form = ref({
    title: "",
    tagline: "",
    label: "",
    price: "",
    billing_cycle: "",
    status: 1, // 1 Active, 0 Inactive
    features: "",
});

/* --------- dropdown options --------- */
const statuses = [
    { label: "Active", value: 1 },
    { label: "Inactive", value: 0 },
];

const billingCycles = [
    "Monthly",
    "Quarterly",
    "Half-Yearly",
    "Yearly",
];
const currency = [
    { label: 'Dollar', value: 'USD' },
    { label: 'Rupee', value: 'INR' },
    { label: 'Euro', value: 'EUR' },
    { label: 'Pound', value: 'GBP' }
];


/* --------- submit (UI only) --------- */
const submit = () => {
    form.post(route('admin.subscription-plan.store'));
 };
</script>

<template>
    <AuthLayout
        title="Create Subscription Plan"
        description="Create Subscription Plan"
        heading="Create Subscription Plan"
    >
        <div class="container-fluid">
            <form @submit.prevent="submit">
                <div class="row">
                    <div class="col-12 mb-4">
                     </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Title</label>
                        <input v-model="form.title" type="text" class="form-control" />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tagline</label>
                        <input v-model="form.tagline" type="text" class="form-control" />
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Label (e.g., Most Popular)</label>
                        <input v-model="form.label" type="text" class="form-control" />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Price</label>
                        <input v-model="form.price" type="number" class="form-control" />
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Billing Cycle</label>
                        <select v-model="form.billing_cycle" class="form-select">
                            <option value="" disabled>-- Select --</option>
                            <option v-for="c in billingCycles" :key="c" :value="c">{{ c }}</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"> Currency</label>

                        <select v-model="form.currency" class="form-select">
                        <option v-for="cur in currency" :value="cur.value" :key="cur.value">
                                {{ cur.label }}
                        </option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select v-model="form.status" class="form-select">
                            <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                        </select>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Features</label>
                        <textarea
                            v-model="form.features"
                            rows="6"
                            class="form-control"
                            placeholder="Enter plan features here..."
                        ></textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button
                        type="button"
                        class="btn btn-danger">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </AuthLayout>
</template>