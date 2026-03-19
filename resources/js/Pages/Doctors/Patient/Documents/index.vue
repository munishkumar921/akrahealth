<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useForm } from "@inertiajs/vue3";

import AuthLayout from "@/Layouts/AuthLayout2.vue";
import Table from "@/Components/Table/Table.vue";
import UploadDocument from '@/Pages/Modals/Documents/UploadDocument.vue';
import Education from '@/Pages/Modals/Documents/AddPatientEducation.vue';
import UploadCCD from '@/Pages/Modals/Documents/UploadCCD.vue';
import UploadCCR from '@/Pages/Modals/Documents/UploadCCR.vue';
import GenerateLetterModal from '@/Pages/Modals/Documents/GenerateLetter.vue';
 import Modal from '@/Components/Common/Modal.vue';
import axios from 'axios';
import Swal from 'sweetalert2/dist/sweetalert2.js';

// Document data state
const documentData = ref({
    laboratory: [],
    imaging: [],
    cardiopulmonary: [],
    endoscopy: [],
    refferrals: [],
    pastRecords: [],
    otherForms: [],
    letters: [],
    education: [],
    ccdas: [],
    ccrs: []
});


const tabs = [
    { key: 'Laboratory', label: 'Laboratory', dotClass: 'dot-success' },
    { key: 'Imaging', label: 'Imaging', dotClass: 'dot-warning' },
    { key: 'Cardiopulmonary', label: 'Cardiopulmonary', dotClass: 'dot-primary' },
    { key: 'Endoscopy', label: 'Endoscopy', dotClass: 'dot-secondary' },
    { key: 'Refferrals', label: 'Referrals', dotClass: 'dot-dark' },
    { key: 'Past Records', label: 'Past Records', dotClass: 'dot-primary' },
    { key: 'Other Forms', label: 'Other Forms', dotClass: 'dot-secondary' },
    { key: 'Letters', label: 'Letters', dotClass: 'dot-warning' },
    { key: 'Education', label: 'Education', dotClass: 'dot-success' },
    { key: 'CCDAs', label: 'CCDAs', dotClass: 'dot-primary' },
    { key: 'CCRs', label: 'CCRs', dotClass: 'dot-secondary' },
]

const columns =[
    { label: 'Name', key: 'name' },
    { label: 'Date', key: 'date' },   
];

// Loading state
const isLoading = ref(false);
const isDownloading = ref(false);
const isPrinting = ref(false);
const uploadDocumentModal = ref(false);
const isOpenUploadDocumentCCDModal = ref(false);
const isOpenEducationModal = ref(false);
const isOpenUploadDocumentCCRModal = ref(false);
const isOpenGenerateLetterModal = ref(false);
const isOpenViewModal = ref(false);
const currentDocument = ref(null);
const selectedPatientId = ref(null);
const error = ref(null);
const currentTab = ref('Laboratory');

// Success notification state
const successNotification = ref({
    show: false,
    message: ''
});

// Fetch documents by category
const fetchDocuments = async (category) => {
    isLoading.value = true;
    error.value = null;
    
    try {
        const response = await axios.get(route('doctor.documents.byCategory', { type: category === 'Past Records' ? 'past_records' : category.toLowerCase() }));
        
        if (response.data.success) {
            // Map category names to state keys
            const stateKey = category.toLowerCase().replace(/\s+/g, '');
            if (documentData.value.hasOwnProperty(stateKey)) {
                documentData.value[stateKey] = response.data.data;
            }
        } else {
            error.value = response.data.message || 'Failed to fetch documents';
        }
    } catch (err) {
        console.error('Error fetching documents:', err);
        error.value = err.response?.data?.message || 'An error occurred while fetching documents';
        
        // Fallback to empty array on error
        const stateKey = category.toLowerCase().replace(/\s+/g, '');
        if (documentData.value.hasOwnProperty(stateKey)) {
            documentData.value[stateKey] = [];
        }
    } finally {
        isLoading.value = false;
    }
};
 
// Map tab names to state keys
const getStateKey = (tab) => {
    const mapping = {
        'Laboratory': 'laboratory',
        'Imaging': 'imaging',
        'Cardiopulmonary': 'cardiopulmonary',
        'Endoscopy': 'endoscopy',
        'Refferrals': 'refferrals',
        'Past Records': 'pastRecords',
        'Other Forms': 'otherForms',
        'Letters': 'letters',
        'Education': 'education',
        'CCDAs': 'ccdas',
        'CCRs': 'ccrs'
    };
    return mapping[tab] || tab.toLowerCase().replace(/\s+/g, '');
};

// Computed data for current tab with loading and error handling
const currentData = computed(() => {
    if (isLoading.value) {
        return [];
    }
    
    if (error.value) {
        return [];
    }
    
    const stateKey = getStateKey(currentTab.value);
    
    // If we have data in state, use it
    if (documentData.value[stateKey] && documentData.value[stateKey].length > 0) {
        return documentData.value[stateKey];
    }
    
    // Return empty array if no data
    return [];
});

// Modal open functions
const openUploadDocumentModal = () => {
    uploadDocumentModal.value = true;
};
 

const GenerateLetter = async () => {
    // Fetch selected patient ID first
    try {
        const response = await axios.get(route('doctor.selected.patient'));
        if (response.data && response.data.id) {
            selectedPatientId.value = response.data.id;
        } else {
            selectedPatientId.value = null;
        }
    } catch (error) {
        console.error('Error fetching selected patient:', error);
        selectedPatientId.value = null;
    }
    
    // Open modal and call its openModal to fetch patient info
    isOpenGenerateLetterModal.value = true;
    
   
};

 const closeGenerateLetterModal=() => {
    isOpenGenerateLetterModal.value = false;
}
 const childComponentRef = ref(null);
// Edit document functions
const openEditDocumentModal = (item) => {
     uploadDocumentModal.value=true;
     setTimeout(() => {
        if (childComponentRef.value?.update) {
            childComponentRef.value.update(item);
        }
    }, 10);
};
 
// View letter function
const viewDoument = (item) => {
    currentDocument.value = item;
    isOpenViewModal.value = true;
};

const closeViewModal = () => {
    isOpenViewModal.value = false;
    currentDocument.value = null;
    isDownloading.value = false;
    isPrinting.value = false;
};


// Helper to ensure URL is absolute from root
const getValidUrl = (url) => {
    if (!url) return '';
    return (url.startsWith('http') || url.startsWith('/')) ? url : '/' + url;
};

// Download letter
const download = async (item) => {
    if (!item || !item.url) {
        console.error('download: No item or URL provided');
        alert('Error: No Document data available');
        return;
    }
    
    const url = getValidUrl(item.url);
    isDownloading.value = true;
    try {
        const response = await axios.get(url, {
            responseType: 'blob'
        });
        const blob = new Blob([response.data], { type: 'application/pdf' });
        const downloadUrl = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = item.name || 'document.pdf';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(downloadUrl);
    } catch (err) {
        console.error('Download failed:', err);
        window.open(url, '_blank');
    } finally {
        isDownloading.value = false;
    }
};

// Print letter
const print = async (item) => {
    if (!item || !item.url) {
        console.error('print: No item or URL provided');
        alert('Error: No Document data available');
        return;
    }

    const url = getValidUrl(item.url);
    isPrinting.value = true;

    try {
        // Open PDF in new window for printing
        const printWindow = window.open(url, '_blank');
        if (printWindow) {
            printWindow.onload = () => {
                printWindow.print();
            };
        }
    } catch (error) {
        console.error('Print error:', error);
        // Fallback: just open the URL
        window.open(url, '_blank');
    } finally {
        isPrinting.value = false;
    }

};

 
const openEducationModal = () => {
    isOpenEducationModal.value=true;
};
const closeEducationModal = () => {
    isOpenEducationModal.value=false;
};

const openUploadCCDModal = () => {
    isOpenUploadDocumentCCDModal.value = true;
};

const openUploadCCRModal = () => {
    isOpenUploadDocumentCCRModal.value = true;
};

// Modal close functions
const closeUploadDocumentCCDModal = () => {
    isOpenUploadDocumentCCDModal.value = false;
};

const closeUploadDocumentCCRModal = () => {
    isOpenUploadDocumentCCRModal.value = false;
};
const closeUploadDocumentModal = () => {
    uploadDocumentModal.value = false;
};

// Handle document saved event
const onDocumentSaved = () => {
    // Refresh the current tab data after save
    fetchDocuments(currentTab.value);
};

// Handle education saved event
const onEducationSaved = (data) => {
    // Show success notification
    successNotification.value = {
        show: true,
        message: 'Education material saved successfully!'
    };
    
    // Refresh the Education tab data
    fetchDocuments('Education');
    
    // Auto-hide notification after 5 seconds
    setTimeout(() => {
        successNotification.value.show = false;
    }, 5000);
};

// Delete document function
const deleteDocument = async (item) => {
    Swal.fire(confirmSettings("Are you sure you want to delete this document?"))
        .then(async (result) => {
            if (result.isConfirmed) {
                try {
                    isLoading.value = true;
                    const response = await axios.delete(route('doctor.documents.destroy', { id: item.id }));
                    
                    if (response.data.success) {
                        // Remove the deleted document from the local state
                        const stateKey = getStateKey(currentTab.value);
                        documentData.value[stateKey] = documentData.value[stateKey].filter(
                            doc => doc.id !== item.id
                        );
                        
                        toast('Document has been deleted successfully.', 'success');
                    } else {
                        throw new Error(response.data.message || 'Failed to delete document');
                    }
                } catch (error) {
                    console.error('Error deleting document:', error);
                    toast(error.response?.data?.message , 'error');
                } finally {
                    isLoading.value = false;
                }
            }
        });
};
// Watch for tab changes and fetch data
watch(currentTab, (newTab) => {
    const stateKey = getStateKey(newTab);
    
    // Only fetch if we don't have data yet
    if ((!documentData.value[stateKey] || documentData.value[stateKey].length === 0) && !isLoading.value) {
        fetchDocuments(newTab);
    }
});

// Fetch initial data on mount
onMounted(() => {
    fetchDocuments(currentTab.value);
});

</script>
<template>
    <AuthLayout title="Documents" description="Documents" heading="">
         <!-- Success Notification -->
        <div v-if="successNotification.show" class="alert alert-success alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
            {{ successNotification.message }}
            <button type="button" class="close" @click="successNotification.show = false" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        
        <div class="row">
            <div class="col-lg-3">
                <div class="iq-card">
                    <div class="iq-card-body">
                        <div class="finance-menu">
                            <button
                                v-for="tab in tabs"
                                :key="tab.key"
                                type="button"
                                class="menu-item"
                                :class="{ active: currentTab === tab.key }"
                                @click="currentTab = tab.key"
                            >
                                <span class="dot" :class="tab.dotClass"></span>
                                <span class="label">{{ tab.label }}</span>
                            </button>
                        </div>
                    </div>
                </div>             
            </div>
            <div class="col-sm-9 iq-card p-4">
                <div class="align-items-center d-flex justify-content-between">
                    <div class="todo-date d-flex mr-3">
                        <h4 class="card-title">Documents</h4>
                       
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="notification-icon position-relative d-flex align-items-center mr-3"></div>                       
                        <div class="btn-group ms-2">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa-solid fa-square-plus pointer"></i>
                        </button>
                        <div class="dropdown-menu">
                            <button @click="openUploadDocumentModal" class="dropdown-item pointer">Upload Document</button>
                            <button @click="GenerateLetter" class="dropdown-item pointer">Generate Letter</button>
                            <button @click="openEducationModal" class="dropdown-item pointer">Add Patient Education</button>
                            <button @click="openUploadCCDModal" class="dropdown-item pointer">Upload Consolidated Clinical Document (C-CDA)</button>
                            <button @click="openUploadCCRModal" class="dropdown-item pointer">Upload Continuity of Care Record (CCR)</button>
                                
                        </div>
                        
                        </div>
                    </div>
                </div>
                <div class="iq-card-body">
                     <!-- Data Table -->
                    <Table :columns="columns" :data="{data:currentData}" :search="keyword??''">
                        <template #actions="{row:item }">
                            <div class="d-flex gap-1 justify-content-end">
                                <button @click="openEditDocumentModal(item)" class="btn btn-primary" data-placement="top" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button @click="viewDoument(item)" class="btn btn-success" data-placement="top" title="View">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button @click="deleteDocument(item)" class="btn btn-danger" data-placement="top" title="Delete">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div> 
                        </template>
                    </Table>
                </div>
            </div>
        </div>
        <Modal :isOpen="uploadDocumentModal" @close="closeUploadDocumentModal" :title="'Document'" :size="'lg'">
            <UploadDocument ref="childComponentRef" @close="closeUploadDocumentModal" @saved="onDocumentSaved"/>
        </Modal>
        <Modal :isOpen="isOpenEducationModal" @close="closeEducationModal" :title="'Add Patient Education'" :size="'lg'">        
        <Education ref="educationComponent" @close="closeEducationModal" @saved="onEducationSaved"/>
        </Modal>
        <Modal :isOpen="isOpenUploadDocumentCCDModal" @close="closeUploadDocumentCCDModal" :title="'Upload Consolidated Clinical Document (C-CDA)'" :size="'lg'">
        <UploadCCD ref="uploadCCDModal" @close="closeUploadDocumentCCDModal"/>
        </Modal>
        <Modal :isOpen="isOpenUploadDocumentCCRModal" @close="closeUploadDocumentCCRModal" :title="'Upload Continuity of Care Record (CCR)'":size="'lg'">
        <UploadCCR ref="uploadCCRModal" @close="closeUploadDocumentCCRModal"/>
        </Modal>
        <Modal :isOpen="isOpenGenerateLetterModal" @close="closeGenerateLetterModal" :title="'Generate Letter'" :size="'lg'">   
        <GenerateLetterModal  :patient-id="selectedPatientId" @close="closeGenerateLetterModal"/>
        </Modal>
        
        <!-- Edit Document Modal -->
        <!-- <Modal :isOpen="!!selectedDocumentId" @close="selectedDocumentId = null" :title="'Edit Document'" :size="'lg'">
            <EditDocumentModal ref="editDocumentComponent" :document-id="selectedDocumentId" @close="selectedDocumentId = null" @updated="onDocumentUpdated"/>
        </Modal>
         -->
        <!-- View Letter Modal -->
        <Modal :isOpen="isOpenViewModal" @close="closeViewModal" :title="'View Docoument'" :size="'lg'">
            <div v-if="currentDocument">
                <div class="letter-details mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Type:</strong> {{ currentDocument.type || 'Letter' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Date:</strong> {{ currentDocument.date }}
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <strong>From:</strong> {{ currentDocument.from || 'N/A' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Description:</strong> {{ currentDocument.description || currentDocument.name }}
                        </div>
                    </div>
                </div>
                
                <div class="letter-actions mb-3">
                    <button @click="download(currentDocument)" class="btn btn-primary mr-2" :disabled="isDownloading">
                        <span v-if="isDownloading" class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
                        <i v-else class="bi bi-download"></i> {{ isDownloading ? 'Downloading...' : 'Download PDF' }}
                    </button>
                    <button @click="print(currentDocument)" class="btn btn-success mr-2" :disabled="isPrinting">
                        <span v-if="isPrinting" class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
                        <i v-else class="bi bi-printer"></i> {{ isPrinting ? 'Preparing...' : 'Print' }}
                    </button>
                    <a v-if="currentDocument.url" :href="getValidUrl(currentDocument.url)" target="_blank" class="btn btn-info">
                        <i class="bi bi-eye"></i> Open PDF
                    </a>
                </div>
                
            </div>
            <div v-else class="text-center text-muted">
                No Doument data available
            </div>
        </Modal>
    </AuthLayout>
</template>

<style scoped>
.finance-menu { display: flex; flex-direction: column; gap: 12px; }
.menu-item {
    display: flex; align-items: center; gap: 10px; width: 100%;
    background: #fff; border: 1px solid #eef0f4; border-radius: 12px;
    padding: 12px 14px; box-shadow: 0 4px 12px rgba(0,0,0,.04);
    cursor: pointer; transition: .2s;
}
.menu-item:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(0,0,0,.06); }
.menu-item.active { border-color: #6f42c1; box-shadow: 0 10px 20px rgba(111,66,193,.15); }
.dot { height: 10px; width: 10px; border-radius: 50%; }
.dot-success { background: #28a745; }
.dot-warning { background: #ffc107; }
.dot-secondary { background: #ff7b29; }
.dot-primary { background: #0d6efd; }
.dot-dark { background: #2e2138; }
.label { font-size: 14px; color: #2b2b2b; font-weight: 600; }
</style>
