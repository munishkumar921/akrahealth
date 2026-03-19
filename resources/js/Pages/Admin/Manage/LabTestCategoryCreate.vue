<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, useForm, Link } from "@inertiajs/vue3";

const props = defineProps({
    category: Object,
 });

const form = useForm({
    id: props.category?.id || null,
    name: props.category?.name || '',
    description: props.category?.description || '',
    is_active: props.category?.is_active || 0,
});

const submitForm = () => {
    form.post(route('admin.lab-test-categories.store'), {
        onFinish: () => {
            //
        },
    });
};
</script>

<template>
    <AuthLayout title="Create Lab Test Category" description="Create Lab Test Category"
        heading="Create Lab Test Category">
        <div class="container-fluid">
            <form @submit.prevent="submitForm">
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-xl fw-semibold mb-3">Contact Person Detail</h6>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Name</label>
                        <input v-model="form.name" type="text" class="form-control" />
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select v-model="form.is_active" class="form-select">
                            <option value="">-- Select --</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">More about</label>
                        <textarea v-model="form.description" rows="4" class="form-control"
                            placeholder="Enter more details here..."></textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-danger"
                        @click="router.visit(route('admin.lab-test-categories.index'))">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </AuthLayout>
</template>
