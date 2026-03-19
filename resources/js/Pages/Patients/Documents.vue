<script setup>
import { computed, ref } from "vue";
import AuthLayout from "@/Layouts/AuthLayout2.vue";
import Table from "@/Components/Table/Table.vue";
import Modal from '@/Components/Common/Modal.vue';
import Document from '@/Pages/Modals/Documents/UploadDocument.vue';
import { route } from 'ziggy-js';
 
const props = defineProps({
    documents: Object,
    keyword: String,
})

const currentTab = ref("laboratory");
const computedData = computed(() => props.documents[currentTab.value] || []);

const tabs = [
    { value: "laboratory", label: "Laboratory", dotClass: "dot-success" },
    { value: "imaging", label: "Imaging", dotClass: "dot-warning" },
    { value: "cardiopulmonary", label: "Cardiopulmonary", dotClass: "dot-primary" },
    { value: "endoscopy", label: "Endoscopy", dotClass: "dot-secondary" },
    { value: "referrals", label: "Referrals", dotClass: "dot-dark" },
    { value: "past-records", label: "Past Records", dotClass: "dot-primary" },
    { value: "other-forms", label: "Other Forms", dotClass: "dot-secondary" },
    { value: "letters", label: "Letters", dotClass: "dot-warning" },
    { value: "education", label: "Education", dotClass: "dot-success" },
    { value: "ccdas", label: "CCDAs", dotClass: "dot-primary" },
    { value: "ccrs", label: "CCRs", dotClass: "dot-secondary" },
];

const columns = [
    { label: "Name", key: "description" },
    { label: "From", key: "from" },
    { label: "Date", key: "date" },
];

const isOpenViewModal = ref(false);
const currentDocument = ref(null);
const item = ref(null);
const isDownloading = ref(false);
const isPrinting = ref(false);

const view = (document) => {
    currentDocument.value = document;
    item.value = document;
    isOpenViewModal.value = true;
};

const closeViewModal = () => {
    isOpenViewModal.value = false;
    currentDocument.value = null;
    item.value = null;
};

const getValidUrl = (url) => {
    if (!url) return '#';
    if (url.startsWith('http://') || url.startsWith('https://')) {
        return url;
    }
    return '/' + url.replace(/^\/+/, '');
};

const download = async (document) => {
    if (!document || !document.url) return;
    
    isDownloading.value = true;
    try {
        const validUrl = getValidUrl(document.url);
        window.open(validUrl, '_blank');
    } catch (error) {
        console.error('Download error:', error);
    } finally {
        isDownloading.value = false;
    }
};

const print = async (document) => {
    if (!document || !document.url) {
        console.error('print: No item or URL provided');
        alert('Error: No Document data available');
        return;
    }

    const url = getValidUrl(document.url);
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
</script>

<template>
    <AuthLayout title="Documents" description="Documents" heading="Documents">
        <div class="row">
            <!-- Side Tabs -->
            <div class="col-lg-3">
                <div class="iq-card">
                    <div class="iq-card-body">
                        <div class="finance-menu">
                            <button v-for="tab in tabs" :key="tab.value" type="button" class="menu-item"
                                :class="{ active: currentTab === tab.value }" @click="currentTab = tab.value">
                                <span class="dot" :class="tab.dotClass"></span>
                                <span class="label">{{ tab.label }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Content -->
            <div class="card col-sm-9 p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="card-title">Documents</h4>
                </div>
                <div class="iq-card-body">
                    <Table :columns="columns" :data="computedData" :search="keyword">
                        <template #actions="{ row: order }">
                            <div class="d-flex justify-content-end gap-1">
                              
                                <button class="btn btn-primary" data-tooltip="View docoument" @click="view(order)">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                        </template>
                    </Table>
                    
                </div>
                
            </div>
                     <Modal :isOpen="isOpenViewModal" @close="closeViewModal" :title="'View Docoument'" :size="'lg'">
            <div v-if="item" >
                <div class="letter-details mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Type:</strong> {{ item.type || 'Letter' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Date:</strong> {{ item.date }}
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <strong>From:</strong> {{ item.from || 'N/A' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Description:</strong> {{ item.description || item.name }}
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
        </div>
    </AuthLayout>
</template>

<style scoped>
.finance-menu {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    background: #fff;
    border: 1px solid #eef0f4;
    border-radius: 12px;
    padding: 12px 14px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    cursor: pointer;
    transition: 0.2s;
}

.menu-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 18px rgba(0, 0, 0, 0.06);
}

.menu-item.active {
    border-color: #6f42c1;
    box-shadow: 0 10px 20px rgba(111, 66, 193, 0.15);
}

.dot {
    height: 10px;
    width: 10px;
    border-radius: 50%;
}

.dot-success {
    background: #28a745;
}

.dot-warning {
    background: #ffc107;
}

.dot-secondary {
    background: #ff7b29;
}

.dot-primary {
    background: #0d6efd;
}

.dot-dark {
    background: #2e2138;
}

.label {
    font-size: 14px;
    color: #2b2b2b;
    font-weight: 600;
}
</style>
