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

