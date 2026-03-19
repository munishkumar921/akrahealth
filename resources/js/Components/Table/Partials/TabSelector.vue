<script setup>
import { defineProps, defineEmits } from "vue";
import ActionButtons from "./ActionButtons.vue";

const props = defineProps({
    currentTab: String,
    tabs: {
        type: Array,
        required: true,
    },
    actionButtons: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["update:currentTab"]);

const changeTab = (tab) => {
    if (props.currentTab !== tab.value) {
        emit("update:currentTab", tab.value);
    }
};
</script>

<template>
         <div class="d-flex justify-content-between">
                 <div class="d-flex justify-content-end align-items-center">
                    <div class="todo-notification d-flex align-items-center">                         
                        <div  v-if="tabs.length">
                            <div v-for="tab in tabs" :key="tab.value" class="form-check form-check-inline">
                                 <input class="form-check-input" type="checkbox" :name="'tab-selector'" :id="tab.value" :value="tab.value"
                                    :checked="currentTab === tab.value" @change="changeTab(tab)" :class="tab.value === 'is_active'?'status-check status-check--green':'status-check status-check--grey'" />
                                <label class="form-check-label" :for="tab.value">
                                    {{ tab.label }}
                                </label>
                            </div>
                        </div>
                        <ActionButtons :actionButtons="actionButtons" />
                    </div>
                </div>
            </div>
 </template>
 <style scoped>

.status-check {
	appearance: none;
	width: 16px;
	height: 16px;
	border: 2px solid #d1d5db;
	/* gray-300 */
	border-radius: 4px;
	display: inline-block;
	position: relative;
	cursor: pointer;
	background: #fff;
}
 
.status-check:focus {
	outline: none;
	box-shadow: 0 0 0 2px rgba(59, 130, 246, .2);
}

.status-check--green:checked {
	border-color: #06c270;
	/* gray-400 */
	background-color: #06c270;
}

.status-check--grey:checked {
	border-color: #9ca3af;
	/* gray-400 */
	background-color: #9ca3af;
}

/* tick icon */
.status-check:checked::after {
	content: "";
	position: absolute;
	left: 4px;
	top: 1px;
	width: 4px;
	height: 8px;
	border: solid #fff;
	border-width: 0 2px 2px 0;
	transform: rotate(45deg);
}
</style>
