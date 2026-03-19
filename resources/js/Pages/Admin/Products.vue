<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
</script>
<template>
<AuthLayout title="Products" description="Manage your products inventory and details" heading="Products">
    <div class="section-box">
    <div class="product-section-title">Products</div>

    <div class="input-group search-input-group">
      <input type="text" class="form-control" placeholder="Keyword...">
      <button class="btn btn-info search-btn text-white" type="button">
        <i class="bi bi-search"></i>
      </button>
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
.section-box {

      background: #fff;
      border: 1px solid #ccc;
      border-radius: 6px;
      padding: 15px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .product-section-title {
      font-size: 14px;
      font-weight: 600;
      padding: 10px 15px;
      margin-bottom: 10px;
      border-bottom: 1px solid transparent;
      border-top-right-radius: 3px;
      border-top-left-radius: 3px;
      color: #555555;
      background-color: #e8f1f8;
      border-color: #dddddd;
    }

    .search-input-group {
      margin-bottom: 15px;
    }

    table thead {
      background-color: #f9f9f9;
    }

    table th, table td {
      vertical-align: middle;
    }

    .bi-pencil-square {
      font-size: 1rem;
      color: #007bff;
      cursor: pointer;
    }

    .bi-pencil-square:hover {
      color: #0056b3;
    }

    .form-control {
      border-radius: 6px;
    }

    .search-btn {
      border-radius: 6px;
    }
</style>
