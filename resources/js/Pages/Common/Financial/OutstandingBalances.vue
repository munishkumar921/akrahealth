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

const currentTab = ref("outstanding_balances");
 const columns=[
	{ label: 'id', key: 'patient_id' },
	{ label: 'Patient Name', key: 'patient_name' },
	{ label: 'Balance', key: 'balance' },
	{ label: 'Notes', key: 'billing_notes' },
 ];
  const onCellClick = ({ row }) => {
    router.get(route('doctor.billing.index', {type:'encounter',patient_id:row.id}));
 };
 
 </script>

<template>
<AuthLayout2 title="Outstanding Balances" description="View patient outstanding balances" heading="Outstanding Balances">
		<div class="row">
		 <Tab :currentTab="currentTab"/>


			<div class="col-lg-9 card">
 				<Table :columns="columns" @cell-click="onCellClick"  :data="{data:bills}" :search="keyword" class="cursor-pointer"/>
					
			</div>
		</div>
	</AuthLayout2>
</template>
 