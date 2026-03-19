<script setup>
import { useForm } from "@inertiajs/vue3";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";

const emit = defineEmits(["close", "submit"]);

 
const form = useForm({
    id:'',
    name: '',
    description: '',
    is_active: 1,
});

const submitForm = () => {
            form.post(route('admin.lab-test-categories.store'), {
                onSuccess: () => {
                    emit("submit");
                    closeModal();
                },
                onError: () => {
                }
            });
        
};


const closeModal = () => {
    emit("close");
    form.reset();
 };

const update = (data) => {
    form.id = data.id;
    form.name = data.name;
    form.description = data.description;
    form.is_active = data.is_active;
};

defineExpose({ update });
</script>

<template>
    <form @submit.prevent="submitForm">
        <div class="row">
            <div class="col-12 mb-3">
                <BaseInput v-model="form.name" label="Name" placeholder="Category Name" required :error="form.errors.name" />
            </div>
            <div class="col-12 mb-3">
                <BaseInput v-model="form.description" label="Description" placeholder="Description" type="textarea" :error="form.errors.description" />
            </div>
            <div class="col-12 mb-3">
                <BaseSelect v-model="form.is_active" label="Status" required :error="form.errors.is_active">
                    <option :value="1">Active</option>
                    <option :value="0">Inactive</option>
                </BaseSelect>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-3">
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-danger" @click="closeModal">Close</button>

        </div>
    </form>
</template>
