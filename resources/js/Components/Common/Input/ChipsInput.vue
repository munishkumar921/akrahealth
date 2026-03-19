<script setup>
const props = defineProps({
    formValue: {
        type: Object,
        required: true,
    },
    selectedItems: {
        type: Array,
        required: true,
    },
    name: {
        type: String,
        required: true,
    },
    placeholder: {
        type: String,
        default: "Search...",
    },
});

const emit = defineEmits(["update:selectedItems", "add", "remove"]);

const onInput = () => {
    emit("add", props.formValue[props.name]);
};

const removeItem = (index) => {
    const updatedItems = [...props.selectedItems];
    updatedItems.splice(index, 1);
    emit("update:selectedItems", updatedItems);
};
</script>

<template>
    <div class="search-container d-flex align-items-center">
        <div class="chips-container">
            <div v-for="(item, index) in selectedItems" :key="item.slug">
                <div class="input-group">
                    <div type="text" class="form-control text-truncate">
                        {{ item.name }}
                    </div>
                    <button class="btn btn-outline-secondary" type="button" @click="removeItem(index)"
                        aria-label="Remove">
                        X
                    </button>
                </div>
            </div>
        </div>

        <input type="text" class="search-input flex-grow-1" :class="selectedItems.length > 0 ? 'ml-4' : ''"
            :placeholder="placeholder" v-model="formValue[name]" @input="onInput" />
    </div>
</template>

<style scoped>
.search-container {
    display: flex;
    align-items: center;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    padding: 0.25rem;
    background-color: #fff;
    width: 100%;
}

.chips-container {
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
    max-width: 60%;
    padding-right: 0.25rem;
    scrollbar-width: thin;
}

.chips-container::-webkit-scrollbar {
    height: 6px;
}

.chips-container::-webkit-scrollbar-thumb {
    background-color: #ced4da;
    border-radius: 4px;
}

.search-input {
    min-width: 120px;
    border: none;
    outline: none;
    padding: 0.25rem;
    font-size: 1rem;
}

.search-input:focus {
    outline: none;
}
</style>