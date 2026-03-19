<script setup>
import { ref, onMounted } from "vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import UserList from "./UserList.vue";

defineProps({
  socialHistory: Object,
});

const activeTab = ref("lifestyle");

const setTab = (t) => {
  activeTab.value = t;
  // Store active tab in localStorage
  localStorage.setItem('socialHistoryActiveTab', t);
};

// Restore active tab from localStorage on component mount
onMounted(() => {
  const storedTab = localStorage.getItem('socialHistoryActiveTab');
  if (storedTab && ['lifestyle', 'habits', 'mental'].includes(storedTab)) {
    activeTab.value = storedTab;
  }
});
</script>

<template>
<AuthLayout title="Social History" description="Manage patient social history" heading="Social History">
    <!-- Section header -->
    <div class="d-flex justify-content-between divider pb-2">
      <div class="d-flex align-items-center gap-3 pl-2">
        <h3 class="d-flex align-items-center pt-2">Social History</h3>
      </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-pills mb-3" role="tablist">
      <li class="nav-item">
        <a class="nav-link rounded-pill px-4" :class="{ active: activeTab === 'lifestyle' }" href="#" role="tab"
          @click.prevent="setTab('lifestyle')">
          Lifestyle
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link rounded-pill px-4" :class="{ active: activeTab === 'habits' }" href="#" role="tab"
          @click.prevent="setTab('habits')">
          Habits
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link rounded-pill px-4" :class="{ active: activeTab === 'mental' }" href="#" role="tab"
          @click.prevent="setTab('mental')">
          Mental Health
        </a>
      </li>
    </ul>

    <!-- Pass tab and social history data down -->
    <UserList :activeTab="activeTab" :socialHistory="socialHistory" />
  </AuthLayout>
</template>

<style scoped>
.nav-pills .nav-link {
  border-radius: 50px;
  color: #333;
  background-color: #f5f7fa;
  margin-right: 10px;
}

.nav-pills .nav-link.active {
  background-color: #09acff !important;
  color: #fff;
  font-weight: 500;
}

/* Mobile responsiveness for tabs */
@media (max-width: 768px) {
  .nav {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    flex-wrap: nowrap;
    padding-bottom: 0.5rem;
  }

  .nav-pills {
    flex-wrap: nowrap;
  }

  .nav-item {
    flex-shrink: 0;
    margin-right: 0.5rem;
  }

  .nav-pills .nav-link {
    padding: 0.5rem 1rem;
    margin-right: 0.5rem;
  }
}
</style>
