<script setup>
const emit = defineEmits(["close"]);

const closeModal = () => emit("close");
</script>

<template>
    <button type="button" class="btn-close" @click="closeModal">
        <span class="icon-cross"></span>
        <span class="visually-hidden">Close</span>
    </button>
</template>

<style scoped>
.btn-close {
    margin: 0;
    border: 0;
    padding: 0;
    background:none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    flex-flow: column nowrap;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    transition: all 150ms;
}

.btn-close .icon-cross {
    margin: 0;
    padding: 0;
    border: 0;
    background: none;
    position: relative;
    width: 15px;
    height: 15px;
}

.btn-close .icon-cross:before,
.btn-close .icon-cross:after {
    content: "";
    position: absolute;
    top: 4px;
    left: 0;
    right: 0;
    height: 6px;
    background: #fff;
    border-radius: 6px;
}

.btn-close .icon-cross:before {
    transform: rotate(45deg);
}

.btn-close .icon-cross:after {
    transform: rotate(-45deg);
}

.btn-close .icon-cross span {
    display: block;
}

.btn-close:hover,
.btn-close:focus {
    transform: rotateZ(90deg);
    background: #0052cc;
}

.visually-hidden {
    position: absolute !important;
    clip: rect(1px, 1px, 1px, 1px);
    padding: 0 !important;
    border: 0 !important;
    height: 1px !important;
    width: 1px !important;
    overflow: hidden;
}
</style>
