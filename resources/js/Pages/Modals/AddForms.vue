<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    route: Array,
});

const showModal = ref(false);
const isValidated = ref(false);

const form = useForm({
    rows: [
        {
            title: '',
            gender: '',
            age: ''
        }
    ]
});

const addRow = () => {
    form.rows.push({
        title: '',
        gender: '',
        age: ''
    });
};

const removeRow = (index) => {
    if (form.rows.length > 1) {
        form.rows.splice(index, 1);
    }
};

const openModal = () => {
    showModal.value = true;
};

const closeModal = () => {
    emit("close");
};
const emit = defineEmits(["close", "submit"]);

const update = (data) => {
    if (data) {
        form.title = data.title || '';
        form.gender = data.gender || '';
        form.age = data.age || '';
    }
    openModal();
};

const submit = () => {
    isValidated.value = true;
    form.post(route('doctor.forms.store'), {
        onSuccess: () => {
            closeModal();
            form.reset();
            emit("submit");
        }
    });
}

const genders = ['All Genders', 'Male Only', 'Female Only', 'Undifferentiated Only'];
const ages = ['All Ages', 'Adult Only', 'Child Only'];

/* Expose methods to parent component */
defineExpose({
    openModal,
    closeModal,
    update
});
</script>
<template>

    <form @submit.prevent="submit" novalidate class="needs-validation" :class="{ 'was-validated': isValidated }">

        <div v-for="(row, index) in form.rows" :key="index" class="border p-3 mb-3 rounded">

            <!-- Title -->
            <div class="form-group">
                <label>Form Title</label>
                <input v-model="row.title" type="text" class="form-control" required />
            </div>

            <!-- Gender -->
            <div class="form-group">
                <label>Gender Association</label>
                <select v-model="row.gender" class="form-control" required>
                    <option disabled value="">Select Gender</option>
                    <option v-for="gender in genders" :key="gender" :value="gender">
                        {{ gender }}
                    </option>
                </select>
            </div>

            <!-- Age -->
            <div class="form-group">
                <label>Age Association</label>
                <select v-model="row.age" class="form-control" required>
                    <option disabled value="">Select Age</option>
                    <option v-for="age in ages" :key="age" :value="age">
                        {{ age }}
                    </option>
                </select>
            </div>

            <!-- Delete Button -->
            <div v-if="index" class="mt-2 text-end">
                <button type="button" class="btn btn-danger btn-sm" @click="removeRow(index)">
                    Delete
                </button>
            </div>

        </div>

        <!-- Add More Button -->
        <button type="button" class="btn btn-secondary mb-3" @click="addRow">
            + Add More
        </button>

        <!-- Submit -->
        <div class="d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-danger" @click="closeModal">Close</button>
        </div>

    </form>

</template>