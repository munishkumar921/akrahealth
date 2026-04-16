<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import Table from '@/Components/Table/Table.vue';
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  appointments: Object,
  filters: {
    type: Object,
    default: () => ({}),
  },
});

const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get('per_page')) || Number(props.appointments?.per_page) || 10);
const filterForm = ref({
  keyword: props.filters?.keyword || '',
  status: props.filters?.status || '',
});

const goBackToSchedule = () => {
  router.visit(route('patient.book.appointment'));
};

const buildQuery = (overrides = {}) => {
  const query = {
    ...filterForm.value,
    per_page: perPage.value,
    ...overrides,
  };

  return Object.fromEntries(
    Object.entries(query).filter(([, value]) => value !== '' && value !== null && value !== undefined)
  );
};

const applyFilters = () => {
  router.get(route('patient.booking.list'), buildQuery({ page: 1 }), {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
};

const clearFilters = () => {
  filterForm.value = {
    keyword: '',
    status: '',
  };

  router.get(route('patient.booking.list'), {}, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
};

const updatePerPage = () => {
  router.get(route('patient.booking.list'), buildQuery({ per_page: perPage.value, page: 1 }), {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
};

const activeFilterCount = computed(() =>
  Object.values(filterForm.value).filter((value) => value !== null && value !== '').length
);

const hasActiveFilters = computed(() => activeFilterCount.value > 0);

const resultSummary = computed(() => {
  const total = props.appointments?.total ?? props.appointments?.data?.length ?? 0;
  const from = props.appointments?.from ?? (total ? 1 : 0);
  const to = props.appointments?.to ?? total;

  if (!total) {
    return 'No bookings found';
  }

  return `Showing ${from}-${to} of ${total} bookings`;
});

const columns = [
  { label: 'Doctor', key: 'doctor_name', type: 'slot', slot: 'doctor', align: 'left' },
  { label: 'Visit Type', key: 'visit_type_label', type: 'slot', slot: 'visitType', align: 'left' },
  { label: 'When', key: 'appointment_date_label', type: 'slot', slot: 'when', align: 'left' },
  { label: 'Reason', key: 'reason_label', type: 'slot', slot: 'reason', align: 'left' },
  { label: 'Payment status', key: 'status_label', type: 'slot', slot: 'status', align: 'left' },
];
</script>

<template>
  <AuthLayout title="Booking List" description="Patient Booking Appointments" heading="Booking List">
    <div class="users-toolbar card border-0 shadow-sm mb-4">
      <div class="card-body">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
          <div>
            <h3 class="mb-1">Booking Appointments</h3>
            <p class="text-muted mb-0">{{ resultSummary }}</p>
          </div>

          <div class="d-flex align-items-center gap-2 flex-wrap justify-content-lg-end">
            <span v-if="hasActiveFilters" class="filter-count-badge">
              {{ activeFilterCount }} filter{{ activeFilterCount > 1 ? 's' : '' }} active
            </span>
            <button v-if="hasActiveFilters" type="button" class="btn btn-outline-secondary btn-sm"
              @click="clearFilters">
              <i class="bi bi-x-circle me-1"></i>Clear filters
            </button>
            <!-- <button class="btn btn-danger" @click="goBackToSchedule">
              <i class="bi bi-arrow-left me-1"></i>Back to Schedule
            </button> -->
          </div>
        </div>

        <div class="row g-3 align-items-end">
          <div class="col-12 col-sm-6 col-xl-4">
            <label class="form-label text-muted small text-uppercase mb-2">Search</label>
            <div class="input-group booking-search-control">
              <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                <i class="bi bi-search text-muted"></i>
              </span>
              <input v-model="filterForm.keyword" type="search" class="form-control border-start-0"
                placeholder="Search doctor, reason, date" @keydown.enter.prevent="applyFilters" />
              <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
            </div>
          </div>

          <div class="col-12 col-sm-6 col-xl-3">
            <label class="form-label text-muted small text-uppercase mb-2">Status</label>
            <select v-model="filterForm.status" class="form-select" @change="applyFilters">
              <option value="">All status</option>
              <option value="pending">Pending</option>
              <option value="confirmed">Confirmed</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-body p-0 p-md-3">
        <div class="d-flex justify-content-end align-items-center px-3 px-md-0 pt-3 pt-md-0 pb-2">
          <div class="d-flex align-items-center gap-2 rows-select-wrap">
            <label for="booking-per-page" class="text-muted small text-uppercase mb-0">Rows</label>
            <select id="booking-per-page" v-model="perPage" class="form-select form-select-sm top-page-select"
              @change="updatePerPage">
              <option v-for="option in perPageOptions" :key="option" :value="option">
                {{ option }}
              </option>
            </select>
          </div>
        </div>

        <Table :columns="columns" :data="appointments" :search-show="false" :PageOptions="false">
          <template #doctor="{ row }">
            <div class="text-start">
              <div class="fw-semibold text-dark">{{ row.doctor_name || '-' }}</div>
              <div class="text-muted small">{{ row.doctor_speciality || '-' }}</div>
            </div>
          </template>

          <template #visitType="{ row }">
            <div class="text-start">
              <div class="fw-medium text-dark">{{ row.visit_type_label || '-' }}</div>
            </div>
          </template>

          <template #when="{ row }">
            <div class="text-start">
              <div class="fw-medium text-dark">{{ row.appointment_date_label || '-' }}</div>
              <div class="text-muted small">{{ row.appointment_time_label || '-' }}</div>
            </div>
          </template>

          <template #reason="{ row }">
            <div class="text-start">
              <div class="fw-medium text-dark">{{ row.reason_label || '-' }}</div>
            </div>
          </template>

          <template #status="{ row }">
            <div class="text-start d-flex flex-column gap-1">
              <!-- <span class="status-pill" :class="`status-${(row.status || 'pending').toLowerCase()}`">
                {{ row.status_label || '-' }}
              </span> -->
              <span class="payment-pill" :class="`payment-${(row.payment_status || 'pending').toLowerCase()}`">
                {{ row.payment_status_label || '-' }}
              </span>
            </div>
          </template>

          <template #actions="{ row }">
            <div class="d-flex gap-2 justify-content-end">
              <button v-if="(row.payment_status || '').toLowerCase() === 'pending'" class="btn btn-success action-btn"
                title="Pay now" @click="router.visit(route('patient.booking.payment', row.id))">
                <i class="bi bi-credit-card"></i>
              </button>

              <button v-if="row.can_join_live" class="btn btn-info action-btn" title="Join consultation"
                @click="router.visit(route('patient.live.consultation', row.id))">
                <i class="bi bi-camera-video"></i>
              </button>
            </div>
          </template>
        </Table>
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

.booking-search-control {
  max-width: 100%;
}

.rows-select-wrap {
  min-width: 118px;
}

.top-page-select {
  width: 84px;
}

.action-btn {
  width: 36px;
  height: 36px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
}

.status-pill,
.payment-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: fit-content;
  min-width: 96px;
  padding: 0.35rem 0.7rem;
  border-radius: 999px;
  font-size: 0.76rem;
  font-weight: 700;
}

.status-pending {
  background: #fef3c7;
  color: #b45309;
}

.status-confirmed {
  background: #dbeafe;
  color: #1d4ed8;
}

.status-completed {
  background: #dcfce7;
  color: #15803d;
}

.status-cancelled,
.status-rejected {
  background: #fee2e2;
  color: #b91c1c;
}

.payment-pending {
  background: #f8fafc;
  color: #475569;
}

.payment-paid {
  background: #dcfce7;
  color: #15803d;
}
</style>
