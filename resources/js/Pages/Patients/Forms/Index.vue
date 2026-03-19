<script setup>
import AuthLayout from '@/Layouts/AuthLayout2.vue';
import { Link, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Common/Modal.vue';
import formsModal from '@/Pages/Modals/AddForms.vue';
import { ref } from 'vue';

const props = defineProps({
    keyword: String,
    forms: Array,
    completdForms: Object,
    formatterForm: Object,
});
 
const form = useForm({
    keyword: '',
});

const Search = () => {
    if (form.keyword == "") {
        toast('Please enter some keyword for search.');
    } else {
        form.get(route('patient.forms'));
    }
}
  
const currentTab = ref('Forms to Fill Out')

const tabs = [
    { key: 'Forms to Fill Out', label: 'Forms to Fill Out', iconClass: 'icon-warning', icon: 'fa-regular fa-clipboard' },
    { key: 'Completed Forms', label: 'Completed Forms', iconClass: 'icon-success', icon: 'fa-solid fa-check-circle' },
]
</script>
<template>
<AuthLayout title="Patient Forms" description="View and complete patient forms" heading="Patient Forms">
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
                    
                </div>
                <div class="iq-card-body mt-3">
                    <div id="table" class="table-responsive">
                        <table class="table table-striped">
                            <tbody v-if="currentTab === 'Forms to Fill Out'">
                                <!-- {{ forms }} -->
                                <template v-for="(formRecord, key) in forms" :key="key">
                                    <tr>
                                        <td class="text-justify text-capitalize">
                                        {{ formRecord?.label  }}
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1 justify-content-end">
                                               <Link v-if="formRecord?.view"  :href="formRecord?.view" 
                                                class="btn btn-primary" data-toggle="tooltip" data-placement="top"
                                                title="View Form">
                                                <i class="fa-regular fa-eye"></i>
                                                </Link> 
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>

                            <tbody>
                                 <tr v-if="currentTab === 'Completed Forms'" v-for="form in completdForms"
                                    :key="form.id">

                                      <td class="text-justify text-capitalize">
                                        <span class="font-weight-bold">{{ form?.date }} - </span>{{ form?.title }}
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-end">
                                            <Link v-if="form?.id"  :href="route('patient.form.completeform', form?.id)" 
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
    box-shadow: 0 4px 12px rgba(0, 0, 0, .04);
    cursor: pointer;
    transition: .2s;
}

.menu-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 18px rgba(0, 0, 0, .06);
}

.menu-item.active {
    border-color: #6f42c1;
    box-shadow: 0 10px 20px rgba(111, 66, 193, .15);
}

.icon {
    height: 10px;
    width: 10px;
    border-radius: 50%;
}

.icon-success {
    color: #28a745;
}

.icon-warning {
    color: #ffc107;
}

.icon-secondary {
    color: #ff7b29;
}

.icon-primary {
    color: #0d6efd;
}

.icon-dark {
    color: #2e2138;
}

.label {
    font-size: 14px;
    color: #2b2b2b;
    font-weight: 600;
}
</style>