<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
</script>
<template>
<AuthLayout title="Audit Logs" description="View and manage system audit logs" heading="Audit Logs">
    <div class="log-container">
    <h5 class="mb-2 card-heading">Audit Logs</h5>

    <input type="text" class="form-control filter-input" placeholder="Filter Results...">

   
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
.log-container {
      max-width: 1000px;
      background: #fff;
      border-radius: 6px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .log-entry {
      border-bottom: 1px solid #eee;
      padding: 10px 0;
    }

    .log-entry:last-child {
      border-bottom: none;
    }

    .log-date {
      font-weight: bold;
      color: #333;
    }

    .log-user {
      color: #555;
      font-style: italic;
    }

    .log-sql {
      margin-top: 5px;
      font-family: monospace;
      color: #444;
      white-space: pre-wrap;
    }

    .filter-input {
      margin-bottom: 15px;
      border-radius: 6px;
    }
</style>
