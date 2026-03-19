<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { computed } from 'vue';

const props = defineProps({
     medicines: Object,
     keyword: String,
});

const tableColumns = [
    {
        key: "name",
        label: "Medicine Name",
    },
    {
        key: "generic_name",
        label: "Generic Name",
    },
    {
        key: "brand_name",
        label: "Brand Name",
    },
    {
        key: "form",
        label: "Form",
    },
    {
        key: "strength",
        label: "Strength",
    },
    {
        key: "price",
        label: "Price",
    },
    {
        key: "quantity",
        label: "Stock",
    },
    {
        key: "expiry_date",
        label: "Expiry Date",
        isDate: true,
    },
];

const medicineData = computed(() => {
    const list = props.medicines?.data ?? props.medicines ?? [];

    return list.map(medicine => ({
        ...medicine,
        price: medicine?.price ? `$${medicine.price}` : '$0.00',
    }));
});
</script>
<template>
 <AuthLayout >
     <Table :columns="tableColumns" :data="{ data: medicineData }" :search="keyword" class="mt-4">

    <template #actions="{ row }">
        <button class="btn btn-primary" @click="router.visit(route('pharmacy.pharmacies.show', row.id))"><i class="bi bi-eye"></i></button>
    </template>
     </Table>
</AuthLayout>
</template>

