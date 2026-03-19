<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router } from "@inertiajs/vue3";
import { ref, onMounted, computed } from "vue";

/* --------- props --------- */
const props = defineProps({
	bankAccounts: {
		type: Object,
		default: () => ({
			data: [],
			current_page: 1,
			last_page: 1,
			total: 0,
		}),
	},
	keyword: {
		type: String,
		default: "",
	},
	filters: {
		type: Object,
		default: () => ({
			status: "",
		}),
	},
	success: {
		type: String,
		default: "",
	},
});

/* --------- form state (UI only) --------- */
const form = ref({
	account_holder_name: "",
	bank_name: "",
	account_number: "",
	ifsc_code: "",
	branch_address: "",
	account_type: "savings",
	is_primary: false,
	is_active: true,
});

const editForm = ref({
	id: null,
	account_holder_name: "",
	bank_name: "",
	account_number: "",
	ifsc_code: "",
	branch_address: "",
	account_type: "savings",
	is_primary: false,
	is_active: true,
});

/* --------- error state --------- */
const errors = ref({
	account_holder_name: "",
	bank_name: "",
	account_number: "",
	ifsc_code: "",
	branch_address: "",
	account_type: "",
});

const editErrors = ref({
	account_holder_name: "",
	bank_name: "",
	account_number: "",
	ifsc_code: "",
	branch_address: "",
	account_type: "",
});

const isCreating = ref(false);
const isEditing = ref(false);
const showSuccess = ref(false);
const loading = ref(false);
const showDeleteModal = ref(false);
const accountToDelete = ref(null);

const accountTypes = [
	{ label: "Savings", value: "savings" },
	{ label: "Current", value: "current" },
];

const statusOptions = [
	{ label: "All", value: "" },
	{ label: "Active", value: "active" },
	{ label: "Inactive", value: "inactive" },
	{ label: "Primary", value: "primary" },
];

/* --------- table data computed from props --------- */
const bankAccountsList = computed(() => {
	return props.bankAccounts.data.map((account) => ({
		id: account.id,
		account_holder_name: account.account_holder_name,
		bank_name: account.bank_name,
		account_number: account.masked_account_number || "****" + account.account_number.slice(-4),
		ifsc_code: account.ifsc_code,
		branch_address: account.branch_address || "-",
		account_type: account.account_type,
		is_primary: account.is_primary,
		is_active: account.is_active,
		created_at: account.created_at,
	}));
});

/* --------- validation functions --------- */
const validateAccountHolderName = (name) => {
	if (!name || name.trim() === "") {
		return "Account holder name is required";
	}
	if (name.trim().length < 2) {
		return "Account holder name must be at least 2 characters";
	}
	if (name.trim().length > 255) {
		return "Account holder name must not exceed 255 characters";
	}
	// Only allow letters, spaces, apostrophes, hyphens, and periods
	const validPattern = /^[a-zA-Z][a-zA-Z\s\-'.]*$/;
	if (!validPattern.test(name.trim())) {
		return "Account holder name can only contain letters, spaces, hyphens, apostrophes, and periods";
	}
	return "";
};

const validateBankName = (name) => {
	if (!name || name.trim() === "") {
		return "Bank name is required";
	}
	if (name.trim().length < 2) {
		return "Bank name must be at least 2 characters";
	}
	if (name.trim().length > 255) {
		return "Bank name must not exceed 255 characters";
	}
	// Only allow letters, spaces, and basic punctuation
	const validPattern = /^[a-zA-Z][a-zA-Z\s\-'&.]*$/;
	if (!validPattern.test(name.trim())) {
		return "Bank name can only contain letters, spaces, hyphens, ampersands, apostrophes, and periods";
	}
	return "";
};

const validateAccountNumber = (number) => {
	if (!number || number.trim() === "") {
		return "Account number is required";
	}
	// Remove any spaces or special characters except alphanumeric
	const cleaned = number.replace(/[^a-zA-Z0-9]/g, "");
	if (cleaned.length < 9) {
		return "Account number must be at least 9 characters";
	}
	if (cleaned.length > 18) {
		return "Account number must not exceed 18 characters";
	}
	// Should contain at least some numbers
	if (!/\d/.test(cleaned)) {
		return "Account number must contain at least one digit";
	}
	return "";
};

const validateIfscCode = (code) => {
	if (!code || code.trim() === "") {
		return "IFSC code is required";
	}
	// Indian IFSC format: 4 uppercase letters + 7 alphanumeric characters (usually ends with digits)
	const ifscPattern = /^[A-Z]{4}0[A-Z0-9]{6}$/i;
	const cleanedCode = code.replace(/\s/g, "").toUpperCase();
	if (!ifscPattern.test(cleanedCode)) {
		return "Invalid IFSC code format (e.g., HDFC0001234)";
	}
	return "";
};

const validateForm = () => {
	errors.value.account_holder_name = validateAccountHolderName(form.value.account_holder_name);
	errors.value.bank_name = validateBankName(form.value.bank_name);
	errors.value.account_number = validateAccountNumber(form.value.account_number);
	errors.value.ifsc_code = validateIfscCode(form.value.ifsc_code);

	// Check if any errors exist
	return !errors.value.account_holder_name &&
		!errors.value.bank_name &&
		!errors.value.account_number &&
		!errors.value.ifsc_code;
};

const validateEditForm = () => {
	editErrors.value.account_holder_name = validateAccountHolderName(editForm.value.account_holder_name);
	editErrors.value.bank_name = validateBankName(editForm.value.bank_name);
	editErrors.value.account_number = validateAccountNumber(editForm.value.account_number);
	editErrors.value.ifsc_code = validateIfscCode(editForm.value.ifsc_code);

	// Check if any errors exist
	return !editErrors.value.account_holder_name &&
		!editErrors.value.bank_name &&
		!editErrors.value.account_number &&
		!editErrors.value.ifsc_code;
};

/* --------- real-time validation on blur --------- */
const validateField = (field, value, isEdit = false) => {
	const errorObj = isEdit ? editErrors : errors;
	
	switch (field) {
		case "account_holder_name":
			errorObj.value.account_holder_name = validateAccountHolderName(value);
			break;
		case "bank_name":
			errorObj.value.bank_name = validateBankName(value);
			break;
		case "account_number":
			errorObj.value.account_number = validateAccountNumber(value);
			break;
		case "ifsc_code":
			errorObj.value.ifsc_code = validateIfscCode(value);
			break;
	}
};

/* --------- computed properties for validation status --------- */
const isFormValid = computed(() => {
	return validateForm();
});

const isEditFormValid = computed(() => {
	return validateEditForm();
});

/* --------- load initial values from props --------- */
const loadFormValues = (account = null) => {
	// Reset errors when loading form
	errors.value = {
		account_holder_name: "",
		bank_name: "",
		account_number: "",
		ifsc_code: "",
		branch_address: "",
		account_type: "",
	};
	editErrors.value = {
		account_holder_name: "",
		bank_name: "",
		account_number: "",
		ifsc_code: "",
		branch_address: "",
		account_type: "",
	};
	
	if (account) {
		editForm.value = {
			id: account.id,
			account_holder_name: account.account_holder_name,
			bank_name: account.bank_name,
			account_number: account.account_number,
			ifsc_code: account.ifsc_code,
			branch_address: account.branch_address || "",
			account_type: account.account_type,
			is_primary: account.is_primary,
			is_active: account.is_active,
		};
	} else {
		editForm.value = {
			id: null,
			account_holder_name: "",
			bank_name: "",
			account_number: "",
			ifsc_code: "",
			branch_address: "",
			account_type: "savings",
			is_primary: false,
			is_active: true,
		};
	}
};

onMounted(() => {
	if (props.success) {
		showSuccess.value = true;
		setTimeout(() => {
			showSuccess.value = false;
		}, 5000);
	}
});

/* --------- edit mode --------- */
const startCreate = () => {
	loadFormValues();
	isCreating.value = true;
	isEditing.value = false;
};

const startEdit = (account) => {
	loadFormValues(account);
	isEditing.value = true;
	isCreating.value = false;
};

const cancelCreate = () => {
	isCreating.value = false;
	loadFormValues();
};

const cancelEdit = () => {
	isEditing.value = false;
	loadFormValues();
	resetForm();
};

/* --------- submit (UI only) --------- */
const submitCreate = () => {
	if (!validateForm()) {
		// Focus on first error field
		if (errors.value.account_holder_name) {
			document.querySelector('[name="account_holder_name_create"]')?.focus();
		} else if (errors.value.bank_name) {
			document.querySelector('[name="bank_name_create"]')?.focus();
		} else if (errors.value.account_number) {
			document.querySelector('[name="account_number_create"]')?.focus();
		} else if (errors.value.ifsc_code) {
			document.querySelector('[name="ifsc_code_create"]')?.focus();
		}
		return;
	}
	
	loading.value = true;

	router.post(
		route("admin.bank-accounts.store"),
		{
			account_holder_name: form.value.account_holder_name.trim(),
			bank_name: form.value.bank_name.trim(),
			account_number: form.value.account_number.replace(/[^a-zA-Z0-9]/g, ""),
			ifsc_code: form.value.ifsc_code.replace(/\s/g, "").toUpperCase(),
			branch_address: form.value.branch_address,
			account_type: form.value.account_type,
			is_primary: form.value.is_primary,
			is_active: form.value.is_active,
		},
		{
			preserveScroll: true,
			onSuccess: () => {
				loading.value = false;
				isCreating.value = false;
				showSuccess.value = true;
				setTimeout(() => {
					showSuccess.value = false;
				}, 5000);
				resetForm();
			},
			onError: () => {
				loading.value = false;
			},
		}
	);
};

const submitEdit = () => {
	if (!validateEditForm()) {
		// Focus on first error field
		if (editErrors.value.account_holder_name) {
			document.querySelector('[name="account_holder_name_edit"]')?.focus();
		} else if (editErrors.value.bank_name) {
			document.querySelector('[name="bank_name_edit"]')?.focus();
		} else if (editErrors.value.account_number) {
			document.querySelector('[name="account_number_edit"]')?.focus();
		} else if (editErrors.value.ifsc_code) {
			document.querySelector('[name="ifsc_code_edit"]')?.focus();
		}
		return;
	}
	
	loading.value = true;

	router.put(
		route("admin.bank-accounts.update", editForm.value.id),
		{
			account_holder_name: editForm.value.account_holder_name.trim(),
			bank_name: editForm.value.bank_name.trim(),
			account_number: editForm.value.account_number.replace(/[^a-zA-Z0-9]/g, ""),
			ifsc_code: editForm.value.ifsc_code.replace(/\s/g, "").toUpperCase(),
			branch_address: editForm.value.branch_address,
			account_type: editForm.value.account_type,
			is_primary: editForm.value.is_primary,
			is_active: editForm.value.is_active,
		},
		{
			preserveScroll: true,
			onSuccess: () => {
				loading.value = false;
				isEditing.value = false;
				showSuccess.value = true;
				setTimeout(() => {
					showSuccess.value = false;
				}, 5000);
			},
			onError: () => {
				loading.value = false;
			},
		}
	);
};

const resetForm = () => {
	form.value = {
		account_holder_name: "",
		bank_name: "",
		account_number: "",
		ifsc_code: "",
		branch_address: "",
		account_type: "savings",
		is_primary: false,
		is_active: true,
	};
	errors.value = {
		account_holder_name: "",
		bank_name: "",
		account_number: "",
		ifsc_code: "",
		branch_address: "",
		account_type: "",
	};
};

/* --------- delete --------- */
const confirmDelete = (account) => {
	accountToDelete.value = account;
	showDeleteModal.value = true;
};

const deleteBankAccount = () => {
	if (!accountToDelete.value) return;

	router.delete(
		route("admin.bank-accounts.destroy", accountToDelete.value.id),
		{
			preserveScroll: true,
			onSuccess: () => {
				showDeleteModal.value = false;
				accountToDelete.value = null;
				showSuccess.value = true;
				setTimeout(() => {
					showSuccess.value = false;
				}, 5000);
			},
		}
	);
};

/* --------- set primary --------- */
const setAsPrimary = (id) => {
	router.put(
		route("admin.bank-accounts.primary", id),
		{},
		{
			preserveScroll: true,
			onSuccess: () => {
				showSuccess.value = true;
				setTimeout(() => {
					showSuccess.value = false;
				}, 5000);
			},
		}
	);
};

/* --------- filter --------- */
const filterStatus = ref(props.filters.status || "");

const applyFilter = () => {
	router.get(
		route("admin.bank-accounts"),
		{
			keyword: props.keyword,
			status: filterStatus.value,
		},
		{
			preserveState: true,
		}
	);
};
 
</script>

<template>
	<AuthLayout title="Bank Accounts" description="Manage your bank account details for payments" heading="Bank Account Management">
		<div class="container-fluid">
		
			<!-- Create Form -->
			<div v-if="isCreating" class="card mb-4">
				<div class="card-header d-flex justify-content-between align-items-center">
					<h5 class="mb-0">Add New Bank Account</h5>
					<button type="button" class="btn-close" @click="cancelCreate"></button>
				</div>
				<div class="card-body">
					<form @submit.prevent="submitCreate">
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Account Holder Name <span class="text-danger">*</span></label>
								<input
									v-model="form.account_holder_name"
									type="text"
									class="form-control"
									:class="{ 'is-invalid': errors.account_holder_name }"
									placeholder="Enter account holder name"
									name="account_holder_name_create"
									@blur="validateField('account_holder_name', form.account_holder_name, false)"
									required
								/>
								<div v-if="errors.account_holder_name" class="invalid-feedback d-block">
									{{ errors.account_holder_name }}
								</div>
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Bank Name <span class="text-danger">*</span></label>
								<input
									v-model="form.bank_name"
									type="text"
									class="form-control"
									:class="{ 'is-invalid': errors.bank_name }"
									placeholder="Enter bank name"
									name="bank_name_create"
									@blur="validateField('bank_name', form.bank_name, false)"
									required
								/>
								<div v-if="errors.bank_name" class="invalid-feedback d-block">
									{{ errors.bank_name }}
								</div>
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Account Number <span class="text-danger">*</span></label>
								<input
									v-model="form.account_number"
									type="text"
									class="form-control"
									:class="{ 'is-invalid': errors.account_number }"
									placeholder="Enter account number (9-18 digits)"
									name="account_number_create"
									@blur="validateField('account_number', form.account_number, false)"
									required
								/>
								<div v-if="errors.account_number" class="invalid-feedback d-block">
									{{ errors.account_number }}
								</div>
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">IFSC Code <span class="text-danger">*</span></label>
								<input
									v-model="form.ifsc_code"
									type="text"
									class="form-control"
									:class="{ 'is-invalid': errors.ifsc_code }"
									placeholder="e.g., HDFC0001234"
									name="ifsc_code_create"
									@blur="validateField('ifsc_code', form.ifsc_code, false)"
									required
								/>
								<div v-if="errors.ifsc_code" class="invalid-feedback d-block">
									{{ errors.ifsc_code }}
								</div>
								<div class="form-text">Format: 4 uppercase letters + 7 alphanumeric characters</div>
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Branch Address</label>
								<textarea
									v-model="form.branch_address"
									class="form-control"
									placeholder="Enter branch address"
									rows="2"
								></textarea>
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Account Type <span class="text-danger">*</span></label>
								<select v-model="form.account_type" class="form-select" required>
									<option v-for="type in accountTypes" :key="type.value" :value="type.value">
										{{ type.label }}
									</option>
								</select>
							</div>
							<div class="col-md-6 mb-3">
								<div class="form-check mt-4">
									<input v-model="form.is_primary" type="checkbox" class="form-check-input" id="isPrimaryCreate" />
									<label class="form-check-label" for="isPrimaryCreate">Set as primary account</label>
								</div>
							</div>
							<div class="col-md-6 mb-3">
								<div class="form-check mt-4">
									<input v-model="form.is_active" type="checkbox" class="form-check-input" id="isActiveCreate" />
									<label class="form-check-label" for="isActiveCreate">Account is active</label>
								</div>
							</div>
						</div>

						<div class="mt-4 d-flex justify-content-end gap-2">
							<button type="button" class="btn btn-secondary" @click="cancelCreate">Cancel</button>
							<button type="submit" class="btn btn-primary" :disabled="loading || !isFormValid">
								<span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
								Save Bank Account
							</button>
						</div>
					</form>
				</div>
			</div>

			<!-- Edit Form -->
			<div v-if="isEditing" class="card mb-4">
				<div class="card-header d-flex justify-content-between align-items-center">
					<h5 class="mb-0">Edit Bank Account</h5>
					<button type="button" class="btn-close" @click="cancelEdit"></button>
				</div>
				<div class="card-body">
					<form @submit.prevent="submitEdit">
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Account Holder Name <span class="text-danger">*</span></label>
								<input
									v-model="editForm.account_holder_name"
									type="text"
									class="form-control"
									:class="{ 'is-invalid': editErrors.account_holder_name }"
									placeholder="Enter account holder name"
									name="account_holder_name_edit"
									@blur="validateField('account_holder_name', editForm.account_holder_name, true)"
									required
								/>
								<div v-if="editErrors.account_holder_name" class="invalid-feedback d-block">
									{{ editErrors.account_holder_name }}
								</div>
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Bank Name <span class="text-danger">*</span></label>
								<input
									v-model="editForm.bank_name"
									type="text"
									class="form-control"
									:class="{ 'is-invalid': editErrors.bank_name }"
									placeholder="Enter bank name"
									name="bank_name_edit"
									@blur="validateField('bank_name', editForm.bank_name, true)"
									required
								/>
								<div v-if="editErrors.bank_name" class="invalid-feedback d-block">
									{{ editErrors.bank_name }}
								</div>
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Account Number <span class="text-danger">*</span></label>
								<input
									v-model="editForm.account_number"
									type="text"
									class="form-control"
									:class="{ 'is-invalid': editErrors.account_number }"
									placeholder="Enter account number (9-18 digits)"
									name="account_number_edit"
									@blur="validateField('account_number', editForm.account_number, true)"
									required
								/>
								<div v-if="editErrors.account_number" class="invalid-feedback d-block">
									{{ editErrors.account_number }}
								</div>
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">IFSC Code <span class="text-danger">*</span></label>
								<input
									v-model="editForm.ifsc_code"
									type="text"
									class="form-control"
									:class="{ 'is-invalid': editErrors.ifsc_code }"
									placeholder="e.g., HDFC0001234"
									name="ifsc_code_edit"
									@blur="validateField('ifsc_code', editForm.ifsc_code, true)"
									required
								/>
								<div v-if="editErrors.ifsc_code" class="invalid-feedback d-block">
									{{ editErrors.ifsc_code }}
								</div>
								<div class="form-text">Format: 4 uppercase letters + 7 alphanumeric characters</div>
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Branch Address</label>
								<textarea
									v-model="editForm.branch_address"
									class="form-control"
									placeholder="Enter branch address"
									rows="2"
								></textarea>
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Account Type <span class="text-danger">*</span></label>
								<select v-model="editForm.account_type" class="form-select" required>
									<option v-for="type in accountTypes" :key="type.value" :value="type.value">
										{{ type.label }}
									</option>
								</select>
							</div>
							<div class="col-md-6 mb-3">
								<div class="form-check mt-4">
									<input v-model="editForm.is_primary" type="checkbox" class="form-check-input" id="isPrimaryEdit" />
									<label class="form-check-label" for="isPrimaryEdit">Set as primary account</label>
								</div>
							</div>
							<div class="col-md-6 mb-3">
								<div class="form-check mt-4">
									<input v-model="editForm.is_active" type="checkbox" class="form-check-input" id="isActiveEdit" />
									<label class="form-check-label" for="isActiveEdit">Account is active</label>
								</div>
							</div>
						</div>

						<div class="mt-4 d-flex justify-content-end gap-2">
							<button type="button" class="btn btn-secondary" @click="cancelEdit">Cancel</button>
							<button type="submit" class="btn btn-primary" :disabled="loading || !isEditFormValid">
								<span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
								Update Bank Account
							</button>
						</div>
					</form>
				</div>
			</div>

			<!-- Bank Accounts List -->
			<div v-if="!isCreating && !isEditing" class="card">
				<div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
					<h5 class="mb-0 text-white">Bank Accounts</h5>
					<div class="d-flex gap-2 align-items-center">
						<!-- Status Filter -->
						<select v-model="filterStatus" class="form-select form-select-sm" style="width: auto;" @change="applyFilter">
							<option v-for="status in statusOptions" :key="status.value" :value="status.value">
								{{ status.label }}
							</option>
						</select>
						<button class="btn btn-primary border bg-white text-dark" @click="startCreate">
							<i class="bi bi-plus-lg"></i> Add Bank Account
						</button>
					</div>
				</div>
				<div class="card-body p-0">
					<!-- Empty State -->
					<div v-if="bankAccountsList.length === 0" class="text-center py-5">
						<i class="bi bi-bank fs-1 text-muted"></i>
						<p class="mt-3 text-muted">No bank accounts found.</p>
						<button class="btn btn-primary" @click="startCreate">
							<i class="bi bi-plus-lg"></i> Add Your First Bank Account
						</button>
					</div>

					<!-- Table -->
					<table v-else class="table table-hover mb-0">
						<thead>
							<tr>
								<th style="width: 15%">Bank</th>
								<th style="width: 18%">Account Holder</th>
								<th style="width: 15%">Account No.</th>
								<th style="width: 12%">IFSC</th>
								<th style="width: 10%">Type</th>
								<th style="width: 10%">Status</th>
								<th style="width: 20%">Actions</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="account in bankAccountsList" :key="account.id">
								<td class="fw-semibold">{{ account.bank_name }}</td>
								<td>{{ account.account_holder_name }}</td>
								<td><code>{{ account.account_number }}</code></td>
								<td><code>{{ account.ifsc_code }}</code></td>
								<td>
									<span class="badge bg-secondary">{{ account.account_type }}</span>
								</td>
								<td>
									<div class="d-flex flex-column gap-1">
										<span
											class="badge"
											:class="account.is_active ? 'bg-success' : 'bg-warning'"
										>
											{{ account.is_active ? 'Active' : 'Inactive' }}
										</span>
										<span v-if="account.is_primary" class="badge bg-primary">
											Primary
										</span>
									</div>
								</td>
								<td>
									<div class="d-flex gap-1 flex-wrap">
										<button
											v-if="!account.is_primary"
											class="btn   btn-success"
											@click="setAsPrimary(account.id)"
											title="Set as Primary"
										>
											<i class="bi bi-star"></i>
										</button>
										<button class="btn   btn-primary" @click="startEdit(account)" title="Edit">
											<i class="bi bi-pencil"></i>
										</button>
										<button class="btn  btn-danger" @click="confirmDelete(account)" title="Delete">
											<i class="bi bi-trash"></i>
										</button>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>

				<!-- Pagination -->
				<div v-if="props.bankAccounts.last_page > 1" class="card-footer d-flex justify-content-between align-items-center">
					<span class="text-muted">
						Showing {{ props.bankAccounts.from }} to {{ props.bankAccounts.to }} of {{ props.bankAccounts.total }} accounts
					</span>
					<nav>
						<ul class="pagination mb-0">
							<li class="page-item" :class="{ disabled: !props.bankAccounts.prev_page_url }">
								<button class="page-link" @click="router.get(props.bankAccounts.prev_page_url)" :disabled="!props.bankAccounts.prev_page_url">
									Previous
								</button>
							</li>
							<li class="page-item" :class="{ disabled: !props.bankAccounts.next_page_url }">
								<button class="page-link" @click="router.get(props.bankAccounts.next_page_url)" :disabled="!props.bankAccounts.next_page_url">
									Next
								</button>
							</li>
						</ul>
					</nav>
				</div>
			</div>
		</div>

		<!-- Delete Confirmation Modal -->
		<div v-if="showDeleteModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Confirm Delete</h5>
						<button type="button" class="btn-close" @click="showDeleteModal = false"></button>
					</div>
					<div class="modal-body">
						<p>Are you sure you want to delete this bank account?</p>
						<p class="mb-0 text-muted">
							<strong>{{ accountToDelete?.bank_name }}</strong><br />
							Account Holder: {{ accountToDelete?.account_holder_name }}<br />
							Account No.: {{ accountToDelete?.account_number }}
						</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" @click="showDeleteModal = false">Cancel</button>
						<button type="button" class="btn btn-danger" @click="deleteBankAccount">Delete</button>
					</div>
				</div>
			</div>
		</div>
	</AuthLayout>
</template>

