<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import BaseInput from '@/Components/Common/Input/BaseInput.vue';
import BaseSelect from '@/Components/Common/Input/BaseSelect.vue';
import BaseFileInput from '@/Components/Common/Input/BaseFileInput.vue'; 
import BaseDatePicker from '@/Components/Common/Input/BaseDatePicker.vue';
import axios from 'axios';
import { identity } from 'lodash';
 
const props = defineProps({
    documentId: {
        required: true
    }
});

const emit = defineEmits(['close', 'updated']);

const showModal = ref(false);
const isLoading = ref(false);
const isSubmitting = ref(false);
const errorMessage = ref('');

const form = useForm({
    id: '',
    description: '',
    from: '',
    date: '',
    type: '',
    file: null,
});
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

// Watch for documentId changes to fetch data
watch(() => props.documentId, (newId) => {
    if (newId && showModal.value) {
        fetchDocumentData();
    }
});

// Watch for modal visibility
watch(showModal, (visible) => {
    if (visible && props.documentId) {
        fetchDocumentData();
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = 'auto';
    }
});

const fetchDocumentData = async () => {
    isLoading.value = true;
    errorMessage.value = '';

    try {
        const response = await axios.get(route('doctor.documents.edit', { document: props.documentId }));

        if (response.data.success) {
            const data = response.data.data;
            form.clearErrors();
            form.id = data.id || '';
            form.description = data.description || '';
            form.from = data.from || '';
            form.date = data.date ? data.date.split(' ')[0] : '';
            form.type = data.type || '';
            form.file = data.url || ''; // Set to URL string for display in BaseFileInput
            
        } else {
         }
    } catch (error) {
        console.error('Error fetching document:', error);
     } finally {
        isLoading.value = false;
    }
};

const openModal = () => {
    showModal.value = true;
 };

const closeModal = () => {
    showModal.value = false;
    document.body.style.overflow = 'auto';
    emit('close');
};

const submit = () => {
    if (isSubmitting.value) return

    isSubmitting.value = true
 
    form.post(route('doctor.documents.update'), {
        onSuccess: () => {
            emit('updated');
            closeModal();
        },
        onError: () => {
         },
        onFinish: () => {
            isSubmitting.value = false
        }
    })
}

// Expose methods to parent component
defineExpose({
    openModal,
    closeModal
});

</script>

<template>

    <!-- Form -->
    <form @submit.prevent="submit">
         <div class="row">
                  <BaseFileInput v-model="form.file" />
             </div>
        <div class="form-group">
             <BaseSelect v-model="form.type" label="Type" placeholder="Select a type..." :error="form.errors?.type">
                <option v-for="option in types" :key="option.value" :value="option.value">{{ option.label }}</option>
             </BaseSelect>
         </div>

        <div class="form-group mt-3">
             <BaseInput v-model="form.description" type="text" label="Description" placeholder="Enter a description for this file..." :error="form.errors?.description" />
        </div>

        <div class="form-group mt-3">
            <BaseInput v-model="form.from" type="text" label="From" placeholder="Enter a from for this file..." :error="form.errors?.from" />
        </div>

        <div class="form-group mt-3">
             <BaseDatePicker type="date" v-model="form.date" label="Date" placeholder="Select date..." :error="form.errors?.date" />
        </div>
        <div class="d-flex justify-content-end gap-2">
          
            <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2" role="status"
                    aria-hidden="true"></span>
                {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
            </button>
              <button type="button" class="btn btn-light" @click="closeModal" :disabled="isSubmitting">
                Close
            </button>
        </div>
    </form>

</template>

<style scoped>
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1040;
}

.modal {
    z-index: 1050;
}

.modal.show {
    background-color: rgba(0, 0, 0, 0.5);
}

.form-label {
    font-weight: 600;
    color: #495057;
    font-size: 0.9rem;
}

.form-text {
    font-size: 0.75rem;
}
</style>
