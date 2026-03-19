<script setup>
import { useForm } from '@inertiajs/vue3';
import InputError from "@/Components/InputError.vue";
import Modal from '@/Components/Common/Modal.vue';
import Lab from '@/Pages/Modals/AddLab.vue';

import { ref } from 'vue';
import Search from "@/Components/Common/Search.vue";
import DatePicker from '@/Components/Common/Input/BaseDatePicker.vue';
import axios from "axios";

const props = defineProps({
    data: Object,
});

const showModal = ref(false);
const showLabProviderModal = ref(false);

const form = useForm({
    slug: "",
    test: [],
    diagnosis_codes: "",
    laboratory_provider: "",
    pending_date: "",
    insurance: "",
    notes: "",
});

const searchForm = ref({ keyword: '' });
const searchQuery = ref("");
const searchResult = ref([]);
const loader = ref(false);
const labProviders = ref([]);

const search = () => {
    loader.value=true;
    
     axios.get(route('search', { search: searchQuery.value,  })).then((response) => {
        searchResult.value = response.data;
        loader.value=false;
    }).catch((error) => {
            loader.value=false;
            console.error('Error in search request:', error);
        });
};
const close = () => {
    searchQuery.value = '';
    searchResult.value = [];
    // form.reset();
};
const clearSearch = () => {
    searchForm.value.keyword = '';
    searchResult.value = [];
};

// Define a function to handle click events
const handleClick = (item) => {
    clearSearch();
    form.test = [
        ...form.test,
        ...item
            .replace(/<br\s*\/>?/gi, '\n')
            .split(/[\n,]+/)
            .map((value) => value.trim())
            .filter((value) => value !== '')
    ];
};

const submit = () => {
    form.post(route("doctor.orders.store"), {
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    });
};

// Lab Provider Modal Functions
const openLabProviderModal = () => {
    showLabProviderModal.value = true;
};

const closeLabProviderModal = () => {
    showLabProviderModal.value = false;
    labProviderForm.reset();
};

const newLabProvider = () => {
    openLabProviderModal();
};
const openModal = () => {
    showModal.value = true;
};
const closeModal = () => {
    showModal.value = false;
    form.reset();
};

// expose to parent
defineExpose({ openModal, closeModal });
</script>

<template>
    <form @submit.prevent="submit" novalidate class="needs-validation" :class="{ 'was-validated': isValidated }">

        <div class=" mb-4">
            <Search v-model="searchQuery" :searchResult="searchResult" :loader="loader" @input="search"
                :placeholder="'Search for Login'" />

            <div v-if="searchResult.length > 0" class="search-result">
                <div class="search-result-list">
                    <div v-for="(item, index) in searchResult" :key="index" class="search-result-item">
                        <div class="search-result-item-title">
                            <span @click="handleClick(item.label)" class="pointer">{{ item.label }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="iq-card-body">
            <div class="row">
                <div class="col">
                    <label>Lab Test(s)</label>
                    <textarea rows="3" class="form-control" v-model="form.test"></textarea>
                    <InputError class="mt-2" :message="form.errors.test" />
                </div>
            </div>
            <div class="row mt-2">
                <div class="col">
                    <label>Diagnosis Codes</label>
                    <input v-model="form.diagnosis_codes" type="text" class="form-control"
                        placeholder="Type a few words" required />
                    <InputError class="mt-2" :message="form.errors.diagnosis_codes" />
                </div>
                <div class="col">
                    <label>Laboratory Provider</label>
                    <select class="form-control" id="message_alert" v-model="form.laboratory_provider">
                        <option value="">Select Lab Provider</option>
                        <option v-for="provider in labProviders" :key="provider.id" :value="provider.id">
                            {{ provider.name }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.laboratory_provider" />
                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" @click="newLabProvider">
                        <i class="fa fa-plus me-1"></i>Add New Lab Provider
                    </button>
                </div>

            </div>

            <div class="row mt-3">
                <div class="col">
                    <label>Order Pending Date</label>
                    <DatePicker v-model="form.pending_date" type="date"  required />
                    <InputError class="mt-2" :message="form.errors.pending_date" />
                </div>

                <div class="col">
                    <label>Insurance</label>
                    <select class="form-control" id="message_alert" v-model="form.insurance">
                        <option value="client">
                            Bill Client
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.insurance" />
                </div>
            </div>

            <div class="row mt-3">
                <div class="col">
                    <label>Notes about Order</label>
                    <textarea rows="3" class="form-control" v-model="form.notes"></textarea>
                    <InputError class="mt-2" :message="form.errors.notes" />
                </div>
            </div>

            <div class="row mt-3">
                <div class="col">
                    <label>Action after Saving</label>
                    <select class="form-control" id="message_alert">
                        <option value="save">Save Only</option>
                        <option value="print">Print</option>
                        <option value="queue">
                            Add to Print Queue
                        </option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
             <button type="submit" class="btn btn-primary">
                Save
            </button>
            <button type="button" class="btn btn-danger" @click="closeModal">
                Close
            </button>
        </div>
    </form>
          <Modal :isOpen="showLabProviderModal" title="Add Laboratory Entry" @close="closeLabProviderModal" size="lg">
        <Lab :specialities="data.specialties" @close="closeLabProviderModal" />
    </Modal>
</template>
