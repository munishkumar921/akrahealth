<script setup>
import { ref, onMounted, watch } from 'vue';
import { useForm } from "@inertiajs/vue3";
import Modal from '@/Components/Common/Modal.vue';
import axios from 'axios';
import Swal from 'sweetalert2/dist/sweetalert2.js';
import 'sweetalert2/src/sweetalert2.scss';

// Define emits
const emit = defineEmits(['template-selected', 'update-template']);

const props = defineProps({
    data: Object,
    category: String,
});

const selectedParentTemplate = ref(null);
const templates = ref([]);
const templateItems = ref([]);
const separator = ref(', ');

// Watch for data changes and update templates
watch(() => props.data, (newData) => {
    if (newData && newData.message) {
        templates.value = newData.message;
     }
}, { immediate: true });

// Initialize with prop data
onMounted(() => {
    if (props.data && props.data.message) {
        templates.value = props.data.message;
    }
});

// Function to get sub-templates when a category is clicked
const getTemplateItems = (template) => {
     selectedParentTemplate.value = template;
    const form = new FormData();
    form.append("id", props.category);
    form.append("template_group", template?.value);
    
    axios.post(route('doctor.get.templates'), form)
        .then(response => {
            templateItems.value = response.data?.message || [];
        })
        .catch(error => {
            console.error('Error fetching template items:', error);
            templateItems.value = [];
        });
}

// Function to go back to main categories
const backToTemplates = () => {
    selectedParentTemplate.value = null;
    templateItems.value = [];
}

// Function to select a sub-template item
const selectTemplateItem = (item) => {
    if (['text', 'radio', 'checkbox', 'select'].includes(item.input)) {
        openTemplateModal(item);
    } else {
        // Emit the selected template data to parent component
        emit('template-selected', {
            value: item.value,
            text: item.value,
            category: props.category,
            inputType: item.input,
            options: item.options
        });
    }
};

// Function to select main template directly (if needed)
const selectDirectTemplate = (template) => {
    emit('template-selected', {
        value: template.value,
        text: template.value,
        category: props.category,
        inputType: template.input,
        options: template.options
    });
};

// Delete template function
const deleteTemplate = (template) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = new FormData();
            form.append('id', template?.id || null);
            form.append('group_name', template?.group ? template?.group : template?.value);
            form.append('category', props.category);
            form.append('template_edit_type', template?.id ? 'item' : 'group');

            axios.post(route('doctor.delete.template'), form)
                .then(response => {
                    if (selectedParentTemplate.value) {
                        getTemplateItems(selectedParentTemplate.value);
                    } else {
                        templates.value = templates.value.filter(t => t.value !== template?.value);
                    }
                    emit('update-template');
                });
        }
    });
};

// Template modal functions
const templateForm = useForm({
    id: '',
    template_text: '',
    template_gender: '',
    template_age: '',
    template_input_type: '',
    template_options: '',
    template_options_orders_facility: '',
    template_options_orders_orders_code: '',
    template_options_orders_cpt: '',
    template_options_orders_loinc: '',
    template_options_orders_results_code: '',
    template_edit_type: '',
    group_name: '',
    category: '',
});

const isTemplateModalOpen = ref(false);
const closeTemplateModal = () => {
    isTemplateModalOpen.value = false;
};

const openTemplateModal = (template) => {
    isTemplateModalOpen.value = true;
    templateForm.category = props.category;
    templateForm.template_text = template?.value;

    templateForm.template_gender = template?.gender || '';
    templateForm.template_age = template?.age || '';

    templateForm.template_edit_type = selectedParentTemplate.value?.value ? 'item' : 'group';
    templateForm.group_name = selectedParentTemplate.value?.value ? selectedParentTemplate.value?.value : template?.value;

    templateForm.template_input_type = template?.input;
    templateForm.template_options = template?.options || '';

    if (template?.id) {
        templateForm.id = template?.id;
        templateForm.template_options_orders_facility = template?.orders?.facility || '';
        templateForm.template_options_orders_orders_code = template?.orders?.orders_code || '';
        templateForm.template_options_orders_cpt = template?.orders?.cpt || '';
        templateForm.template_options_orders_loinc = template?.orders?.loinc || '';
        templateForm.template_options_orders_results_code = template?.orders?.results_code || '';
    }
}

const newTemplate = () => {
    isTemplateModalOpen.value = true;
    templateForm.template_edit_type = selectedParentTemplate.value?.value ? 'item' : 'group';
    templateForm.category = props.category;
    templateForm.template_options_orders_facility = selectedParentTemplate.value?.value || '';
    templateForm.group_name = selectedParentTemplate.value?.value || '';
    templateForm.id = selectedParentTemplate.value?.value ? 'new' : '';
}

const updateTemplate = () => {
    axios.post(route('doctor.update.template'), templateForm)
        .then(response => {
            closeTemplateModal();
            emit('update-template');
            
            if (selectedParentTemplate.value) {
                getTemplateItems(selectedParentTemplate.value);
            }
        });
};
</script>

<template>
    <div class="col-md-10">
        <div class="card mb-3 template-container">
            <div class="card-header bg-primary bg-white border-bottom-0">
                <div class="card-title flex justify-between align-items-center">
                    <span class="font-bold">Templates</span>
                    <div class="d-flex align-items-center mt-3">
                        <!-- Show current category name when in sub-templates -->
                        <p v-if="selectedParentTemplate" class="form-label btn-primary p-1 rounded-3 mb-0 mr-2">
                            {{ selectedParentTemplate.value }}
                        </p>
                        
                        <select v-model="separator" v-if="!selectedParentTemplate" class="form-select w-auto mx-2">
                            <option value="">Delimiter:</option>
                            <option value=", ">comma (,)</option>
                            <option value=" ">space</option>
                            <option value="  ">double space</option>
                            <option value="; ">semi-colon (;)</option>
                        </select>

                        <!-- Add template button -->
                        <i @click="newTemplate()" class="cursor-pointer fa fa-plus fa-lg"></i>
                    </div>
                </div>
            </div>
            
            <!-- Back button when viewing sub-templates -->
            <div v-if="selectedParentTemplate" class="card-header bg-light py-2">
                <button @click="backToTemplates" class="btn btn-sm btn-outline-secondary">
                    <i class="fa fa-arrow-left me-1"></i> Back to Categories
                </button>
            </div>

            <div class="card-body p-0 template-content">
                <!-- Main Categories View -->
                <div v-if="!selectedParentTemplate" class="template-categories">
                    <div v-if="templates?.length > 0" class="categories-list">
                        <div v-for="template in templates" :key="template.value" 
                             class="template-category-item border-bottom p-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="fa fa-folder text-warning me-2"></i>
                                <span class="template-name cursor-pointer" 
                                      @click="getTemplateItems(template)">
                                    {{ template.value }}
                                </span>
                            </div>
                            <div class="template-actions">
                                <i @click="getTemplateItems(template)" 
                                   class="fa fa-arrow-right cursor-pointer me-2 text-muted" 
                                   title="View items"></i>
                                <i @click="openTemplateModal(template)"
                                   class="fa fa-pencil cursor-pointer me-2 text-primary" 
                                   title="Edit"></i>
                                <i @click="deleteTemplate(template)"
                                   class="fa fa-trash cursor-pointer text-danger" 
                                   title="Delete"></i>
                            </div>
                        </div>
                    </div>
                    <div v-else class="empty-state">
                        <p class="text-muted text-center py-4 mb-0">No template categories available</p>
                    </div>
                </div>

                <!-- Sub-Templates View -->
                <div v-else class="template-items">
                    <div v-if="templateItems?.length > 0" class="items-list">
                        <div v-for="item in templateItems" :key="item.value" 
                             class="template-item border-bottom p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="template-item-name cursor-pointer fw-medium"
                                      @click="selectTemplateItem(item)">
                                    {{ item.value }}
                                </span>
                                <div class="template-item-actions">
                                    <i @click="openTemplateModal(item)"
                                       class="fa fa-pencil cursor-pointer me-2 text-primary" 
                                       title="Edit"></i>
                                    <i @click="deleteTemplate(item)"
                                       class="fa fa-trash cursor-pointer text-danger" 
                                       title="Delete"></i>
                                </div>
                            </div>
                            
                            <!-- Input fields for complex template types -->
                            <div v-if="['text', 'radio', 'checkbox', 'select'].includes(item.input)" 
                                 class="template-input-fields mt-2">
                                <div v-if="item.input == 'text'" class="mb-2">
                                    <input type="text" class="form-control form-control-sm" 
                                           :placeholder="`Enter ${item.value}`" />
                                </div>

                                <div v-else-if="item.input == 'radio'" class="mb-2">
                                    <div class="row">
                                        <template v-for="option in item.options.split(',')">
                                            <div class="col-6 mb-1">
                                                <label class="d-flex align-items-center small">
                                                    <input type="radio" class="me-2 cursor-pointer" 
                                                           :name="'radio_' + item.value" />
                                                    {{ option.trim() }}
                                                </label>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div v-else-if="item.input == 'checkbox'" class="mb-2">
                                    <div class="row">
                                        <template v-for="option in item.options.split(',')">
                                            <div class="col-6 mb-1">
                                                <label class="d-flex align-items-center small">
                                                    <input type="checkbox" class="me-2 cursor-pointer" 
                                                           :name="'checkbox_' + item.value" />
                                                    {{ option.trim() }}
                                                </label>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div v-else-if="item.input == 'select'" class="mb-2">
                                    <select class="form-control form-control-sm">
                                        <option value="">Select {{ item.value }}</option>
                                        <template v-for="option in item.options.split(',')">
                                            <option :value="option.trim()">{{ option.trim() }}</option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="empty-state">
                        <p class="text-muted text-center py-4 mb-0">No items in this category</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Template Modal -->
    <Modal :isOpen="isTemplateModalOpen" title="Template Toolbox" @close="closeTemplateModal" size="lg">
        <div>
            <div class="mb-3">
                <label for="template-text" class="form-label">Template Text</label>
                <textarea id="template-text" class="form-control" rows="4"
                    v-model="templateForm.template_text"></textarea>
            </div>
            <div class="mb-3">
                <label for="template-category" class="form-label">Gender Association</label>
                <select id="template-category" class="form-control" v-model="templateForm.template_gender">
                    <option value="">All Genders</option>
                    <option value="male">Male Only</option>
                    <option value="female">Female Only</option>
                    <option value="u">Undifferentiated Only</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="template-type" class="form-label">Age Association</label>
                <select id="template-type" class="form-control" v-model="templateForm.template_age">
                    <option value="">All Ages</option>
                    <option value="adult">Adult Only</option>
                    <option value="child">Child Only</option>
                </select>
            </div>

            <div v-if="selectedParentTemplate">
                <div class="mb-3">
                    <label for="template-type" class="form-label">Template Input Type</label>
                    <select id="template-type" class="form-control" v-model="templateForm.template_input_type">
                        <option value="">None</option>
                        <option value="text">Text Input</option>
                        <option value="select">Dropdown List</option>
                        <option value="checkbox">Checkbox</option>
                        <option value="radio">Radio Button</option>
                        <option value="orders">Orders</option>
                    </select>
                </div>

                <div v-if="['select', 'checkbox', 'radio'].includes(templateForm.template_input_type)" class="mb-3">
                    <label for="template-type" class="form-label">Options</label>
                    <input type="text" v-model="templateForm.template_options" class="form-control"
                        placeholder="Comma separated e.g. option-1,option-2" />
                </div>

                <div v-if="templateForm.template_input_type == 'orders'">
                    <!-- Orders specific fields -->
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-3">
                <button class="btn btn-sm btn-primary" @click="updateTemplate()">Update</button>
                <button class="btn btn-sm btn-danger" @click="closeTemplateModal">Cancel</button>
            </div>
        </div>
    </Modal>
</template>

<style scoped>
.template-container {
    height: 600px; /* Fixed height for the entire template container */
    display: flex;
    flex-direction: column;
}

.template-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden; /* Prevent overall overflow */
}

.template-categories,
.template-items {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.categories-list,
.items-list {
    flex: 1;
    overflow-y: auto; /* Scrollable content */
    overflow-x: hidden;
    max-height: 100%;
}

/* Custom scrollbar styling */
.categories-list::-webkit-scrollbar,
.items-list::-webkit-scrollbar {
    width: 6px;
}

.categories-list::-webkit-scrollbar-track,
.items-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.categories-list::-webkit-scrollbar-thumb,
.items-list::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.categories-list::-webkit-scrollbar-thumb:hover,
.items-list::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.template-category-item {
    min-height: 60px;
    transition: background-color 0.2s ease;
}

.template-category-item:hover {
    background-color: #f8f9fa;
}

.template-item {
    min-height: 50px;
    transition: background-color 0.2s ease;
}

.template-item:hover {
    background-color: #f8f9fa;
}

.template-name {
    font-weight: 500;
    word-break: break-word;
}

.template-item-name {
    word-break: break-word;
}

.template-item-name:hover {
    color: #0d6efd;
}

.cursor-pointer {
    cursor: pointer;
}

.template-actions i,
.template-item-actions i {
    opacity: 0.7;
    transition: opacity 0.2s;
    font-size: 14px;
}

.template-actions i:hover,
.template-item-actions i:hover {
    opacity: 1;
}

.template-input-fields {
    border-left: 3px solid #e9ecef;
    padding-left: 1rem;
    background-color: #fafafa;
    border-radius: 4px;
    padding: 8px;
    margin-top: 8px;
}

.empty-state {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 200px;
}

/* Responsive adjustments */
@media (max-height: 700px) {
    .template-container {
        height: 500px;
    }
}

@media (max-height: 600px) {
    .template-container {
        height: 400px;
    }
}

/* Ensure proper spacing for long content */
.template-category-item:last-child,
.template-item:last-child {
    border-bottom: none !important;
}

/* Smooth scrolling */
.categories-list,
.items-list {
    scroll-behavior: smooth;
}

/* Focus states for accessibility */
.template-category-item:focus-within,
.template-item:focus-within {
    background-color: #e7f1ff;
    outline: 2px solid #0d6efd;
    outline-offset: -2px;
}
</style>