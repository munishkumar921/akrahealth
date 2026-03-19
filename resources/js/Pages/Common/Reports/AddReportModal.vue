<script setup>
import { ref } from "vue";
import BaseSelect from "../../../Components/Common/Input/BaseSelect.vue";
import BaseInput from "../../../Components/Common/Input/BaseInput.vue";
import {
    genderOptions,
    operatorOptions,
    reportFieldOptions,
} from "../../../Data/commonData";
import BaseCheckbox from "../../../Components/Common/Input/BaseCheckbox.vue";

const props = defineProps({
    form: Object,
});
const emit = defineEmits(["close", "submit"]);

const fieldSets = ref([{ field: "", operator: "", value: "" }]);

const isValidated = ref(false);

const addFieldSet = () => {
    if (fieldSets.value.length < 5) {
        fieldSets.value.push({ field: "", operator: "", value: "" });
    }
};

const removeFieldSet = (index) => {
    if (fieldSets.value.length > 1) {
        fieldSets.value.splice(index, 1);
    }
};

const submitForm = () => {
    isValidated.value = true;
    emit("submit");
};

const closeModal = () => {
    emit("close");
};
</script>

<template>
    <form
        @submit.prevent="submitForm"
        novalidate
        class="needs-validation"
        :class="{ 'was-validated': isValidated }"
    >
        <div class="border rounded pt-2 mb-3">
            <div
                v-for="(fieldSet, index) in fieldSets"
                :key="index"
                class="d-flex align-items-center"
            >
                <div
                    class="w-25 d-flex align-items-center justify-content-center"
                >
                    <button
                        v-if="fieldSets.length < 5"
                        type="button"
                        class="btn btn-info me-2"
                        @click="addFieldSet"
                    >
                        <i class="bi bi-plus-lg"></i>
                    </button>

                    <button
                        v-if="fieldSets.length > 1"
                        type="button"
                        class="btn btn-danger"
                        @click="removeFieldSet(index)"
                    >
                        <i class="bi bi-dash-lg"></i>
                    </button>
                </div>

                <div class="w-75">
                    <div class="mb-2">
                        <BaseSelect
                            v-model="fieldSet.field"
                            placeholder="Field"
                            :name="'field' + index"
                            required
                        >
                            <option
                                v-for="option in reportFieldOptions"
                                :key="option"
                                :value="option"
                            >
                                {{ option }}
                            </option>
                        </BaseSelect>
                    </div>

                    <div class="mb-2">
                        <BaseSelect
                            v-model="fieldSet.operator"
                            placeholder="Operator"
                            :name="'operator' + index"
                            required
                        >
                            <option
                                v-for="option in operatorOptions"
                                :key="option"
                                :value="option"
                            >
                                {{ option }}
                            </option>
                        </BaseSelect>
                    </div>

                    <BaseInput
                        v-model="fieldSet.value"
                        placeholder="Value"
                        type="text"
                        required
                    />
                </div>
            </div>
        </div>

        <div class="container mt-2 mb-2">
            <BaseCheckbox
                v-model="form.is_active"
                label="Active Patients Only"
            />
            <BaseCheckbox
                v-model="form.without_insurance"
                label="Patients Without Insurance"
            />
        </div>

        <BaseSelect
            v-model="form.gender"
            placeholder="Gender"
            name="Gender"
            required
        >
            <option
                v-for="gender in genderOptions"
                :key="gender"
                :value="gender"
            >
                {{ gender }}
            </option>
        </BaseSelect>

        <BaseInput
            v-model="form.title"
            placeholder="Report Title"
            type="text"
            required
        />

        <div class="form-button mt-4 px-3 d-flex justify-content-end gap-3">
            <button type="submit" class="btn btn-primary">Save</button>
            <button
                type="button"
                class="btn btn-danger"
                @click="closeModal"
            >
                Cancel
            </button>
        </div>
    </form>
</template>
