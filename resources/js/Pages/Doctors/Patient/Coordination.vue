<script setup>
import { computed } from 'vue';
import AuthLayout from "@/Layouts/AuthLayout.vue";
import UploadDocument from '../../Modals/Documents/UploadDocument.vue';
import GenerateLetter from '../../Modals/Documents/GenerateLetter.vue';
import Education from '../../Modals/Documents/AddPatientEducation.vue';
import UploadCCD from '../../Modals/Documents/UploadCCD.vue';
import UploadCCR from '../../Modals/Documents/UploadCCR.vue';
import { useForm } from '@inertiajs/vue3';



const props = defineProps({
    releases: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    keyword: '',
});

const normalizedKeyword = computed(() => form.keyword?.toLowerCase()?.trim() || '');

const filteredReleases = computed(() => {
    if (!normalizedKeyword.value) return props.releases;
    return props.releases.filter(item =>
        (item?.text || '').toLowerCase().includes(normalizedKeyword.value) ||
        (item?.date || '').toLowerCase().includes(normalizedKeyword.value)
    );
});

const Search = () => {
    // Client-side filter is reactive via v-model; keep this
    // hook to align with other pages using Search()
    if (!normalizedKeyword.value) {
        toast('Please enter some keyword for search.');
    }
};
</script>
<template>
<AuthLayout title="Coordination of Care" description="Manage patient care coordination and records" heading="Coordination of Care">

        <div class="iq-card">
            <div class="iq-card-header d-flex justify-content-between bg-white border-bottom pt-1 rounded-top">
                <div class="col-sm-12">
                    <div class="align-items-center d-flex justify-content-between">
                        <div class="todo-date d-flex mr-3">
                            <h4 class="card-title">Coordination of Care</h4>
                            <div class="iq-search-bar d-none d-md-block">
                                <form  class="searchbox">
                                    <input type="search" v-model="form.keyword" class="text search-input" placeholder="Filter...">
                                    <div  type="button" @click="Search()" class="search-link" href="#">
                                     <i class="ri-search-line"></i>
                                     </div>
                                </form>
                            </div>
                        </div>
                        <div class="todo-notification d-flex align-items-center">
                            <div class="notification-icon position-relative d-flex align-items-center mr-3"></div>
                            <div class="btn-group ms-2">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa-solid fa-square-plus pointer"></i>
                            </button>
                            <div class="dropdown-menu">
                                <button data-toggle="modal" data-target=".bd-upload-document-modal-lg"  class="dropdown-item pointer">Add Records Release</button>
                                <button data-toggle="modal" data-target=".bd-education-modal-lg"  class="dropdown-item pointer">Add Patient Education</button>
                                <button data-toggle="modal" data-target=".bd-upload-ccd-modal-lg"  class="dropdown-item pointer">Upload Consolidated Clinical Document (C-CDA)</button>
                                <button data-toggle="modal" data-target=".bd-upload-ccr-modal-lg"  class="dropdown-item pointer">Upload Continuity of Care Record (CCR)</button>                                
                            </div>
                             </div>                                                   
                        </div>
                    </div>
                </div>
            </div>
            <!-- <UploadDocument/> -->
            <!-- <GenerateLetter/> -->
            <!-- <Education/> -->
            <!-- <UploadCCD/> -->
            <!-- <UploadCCR/> -->
            <div class="iq-card-body">
                <div id="table" class="table-container">
                    <table class="table table-striped">
                        <tbody>
                            <tr v-for="order in filteredReleases" :key="order.text">
                                <td class="text-justify text-capitalize"><span class="font-weight-bold">{{ order.date }}</span> - {{ order.text }}
                                </td>
                                <td>
                                  <div class="d-flex gap-1 justify-content-end">
                                        <button class="btn btn-primary" data-placement="top" title="Edit" data-toggle="modal" data-target=".bd-supplement-modal-lg">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-danger" data-placement="top" title="Delete" data-toggle="modal" data-target=".bd-supplement-modal-lg">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                  </div> 
                                </td>
                            </tr>
                            <tr v-if="filteredReleases.length === 0">
                                <td colspan="2" class="text-center">No records found</td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
