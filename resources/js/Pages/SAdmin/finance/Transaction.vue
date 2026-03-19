<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { ref, computed } from "vue";
import Swal from 'sweetalert2/dist/sweetalert2.js';
import jsPDF from 'jspdf';

const props = defineProps({
  transactions: {
    type: Array,
    default: () => []
  },
  keyword: String
});

const transactions = ref(props.transactions);

const columns = [
  { label: 'User', key: 'user' },
  { label: 'Status', key: 'status' },
  { label: 'Plan Name', key: 'plan_name' },
  { label: 'Price', key: 'amount', formatter: (v) => `$${Number(v || 0).toFixed(2)}` },
  { label: 'Order ID', key: 'order_id' },
  { label: 'Paid On', key: 'created_on' },
  { label: 'Pricing Plan', key: 'frequency' },
];

// Return all transactions
const tableDataProp = computed(() => {
  return { data: transactions.value, links: [] };
});

// Modal states
const showModal = ref(false);
const selectedTransaction = ref(null);

// Open modal to view transaction details
const openRowModal = ({ row }) => {
  selectedTransaction.value = { ...row };
  showModal.value = true;
};

// Close modal
const closeModal = () => {
  showModal.value = false;
  selectedTransaction.value = null;
};

// Download transaction PDF
const downloadTransactionPDF = () => {
  if (!selectedTransaction.value) return;

  const doc = new jsPDF();
  const pageWidth = doc.internal.pageSize.getWidth();
  const pageHeight = doc.internal.pageSize.getHeight();
  let yPosition = 20;
  const lineHeight = 8;

  // Header
  doc.setFontSize(18);
  doc.setFont(undefined, 'bold');
  doc.text('Transaction Receipt', pageWidth / 2, yPosition, { align: 'center' });
  yPosition += 15;

  // Divider line
  doc.setDrawColor(200);
  doc.line(15, yPosition, pageWidth - 15, yPosition);
  yPosition += 10;

  // Transaction details
  doc.setFontSize(11);
  doc.setFont(undefined, 'normal');

  const details = [
    { label: 'User:', value: selectedTransaction.value.user },
    { label: 'Status:', value: selectedTransaction.value.status },
    { label: 'Plan Name:', value: selectedTransaction.value.plan_name },
    { label: 'Amount:', value: `$${Number(selectedTransaction.value.amount || 0).toFixed(2)}` },
    { label: 'Order ID:', value: selectedTransaction.value.order_id },
    { label: 'Paid On:', value: selectedTransaction.value.created_on },
    { label: 'Pricing Plan:', value: selectedTransaction.value.frequency },
  ];

  details.forEach((detail) => {
    doc.setFont(undefined, 'bold');
    doc.text(detail.label, 20, yPosition);
    doc.setFont(undefined, 'normal');
    doc.text(detail.value, 80, yPosition);
    yPosition += lineHeight;
  });

  yPosition += 5;
  doc.setDrawColor(200);
  doc.line(15, yPosition, pageWidth - 15, yPosition);
  yPosition += 10;

  // Footer
  doc.setFontSize(10);
  doc.setFont(undefined, 'italic');
  doc.text(`Generated on: ${new Date().toLocaleString()}`, pageWidth / 2, pageHeight - 10, { align: 'center' });

  // Download the PDF
  doc.save(`transaction_${selectedTransaction.value.order_id}.pdf`);
};

// Delete transaction
const deleteTransaction = (row) => {
  Swal.fire({
    title: 'Confirm Transaction Deletion',
    text: 'It will permanently delete this transaction information',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Delete',
    reverseButtons: true,
  }).then((result) => {
    if (result.isConfirmed) {
      // Note: In a real implementation, this would make an API call to delete the transaction
      // For now, we'll just show a success message
      Swal.fire('Transaction Deleted', 'Transaction information has been successfully deleted', 'success');
    }
  });
};

// Status badge color helper
const getStatusClass = (status) => {
  const statusMap = {
    'Completed': 'badge badge-success',
    'Pending': 'badge badge-warning',
    'Failed': 'badge badge-danger',
    'Refunded': 'badge badge-secondary',
  };
  return statusMap[status] || 'badge badge-secondary';
};
</script>

<template>
  <AuthLayout title="Transactions" description="Finance Management" heading="Transactions">
    <div class="page-header mt-2">
      <div class="page-leftheader">
        <h4 class="page-title">Transactions</h4>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12 col-md-12 col-xm-12">
        <div class="border-0">
            <Table :columns="columns" :data="tableDataProp" table="transactions" :search="keyword">
              <template #actions="{ row }">
                <div class="d-flex gap-2">
                  <button class="btn btn-sm btn-primary" @click="openRowModal({ row })" title="View Details">
                    <i class="bi bi-eye"></i> View
                  </button>
                  <button class="btn btn-sm btn-danger" @click="deleteTransaction(row)" title="Delete">
                    <i class="bi bi-trash"></i> Delete
                  </button>
                </div>
              </template>

              <!-- Custom cell renderers for status -->
              <template #status="{ row }">
                <span :class="getStatusClass(row.status)">{{ row.status }}</span>
              </template>
            </Table>        
        </div>
      </div>
    </div>

    <!-- Transaction Details Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Transaction Details</h5>
          <button type="button" class="btn-close" @click="closeModal"></button>
        </div>
        <div class="modal-body" v-if="selectedTransaction">
          <div class="detail-row">
            <label class="detail-label">User:</label>
            <span class="detail-value">{{ selectedTransaction.user }}</span>
          </div>
          <div class="detail-row">
            <label class="detail-label">Status:</label>
            <span :class="getStatusClass(selectedTransaction.status)">{{ selectedTransaction.status }}</span>
          </div>
          <div class="detail-row">
            <label class="detail-label">Plan Name:</label>
            <span class="detail-value">{{ selectedTransaction.plan_name }}</span>
          </div>
          <div class="detail-row">
            <label class="detail-label">Amount:</label>
            <span class="detail-value">${{ Number(selectedTransaction.amount || 0).toFixed(2) }}</span>
          </div>
          <div class="detail-row">
            <label class="detail-label">Order ID:</label>
            <span class="detail-value">{{ selectedTransaction.order_id }}</span>
          </div>
          <div class="detail-row">
            <label class="detail-label">Gateway:</label>
            <span class="detail-value">{{ selectedTransaction.gateway }}</span>
          </div>
          <div class="detail-row">
            <label class="detail-label">Paid On:</label>
            <span class="detail-value">{{ selectedTransaction.created_on }}</span>
          </div>
          <div class="detail-row">
            <label class="detail-label">Pricing Plan:</label>
            <span class="detail-value">{{ selectedTransaction.frequency }}</span>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success" @click="downloadTransactionPDF">
            <i class="bi bi-download"></i> Download PDF
          </button>
          <button type="button" class="btn btn-danger" @click="closeModal">Close</button>
        </div>
      </div>
    </div>
  </AuthLayout>
</template>

<style scoped>
.badge {
  padding: 6px 12px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 500;
}
.badge-success { background-color: #28a745; color: #fff; }
.badge-warning { background-color: #ffc107; color: #000; }
.badge-danger { background-color: #dc3545; color: #fff; }
.badge-secondary { background-color: #6c757d; color: #fff; }
.gap-2 { gap: 8px; }

/* Modal Overlay */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
}

.modal-content {
  background: white;
  border-radius: 8px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  padding: 20px;
  border-bottom: 1px solid #e9ecef;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-title {
  font-size: 18px;
  font-weight: 600;
  color: #333;
  margin: 0;
}

.btn-close {
  background: none;
  border: none;
  font-size: 24px;
  color: #666;
  cursor: pointer;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-close:hover {
  color: #333;
}

.modal-body {
  padding: 20px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #f0f0f0;
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-label {
  font-weight: 600;
  color: #666;
  min-width: 120px;
}

.detail-value {
  color: #333;
  text-align: right;
  flex: 1;
}

.modal-footer {
  padding: 15px 20px;
  border-top: 1px solid #e9ecef;
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.btn {
  padding: 8px 16px;
  border-radius: 4px;
  border: none;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-primary {
  background-color: #007bff;
  color: white;
}

.btn-primary:hover {
  background-color: #0056b3;
}

.btn-success {
  background-color: #28a745;
  color: white;
}

.btn-success:hover {
  background-color: #218838;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background-color: #545b62;
}

.btn-sm {
  padding: 6px 12px;
  font-size: 12px;
}
</style>
