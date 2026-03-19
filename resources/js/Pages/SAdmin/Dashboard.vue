<script setup>
import { reactive,onMounted, nextTick } from 'vue'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import Chart from 'chart.js/auto';

// Sample data (mirrors variables used in Blade)
const total_data_monthly = reactive({
  new_users_current_month: 124,
  new_users_past_month: 100,
  new_subscribers_current_month: 18,
  new_subscribers_past_month: 14,
  income_current_month: [{ currency: 'USD', data: 12500 }],
  income_past_month: [{ currency: 'USD', data: 10100 }],
  spending_current_month: 3500.12,
  spending_past_month: 3000.00,
  words_current_month: 450000,
  words_past_month: 420000,
  images_current_month: 1200,
  images_past_month: 1100,
  contents_current_month: 320,
  contents_past_month: 280,
  transactions_current_month: [{ data: 230 }],
  transactions_past_month: [{ data: 190 }]
})

const total_data_yearly = reactive({
  total_new_users: 1240,
  total_new_subscribers: 240,
  total_income: [{ currency: 'USD', data: 145000 }],
  total_spending: 42000,
  words_generated: 5000000,
  images_generated: 24000,
  contents_generated: 3200,
  transactions_generated: [{ data: 2800 }]
})

const chart_data = reactive({
  monthly_new_users: JSON.stringify([4,5,12,7,10,8,6,9,12,11,13,14,5,6,7,8,9,3,4,5,6,7,8,9,2,1,0,0,0,0,2]),
  total_new_users: JSON.stringify([10,20,30,25,40,60,55,45,70,85,95,100]),
  total_income: JSON.stringify([2000,3000,4000,5000,6000,7000,8000,9000,7500,8500,9500,10000])
})

const percentage = reactive({
  users_current: JSON.stringify(124),
  users_past: JSON.stringify(100),
  subscribers_current: JSON.stringify(18),
  subscribers_past: JSON.stringify(14),
  income_current: JSON.stringify([{ data: 12500 }]),
  income_past: JSON.stringify([{ data: 10100 }]),
  spending_current: JSON.stringify(3500),
  spending_past: JSON.stringify(3000),
  words_current: JSON.stringify(450000),
  words_past: JSON.stringify(420000),
  images_current: JSON.stringify(1200),
  images_past: JSON.stringify(1100),
  contents_current: JSON.stringify(320),
  contents_past: JSON.stringify(280),
  transactions_current: JSON.stringify([{ data: 230 }]),
  transactions_past: JSON.stringify([{ data: 190 }])
})

const result = reactive([
  { id: 1, profile_photo_path: '/images/user/02.jpg', name: 'Alice', email: 'alice@example.com', group: 'admin', status: 'active', created_at: '2025-12-01 09:00:00' },
  { id: 2, profile_photo_path: '/images/user/03.jpg', name: 'Bob', email: 'bob@example.com', group: 'user', status: 'inactive', created_at: '2025-12-05 14:00:00' }
])

const transaction = reactive([
  { id: 1, profile_photo_path: '/images/user/05.jpg', name: 'Charlie', email: 'charlie@example.com', status: 'Paid', price: '49.99', gateway: 'Stripe', created_at: '2025-12-07 11:00:00' }
])

const notifications = reactive([
  { id: 1, data: { type: 'new-user', subject: 'New user registered', name: 'Eve', email: 'eve@example.com' }, created_at: '2025-12-08 12:00:00' }
])

function formatNumber(n) {
  if (n === null || n === undefined) return '-' 
  if (typeof n === 'number') return n.toLocaleString()
  return n
}

function mainPercentageDifference(past, current) {
  past = Number(past) || 0
  current = Number(current) || 0
  if (past === 0) return current === 0 ? '<span class="text-muted"> 0%</span>' : '<span class="text-success fs-14">+100%</span>'
  const diff = current - past
  const change = (Math.abs(diff) / past) * 100
  return diff > 0 ? `<span class="text-success fs-14">+${change.toFixed(1)}%</span>` : `<span class="text-danger">-${change.toFixed(1)}%</span>`
}

onMounted(async () => {
  // Wait for DOM to be ready
  await nextTick()
  
  // Helper function to init charts with retry
  const initChart = (elementId, chartConfig, retries = 5) => {
    return new Promise((resolve) => {
      const tryFind = () => {
        const ctx = document.getElementById(elementId)
        if (ctx) {
          try {
            new Chart(ctx, chartConfig)
            resolve(true)
          } catch (e) {
            console.error(`Chart ${elementId} error:`, e)
            resolve(false)
          }
        } else if (retries > 0) {
          setTimeout(() => tryFind(), 100)
        } else {
          console.warn(`Element ${elementId} not found after retries`)
          resolve(false)
        }
      }
      tryFind()
    })
  }

  // monthly users chart
  try {
    const userMonthly = JSON.parse(chart_data.monthly_new_users)
    await initChart('chart-total-users-month', {
      type: 'bar',
      data: { labels: Array.from({ length: userMonthly.length }, (_, i) => (i+1).toString()), datasets: [{ label: 'New Registered Users', data: userMonthly, backgroundColor: '#007bff' }] },
      options: { responsive: true, maintainAspectRatio: false }
    })
  } catch (e) {
    console.warn('Monthly users chart error:', e)
  }

  try {
    const userYearly = JSON.parse(chart_data.total_new_users)
    await initChart('chart-total-users-year', {
      type: 'bar',
      data: { labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'], datasets: [{ label: 'Total New Registered Users', data: userYearly, backgroundColor: '#1e1e2d' }] },
      options: { responsive: true, maintainAspectRatio: false }
    })
  } catch (e) {
    console.warn('Yearly users chart error:', e)
  }

  try {
    const income = JSON.parse(chart_data.total_income)
    await initChart('chart-total-income', {
      type: 'bar',
      data: { labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'], datasets: [{ label: 'Total Income', data: income, backgroundColor: '#FF9D00' }] },
      options: { responsive: true, maintainAspectRatio: false }
    })
  } catch (e) {
    console.warn('Income chart error:', e)
  }

  // compute and inject differences into elements (mirroring Blade JS behavior)
  try {
    const users_change = mainPercentageDifference(JSON.parse(percentage.users_past), JSON.parse(percentage.users_current))
    const subscribers_change = mainPercentageDifference(JSON.parse(percentage.subscribers_past), JSON.parse(percentage.subscribers_current))
    const income_change = mainPercentageDifference( (percentage.income_past && JSON.parse(percentage.income_past)[0]?.data) || 0, (percentage.income_current && JSON.parse(percentage.income_current)[0]?.data) || 0)
    const spending_change = mainPercentageDifference(JSON.parse(percentage.spending_past), JSON.parse(percentage.spending_current))
    const words_change = mainPercentageDifference(JSON.parse(percentage.words_past), JSON.parse(percentage.words_current))
    const images_change = mainPercentageDifference(JSON.parse(percentage.images_past), JSON.parse(percentage.images_current))
    const contents_change = mainPercentageDifference(JSON.parse(percentage.contents_past), JSON.parse(percentage.contents_current))
    const transactions_change = mainPercentageDifference( (percentage.transactions_past && JSON.parse(percentage.transactions_past)[0]?.data) || 0, (percentage.transactions_current && JSON.parse(percentage.transactions_current)[0]?.data) || 0)

    const setHtml = (id, html) => {
      const el = document.getElementById(id);
      if (el) el.innerHTML = html
    }

    setHtml('users_change', users_change)
    setHtml('subscribers_change', subscribers_change)
    setHtml('income_change', income_change)
    setHtml('spending_change', spending_change)
    setHtml('words_change', words_change)
    setHtml('images_change', images_change)
    setHtml('contents_change', contents_change)
    setHtml('transactions_change', transactions_change)
  } catch (e) {}
})
</script>

<template>
  <AuthLayout title="Admin Dashboard" description="Converted admin dashboard sample">
    <div class="page-header mt-5-7">
      <div class="page-leftheader">
        <h4 class="page-title mb-0">Admin Dashboard</h4>
      </div>
    </div>

    <!-- Metrics row: Users, Subscribers, Income, Transactions (icons instead of images) -->
    <div class="row">
      <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 d-flex" v-for="(i, idx) in [0,1,2,3]" :key="idx">
        <div class="card overflow-hidden border-0 mb-3 metric-card w-100">
          <div class="card-body d-flex align-items-center">
            <div class="d-flex align-items-end justify-content-between">
              <div>
                <p class="mb-3 fs-14 font-weight-bold">{{ i===0 ? 'Total New Users' : i===1 ? 'Total New Subscribers' : i===2 ? 'Total Income' : 'Total Transactions' }} <span class="text-muted">(Current Month)</span></p>
                <h2 class="mb-0"><span class="number-font fs-14">
                  {{ i===0 ? formatNumber(total_data_monthly.new_users_current_month) : i===1 ? formatNumber(total_data_monthly.new_subscribers_current_month) : i===2 ? '$' + formatNumber(total_data_monthly.income_current_month[0].data) : formatNumber(total_data_monthly.transactions_current_month[0].data) }}</span>
                  <span class="ml-2 text-muted fs-11 data-percentage-change"><span :id="i===0 ? 'users_change' : i===1 ? 'subscribers_change' : i===2 ? 'income_change' : 'transactions_change'"></span></span>
                </h2>
              </div>
              <div>
                <div style="width:70px;height:70px;display:flex;align-items:center;justify-content:center;border-radius:8px;background:#f3f4f6;">
                  <i :class="i===0 ? 'fa-solid fa-users text-info' : i===1 ? 'fa-solid fa-user-plus text-info' : i===2 ? 'fa-solid fa-dollar-sign text-success' : 'fa-solid fa-receipt text-primary'" style="font-size:28px"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts -->
    <div class="row">
      <div class="col-lg-6 col-md-12 mt-3">
        <div class="card overflow-hidden border-0 mb-3">
          <div class="card-header d-inline border-0">
            <h3 class="card-title fs-16 mt-2 mb-2 text-white">Finance Overview</h3>
            <div>
              <h3 class="card-title fs-24 font-weight-800 text-white">${{ formatNumber(total_data_yearly.total_income[0].data) }}</h3>
              <div class="text-white">Total Earnings Current Year</div>
            </div>
          </div>
          <div class="card-body">
            <canvas id="chart-total-income" class="h-330"></canvas>
          </div>
        </div>
      </div>

      <div class="col-lg-6 col-md-12 mt-3">
        <div class="card overflow-hidden border-0 mb-3">
          <div class="card-header d-inline border-0">
            <h3 class="card-title fs-16 mt-2 mb-2 text-white">Total New Users</h3>
            <div>
              <h3 class="card-title fs-24 font-weight-800 text-white">{{ formatNumber(total_data_yearly.total_new_users) }}</h3>
              <div class="text-white">Total New Registered Users Current Year</div>
            </div>
          </div>
          <div class="card-body">
            <canvas id="chart-total-users-year" class="h-330"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Latest registrations and transactions -->
    <div class="row">
      <div class="col-lg-12 col-md-12 mt-3">
        <div class="card border-0 mb-3">
          <div class="card-header d-inline border-0">
            <h3 class="card-title fs-16 mt-2 text-white">Latest Registrations</h3>
          </div>
          <div class="card-body">
            <table class="table">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Group</th>
                  <th>Status</th>
                  <th>Registered On</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="u in result" :key="u.id">
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="widget-user-image-sm overflow-hidden mr-4"><img :src="u.profile_photo_path || '/img/users/avatar.png'" style="width:48px;height:48px;"/></div>
                      <div class="widget-user-name"><span class="font-weight-bold">{{ u.name }}</span><br/><span class="text-muted">{{ u.email }}</span></div>
                    </div>
                  </td>
                  <td><span class="cell-box user-group-{{ u.group }}">{{ u.group }}</span></td>
                  <td><span class="cell-box user-{{ u.status }}">{{ u.status }}</span></td>
                  <td><span class="font-weight-bold">{{ u.created_at }}</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="card border-0 mb-3">
          <div class="card-header d-inline border-0"><h3 class="card-title fs-16 mt-2 text-white">Latest Transactions</h3></div>
          <div class="card-body">
            <table class="table">
              <thead>
                <tr><th>Paid By</th><th>Status</th><th>Total</th><th>Gateway</th><th>Paid On</th></tr>
              </thead>
              <tbody>
                <tr v-for="t in transaction" :key="t.id">
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="widget-user-image-sm overflow-hidden mr-4"><img :src="t.profile_photo_path || '/img/users/avatar.png'" style="width:48px;height:48px;"/></div>
                      <div><span class="font-weight-bold">{{ t.name }}</span><br/><span class="text-muted">{{ t.email }}</span></div>
                    </div>
                  </td>
                  <td><span class="cell-box payment-{{ t.status.toLowerCase() }}">{{ t.status }}</span></td>
                  <td><span class="font-weight-bold">${{ t.price }}</span></td>
                  <td>{{ t.gateway }}</td>
                  <td>{{ t.created_at }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>

    <!--Notifications -->
    <div class="row mt-3">

      <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="card border-0 mb-3 notification-card">
          <div class="card-header notification-header">
            <h3 class="card-title mb-0 fs-16 fw-bold">
              <i class="fa-solid fa-bell me-2"></i>Notifications
            </h3>
          </div>
          <div class="card-body pt-0 dashboard-timeline height-400">
            <div class="vertical-timeline">
              <div v-for="n in notifications" :key="n.id" class="vertical-timeline-item notification-item">
                <div class="d-flex align-items-start">
                  <div class="notification-badge">
                    <i class="fa-solid fa-check-circle text-success"></i>
                  </div>
                  <div class="notification-content">
                    <h6 class="notification-title mb-2">
                      <span class="badge bg-info me-2">{{ n.data.type === 'new-user' ? 'NEW USER' : 'EVENT' }}</span>
                      {{ n.data.subject }}
                    </h6>
                    <p class="notification-user mb-2">
                      <i class="fa-solid fa-user me-1 text-muted"></i>
                      <span class="fw-500">{{ n.data.name }}</span> 
                      <span class="text-muted">{{ n.data.email }}</span>
                    </p>
                    <small class="notification-time text-muted">
                      <i class="fa-solid fa-clock me-1"></i>{{ n.created_at }}
                    </small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </AuthLayout>
</template>

<style scoped>
.h-330 { height: 330px; }
.card { background: #fff; }

.metric-card {
  min-height: 160px;
}

/* Notifications styling */
.notification-card {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  border-radius: 6px !important;
  overflow: hidden;
}

.notification-header {
  background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
  color: #fff;
  padding: 1.5rem;
  border: none;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.notification-header .card-title {
  color: #fff;
  margin: 0;
  font-weight: 600;
  font-size: 1.1rem;
}

.vertical-timeline {
  position: relative;
  padding: 1rem 0;
}

.vertical-timeline::before {
  content: '';
  position: absolute;
  left: 25px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: linear-gradient(to bottom, #0ea5e9, transparent);
}

.vertical-timeline-item {
  position: relative;
  padding-left: 70px;
  padding-bottom: 1.5rem;
}

.vertical-timeline-item:last-child {
  padding-bottom: 0;
}

.notification-badge {
  position: absolute;
  left: 10px;
  top: 5px;
  width: 36px;
  height: 36px;
  background: #f0f9ff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 3px solid #0ea5e9;
  font-size: 16px;
}

.notification-content {
  background: #f8fafc;
  padding: 1rem;
  border-radius: 8px;
  border-left: 4px solid #0ea5e9;
  transition: all 0.3s ease;
}

.notification-content:hover {
  background: #f1f5f9;
  border-left-color: #06b6d4;
  box-shadow: 0 2px 8px rgba(14, 165, 233, 0.1);
}

.notification-title {
  font-size: 0.95rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

.notification-title .badge {
  font-size: 0.7rem;
  padding: 0.35rem 0.6rem;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.notification-user {
  font-size: 0.9rem;
  color: #64748b;
  margin: 0.5rem 0;
}

.notification-user span.fw-500 {
  color: #1e293b;
  font-weight: 500;
}

.notification-time {
  font-size: 0.85rem;
  display: inline-flex;
  align-items: center;
}
</style>
