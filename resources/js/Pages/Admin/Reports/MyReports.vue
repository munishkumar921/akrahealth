<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { computed, ref } from "vue";

const filterForm = ref({
  keyword: "",
  status: "",
  type: "",
});

const statusOptions = [
  { value: "", label: "All status" },
  { value: "ready", label: "Ready" },
  { value: "processing", label: "Processing" },
  { value: "failed", label: "Failed" },
];

const typeOptions = [
  { value: "", label: "All types" },
  { value: "lab", label: "Lab" },
  { value: "pharmacy", label: "Pharmacy" },
  { value: "financial", label: "Financial" },
];

const reportRows = ref([]);
const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(10);

const columns = [
  { label: "Report", key: "name", type: "slot", slot: "report", align: "left" },
  { label: "Type", key: "type", type: "slot", slot: "type", align: "left" },
  { label: "Generated", key: "generated_at", type: "slot", slot: "generated", align: "left" },
  { label: "Status", key: "status", type: "slot", slot: "status", align: "center" },
];

const filteredRows = computed(() => {
  let rows = [...reportRows.value];

  if (filterForm.value.status) {
    rows = rows.filter((row) => row.status === filterForm.value.status);
  }

  if (filterForm.value.type) {
    rows = rows.filter((row) => row.type === filterForm.value.type);
  }

  if (filterForm.value.keyword) {
    const needle = filterForm.value.keyword.toLowerCase();
    rows = rows.filter((row) =>
      [row.name, row.type, row.generated_at, row.status]
        .filter(Boolean)
        .join(" ")
        .toLowerCase()
        .includes(needle)
    );
  }

  return rows;
});

const visibleRows = computed(() => filteredRows.value.slice(0, perPage.value));
const activeFilterCount = computed(() =>
  Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);
const resultSummary = computed(() => {
  const total = filteredRows.value.length;
  const to = Math.min(total, visibleRows.value.length);

  if (!total) {
    return "No reports found";
  }

  return `Showing 1-${to} of ${total} reports`;
});

const totalReports = computed(() => reportRows.value.length);
const thisMonthReports = computed(() => 0);
const thisWeekReports = computed(() => 0);
const todayReports = computed(() => 0);

const clearFilters = () => {
  filterForm.value = {
    keyword: "",
    status: "",
    type: "",
  };
};

const getStatusClass = (status) => {
  const value = String(status || "").toLowerCase();

  if (value === "ready") return "status-pill--active";
  if (value === "processing") return "status-pill--pending";

  return "status-pill--inactive";
};
</script>

<template>
  <AuthLayout title="My Reports" description="View and manage your reports" heading="My Reports">
    <div class="my-reports-page">
      <div class="users-toolbar card border-0 shadow-sm mb-4">
        <div class="card-body">
          <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
            <div>
              <h3 class="mb-1">My Reports</h3>
              <p class="text-muted mb-0">{{ resultSummary }}</p>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-lg-end">
              <span v-if="hasActiveFilters" class="filter-count-badge">
                {{ activeFilterCount }} filter{{ activeFilterCount > 1 ? "s" : "" }} active
              </span>
              <button v-if="hasActiveFilters" type="button" class="btn btn-outline-secondary btn-sm"
                @click="clearFilters">
                <i class="bi bi-x-circle me-1"></i>Clear filters
              </button>
              <button class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Generate Report
              </button>
            </div>
          </div>

          <div class="row g-3 align-items-end">
            <div class="col-12 col-xl-4">
              <label class="form-label text-muted small text-uppercase mb-2">Search</label>
              <div class="input-group reports-search-control">
                <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                  <i class="bi bi-search text-muted"></i>
                </span>
                <input v-model="filterForm.keyword" type="search" class="form-control border-start-0"
                  placeholder="Search by report name, type, date, or status" />
                <button type="button" class="btn btn-primary">Search</button>
              </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-2">
              <label class="form-label text-muted small text-uppercase mb-2">Type</label>
              <select v-model="filterForm.type" class="form-select">
                <option v-for="option in typeOptions" :key="option.value" :value="option.value">
                  {{ option.label }}
                </option>
              </select>
            </div>

            <div class="col-12 col-sm-6 col-xl-2">
              <label class="form-label text-muted small text-uppercase mb-2">Status</label>
              <select v-model="filterForm.status" class="form-select">
                <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                  {{ option.label }}
                </option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
          <div class="summary-card">
            <div class="summary-value">{{ totalReports }}</div>
            <div class="summary-label">Total Reports</div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
          <div class="summary-card">
            <div class="summary-value">{{ thisMonthReports }}</div>
            <div class="summary-label">This Month</div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
          <div class="summary-card">
            <div class="summary-value">{{ thisWeekReports }}</div>
            <div class="summary-label">This Week</div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
          <div class="summary-card">
            <div class="summary-value">{{ todayReports }}</div>
            <div class="summary-label">Today</div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-body p-0 p-md-3">
          <div class="d-flex justify-content-end align-items-center px-3 px-md-0 pt-3 pt-md-0 pb-2">
            <div class="d-flex align-items-center gap-2 rows-select-wrap">
              <select id="my-reports-per-page" v-model="perPage" class="form-select form-select-sm top-page-select">
                <option v-for="option in perPageOptions" :key="option" :value="option">
                  {{ option }}
                </option>
              </select>
            </div>
          </div>

          <div v-if="!visibleRows.length" class="empty-state text-center py-5">
            <i class="bi bi-file-earmark-text fs-1 text-muted"></i>
            <p class="mt-3 text-muted mb-0">No reports found. Click "Generate Report" to create one.</p>
          </div>

          <Table v-else :columns="columns" :data="visibleRows" :search-show="false" :PageOptions="false">
            <template #report="{ row }">
              <div class="text-start">
                <div class="fw-semibold text-dark">{{ row.name || "-" }}</div>
              </div>
            </template>

            <template #type="{ row }">
              <div class="text-start">
                <div class="fw-medium text-dark">{{ row.type || "-" }}</div>
              </div>
            </template>

            <template #generated="{ row }">
              <div class="text-start">
                <div class="fw-medium text-dark">{{ row.generated_at || "-" }}</div>
              </div>
            </template>

            <template #status="{ row }">
              <span class="status-pill" :class="getStatusClass(row.status)">
                {{ row.status || "-" }}
              </span>
            </template>
          </Table>
        </div>
      </div>
    </div>
  </AuthLayout>
</template>

<style scoped>
.users-toolbar {
  border-radius: 20px;
}

.filter-count-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.45rem 0.75rem;
  border-radius: 999px;
  background: #eef2ff;
  color: #3730a3;
  font-size: 0.8rem;
  font-weight: 600;
}

.reports-search-control {
  max-width: 720px;
}

.summary-card {
  background: #ffffff;
  border-radius: 18px;
  padding: 1.25rem;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
  height: 100%;
}

.summary-value {
  font-size: 2rem;
  font-weight: 700;
  color: #0f172a;
}

.summary-label {
  color: #64748b;
  font-size: 0.95rem;
}

.top-page-select {
  min-width: 74px;
  width: 74px;
}

.rows-select-wrap {
  flex: 0 0 auto;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 84px;
  padding: 0.35rem 0.7rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 700;
}

.status-pill--active {
  background: #dcfce7;
  color: #166534;
}

.status-pill--inactive {
  background: #f1f5f9;
  color: #475569;
}

.status-pill--pending {
  background: #fef3c7;
  color: #92400e;
}
</style>
