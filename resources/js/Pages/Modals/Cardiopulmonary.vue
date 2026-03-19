<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import InputError from "@/Components/InputError.vue";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import BaseDatePicker from "@/Components/Common/Input/BaseDatePicker.vue";

const props = defineProps({
    row: {},
    insurances: Object,
    states:Object
});

const form = useForm({
    facility: "",
    address: "",
    city: "",
    state: "",
    phone: "",
    pin_code: "",
    email: "",
    comment: "",
    ordering_id: "",
    electronic_order: "",
});

 
const closeModal = () => {
     form.reset();
    emit("close");
};

const submit = () => {
    form.post(route("doctor.configure.addressBook.store"), {
        onSuccess: () => {
            closeModal();
        },
    });
};
const emit = defineEmits(["close", "submit"]);

 </script>
<template>

    <form @submit.prevent="submit">

        <div class="row mt-4">
            <div class="col">
                <BaseInput type="text" label="Facility" v-model="form.facility" />
                <InputError class="mt-2" :message="form.errors.facility" />
            </div>
        </div>
        <div class="row mt-3">
            <div class="col">
                <BaseInput v-model="form.address" label="Adress" placeholder="city" required />
                <InputError class="mt-2" :message="form.errors.address" />
            </div>
            <div class="col">
                <BaseInput v-model="form.city" label="City" placeholder="City" required />

                <InputError class="mt-2" :message="form.errors.city" />
            </div>
        </div>

        <div class="row mt-3">

            <div class="col">
                <BaseSelect id="message_alert" label="State" v-model="form.state">
                    <option v-for="state in states" :key="state.id" :value="state.name">
                        {{ state.name }}
                    </option>
                </BaseSelect>
                <InputError class="mt-2" :message="form.errors.state" />
            </div>
            <div class="col">
                <BaseInput v-model="form.pin_code" label="Pin Code"  type="text"/>
                <InputError class="mt-2" :message="form.errors.pin_code" />
            </div>
        </div>

        <div class="row mt-3">
            <div class="col">
                <BaseInput v-model="form.phone" type="text" label="Phone" />
                <InputError class="mt-2" :message="form.errors.phone" />
            </div>
            <div class="col">
                <BaseInput v-model="form.email" type="email" label="Email" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>
        </div>
        <div class="row mt-3">
            <div class="col">
                <BaseInput v-model="form.comment" label="Comment" />
                <InputError class="mt-2" :message="form.errors.comment" />
            </div>
            <div class="col">
                <BaseInput v-model="form.ordering_id" label="Provider/Clinic Identity" />
                <InputError class="mt-2" :message="form.errors.ordering_id" />
            </div>
        </div>

        <div class="row mt-3">
            <div class="col">
                <label>Electronic Order Interface</label>
                <BaseSelect id="message_alert" v-model="form.electronic_order">
                    <option value="PeaceHealth">PeaceHealth Labs</option>
                </BaseSelect>
            </div>
        </div>
        <div class="gap-1 d-flex justify-content-end mt-3">

            <button type="button" class="btn btn-danger" @click="closeModal">Close</button>
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>

</template>

