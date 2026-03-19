<script setup>
import { useForm } from "@inertiajs/vue3";
import { watch } from "vue";
import Editor from 'primevue/editor';
import InputError from '@/Components/InputError.vue';
  
const props = defineProps({
	countries:Object,
  permissions: Array,
});
const emit = defineEmits(["close"]);

const form = useForm({
  id: "",
  plan_for: '',
  title: '',
  frequency: '',
  price: '',
  currency: '',
  status: true,
  features:'',
  country_id:'',
  permissions: [],
});

// Watcher to auto-fill currency when country changes
watch(() => form.country_id, (newCountry) => {
  if (newCountry) {
    const country = props.countries.find(c => c.id === newCountry);
    if (country) {
      form.currency = country.currency;
    }
  } else {
    form.currency = '';
  }
});
 
const statusOptions = [
  { value: true, label: 'Active' },
  { value: false, label: 'Inactive' },
];
const tools = [
    ["Bold", "Italic", "Underline", "Strikethrough"],
    ["Subscript", "Superscript"],
    ["AlignLeft", "AlignCenter", "AlignRight", "AlignJustify"],
    ["Indent", "Outdent"],
    ["OrderedList", "UnorderedList"],
    "FontSize",
    "FontName",
    "FormatBlock",
    ["Undo", "Redo"],
    ["Link", "Unlink", "InsertImage", "ViewHtml"],
    ["InsertTable"],
    ["AddRowBefore", "AddRowAfter", "AddColumnBefore", "AddColumnAfter"],
    ["DeleteRow", "DeleteColumn", "DeleteTable"],
    ["MergeCells", "SplitCell"],
];
const closeModal = () => {
  emit("close");
};

const toggleSelectAll = (event) => {
  if (event.target.checked) {
    // Convert permissions object to array of values (permission names)
    form.permissions = Object.values(props.permissions);
  } else {
    form.permissions = [];
  }
};

const submit = () => {
  if (form.id) {
    form.put(route('superAdmin.subcriptionPlan.update', form.id), {
      onSuccess: () => closeModal(),
    });
  } else {
    form.post(route('superAdmin.subcriptionPlan.store'), {
      onSuccess: () => closeModal(),
    });
  }
};
const update = (plan) => {
    Object.keys(form).forEach(key => {
        if (plan[key] !== undefined) {
            form[key] = plan[key];
        }
    });
};

defineExpose({
    update,
    resetForm: () => form.reset(),
});
</script>
<template>
  <form @submit.prevent="submit">
    <div class="p-3">
      
      <!-- Plan For / Title -->
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Plan For</label>
          <input class="form-control" v-model="form.plan_for" />
          <InputError :message="form.errors.plan_for" />
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Title</label>
          <input class="form-control" v-model="form.title" />
          <InputError :message="form.errors.title" />
        </div>
      </div>

      <!-- Price / Currency / Frequency / Status -->
      <div class="row">
        <div class="col-md-3 mb-3">
          <label class="form-label">Price</label>
          <input type="number" class="form-control" v-model="form.price" />
          <InputError :message="form.errors.price" />
        </div>
         <div class="col-md-3 mb-3">
          <label class="form-label">Country</label>
          <select class="form-select" v-model="form.country_id">
            <option
              v-for="row in countries"
              :key="row.id"
              :value="row.id"
            >
              {{ row.name }}
            </option>
          </select>
        </div>
        <div class="col-md-3 mb-3">
          <label class="form-label">Currency</label>
          <input v-model="form.currency" type="text" class="form-control" placeholder="Enter currency" />
        </div>
        

        <div class="col-md-3 mb-3">
          <label class="form-label">Frequency</label>
          <select class="form-select" v-model="form.frequency">
             <option value="daily">Daily</option>
             <option value="monthly">Monthly</option>
            <option value="yearly">Yearly</option>
          </select>
        </div>

        <div class="col-md-3 mb-3">
          <label class="form-label">Status</label>
          <select class="form-select" v-model="form.status">
            <option
              v-for="row in statusOptions"
              :key="row.value"
              :value="row.value"
            >
              {{ row.label }}
            </option>
          </select>
        </div>
      </div>

      
      <div class="mb-3">
        <label class="form-label">Featured</label>
      <Editor v-model="form.features"
            :tools="tools"
            :content-style="{ height: '690px' }"
           />
           <InputError :message="form.errors.features" />
      </div>

      <!-- Permissions -->
       <div class="mb-3">
             <label class="form-label">Permissions</label>
            <div class="permissions-list">
              <div class="form-check mb-2">
                <input
                  class="form-check-input"
                  type="checkbox"
                  id="selectAll"
                  :checked="Object.values(permissions).length > 0 && form.permissions.length === Object.values(permissions).length"
                  @change="toggleSelectAll"
                />
                <label class="form-check-label" for="selectAll">Select All</label>
              </div>

              <div v-for="permission in permissions" :key="permission" class="form-check">
                <input
                  class="form-check-input"
                  type="checkbox"
                  :id="`perm-${permission}`"
                  :value="permission"
                  v-model="form.permissions"
                />
                <label class="form-check-label" :for="`perm-${permission}`">{{ permission }}</label>
              </div>
            </div>
          </div>
      <!-- Actions -->
      <div class="d-flex justify-content-end gap-2 mt-4">
        
        <button type="submit" class="btn btn-primary" :disabled="form.processing">
          Save
        </button>
        <button type="button" class="btn btn-danger" @click="closeModal">
                Close
            </button>
      </div>
    </div>
  </form>
</template>
<style>
.permissions-list {
  max-height: 200px;
  overflow-y: auto;
  border: 1px solid #ddd;
  padding: 10px;
  border-radius: 5px;
}

.permissions-list .form-check {
  margin-bottom: 8px;
}
</style>
