<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import BaseInput from '@/Components/Common/Input/BaseInput.vue';
import Search from "@/Components/Common/Search.vue";
import Modal from '@/Components/Common/Modal.vue';

import axios from 'axios';

const props = defineProps({
    selectedTopic: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['close-modal', 'saved']);

const isSearching = ref(false);
const showResults = ref(false);
const searchQuery = ref("");
const searchResult = ref([]);
const loader = ref(false);

// Healthwise content modal
const showHealthwiseModal = ref(false);
const healthwiseContent = ref('');
const healthwiseTitle = ref('');
const isLoadingContent = ref(false);
const currentItemUrl = ref('');

const form = useForm({
    topic: '',
    description: '',
    url: '',
    education_type: '',
  });
 
const search = () => {
    loader.value=true;
    
     axios.post(route('doctor.education.search', { search: searchQuery.value,  })).then((response) => {
        searchResult.value = response.data.data || [];
        loader.value=false;
        showResults.value = true;
    }).catch((error) => {
            loader.value=false;
            searchResult.value = [];
            showResults.value = true;
            console.error('Error in search request:', error);
        });
};

// Clear selection helper
const clearSelection = () => {
    form.topic = '';
    form.description = '';
    form.url = '';
    form.education_type = '';
    showResults.value = false;
};

const close = () => {
    searchQuery.value = '';
    searchResult.value = [];
    showResults.value = false;
};

// Fetch healthwise content and show modal
const fetchHealthwiseContent = async (item) => {
     isLoadingContent.value = true;
    healthwiseTitle.value = item.topic;
    currentItemUrl.value = item.url;
    
    try {
        const response = await axios.post(route('doctor.education.healthwise_view'), {
            url: item.url
        });
        
        if (response.data.success && response.data.html) {
            healthwiseContent.value = response.data.html || '';
            showHealthwiseModal.value = true;
        }  
    } catch (error) {
        console.log('Error fetching healthwise content:', error);
        
       
        showHealthwiseModal.value = true;
    } finally {
        isLoadingContent.value = false;
    }
};

const handleClick = (item) => {
    // Fetch and display healthwise content in modal
    selectEducationMaterial(item);
    fetchHealthwiseContent(item);
};

// Close healthwise modal and set form data
const selectEducationMaterial = (item) => {
    form.topic = item.topic || healthwiseTitle.va;
    form.description =  item.description || healthwiseTitle.value;
    form.url = currentItemUrl.value;
    showHealthwiseModal.value = false;
    close();
};

// Close healthwise modal without selecting
const closeHealthwiseModal = () => {
    showHealthwiseModal.value = false;
    healthwiseContent.value = '';
    healthwiseTitle.value = '';
};

// Close modal
const closeModal = () => {
    emit('close-modal');
    form.reset();
    clearSelection();
};

// Submit form - Save Only
const submitSave = async () => {
    await saveEducation('save');
};

// Submit form - Save and Download
const submitSaveDownload = async () => {
    await saveEducation('download');
};

// Main save function
const saveEducation = async (action) => {
    if (!form.topic) {
        alert('Please select a topic first');
        return;
    }

    try {
        const response = await axios.post(route('doctor.education.store'), {
            topic: form.topic,
            description: form.description,
            url: form.url,
            education_type: form.education_type,
        });

        if (response.data.success) {
            if (action === 'download' && form.url) {
                // Open the education material URL in new tab
                window.open(form.url, '_blank');
            }
            
            emit('saved', response.data.data);
            closeModal();
        } else {
            alert(response.data.message || 'Failed to save education material');
        }
    } catch (error) {
        console.error('Failed to save education material:', error);
        alert(error.response?.data?.message || 'Failed to save education material');
    }
};
 
// Expose methods to parent component
defineExpose({
    closeModal,
 });
</script>

<template>
    <div class="patient-education-modal">
        <form @submit.prevent>
            <!-- Search Section -->
            <div class="modal-body">
                <div class="iq-card-body">
                    <h5 class="mb-3">Search Patient Education Materials</h5>
                    <!-- Search Input -->
                     <div class=" mb-4">
                        <Search
                            v-model="searchQuery"
                            :searchResult="searchResult"
                            :loader="loader"
                            @input="search"
                            :placeholder="'Search for RX'"/>
                    </div>
                    <!-- Search Results Dropdown -->
                    <div v-if="showResults && (searchResult.length > 0 || isSearching || loader)" class="search-results-container mb-3">
                        <div v-if="loader" class="text-center py-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Searching...</p>
                        </div>
                        
                        <ul v-else-if="searchResult.length > 0" class="list-group search-results-list overflow-auto h-64">
                            <li 
                                v-for="(result, index) in searchResult" 
                                :key="index"
                                class="list-group-item list-group-item-action cursor-pointer"
                                @click="handleClick(result)"
                            >
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ result.topic }}</h6>
                                        <small class="text-muted">{{ result.type }}</small>
                                    </div>
                                    <span class="badge bg-primary rounded-pill" v-if="result.url">
                                        <i class="bi bi-link-45deg"></i>
                                    </span>
                                </div>
                            </li>
                        </ul>

                        <div v-else-if="searchQuery && !loader" class="text-center py-3 text-muted">
                            <i class="bi bi-search display-6"></i>
                            <p>No results found for "{{ searchQuery }}"</p>
                            <small>Try different keywords or browse popular topics below</small>
                        </div>
                    </div>
                    <div class="row">
                        <BaseInput v-model="form.topic" label="Topic" placeholder="Topic" required :error="form.errors.topic" />
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="d-flex justify-content-end gap-2">
                <button
                    type="button"
                    class="btn btn-danger"
                    @click="closeModal"
                >
                    Cancel
                </button>
                <button 
                    type="button" 
                    class="btn btn-primary"
                    @click="submitSave"
                    :disabled="!form.topic"
                >
                    <i class="bi bi-save me-1"></i>
                    Save Only
                </button>
                <button 
                    type="button" 
                    class="btn btn-success"
                    @click="submitSaveDownload"
                    :disabled="!form.topic"
                >
                    <i class="bi bi-download me-1"></i>
                    Save and Download
                </button>
            </div>
        </form>

        <!-- Healthwise Content Modal -->
        <Modal :isOpen="showHealthwiseModal" @close="closeHealthwiseModal" :title="healthwiseTitle" size="xl">
            <div v-if="isLoadingContent" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading education material...</p>
            </div>
            <div v-else class="healthwise-content">
                <div v-html="healthwiseContent"></div>
            </div>
            <template #footer>
              
                <button type="button" class="btn btn-primary" @click="selectEducationMaterial">
                    <i class="bi bi-check-circle me-1"></i>
                    Select This Material
                </button>
                  <button type="button" class="btn btn-secondary" @click="closeHealthwiseModal">
                    Close
                </button>
            </template>
        </Modal>
    </div>
</template>

<style scoped>
.healthwise-content {
    max-height: 60vh;
    overflow-y: auto;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 8px;
}

.healthwise-content :deep(img) {
    max-width: 100%;
    height: auto;
}

.healthwise-content :deep(a) {
    color: #0d6efd;
}

.healthwise-content :deep(h1),
.healthwise-content :deep(h2),
.healthwise-content :deep(h3),
.healthwise-content :deep(h4),
.healthwise-content :deep(h5),
.healthwise-content :deep(h6) {
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}

.healthwise-content :deep(p) {
    margin-bottom: 0.5rem;
}

.healthwise-content :deep(ul),
.healthwise-content :deep(ol) {
    margin-left: 1.5rem;
    margin-bottom: 0.5rem;
}

.healthwise-content :deep(.HwSectionNameTag) {
    display: none;
}
</style>
