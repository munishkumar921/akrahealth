<script setup>
import axios from "axios";
import { ref, reactive, defineEmits, onMounted, onBeforeUnmount } from "vue";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import Canvas from "@/Components/Common/Canvas.vue";
import { useForm } from "@inertiajs/vue3";
import Swal from 'sweetalert2/dist/sweetalert2.js';

const props = defineProps({
    form: Object,
});

// Canvas refs
const canvas1Ref = ref(null);
const canvas2Ref = ref(null);

// Create separate form objects for each canvas type
const anatomicalForm = reactive({
    annotate_image: ''
});

const photoForm = reactive({
    annotate_image: ''
});

const fieldsOne = [
    { key: "chief_complaint", type: "text", placeholder: "Chief Complaint" },
    { key: "history", type: "textarea", placeholder: "History of Present Illness" },
    { key: "review", type: "textarea", placeholder: "Review of Systems" },
];

const imageForm = useForm({
    encounter_id: props.form.id,
    name: '',
    url: '',
    description: '',
    type: '',
});

 
const emit = defineEmits();

const getDateMeta = (keyword) => {

    const form = new FormData();
    form.append('id', keyword);
    axios.post(route('doctor.get.templates'), form)
        .then(response => {
            response.category = keyword;
            response.field = keyword;
            emit('templateData', response);
        });
}

const updatedImage = (data) => {
    imageForm.name = data.path;
    imageForm.url = data.path;
}

 

const documents = ref([]);
const photos = ref([]);
const isUploading = ref(false);
const upsert = (type) => {
    imageForm.type = type;
    if(type == 'anatomical'){
        document.getElementById('canvas-1').click();
    }else{
        document.getElementById('canvas-2').click();
    }
    isUploading.value = true;
    setTimeout(() => {

        if (imageForm.description === '' || imageForm.url === '') {
            toast('Please provide both description and an image.', 'error');
            isUploading.value = false;
            return;
        }

        axios.post(route('doctor.upload.document'), imageForm).then(response => {

            if (type == 'anatomical') {
                documents.value = response.data;
                anatomicalForm.annotate_image = '';
            } else {
                photos.value = response.data;
                photoForm.annotate_image = '';
            }

            isUploading.value = false;
            toast('Document uploaded successfully.', 'success');
            imageForm.reset();
            
        });
    }, 5000);
}

const deleteDocument = (id, type) => {
    Swal.fire(confirmSettings('Are you sure to delete this data?', 'You want be get it back')).then((result) => {
        if (result.isConfirmed) {
            axios.post(route('doctor.delete.document', [id, type])).then(response => {
                if (type == 'anatomical') {
                    documents.value = response.data;
                } else {
                    photos.value = response.data;
                }
            });
        }
    });
}

const getDocuments = (type) => {
    axios.get(route('doctor.get.documents', [props.form.id, type])).then(response => {
        if (type == 'anatomical') {
            documents.value = response.data;
        } else {
            photos.value = response.data;
        }
    });
}

const file = ref(null);
const uploading = ref(false);
const selectedPhotoFile = ref(null);
const selectedPhotoName = ref('');
const selectedPhotoPreview = ref('');

const toDisplayUrl = (path) => {
    if (!path) return '';
    return path.startsWith('http') || path.startsWith('/') ? path : `/${path}`;
};

const clearObjectPreview = () => {
    if (selectedPhotoPreview.value?.startsWith('blob:')) {
        URL.revokeObjectURL(selectedPhotoPreview.value);
    }
};

const resetPhotoInputSelection = () => {
    selectedPhotoFile.value = null;
    selectedPhotoName.value = '';
    if (file.value) {
        file.value.value = '';
    }
};

const onPhotoFileChange = (event) => {
    const selectedFile = event.target.files?.[0] || null;
    selectedPhotoFile.value = selectedFile;
    selectedPhotoName.value = selectedFile ? selectedFile.name : '';
    clearObjectPreview();
    selectedPhotoPreview.value = selectedFile && selectedFile.type.startsWith('image/')
        ? URL.createObjectURL(selectedFile)
        : '';
};

const uploadFile = () => {
    const photo = selectedPhotoFile.value || file.value?.files?.[0];
    if (!photo) {
        toast('Please select a file first.');
        return;
    }

    const formData = new FormData();
    formData.append('file', photo);
    formData.append('encounter_id', props.form.id);
    uploading.value = true;

    axios.post(route('doctor.upload.file'), formData).then(response => {
        photoForm.annotate_image = response.data.url;
        clearObjectPreview();
        selectedPhotoPreview.value = toDisplayUrl(response.data.url);
        uploading.value = false;
        resetPhotoInputSelection();
        toast('File uploaded successfully.', 'success');
    }).catch(error => {
        console.error('Upload error:', error);
        const errorMessage = error?.response?.data?.message
            || error?.response?.data?.errors?.file?.[0]
            || 'Failed to upload file. Please try again.';
        toast(errorMessage, 'error');
        uploading.value = false;
    });
};

onBeforeUnmount(() => {
    clearObjectPreview();
});
 
 
const procedureForm = useForm({
    encounter_id: props.form.id,
    type: "",
    cpt: "",
    description: "",
});

const procedures = ref([]);
const saveProcedure = () => {
    if (procedureForm.type === '' || procedureForm.cpt === '' || procedureForm.description === '') {
        toast('Please fill all the fields.', 'error');
        return;
    }

    axios.post(route('doctor.upsert.procedure'), procedureForm).then(response => {
        procedures.value = response.data;
        toast('Procedure saved successfully.', 'success');
        procedureForm.reset();
    });
}

const getProcedures = () => {
    axios.get(route('doctor.get.procedures', props.form.id)).then(response => {
        procedures.value = response.data;
    });
}

const deleteProcedure = (id) => {
    Swal.fire(confirmSettings('Are you sure to delete this data?', 'You want be get it back')).then((result) => {
        if (result.isConfirmed) {
            axios.post(route('doctor.delete.procedure', id)).then(response => {
                procedures.value = response.data;
            });
        }
    });
}

onMounted(() => {
    getDocuments('anatomical');
    getDocuments('photo');
    getProcedures();
});
</script>

<template>
    <div class="accordion accordion-clean" id="anatomical-image-accordion">
        <!-- Add Anatomical Image + Annotate Tools -->
        <div class="card mb-3">
            <div class="card-header bg-primary">
                <div class="card-title" data-toggle="collapse" data-target="#anatomical-image-collapse">
                    <h6 class="d-flex justify-content-between text-white">
                        Add Anatomical Image
                        <i class="fas fa-angle-right angle d-flex justify-content-end"></i>
                    </h6>
                </div>
            </div>
            <div id="anatomical-image-collapse" class="collapse show" data-parent="#anatomical-image-accordion">
                <div class="card-body">


                    <!-- Preset Image Dropdown -->
                    <div class="form-group mb-4">
                        <label for="preset-image" class="form-label">Select a preset image to
                            annotate</label>
                        <select v-model="anatomicalForm.annotate_image" id="preset-image" class="form-control">
                            <option value="">Select...</option>
                            <option value="/assets/images/illustrations/m/ABDOMINAL.JPG">
                                Abdominal - Male</option>
                            <option value="/assets/images/illustrations/m/ABDOMINAL_INFANT.jpg">
                                Abdominal Infant - Male</option>
                            <option value="/assets/images/illustrations/m/ABDOMINAL_TODDLER.jpg">
                                Abdominal Toddler - Male</option>
                            <option value="/assets/images/illustrations/m/ARMS.JPG">
                                Arms - Male</option>
                            <option value="/assets/images/illustrations/m/ARMS_TODDLER.jpg">
                                Arms Toddler - Male</option>
                            <option value="/assets/images/illustrations/m/BACK.JPG">
                                Back - Male</option>
                            <option value="/assets/images/illustrations/m/BACK_PULM.JPG">
                                Back Pulm - Male</option>
                            <option value="/assets/images/illustrations/m/BACK_TODDLER.jpg">
                                Back Toddler - Male</option>
                            <option value="/assets/images/illustrations/m/CARDIO.jpg">
                                Cardio - Male</option>
                            <option value="/assets/images/illustrations/m/CHEST.JPG">
                                Chest - Male</option>
                            <option value="/assets/images/illustrations/m/CHEST_INFANT.jpg">
                                Chest Infant - Male</option>
                            <option value="/assets/images/illustrations/m/CHEST_YOUTH.jpg">
                                Chest Youth - Male</option>
                            <option value="/assets/images/illustrations/m/CONSTITUTION.JPG">
                                Constitution - Male</option>
                            <option value="/assets/images/illustrations/m/CONSTITUTION_INFANT.jpg">
                                Constitution Infant - Male</option>
                            <option value="/assets/images/illustrations/m/CONSTITUTION_TODDLER.jpg">
                                Constitution Toddler - Male</option>
                            <option value="/assets/images/illustrations/m/CONSTITUTION_YOUTH.jpg">
                                Constitution Youth - Male</option>
                            <option value="/assets/images/illustrations/m/EARS.jpg">
                                Ears - Male</option>
                            <option value="/assets/images/illustrations/m/EARS_TODDLER.jpg">
                                Ears Toddler - Male</option>
                            <option value="/assets/images/illustrations/m/EYES.JPG">
                                Eyes - Male</option>
                            <option value="/assets/images/illustrations/m/EYES_TODDLER.jpg">
                                Eyes Toddler - Male</option>
                            <option value="/assets/images/illustrations/m/FEET.jpg">
                                Feet - Male</option>
                            <option value="/assets/images/illustrations/m/FEET_TODDLER.jpg">
                                Feet Toddler - Male</option>
                            <option value="/assets/images/illustrations/m/GROIN_MALE_ADULT.jpg">
                                Groin Male Adult - Male</option>
                            <option value="/assets/images/illustrations/m/GROIN_MALE_TODDLER.jpg">
                                Groin Male Toddler - Male</option>
                            <option value="/assets/images/illustrations/m/GROIN_MALE_YOUTH.jpg">
                                Groin Male Youth - Male</option>
                            <option value="/assets/images/illustrations/m/HANDS.JPG">
                                Hands - Male</option>
                            <option value="/assets/images/illustrations/m/HANDS_TODDLER.jpg">
                                Hands Toddler - Male</option>
                            <option value="/assets/images/illustrations/m/HEAD.jpg">
                                Head - Male</option>
                            <option value="/assets/images/illustrations/m/HEAD_INFANT.jpg">
                                Head Infant - Male</option>
                            <option value="/assets/images/illustrations/m/HEAD_TODDLER.jpg">
                                Head Toddler - Male</option>
                            <option value="/assets/images/illustrations/m/HEAD_YOUTH.jpg">
                                Head Youth - Male</option>
                            <option value="/assets/images/illustrations/m/LEGS.JPG">
                                Legs - Male</option>
                            <option value="/assets/images/illustrations/m/LEGS_TODDLER.jpg">
                                Legs Toddler - Male</option>
                            <option value="/assets/images/illustrations/m/NECK.JPG">
                                Neck - Male</option>
                            <option value="/assets/images/illustrations/m/NECK_TODDLER.jpg">
                                Neck Toddler - Male</option>
                            <option value="/assets/images/illustrations/m/NOSE.jpg">
                                Nose - Male</option>
                            <option value="/assets/images/illustrations/m/NOSE_INFANT.jpg">
                                Nose Infant - Male</option>
                            <option value="/assets/images/illustrations/m/NOSE_TODDLER.jpg">
                                Nose Toddler - Male</option>
                            <option value="/assets/images/illustrations/m/PERINEUM_MALE_ADULT.jpg">
                                Perineum Male Adult - Male</option>
                            <option value="/assets/images/illustrations/m/PERINEUM_MALE_TODDLER.jpg">
                                Perineum Male Toddler - Male</option>
                            <option value="/assets/images/illustrations/m/PERINEUM_MALE_YOUTH.jpg">
                                Perineum Male Youth - Male</option>
                            <option value="/assets/images/illustrations/m/SKIN.jpg">
                                Skin - Male</option>
                            <option value="/assets/images/illustrations/m/THROAT.jpg">
                                Throat - Male</option>
                            <option value="/assets/images/illustrations/m/THROAT_TODDLER.jpg">
                                Throat Toddler - Male</option>
                        </select>
                    </div>

                    <template v-for="document in documents">
                        <div class="p-2 border-bottom bg-color-white-lilac cursor-pointer mt-2">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <p class="mb-0">
                                        <span class="badge mr-2">{{ document.description }}</span>
                                    </p>
                                </div>
                                <div class="col-4 text-end">

                                    <a :href="document.url && (document.url.startsWith('http') || document.url.startsWith('/')) ? document.url : '/' + document.url" target="_blank" class="btn btn-success mr-1" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <button class="btn btn-danger" type="button" title="Delete"
                                        @click="deleteDocument(document.id, 'anatomical')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Annotation Toolbar -->
                    <div class="mb-3 d-flex flex-wrap gap-2 mt-3">
                        <Canvas ref="canvas1Ref" :form="anatomicalForm" @updatedImage="updatedImage" id="canvas-1" />
                    </div>

                    <!-- Description -->
                    <div class="form-group mb-3">
                         <BaseInput type="text" v-model="imageForm.description" label="Description"
                            placeholder="Enter description" required
                             id="annotation-description" />
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-end gap-2">
                         <button  type="button" @click="upsert('anatomical')"
                            class="btn btn-sm btn-primary" :disabled="isUploading">{{isUploading ? 'uploading...' : 'Save'}}</button>
                         <button class="btn btn-sm btn-danger">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Photo Section -->
        <div class="card mb-3">
            <div class="card-header bg-primary">
                <div class="card-title collapsed" data-toggle="collapse" data-target="#add-photo-collapse">
                    <h6 class="d-flex justify-content-between text-white">
                        Add Photo
                        <i class="fas fa-angle-right angle d-flex justify-content-end"></i>
                    </h6>
                </div>
            </div>

            <div id="add-photo-collapse" class="collapse" data-parent="#anatomical-image-accordion">
                <div class="card-body">
                    <div class="form-group">
                        <template v-for="photo in photos">
                            <div class="p-2 border-bottom bg-color-white-lilac cursor-pointer mt-2">
                                <div class="row align-items-center">
                                    <div class="col-8">
                                        <p class="mb-0">
                                            <span class="badge mr-2">{{ photo.description }}</span>
                                        </p>
                                    </div>
                                    <div class="col-4 text-end">

                                        <a :href="photo.url && (photo.url.startsWith('http') || photo.url.startsWith('/')) ? photo.url : '/' + photo.url" target="_blank" class="btn btn-success mr-1" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <button class="btn btn-danger" type="button" title="Delete"
                                            @click="deleteDocument(photo.id, 'photo')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div class="d-flex align-items-end gap-3 mt-4">
                            <div class="flex-grow-1">
                                <label for="photo-upload" class="form-label">Upload Photo</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="photo-upload" ref="file"
                                        accept="image/*" @change="onPhotoFileChange">
                                    <label class="custom-file-label cursor-pointer" for="photo-upload">
                                        {{ selectedPhotoName || 'Choose file' }}
                                    </label>
                                </div>
                              
                            </div>
                            <div class="mb-1">
                                <label for="photo-upload" class="form-label"></label>
                                 <button  type="button" @click="uploadFile" :disabled="uploading"
                                    class="btn btn-sm btn-primary mb-1">{{uploading ? 'uploading...' : 'Upload' }}</button>
                            </div>
                        </div>
                    </div>
                    <Canvas ref="canvas2Ref" :form="photoForm" @updatedImage="updatedImage" id="canvas-2" />

                    <!-- Description -->
                    <div class="form-group mb-3">
                        
                        <BaseInput type="text" v-model="imageForm.description" label="Description"
                            placeholder="Enter description" required
                             id="annotation-description" />
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-end gap-2">
                      
                        <button  type="button" @click="upsert('photo')"
                            class="btn btn-sm btn-primary" :disabled="isUploading">{{  isUploading ? 'uploading...' : 'Save'}}</button>
                         <button class="btn btn-sm btn-danger">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Procedure Section -->
        <div class="card mb-3">
            <div class="card-header bg-primary">
                <div class="card-title collapsed" data-toggle="collapse" data-target="#add-procedure-collapse">
                    <h6 class="d-flex justify-content-between text-white">
                        Add Procedure
                        <i class="fas fa-angle-right angle d-flex justify-content-end"></i>
                    </h6>
                </div>
            </div>
            <div id="add-procedure-collapse" class="collapse" data-parent="#anatomical-image-accordion">
                <div class="card-body">

                    <template v-for="procedure in procedures">
                        <div class="p-2 border-bottom bg-color-white-lilac cursor-pointer mt-2">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <p class="mb-0">
                                        <span class="badge mr-2">{{ procedure.description }}</span>
                                    </p>
                                </div>
                                <div class="col-4 text-end">

                                    <button class="btn btn-danger" type="button" title="Delete"
                                        @click="deleteProcedure(procedure.id)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="procedure-type" class="form-label">Procedure Type</label>
                            <input type="text" v-model="procedureForm.type" class="form-control" id="procedure-type"
                                placeholder="Enter procedure type">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="procedure-code" class="form-label">Procedure Code</label>
                            <input type="text" v-model="procedureForm.cpt" class="form-control" id="procedure-code"
                                placeholder="Enter procedure code">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="procedure-description" class="form-label">Procedure
                                Description</label>
                            <textarea class="form-control" v-model="procedureForm.description"
                                id="procedure-description" rows="3" placeholder="Enter procedure description"
                                @click="getDateMeta('procedure')"></textarea>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" @click="saveProcedure()" class="btn btn-sm btn-primary">Save</button>
                        <button type="button" class="btn btn-sm btn-danger">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
