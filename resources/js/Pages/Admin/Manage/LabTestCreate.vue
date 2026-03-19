<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, useForm, Link } from "@inertiajs/vue3";

const props = defineProps({
    test: Object,
    categories: Object,
});

const form = useForm({
    id: props.test?.id || null,
    lab_test_category_id: props.test?.lab_test_category_id || null,
    name: props.test?.name,
    description: props.test?.description,
    sample_type: props.test?.sample_type || '',
    fasting_required: props.test?.fasting_required || '',
    report_time: props.test?.report_time || '',
    instructions: props.test?.instructions,
    is_active: props.test?.is_active || 0,
});

const submitForm = () => {
    form.post(route('admin.lab-tests.store'), {
        onFinish: () => {
            //
        },
    });
};
</script>

<template>
    <AuthLayout title="Create Lab Test" description="Create Lab Test" heading="Create Lab Test">
        <div class="container-fluid">
            <form @submit.prevent="submitForm">
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-xl fw-semibold mb-3">Lab test</h6>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Test category</label>
                        <select v-model="form.lab_test_category_id" class="form-select">
                            <option value="">-- Select --</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Fasting required</label>
                        <select v-model="form.fasting_required" class="form-select">
                            <option value="">-- Select --</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Name</label>

                        <input v-model="form.name" type="text" class="form-control mt-2" />
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Description</label>

                        <input v-model="form.description" type="text" class="form-control mt-2" />
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Sample type</label>
                        <input v-model="form.sample_type" type="text" class="form-control"
                            placeholder="e.g., blood, urine, imaging" />
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Report time</label>
                        <input v-model="form.report_time" type="text" class="form-control"
                            placeholder="Estimated time to process the test. e.g., 8 hours or 5 days" />
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select v-model="form.is_active" class="form-select">
                            <option value="">-- Select --</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Instructions</label>

                        <textarea v-model="form.instructions" rows="4" class="form-control mt-2"></textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <Link class="btn btn-danger" :href="route('admin.lab-tests.index')">
                    Cancel
                    </Link>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </AuthLayout>
</template>
