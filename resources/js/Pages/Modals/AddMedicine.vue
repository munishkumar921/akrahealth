<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
 import BaseDatePicker from "@/Components/Common/Input/BaseDatePicker.vue";

const props = defineProps({
    dosageForms: {
        type: Array,
        default: () => [
            { id: 'tablet', name: 'Tablet' },
            { id: 'capsule', name: 'Capsule' },
            { id: 'syrup', name: 'Syrup' },
            { id: 'injection', name: 'Injection' },
            { id: 'ointment', name: 'Ointment' },
            { id: 'spray', name: 'Spray' },
            { id: 'drop', name: 'Drop' },
            { id: 'powder', name: 'Powder' },
            { id: 'gel', name: 'Gel' },
        ]
    },
    routes: {
        type: Array,
        default: () => [
            { id: 'oral', name: 'Oral' },
            { id: 'topical', name: 'Topical' },
            { id: 'intravenous', name: 'Intravenous' },
            { id: 'intramuscular', name: 'Intramuscular' },
            { id: 'sublingual', name: 'Sublingual' },
            { id: 'nasal', name: 'Nasal' },
            { id: 'rectal', name: 'Rectal' },
            { id: 'inhalation', name: 'Inhalation' },
        ]
    }
});

const emit = defineEmits(["close", "submit"]);

const isValidated = ref(false);

const form = useForm({
    id: '',
    name: '',
    brand_name: '',
    generic_name: '',
    composition: '',
    dosage_form: '',
    strength: '',
    route: '',
    indications: '',
    contraindications: '',
    side_effects: '',
    precautions: '',
    instructions: '',
    price: '',
    currency: 'USD',
    stock_quantity: '',
    expiry_date: '',
    batch_no: '',
    is_prescription_required: false,
    is_active: true,
});

const submitForm = () => {
    isValidated.value = true;
    
    form.post(route('admin.medicines.store'), {
        onSuccess: () => {
            isValidated.value = false;
            emit("submit");
            closeModal();
        },
        onError: () => {
            isValidated.value = true;
        }
    });
};

const closeModal = () => {
    emit("close");
    form.reset();
    isValidated.value = false;
};
 const update = (data) => {
    form.id = data.id;
    form.name = data.name;
    form.brand_name = data.brand_name;
    form.generic_name = data.generic_name;
    form.composition = data.composition;
    form.dosage_form = data.dosage_form;
    form.strength = data.strength;
    form.route = data.route;
    form.indications = data.indications;
    form.contraindications = data.contraindications;
    form.side_effects = data.side_effects;
    form.precautions = data.precautions;
    form.instructions = data.instructions;
    form.price = data.price;
    form.currency = data.currency;
    form.stock_quantity = data.stock_quantity;
    form.expiry_date = data.expiry_date;
    form.batch_no = data.batch_no;
    form.is_prescription_required = data.is_prescription_required;
    form.is_active = data.is_active;
}
defineExpose({ update });

</script>

<template>
    <form @submit.prevent="submitForm">
        <div class="row">
            <!-- Basic Information -->
            <div class="col-12">
                <h6 class="text-primary mb-3">Basic Information</h6>
            </div>
            
            <div class="col-md-6 mb-3">
                <BaseInput 
                    v-model="form.name" 
                    label="Medicine Name" 
                    placeholder="Enter medicine name" 
                    required 
                    :error="form.errors.name" 
                />
            </div>
            
            <div class="col-md-6 mb-3">
                <BaseInput 
                    v-model="form.brand_name" 
                    label="Brand Name" 
                    placeholder="Enter brand name" 
                    :error="form.errors.brand_name" 
                />
            </div>
            
            <div class="col-md-6 mb-3">
                <BaseInput 
                    v-model="form.generic_name" 
                    label="Generic Name" 
                    placeholder="Enter generic name" 
                    :error="form.errors.generic_name" 
                />
            </div>
            
            <div class="col-md-6 mb-3">
                <BaseInput 
                    v-model="form.composition" 
                    label="Composition" 
                    placeholder="Enter composition" 
                    :error="form.errors.composition" 
                />
            </div>

            <!-- Form Details -->
            <div class="col-12 mt-3">
                <h6 class="text-primary mb-3">Form Details</h6>
            </div>
            
            <div class="col-md-4 mb-3">
                <BaseSelect 
                    v-model="form.dosage_form" 
                    label="Dosage Form" 
                    placeholder="Select dosage form"
                    :error="form.errors.dosage_form"
                >
                    <option v-for="form in dosageForms" :key="form.id" :value="form.id">
                        {{ form.name }}
                    </option>
                </BaseSelect>
            </div>
            
            <div class="col-md-4 mb-3">
                <BaseInput 
                    v-model="form.strength" 
                    label="Strength" 
                    placeholder="e.g., 500mg" 
                    :error="form.errors.strength" 
                />
            </div>
            
            <div class="col-md-4 mb-3">
                <BaseSelect 
                    v-model="form.route" 
                    label="Route" 
                    placeholder="Select route"
                    :error="form.errors.route"
                >
                    <option v-for="route in routes" :key="route.id" :value="route.id">
                        {{ route.name }}
                    </option>
                </BaseSelect>
            </div>

            <!-- Medical Information -->
            <div class="col-12 mt-3">
                <h6 class="text-primary mb-3">Medical Information</h6>
            </div>
            
            <div class="col-md-6 mb-3">
                <BaseInput 
                    v-model="form.indications" 
                    label="Indications" 
                    placeholder="Enter indications" 
                    type="textarea"
                    :rows="3"
                    :error="form.errors.indications" 
                />
            </div>
            
            <div class="col-md-6 mb-3">
                <BaseInput 
                    v-model="form.contraindications" 
                    label="Contraindications" 
                    placeholder="Enter contraindications" 
                    type="textarea"
                    :rows="3"
                    :error="form.errors.contraindications" 
                />
            </div>
            
            <div class="col-md-6 mb-3">
                <BaseInput 
                    v-model="form.side_effects" 
                    label="Side Effects" 
                    placeholder="Enter side effects" 
                    type="textarea"
                    :rows="3"
                    :error="form.errors.side_effects" 
                />
            </div>
            
            <div class="col-md-6 mb-3">
                <BaseInput 
                    v-model="form.precautions" 
                    label="Precautions" 
                    placeholder="Enter precautions" 
                    type="textarea"
                    :rows="3"
                    :error="form.errors.precautions" 
                />
            </div>
            
            <div class="col-md-12 mb-3">
                <BaseInput 
                    v-model="form.instructions" 
                    label="Instructions" 
                    placeholder="Enter usage instructions" 
                    type="textarea"
                    :rows="3"
                    :error="form.errors.instructions" 
                />
            </div>

            <!-- Pricing & Inventory -->
            <div class="col-12 mt-3">
                <h6 class="text-primary mb-3">Pricing & Inventory</h6>
            </div>
            
            <div class="col-md-4 mb-3">
                <BaseInput 
                    v-model="form.price" 
                    label="Price" 
                    placeholder="Enter price" 
                    type="number"
                    step="0.01"
                    :error="form.errors.price" 
                />
            </div>
            
            <div class="col-md-4 mb-3">
                <BaseSelect 
                    v-model="form.currency" 
                    label="Currency" 
                    :error="form.errors.currency"
                >
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
                    <option value="GBP">GBP</option>
                    <option value="INR">INR</option>
                </BaseSelect>
            </div>
            
            <div class="col-md-4 mb-3">
                <BaseInput 
                    v-model="form.stock_quantity" 
                    label="Stock Quantity" 
                    placeholder="Enter stock quantity" 
                    type="number"
                    :error="form.errors.stock_quantity" 
                />
            </div>
            
            <div class="col-md-4 mb-3">
                <BaseDatePicker 
                    v-model="form.expiry_date" 
                    label="Expiry Date" 
                    placeholder="Select expiry date" 
                    type="date"
                    :error="form.errors.expiry_date" 
                />
            </div>
            
            <div class="col-md-4 mb-3">
                <BaseInput 
                    v-model="form.batch_no" 
                    label="Batch Number" 
                    placeholder="Enter batch number" 
                    :error="form.errors.batch_no" 
                />
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
                        id="is_prescription_required"
                        v-model="form.is_prescription_required"
                    >
                    <label class="form-check-label" for="is_prescription_required">
                        Prescription Required
                    </label>
                </div>
                <div v-if="form.errors.is_prescription_required" class="text-danger mt-2">
                    {{ form.errors.is_prescription_required }}
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
            
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                Save Medicine
            </button>
            <button type="button" class="btn btn-danger" @click="closeModal">Close</button>
        </div>
    </form>
</template>

