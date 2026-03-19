<script setup>
import { defineProps } from "vue";

const props = defineProps({
    currentTab: {
        type: String,
        required: true,
    },
    headings: {
        type: Array,
        required: true,
    },
    tableData: {
        type: Object,
        required: true,
    },
    showHeading: Boolean,
    showActions: Boolean,
});
</script>

<template>
   	<div class="iq-card-body">
		<div id="table" class="table-editable">
			<div class="table-responsive table-scroll">
				<table class="table table-bordered table-striped text-center align-middle">
					<thead v-if="showHeading">
						<tr>
							<th v-for="heading in headings" :key="heading">
								{{ heading }}
							</th>
							<th v-if="showActions">Actions</th>
						</tr>
					</thead>
					<tbody v-if="currentTab">
						<tr v-for="order in tableData[currentTab]" :key="order.id">
							<td v-for="heading in headings" :key="heading">
								{{ order[heading.toLowerCase()] }}
							</td>
							<td v-if="showActions">
								<div class="float-end">
									<slot name="actions"></slot>
								</div>
							</td>
						</tr>
					</tbody>
					<tbody v-else>
						<tr v-for="order in tableData" :key="order.id">
							<td v-for="heading in headings" :key="heading">
								{{ order[heading.toLowerCase()] }}
							</td>
							<td v-if="showActions">
								<div class="float-end">
									<slot name="actions"></slot>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</template>
 