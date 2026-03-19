<script setup>
const props = defineProps({
    tabs: {
        type: Array,
        required: true,
        default: () => [],
    },
    currentTab: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(["update:currentTab"]);
</script>

<template>
    <div class="container">
        <nav class="tab-nav mb-5 mt-4" v-if="!route().current('doctor.encounters.create')">
            <div v-for="tab in tabs" :key="tab.value" :class="{ 'active-tab': currentTab === tab.value }"
                @click="emit('update:currentTab', tab.value)" class="tab shadow rounded cursor-pointer text-center"
                :aria-selected="currentTab === tab.value" role="tab">
                {{ tab.label }}
            </div>
        </nav>

        <transition name="fade" mode="out-in">
            <slot name="content"></slot>
        </transition>
    </div>
</template>

<style scoped>
.tab-nav {
    display: flex;
    position: relative;
    align-items: center;
    justify-content: space-around;
    margin-bottom: 1rem;
    /* flex-wrap: wrap; */
}

.tab {
    padding: 0.5rem 1rem;
    font-weight: 500;
    cursor: pointer;
    color: #333;
    flex-basis: 20%;
    /* Default width for tabs */
}

.active-tab {
    color: #007bff;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.tab-underline {
    position: absolute;
    bottom: -10px;
    left: 0;
    height: 60px !important;
    overflow: hidden;
    width: 25%;
    transition: transform 0.3s ease;
}

@media (max-width: 768px) {
    .tab {
        flex-basis: 50%;
        padding: 0.5rem;
        font-size: 0.9rem;
    }
}

@media (max-width: 480px) {
    .tab {
        flex-basis: 100%;
        text-align: center;
    }
}
</style>
