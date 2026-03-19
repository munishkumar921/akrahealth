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

<style scoped>
.icon-btn {
	padding: 9px 8px 6px 8px;
	border: none;
	border-radius: 12px;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	color: #fff;
	cursor: pointer;
	transition: transform .07s ease-in-out, opacity .15s ease-in-out;
}

.icon-btn:active {
	transform: scale(0.97);
}

.icon-btn--red {
	background: #ef4444;
}

.icon-btn i {
	font-size: 14px;
	line-height: 1;
}

.modal-content {
	border-radius: 12px;
}

.modal-title {
	font-size: 20px;
}

.form-label {
	font-weight: 600;
	color: #374151;
}

.modal-overlay {
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background-color: rgba(0, 0, 0, 0.5);
	display: flex;
	justify-content: center;
	align-items: center;
	z-index: 9999;
	padding: 20px;
}

.modal-container {
	background: white;
	border-radius: 8px;
	box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
	width: 100%;
	max-width: 600px;
	max-height: 90vh;
	overflow: hidden;
	display: flex;
	flex-direction: column;
}

.modal-content {
	display: flex;
	flex-direction: column;
	height: 100%;
}

.modal-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 1rem 1.5rem;
	border-bottom: 1px solid #dee2e6;
	background-color: #f8f9fa;
}

.modal-title {
	font-size: 1.25rem;
	font-weight: 600;
	color: #333;
}

.close {
	background: none;
	border: none;
	font-size: 1.5rem;
	line-height: 1;
	color: #000;
	opacity: .5;
	cursor: pointer;
	padding: 0;
	width: 30px;
	height: 30px;
	border-radius: 50%;
}

.close:hover {
	opacity: 1;
	background-color: rgba(0, 0, 0, .1);
}

.modal-body {
	flex: 1;
	overflow-y: auto;
	padding: 1.5rem;
	max-height: calc(90vh - 140px);
}

.modal-footer {
	display: flex;
	justify-content: flex-end;
	gap: 10px;
	padding: 1rem 1.5rem;
	border-top: 1px solid #dee2e6;
	background-color: #f8f9fa;
}
</style>
