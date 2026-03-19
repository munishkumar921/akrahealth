<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2/dist/sweetalert2.js';
import 'sweetalert2/src/sweetalert2.scss';
const form = useForm({
  name: '',
  email: '',
  phone: '',
  project: '',
 });

const successMessage = ref('')
const errors = ref([])
const isSubmitting = ref(false)

const submitForm = async () => {
  errors.value = []
  isSubmitting.value = true
  try {
  const res = await axios.post('/contact-us/store', form);
  successMessage.value = res.data.success;

  // ✅ Show success message
  Swal.fire({
    title: 'Success!',
    text: 'Your message has been sent successfully',
    icon: 'success',
    confirmButtonColor: '#3085d6',
    timer: 3000,
  });

  // Reset form
  form.name = '';
  form.email = '';
  form.phone = '';
  form.subject = '';
  form.project = '';
} catch (err) {
  const response = err.response?.data;

  if (response?.errors) {
    // Laravel validation errors (object of arrays)
    errors.value = Object.values(response.errors).flat();

    Swal.fire({
      title: 'Error!',
      text: errors.value.join(', '),
      icon: 'error',
      confirmButtonColor: '#d33',
    });
  } else if (response?.error) {
    // Generic single error message (your case)
    Swal.fire({
      title: 'Error!',
      text: response.error,
      icon: 'error',
      confirmButtonColor: '#d33',
      timer:3000,
    });
  } else {
    // Fallback for unexpected errors
    Swal.fire({
      title: 'Error!',
      text: 'Something went wrong. Please try again later.',
      icon: 'error',
      confirmButtonColor: '#d33',
      timer:3000,
    });
  }
} finally {
  isSubmitting.value = false;
}
}
</script>

<template>
  <div>
    <form @submit.prevent="submitForm" class="contact-form p-4 bg-white rounded shadow-sm">
      <div class="mb-3 form-group">
        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
        <input 
          type="text"
          id="name"
          v-model="form.name"
          class="form-control"
          placeholder="Your name"
          required
        >
      </div>

      <div class="mb-3 form-group">
        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
        <input
          type="email"
          id="email"
          v-model="form.email"
          class="form-control"
          placeholder="your.email@example.com"
          required
        >
      </div>

      <div class="mb-3">
        <label for="phone" class="form-label">Phone number</label>
        <input
          type="tel"
          id="phone"
          v-model="form.phone"
          class="form-control"
          placeholder="Phone number"
        >
      </div>

      <div class="mb-3">
        <label for="project" class="form-label">Brief about your project</label>
        <textarea
          id="project"
          v-model="form.project"
          class="form-control"
          rows="4"
          placeholder="Brief about your project"
        ></textarea>
      </div>

      <div class="text-center">
        <button type="submit" name="submit" :disabled="isSubmitting" class="btn btn-primary  p-2">{{ isSubmitting ? 'Sending...' : 'Send Message' }}</button>
      </div>
       <p v-if="errors.length" class="text-red-600" v-for="(err, i) in errors" :key="i">{{ err }}</p>
    </form>
  </div>
</template>

<style scoped>
.text-danger {
  color: #dc3545;
}
</style>