<script setup>
import { ref, defineEmits } from "vue";

const props = defineProps({
    modelValue: String,
    placeholder: String,
    searchResult: Array,
    loader: Boolean,
});

const searchQuery = ref("");

const emit = defineEmits(["update:modelValue"]);
const search = () => {

    emit("update:modelValue", searchQuery.value);


};
</script>

<template>
        <div class="row">
            <div class="col-md-12">
                <div class="form">
                    <span v-if="loader" class="spinner-border spinner-border-sm"></span>
                    <i  v-else class="fa fa-search"></i>
                    <input
                        type="text"
                        class="form-control form-input"
                        :placeholder="placeholder"
                        v-model="searchQuery"
                        @input="search"
                    />
                </div>
            </div>
        </div>
</template>

<style scoped>
.form {
    position: relative;
}

.form .fa-search {
    position: absolute;
    top: 13px;
    right: 20px;
    color: #9ca3af;
}
.form .spinner-border {
    position: absolute;
    top: 13px;
    right: 20px;
    color: #9ca3af;
}
.form .form-input {
    padding-right: 30px;
}
</style>
