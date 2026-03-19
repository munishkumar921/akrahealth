<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
</script>
<template>
<AuthLayout title="Practice Setup" description="Configure your practice settings and preferences" heading="Practice Setup">
    <h4 class="mb-2 card-heading">Practice Setup</h4>
    <!-- Practice Information -->
    <div class="section">
      <div class="section-header">
        <span>Practice Information</span>
        <button class="edit-btn">Edit</button>
      </div>
     
    </div>

    <!-- Practice Settings -->
    <div class="section mt-5">
      <div class="section-header">
        <span>Practice Settings</span>
        <button class="edit-btn">Edit</button>
      </div>  
    </div> 
    <!-- Billing Settings -->
    <div class="section mt-5">
      <div class="section-header">
        <span>Billing</span>
        <button class="edit-btn">Edit</button>
      </div>  
    </div> 
    <!--Extensions -->
    <div class="section mt-5">
      <div class="section-header">
        <span>Extensions</span>
        <button class="edit-btn">Edit</button>
      </div>  
    </div>
     <!--Practice Logo -->
     <div class="section mt-5">
      <div class="section-header">
        <span>Practice Logo</span>
        <button class="edit-btn">Edit</button>
      </div>  
    </div>
  </AuthLayout>
</template>

<style scoped>
.card-heading {
    padding: 15px 15px;
    border-bottom: 1px solid transparent;
    border-top-right-radius: 3px;
    border-top-left-radius: 3px;
    color: #555555;
    background-color: #e8f1f8;
    border-color: #dddddd;
}
.practice-setup {
  max-width: 900px;
  margin: 2em auto;
  background: #f8f9fa;
  padding: 2em;
  border-radius: 8px;
}
.section-card {
  background: white;
  border-radius: 6px;
  margin-bottom: 2em;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
.section-header {
  background: #03a9f4;
  color: white;
  padding: 0.75em 1em;
  border-radius: 6px 6px 0 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.section-body {
  padding: 1em;
}
.edit-btn {
  background: white;
  color: #03a9f4;
  border: 1px solid #03a9f4;
  border-radius: 4px;
  padding: 0.25em 1em;
  cursor: pointer;
  font-weight: bold;
}
.info-table {
  width: 100%;
  border-collapse: collapse;
}
.info-table td {
  padding: 0.5em 0.75em;
  border-bottom: 1px solid #eee;
}
</style>
