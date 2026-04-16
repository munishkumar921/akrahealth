<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import { type } from "jquery";

const props = defineProps({
	request: Array,
	prescriptions: Object,
	keyword: String,
});

const columns = [
	{ label: "Title", key: "title" },
	{ label: "Patient", key: "patient.user.name" },
	{ label: "Doctor", key: "doctor.user.name" },
	{ label: "Prescribed At", key: "prescribed_at" },
	{ label: "Notes", key: "notes" },
	{ label: "Repeatable", key: "is_repeatable", type: "boolean" },
	{ label: "Next Visit At", key: "next_visit_at" },
];

</script>

<template>
	<AuthLayout title="Prescriptions" description="Prescriptions" heading="Prescriptions">
		<div class="d-flex align-items-center justify-content-between pl-4">
			<h3 class="d-flex align-items-center text-xl mb-0">Prescriptions</h3>

			<div class="d-flex align-items-center gap-3">
			</div>
		</div>

		<Table :columns="columns" :data="prescriptions" table="prescriptions":search="keyword">
		</Table>
	</AuthLayout>
</template>

