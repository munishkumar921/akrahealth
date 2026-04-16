<script setup>
import { nextTick, onMounted } from 'vue'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import Chart from 'chart.js/auto'

const props = defineProps({
  metrics: Object,
  charts: Object,
  latestRegistrations: Array,
  latestTransactions: Array,
  topPlans: Array,
})

const formatNumber = (value) => {
  const amount = Number(value || 0)
  return amount.toLocaleString('en-IN')
}

const formatCurrency = (value, currency = 'USD') => {
  const amount = Number(value || 0)

  try {
    return new Intl.NumberFormat('en-IN', {
      style: 'currency',
      currency,
      maximumFractionDigits: 0,
    }).format(amount)
  } catch (error) {
    return `${currency} ${amount.toFixed(2)}`
  }
}

const getChange = (past, current) => {
  const previous = Number(past || 0)
  const now = Number(current || 0)

  if (previous === 0) {
    return {
      label: now === 0 ? '0%' : '+100%',
      tone: now === 0 ? 'neutral' : 'up',
    }
  }

  const diff = ((now - previous) / previous) * 100

  if (diff === 0) {
    return { label: '0%', tone: 'neutral' }
  }

  return {
    label: `${diff > 0 ? '+' : ''}${diff.toFixed(1)}%`,
    tone: diff > 0 ? 'up' : 'down',
  }
}

const statCards = [
  {
    key: 'users',
    label: 'New Users',
    value: props.metrics?.new_users_current_month ?? 0,
    helper: 'Compared to last month',
    icon: 'fa-solid fa-user-plus',
    change: getChange(props.metrics?.new_users_past_month, props.metrics?.new_users_current_month),
  },
  {
    key: 'subscribers',
    label: 'New Subscribers',
    value: props.metrics?.new_subscribers_current_month ?? 0,
    helper: 'Compared to last month',
    icon: 'fa-solid fa-id-card',
    change: getChange(props.metrics?.new_subscribers_past_month, props.metrics?.new_subscribers_current_month),
  },
  {
    key: 'income',
    label: 'Revenue This Month',
    value: formatCurrency(props.metrics?.income_current_month ?? 0),
    helper: 'Compared to last month',
    icon: 'fa-solid fa-wallet',
    change: getChange(props.metrics?.income_past_month, props.metrics?.income_current_month),
  },
  {
    key: 'transactions',
    label: 'Transactions',
    value: props.metrics?.transactions_current_month ?? 0,
    helper: 'Compared to last month',
    icon: 'fa-solid fa-receipt',
    change: getChange(props.metrics?.transactions_past_month, props.metrics?.transactions_current_month),
  },
]

const renderChangeClass = (tone) => {
  if (tone === 'up') return 'metric-change metric-change--up'
  if (tone === 'down') return 'metric-change metric-change--down'
  return 'metric-change metric-change--neutral'
}

onMounted(async () => {
  await nextTick()

  const initChart = (selector, config) => {
    const target = document.getElementById(selector)
    if (!target) return
    new Chart(target, config)
  }

  initChart('sadmin-users-chart', {
    type: 'line',
    data: {
      labels: props.charts?.daily_labels || [],
      datasets: [
        {
          label: 'Daily registrations',
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

  initChart('sadmin-yearly-users-chart', {
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

  initChart('sadmin-income-chart', {
    type: 'bar',
    data: {
      labels: props.charts?.month_labels || [],
      datasets: [
        {
          label: 'Revenue',
          data: props.charts?.yearly_income || [],
          backgroundColor: '#0ea5e9',
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
  <AuthLayout title="Super Admin Dashboard" description="Platform analytics and business overview">
    <section class="sadmin-dashboard">
      <div class="dashboard-hero">
        <div>
          <p class="dashboard-kicker">Platform Overview</p>
          <h1 class="dashboard-title">Super Admin control center</h1>
          <p class="dashboard-copy">
            Review platform growth, subscription health, transaction activity, and the latest user movement from one
            place.
          </p>
        </div>

        <div class="hero-stats">
          <div class="hero-stat">
            <span class="hero-stat__label">Annual Revenue</span>
            <strong>{{ formatCurrency(metrics?.total_income_current_year ?? 0) }}</strong>
          </div>
          <div class="hero-stat">
            <span class="hero-stat__label">Yearly Users</span>
            <strong>{{ formatNumber(metrics?.total_new_users_current_year ?? 0) }}</strong>
          </div>
        </div>
      </div>

      <div class="metric-grid">
        <article v-for="card in statCards" :key="card.key" class="metric-card">
          <div class="metric-card__top">
            <div>
              <p class="metric-label">{{ card.label }}</p>
              <h3 class="metric-value">{{ card.value }}</h3>
            </div>
            <div class="metric-icon">
              <i :class="card.icon"></i>
            </div>
          </div>
          <div class="metric-card__bottom">
            <span :class="renderChangeClass(card.change.tone)">{{ card.change.label }}</span>
            <span class="metric-helper">{{ card.helper }}</span>
          </div>
        </article>
      </div>

      <div class="signal-grid">
        <article class="signal-card signal-card--warning">
          <p class="signal-card__label">Active Subscribers</p>
          <h3>{{ formatNumber(metrics?.active_subscribers ?? 0) }}</h3>
          <p>Currently active recurring subscriptions across the platform.</p>
        </article>

        <article class="signal-card signal-card--warning">
          <p class="signal-card__label">Plans Live</p>
          <h3>{{ formatNumber(metrics?.plans_live ?? 0) }}</h3>
          <p>Subscription plans currently available for signup and upgrades.</p>
        </article>

        <article class="signal-card signal-card--warning">
          <p class="signal-card__label">Trial Ending Soon</p>
          <h3>{{ formatNumber(metrics?.trial_ending_soon ?? 0) }}</h3>
          <p>Subscriptions reaching their end date within the next 14 days.</p>
        </article>
      </div>

      <div class="analytics-grid">
        <article class="panel-card panel-card--wide">
          <div class="panel-head">
            <div>
              <p class="panel-kicker">Growth</p>
              <h3>Daily registrations this month</h3>
            </div>
            <span class="panel-chip">{{ formatNumber(metrics?.new_users_current_month ?? 0) }} new users</span>
          </div>
          <div class="chart-wrap">
            <canvas id="sadmin-users-chart"></canvas>
          </div>
        </article>

        <article class="panel-card">
          <div class="panel-head">
            <div>
              <p class="panel-kicker">Subscriptions</p>
              <h3>Top plans</h3>
            </div>
          </div>
          <div class="plan-list">
            <div v-for="plan in topPlans" :key="plan.plan_name" class="plan-item">
              <div>
                <strong>{{ plan.plan_name }}</strong>
                <p>{{ formatNumber(plan.subscriptions_count) }} subscriptions</p>
              </div>
              <span>{{ formatCurrency(plan.revenue, plan.currency) }}</span>
            </div>
            <div v-if="!topPlans?.length" class="empty-state">No subscription data available yet.</div>
          </div>
        </article>

        <article class="panel-card">
          <div class="panel-head">
            <div>
              <p class="panel-kicker">Users</p>
              <h3>Yearly new users</h3>
            </div>
          </div>
          <div class="chart-wrap chart-wrap--small">
            <canvas id="sadmin-yearly-users-chart"></canvas>
          </div>
        </article>

        <article class="panel-card">
          <div class="panel-head">
            <div>
              <p class="panel-kicker">Revenue</p>
              <h3>Yearly income</h3>
            </div>
          </div>
          <div class="chart-wrap chart-wrap--small">
            <canvas id="sadmin-income-chart"></canvas>
          </div>
        </article>
      </div>

      <div class="table-grid">
        <article class="panel-card">
          <div class="panel-head">
            <div>
              <p class="panel-kicker">Latest Users</p>
              <h3>Recent registrations</h3>
            </div>
          </div>
          <div class="table-list">
            <div v-for="user in latestRegistrations" :key="user.id" class="table-row">
              <div class="table-user">
                <img :src="user.profile_photo_url" alt="user" class="table-avatar" />
                <div>
                  <strong>{{ user.name || 'Unknown user' }}</strong>
                  <p>{{ user.email || 'No email' }}</p>
                </div>
              </div>
              <div class="table-meta">
                <span class="role-pill">{{ user.group }}</span>
                <span class="status-pill" :class="user.status === 'active' ? 'status-pill--active' : 'status-pill--inactive'">
                  {{ user.status }}
                </span>
                <small>{{ user.created_at }}</small>
              </div>
            </div>
            <div v-if="!latestRegistrations?.length" class="empty-state">No recent registrations found.</div>
          </div>
        </article>

        <article class="panel-card">
          <div class="panel-head">
            <div>
              <p class="panel-kicker">Revenue Feed</p>
              <h3>Latest transactions</h3>
            </div>
          </div>
          <div class="table-list">
            <div v-for="transaction in latestTransactions" :key="transaction.id" class="table-row">
              <div class="table-user">
                <img :src="transaction.profile_photo_url" alt="customer" class="table-avatar" />
                <div>
                  <strong>{{ transaction.name || 'Unknown customer' }}</strong>
                  <p>{{ transaction.email || 'No email' }}</p>
                </div>
              </div>
              <div class="table-meta table-meta--transaction">
                <span class="gateway-pill">{{ transaction.gateway }}</span>
                <strong>{{ formatCurrency(transaction.price, transaction.currency) }}</strong>
                <small>{{ transaction.created_at }}</small>
              </div>
            </div>
            <div v-if="!latestTransactions?.length" class="empty-state">No recent transactions found.</div>
          </div>
        </article>
      </div>
    </section>
  </AuthLayout>
</template>

<style scoped>
.sadmin-dashboard {
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
    radial-gradient(circle at top left, rgba(14, 165, 233, 0.20), transparent 28%),
    linear-gradient(135deg, #f8fcff 0%, #eef7ff 48%, #ffffff 100%);
  border: 1px solid rgba(18, 148, 234, 0.10);
}

.dashboard-kicker,
.panel-kicker,
.signal-card__label {
  margin: 0 0 8px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #1294ea;
}

.dashboard-title {
  margin: 0 0 12px;
  font-size: 42px;
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

.hero-stats {
  display: grid;
  grid-template-columns: repeat(2, minmax(160px, 1fr));
  gap: 16px;
  min-width: 340px;
}

.hero-stat {
  padding: 20px;
  border-radius: 22px;
  background: rgba(255, 255, 255, 0.88);
  border: 1px solid rgba(148, 163, 184, 0.14);
  box-shadow: 0 16px 36px rgba(15, 23, 42, 0.06);
}

.hero-stat__label {
  display: block;
  margin-bottom: 10px;
  font-size: 13px;
  color: #64748b;
}

.hero-stat strong {
  color: #0f172a;
  font-size: 28px;
  line-height: 1.1;
}

.metric-grid,
.signal-grid,
.table-grid {
  display: grid;
  gap: 20px;
}

.metric-grid {
  grid-template-columns: repeat(4, minmax(0, 1fr));
}

.signal-grid {
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

.table-grid {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.metric-card,
.signal-card,
.panel-card {
  background: #fff;
  border: 1px solid rgba(148, 163, 184, 0.16);
  border-radius: 24px;
  box-shadow: 0 18px 38px rgba(15, 23, 42, 0.05);
}

.metric-card {
  padding: 22px;
}

.metric-card__top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
}

.metric-label {
  margin: 0 0 10px;
  color: #64748b;
  font-size: 14px;
}

.metric-value {
  margin: 0;
  color: #0f172a;
  font-size: 32px;
  line-height: 1.1;
  font-weight: 700;
}

.metric-icon {
  width: 56px;
  height: 56px;
  border-radius: 18px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%);
  color: #0ea5e9;
  font-size: 24px;
}

.metric-card__bottom {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: center;
  margin-top: 18px;
}

.metric-change {
  font-size: 13px;
  font-weight: 700;
}

.metric-change--up {
  color: #059669;
}

.metric-change--down {
  color: #dc2626;
}

.metric-change--neutral {
  color: #64748b;
}

.metric-helper {
  color: #94a3b8;
  font-size: 12px;
}

.signal-card {
  padding: 24px;
}

.signal-card h3 {
  margin: 0 0 8px;
  font-size: 34px;
  line-height: 1;
  color: #0f172a;
}

.signal-card p:last-child {
  margin: 0;
  color: #64748b;
  line-height: 1.6;
}

.signal-card--dark {
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  border-color: rgba(15, 23, 42, 0.7);
}

.signal-card--dark .signal-card__label,
.signal-card--dark h3,
.signal-card--dark p:last-child {
  color: #fff;
}

.signal-card--warning {
  background: linear-gradient(135deg, #fff7ed 0%, #ffffff 100%);
}

.analytics-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 20px;
}

.panel-card {
  padding: 24px;
}

.panel-card--wide {
  grid-column: span 1;
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

.plan-list,
.table-list {
  display: grid;
  gap: 14px;
}

.plan-item,
.table-row {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: center;
  padding: 16px 18px;
  border-radius: 18px;
  background: #f8fbff;
  border: 1px solid #e2e8f0;
}

.plan-item strong,
.table-user strong {
  color: #0f172a;
}

.plan-item p,
.table-user p {
  margin: 4px 0 0;
  color: #64748b;
  font-size: 13px;
}

.table-user {
  display: flex;
  align-items: center;
  gap: 14px;
  min-width: 0;
}

.table-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  object-fit: cover;
  box-shadow: 0 10px 18px rgba(15, 23, 42, 0.10);
}

.table-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 6px;
  text-align: right;
}

.table-meta small {
  color: #94a3b8;
}

.table-meta--transaction strong {
  font-size: 16px;
}

.role-pill,
.status-pill,
.gateway-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
  text-transform: capitalize;
}

.role-pill {
  background: #eff6ff;
  color: #2563eb;
}

.status-pill--active {
  background: #ecfdf3;
  color: #059669;
}

.status-pill--inactive {
  background: #fff1f2;
  color: #dc2626;
}

.gateway-pill {
  background: #f1f5f9;
  color: #334155;
}

.empty-state {
  padding: 24px;
  border-radius: 18px;
  background: #f8fafc;
  color: #64748b;
  text-align: center;
}

@media (max-width: 1400px) {
  .metric-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .signal-grid,
  .analytics-grid,
  .table-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 991px) {
  .dashboard-hero {
    flex-direction: column;
  }

  .hero-stats {
    min-width: 0;
  }
}

@media (max-width: 640px) {
  .metric-grid,
  .signal-grid,
  .table-grid {
    grid-template-columns: 1fr;
  }

  .dashboard-title {
    font-size: 32px;
  }

  .hero-stats {
    grid-template-columns: 1fr;
  }

  .panel-head,
  .plan-item,
  .table-row,
  .metric-card__bottom {
    flex-direction: column;
    align-items: flex-start;
  }

  .table-meta {
    align-items: flex-start;
    text-align: left;
  }
}
</style>
