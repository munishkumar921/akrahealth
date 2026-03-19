<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import { ref, computed } from "vue";
import Swal from 'sweetalert2/dist/sweetalert2.js';

const props = defineProps({
    request: String,
    admins: Object,
    keyword: String,
 });

const columns = [
    { label: "Profile Picture", key: "profile_photo_url", type: "image" },
    { label: "Name", key: "name" },
    { label: "Email", key: "email" },
    { label: "Mobile", key: "mobile" },
    { label: "Created At", key: "created_at" },
    { label: "Status", key: "is_active", type: "toggle" },
];

const removeRow = (row) => {
    Swal.fire(confirmSettings('Are you sure to delete this data?', 'You want be get it back')).then((result) => {
        if (result.isConfirmed) {
            const form = useForm({});
            form.delete(route('admin.admins.destroy', row.id));
        }
    });
};

const goToAddEncounter = () => {
    router.visit(route("admin.admins.create"));
};

const buttons = [
    {
        label: "Add Admins",
        function: goToAddEncounter,
        icon: "bi bi-plus-circle",
    },
];

const filterActive = ref(usePage().props?.request?.filterActive);
const filterInactive = ref(usePage().props?.request?.filterInactive);
const byStatus = () => {

    const searchForm = useForm({
        filterActive: filterActive.value,
        filterInactive: filterInactive.value,
        search: usePage().props?.request?.search
    });
    searchForm.get(route(route().current()));
}
</script>

<template>
    <AuthLayout title="Admins" description="Admins" heading="Admins">
        <div class="d-flex align-items-center justify-content-between pl-4">
            <h3 class="d-flex align-items-center text-xl mb-0">Admins</h3>

            <div class="d-flex align-items-center gap-3">

                <div class="d-flex align-items-center gap-3">
                    <div class="form-check d-flex align-items-center gap-2 m-0">
                        <input id="flt-active" type="checkbox" @change="byStatus()"
                            class="status-check status-check--green" v-model="filterActive" />
                        <label class="form-check-label" for="flt-active">Active</label>
                    </div>
                    <div class="form-check d-flex align-items-center gap-2 m-0">
                        <input id="flt-inactive" type="checkbox" @change="byStatus()"
                            class="status-check status-check--grey" v-model="filterInactive" />
                        <label class="form-check-label" for="flt-inactive">Inactive</label>
                    </div>
                </div>

                <ActionButtons :actionButtons="buttons" />
            </div>
        </div>

        <Table :columns="columns" :data="props.admins" table="users" :search="props.keyword" >
            <template #actions="{ row }">
                <div class="d-flex gap-2">
                    <Link :href="route('admin.admins.edit', row.id)" class="icon-btn btn btn-success">
                    <i class="bi bi-pencil"></i>
                    </Link>
                    <button class="icon-btn btn btn-primary" @click="removeRow(row)" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </template>
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

.ah-toast--warning {
    background: #f59e0b;
}

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
    background-color: #06c270;
}

.status-check--grey:checked {
    border-color: #9ca3af;
    background-color: #9ca3af;
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
