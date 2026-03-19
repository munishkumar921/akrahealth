<script setup>
import { useForm } from '@inertiajs/vue3';
import DatePicker from '@/Components/Common/Input/BaseDatePicker.vue';
import InputError from '@/Components/InputError.vue';


const props = defineProps({

    billingData: {
        type: Object,
        default: () => ({}),
    },
    billingId: {
        type: [String, Number],
        required: true,
    },
    index: {
        type: [String, Number],
        required: true,
    }
});

const emit = defineEmits(['close', 'payment-saved']);

const form = useForm({
    id: props.billingData?.id || '',
    encounter_id: props.billingData?.encounter_id || '',
    other_billing_id: props.billingData?.other_billing_id || '',
    patient_id: props.billingData?.patient_id || '',
    dos_f: props.billingData?.dos_f || new Date().toISOString().slice(0, 10),
    payment: props.billingData?.payment || '',
    payment_type: props.billingData?.payment_type || '',
    hospital_id: props.billingData?.hospital_id || '',
});


const submitPayment = () => {

    form.post(route('doctor.billing_make_payment.store'), {
        onSuccess: () => {
            emit('payment-saved');
            emit('close');
        },
        onError: (errors) => {
            console.error('Error saving payment:', errors);
            // Handle error display if needed
        }
    });
};

const closeModal = () => {
    emit('close');
};
</script>

<template>
    <form @submit.prevent="submitPayment" class="space-y-6">
        <!-- Date of Payment -->
        <div>
            <label for="dos_f" class="form-label">Date of Payment</label>
            <DatePicker id="dos_f" v-model="form.dos_f" required />
            <InputError class="mt-2" :message="form.errors.dos_f" />
        </div>

        <!-- Payment Amount -->
        <div class="mt-4">
            <label for="payment" class="form-label">Payment Amount</label>
            <input id="payment" type="text" class="form-control" v-model="form.payment" required placeholder="0.00">
            <div v-if="form.errors.payment" class="text-danger mt-1">{{ form.errors.payment }}</div>
        </div>

        <!-- Payment Type -->
        <div class="mt-4">
            <label for="payment_type" class="form-label">Payment Type</label>
            <input id="payment_type" type="text" class="form-control" v-model="form.payment_type" required
                placeholder="e.g., Credit Card, Cash">
            <div v-if="form.errors.payment_type" class="text-danger mt-1">{{ form.errors.payment_type }}</div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4 border-t pt-4">
            
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                <span v-if="form.processing" class="spinner-border spinner-border-sm" role="status"
                    aria-hidden="true"></span>
                Save Payment
            </button>
            <button type="button" class="btn btn-danger" @click="closeModal">
                Close
            </button>
        </div>
    </form>

</template>
