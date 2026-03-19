<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import { route } from 'ziggy-js';

const props = defineProps({
	isOpen: Boolean,
	order: Object,
	labs: {
		type: Array,
		default: () => []
	}
});

const emit = defineEmits(['close', 'assigned', 'error']);

const selectedLabId = ref('');
const loading = ref(false);
const error = ref('');

// Reset selected lab when modal opens
watch(() => props.isOpen, (newVal) => {
	if (newVal) {
		selectedLabId.value = props.order?.lab_id || '';
		error.value = '';
	}
});

// Don't show assign option if already assigned
const availableLabs = computed(() => {
	return props.labs.filter(lab => lab.id !== props.order?.lab_id);
});

const canAssign = computed(() => {
	return selectedLabId.value && selectedLabId.value !== props.order?.lab_id;
});

const assignOrder = async () => {
	if (!canAssign.value || loading.value) return;
	
	loading.value = true;
	error.value = '';
	
	try {
		const response = await axios.post(route('admin.lab-orders.assign', props.order.id), {
			lab_id: selectedLabId.value
		});
		
		if (response.data.success) {
			emit('assigned', response.data.order);
			emit('close');
		} else {
			error.value = response.data.message || 'Failed to assign order';
			emit('error', error.value);
		}
	} catch (err) {
		console.error('Error assigning lab order:', err);
		error.value = err.response?.data?.message || 'Failed to assign order. Please try again.';
		emit('error', error.value);
	} finally {
		loading.value = false;
	}
};

const closeModal = () => {
	error.value = '';
	emit('close');
};
</script>

<template>
	<Teleport to="body">
		<div v-if="isOpen" class="modal-overlay" @click="closeModal">
			<div class="modal-container" @click.stop>
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Assign Lab Order</h5>
						<button type="button" class="close" @click="closeModal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>

					<div class="modal-body">
						<!-- Order Info -->
						<div class="alert alert-info mb-3">
							<div class="d-flex align-items-center">
								<i class="fas fa-info-circle me-2"></i>
								<div>
									<strong>Order #{{ order?.id }}</strong>
									<div class="small">
										Patient: {{ order?.patient || 'N/A' }}<br>
										Doctor: {{ order?.doctor || 'N/A' }}
									</div>
								</div>
							</div>
						</div>

						<!-- Current Assignment -->
						<div v-if="order?.lab_id" class="mb-3">
							<label class="form-label text-muted">Currently Assigned To</label>
							<div class="form-control bg-light">
								{{ order?.lab || 'N/A' }}
							</div>
						</div>

						<!-- Lab Selection -->
						<div class="mb-3">
							<label class="form-label">Select Laboratory <span class="text-danger">*</span></label>
							<select 
								v-model="selectedLabId" 
								class="form-select"
								:class="{ 'is-invalid': error }"
							>
								<option value="" disabled>Choose a laboratory...</option>
								<option 
									v-for="lab in labs" 
									:key="lab.id" 
									:value="lab.id"
									:disabled="lab.id === order?.lab_id"
								>
									{{ lab.name }}
									<span v-if="lab.id === order?.lab_id">(Current)</span>
								</option>
							</select>
							<div v-if="error" class="invalid-feedback">{{ error }}</div>
						</div>

						<!-- No labs available -->
						<div v-if="labs.length === 0" class="alert alert-warning">
							<i class="fas fa-exclamation-triangle me-2"></i>
							No laboratories available for assignment.
						</div>
					</div>

					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" @click="closeModal" :disabled="loading">
							Cancel
						</button>
						<button 
							type="button" 
							class="btn btn-primary" 
							@click="assignOrder"
							:disabled="!canAssign || loading"
						>
							<span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
							{{ loading ? 'Assigning...' : 'Assign Order' }}
						</button>
					</div>
				</div>
			</div>
		</div>
	</Teleport>
</template>

<style scoped>
.modal-content { border-radius: 12px; }
.modal-title { font-size: 20px; }
.form-label { font-weight: 600; color: #374151; }
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
	max-width: 500px;
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
.modal-title { font-size: 1.25rem; font-weight: 600; color: #333; }
.close {
	background: none; border: none; font-size: 1.5rem; line-height: 1;
	color: #000; opacity: .5; cursor: pointer; padding: 0; width: 30px; height: 30px; border-radius: 50%;
}
.close:hover { opacity: 1; background-color: rgba(0,0,0,.1); }
.modal-body {
	flex: 1; overflow-y: auto; padding: 1.5rem;
}
.modal-footer {
	display: flex; justify-content: flex-end; gap: 10px;
	padding: 1rem 1.5rem; border-top: 1px solid #dee2e6; background-color: #f8f9fa;
}
</style>
