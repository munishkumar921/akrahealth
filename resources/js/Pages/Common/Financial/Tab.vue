<script setup>
import { router } from "@inertiajs/vue3";

const props = defineProps({
	currentTab: Object,
  });
  const go = (tab) => router.visit(route(`doctor.finance.${tab}`));


</script>
<template>
    <div class="col-lg-3">
				<div class="iq-card">
					<div class="iq-card-body">
						<div class="finance-menu">
							<button type="button" class="menu-item" :class="{ active: currentTab === 'bills_to_submit' }" @click="go('bills_to_submit')">
								<span class="dot dot-success"></span>
								<span class="label">Bills to Submit</span>
							</button>
							<button type="button" class="menu-item" :class="{ active: currentTab === 'processed_bills' }" @click="go('processed_bills')">
								<span class="dot dot-warning"></span>
								<span class="label">Processed Bills</span>
							</button>
							<button type="button" class="menu-item" :class="{ active: currentTab === 'outstanding_balances' }" @click="go('outstanding_balances')">
								<span class="dot dot-secondary"></span>
								<span class="label">Outstanding Balances</span>
							</button>
							<button type="button" class="menu-item" :class="{ active: currentTab === 'monthly_financial_report' }" @click="go('monthly_financial_report')">
								<span class="dot dot-primary"></span>
								<span class="label">Monthly Financial Report</span>
							</button>
							<button type="button" class="menu-item" :class="{ active: currentTab === 'yearly_financial_report' }" @click="go('yearly_financial_report')">
								<span class="dot dot-dark"></span>
								<span class="label">Yearly Financial Report</span>
							</button>
								<button type="button" class="menu-item" :class="{ active: currentTab === 'custom_report_by_payment_type' }" @click="go('financial_cutom_report_by_payment')">
								<span class="dot dot-pink"></span>
								<span class="label">Custom Report by Payment</span>
							</button>
							<button type="button" class="menu-item" :class="{ active: currentTab === 'custom_report_by_procedure_code' }" @click="go('financial_cutom_report_by_procedure')">
								<span class="dot dot-red"></span>
								<span class="label">Custom Report by Procedure</span>
							</button>
						</div>
					</div>

				</div>
			</div>
</template>
<style scoped>
.finance-menu { display: flex; flex-direction: column; gap: 12px; }
.menu-item {
	display: flex; align-items: center; gap: 10px; width: 100%;
	background: #fff; border: 1px solid #eef0f4; border-radius: 12px;
	padding: 12px 14px; box-shadow: 0 4px 12px rgba(0,0,0,.04);
	cursor: pointer; transition: .2s;
}
.menu-item:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(0,0,0,.06); }
.menu-item.active { border-color: #6f42c1; box-shadow: 0 10px 20px rgba(111,66,193,.15); }
.dot { height: 10px; width: 10px; border-radius: 50%; }
.dot-success { background: #28a745; }
.dot-warning { background: #ffc107; }
.dot-secondary { background: #ff7b29; }
.dot-primary { background: #0d6efd; }
.dot-dark { background: #2e2138; }
.dot-pink{ background: #6f42c1;}
.dot-red{background: rgb(145, 56, 49);}

.label { font-size: 14px; color: #2b2b2b; font-weight: 600; }
</style>