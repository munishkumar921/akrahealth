<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import VueMultiselect from 'vue-multiselect';
import DatePicker from '@/Components/Common/Input/BaseDatePicker.vue';
import 'vue-multiselect/dist/vue-multiselect.css';
import axios from 'axios';
import { ref } from 'vue';

const props = defineProps({
    doctors: String,

});
const randomPassword = (length = 10) => {
    const upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const lower = 'abcdefghijklmnopqrstuvwxyz';
    const numbers = '0123456789';
    const all = upper + lower + numbers;

    const result = [
        upper[Math.floor(Math.random() * upper.length)],
        lower[Math.floor(Math.random() * lower.length)],
        numbers[Math.floor(Math.random() * numbers.length)],
    ];

    while (result.length < length) {
        result.push(all[Math.floor(Math.random() * all.length)]);
    }

    return result.sort(() => 0.5 - Math.random()).join('');
};

const password = randomPassword();

const form = useForm({
    role_id: 4,
    first_name: '',
    last_name: '',
    email: '',
    mobile: '',
    dob: '',
    sex: '',
    address: '',
    password: password,
    password_confirmation: password,
    type: 'Patient',
});

const setImage = (event) => {
    form.file = event.target.files[0];
}

const submit = () => {
    form.post(route('signup'), {
        onSuccess: () => {
            $('.close').click();
            location.reload();
        },
    });
};

const multiValue = ref([]);
const updateMultiValue = (value) => {
    multiValue.value = value;
};
</script>
<template>
    <div class="modal fade bd-patient-modal-lg" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Patient</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form @submit.prevent="submit">
                    <div class="modal-body">

                        <div class="iq-card-body">

                            <div class="row">
                                <div class="col">
                                    <InputLabel value="First name" />
                                    <input v-model="form.first_name" type="text" class="form-control"
                                        placeholder="First name" />
                                    <InputError class="mt-2" :message="form.errors.first_name" />
                                </div>
                                <div class="col">
                                    <InputLabel value="Last name" />
                                    <input v-model="form.last_name" type="text" class="form-control"
                                        placeholder="Last name">
                                    <InputError class="mt-2" :message="form.errors.last_name" />
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col">
                                    <label>Email Address</label>
                                    <input v-model="form.email" type="email" class="form-control"
                                        placeholder="Email Address">
                                    <InputError class="mt-2" :message="form.errors.email" />
                                </div>
                                <div class="col">
                                    <label>Mobile</label>
                                    <input v-model="form.mobile" type="text" class="form-control phone-validation"
                                        placeholder="Mobile">
                                    <InputError class="mt-2" :message="form.errors.mobile" />
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col">
                                    <div class="custom-file h-45">
                                        <DatePicker v-model="form.dob" type="date" label="Date of Birth"
                                            placeholder="Date of Birth"/>
                                        <InputError class="mt-2" :message="form.errors.dob" />
                                    </div>
                                </div>
                                <div class="col">
                                    <label>Gender</label>
                                    <select v-model="form.sex" class="form-control mb-3 h-45 border-radius-8">
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.sex" />
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col">
                                    <label>Full Address</label>
                                    <input v-model="form.address" type="text" class="form-control"
                                        placeholder="Full street address">
                                    <InputError class="mt-2" :message="form.errors.address" />
                                </div>
                                <div class="col">
                                    <label>Select Doctor</label>
                                    <VueMultiselect class="mb-3 form-group border-radius-10" v-model="form.doctors"
                                        :options="['test']" :hide-selected="false" :clear-on-select="false"
                                        :close-on-select="false" :selected="multiValue" :multiple="true" :limit="10"
                                        :searchable="true" @update="updateMultiValue" placeholder="Select Doctor" />
                                    <InputError class="mt-2" :message="form.errors.doctors" />
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger close-modal" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Patient</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
