<script setup>
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const form = useForm({
    slug: '',

});
const submit = () => {
    form.post(route('doctor.immunizations'), {
        onSuccess: () => {
            $('.close-modal').click();
            form.reset();
        },
    });
};
</script>
<template>
    <div class="modal fade bd-immunization-note-modal-lg" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Immunization Note</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form @submit.prevent="submit">
                    <div class="modal-body">

                        <div class="iq-card-body">

                            <div class="row ">
                                <div class="col-8 mb-2">
                                    <label>Immunization Note</label>
                                   
                                    <textarea v-model="form.note" class="form-control" type="text" 
                                        placeholder="note" required ></textarea>
                                    <InputError class="mt-2" :message="form.errors.note" />
                                </div>

                            </div>
                            <div class="gap-1 d-flex justify-content-end mt-3">
                     <button type="submit" class="btn btn-primary">Save Immunization</button>
                     <button type="button" class="btn btn-danger" @click="closeModal">
                Close
            </button>
                </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>