<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { Link } from "@inertiajs/vue3";
import Swal from "sweetalert2";

// Props with default empty object
const props = defineProps({
  invoice: {
    type: Object,
    default: () => ({})
  }
});

// Form data with optional chaining to avoid errors
const form = useForm({
  invoice_currency: props.invoice?.invoice_currency || '',
  invoice_language: props.invoice?.invoice_language || '',
  invoice_vendor: props.invoice?.invoice_vendor || '',
  invoice_vendor_website: props.invoice?.invoice_vendor_website || '',
  invoice_address: props.invoice?.invoice_address || '',
  invoice_city: props.invoice?.invoice_city || '',
  invoice_state: props.invoice?.invoice_state || '',
  invoice_postal_code: props.invoice?.invoice_postal_code || '',
  invoice_country: props.invoice?.invoice_country || '',
  invoice_phone: props.invoice?.invoice_phone || '',
  invoice_vat_number: props.invoice?.invoice_vat_number || ''
});

// Submit handler
const submitForm = () => {
    Swal.fire({
        title: "Save Payment Settings?",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Yes, Save",
    }).then((r) => {
        if (r.isConfirmed) {
            Swal.fire("Saved!", "Settings updated (demo).", "success");
        }
    });
};

// Countries list
const countries = [
  'United States', 'United Kingdom', 'India', 'Canada', 'Australia', 'Germany', 'France', 'Netherlands', 'Brazil'
];

// Currencies list (shortened here for brevity)
const currencies = {
  USD: 'US Dollar',
  EUR: 'Euro',
  GBP: 'British Pound Sterling',
  INR: 'Indian Rupee',
  AUD: 'Australian Dollar',
  CAD: 'Canadian Dollar'
};
</script>

<template>
<AuthLayout title="Invoice Settings" description="Finance - Invoice Settings" heading="Invoice Settings">
  <div class="invoice-settings">
    <div class="page-header">
      <div class="page-leftheader">
        <h4 class="page-title mb-0">Invoice Settings</h4>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12 col-md-12 col-xm-12">
        <div class="card border-0 mt-2">
          <div class="card-header">
            <h3 class="card-title text-white fs-22">Setup Invoice Settings</h3>
          </div>
          <div class="card-body">
            <form @submit.prevent="submitForm">
              <div class="row">

                <!-- Invoice Currency -->
                <div class="col-md-6 col-sm-12">
                  <div class="input-box">
                    <h6>Invoice Currency <span class="text-required">*</span></h6>
                    <select v-model="form.invoice_currency" class="form-select">
                      <option v-for="(name, code) in currencies" :key="code" :value="code">{{ name }}</option>
                    </select>
                    <p v-if="form.errors.invoice_currency" class="text-danger">{{ form.errors.invoice_currency }}</p>
                  </div>
                </div>

                <!-- Invoice Language -->
                <div class="col-md-6 col-sm-12">
                  <div class="input-box">
                    <h6>Invoice Language <span class="text-required">*</span></h6>
                    <select v-model="form.invoice_language" class="form-select">
                      <option value="br">BR</option>
                      <option value="de">DE</option>
                      <option value="en">EN</option>
                      <option value="es">ES</option>
                      <option value="fr">FR</option>
                      <option value="it">IT</option>
                      <option value="nl">NL</option>
                    </select>
                    <p v-if="form.errors.invoice_language" class="text-danger">{{ form.errors.invoice_language }}</p>
                  </div>
                </div>

                <!-- Company Name -->
                <div class="col-md-6 col-sm-12">
                  <div class="input-box">
                    <h6>Company Name <span class="text-required">*</span></h6>
                    <input type="text" v-model="form.invoice_vendor" class="form-control">
                    <p v-if="form.errors.invoice_vendor" class="text-danger">{{ form.errors.invoice_vendor }}</p>
                  </div>
                </div>

                <!-- Company Website -->
                <div class="col-md-6 col-sm-12">
                  <div class="input-box">
                    <h6>Company Website</h6>
                    <input type="text" v-model="form.invoice_vendor_website" class="form-control">
                    <p v-if="form.errors.invoice_vendor_website" class="text-danger">{{ form.errors.invoice_vendor_website }}</p>
                  </div>
                </div>

                <!-- Business Address -->
                <div class="col-12">
                  <div class="input-box">
                    <h6>Business Address</h6>
                    <input type="text" v-model="form.invoice_address" class="form-control">
                    <p v-if="form.errors.invoice_address" class="text-danger">{{ form.errors.invoice_address }}</p>
                  </div>
                </div>

                <!-- City -->
                <div class="col-md-4 col-sm-12">
                  <div class="input-box">
                    <h6>City</h6>
                    <input type="text" v-model="form.invoice_city" class="form-control">
                    <p v-if="form.errors.invoice_city" class="text-danger">{{ form.errors.invoice_city }}</p>
                  </div>
                </div>

                <!-- State -->
                <div class="col-md-2 col-sm-12">
                  <div class="input-box">
                    <h6>State</h6>
                    <input type="text" v-model="form.invoice_state" class="form-control">
                    <p v-if="form.errors.invoice_state" class="text-danger">{{ form.errors.invoice_state }}</p>
                  </div>
                </div>

                <!-- Postal Code -->
                <div class="col-md-2 col-sm-12">
                  <div class="input-box">
                    <h6>Postal Code</h6>
                    <input type="text" v-model="form.invoice_postal_code" class="form-control">
                    <p v-if="form.errors.invoice_postal_code" class="text-danger">{{ form.errors.invoice_postal_code }}</p>
                  </div>
                </div>

                <!-- Country -->
                <div class="col-md-4 col-sm-12">
                  <div class="input-box">
                    <h6>Country</h6>
                    <select v-model="form.invoice_country" class="form-select">
                      <option v-for="country in countries" :key="country" :value="country">{{ country }}</option>
                    </select>
                    <p v-if="form.errors.invoice_country" class="text-danger">{{ form.errors.invoice_country }}</p>
                  </div>
                </div>

                <!-- Phone -->
                <div class="col-md-6 col-sm-12">
                  <div class="input-box">
                    <h6>Phone Number</h6>
                    <input type="text" v-model="form.invoice_phone" class="form-control">
                    <p v-if="form.errors.invoice_phone" class="text-danger">{{ form.errors.invoice_phone }}</p>
                  </div>
                </div>

                <!-- VAT Number -->
                <div class="col-md-6 col-sm-12">
                  <div class="input-box">
                    <h6>VAT Number</h6>
                    <input type="text" v-model="form.invoice_vat_number" class="form-control">
                    <p v-if="form.errors.invoice_vat_number" class="text-danger">{{ form.errors.invoice_vat_number }}</p>
                  </div>
                </div>

              </div>

              <!-- Action Buttons -->
              <div class="border-0 text-right mb-2 mt-1">
                <Link :href="route('superAdmin.invoice')" class="btn btn-cancel mr-2">Cancel</Link>
                <button type="submit" class="btn btn-primary">Save</button>
              </div>

            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</AuthLayout>
</template>

<style scoped>
.invoice-settings .input-box {
  margin-bottom: 1.5rem;
}

.invoice-settings .input-box h6 {
  font-size: 14px;
  font-weight: 500;
  margin-bottom: 5px;
}

.invoice-settings .text-required {
  color: red;
}

.invoice-settings .form-select,
.invoice-settings .form-control {
  border: 1px solid #d1d5db;
  border-radius: 5px;
  padding: 0.5rem;
}

.invoice-settings .btn {
  padding: 0.5rem 1.25rem;
  border-radius: 5px;
}

.invoice-settings .btn-cancel {
  background-color: #f3f4f6;
  color: #111827;
  border: 1px solid #d1d5db;
}

.invoice-settings .btn-cancel:hover {
  background-color: #e5e7eb;
}

.invoice-settings .btn-primary {
  background-color: #3b82f6;
  color: #fff;
  border: none;
}

.invoice-settings .btn-primary:hover {
  background-color: #2563eb;
}
</style>
