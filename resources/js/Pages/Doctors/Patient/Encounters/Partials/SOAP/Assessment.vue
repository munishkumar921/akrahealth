<script setup>
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import { ref, defineEmits } from "vue";
import axios from "axios";
import Swal from 'sweetalert2/dist/sweetalert2.js';
import Modal from "@/Components/Common/Modal.vue";
import { useForm } from "@inertiajs/vue3";
import Search from "@/Components/Common/Search.vue";
import { route } from "ziggy-js";
const props = defineProps({
    form: Object,
    data: Object,
});

const fields = [
    { key: "assessment_other", type: "textarea", placeholder: "Additional Diagnoses" },
    { key: "differential_diagnoses", type: "textarea", placeholder: "Differential Diagnoses" },
    { key: "assessment_discussion", type: "textarea", placeholder: "Assessment Discussion" },
];

const isValidated = ref(false);
const searchResult = ref([]);
const loader = ref(false);
const searchQuery = ref("");
const icd10 = ref([]);
const search = () => {

    loader.value = true;
    const form = new FormData();
    form.append("search_icd", searchQuery.value);
    axios.post(route('doctor.icd.search', 1), form).then(response => {
        icd10.value = response.data.message;
        loader.value = false;
    });
};

const emit = defineEmits();
const getDateMeta = (key) => {

    let keyword = null;
    if (key == 'assessment_other') {
        keyword = 'assessment_other';
    } else if (key == 'differential_diagnoses') {
        keyword = 'assessment_ddx';
    } else if (key == 'assessment_discussion') {
        keyword = 'assessment_notes';
    }
    if (!keyword) {
        return;
    }
    const form = new FormData();
    form.append('id', keyword);
    axios.post(route('doctor.get.templates'), form)
        .then(response => {
            response.category = keyword;
            response.field = key;
            emit('templateData', response);
        });
}

const icdCodes = ref(props.data.icdCodes);
const selectICD = (row) => {
    loader.value = true;
    axios.get(row.href + '/' + props.form.id).then(response => {
        icd10.value = [];
        toast('Assessments updated!', 'success');
        loader.value = false;
        icdCodes.value = response.data;
    });
}

const deleteICD = (id) => {
    Swal.fire(confirmSettings('Are you sure to delete this data?', 'You want be get it back')).then((result) => {
        if (result.isConfirmed) {
            axios.get(route('doctor.delete.encounter.assessment', [id, props.form.id])).then(response => {
                toast('Assessments deleted!', 'success');
                icdCodes.value = response.data;
            });
        }
    });
}

const isAssessModalOpen = ref(false);

const editForm = useForm({
    id: "",
    assessment: "",
    icd: "",
    encounter_id: "",
});
const editICD = (code, id) => {

    isAssessModalOpen.value = true;
    editForm.id = id;
    editForm.assessment = code;
    editForm.icd = code;
    editForm.encounter_id = props.form.id;
}

const updateAssessment = () => {

    axios.post(route('doctor.update.encounter.assessment', editForm.id), editForm).then(response => {
        toast('Assessments updated!', 'success');
        icdCodes.value = response.data;
        closeAssessModal();
    });
}

const closeAssessModal = () => {
    isAssessModalOpen.value = false;
}
</script>

<template>
    <div class="col-md-12 mb-4 ">
        <Search v-model="searchQuery" :searchResult="searchResult" :loader="loader" @input="search"
            :placeholder="'Search for Icd10'" />
        <template v-for="row in icd10" v-if="!loader">
            <p class="p-2 border-bottom bg-color-white-lilac cursor-pointer" @click="selectICD(row)">
                {{ row.label }}
            </p>
        </template>
        <template v-else>
            <div class="text-center p-4">
                <span v-if="loader" class="spinner-border spinner-border-sm"></span>
            </div>
        </template>

        <template v-for="(code, i) in icdCodes" :key="i">
            <div v-if="code" class="p-2 border-bottom bg-color-white-lilac cursor-pointer mt-2">
                <div class="row align-items-center">
                    <div class="col-8">
                        <p class="mb-0">{{ code }}</p>
                    </div>
                    <div class="col-4 text-end">
                        <button class="btn btn-success mr-2" type="button" title="Edit" @click="editICD(code, i)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-danger" type="button" title="Delete" @click="deleteICD(i)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <div class="search-result" v-if="searchResult.length > 0">
            <div class="search-result-list">
                <div v-for="(item, index) in searchResult" :key="index" class="search-result-item">
                    <div class="search-result-item-title">
                        <span @click="handleClick(item)" class="pointer">{{ item.label }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form novalidate class="needs-validation" :class="{ 'was-validated': isValidated }">
        <div v-for="field in fields" :key="field.key">
            <BaseInput v-model="form[field.key]" :name="field.key" :placeholder="field.placeholder" :type="field.type"
                :label="field.placeholder" required @click="getDateMeta(field.key)" />
        </div>
    </form>

    <Modal :isOpen="isAssessModalOpen" title="Template Toolbox" @close="closeAssessModal" size="lg">
        <div>
            <div class="mb-3">
                <label for="template-type" class="form-label">Assessment</label>
                <input type="text" v-model="editForm.assessment" class="form-control" placeholder="Assessment" />
            </div>

            <div class="mb-3">
                <label for="template-type" class="form-label">ICD Code</label>
                <input type="text" v-model="editForm.icd" class="form-control" placeholder="ICD Code" />
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-3">
            <button class="btn btn-sm btn-primary" type="button" @click="updateAssessment()">Update</button>
            <button class="btn btn-sm btn-danger" type="button" @click="closeAssessModal">Cancel</button>
        </div>
    </Modal>
</template>
