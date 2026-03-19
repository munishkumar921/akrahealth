<script setup>
import { useForm, router } from '@inertiajs/vue3';
import AuthLayout from "@/Layouts/AuthLayout2.vue";

const props = defineProps({
    data: Object
});

const form = useForm({
    insurance_id: props.data.order.insurance_id || "",
    referrals: props.data.order.referrals,
    notes: props.data.order.notes,
});

const submit = () => {
    form.put(route('doctor.orders.update', props.data.order.id));
};
</script>

<template>
    <AuthLayout title="Edit Referral Order" heading="Edit Referral Order">
        <div class="row">
            <div class="col-lg-12">
                <div class="iq-card">
                    <div class="iq-card-header d-flex justify-content-between">
                        <div class="iq-header-title">
                            <h4 class="card-title">Edit Referral Order</h4>
                        </div>
                    </div>
                    <div class="iq-card-body">
                        <form @submit.prevent="submit">
                            <div class="form-group">
                                <label>Insurance</label>
                                <select v-model="form.insurance_id" class="form-control">
                                    <option value="">Select Insurance</option>
                                    <option v-for="ins in data.insurances" :key="ins.id" :value="ins.id">
                                        {{ ins.insurance_company }}
                                    </option>
                                </select>
                                <div v-if="form.errors.insurance_id" class="text-danger">{{ form.errors.insurance_id }}</div>
                            </div>
                            <div class="form-group">
                                <label>Referral Details</label>
                                <textarea v-model="form.referrals" class="form-control" rows="5" placeholder="Enter referral details"></textarea>
                                <div v-if="form.errors.referrals" class="text-danger">{{ form.errors.referrals }}</div>
                            </div>
                            <div class="form-group">
                                <label>Notes</label>
                                <textarea v-model="form.notes" class="form-control" rows="3"></textarea>
                                <div v-if="form.errors.notes" class="text-danger">{{ form.errors.notes }}</div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-danger me-2" @click="router.visit(route('doctor.orders.index'))">Cancel</button>
                                <button type="submit" class="btn btn-primary" :disabled="form.processing">Update Order</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>