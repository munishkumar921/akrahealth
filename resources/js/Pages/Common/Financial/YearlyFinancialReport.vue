<script setup>
import { router } from "@inertiajs/vue3";
import { ref } from "vue";
import AuthLayout2 from "@/Layouts/AuthLayout2.vue";
import Table from "@/Components/Table/Table.vue";
import Tab from "./Tab.vue";

const props = defineProps({
	bills: Object,
	keyword: String,
});

const currentTab = ref("yearly_financial_report");
 const columns = [
	{ label: 'Year', key: 'year' },
	{ label: 'Patients Seen', key: 'patients_seen' },
	{ label: 'Total Billed', key: 'total_billed' },
	{ label: 'Total Payments', key: 'total_payments' },
	{ label: 'DNKA', key: 'dnka' },
	{ label: 'LMC', key: 'lmc' },

];
   const onCellClick = ({ row }) => {
    router.get(route('doctor.finance.financial_insurance', {date:row?.year}));
 };
</script>

<template>
<AuthLayout2 title="Yearly Financial Report" description="View yearly financial reports" heading="Yearly Financial Report">
		<div class="row">
					<Tab :currentTab="currentTab"/>


			<div class="col-lg-9 card">
				<Table :columns="columns" :data="{ data: bills }" :search="keyword" @cell-click="onCellClick" class="pointer">
				</Table>

			</div>
		</div>
	</AuthLayout2>
</template>
 