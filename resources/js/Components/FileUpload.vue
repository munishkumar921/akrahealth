<script setup>
import { ref, watch } from "vue";

// Props and emits for v-model
const props = defineProps({
  modelValue: File, // the bound value (from parent, e.g., form.file)
});
const emit = defineEmits(["update:modelValue"]);

const filename = ref("");

// Watch for external reset
watch(
  () => props.modelValue,
  (newVal) => {
    if (!newVal) filename.value = "";
  }
);

// Handle file input
const onFileChange = (event) => {
  const file = event.target.files[0];

  if (file) {
    filename.value = file.name;
    emit("update:modelValue", file); // emit file to parent
  } else {
    filename.value = "";
    emit("update:modelValue", null);
  }
};
</script>

<template>
  <div class="my-3">
    <div class="input-group">
      <div class="custom-file w-100">
        <input
          type="file"
          class="custom-file-input"
          id="inputFileUpload"
          @change="onFileChange"
        />
        <label class="custom-file-label text-left" for="inputFileUpload">
          {{ filename || "Choose file" }}
        </label>
      </div>
    </div>
  </div>
</template>
 
