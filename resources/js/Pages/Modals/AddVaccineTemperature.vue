<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import BaseInput from '@/Components/Common/Input/BaseInput.vue';
import BaseDatePicker from '@/Components/Common/Input/BaseDatePicker.vue';
 
const emit = defineEmits(['close', 'submit']);

const form = useForm({
    id: '',
    date: '',
    time: '',
    temperature: '',
    action: '',
});

const isEditing = ref(false);

const submit = () => {
   
    form.post(route('admin.vaccine.temperatures.store'),{
        onSuccess: () => {
            form.reset();
            emit('submit');
        }
    });
};

const update = (data) => {
     isEditing.value = true;
    form.id = data.id;
    form.date = data.date;
    form.time = data.time;
    form.temperature = data.temperature;
    form.action = data.action;
    form.clearErrors();
};

const reset = () => {
    isEditing.value = false;
    form.reset();
    form.clearErrors();
};

defineExpose({ update, reset });
</script>

<template>
    <form @submit.prevent="submit">
        <div class="modal-body">
            
            <div class="row">
                   <div class="col-md-12 mb-3">
                <BaseInput v-model="form.temperature" label="Temperature (F)" placeholder="Enter temperature" :error="form.errors.temperature" required />
            </div>
            
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <BaseDatePicker v-model="form.date" type="date" label="Date" placeholder="Select Date" :error="form.errors.date" required />
                </div>
                 <div class="col-md-6 mb-3">
                    <BaseDatePicker v-model="form.time" type="time" label="Time" placeholder="Select time" :error="form.errors.time" required />
                </div>
                
            </div>
            <div class="mb-3">
                <BaseInput v-model="form.action" label="Action if Out of Range" placeholder="Enter action" :error="form.errors.action" required />
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">{{ isEditing ? 'Update' : 'Save' }}</button>
            <button type="button" class="btn btn-danger" @click="$emit('close')">Close</button>
 
        </div>
    </form>
</template>