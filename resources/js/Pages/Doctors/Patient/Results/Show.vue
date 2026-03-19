<script setup>
import { ref } from 'vue';
import AuthLayout from "@/Layouts/AuthLayout2.vue";
import {router } from '@inertiajs/vue3';
import Modal from '@/Components/Common/Modal.vue';
import AddResult from '@/Pages/Modals/AddResult.vue';
import { defineProps } from "vue";

const props = defineProps({
    result: Object,
    doctors: Array,

});


// Modal refs
const isAddResultModalOpen = ref(false);
const childComponentRef = ref(null);

const getFileExtension = (filename) => {
    return filename.split('.').pop();
};

const isImage = (filename) => {
    const ext = getFileExtension(filename);
    return ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(ext.toLowerCase());
};

const isOpen = ref(false);
 
const openAddResultModal = () => {
    isAddResultModalOpen.value = true;
};
// Modal close functions
const closeAddResultModal = () => {
    isAddResultModalOpen.value = false;
};
const editResult = (result) => {

    setTimeout(() => {
        if (childComponentRef.value?.update) {
            childComponentRef.value.update(result);
        }
    }, 10);
    openAddResultModal();
};

const Chat = (result) => {
    router.get(route('doctor.resultsChart', { result: result.id }));
};

const printResult = () => {
    const printableArea = document.getElementById('printable-area');
    if (printableArea) {
        const printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title>Print Result</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">');
        printWindow.document.write('</head><body>');
        printWindow.document.write(printableArea.innerHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    }
};

const menuItems = (result) => [
    { label: "Edit", icon: "fa fa-edit", action: () => editResult(result) },

    { label: "Chart", icon: "fa fa-chart-line", action: () =>Chat(result)},
    { label: "Print", icon: "fa fa-print",action:()=>printResult() },

    { label: "Add Lab Order", icon: "fa fa-flask", action:()=>orderBy() },

    { label: "Add Imaging Order", icon: "fa fa-image",  },

    { label: "Add Cardiopulmonary Order", icon: "fa fa-heartbeat", },

    { label: "Add Referral", icon: "fa fa-user-md", },
];


</script>

<template>
    <AuthLayout title="Result Details" description="View details of a patient's result" heading="Result Details">
        <div class="row">
            <div class="col-lg-12">
                <div class="iq-card">
                    <div class="iq-card-header d-flex justify-content-between">
                        <div class="iq-header-title">
                            <h4 class="card-title">Result: {{ result.name }}</h4>
                        </div>

                        <div class="dropdown" style="position: relative; display: inline-block;">
                            <!-- Button -->
                            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-bars"></i>  
                            </button>

                            <!-- Dropdown Menu -->
                            <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton"
                                style="min-width: 200px;">
                                   <li v-for="(item, index) in menuItems(result)" :key="index">
                                <a
                                v-if="item.href"
                                :href="item.href"
                                target="_blank"
                                class="dropdown-item"
                                style="cursor: pointer;"
                                >
                                <i :class="[item.icon, 'fa-fw', 'fa-btn mr-1']"></i> {{ item.label }}
                                </a>
                                <a
                                v-else
                                href="javascript:void(0)"
                                class="dropdown-item"
                                style="cursor: pointer;"
                                @click="item.action()"
                                >
                                <i :class="[item.icon, 'fa-fw', 'fa-btn mr-1']"></i> {{ item.label }}
                                </a>
                            </li>
                            </ul>
                        </div>

                    </div>
                    <div class="iq-card-body" id="printable-area">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered align-middle">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Type</th>
                                        <th>Test Name</th>
                                        <th>Result</th>
                                        <th>Reference Range</th>
                                        <th>Flag</th>
                                        <th>Date</th>
                                        <th>Location</th>
                                        <th>LOINC Code</th>
                                        <th>Provider</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <td>{{ result.type }}</td>
                                    <td>{{ result.name }}</td>
                                    <td>{{ result.result }} {{ result.units }}</td>
                                    <td>{{ result.reference }}</td>
                                    <td>
                                        <span :class="{
                                            'text-danger': result.flags === 'High',
                                            'text-success': result.flags === 'Normal',
                                            'text-warning': result.flags === 'Low'
                                        }">
                                            {{ result.flags }}
                                        </span>
                                    </td>
<td>{{ result.time ?? '—' }}</td>
                                    <td>{{ result.location }}</td>
                                    <td>{{ result.code }}</td>
                                    <td>{{ result.doctor?.name || result.doctor?.user?.name || '—' }}</td>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Modal :isOpen="isAddResultModalOpen" title="Add Result" @close="closeAddResultModal" size="lg">
            <AddResult ref="childComponentRef" :doctors="doctors" @close="closeAddResultModal" />
        </Modal>

    </AuthLayout>
</template>
