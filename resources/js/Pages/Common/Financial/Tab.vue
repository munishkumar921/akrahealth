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
