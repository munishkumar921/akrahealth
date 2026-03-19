<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import { ref, computed } from "vue";

// sample activity data (demo)
const sampleActivities = ref([
  { id: 1, email: 'alice@example.com', group: 'admin', ip_address: '192.168.1.12', user_agent: 'Chrome on Windows 10', last_activity: Date.now() - 1000 * 60 * 5 },
  { id: 2, email: 'bob@example.com', group: 'user', ip_address: '203.0.113.45', user_agent: 'Firefox on Mac', last_activity: Date.now() - 1000 * 60 * 60 * 3 },
  { id: 3, email: 'carlos@example.org', group: 'manager', ip_address: '198.51.100.22', user_agent: 'Safari on iPhone', last_activity: Date.now() - 1000 * 60 * 60 * 24 },
  { id: 4, email: 'diana@example.net', group: 'user', ip_address: '10.0.0.5', user_agent: 'Edge on Windows 11', last_activity: Date.now() - 1000 * 60 * 30 },
  { id: 5, email: 'eve@example.com', group: 'admin', ip_address: '172.16.0.2', user_agent: 'Chrome on Android', last_activity: Date.now() - 1000 * 60 * 60 * 24 * 2 },
]);

function timeAgo(ts) {
  if (!ts) return '-';
  const now = Date.now();
  const diff = Math.floor((now - Number(ts)) / 1000);
  if (diff < 60) return `${diff}s ago`;
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
  return `${Math.floor(diff / 86400)}d ago`;
}

const columns = [
  { label: 'User Email', key: 'email' },
  { label: 'User Group', key: 'group' },
  { label: 'IP Address', key: 'ip_address' },
  { label: 'Connection', key: 'user_agent' },
  { label: 'Last Activity', key: 'last_activity', formatter: (v) => timeAgo(v) },
];

// table prop shaped like server paginator: { data: [...], links: [] }
const tableDataProp = computed(() => {
  return { data: sampleActivities.value, links: [] };
});

const buttons = [
  { label: 'Refresh', function: () => { /* demo: no-op */ }, icon: 'bi bi-arrow-clockwise' }
];

// open row details in a simple modal (reuse pattern)
const showModal = ref(false);
const selected = ref(null);
const openRow = ({ row }) => { selected.value = { ...row }; showModal.value = true; };
const closeModal = () => { showModal.value = false; selected.value = null; };
</script>

<template>
  <AuthLayout title="Activity Monitoring" description="User Activity Monitoring" heading="Activity Monitoring">
    <div class="d-flex align-items-center justify-content-between pl-4">
      <h3 class="d-flex align-items-center text-xl mb-0">Activity Monitoring</h3>
      <div class="d-flex align-items-center gap-3">
        <ActionButtons :actionButtons="buttons" />
      </div>
    </div>

    <Table @cell-click="openRow" :columns="columns" :data="tableDataProp" />

    <div v-if="showModal" class="modal-overlay">
      <div class="modal-container">
        <div class="modal-header">
          <h5 class="modal-title">Activity Details</h5>
          <button class="close" @click="closeModal">&times;</button>
        </div>
        <div class="modal-body">
          <p><strong>Email:</strong> {{ selected.email }}</p>
          <p><strong>Group:</strong> {{ selected.group }}</p>
          <p><strong>IP:</strong> {{ selected.ip_address }}</p>
          <p><strong>Connection:</strong> {{ selected.user_agent }}</p>
          <p><strong>Last Activity:</strong> {{ selected ? timeAgo(selected.last_activity) : '-' }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-danger" @click="closeModal">Close</button>
        </div>
      </div>
    </div>
  </AuthLayout>
</template>

<style scoped>
.modal-overlay { position: fixed; top:0; left:0; right:0; bottom:0; background: rgba(0,0,0,.45); display:flex; align-items:center; justify-content:center; z-index:9999 }
.modal-container { background:#fff; border-radius:8px; width:90%; max-width:620px; overflow:hidden }
.modal-header, .modal-footer { padding:12px 16px; border-bottom:1px solid #eee }
.modal-body { padding:16px }
.close { background:none; border:0; font-size:20px }
</style>
