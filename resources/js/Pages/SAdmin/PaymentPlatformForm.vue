<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { useForm, router } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    platform: {
        type: Object,
        default: () => ({
            id: null,
            name: '',
            code: '',
            description: '',
            api_key: '',
            secret_key: '',
            merchant_id: '',
            webhook_url: '',
            environment: 'sandbox',
            settings: null,
            supported_currencies: [],
            is_active: true,
            is_default: false,
        }),
    },
    isEditing: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Add Payment Platform',
    },
});

const form = useForm({
    id: props.platform.id,
    name: props.platform.name,
    code: props.platform.code,
    description: props.platform.description || '',
    api_key: props.platform.api_key || '',
    secret_key: props.platform.secret_key || '',
    merchant_id: props.platform.merchant_id || '',
    webhook_url: props.platform.webhook_url || '',
    environment: props.platform.environment || 'sandbox',
    settings: props.platform.settings || null,
    supported_currencies: props.platform.supported_currencies || [],
    is_active: props.platform.is_active,
    is_default: props.platform.is_default,
});

const currencyOptions = [
    { value: 'USD', label: 'USD - US Dollar' },
    { value: 'EUR', label: 'EUR - Euro' },
    { value: 'GBP', label: 'GBP - British Pound' },
    { value: 'INR', label: 'INR - Indian Rupee' },
    { value: 'CAD', label: 'CAD - Canadian Dollar' },
    { value: 'AUD', label: 'AUD - Australian Dollar' },
    { value: 'JPY', label: 'JPY - Japanese Yen' },
];

const showApiKey = ref(false);
const showSecretKey = ref(false);

const selectedCurrencies = ref(props.platform.supported_currencies || []);

const toggleCurrency = (currency) => {
    const index = selectedCurrencies.value.indexOf(currency);
    if (index === -1) {
        selectedCurrencies.value.push(currency);
    } else {
        selectedCurrencies.value.splice(index, 1);
    }
    form.supported_currencies = selectedCurrencies.value;
};

const isCurrencySelected = (currency) => {
    return selectedCurrencies.value.includes(currency);
};

const submitForm = () => {
    form.supported_currencies = selectedCurrencies.value;
    
    if (props.isEditing) {
        form.put(route('admin.payment-platforms.update', { id: props.platform.id }), {
            onSuccess: () => {
                // Success handling
            },
        });
    } else {
        form.post(route('admin.payment-platforms.store'), {
            onSuccess: () => {
                // Success handling
            },
        });
    }
};

const cancel = () => {
    router.get(route('admin.payment-platforms.index'));
};

const generateCode = () => {
    if (!form.name) return;
    
    // Generate code from name (lowercase, replace spaces with underscores)
    form.code = form.name
        .toLowerCase()
        .replace(/\s+/g, '_')
        .replace(/[^a-z0-9_]/g, '');
};
</script>

<template>
    <AuthLayout :title="title" :description="title" :heading="title">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">{{ title }}</h5>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="submitForm">
                            <!-- Basic Information -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Platform Name <span class="text-danger">*</span></label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        v-model="form.name" 
                                        required
                                        placeholder="e.g., Razorpay"
                                    />
                                    <span v-if="form.errors.name" class="text-danger">{{ form.errors.name }}</span>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Code <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input 
                                            type="text" 
                                            class="form-control" 
                                            v-model="form.code" 
                                            required
                                            placeholder="e.g., razorpay"
                                        />
                                        <button 
                                            type="button" 
                                            class="btn btn-outline-secondary" 
                                            @click="generateCode"
                                            title="Generate code from name"
                                        >
                                            <i class="bi bi-magic"></i>
                                        </button>
                                    </div>
                                    <span v-if="form.errors.code" class="text-danger">{{ form.errors.code }}</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea 
                                    class="form-control" 
                                    v-model="form.description" 
                                    rows="3"
                                    placeholder="Brief description of the payment platform"
                                ></textarea>
                            </div>

                            <!-- Environment -->
                            <div class="mb-3">
                                <label class="form-label">Environment <span class="text-danger">*</span></label>
                                <div class="btn-group w-100" role="group">
                                    <input 
                                        type="radio" 
                                        class="btn-check" 
                                        id="sandbox" 
                                        value="sandbox" 
                                        v-model="form.environment"
                                    />
                                    <label class="btn btn-outline-warning" for="sandbox">
                                        <i class="bi bi-cone-striped"></i> Sandbox
                                    </label>

                                    <input 
                                        type="radio" 
                                        class="btn-check" 
                                        id="live" 
                                        value="live" 
                                        v-model="form.environment"
                                    />
                                    <label class="btn btn-outline-success" for="live">
                                        <i class="bi bi-check-circle"></i> Live
                                    </label>
                                </div>
                            </div>

                            <!-- Credentials -->
                            <div class="card bg-light mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">API Credentials</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">API Key</label>
                                            <div class="input-group">
                                                <input 
                                                    :type="showApiKey ? 'text' : 'password'" 
                                                    class="form-control" 
                                                    v-model="form.api_key" 
                                                    placeholder="Enter API key"
                                                />
                                                <button 
                                                    type="button" 
                                                    class="btn btn-outline-secondary"
                                                    @click="showApiKey = !showApiKey"
                                                >
                                                    <i :class="showApiKey ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Secret Key</label>
                                            <div class="input-group">
                                                <input 
                                                    :type="showSecretKey ? 'text' : 'password'" 
                                                    class="form-control" 
                                                    v-model="form.secret_key" 
                                                    placeholder="Enter secret key"
                                                />
                                                <button 
                                                    type="button" 
                                                    class="btn btn-outline-secondary"
                                                    @click="showSecretKey = !showSecretKey"
                                                >
                                                    <i :class="showSecretKey ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">Merchant ID</label>
                                            <input 
                                                type="text" 
                                                class="form-control" 
                                                v-model="form.merchant_id" 
                                                placeholder="Enter merchant ID (if applicable)"
                                            />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Webhook URL</label>
                                            <input 
                                                type="url" 
                                                class="form-control" 
                                                v-model="form.webhook_url" 
                                                placeholder="https://..."
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Supported Currencies -->
                            <div class="mb-3">
                                <label class="form-label">Supported Currencies</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <div 
                                        v-for="currency in currencyOptions" 
                                        :key="currency.value"
                                        class="form-check"
                                    >
                                        <input 
                                            type="checkbox" 
                                            class="btn-check" 
                                            :id="'currency-' + currency.value" 
                                            :value="currency.value"
                                            :checked="isCurrencySelected(currency.value)"
                                            @change="toggleCurrency(currency.value)"
                                        />
                                        <label 
                                            class="btn btn-sm" 
                                            :class="isCurrencySelected(currency.value) ? 'btn-primary' : 'btn-outline-secondary'"
                                            :for="'currency-' + currency.value"
                                        >
                                            {{ currency.value }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input 
                                            type="checkbox" 
                                            class="form-check-input" 
                                            id="is_active" 
                                            v-model="form.is_active"
                                        />
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input 
                                            type="checkbox" 
                                            class="form-check-input" 
                                            id="is_default" 
                                            v-model="form.is_default"
                                        />
                                        <label class="form-check-label" for="is_default">
                                            Set as Default Platform
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary" @click="cancel">
                                    Cancel
                                </button>
                                <button 
                                    type="submit" 
                                    class="btn btn-primary" 
                                    :disabled="form.processing"
                                >
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                                    {{ isEditing ? 'Update Platform' : 'Create Platform' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

