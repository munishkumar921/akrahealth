<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
</script>
<template>
<AuthLayout title="My Reports" description="View and manage your reports" heading="My Reports">
    
  <div class="container-box">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0 card-heading">My Reports</h5>
      <button class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Generate Report
      </button>
    </div>
    
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card">
          <div class="card-body text-center">
            <h3 class="mb-0">0</h3>
            <p class="text-muted mb-0">Total Reports</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body text-center">
            <h3 class="mb-0">0</h3>
            <p class="text-muted mb-0">This Month</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body text-center">
            <h3 class="mb-0">0</h3>
            <p class="text-muted mb-0">This Week</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body text-center">
            <h3 class="mb-0">0</h3>
            <p class="text-muted mb-0">Today</p>
          </div>
        </div>
      </div>
    </div>
    
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>ID</th>
            <th>Report Name</th>
            <th>Type</th>
            <th>Generated Date</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="6" class="text-center text-muted">No reports found. Click "Generate Report" to create one.</td>
          </tr>
        </tbody>
      </table>
    </div>
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

