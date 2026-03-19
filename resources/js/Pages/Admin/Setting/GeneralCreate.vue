<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, useForm, Link } from "@inertiajs/vue3";

const props = defineProps({
    setting: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    id: props.setting?.id || null,
    key: props.setting?.key || '',
    value: props.setting?.value || '',
    type: props.setting?.type || 'string',
    description: props.setting?.description || '',
    group: props.setting?.group || 'general',
    is_encrypted: true,
    is_active: props.setting?.is_active || 1,
});

const submitForm = () => {
    form.post(route('admin.settings.store'), {
        onFinish: () => {
            //
        },
    });
};

const type_options = [
    { id: 'string', name: 'String' },
    { id: 'text', name: 'Text' },
    { id: 'boolean', name: 'Boolean' },
    { id: 'integer', name: 'Integer' },
    { id: 'decimal', name: 'Decimal' },
    { id: 'json', name: 'Json' },
    { id: 'file', name: 'File' },
    { id: 'encrypted', name: 'Encrypted' },
];
</script>

<template>
    <AuthLayout title="Create General Setting" description="Create General Setting" heading="Create General Setting">
        <div class="container-fluid">
            <form @submit.prevent="submitForm">
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-xl fw-semibold mb-3">Create General Setting</h6>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Key</label>
                        <input v-model="form.key" type="text" class="form-control" />
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Value</label>
                        <input v-model="form.value" type="text" class="form-control" />
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Type</label>
                        <select v-model="form.type" class="form-select">
                            <option v-for="option in type_options" :key="option.id" :value="option.id">{{ option.name }}</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Group</label>
                        <input v-model="form.group" type="text" class="form-control" />
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
                        <label class="form-label">Description</label>
                        <textarea v-model="form.description" rows="4" class="form-control"
                            placeholder="Enter description here..."></textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <Link type="button" class="btn btn-danger" :href="route('admin.settings.index')">
                        Cancel
                    </Link>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </AuthLayout>
</template>
