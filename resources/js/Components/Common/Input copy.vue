<script setup>
import { ref, watch } from "vue";

const props = defineProps({
    modelValue: String,
    label: { type: String, required: true },
    placeholder: { type: String, default: "" },
    type: { type: String, default: "text" },
    icon: String,
    name: String,
    subClass: String,
});

const emit = defineEmits(["update:modelValue"]);
const modelValue = ref(props.modelValue || "");

watch(
    () => props.modelValue,
    (newValue) => {
        modelValue.value = newValue;
    }
);

watch(modelValue, (newValue) => {
    emit("update:modelValue", newValue);
});
</script>

<template>
    <section class="iq-search-bar align-items-center">
        <h6 class="w-50 pt-4" :id="label">{{ label }}</h6>
        <span class="d-flex">
            <input
                v-model="modelValue"
                :type="type"
                :name="name"
                :id="name"
                class="text search-input"
                :placeholder="placeholder"
                :class="subClass"
                :aria-labelledby="label"
            />
            <i :class="`${icon} icon`" v-if="icon"></i>
        </span>
    </section>
</template>

<style scoped>
.icon {
    margin-left: -9%;
}
</style>
