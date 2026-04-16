<script setup>
import { nextTick, onMounted } from 'vue'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import Chart from 'chart.js/auto'

const props = defineProps({
  metrics: Object,
  roleBreakdown: Object,
  topCountries: Array,
  charts: Object,
  latestUsers: Array,
})

const formatNumber = (value) => Number(value || 0).toLocaleString('en-IN')

const statCards = [
  {
    label: 'Total Registered Users',
    value: props.metrics?.total_users ?? 0,
    icon: 'fa-solid fa-users',
    tone: 'blue',
    helper: 'All users across the platform',
  },
  {
    label: 'Active Users',
    value: props.metrics?.active_users ?? 0,
    icon: 'fa-solid fa-user-check',
    tone: 'green',
    helper: 'Accounts currently marked active',
  },
  {
    label: 'Users Registered Today',
    value: props.metrics?.users_today ?? 0,
    icon: 'fa-solid fa-calendar-day',
    tone: 'orange',
    helper: 'New users created today',
  },
  {
    label: 'Verified Users',
    value: props.metrics?.verified_users ?? 0,
    icon: 'fa-solid fa-envelope-circle-check',
    tone: 'indigo',
    helper: 'Users with verified email addresses',
  },
]

const toneClass = (tone) => `stat-card--${tone}`

onMounted(async () => {
  await nextTick()

  const createChart = (id, config) => {
    const el = document.getElementById(id)
    if (!el) return
    new Chart(el, config)
  }

  createChart('sadmin-user-daily-chart', {
    type: 'line',
    data: {
      labels: props.charts?.daily_labels || [],
      datasets: [
        {
          label: 'New users',
          data: props.charts?.monthly_new_users || [],
          borderColor: '#1294ea',
          backgroundColor: 'rgba(18, 148, 234, 0.14)',
          fill: true,
          tension: 0.35,
          borderWidth: 3,
          pointRadius: 0,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(148, 163, 184, 0.16)' },
        },
        x: {
          grid: { display: false },
        },
      },
    },
  })

  createChart('sadmin-user-yearly-chart', {
    type: 'bar',
    data: {
      labels: props.charts?.month_labels || [],
      datasets: [
        {
          label: 'Users',
          data: props.charts?.yearly_users || [],
          backgroundColor: '#0f172a',
          borderRadius: 16,
          barThickness: 18,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(148, 163, 184, 0.16)' },
        },
        x: {
          grid: { display: false },
        },
      },
    },
  })
})
</script>

<template>
  <AuthLayout title="User Dashboard" description="User analytics dashboard">
    <section class="user-dashboard">
      <div class="dashboard-hero">
        <div>
          <p class="dashboard-kicker">User Intelligence</p>
          <h1 class="dashboard-title">Platform user growth and health</h1>
          <p class="dashboard-copy">
            Track verified account growth, registration pace, user distribution, and the latest platform signups from
            one view.
          </p>
        </div>

        <div class="role-breakdown">
          <div class="role-pill role-pill--admin">
            <span>Admins</span>
            <strong>{{ formatNumber(roleBreakdown?.admins ?? 0) }}</strong>
          </div>
          <div class="role-pill role-pill--doctor">
            <span>Doctors</span>
            <strong>{{ formatNumber(roleBreakdown?.doctors ?? 0) }}</strong>
          </div>
          <div class="role-pill role-pill--patient">
            <span>Patients</span>
            <strong>{{ formatNumber(roleBreakdown?.patients ?? 0) }}</strong>
          </div>
        </div>
      </div>

      <div class="stat-grid">
        <article v-for="card in statCards" :key="card.label" class="stat-card" :class="toneClass(card.tone)">
          <div class="stat-card__top">
            <div>
              <p class="stat-label">{{ card.label }}</p>
              <h3 class="stat-value">{{ formatNumber(card.value) }}</h3>
              <p class="stat-helper">{{ card.helper }}</p>
            </div>
            <div class="stat-icon">
              <i :class="card.icon"></i>
            </div>
          </div>
        </article>
      </div>

      <div class="dashboard-grid">
        <article class="panel-card panel-card--wide">
          <div class="panel-head">
            <div>
              <p class="panel-kicker">Current Month</p>
              <h3>Daily new registrations</h3>
            </div>
            <span class="panel-chip">{{ formatNumber(metrics?.users_today ?? 0) }} today</span>
          </div>
          <div class="chart-wrap">
            <canvas id="sadmin-user-daily-chart"></canvas>
          </div>
        </article>

        <article class="panel-card">
          <div class="panel-head">
            <div>
              <p class="panel-kicker">Geography</p>
              <h3>Top countries</h3>
            </div>
          </div>
          <div class="country-list">
            <div v-for="country in topCountries" :key="country.country" class="country-row">
              <span>{{ country.country }}</span>
              <strong>{{ formatNumber(country.users) }}</strong>
            </div>
            <div v-if="!topCountries?.length" class="empty-state">No country data available yet.</div>
          </div>
        </article>

        <article class="panel-card">
          <div class="panel-head">
            <div>
              <p class="panel-kicker">Yearly Trend</p>
              <h3>Total users this year</h3>
            </div>
          </div>
          <div class="chart-wrap chart-wrap--small">
            <canvas id="sadmin-user-yearly-chart"></canvas>
          </div>
        </article>

        <article class="panel-card">
          <div class="panel-head">
            <div>
              <p class="panel-kicker">Recent Activity</p>
              <h3>Latest registrations</h3>
            </div>
          </div>
          <div class="user-list">
            <div v-for="user in latestUsers" :key="user.id" class="user-row">
              <div class="user-row__identity">
                <img :src="user.profile_photo_url" alt="user" class="user-avatar" />
                <div>
                  <strong>{{ user.name || 'Unknown user' }}</strong>
                  <p>{{ user.email || 'No email' }}</p>
                </div>
              </div>
              <div class="user-row__meta">
                <span class="role-chip">{{ user.role }}</span>
                <span class="status-chip" :class="user.status === 'active' ? 'status-chip--active' : 'status-chip--inactive'">
                  {{ user.status }}
                </span>
                <span class="verify-chip" :class="user.verified ? 'verify-chip--yes' : 'verify-chip--no'">
                  {{ user.verified ? 'Verified' : 'Pending verification' }}
                </span>
                <small>{{ user.created_at }}</small>
              </div>
            </div>
            <div v-if="!latestUsers?.length" class="empty-state">No recent user activity found.</div>
          </div>
        </article>
      </div>
    </section>
  </AuthLayout>
</template>

<style scoped>
.user-dashboard {
  display: grid;
  gap: 24px;
}

.dashboard-hero {
  display: flex;
  justify-content: space-between;
  gap: 24px;
  padding: 32px;
  border-radius: 28px;
  background:
    radial-gradient(circle at top left, rgba(18, 148, 234, 0.16), transparent 24%),
    linear-gradient(135deg, #f8fcff 0%, #eef7ff 42%, #ffffff 100%);
  border: 1px solid rgba(18, 148, 234, 0.10);
}

.dashboard-kicker,
.panel-kicker {
  margin: 0 0 8px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #1294ea;
}

.dashboard-title {
  margin: 0 0 12px;
  font-size: 40px;
  line-height: 1.05;
  font-weight: 700;
  color: #0f172a;
}

.dashboard-copy {
  margin: 0;
  max-width: 760px;
  color: #475569;
  font-size: 17px;
  line-height: 1.7;
}

.role-breakdown {
  display: grid;
  gap: 14px;
  min-width: 280px;
}

.role-pill {
  display: flex;
  justify-content: space-between;
  gap: 18px;
  align-items: center;
  padding: 16px 18px;
  border-radius: 20px;
  font-weight: 600;
  box-shadow: 0 16px 32px rgba(15, 23, 42, 0.06);
}

.role-pill strong {
  font-size: 22px;
}

.role-pill--admin {
  background: #eff6ff;
  color: #1d4ed8;
}

.role-pill--doctor {
  background: #ecfdf3;
  color: #059669;
}

.role-pill--patient {
  background: #fff7ed;
  color: #ea580c;
}

.stat-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 20px;
}

.stat-card,
.panel-card {
  background: #fff;
  border: 1px solid rgba(148, 163, 184, 0.16);
  border-radius: 24px;
  box-shadow: 0 18px 38px rgba(15, 23, 42, 0.05);
}

.stat-card {
  padding: 24px;
}

.stat-card__top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
}

.stat-label {
  margin: 0 0 10px;
  color: #64748b;
  font-size: 14px;
}

.stat-value {
  margin: 0 0 8px;
  color: #0f172a;
  font-size: 34px;
  line-height: 1.1;
  font-weight: 700;
}

.stat-helper {
  margin: 0;
  color: #94a3b8;
  font-size: 13px;
}

.stat-icon {
  width: 58px;
  height: 58px;
  border-radius: 18px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
}

.stat-card--blue .stat-icon {
  background: #eff6ff;
  color: #1d4ed8;
}

.stat-card--green .stat-icon {
  background: #ecfdf3;
  color: #059669;
}

.stat-card--orange .stat-icon {
  background: #fff7ed;
  color: #ea580c;
}

.stat-card--indigo .stat-icon {
  background: #eef2ff;
  color: #4f46e5;
}

.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 20px;
}

.panel-card {
  padding: 24px;
}

.panel-head {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  margin-bottom: 18px;
}

.panel-head h3 {
  margin: 0;
  color: #0f172a;
  font-size: 24px;
}

.panel-chip {
  display: inline-flex;
  align-items: center;
  padding: 10px 14px;
  border-radius: 999px;
  background: #eff6ff;
  color: #2563eb;
  font-size: 13px;
  font-weight: 700;
}

.chart-wrap {
  height: 320px;
}

.chart-wrap--small {
  height: 280px;
}

.country-list,
.user-list {
  display: grid;
  gap: 14px;
}

.country-row,
.user-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  padding: 16px 18px;
  border-radius: 18px;
  background: #f8fbff;
  border: 1px solid #e2e8f0;
}

.country-row span {
  color: #334155;
}

.country-row strong {
  color: #0f172a;
}

.user-row__identity {
  display: flex;
  align-items: center;
  gap: 14px;
}

.user-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  object-fit: cover;
  box-shadow: 0 10px 18px rgba(15, 23, 42, 0.10);
}

.user-row__identity strong {
  color: #0f172a;
}

.user-row__identity p {
  margin: 4px 0 0;
  color: #64748b;
  font-size: 13px;
}

.user-row__meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 6px;
  text-align: right;
}

.user-row__meta small {
  color: #94a3b8;
}

.role-chip,
.status-chip,
.verify-chip {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
  text-transform: capitalize;
}

.role-chip {
  background: #eff6ff;
  color: #2563eb;
}

.status-chip--active {
  background: #ecfdf3;
  color: #059669;
}

.status-chip--inactive {
  background: #fff1f2;
  color: #dc2626;
}

.verify-chip--yes {
  background: #ecfeff;
  color: #0f766e;
}

.verify-chip--no {
  background: #fff7ed;
  color: #c2410c;
}

.empty-state {
  padding: 24px;
  border-radius: 18px;
  background: #f8fafc;
  color: #64748b;
  text-align: center;
}

@media (max-width: 1400px) {
  .stat-grid,
  .dashboard-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 991px) {
  .dashboard-hero {
    flex-direction: column;
  }

  .role-breakdown {
    min-width: 0;
  }
}

@media (max-width: 640px) {
  .stat-grid,
  .dashboard-grid {
    grid-template-columns: 1fr;
  }

  .dashboard-title {
    font-size: 32px;
  }

  .panel-head,
  .country-row,
  .user-row {
    flex-direction: column;
    align-items: flex-start;
  }

  .user-row__meta {
    align-items: flex-start;
    text-align: left;
  }
}
</style>
