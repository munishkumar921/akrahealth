<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { reactive, ref, watch } from "vue";
import { router } from '@inertiajs/vue3';

const props = defineProps({
	keyword: String,
	route: Array,
	appointments: Object,
});

const columns = [
	{ label: "Patient", key: "patient.user.name" },
	{ label: "Doctor", key: "doctor.user.name" },
	{ label: "Type", key: "appointment_mode" },
	{ label: "Appointment", key: "appointment_date" },
	{ label: "Call Start time", key: "appointment_time" },
	{ label: "Call duration", key: "duration_minutes" },
	{ label: "Reason", key: "reason" },
	{ label: "Call Status", key: "status" },
];

const filter = reactive({
	completed: false,
	pending: false,
	cancelled: false,
})

watch(filter, (newFilter) => {

    const params = new URLSearchParams(window.location.search)
    Object.keys(newFilter).forEach(key => {
        if (newFilter[key]) {
            params.set(key, 1)
        } else {
            params.delete(key)
        }
    })

    router.get(route(route().current()), Object.fromEntries(params), {
        preserveState: true,
        replace: true
    })

}, { deep: true })
</script>

<template>
	<AuthLayout title="Call Logs" description="View call logs" heading="Call Logs">
		<div class="">
			<div class="d-none d-md-flex align-items-center justify-content-between mb-3">
				<h3 class="text-xl mb-0">Call Logs</h3>
				<div class="d-flex align-items-center gap-3">
					<div class="form-check d-flex align-items-center gap-2 m-0">
						<input id="flt-completed" type="checkbox" class="status-check status-check--green"
							v-model="filter.completed" />
						<label class="mt-2" for="flt-completed">Completed</label>
					</div>
					<div class="form-check d-flex align-items-center gap-2 m-0">
						<input id="flt-pending" type="checkbox" class="status-check status-check--grey"
							v-model="filter.pending" />
						<label class="mt-2" for="flt-pending">Missed</label>
					</div>
					<div class="form-check d-flex align-items-center gap-2 m-0">
						<input id="flt-cancelled" type="checkbox" class="status-check status-check--red"
							v-model="filter.cancelled" />
						<label class="mt-2" for="flt-cancelled">Cancelled</label>
					</div>
				</div>
			</div>

			<div class="d-md-none">
				<!--  MOBILE VIEW - Title  -->
				<h3 class="text-xl mb-3">Call Logs</h3>
			</div>

			<div class="d-md-none">
				<!-- Status Filters -->
				<div class="d-flex gap-3 mb-3">
					<label class="d-flex align-items-center gap-1">
						<input type="checkbox" class="status-check status-check--green" v-model="filterCompleted" />
						Completed
					</label>

					<label class="d-flex align-items-center gap-1">
						<input type="checkbox" class="status-check status-check--grey" v-model="filterPending" />
						Missed
					</label>

					<label class="d-flex align-items-center gap-1">
						<input type="checkbox" class="status-check status-check--red" v-model="filterCancelled" />
						Cancelled
					</label>
				</div>
			</div>
		</div>

		<div class="table-responsive">
			<Table :columns="columns" :data="appointments" table="appointments" :search="keyword"
				:pagination="appointments">
			</Table>
		</div>
	</AuthLayout>
</template>

<style scoped>
.icon-btn {
	/* width: 40px;
	height: 40px; */
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

/* red-500 */
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

.ah-toast {
	position: fixed;
	top: 20px;
	right: 20px;
	z-index: 1050;
	display: inline-flex;
	align-items: center;
	gap: 10px;
	padding: 10px 14px;
	border-radius: 10px;
	color: #fff;
	box-shadow: 0 6px 20px rgba(0, 0, 0, .15);
	animation: ah-fade-in .15s ease-out;
}

.ah-toast--success {
	background: #16a34a;
}

/* green */
.ah-toast--warning {
	background: #f59e0b;
}

/* amber */
.ah-toast .bi {
	font-size: 18px;
}

@keyframes ah-fade-in {
	from {
		opacity: 0;
		transform: translateY(-6px);
	}

	to {
		opacity: 1;
		transform: translateY(0);
	}
}

/* colored checks */
.form-check-input--green:checked {
	background-color: #10b981;
	border-color: #10b981;
}

.form-check-input--blue:checked {
	background-color: #0ea5e9;
	border-color: #0ea5e9;
}

.status-check {
	appearance: none;
	width: 16px;
	height: 16px;
	border: 2px solid #d1d5db;
	/* gray-300 */
	border-radius: 4px;
	display: inline-block;
	position: relative;
	cursor: pointer;
	background: #fff;
}

.status-check:focus {
	outline: none;
	box-shadow: 0 0 0 2px rgba(59, 130, 246, .2);
}

.status-check--green:checked {
	border-color: #06c270;
	/* gray-400 */
	background-color: #06c270;
}

.status-check--grey:checked {
	border-color: #9ca3af;
	/* gray-400 */
	background-color: #9ca3af;
}

.status-check--red:checked {
	border-color: #f35353;
	/* gray-400 */
	background-color: #f35353;
}

/* tick icon */
.status-check:checked::after {
	content: "";
	position: absolute;
	left: 4px;
	top: 1px;
	width: 4px;
	height: 8px;
	border: solid #fff;
	border-width: 0 2px 2px 0;
	transform: rotate(45deg);
}
</style>
