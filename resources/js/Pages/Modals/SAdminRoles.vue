<script setup>
import BaseInput from "../../Components/Common/Input/BaseInput.vue";
import { useForm } from "@inertiajs/vue3";
import BaseSelect from "../../Components/Common/Input/BaseSelect.vue";

const form = useForm({
    id: null,
    name: "",
    guard_name: "web",
    is_status: true,
});

const submit = () => {
    form.post(route('superAdmin.api.roles.store'), {
        onSuccess: () => {
            closeModal();
        },
        onError: (err) => {
            console.log(err);
        },
    });
};

const closeModal = () => {
    form.reset();
    emit("close");
};

const emit = defineEmits(["close"]);

const update = (role) => {
    form.name = role.name;
    form.guard_name = role.guard_name;
    form.is_status = role.is_active;
    form.id = role.id;
};

// Expose update method to parent
defineExpose({
    update,
    resetForm: () => form.reset(),
});
</script>

<template>
    <!-- Role Modal -->
    <form @submit.prevent="submit">
        <div class="mb-3">
            <label class="form-label">Role Name</label>
            <BaseInput v-model="form.name" placeholder="Enter role name" required :error="form.errors.name" />
        </div>

        <div class="mb-3">
            <BaseSelect v-model="form.guard_name" label="Guard Name" placeholder="Select Guard Name" :error="form.errors.guard_name">
                <option value="web">Web</option>
                <option value="api">API</option>
            </BaseSelect>
        </div>
        
        <div class="mb-3">
            <BaseSelect v-model="form.is_status" label="Status" placeholder="Select Status" :error="form.errors.is_status" id="status">
                <option :value="true">Active</option>
                <option :value="false">Inactive</option>
            </BaseSelect>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
           
            <button type="submit" class="btn btn-primary">
                Save
            </button>
            <button type="button" class="btn btn-danger" @click="closeModal">
                Close
            </button>
        </div>
    </form>
</template>

