<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import BaseInput from '@/Components/Common/Input/BaseInput.vue';
import BaseSelect from '@/Components/Common/Input/BaseSelect.vue';
import BaseFileInput from '@/Components/Common/Input/BaseFileInput.vue'; 
import BaseDatePicker from '@/Components/Common/Input/BaseDatePicker.vue';
 import InputError from '@/Components/InputError.vue';
 
const emit = defineEmits(["close", "saved"]);
  
const form = useForm({
    id: '',
    file: null, // Will hold File object for new uploads
    from: '',
    description: '',
    type: '',
    date: '',
    existing_url: '', // Store existing file URL for updates
});

// Track if we're editing an existing document
const isEditing = ref(false);

const types = [
    { value : 'Laboratory', label: 'Laboratory',   },
    { value : 'Imaging', label: 'Imaging',   },
    { value : 'Cardiopulmonary', label: 'Cardiopulmonary', },
    { value : 'Endoscopy', label: 'Endoscopy',  },
    { value : 'Refferrals', label: 'Refferrals',   },
    { value : 'Past Records', label: 'Past Records',  },
    { value : 'Other Forms', label: 'Other Forms',   },
    { value : 'Letters', label: 'Letters',  },
    { value : 'Education', label: 'Education',   },
    { value : 'CCDAs', label: 'CCDAs', },
    { value : 'CCRs', label: 'CCRs',   },
]

const closeModal = () => {
    emit("close");
};

const submit = () => {
    // If editing and no new file is uploaded, use existing URL
    if (isEditing.value && !form.file && form.existing_url) {
        form.url = form.existing_url;
    }
    
    // Use post for both create and update (server handles based on id)
    form.post(route('doctor.documents.store'), {
        onSuccess: () => {
            closeModal();
            form.reset();
            isEditing.value = false;
            emit("saved");
        },
        onError: (errors) => {
            console.error('Form errors:', errors);
            form.reset('file');
        },
    });
};
 
const update = (item) => {
    isEditing.value = true;
    form.id = item.id;  
    form.existing_url = item.url; // Store the existing URL
    form.file = null; // Reset file input
    form.from = item.from || '';
    form.description = item.text || '';
    form.type = item.type || '';
    form.date = item.date ? item.date.split(' ')[0] : '';
};

const resetForm = () => {
    form.reset();
    isEditing.value = false;
};

defineExpose({
  update,
  resetForm,
});
</script>
<template>
    <form @submit.prevent="submit">
        <div class="iq-card-body">
            <div class="row">
               
                <BaseFileInput label="Upload File (Pdf)" id="document-file" v-model="form.file" accept=".pdf" :error="form.errors.file"/>
              </div>
            <div class="row">
                <BaseInput v-model="form.from" type="text" label="Form" placeholder="Enter a form for this file..." :error="form.errors.from" />
            </div>            
            <div class="row">
               <BaseSelect v-model="form.type" label="Type" placeholder="Select a type..." :error="form.errors.type">
                    <option v-for="option in types" :key="option.value" :value="option.value">{{ option.label }}</option>
                 </BaseSelect>
                <InputError class="mt-2" :message="form.errors.type" />
            </div>

            <div class="row mt-3">
                <div class="col">
                    <BaseInput v-model="form.description" type="text" label="Description" placeholder="Enter a description for this file..." :error="form.errors.description" />
                </div>
            </div>
             <div class="row mt-3">
                <div class="col">
                    <label>Date</label>
                     <BaseDatePicker  type="date" v-model="form.date" placeholder="Select date..."/>
                    <InputError class="mt-2" :message="form.errors.date" />
                </div>
            </div>
        </div>

        <div class="iq-card-footer d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-danger" @click="closeModal">Cancel</button>

        </div>
    </form>
</template>
