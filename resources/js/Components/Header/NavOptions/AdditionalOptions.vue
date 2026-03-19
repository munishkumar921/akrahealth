<script setup>
import { ref, watch } from "vue";
import "../header.css";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    options: {
        type: Array,
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
    isVisible: {
        type: Boolean,
        required: true,
    },
});

const animatedOptions = ref([]);

const toggleOptions = () => {
    animatedOptions.value = props.isVisible
        ? props.options.map((option, index) => ({
              ...option,
              delay: index * 100,
          }))
        : [];
};

watch(() => props.isVisible, toggleOptions);

const clickAction = (link) => {
    router.visit(route(link));
};
</script>

<template>
    
    <div
        v-if="isVisible"
        class="additional-options bg-white shadow-lg rounded d-flex flex-column gap-2"
        :id="'expanded-options-' + label"
        @click.stop
    >
    <div
			v-for="option in animatedOptions"
			:key="option.id"
			class="option-item d-flex align-items-center a-option"
			role="button"
			tabindex="0"
			@click.stop="clickAction(option.path)"
			:style="{ animationDelay: option.delay + 'ms' }"
		>
			<div class="nav-card shadow-lg">
				<div class="nav-card-content">
					<div class="nav-card-icon" v-html="option.svg"></div>
				</div>
			</div>
			<span class="option-label ml-2">{{ option.label }}</span>
		</div>
	</div>
</template>

<style scoped>
.option-item { gap: 5px; padding: 2px 5px; border-radius: 12px; }
.option-label { font-size: 14px; color: #4a4a4a; white-space: nowrap; }
</style>
