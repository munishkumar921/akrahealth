<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { ref, computed, onMounted } from 'vue';
import Chart from 'chart.js/auto';

// Sample data (demo)
const currencySymbol = '$';
const totalDataMonthly = ref({
  income_current_month: [{ data: 12500 }],
  income_past_month: [{ data: 10000 }],
  spending_current_month: 4500,
  spending_past_month: 4000,
});
const totalDataYearly = ref({
  total_income: [{ data: 120000 }],
  total_spending: [{ data: 80000 }],
});

// Chart data (monthly totals for the year)
const chartData = ref({
  total_income: [8000, 9500, 12000, 11000, 9000, 11500, 13000, 12500, 14000, 13500, 14500, 15000]
});

const incomeDataset = computed(() => chartData.value.total_income.map(n => Number(n)));

function mainPercentageDifference(past, current) {
  past = Number(past) || 0;
  current = Number(current) || 0;
  if (past === 0) {
    return current === 0
      ? '<span class="text-muted"> 0%</span>'
      : '<span class="text-success"><i class="fa fa-caret-up"></i> 100%</span>';
  }
  if (current === 0) {
    return '<span class="text-danger"><i class="fa fa-caret-down"></i> 100%</span>';
  }
  if (past === current) return '<span class="text-muted"> 0%</span>';
  const difference = current - past;
  const totalDifference = Math.abs(difference);
  const change = (totalDifference / past) * 100;
  if (difference > 0) {
    return `<span class="text-success"><i class="fa fa-caret-up"></i> ${change.toFixed(1)}%</span>`;
  }
  return `<span class="text-danger"><i class="fa fa-caret-down"></i> ${change.toFixed(1)}%</span>`;
}

const income_change_html = computed(() => {
  const income_current = totalDataMonthly.value.income_current_month?.[0]?.data ?? 0;
  const income_past = totalDataMonthly.value.income_past_month?.[0]?.data ?? 0;
  return mainPercentageDifference(income_past, income_current);
});
const spending_change_html = computed(() => {
  const sp_current = Number(totalDataMonthly.value.spending_current_month || 0);
  const sp_past = Number(totalDataMonthly.value.spending_past_month || 0);
  return mainPercentageDifference(sp_past, sp_current);
});

onMounted(() => {
  const ctx = document.getElementById('chart-income-dashboard');
  if (!ctx) return;
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
      datasets: [{
        label: 'Total Income',
        data: incomeDataset.value,
        backgroundColor: '#007bff',
        borderWidth: 1,
        borderRadius: 20,
        barPercentage: 0.5,
        fill: true
      }]
    },
    options: {
      maintainAspectRatio: false,
      responsive: true,
      animation: {
        duration: 600
      },
      scales: {
        y: { beginAtZero: true }
      }
    }
  });
});
</script>

<template>
  <AuthLayout title="Finance Dashboard" description="Finance Management" heading="Finance Dashboard">
   <div class="page-header mt-5-7">
      <div class="page-leftheader">
        <h4 class="page-title mb-0">Finance Dashboard</h4>
      </div>
    </div>
    <div class="row">
      <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
        <div class="card overflow-hidden border-0">
          <div class="card-body">
            <div class="d-flex align-items-end justify-content-between">
              <div>
                <p class="mb-3 fs-12 font-weight-bold">Total Income <span class="text-muted">(Current Month)</span></p>
                <h2 class="mb-0"><span class="number-font fs-14">{{ currencySymbol }}{{ Number(totalDataMonthly.income_current_month?.[0]?.data ?? 0).toFixed(2) }}</span>
                <span class="ml-2 text-muted fs-11 data-percentage-change"><span class="fs-12" v-html="income_change_html"></span></span></h2>
              </div>
              <span class="text-success fs-40 mt-m1"><i class="fa fa-dollar-sign" style="font-size:34px;line-height:1;color:inherit"></i></span>
            </div>
            <div class="d-flex mt-2">
              <div>
                <span class="text-muted fs-12 mr-1">Last Month:</span>
                <span class="number-font fs-12"><i class="fa fa-chain mr-1 text-success"></i>{{ currencySymbol }}{{ Number(totalDataMonthly.income_past_month?.[0]?.data ?? 0).toFixed(2) }}</span>
              </div>
              <div class="ml-auto">
                <span class="text-muted fs-12 mr-1">Current Year:</span>
                <span class="number-font fs-12"><i class="fa fa-bookmark mr-1 text-success"></i>{{ currencySymbol }}{{ Number(totalDataYearly.total_income?.[0]?.data ?? 0).toFixed(2) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="card overflow-hidden border-0">
          <div class="card-body">
            <div class="d-flex align-items-end justify-content-between">
              <div>
                <p class="mb-3 fs-12 font-weight-bold">Total Spending <span class="text-muted">(Current Month)</span></p>
                <h2 class="mb-0"><span class="number-font fs-14">{{ currencySymbol }}{{ Number(totalDataMonthly.spending_current_month ?? 0).toFixed(2) }}</span>
                <span class="ml-2 text-muted fs-11 data-percentage-change"><span class="fs-12" v-html="spending_change_html"></span></span></h2>
              </div>
              <span class="text-danger fs-40 mt-m1"><i class="fa fa-credit-card" style="font-size:34px;line-height:1;color:inherit"></i></span>
            </div>
            <div class="d-flex mt-2">
              <div>
                <span class="text-muted fs-12 mr-1">Last Month:</span>
                <span class="number-font fs-12"><i class="fa fa-chain mr-1 text-danger"></i>{{ currencySymbol }}{{ Number(totalDataMonthly.spending_past_month ?? 0).toFixed(2) }}</span>
              </div>
              <div class="ml-auto">
                <span class="text-muted fs-12 mr-1">Current Year:</span>
                <span class="number-font fs-12"><i class="fa fa-bookmark mr-1 text-danger"></i>{{ currencySymbol }}{{ Number(totalDataYearly.total_spending?.[0]?.data ?? 0).toFixed(2) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-3">
      <div class="col-lg-12 col-md-12">
        <div class="card overflow-hidden border-0">
          <div class="card-header">
            <h3 class="card-title fs-16 mt-2 mb-2 text-white">Income Analysis <span>(Current Year)</span></h3>
          </div>
          <div class="card-body">
            <div class="row mb-5 mt-2">
              <div class="col-xl-3 col-12 ">
                <p class="mb-1 fs-12">Total Income</p>
                <h3 class="mb-0 fs-20 number-font">{{ currencySymbol }}{{ Number(totalDataYearly.total_income?.[0]?.data ?? 0).toFixed(2) }}</h3>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-12 col-md-12 col-sm-12">
                <div>
                  <canvas id="chart-income-dashboard" class="h-330" style="width:100%;height:330px"></canvas>
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
.number-font { font-family: inherit; }
</style>
