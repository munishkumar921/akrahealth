<script setup>
import { ref } from 'vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2/dist/sweetalert2.js';

const props = defineProps({
  doctorForms: {
    type: Array,
    default: () => []
  }
});

// Helper to get doctor name
const getDoctorName = (form) => {
  if (form.doctor) {
    return form.doctor.name || '';
  }
  return '';
};

// Helper to extract form name from YAML content
const getFormName = (form) => {
  try {
    if (form.form) {
      // Simple YAML parsing to get form_name
      const match = form.form.match(/form_name:\s*(.+)/);
      if (match) {
        return match[1].trim().replace(/['"]/g, '');
      }
    }
  } catch (e) {
    // Ignore parsing errors
  }
  return 'Untitled Form';
};

// Delete form
const deleteForm = (id) => {
  Swal.fire({
    title: 'Are you sure?',
    text: 'You want to delete this form?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      useForm().delete(route('admin.forms.destroy', id));
    }
  });
};
</script>

<template>
  <AuthLayout title="Doctor Forms" description="Manage doctor forms" heading="Doctor Forms">
    
    <!-- ================= HEADER ================= -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="text-xl mb-0">Doctor Forms</h3>
      <a :href="route('admin.forms.create')" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Create Form
      </a>
    </div>
    
    <!-- ================= TABLE ================= -->
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Form Name</th>
            <th>Doctor Name</th>
            <th>Created Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!doctorForms || doctorForms.length === 0">
            <td colspan="5" class="text-center text-muted py-4">
              No forms found. Click "Create Form" to add one.
            </td>
          </tr>
          <tr v-else v-for="form in doctorForms" :key="form.id">
            <td>
              <strong>{{ getFormName(form) }}</strong>
            </td>
            <td>{{ getDoctorName(form) }}</td>
            <td>{{ form.created_at }}</td>
            <td>
              <div class="d-flex gap-2">
                <a :href="route('admin.forms.edit', form.id)" class="icon-btn btn btn-success" title="Edit">
                  <i class="bi bi-pencil"></i>
                </a>
                <button @click="deleteForm(form.id)" class="icon-btn btn btn-danger" title="Delete">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </AuthLayout>
</template>

<style scoped>
.icon-btn {
    padding: 9px 8px 6px 8px;
    border: none;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    cursor: pointer;
    transition: transform .07s ease-in-out, opacity .15s ease-in-out;
}

.icon-btn:active {
    transform: scale(0.97);
}

.icon-btn i {
    font-size: 14px;
    line-height: 1;
}
</style>
