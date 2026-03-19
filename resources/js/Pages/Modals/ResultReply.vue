<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    tests: Array,
});

const emit = defineEmits(["submit", "close"]);
const form = useForm({
    testsPerformed: "",
    message: "",
    followup: "",
    actionAfterSaving: "Send Message to Portal"
});
const submit = () => {
    form.post(route("doctor.results.reply"), {
        onSuccess: () => {
            closeModal();
        },
    });
};
const closeModal = () => {
    form.reset();
    emit("close");
};

</script>
<template>

    <form @submit.prevent="submit">
        <div class="row">
            <div class="col">
                <label>Tests Performed</label>
                <select class="form-control" v-model="form.testsPerformed">
                    <option value="">Select Test</option>
                    <option v-for="test in tests" :key="test.id" :value="test.id">
                        {{ test.name }}
                    </option>
                </select>
                <InputError class="mt-2" :message="form.errors.testsPerformed" />
            </div>
        </div>
        <div class="row mt-3">
            <div class="col">
                <label>Message to Patient</label>
                <textarea v-model="form.message" class="form-control" rows="4"
                    placeholder="Enter your message to the patient..." required></textarea>
                <InputError class="mt-2" :message="form.errors.message" />
            </div>
        </div>

        <div class="row mt-3">
            <div class="col">
                <label>Followup</label>
                <input v-model="form.followup" type="text" class="form-control"
                    placeholder="Enter followup instructions..." required />
                <InputError class="mt-2" :message="form.errors.followup" />
            </div>
        </div>

        <div class="row mt-3">
            <div class="col">
                <label>Action after Saving</label>
                <select class="form-control" v-model="form.actionAfterSaving">
                    <option value="Send Message to Portal">Send Message to Portal</option>
                    <option value="Send Letter">Send Letter</option>
                </select>
                <InputError class="mt-2" :message="form.errors.actionAfterSaving" />
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
</template>