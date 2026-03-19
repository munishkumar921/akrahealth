<script setup>
import { router, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import AuthLayout2 from "@/Layouts/AuthLayout2.vue";
import Table from "@/Components/Table/Table.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import axios from "axios";

import Tab from "./Tab.vue";

const props = defineProps({
    data: Object,
  });
const currentTab = ref("custom_report_by_procedure_code");
const isValidated = ref(false);

 
const form = useForm({
    variables:[],
    year:[],
    type:'cpt',
    option:'',

});

const submit = () => {
    form.post(route('doctor.finance.financial_queue'));
};
const print = () => {
    form.option = 'print';

axios.post(route('doctor.finance.financial_queue'), form)
.then(response => {
    form.option = '';
    axios.post(route('doctor.finance.download'),{data:response.data}, {
        responseType: 'blob', // Important for file downloads
    }).then(downloadResponse => {
        // Create a URL for the blob
        const url = window.URL.createObjectURL(new Blob([downloadResponse.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'financial-report.pdf'); // Or a dynamic name
        document.body.appendChild(link);
        link.click();
        link.remove();
        form.option = '';

    });
})
.catch(error => console.error("Error generating or downloading report:", error));
};
</script>

<template>
<AuthLayout2 title="Custom Procedure Code Report" description="Generate custom procedure code reports" heading="Custom Procedure Code Report">
        <div class="row">
        <Tab :currentTab="currentTab"/>
            <div class="col-lg-9 iq-card">
                <div class="iq-card-body mt-4">
                     <form @submit.prevent="submit" novalidate class="needs-validation" :class="{ 'was-validated': isValidated }">
                        <div class=" justify-content-center">
                            <div class="col-md-5 mb-3">
                                <label class="form-label">Variables (Code Types)</label>
                                  <BaseSelect v-model="form.variables"  placeholder="Select one or more variables" multiple :required="true">
                                    <option v-for="variable in data.variables" :key="variable" :value="variable">
                                        {{ variable }}
                                    </option>
                                </BaseSelect>
                                <div v-if="form.errors.variables" class="text-danger">{{ form.errors.variables }}</div>
                            </div>
                            <div class="col-md-5 mb-3">
                                <label class="form-label">Year</label>
                                <BaseSelect v-model="form.year" placeholder="Select Year" :required="true" multiple>
                                    <option v-for="year_option in data.years" :key="year_option" :value="year_option">
                                        {{ year_option }}
                                    </option>
                                </BaseSelect>
                                <div v-if="form.errors.year" class="text-danger">{{ form.errors.year }}</div>
                            </div>
                            
                        </div>
                        <div class="d-flex justify-content-center gap-2 mt-2 border-t">
                                <button type="submit" class="btn btn-primary ">Run Report</button>
                                 <button type="button" @click=print() class="btn btn-primary">Print Report</button>

                             </div>
                        
                    </form>
                </div>
            </div>
            </div>
    </AuthLayout2>
</template>
