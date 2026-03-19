<script setup>
import { computed } from "vue";
import CloseButton from "@/Components/Common/Buttons/CloseButton.vue";

const props = defineProps({
    title: {
        type: String,
        default: "Modal Title",
    },
    isOpen: {
        type: Boolean,
        default: false,
    },
    actionButtons: {
        type: Array,
        default: () => [],
    },
    size: {
        type: String,
        default: "md",
        validator: (value) => ["sm", "md", "lg", "xl"].includes(value),
    },
    autoClose: {
        type: Boolean,
        default: true,
    },
    showCloseButton: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(["close"]);
const closeModal = () => emit("close");

const sizeClass = computed(() => {
    return {
        sm: "modal-sm",
        md: "modal-md",
        lg: "modal-lg",
        xl: "modal-xl",
    }[props.size];
});
</script>

<template>
    <transition name="fade">
        <div
            class="modal-overlay"
            v-if="isOpen"
            @click.self="autoClose && closeModal()"
        >
            <div :class="['modal-content', sizeClass]">

                <!-- HEADER -->
                <div class="form-body bg-primary">
                    <div class="form-items p-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="title text-white mb-0">
                                {{ title }}
                            </p>
                            <CloseButton
                                v-if="showCloseButton"
                                @close="closeModal()"
                            />
                        </div>
                    </div>
                </div>

                <!-- BODY -->
                <div class="modal-scrollable-content p-3">
                    <slot />
                </div>

                <!-- FOOTER -->
                <div
                    class="modal-footer bg-primary"
                    v-if="actionButtons.length"
                >
                    <button
                        v-for="(button, index) in actionButtons"
                        :key="index"
                        class="btn btn-light btn-sm"
                        @click="button.action"
                    >
                        {{ button.label }}
                    </button>
                </div>

            </div>
        </div>
    </transition>
</template>

<style scoped>
/* ================= OVERLAY ================= */
.modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ================= MODAL ================= */
.modal-content {
    display: flex;
    flex-direction: column;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    max-height: 90vh;
     border-radius: 15px;
}

/* ================= BODY ================= */
.modal-scrollable-content {
    flex: 1;
    overflow-y: auto;
    max-height: calc(90vh - 120px);
}

/* ================= TITLE ================= */
.title {
    font-size: 18px;
    font-weight: 500;
    width: 85%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ================= SIZE (DESKTOP) ================= */
.modal-sm { width: 300px; }
.modal-md { width: 420px; }
.modal-lg { width: 600px; }
.modal-xl { width: 760px; }

/* ================= MOBILE FIX ================= */
@media (max-width: 768px) {

    .modal-content {
        width: 95% !important;
        max-height: 65vh;
        border-radius: 16px;
    }

    .modal-scrollable-content {
        max-height: calc(85vh - 100px);
        padding: 12px !important;
    }

    .title {
        font-size: 15px;
    }

    .form-items {
        padding: 10px !important;
       
    }
    .form-body {
        border-radius: 16px 16px 0 0;
    }

    .modal-footer {
        padding: 8px 12px;
        gap: 8px;
    }

    .modal-footer .btn {
        font-size: 0.8rem;
        padding: 5px 10px;
    }
}

/* ================= ANIMATION ================= */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* ================= PRINT ================= */
@media print {
    .modal-overlay {
        background: none;
        position: static;
        display: block;
    }
    .modal-content {
        box-shadow: none;
        width: 100%;
        max-height: none;
    }
    .modal-scrollable-content {
        max-height: none;
    }
}
</style>
