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
    skills: [],
    language: [],
    row: {}

});
const form = useForm({
    slug: '',
    skills: [],
    first_name: '',
    last_name: '',
    mobile: '',
    sex: '',
    description: '',
    profile_photo: '',
    address: '',
    user_id: '',
    language: [],
});

const onChangeFileUpload = (event) => {
    form.profile_photo = event.target.files[0];
    // form.url = URL.createObjectURL(form.profile_photo);
};

const submit = () => {
    form.post(route('virtualAssistant.update'), {
        onSuccess: () => {
            window.location.reload({
                time: 100,
            });
            form.reset();
        },
        onError: () => { }
    });
};
const update = (assistant) => {
    form.slug = assistant?.slug;
    form.user_id = assistant?.id;
    form.first_name = assistant?.first_name;
    form.last_name = assistant?.last_name;
    form.sex = assistant?.sex;
    form.mobile = assistant?.mobile;
    form.description = assistant?.description;
    form.address = assistant?.address?.address_1;
    form.skills = props?.AssistantSkills;
    form.language = assistant?.language;
    form.profile_photo = assistant?.profile_photo_path;

};

defineExpose({
    update
})

const multiValue = ref([]);
const updateMultiValue = (value) => {
    multiValue.value = value;
};
</script>
<template>
    <section>
        <div class="modal fade" id="profile-img-edit" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                <div class="modal-content">
                    <form @submit.prevent="submit">
                        <div class="modal-header bg-white mt-0">
                            <h5 class="modal-title" id="exampleModalCenterTitle">Update Profile image</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <div class="col-md-10 ml-auto mt-0 profile-img">
                                <div class="rounded-circle bg-light-gradient" v-if="assistant?.profile_photo_path">
                                    <img :src="assistant?.profile_photo_path" class="avatar-130 img-fluid" align="left"
                                        alt="profile_photo_path" />
                                </div>
                                <div class="bg-light-gradient rounded-circle" v-else>
                                    <img v-if="assistant?.sex === 'Male'" src="/images/doctor_m_avtar.svg"
                                        alt="profile-img" class="avatar-130 img-fluid" />
                                    <img v-if="assistant?.sex == 'Female'" src="/images/doctor_f_avtar.svg"
                                        alt="profile-img" class="avatar-130 img-fluid" />
                                    <img v-if="assistant?.sex === 'Other'" src="/images/doctor_m_avtar.svg"
                                        alt="profile-img" class="avatar-130 img-fluid" />
                                    <img v-if="assistant?.sex === 'Other'" src="/images/doctor_m_avtar.svg"
                                        alt="profile-img" class="avatar-130 img-fluid" />
                                </div>

                            </div>
                            <div class="col-md-12 text-center">

                                <div class="form-group">
                                    <InputLabel for="Upload profile picture" value="Upload profile picture" />

                                    <div class="custom-file">
                                        <TextInput type="file" class="custom-file-input" id="inputFileUpload"
                                            @change="onChangeFileUpload($event)" accept="image/*" />
                                        <label class="custom-file-label text-left border-radius-8 h-45  pointer"
                                            for="inputFileUpload">
                                            <spam v-if="form.profile_photo.name">{{ form.profile_photo.name }}</spam>
                                            <spam v-else-if="form.profile_photo" class="">{{ form.profile_photo }}
                                            </spam>
                                            <spam v-else>choose picture</spam>
                                        </label>
                                        <InputError class="mt-2" :message="form.errors.profile_photo" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade" id="name-edit" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form @submit.prevent="submit">
                        <div>
                            <div class="modal-header bg-white mt-0">
                                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Name</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="mt-3">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <InputLabel for="first_name" value="First Name*" class=" after:ml-0.5" />
                                        <input id="first_name" type="text" v-model="form.first_name"
                                            class="mt-1 form-control" required autofocus autocomplete="first_name"
                                            placeholder="First Name" />
                                        <InputError class="mt-2" :message="form.errors.first_name" />
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <InputLabel for="last_name" value="Last Name" />
                                        <input id="last_name" type="text" v-model="form.last_name"
                                            class="mt-1 form-control" required autofocus placeholder="Last Name" />
                                        <InputError class="mt-2" :message="form.errors.last_name" />
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="gender"
                                            class="after:ml-0.5 after:text-red-500 block  font-medium text-slate-700 me-2">
                                            Gender*:
                                        </label>
                                        <input class="peer/draft" type="radio" name="sex" id="sex" v-model="form.sex"
                                            required value="Male" />
                                        <span class="ml-2 text-sm text-gray-600">Male</span>
                                        <input class="peer/draft ml-2" type="radio" name="sex" id="sex"
                                            v-model="form.sex" value="Female" />
                                        <span class="ml-2 text-sm text-gray-600">Female</span>
                                        <input class="peer/draft ml-2" type="radio" name="sex" id="sex"
                                            v-model="form.sex" value="Other" />
                                        <span class="ml-2 text-sm text-gray-600">Other</span>
                                        <InputError class="mt-2" :message="form.errors.sex" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade" id="edit-mobile" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form @submit.prevent="submit">
                        <div>
                            <div class="modal-header bg-white mt-0">
                                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Contact </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="mt-3">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <InputLabel for="mobile" value="Mobile *" class=" after:ml-0.5" />
                                        <input id="mobile" type="text" v-model="form.mobile" class="mt-1 form-control"
                                            required autofocus autocomplete="Mobile" placeholder="Mobile Number" />
                                        <InputError class="mt-2" :message="form.errors.mobile" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">

                            <button type="submit" class="btn btn-primary">submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade" id="edit-address" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form @submit.prevent="submit">
                        <div>
                            <div class="modal-header bg-white mt-0">
                                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Contact </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="mt-3">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <InputLabel for="address" value="Address" />
                                        <input id="address" v-model="form.address" type="text" class="mt-1 form-control"
                                            required autocomplete="address"
                                            placeholder="Street1, Street2, City, State, Zip_Code" />
                                        <InputError class="mt-2" :message="form.errors.address" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">

                            <button type="submit" class="btn btn-primary">submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade" id="edit-about" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form @submit.prevent="submit">
                        <div>
                            <div class="modal-header bg-white mt-0">
                                <h5 class="modal-title" id="exampleModalCenterTitle">Edit About Me </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="mt-3">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <InputLabel for="mobile" value="Description*" class=" after:ml-0.5" />
                                        <textarea id="description" rows="" cols="" v-model="form.description"
                                            class="mt-1 form-control" required autofocus
                                            placeholder="Description"></textarea>
                                        <InputError class="mt-2" :message="form.errors.description" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">

                            <button type="submit" class="btn btn-primary">submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade" id="edit-skills" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form @submit.prevent="submit">
                        <div>
                            <div class="modal-header bg-white mt-0">
                                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Skills</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="mt-3">
                                <div class="col-md-12 skills">
                                    <div class="form-group">
                                        <InputLabel for="first_name" value="Skills" class=" after:ml-0.5" />
                                        <VueMultiselect class="mb-3 mt-1" v-model="form.skills" :options="skills"
                                            :hide-selected="false" :clear-on-select="false" :close-on-select="false"
                                            :selected="multiValue" :multiple="true" :limit="10" :searchable="true"
                                            @update="updateMultiValue" placeholder="Select  Skills" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">

                            <button type="submit" class="btn btn-primary">submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade" id="edit-language" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form @submit.prevent="submit">
                        <div>
                            <div class="modal-header bg-white mt-0">
                                <h5 class="modal-title" id="exampleModalCenterTitle">Edit language</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="col-md-12 mt-3">
                                <div class="form-group">
                                    <InputLabel for="language" value="language" class=" after:ml-0.5" />
                                    <VueMultiselect class=" mt-1" v-model="form.language" :options="language"
                                        :hide-selected="false" :clear-on-select="false" :close-on-select="false"
                                        :selected="multiValue" :multiple="true" :limit="10" :searchable="true"
                                        @update="updateMultiValue" placeholder="Select  language" />
                                </div>

                            </div>
                        </div>
                        <div class="modal-footer">

                            <button type="submit" class="btn btn-primary">submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade" id="edit-resume" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form @submit.prevent="submit">
                        <div>
                            <div class="modal-header bg-white mt-0">
                                <h5 class="modal-title" id="exampleModalCenterTitle">Edit Resume</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="col-md-12 mt-3">
                                <div class="form__field">
                                    <div class=" text-center bg-white border-2 border-dashed relative rounded w-full">
                                        <input type="file" class="absolute cursor-pointer h-full opacity-0 w-full"
                                            accept="application/pdf,image/*"><i
                                            class="fa fa-4x fa-cloud-upload mt-5 text-center w-full text-theme-16"></i>
                                        <p class="font-semibold text-center text-gray-900">Drop files here or click to
                                            upload.</p>
                                        <p class="mb-5 text-blue-700 text-center text-sm">Drag the agreement file here
                                            or
                                            just upload it by clicking here.</p>
                                    </div>
                                    <div class="__file_preview flex p-2"></div>
                                </div>

                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</template>