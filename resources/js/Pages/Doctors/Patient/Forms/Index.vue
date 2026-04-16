<script setup>
import AuthLayout from '@/Layouts/AuthLayout2.vue';
import { Link, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Common/Modal.vue';
import formsModal from '@/Pages/Modals/AddForms.vue';
import { ref } from 'vue';

const props = defineProps({
    keyword: String,
    forms: Array,
    completedForms: Object,
    formatterForm: Object,
});

const form = useForm({
    keyword: '',
});

const Search = () => {
    if (form.keyword == "") {
        toast('Please enter some keyword for search.');
    } else {
        form.get(route('doctor.forms.index'));
    }
}


const childComponentRef = ref();
const isAddFormModalOpen = ref(false);

const openAddFormModal = () => {
    isAddFormModalOpen.value = true;
}
const closeAddAddModal = () => {
    isAddFormModalOpen.value = false;

}

const currentTab = ref('Forms to Fill Out')

const tabs = [
    { key: 'Forms to Fill Out', label: 'Forms to Fill Out', iconClass: 'icon-warning', icon: 'fa-regular fa-clipboard' },
    { key: 'Completed Forms', label: 'Completed Forms', iconClass: 'icon-success', icon: 'fa-solid fa-check-circle' },
]
</script>
<template>
    <AuthLayout title="Forms" description="View and manage patient forms" heading="Forms">
        <div class="row">
            <div class="col-lg-3">
                <div class="iq-card">
                    <div class="iq-card-body">
                        <div class="finance-menu">
                            <button v-for="tab in tabs" :key="tab.key" type="button" class="menu-item"
                                :class="{ active: currentTab === tab.key }" @click="currentTab = tab.key">
                                <i :class="tab.icon + ' ' + tab.iconClass"></i>
                                <span class="label">{{ tab.label }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card col-sm-9 p-3">
                <div class="align-items-center d-flex justify-content-between">
                    <div class="todo-date d-flex mr-3">
                        <h4 class="card-title">Forms</h4>
                        <div class="iq-search-bar d-none d-md-block">
                            <form class="searchbox">
                                <input type="search" v-model="form.keyword" class="text search-input"
                                    placeholder="Filter...">
                                <div type="button" @click="Search()" class="search-link" href="#">
                                    <i class="ri-search-line"></i>
                                </div>
                            </form>
                        </div>
                    </div>
                    <button class="btn btn-primary ms-2" @click="openAddFormModal">
                        <i class="fa-solid fa-square-plus pointer"></i> Add Form
                    </button>
                </div>
                <div class="iq-card-body mt-3">
                    <div id="table" class="table-responsive">
                        <table class="table table-striped">
                            <tbody v-if="currentTab === 'Forms to Fill Out'">
                                <template v-for="(formRecord, key) in formatterForm" :key="key">
                                    <tr>
                                        <td class="text-justify text-capitalize">
                                            {{ formRecord?.forms_title }}
                                            <!-- {{ formRecord?.forms_title?.replace(/\n/g, '') }} -->
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1 justify-content-end">
                                                <Link v-if="forms?.id && formRecord?.forms_title"
                                                    :href="route('doctor.form.show', [forms?.id, formRecord?.forms_title])"
                                                    class="btn btn-primary" data-toggle="tooltip" data-placement="top"
                                                    title="View">
                                                    <i class="fa-regular fa-eye"></i>
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>

                            <tbody>
                                <tr v-if="currentTab === 'Completed Forms'" v-for="form in completedForms"
                                    :key="form.id">

                                    <td class="text-justify text-capitalize">
                                        <span class="font-weight-bold">{{ form?.date }} - </span>{{ form?.title }}
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-end">
                                            <Link :href="route('doctor.form.completeform', form?.id)"
                                                class="btn btn-primary" data-toggle="tooltip" data-placement="top"
                                                title="View Completed Form">
                                                <i class="fa-regular fa-eye"></i>
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <Modal :isOpen="isAddFormModalOpen" title="Add Result" @close="closeAddAddModal" size="xl">
            <formsModal ref='childComponentRef' :route="route" @close="closeAddAddModal" />
        </Modal>
    </AuthLayout>
</template>

<style scoped>
.icon {
    height: 10px;
    width: 10px;
    border-radius: 50%;
}
</style>