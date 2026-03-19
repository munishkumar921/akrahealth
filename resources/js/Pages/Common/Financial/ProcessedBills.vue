<script setup>
import { Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import AuthLayout2 from "@/Layouts/AuthLayout2.vue";
import Table from "@/Components/Table/Table.vue";
import Modal from "@/Components/Common/Modal.vue";
import { useForm } from "@inertiajs/vue3";
import AddPaymentModal from "@/Pages/Modals/AddBillingPayment.vue";
import Tab from "./Tab.vue";

const props = defineProps({
	bills: Object,
	keyword: String,
 });
const currentTab = ref("processed_bills");
const isOpenModal=ref(false);

const columns = [
	{ label: 'Date', key: 'date_of_service' },
	{ label: 'Patient Name', key: 'patient_name' },
	{ label: 'Chief Complaint', key: 'chief_complaint' },
	{ label: 'Charges', key: 'charges' },
	{ label: 'Total Balance', key: 'total_balance' },
	{ label: 'Date Processed', key: 'date_processed' },
];
const selectedRow = ref(null);
 const openAddPaymentModal = (row) => {
    selectedRow.value = row;
    isOpenModal.value = true;

 };
const closeAddPaymentModal = () => {
    isOpenModal.value = false;
	selectedRow.value = null;

 };

 const onCellClick = ({ row }) => {
    router.get(route('doctor.billing_payment_history', row.id));
 };
 
</script>

<template>
<AuthLayout2 title="Processed Bills" description="View processed bills" heading="Processed Bills">
		<div class="row">
			<Tab :currentTab="currentTab"/>
 			<div class="col-lg-9 card">
				<Table :columns="columns" :data="bills" :search="keyword" @cell-click="onCellClick" class="cursor-pointer">
					<template #actions="{ row }">
					 
					<button class="btn btn-primary" @click="router.get(route('doctor.billing_payment_history',row.id))" title="Payment History">
						<i class="fa fa-history fa-lg"></i>
					</button>
					<button class="btn btn-danger" @click="openAddPaymentModal(row)" title="Make Payment">
						<i class="fa fa-usd fa-lg"></i>
					</button>
					<button class="btn btn-danger" @click="router.get(route('doctor.finance.financial_resubmit', {id: row.encounter_id}))" title="Resubmit Bill">
						<i class="fa fa-repeat fa-lg"></i>
					</button>
				</template>
					 <template #empty>
						<tr>
							<td colspan="7" class="text-center">No processed bills found.</td>
						</tr>
					</template>
				</Table>
			</div>
		</div>
 		         <Modal :isOpen="isOpenModal" @close="closeAddPaymentModal" title="Add Payment" size="xl">
              <AddPaymentModal 
                 :billingData="selectedRow"
                :record-type="currentTab"
                @close="closeAddPaymentModal" 
                @success="closeAddPaymentModal"
            />
         </Modal>
	</AuthLayout2>
</template>
