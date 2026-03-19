<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import InputError from "@/Components/InputError.vue";
import DatePicker from "@/Components/Common/Input/BaseDatePicker.vue";

const props = defineProps({
	row: {},
});

const showModal = ref(false);

const form = useForm({
	slug: "",
	test: "",
	diagnosis_codes: "",
	laboratory_provider: "",
	referral_provider: "",
	pending_date: "",
	insurance: "",
	notes: "",
});

const openModal = () => {
	showModal.value = true;
	document.body.style.overflow = 'hidden';
};

const closeModal = () => {
	showModal.value = false;
	form.reset();
	document.body.style.overflow = 'auto';
};

const submit = () => {
	form.post(route("doctor.order.store"), {
		onSuccess: () => {
			closeModal();
		},
	});
};

defineExpose({ openModal, closeModal });
</script>
<template>
	<Teleport to="body">
		<div v-if="showModal" class="modal-overlay" @click="closeModal">
			<div class="modal-container" @click.stop>
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Add Referrals Order</h5>
						<button type="button" class="close" @click="closeModal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>

					<form @submit.prevent="submit">
						<div class="modal-body">
							<div class="iq-card-body">
								<div class="row mt-4">
									<div class="col">
										<label>Referral Details</label>
										<textarea rows="5" class="form-control" v-model="form.test"></textarea>
										<InputError class="mt-2" :message="form.errors.test" />
									</div>
								</div>
								<div class="row mt-3">
									<div class="col">
										<label>Diagnosis Codes</label>
										<input v-model="form.diagnosis_codes" type="text" class="form-control"
											placeholder="Type a few words" required />
										<InputError class="mt-2" :message="form.errors.diagnosis_codes" />
									</div>
									<div class="col">
										<label>Specialty</label>
										<select class="form-control" id="message_alert"
											v-model="form.laboratory_provider">
											<option value="shan">Shan</option>
											<option value="shogan">Razal</option>
										</select>
										<InputError class="mt-2" :message="form.errors.laboratory_provider" />
									</div>
								</div>

								<div class="row mt-3">
									<div class="col">
										<label>Referral Provider</label>
										<select class="form-control" id="message_alert"
											v-model="form.referral_provider">
											<option value="shan">Shan</option>
											<option value="shogan">Razal</option>
										</select>
										<InputError class="mt-2" :message="form.errors.laboratory_provider" />
									</div>
								</div>

								<div class="row mt-3">
									<div class="col">
										<label>Order Pending Date</label>
										<DatePicker v-model="form.pending_date" type="date" required />
										<InputError class="mt-2" :message="form.errors.pending_date" />
									</div>
									<div class="col">
										<label>Insurance</label>
										<select class="form-control" id="message_alert" v-model="form.insurance">
											<option value="client">Bill Client</option>
										</select>
										<InputError class="mt-2" :message="form.errors.insurance" />
									</div>
								</div>

								<div class="row mt-3">
									<div class="col">
										<label>Notes about Order</label>
										<textarea rows="5" class="form-control" v-model="form.notes"></textarea>
										<InputError class="mt-2" :message="form.errors.notes" />
									</div>
								</div>

								<div class="row mt-3">
									<div class="col">
										<label>Action after Saving</label>
										<select class="form-control" id="message_alert">
											<option value="save">Save Only</option>
											<option value="print">Print</option>
											<option value="queue">Add to Print Queue</option>
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="submit" class="btn btn-primary">Save</button>
							<button type="button" class="btn btn-danger" @click="closeModal">
								Close
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</Teleport>
</template>

<style scoped>
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
	max-width: 700px;
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
	border-radius: 8px 8px 0 0;
}

.modal-title {
	margin: 0;
	font-size: 1.25rem;
	font-weight: 600;
	color: #333;
}

.close {
	background: none;
	border: none;
	font-size: 1.5rem;
	font-weight: 700;
	line-height: 1;
	color: #000;
	opacity: 0.5;
	cursor: pointer;
	padding: 0;
	width: 30px;
	height: 30px;
	display: flex;
	align-items: center;
	justify-content: center;
	border-radius: 50%;
	transition: all 0.2s;
}

.close:hover {
	opacity: 1;
	background-color: rgba(0, 0, 0, 0.1);
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
	border-radius: 0 0 8px 8px;
}

.form-control {
	border: 1px solid #ced4da;
	border-radius: 4px;
	padding: 0.375rem 0.75rem;
	font-size: 1rem;
	line-height: 1.5;
	transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
	width: 100%;
}

.form-control:focus {
	border-color: #80bdff;
	outline: 0;
	box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

label {
	font-weight: 600;
	color: #495057;
	margin-bottom: 0.5rem;
	display: block;
}

.row {
	margin-left: -15px;
	margin-right: -15px;
}

.col {
	padding-left: 15px;
	padding-right: 15px;
}

.mt-2 {
	margin-top: 0.5rem;
}

.mt-3 {
	margin-top: 1rem;
}

.mt-4 {
	margin-top: 1.5rem;
}

@media (max-width: 768px) {
	.modal-overlay {
		padding: 10px;
	}

	.modal-container {
		max-width: 100%;
		max-height: 95vh;
	}

	.modal-body {
		max-height: calc(95vh - 140px);
		padding: 1rem;
	}

	.row {
		flex-direction: column;
	}

	.col {
		margin-bottom: 1rem;
	}
}
</style>
