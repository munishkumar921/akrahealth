<script setup>
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

const props = defineProps({
    type:String,
    keyword:String,
    row: {}
   
});
const form = useForm({
    keyword:'',
    shortBy:'',
});

const assistantSearch = () => {
    if(form.keyword == ""){
        Swal.fire({
            position: 'top-end',
            icon:'error',
            title: 'Please enter some keyword for search.',
            timer: 2000,
            showConfirmButton: false,
        });

    }else{
        form.get(route('virtualAssistant.index'));
    }
    
};
const doctorSearch = () => {
    if(form.keyword == ""){
        Swal.fire({
            position: 'top-end',
            icon:'error',
            title: 'Please enter some keyword for search.',
            timer: 2000,
            showConfirmButton: false,
        });
    }else{
    form.get(route('doctors.index'));   
    }
};

const labSearch = () => {
    if(form.keyword == ""){
        Swal.fire({
            position: 'top-end',
            icon:'error',
            title: 'Please enter some keyword for search.',
            timer: 2000,
            showConfirmButton: false,
        });

    }else{
        form.get(route('laboratory.index'));
    }      
};
const pharmacySearch = () => {
    if(form.keyword == ""){
        Swal.fire({
            position: 'top-end',
            icon:'error',
            title: 'Please enter some keyword for search.',
            timer: 2000,
            showConfirmButton: false,
        });

    }else{
        form.get(route('pharmacy.index'));
    }   
};
</script>
<template>
    <div class="col-md-12">
        <div class="iq-card">
            <div class="d-md-flex align-items-center justify-content-between pl-2 pr-3 p-2">
                <div class="iq-algolia-search w-40">
                    <div id="" class="ais-SearchBox">
                        <div class="ais-SearchBox">
                            <div v-if="props.type == 'doctor'" class="ais-SearchBox-form">
                            <input v-model="form.keyword"   class="ais-SearchBox-input" type="search" placeholder="Search for Name"
                                    autocomplete="off" autocorrect="off" autocapitalize="off" maxlength="512">   
                                    <button @click="doctorSearch()" class="ais-SearchBox-submit" type="submit" title="Submit the search query."><svg
                                        class="ais-SearchBox-submitIcon" xmlns="http://www.w3.org/2000/svg" width="10"
                                        height="10" viewBox="0 0 40 40">
                                        <path
                                            d="M26.804 29.01c-2.832 2.34-6.465 3.746-10.426 3.746C7.333 32.756 0 25.424 0 16.378 0 7.333 7.333 0 16.378 0c9.046 0 16.378 7.333 16.378 16.378 0 3.96-1.406 7.594-3.746 10.426l10.534 10.534c.607.607.61 1.59-.004 2.202-.61.61-1.597.61-2.202.004L26.804 29.01zm-10.426.627c7.323 0 13.26-5.936 13.26-13.26 0-7.32-5.937-13.257-13.26-13.257C9.056 3.12 3.12 9.056 3.12 16.378c0 7.323 5.936 13.26 13.258 13.26z">
                                        </path>
                                    </svg>
                                    </button>
                                    <Link v-if="$page.props.keyword" :href="route('doctor.index')" class="ais-SearchBox-reset" type="reset"
                                    title="Clear the search query."><svg class="ais-SearchBox-resetIcon"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="10" height="10">
                                        <path
                                            d="M8.114 10L.944 2.83 0 1.885 1.886 0l.943.943L10 8.113l7.17-7.17.944-.943L20 1.886l-.943.943-7.17 7.17 7.17 7.17.943.944L18.114 20l-.943-.943-7.17-7.17-7.17 7.17-.944.943L0 18.114l.943-.943L8.113 10z">
                                        </path>
                                    </svg></Link>
                             </div>
                             <div v-if="props.type == 'assistant'" class="ais-SearchBox-form">
                            <input v-model="form.keyword" class="ais-SearchBox-input" type="search" placeholder="Search for Name"
                                    autocomplete="off" autocorrect="off" autocapitalize="off" maxlength="512">   
                                    <button @click="assistantSearch()" class="ais-SearchBox-submit" type="submit" title="Submit the search query."><svg
                                        class="ais-SearchBox-submitIcon" xmlns="http://www.w3.org/2000/svg" width="10"
                                        height="10" viewBox="0 0 40 40">
                                        <path
                                            d="M26.804 29.01c-2.832 2.34-6.465 3.746-10.426 3.746C7.333 32.756 0 25.424 0 16.378 0 7.333 7.333 0 16.378 0c9.046 0 16.378 7.333 16.378 16.378 0 3.96-1.406 7.594-3.746 10.426l10.534 10.534c.607.607.61 1.59-.004 2.202-.61.61-1.597.61-2.202.004L26.804 29.01zm-10.426.627c7.323 0 13.26-5.936 13.26-13.26 0-7.32-5.937-13.257-13.26-13.257C9.056 3.12 3.12 9.056 3.12 16.378c0 7.323 5.936 13.26 13.258 13.26z">
                                        </path>
                                    </svg>
                                    </button>
                                    <button class="ais-SearchBox-reset" type="reset"
                                    title="Clear the search query." hidden=""><svg class="ais-SearchBox-resetIcon"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="10" height="10">
                                        <path
                                            d="M8.114 10L.944 2.83 0 1.885 1.886 0l.943.943L10 8.113l7.17-7.17.944-.943L20 1.886l-.943.943-7.17 7.17 7.17 7.17.943.944L18.114 20l-.943-.943-7.17-7.17-7.17 7.17-.944.943L0 18.114l.943-.943L8.113 10z">
                                        </path>
                                    </svg></button>
                             </div>
                             <div v-if="props.type == 'lab'" class="ais-SearchBox-form">
                            <input v-model="form.keyword" class="ais-SearchBox-input" type="search" placeholder="Search for Name"
                                    autocomplete="off" autocorrect="off" autocapitalize="off" maxlength="512">   
                                    <button @click="labSearch()" class="ais-SearchBox-submit" type="submit" title="Submit the search query."><svg
                                        class="ais-SearchBox-submitIcon" xmlns="http://www.w3.org/2000/svg" width="10"
                                        height="10" viewBox="0 0 40 40">
                                        <path
                                            d="M26.804 29.01c-2.832 2.34-6.465 3.746-10.426 3.746C7.333 32.756 0 25.424 0 16.378 0 7.333 7.333 0 16.378 0c9.046 0 16.378 7.333 16.378 16.378 0 3.96-1.406 7.594-3.746 10.426l10.534 10.534c.607.607.61 1.59-.004 2.202-.61.61-1.597.61-2.202.004L26.804 29.01zm-10.426.627c7.323 0 13.26-5.936 13.26-13.26 0-7.32-5.937-13.257-13.26-13.257C9.056 3.12 3.12 9.056 3.12 16.378c0 7.323 5.936 13.26 13.258 13.26z">
                                        </path>
                                    </svg>
                                    </button>
                                    <button class="ais-SearchBox-reset" type="reset"
                                    title="Clear the search query." hidden=""><svg class="ais-SearchBox-resetIcon"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="10" height="10">
                                        <path
                                            d="M8.114 10L.944 2.83 0 1.885 1.886 0l.943.943L10 8.113l7.17-7.17.944-.943L20 1.886l-.943.943-7.17 7.17 7.17 7.17.943.944L18.114 20l-.943-.943-7.17-7.17-7.17 7.17-.944.943L0 18.114l.943-.943L8.113 10z">
                                        </path>
                                    </svg></button>
                             </div>
                             <div v-if="props.type == 'pharmacy'" class="ais-SearchBox-form">
                            <input v-model="form.keyword" class="ais-SearchBox-input" type="search" placeholder="Search for Name"
                                    autocomplete="off" autocorrect="off" autocapitalize="off" maxlength="512">   
                                    <button @click="pharmacySearch()" class="ais-SearchBox-submit" type="submit" title="Submit the search query."><svg
                                        class="ais-SearchBox-submitIcon" xmlns="http://www.w3.org/2000/svg" width="10"
                                        height="10" viewBox="0 0 40 40">
                                        <path
                                            d="M26.804 29.01c-2.832 2.34-6.465 3.746-10.426 3.746C7.333 32.756 0 25.424 0 16.378 0 7.333 7.333 0 16.378 0c9.046 0 16.378 7.333 16.378 16.378 0 3.96-1.406 7.594-3.746 10.426l10.534 10.534c.607.607.61 1.59-.004 2.202-.61.61-1.597.61-2.202.004L26.804 29.01zm-10.426.627c7.323 0 13.26-5.936 13.26-13.26 0-7.32-5.937-13.257-13.26-13.257C9.056 3.12 3.12 9.056 3.12 16.378c0 7.323 5.936 13.26 13.258 13.26z">
                                        </path>
                                    </svg>
                                    </button>
                                    <button class="ais-SearchBox-reset" type="reset"
                                    title="Clear the search query." hidden=""><svg class="ais-SearchBox-resetIcon"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="10" height="10">
                                        <path
                                            d="M8.114 10L.944 2.83 0 1.885 1.886 0l.943.943L10 8.113l7.17-7.17.944-.943L20 1.886l-.943.943-7.17 7.17 7.17 7.17.943.944L18.114 20l-.943-.943-7.17-7.17-7.17 7.17-.944.943L0 18.114l.943-.943L8.113 10z">
                                        </path>
                                    </svg></button>
                             </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <form>
                    <div id="sort-by" class="iq-algolia-sort">
                        <div class="ais-SortBy iq-algolia-sort">
                            <select class="ais-SortBy-select"  v-model="form.shortBy">
                                <option  class="ais-SortBy-option">popular</option>
                                <option class="ais-SortBy-option" :value="asc">oldest
                                </option>
                                <option class="ais-SortBy-option">newest
                                </option>
                            </select>
                        </div>
                    </div>
                </form>
                    <div class="d-flex">
                        <button type="button" class="btn mr-2 btn-primary">
                            <i class="ri-grid-fill mr-0"></i>
                        </button>
                        <button type="button" class="btn btn- iq-bg-primary">
                            <i class="ri-list-unordered mr-0"></i>
                        </button>
                    </div>
                </div>
                <div class="d-flex stats-data">
                    <div id="stats">
                        <div class="ais-Stats"><span class="ais-Stats-text">21,469
                                results found in 5ms</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>