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
    title: '',
    gender: 'All Genders',
    age: 'All Ages',
});

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
     form.post(route('doctor.forms.store'),{
            onSuccess: () => {
            closeModal();
            form.reset();
            emit("submit");
            } 
     });
}

const genders = ['All Genders', 'Male Only', 'Female Only', 'Undifferentiated Only'];
const ages = ['All Ages', 'Adult Only', 'Child Only'];

// Expose methods to parent component
defineExpose({
    openModal,
    closeModal,
    update
});
</script>
<template>

    <form @submit.prevent="submit" novalidate class="needs-validation" :class="{ 'was-validated': isValidated }">
        <!-- Form Title -->
        <div class="form-group">
            <label for="title">Form Title</label>
            <input id="title" v-model="form.title" type="text" class="form-control" placeholder="Enter title"
                required />
            <InputError class="mt-2" :message="form.errors.title" />
        </div>

        <!-- Gender Association -->
        <div class="form-group">
            <label for="gender">Gender Association</label>
            <select id="gender" v-model="form.gender" class="form-control" required>
                <option disabled value="">Select Gender Association</option>
                <option v-for="gender in genders" :key="gender" :value="gender">
                    {{ gender }}
                </option>
            </select>
            <InputError class="mt-2" :message="form.errors.gender" />
        </div>

        <!-- Age Association -->
        <div class="form-group">
            <label for="age">Age Association</label>
            <select id="age" v-model="form.age" class="form-control" required>
                <option disabled value="">Select Age Association</option>
                <option v-for="age in ages" :key="age" :value="age">
                    {{ age }}
                </option>
            </select>
            <InputError class="mt-2" :message="form.errors.age" />
        </div>

        <!-- Buttons -->
        <div class="d-flex justify-content-end gap-2 mt-4">
           
            <button type="submit" class="btn btn-primary" >
                Save
            </button>
             <button type="button" class="btn btn-danger" @click="closeModal">
                Close
            </button>
        </div>
    </form>

</template>