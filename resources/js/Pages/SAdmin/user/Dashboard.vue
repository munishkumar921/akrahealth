<script setup>
import { reactive, onMounted } from 'vue'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import Chart from 'chart.js/auto'

// User Dashboard - User Management Data
const userData = reactive({
  totalUsers: 5234,
  onlineUsers: 342,
  usersToday: 89,
  totalFreeUsers: 3500
})

// Top countries data
const topCountries = reactive([
  { country: 'United States', users: 1245 },
  { country: 'Canada', users: 456 },
  { country: 'United Kingdom', users: 389 },
  { country: 'Australia', users: 345 },
  { country: 'Germany', users: 312 },
  { country: 'France', users: 298 },
  { country: 'India', users: 276 },
  { country: 'Japan', users: 234 },
  { country: 'Brazil', users: 198 },
  { country: 'Mexico', users: 187 },
  { country: 'Spain', users: 156 },
  { country: 'Netherlands', users: 145 },
  { country: 'Sweden', users: 132 },
  { country: 'Switzerland', users: 128 },
  { country: 'Singapore', users: 115 },
  { country: 'South Korea', users: 109 },
  { country: 'Norway', users: 98 },
  { country: 'Denmark', users: 87 },
  { country: 'Belgium', users: 76 },
  { country: 'Austria', users: 65 }
])

// Chart data - Current month new registrations (31 days)
const monthlyNewUsers = reactive({
  data: [12, 19, 15, 25, 22, 30, 28, 35, 42, 38, 45, 52, 48, 55, 60, 58, 65, 62, 68, 70, 72, 68, 75, 78, 72, 68, 65, 60, 55, 50, 48]
})

// Chart data - Yearly total users (12 months)
const yearlyUsers = reactive({
  data: [450, 520, 610, 745, 890, 1050, 1240, 1420, 1580, 1780, 1950, 2100]
})

// User distribution by region (for geocharts simulation)
const usersByCountry = reactive({
  'United States': 1245,
  'Canada': 456,
  'United Kingdom': 389,
  'Australia': 345,
  'Germany': 312,
  'France': 298,
  'India': 276,
  'Japan': 234,
  'Brazil': 198,
  'Mexico': 187,
  'Spain': 156,
  'Netherlands': 145,
  'Sweden': 132,
  'Switzerland': 128,
  'Singapore': 115,
  'South Korea': 109,
  'Norway': 98,
  'Denmark': 87,
  'Belgium': 76,
  'Austria': 65
})

const formatNumber = (n) => {
  if (n === null || n === undefined) return '-'
  return n.toLocaleString()
}

onMounted(() => {
  // Initialize Yearly Chart
  try {
    const ctxYear = document.getElementById('chart-new-users-year')
    if (ctxYear) {
      new Chart(ctxYear, {
        type: 'bar',
        data: {
          labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
          datasets: [{
            label: 'Total Users',
            data: yearlyUsers.data,
            backgroundColor: '#1e1e2d',
            borderRadius: 6,
            barPercentage: 0.6
          }]
        },
        options: {
          maintainAspectRatio: false,
          responsive: true,
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: '#000',
              titleColor: '#FF9D00',
              padding: 10,
              cornerRadius: 8
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              grid: { color: '#ebecf1', borderDash: [3, 2] },
              ticks: { stepSize: 40, font: { size: 10 } }
            },
            x: {
              grid: { color: '#ebecf1', borderDash: [3, 2] },
              ticks: { font: { size: 10 } }
            }
          }
        }
      })
    }
  } catch (e) {
    console.error('Yearly chart error:', e)
  }

  // Initialize Monthly Chart
  try {
    const ctxMonth = document.getElementById('chart-new-users-month')
    if (ctxMonth) {
      const days = Array.from({ length: 31 }, (_, i) => i + 1)
      new Chart(ctxMonth, {
        type: 'bar',
        data: {
          labels: days,
          datasets: [{
            label: 'New Registered Users',
            data: monthlyNewUsers.data,
            backgroundColor: '#007bff',
            borderRadius: 6,
            barPercentage: 0.8
          }]
        },
        options: {
          maintainAspectRatio: false,
          responsive: true,
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: '#000',
              titleColor: '#FF9D00',
              padding: 10,
              cornerRadius: 8
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              grid: { color: '#ebecf1', borderDash: [3, 2] },
              ticks: { stepSize: 20, font: { size: 10 } }
            },
            x: {
              grid: { color: '#ebecf1', borderDash: [3, 2] },
              ticks: { font: { size: 10 } }
            }
          }
        }
      })
    }
  } catch (e) {
    console.error('Monthly chart error:', e)
  }
})
</script>

<template>
  <AuthLayout title="User Dashboard" description="User management dashboard">
    <!-- PAGE HEADER -->
    <div class="page-header mt-5-7">
      <div class="page-leftheader">
        <h4 class="page-title mb-0">User Dashboard</h4>
      </div>
    </div>

    <!-- USER BOX INFO -->
    <div class="row mt-4">
      <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12">
        <div class="card overflow-hidden border-0 stat-box">
          <div class="card-body">
            <div class="stat-content">
              <p class="stat-label mb-3 fs-12 fw-bold ">Total Registered Users</p>
              <h2 class="stat-value mb-0">{{ formatNumber(userData.totalUsers) }}</h2>
            </div>
            <i class="fa-solid fa-user-check stat-icon text-primary"></i>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12">
        <div class="card overflow-hidden border-0 stat-box">
          <div class="card-body">
            <div class="stat-content">
              <p class="stat-label mb-3 fs-12 fw-bold">Online Users</p>
              <h2 class="stat-value mb-0">{{ formatNumber(userData.onlineUsers) }}</h2>
            </div>
            <i class="fa-solid fa-user-headset stat-icon text-warning"></i>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12">
        <div class="card overflow-hidden border-0 stat-box">
          <div class="card-body">
            <div class="stat-content">
              <p class="stat-label mb-3 fs-12 fw-bold">Visitors Today (Registered)</p>
              <h2 class="stat-value mb-0">{{ formatNumber(userData.usersToday) }}</h2>
            </div>
            <i class="fa-solid fa-user-clock stat-icon text-info"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- MONTHLY USAGE ANALYTICS -->
    <div class="row mt-4">
      <!-- Registered Users by Countries -->
      <div class="col-lg-12 col-md-12">
        <div class="card overflow-hidden border-0">
          <div class="card-header d-inline border-0">
            <h3 class="card-title fs-16 mt-2 text-white">Registered User Countries</h3>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-lg-7 col-md-12 col-sm-12">
                <div class="mt-3">
                  <div class="countries-map-placeholder">
                    <div class="text-center">
                      <i class="fa-solid fa-globe fs-60 text-primary mb-3"></i>
                      <p class="fs-14 text-muted">Google Maps Integration Available</p>
                      <small class="text-muted">Top {{ Object.keys(usersByCountry).length }} countries displayed in list</small>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-5 col-md-12 col-sm-12">
                <div class="mt-3 country-users">
                  <h6 class="mb-3">
                    <i class="fa-solid fa-flag me-2"></i>Top 20 Countries
                  </h6>
                  <ul class="country-list">
                    <li v-for="country in topCountries.slice(0, 20)" :key="country.country" class="country">
                      <span class="country-name">{{ country.country }}</span>
                      <span class="country-count">{{ formatNumber(country.users) }}</span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- New Registered Users (Current Month) -->
      <div class="col-lg-12 col-md-12">
        <div class="card overflow-hidden border-0">
          <div class="card-header d-inline border-0">
            <h3 class="card-title fs-16 mt-2 text-white">New Registered Users <span class="text-black">(Current Month)</span></h3>
          </div>
          <div class="card-body mb-3 mt-3">
            <div class="row">
              <div class="col-lg-12 col-md-12 col-sm-12">
                <canvas id="chart-new-users-month" class="h-330"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Total Registered Users (Yearly) -->
      <div class="col-lg-12 col-md-12">
        <div class="card overflow-hidden border-0">
          <div class="card-header d-inline border-0">
            <h3 class="card-title fs-16 mt-2 text-white">Total Registered Users <span class="text-black">(Current Year)</span></h3>
          </div>
          <div class="card-body">
            <div class="row mb-5 mt-2">
              <div class="col-xl-3 col-6">
                <p class="mb-1 fs-12">Total Users</p>
                <h3 class="mb-0 fs-20 fw-bold">{{ formatNumber(userData.totalFreeUsers) }}</h3>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-12 col-md-12 col-sm-12">
                <canvas id="chart-new-users-year" class="h-330"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthLayout>
</template>

<style scoped>
/* Stat Boxes */
.stat-box {
  border-radius: 12px !important;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
}

.stat-box:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
}

.stat-box .card-body {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 2rem;
}

.stat-content {
  flex: 1;
}

.stat-label {
  color: #6b7280;
  margin-bottom: 0.5rem !important;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1f2937;
}

.stat-icon {
  font-size: 2.5rem;
  opacity: 0.8;
}

/* Cards */


/* Countries Map Placeholder */
.countries-map-placeholder {
  background: linear-gradient(135deg, #f3f4f6 0%, #ffffff 100%);
  border: 2px dashed #d1d5db;
  border-radius: 8px;
  padding: 3rem 1rem;
  text-align: center;
  min-height: 330px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Country List */
.country-users {
  padding: 1rem;
}

.country-users h6 {
  font-weight: 600;
  color: #1f2937;
}

.country-list {
  list-style: none;
  padding: 0;
  margin: 0;
  max-height: 400px;
  overflow-y: auto;
}

.country-list::-webkit-scrollbar {
  width: 6px;
}

.country-list::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.country-list::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 3px;
}

.country-list::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}

.country {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid #e5e7eb;
  font-size: 0.875rem;
}

.country:last-child {
  border-bottom: none;
}

.country-name {
  font-weight: 500;
  color: #374151;
}

.country-count {
  font-weight: 600;
  color: #007bff;
  background-color: #e7f1ff;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.8rem;
}

/* Chart Container */
.h-330 {
  height: 330px;
}

/* Responsive */
@media (max-width: 768px) {
  .stat-box .card-body {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }

  .stat-icon {
    order: -1;
  }

  .card-header {
    padding: 1rem;
  }

  .country-list {
    max-height: 250px;
  }
}
</style>
