<script setup>
import { ref } from "vue";
 import "vue-datepicker-next/index.css";
import { router } from '@inertiajs/vue3'

const props = defineProps({
  modelValue: [String, Number, File],
  type: { type: String, default: "text" },
  name: String,
  placeholder: String,
  required: Boolean,
  label: { type: String, required: true },
  icon: String,
  validFeedback: { type: String, default: "" },
  invalidFeedback: { type: String, default: "" },
  needMargin: { type: Boolean, default: false },
  rows: { type: Number, default: 3 },
  error: { type: String, default: "" },
  accept: String,
  readonly: Boolean,
});

const emit = defineEmits(["update:modelValue"]);
const isValidated = ref(false);
const fileName = ref("");
const removeSearch = () => {
  isValidated.value = false;
  router.get(route(route().current()));
};
const onInput = (e) => {
  let value = e.target.value.replace(/\D/g, '').slice(0, 10);
  e.target.value = value;
  emit('update:modelValue', value);
};


</script>

<template>
  <div>
    <label v-if="label" :for="name" class="form-label">{{ label }}  <span v-if="required" class="text-danger">*</span></label>

    <div class="input-group">
      <!-- 📝 Textarea -->
      <template v-if="type === 'textarea'">
        <textarea
          :value="modelValue"
          :name="name"
          :placeholder="placeholder"
          :rows="rows"
          class="form-control"
          :required="required"
          @input="$emit('update:modelValue', $event.target.value)"
        />
      </template>

      <!-- 📁 File -->
      <template v-else-if="type === 'file'">
        <div class="input-group w-100">
          <input
            type="text"
            class="form-control mb-3"
            :value="fileName || placeholder || 'Choose file...'"
            readonly
          />
          <label class="input-group-text cursor-pointer bg-light border" :for="id">
            Browse
          </label>
          <input
            type="file"
            :id="id"
            class="d-none"
            :accept="accept"
            @change="
              $emit('update:modelValue', $event.target.files[0]);
              fileName = $event.target.files[0]?.name || '';
            "
          />
        </div>
      </template>
      <template v-else-if="type === 'tel'">
      <input
        type="tel"
        inputmode="numeric"
        minlength="10"
        maxlength="10"
        @input="onInput"
        placeholder="Enter 10-digit mobile number"
        class="form-control"
      />

</template>


      <!-- 📅 Date -->
      <!-- <template v-else-if="type === 'date'">
        <div class="input-group">
           <DatePicker
        class="w-100"
        v-model:value="internalDate"
        format="MM-DD-YYYY"
         placeholder="Select date"
      />
          <span v-if="icon" class="input-group-text">
            <i :class="icon"></i>
          </span>
        </div>
      </template> -->

      <!-- 🔍 Search -->
      <template v-else-if="type === 'search'">
        <div class="position-relative">
          <input
            :value="modelValue"
            type="text"
            :name="name"
            :placeholder="placeholder"
            class="form-control pr-5"
            :required="required"
            @input="$emit('update:modelValue', $event.target.value)"
          />
          <span v-if="modelValue" class="clear-icon" @click="removeSearch">x</span>
        </div>
      </template>

      <!-- 🔤 Default Input -->
      <template v-else>
        <input
          :value="modelValue"
          :type="type"
          :name="name"
          :placeholder="placeholder"
          class="form-control" :class="error?'mb-2':'mb-3'"
          :required="required"
          :readonly="readonly"
          @input="$emit('update:modelValue', $event.target.value)"
        />
      </template>

      <span v-if="icon" class="input-group-text">
        <i :class="icon"></i>
      </span>
    </div>

    <!-- Feedback -->
    <span>
      <div v-if="error" class="text-danger mb-3">{{ error }}</div>
      <div v-else-if="isValidated && !modelValue" class="invalid-feedback d-block">
        {{ invalidFeedback }}
      </div>
      <div v-else-if="validFeedback" class="valid-feedback d-block">
        {{ validFeedback }}
      </div>
    </span>
  </div>
</template>

<style scoped>
.input-group-text {
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f8f9fa;
  border: 1px solid #ced4da;
  border-radius: 0.25rem;
  color: #495057;
  cursor: pointer;
}

.clear-icon {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  font-size: 20px;
  color: #888;
}
.clear-icon:hover {
  color: #000;
}

.mx-datepicker-popup {
  z-index: 9999 !important;
}
.input-group > .form-control, .input-group > .form-select:hover
{
  content: unset;
}
</style>
