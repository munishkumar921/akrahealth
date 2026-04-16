<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, Link, useForm, usePage } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { ref } from "vue";

/* ---------- PROPS ---------- */
const props = defineProps({
    keyword: String,
    doctors: Object,
});

/* ---------- TABLE COLUMNS ---------- */
const columns = [
    { label: "Profile Picture", key: "user.profile_photo_url", type: "image" },
    { label: "Name", key: "user.name" },
    { label: "Email", key: "user.email" },
    { label: "Mobile", key: "user.mobile" },
    { label: "Specialty", key: "specialty" },
    { label: "Created At", key: "created_at" },
    { label: "Status", key: "is_active", type: "toggle", onToggle: (row) => toggleStatus(row) },
];

/* ---------- FILTER STATE (FIXED — no duplicates) ---------- */
const filterActive = ref(usePage().props?.request?.filterActive ?? true);
const filterInactive = ref(usePage().props?.request?.filterInactive ?? true);

/* ---------- DELETE ROW ---------- */
const removeRow = (row) => {
    Swal.fire(
        confirmSettings("Are you sure to delete this data?", "You won't be able to get it back")
    ).then((result) => {
        if (result.isConfirmed) {
            useForm({}).delete(route("admin.doctors.destroy", row.id));
        }
    });
};

/* ---------- ROUTE TO CREATE PAGE ---------- */
const createRoute = () => {
    router.visit(route("admin.doctors.create"));
};

/* ---------- TOP ACTION BUTTONS ---------- */
const buttons = [
    {
        label: "Add Doctors",
        function: createRoute,
        icon: "bi bi-plus-circle",
    },
];

/* ---------- TOGGLE STATUS ---------- */
const toggleStatus = (row) => {
    const form = useForm({
        is_active: !row.is_active,
    });

    form.put(route("admin.doctors.update", row.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Optionally refresh the page or update the local state
        },
    });
};

/* ---------- FILTER BY STATUS ---------- */
const byStatus = () => {
    const searchForm = useForm({
        filterActive: filterActive.value,
        filterInactive: filterInactive.value,
        search: usePage().props?.request?.search,
    });

    searchForm.get(route(route().current()));
};
</script>


<template>
    <AuthLayout title="Doctors" description="Doctors" heading="Doctors">

        <!-- ================= HEADER ================= -->
        <div class="">

            <!-- ================= DESKTOP VIEW - Title and Controls in Same Row ================= -->
            <div class="d-none d-md-flex align-items-center justify-content-between mb-3">

                <!-- Title -->
                <h3 class="text-xl mb-0">Doctors</h3>

                <!-- Status Filters and Add Button -->
                <div class="d-flex align-items-center gap-3">
                    <div class="form-check d-flex align-items-center gap-2 m-0">
                        <input
                            id="flt-active"
                            type="checkbox"
                            class="status-check status-check--green"
                            v-model="filterActive"
                            @change="byStatus()" />
                        <label class="mt-2" for="flt-active">Active</label>
                    </div>

                    <div class="form-check d-flex align-items-center gap-2 m-0">
                        <input
                            id="flt-inactive"
                            type="checkbox"
                            class="status-check status-check--grey"
                            v-model="filterInactive"
                            @change="byStatus()" />
                        <label class="mt-2" for="flt-inactive">Inactive</label>
                    </div>

                    <!-- Add Button -->
                    <ActionButtons :actionButtons="buttons" />
                </div>
            </div>

            <!-- ================= MOBILE VIEW - Title ================= -->
            <div class="d-md-none">
                <h3 class="text-xl mb-3">Doctors</h3>
            </div>

           
            <div class="d-md-none">

                <!-- Active / Inactive / Add -->
                <div class="d-flex align-items-center justify-content-between mb-3">

                    <div class="d-flex gap-3">
                        <label class="d-flex align-items-center gap-1">
                            <input
                                type="checkbox"
                                class="status-check status-check--green"
                                v-model="filterActive"
                                @change="byStatus()" />
                            Active
                        </label>

                        <label class="d-flex align-items-center gap-1">
                            <input
                                type="checkbox"
                                class="status-check status-check--grey"
                                v-model="filterInactive"
                                @change="byStatus()" />
                            Inactive
                        </label>
                    </div>

                    <button
                        class="btn btn-primary btn-sm"
                        @click="createRoute">
                        <i class="bi bi-plus"> </i>Add Doctor
                    </button>
                </div>

               
            </div>
        </div>

        <!-- ================= TABLE + PAGINATION ================= -->
        <div class="table-responsive">
            <Table
                :columns="columns"
                :data="doctors"
                table="doctors"
                :search="keyword"
            >
                <template #actions="{ row }">
                    <div class="d-flex gap-2">
                        <Link
                            class="icon-btn btn btn-success"
                            :href="route('admin.doctors.edit', row.id)">
                            <i class="bi bi-pencil"></i>
                        </Link>

                        <button
                            class="icon-btn btn btn-danger"
                            @click="removeRow(row)"
                            title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </template>
            </Table>
        </div>

    </AuthLayout>
</template>

