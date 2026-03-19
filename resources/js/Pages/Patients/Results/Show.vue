<script setup>
import { ref, } from 'vue';
import AuthLayout from "@/Layouts/AuthLayout2.vue";
import {router } from '@inertiajs/vue3';
 

import { defineProps } from "vue";

const props = defineProps({
    result: Object,
   
});
 
const goBack = () => {
    router.get(route('patient.results'));
 };
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
                        <button class="btn btn-primary" @click="goBack()">
                            <i class="fa fa-arrow-left"></i> Back
                        </button>
                        
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
                                    <td>{{ result.time }}</td>
                                    <td>{{ result.location }}</td>
                                    <td>{{ result.code }}</td>
                                    <td>{{ result.doctor?.name||result.doctor?.user?.name || '—' }}</td>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
         
    </AuthLayout>
</template>
