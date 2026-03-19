<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { computed, ref, watch } from "vue";
import { Link, router } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue";
import Modal from "@/Components/Common/Modal.vue";
import AddPaymentModal from "@/Pages/Modals/AddBillingPayment.vue";

const props = defineProps({
  invoices: Object,
  search: String,
  billingData: {
    type: Object,
    default: () => ({}),
  },
  billing: {
    type: Object,
    default: () => ({}),
  },
  keyword: {
    type: String,
    default: ''
  },
  notes: {
    type: String,
    default: "",
  }
});

const columns = ref([
  { label: "Invoice Number", key: "invoice.invoice_number" },
  { label: "Doctor", key: "doctor.user.name" },
  { label: "Appointment date", key: "appointment_date" },
  { label: "Fee", key: "fee_amount" },
  { label: "Discount", key: "invoice.discount_amount" },
  { label: "Tax", key: "tax_amount" },
  { label: "Amount paid", key: "total_amount" },
  { label: "Payment Status", key: "payment_status" },
])

// Format currency display
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount || 0);
};
const isAddPaymentModal = ref(false);
const selectedRow = ref(null);
const openAddPaymentModal = (row) => {
  selectedRow.value = row;
  isAddPaymentModal.value = true;
};

const closeAddPaymentModal = () => {
  isAddPaymentModal.value = false;
  selectedRow.value = null;
};
</script>

<template>
  <AuthLayout title="Billing" description="Billing" heading="Billing">
    <div class="row">
      <!-- Right side content -->
      <div class="card col-sm-12 p-3">
        <div class="align-items-center d-flex justify-content-between">
          <div class="todo-date d-flex mr-3">
            <h4 class="card-title">Billing</h4>

          </div>
        </div>
        <div class="iq-card-body mt-3">
          <div class="row mb-4" v-if="billingData.length > 0">
            <div class="col-md-4">
              <div class="card bg-light">
                <div class="card-body">
                  <h6 class="card-title">Total Records</h6>
                  <h4 class="text-primary">{{ billingData.length }}</h4>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card bg-light">
                <div class="card-body">
                  <h6 class="card-title">Total Balance</h6>
                  <h4 class="text-warning">
                    {{formatCurrency(billingData.reduce((sum, row) => sum + (parseFloat(row.balance) || 0), 0))}}
                  </h4>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card bg-light">
                <div class="card-body">
                  <h6 class="card-title">Total Charges</h6>
                  <h4 class="text-success">
                    {{formatCurrency(billingData.reduce((sum, row) => sum + (parseFloat(row.charges) || 0), 0))}}
                  </h4>
                </div>
              </div>
            </div>
            <div class="alert alert-success rounded">
              <p>Billing Notes: {{ notes?.billing_note }}</p>
            </div>

          </div>
          <Table :columns="columns" :data="{ data: invoices.data }" :search="search">
            <template #balance="{ value }">
              <span :class="{ 'text-danger': value < 0, 'text-success': value > 0 }">
                {{ formatCurrency(value) }}
              </span>
            </template>

            <template #charges="{ value }">
              <span class="text-success">
                {{ formatCurrency(value) }}
              </span>
            </template>

            <template #actions="{ row }">
              <div class="btn-group btn-group-sm">
                <!-- Payment History -->
                <Link :href="route('patient.billing_payment_history', row?.encounter?.id)" v-if="row?.encounter"
                  class="btn btn-outline-primary mr-2" title="Payment History">
                  <i class="fa fa-history"></i>
                </Link>

                <!-- Add Payment -->
                <a v-if="row.payment_status == 'pending'" :href="route('patient.appointment.payment', row.id)"
                  class="btn btn-outline-success w-30px" title="Add Payment">
                  <i class="fa fa-usd"></i>
                </a>
                <button v-else class="btn btn-outline-secondary" disabled title="Payment Completed">
                  <i class="fa fa-check"></i>
                </button>

                <!---print -->
                <a v-if="row?.invoice?.invoice_number" class="btn btn-outline-primary ml-2"
                  :href="route('patient.billing.print', { id: row.id })" title="Print" target="_blank">
                  <i class="fa fa-print"></i>
                </a>
              </div>
            </template>
          </Table>
        </div>
      </div>
    </div>
    <Modal :isOpen="isAddPaymentModal" title="Add Payment" @close="closeAddPaymentModal" size="lg">
      <AddPaymentModal v-if="isAddPaymentModal" :billingData="billingData" @close="closeAddPaymentModal"
        @success="closeAddPaymentModal" />
    </Modal>

  </AuthLayout>
</template>
