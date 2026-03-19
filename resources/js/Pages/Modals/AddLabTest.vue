<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";

const props = defineProps({
    categories: {
        type: Array,
        default: () => []
    },
    sampleTypes: {
        type: Array,
        default: () => [
            { id: 'blood', name: 'Blood' },
            { id: 'urine', name: 'Urine' },
            { id: 'stool', name: 'Stool' },
            { id: 'saliva', name: 'Saliva' },
            { id: 'sputum', name: 'Sputum' },
            { id: 'cerebrospinal_fluid', name: 'Cerebrospinal Fluid' },
            { id: 'tissue', name: 'Tissue' },
            { id: 'swab', name: 'Swab' },
        ]
    }
});

const emit = defineEmits(["close", "submit"]);
 
const form = useForm({
    id: '',
    lab_test_category_id: '',
    name: '',
    description: '',
    sample_type: '',
    fasting_required: false,
    report_time: '',
    instructions: '',
    price: '',
    discount: 0,
    final_price: '',
    currency: 'USD',
    is_home_collection_available: false,
    is_active: true,
});

const calculateFinalPrice = () => {
    const price = parseFloat(form.price) || 0;
    const discount = parseFloat(form.discount) || 0;
    form.final_price = (price - (price * discount / 100)).toFixed(2);
};

const submitForm = () => {

    // Validate that category is selected
    if (!form.lab_test_category_id) {
        form.errors.lab_test_category_id = 'Please select a test category';
        return;
    }
    
    form.post(route('admin.lab-tests.store'), {
        onSuccess: () => {
             emit("submit");
            closeModal();
         },
        onError: () => {
 }
    });
};

const update=(data) =>{
    form.id = data.id;
    form.name = data.name;
    form.lab_test_category_id = data.lab_test_category_id;
    form.description = data.description;
    form.sample_type = data.sample_type;
    form.fasting_required = data.fasting_required;
    form.report_time = data.report_time;
    form.instructions = data.instructions;
    form.price = data.price;
    form.discount = data.discount;
    form.final_price = data.final_price;
    form.currency = data.currency;
    form.is_home_collection_available = data.is_home_collection_available;
    form.is_active = data.is_active;
}

defineExpose({ update });

const closeModal = () => {
    emit("close");
    form.reset();
    form.errors.reset();
};
</script>

<template>
    <form @submit.prevent="submitForm">
        <div class="row">
            <!-- Basic Information -->
            <div class="col-12">
                <h6 class="text-primary mb-3">Basic Information</h6>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="mb-2">
                    <label class="form-label">Test Category <span class="text-danger">*</span></label>
                    <select v-model="form.lab_test_category_id" class="form-select" :class="{ 'is-invalid': form.errors.lab_test_category_id }">
                        <option value="" disabled>Select category</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                    <div v-if="form.errors.lab_test_category_id" class="text-danger mt-2">{{ form.errors.lab_test_category_id }}</div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <BaseInput 
                    v-model="form.name" 
                    label="Test Name" 
                    placeholder="Enter test name" 
                    required 
                    :error="form.errors.name" 
                />
            </div>
            
            <div class="col-md-12 mb-3">
                <BaseInput 
                    v-model="form.description" 
                    label="Description" 
                    placeholder="Enter test description" 
                    type="textarea"
                    :rows="3"
                    :error="form.errors.description" 
                />
            </div>

            <!-- Sample Details -->
            <div class="col-12 mt-3">
                <h6 class="text-primary mb-3">Sample Details</h6>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="mb-2">
                    <label class="form-label">Sample Type</label>
                    <select v-model="form.sample_type" class="form-select" :class="{ 'is-invalid': form.errors.sample_type }">
                        <option value="" disabled>Select sample type</option>
                        <option v-for="type in sampleTypes" :key="type.id" :value="type.id">
                            {{ type.name }}
                        </option>
                    </select>
                    <div v-if="form.errors.sample_type" class="text-danger mt-2">{{ form.errors.sample_type }}</div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <BaseInput 
                    v-model="form.report_time" 
                    label="Report Time" 
                    placeholder="e.g., 24 hours" 
                    :error="form.errors.report_time" 
                />
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="form-check form-switch">
                    <input 
                        class="form-check-input" 
                        type="checkbox" 
                        id="fasting_required"
                        v-model="form.fasting_required"
                    >
                    <label class="form-check-label" for="fasting_required">
                        Fasting Required
                    </label>
                </div>
                <div v-if="form.errors.fasting_required" class="text-danger mt-2">
                    {{ form.errors.fasting_required }}
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <BaseInput 
                    v-model="form.instructions" 
                    label="Pre-Test Instructions" 
                    placeholder="Enter patient instructions" 
                    type="textarea"
                    :rows="3"
                    :error="form.errors.instructions" 
                />
            </div>

            <!-- Pricing -->
            <div class="col-12 mt-3">
                <h6 class="text-primary mb-3">Pricing</h6>
            </div>
            
            <div class="col-md-4 mb-3">
                <BaseInput 
                    v-model="form.price" 
                    label="Price" 
                    placeholder="Enter price" 
                    type="number"
                    step="0.01"
                    @input="calculateFinalPrice"
                    :error="form.errors.price" 
                />
            </div>
            
            <div class="col-md-4 mb-3">
                <BaseInput 
                    v-model="form.discount" 
                    label="Discount (%)" 
                    placeholder="Enter discount percentage" 
                    type="number"
                    step="0.01"
                    @input="calculateFinalPrice"
                    :error="form.errors.discount" 
                />
            </div>
            
            <div class="col-md-4 mb-3">
                <BaseInput 
                    v-model="form.final_price" 
                    label="Final Price" 
                    placeholder="Calculated automatically" 
                    type="number"
                    step="0.01"
                    readonly
                    :error="form.errors.final_price" 
                />
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="mb-2">
                    <label class="form-label">Currency</label>
                    <select v-model="form.currency" class="form-select" :class="{ 'is-invalid': form.errors.currency }">
                        <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                        <option value="GBP">GBP</option>
                        <option value="INR">INR</option>
                    </select>
                    <div v-if="form.errors.currency" class="text-danger mt-2">{{ form.errors.currency }}</div>
                </div>
            </div>

            <!-- Settings -->
            <div class="col-12 mt-3">
                <h6 class="text-primary mb-3">Settings</h6>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="form-check form-switch">
                    <input 
                        class="form-check-input" 
                        type="checkbox" 
                        id="is_home_collection_available"
                        v-model="form.is_home_collection_available"
                    >
                    <label class="form-check-label" for="is_home_collection_available">
                        Home Collection Available
                    </label>
                </div>
                <div v-if="form.errors.is_home_collection_available" class="text-danger mt-2">
                    {{ form.errors.is_home_collection_available }}
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="form-check form-switch">
                    <input 
                        class="form-check-input" 
                        type="checkbox" 
                        id="is_active"
                        v-model="form.is_active"
                    >
                    <label class="form-check-label" for="is_active">
                        Active
                    </label>
                </div>
                <div v-if="form.errors.is_active" class="text-danger mt-2">
                    {{ form.errors.is_active }}
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="submit" class="btn btn-primary" :disabled="form.processing" :aria-disabled="form.processing" aria-live="polite">
                <span v-if="form.processing" class="d-flex align-items-center">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Saving...
                </span>
                <span v-else>Save</span>
            </button>
                        <button type="button" class="btn btn-danger" @click="closeModal" :disabled="form.processing">Close</button>

        </div>
    </form>
</template>

