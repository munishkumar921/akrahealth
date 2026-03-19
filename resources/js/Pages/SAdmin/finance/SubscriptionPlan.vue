<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, useForm } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import Modal from "@/Components/Common/Modal.vue";
import AddsubscriptionPlan from "@/Pages/Modals/SubscriptionPlan.vue";
import { ref, computed } from "vue";

const props = defineProps({
  subscriptionPlans: Object,
  filters: Object,
  countries: Object,
  permissions:Array,
});

const columns = [
  { label: "Plan for", key: "plan_for" },
  { label: "Title", key: "title" },
  { label: "frequency", key: "frequency" },
  { label: "Price", key: "price"},
  { label: "Currency", key: "currency" },
  { label: "Status", key: "status", type: "toggle", onToggle: (row) => toggleStatus(row) },
];

const filterActive = ref(props.filters?.active !== false);
const filterInactive = ref(props.filters?.active !== true);
const keyword = ref('');

const filteredRows = computed(() => {
  if (!props.subscriptionPlans || !props.subscriptionPlans.data) return [];
  return props.subscriptionPlans.data.filter(r => {
    const isActive = !!(r.status !== undefined ? r.status : r.active);
    const matchesStatus = (filterActive.value && isActive) || (filterInactive.value && !isActive);
    const matchesSearch = !keyword.value || r.title.toLowerCase().includes(keyword.value.toLowerCase()) || r.plan_for.toLowerCase().includes(keyword.value.toLowerCase());
    return matchesStatus && matchesSearch;
  });
});
 
const removeRow = (row) => {
  if (confirm('Are you sure you want to delete this subscription plan?')) {
    router.delete(route('superAdmin.subscriptionPlan.destroy', row.id));
  }
};

// Toast state (UI-only)
const showToast = ref(false);
const toastMsg = ref("");
const toastType = ref("success"); // 'success' | 'warning'
 
const notifyStatus = (row) => {
  toastType.value = row.status ? "success" : "warning";
  toastMsg.value = row.status ? `${row.title} is now Active` : `${row.title} is now Inactive`;
  showToast.value = true;
  setTimeout(() => (showToast.value = false), 2000);
};

// Update your existing toggle handler to call notify
const toggleStatus = (row) => {
   router.post(route('superAdmin.toggle-active', row.id), {}, {
    onSuccess: () => {
      row.status = !row.status;
      row.active = row.status; // Sync active with status
      notifyStatus(row);
    }
  });
};
 

const OpenModal = ref(false);
const childComponentRef = ref(null);

// Open create modal
const addPlan = () => {
  OpenModal.value = true;
};
const openEdit=(plan)=>{
    OpenModal.value = true;
     setTimeout(() => {
        if (childComponentRef.value?.update) {
            childComponentRef.value.update(plan);
        }
    }, 10);
}

const closeModal=()=>{
  OpenModal.value=false;
}
const buttons = [
  {
    label: "Add Plan",
    function: addPlan,
    icon: "bi bi-plus-circle",
  },
];

</script>
<template>
  <AuthLayout title="Subscription Plans" description="Subscription Plans" heading="Subscription Plans">
    <div class="d-flex align-items-center justify-content-between">
      <h3 class="d-flex align-items-center text-xl mb-0">Subscription Plans</h3>

      <div class="d-flex align-items-center gap-3">
        <!-- Status Filter FIRST -->
        <div class="d-flex align-items-center gap-3">
          <div class="form-check d-flex align-items-center gap-2 m-0">
            <input id="flt-active" type="checkbox" class="status-check status-check--green" v-model="filterActive" />
            <label class="form-check-label" for="flt-active">Active</label>
          </div>
          <div class="form-check d-flex align-items-center gap-2 m-0">
            <input id="flt-inactive" type="checkbox" class="status-check status-check--grey" v-model="filterInactive" />
            <label class="form-check-label" for="flt-inactive">Inactive</label>
          </div>
        </div>

        <!-- Add Plan BUTTON AFTER filter -->
        <ActionButtons :actionButtons="buttons" />
      </div>
    </div>
 
     <!-- Create Plan Modal -->
    <Modal :isOpen="OpenModal" @close="closeModal" title="Subscription Plans" size="xl">
    <AddsubscriptionPlan ref="childComponentRef" :countries="countries" :permissions="permissions" @close="closeModal" />
    </Modal>

     <Table :columns="columns"  :data="{data:filteredRows}" :search="keyword">
      <template #actions="{ row }">
        
        <div class="d-flex gap-2">
          <button class="icon-btn btn btn-success" @click="openEdit(row)" title="Edit">
            <i class="bi bi-pencil"></i>
          </button>
          <button class="icon-btn btn btn-danger" @click="removeRow(row)" title="Delete">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      </template>
    </Table>

  </AuthLayout>
</template>
