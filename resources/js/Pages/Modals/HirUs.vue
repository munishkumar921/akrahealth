<script setup>
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import VueMultiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import { ref } from 'vue';

const props = defineProps({
    slug: String,
    assistant: String,
    AssistantSkills: [],
    row: {}

});
const form = useForm({
    slug: props.slug,
    skills: [],
    message: '',
});
const submit = () => {
    form.post(route('user.hir_us'), {
        onSuccess: () => {
            $('.close').click();
            form.reset();
        },
    });
};

const multiValue = ref([]);
const updateMultiValue = (value) => {
    multiValue.value = value;
};
</script>
<template>
    <section>
        <div class="modal  mt-4 show container container-wide " id="hir_us" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalLabel" aria-hidden="true">

            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title text-white " id="exampleModalLabel">Hire me</h3>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-6">
                                    <img v-if="assistant?.profile_photo_path" :src="assistant?.profile_photo_path"
                                        class=" w-60 m-5 img-fluid" align="left" alt="profile_photo_path" />
                                    <div v-else>
                                        <img v-if="assistant?.sex === 'Male'" src="/images/assistant_m_avtar.svg"
                                            alt="profile-img" class=" w-60  m-5 img-fluid" />
                                        <img v-else src="/images/assistant_f_avtar.svg" alt="profile-img"
                                            class=" w-60 m-5 img-fluid" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <form @submit.prevent="submit">
                                        <div class="form-group">
                                            <InputLabel class="col-form-label" value="Name" />
                                            <span>*</span>

                                            <input type="text" :value="$page.props.auth.user?.name" readonly
                                                class="form-control" placeholder="name" required>
                                            <InputError class="mt-2" :message="form.errors.name" />
                                        </div>
                                        <div class="form-group">
                                            <InputLabel class="col-form-label" value="Skills" />
                                            <VueMultiselect class="mb-3 " v-model="form.skills"
                                                :options="AssistantSkills" :hide-selected="false"
                                                :clear-on-select="false" :close-on-select="false" :selected="multiValue"
                                                :multiple="true" :limit="10" :searchable="true"
                                                @update="updateMultiValue" placeholder="Select  Skills" />
                                            <InputError class="mt-2" :message="form.errors.skills" />
                                        </div>
                                        <div class="form-group">
                                            <InputLabel class="col-form-label" value="Message" />
                                            <span class="mt-1">*</span>
                                            <textarea v-model="form.message" class="form-control" placeholder="message"
                                                required></textarea>
                                            <InputError class="mt-2" :message="form.errors.message" />
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary">submit</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>