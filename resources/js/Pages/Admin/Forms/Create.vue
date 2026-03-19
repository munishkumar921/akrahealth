<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
</script>
<template>
<AuthLayout title="Create Form" description="Create a new form" heading="Create Form">
    
  <div class="container-box">
    <h5 class="mb-3 card-heading">New Form</h5>
    
    <form>
      <div class="mb-3">
        <label for="formName">Form Name</label>
        <input type="text" class="form-control" id="formName" placeholder="Enter form name">
      </div>
      
      <div class="mb-3">
        <label for="formType">Form Type</label>
        <select class="form-select" id="formType">
          <option value="">Select type</option>
          <option value="medical">Medical</option>
          <option value="consent">Consent</option>
          <option value="intake">Intake</option>
          <option value="other">Other</option>
        </select>
      </div>
      
      <div class="mb-3">
        <label for="formDescription">Description</label>
        <textarea class="form-control" id="formDescription" rows="3" placeholder="Enter form description"></textarea>
      </div>
      
      <div class="mt-4">
        <button type="submit" class="btn btn-primary">Save</button>
        <a :href="route('admin.forms.index')" class="btn btn-danger ms-2">Cancel</a>
      </div>
    </form>
  </div>

</AuthLayout>
</template>

<style scoped>
.card-heading {
    padding: 12px 10px;
    border-bottom: 1px solid transparent;
    border-top-right-radius: 3px;
    border-top-left-radius: 3px;
    color: #555555;
    background-color: #e8f1f8;
    border-color: #dddddd;
}
.container-box {
    background: white;
    border-radius: 8px;
    padding: 10px;
}
</style>

