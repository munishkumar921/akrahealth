<script setup>
import { defineProps, defineEmits, ref, watch, onMounted, onUnmounted, useSlots, computed } from "vue";

const props = defineProps({
    modelValue: [String, Array],
    required: Boolean,
    placeholder: String,
    multiple: Boolean,
    label: { type: String, required: true },
    validFeedback: {
        type: String,
        default: "",
    },
    invalidFeedback: {
        type: String,
        default: "Please select an option!",
    },
    error: {
        type: String,
        default: "",
    },
    options: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits();

const isValidated = ref(false);
const selectedValue = ref(props.modelValue);

watch(
    () => props.modelValue,
    (newValue) => {
        selectedValue.value = props.multiple ? (Array.isArray(newValue) ? newValue : []) : newValue;
        isValidated.value = true;
    }
);
const isOpen=ref(false);
watch(selectedValue, (newValue) => {
    emit("update:modelValue", newValue);
});

const updateValue = () => {
    emit("update:modelValue", selectedValue.value);
};

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

const selectOption = (value) => {
    if (props.multiple) {
        
        if (selectedValue.value.includes(value)) {
            selectedValue.value = selectedValue.value.filter(v => v !== value);
        } else {
            selectedValue.value = [...selectedValue.value, value];
        }
    } else {
        selectedValue.value = value;
        isOpen.value = false;
    }
};
const slots = useSlots();

// Get options from slot content - handles both v-for and static options
const slotOptions = computed(() => {
    const defaultSlot = slots.default?.() || [];
    
    // If options prop is provided, use it
    if (props.options && props.options.length > 0) {
        return props.options.map(opt => ({
            value: opt.id || opt.value || opt,
            label: opt.name || opt.label || opt
        }));
    }
    
    // Otherwise, try to extract from slots
    let extractedOptions = [];
    
    if (defaultSlot.length > 0) {
        // Handle array of VNodes
        if (Array.isArray(defaultSlot)) {
            defaultSlot.forEach(vnode => {
                if (vnode.children && Array.isArray(vnode.children)) {
                    // For v-for rendered options, children will contain the option VNodes
                    vnode.children.forEach(child => {
                        if (child.props) {
                            extractedOptions.push({
                                value: child.props.value,
                                label: child.children
                            });
                        }
                    });
                } else if (vnode.props) {
                    // Static option
                    extractedOptions.push({
                        value: vnode.props.value,
                        label: vnode.children
                    });
                }
            });
        }
    }
    
    return extractedOptions;
});

// Get option label by value - supports both prop options and slot options
const getOptionLabel = (value) => {
    // First check if we have prop options
    if (props.options && props.options.length > 0) {
        const found = props.options.find(opt => {
            const optValue = opt.id || opt.value || opt;
            return String(optValue) === String(value);
        });
        if (found) {
            return found.name || found.label || found;
        }
    }
    
    // Fall back to slot parsing
    const defaultSlot = slots.default?.() || [];
    let options = [];

    if (defaultSlot.length > 0 && defaultSlot[0].children && Array.isArray(defaultSlot[0].children)) {
        options = defaultSlot[0].children;
    } else {
        options = defaultSlot;
    }

    for (let option of options) {
        if (option.props?.value == value) {
            return option.children;
        }
    }
    return value;
};

const removeValue = (value) => {
    selectedValue.value = selectedValue.value.filter(v => v !== value);
};

const handleClickOutside = (event) => {
    const container = event.target.closest('.selectpicker-container');
    if (!container) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div class="mb-2">
        <label :for="label" class="form-label" v-if="label">{{ label }} <span v-if="required" class="text-danger">*</span></label>
        
        <div v-if="props.multiple" class="selectpicker-container" :class="{ 'open': isOpen }">
            <div class="selectpicker-toggle form-control rounded h-auto d-flex align-items-center justify-content-between" @click="toggleDropdown">
                <div class="selected-values">
                    <span v-if="props.multiple && selectedValue.length === 0" class="text-muted">{{ placeholder || 'Select options' }}</span>
                    <span v-else-if="!props.multiple" class="selected-text">{{ selectedValue ? getOptionLabel(selectedValue) : (placeholder || 'Select an option') }}</span>
                    <div v-else class="d-flex flex-wrap gap-1 ">
                        <span v-for="value in selectedValue.slice(0, 2)" :key="value" class="badge bg-primary text-white">
                             {{ getOptionLabel(value) }}
                            <button type="button" class="btn-close btn-close-white ms-1" @click.stop="removeValue(value)" style="font-size: 0.5rem;"></button>
                        </span>
                        <span v-if="selectedValue.length > 2">
                            +{{ selectedValue.length - 2 }} more
                        </span>
                    </div>
                </div>
           </div>
            <div v-if="isOpen" class="selectpicker-dropdown">
                <!-- Hidden original slot for fallback -->
                <div id="select-options" class="d-none">
                    <slot></slot>
                </div>
                <!-- Render options from slotOptions computed property -->
                <div class="selectpicker-options">
                    <div v-for="option in slotOptions" :key="option.value" class="selectpicker-option" :class="{ 'selected': props.multiple ? selectedValue.includes(option.value) : selectedValue === option.value }" @click="selectOption(option.value)">
                        {{ option.label }}
                        <span v-if="props.multiple && selectedValue.includes(option.value)" class="checkmark">✓</span>
                    </div>
                </div>
            </div>
        </div>
        <div v-else>
             <select v-model="selectedValue" class="form-select rounded" :required="required" @change="updateValue">
            <option v-if="!selectedValue" value="" disabled>{{ placeholder }}</option>
            <slot></slot>
            </select>
        </div>
        <div v-if="error" class="text-danger mt-2">{{ error }}</div>
        <div v-else-if="isValidated && !selectedValue" class="invalid-feedback">
            {{ invalidFeedback }}
        </div>
        <div v-else-if="validFeedback" class="valid-feedback">{{ validFeedback }}</div>
    </div>
</template>

<style scoped>
.selectpicker-container {
    position: relative;
}

.selectpicker-toggle {
    cursor: pointer;
    min-height: 38px;
    padding: 0.375rem 0.75rem;
      background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23384c74' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3E%3C/svg%3E");
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 16px 12px;    
    width: 100%;
        transition: border-color 0.15s ease-in-out, -webkit-box-shadow 0.15s ease-in-out;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out, -webkit-box-shadow 0.15s ease-in-out;
}

.selectpicker-toggle:hover {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.selected-values {
    flex: 1;
    white-space: nowrap; 
    overflow: hidden;
}

.selected-text {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
 

.selectpicker-container.open .dropdown-arrow {
    transform: rotate(180deg);
}

.selectpicker-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1000;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    max-height: 200px;
    overflow-y: auto;
}

.selectpicker-options {
    padding: 0.5rem 0;
}

.selectpicker-option {
    padding: 0.375rem 0.75rem;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.selectpicker-option:hover {
    background-color: #f8f9fa;
}

.selectpicker-option.selected {
    background-color: #e3f2fd;
    color: #1976d2;
}

.checkmark {
    color: #1976d2;
    font-weight: bold;
}

.badge {
    font-size: 0.75rem;
}
 

</style>
