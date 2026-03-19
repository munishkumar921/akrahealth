<script setup>
import { h, ref, onMounted } from "vue"
import Modal from "@/Components/Common/Modal.vue"
import AuthLayout from "@/Layouts/AuthLayout.vue"
import AddBranch from "@/Pages/Modals/AddBranch.vue"

const props = defineProps({
  hospital: Object,
  branches: Array,
})

const isOpenModal = ref(false)
const activeBranchId = ref(null) // ⭐ accordion state - set first branch as default
const childComponentRef = ref(null)

onMounted(() => {
  // Set first branch as open by default
  if (props.branches && props.branches.length > 0) {
    activeBranchId.value = props.branches[0].id
  }
})
const openAddModal = () => {
  isOpenModal.value = true
}

const closeModal = () => {
  isOpenModal.value = false
}

const toggleAccordion = (id) => {
  activeBranchId.value = activeBranchId.value === id ? null : id
}


const openEditModal = (branch) => {
  isOpenModal.value = true;
  setTimeout(() => {
    if (childComponentRef.value?.update) {
      childComponentRef.value.update(branch);
    }
  }, 10);

}


</script>


<template>
  <AuthLayout title="Branches" description="Manage your practice branches" heading="">

    <div class="d-flex justify-content-between">
      <h3 class="mb-3">Branches</h3>
      <button class="btn btn-primary mb-3" @click="openAddModal">
        Add Branch
      </button>
    </div>
    <div v-if="branches && branches.length > 0" class="branch-list">
      <div v-for="branch in branches" :key="branch.id" class="iq-card mb-3">
        <!-- HEADER -->
        <div class="iq-card-header d-flex justify-content-between align-items-center bg-primary text-white pointer"
          @click="toggleAccordion(branch.id)">
          <h5 class="mb-0 text-white">{{ branch.name }}</h5>

          <div class="d-flex align-items-center gap-2">
            <button class="btn btn-light btn-sm" @click.stop="openEditModal(branch)">
              Edit
            </button>

            <i class="ri-arrow-down-s-line fs-4 transition" :class="{ 'rotate-180': activeBranchId === branch.id }"></i>
          </div>
        </div>
        <!-- BODY (Accordion Content) -->
        <transition name="accordion">
          <div v-show="activeBranchId === branch.id" class="iq-card-body "
            style="border: 1.5px solid var(--iq-light-border)">
            <div class="row g-3">
              <div class="col-md-6">
                <strong>Main Branch Name :</strong> {{ branch.name || 'Not specified' }}
              </div>

              <div class="col-md-6">
                <strong>Street Address 1 :</strong> {{ branch.street_address1 || 'Not specified' }}
              </div>

              <div class="col-md-6">
                <strong>Street Address 2 :</strong> {{ branch.street_address2 || 'Not specified' }}
              </div>

              <div class="col-md-6">
                <strong>Country :</strong> {{ branch.country || 'Not specified' }}
              </div>

              <div class="col-md-6">
                <strong>City :</strong> {{ branch.city || 'Not specified' }}
              </div>

              <div class="col-md-6">
                <strong>State :</strong> {{ branch.state || 'Not specified' }}
              </div>

              <div class="col-md-6">
                <strong>ZIP :</strong> {{ branch.zip || 'Not specified' }}
              </div>

              <div class="col-md-6">
                <strong>Phone :</strong> {{ branch.phone || 'Not specified' }}
              </div>

              <div class="col-md-6">
                <strong>Email :</strong> {{ branch.email || 'Not specified' }}
              </div>

              <div class="col-md-6">
                <strong>Timezone :</strong> {{ branch.timezone || 'Not specified' }}
              </div>

              <div class="col-md-6">
                <strong>Status :</strong>
                <span :class="'badge badge-' + (branch.is_active == 1 ? 'success' : 'danger')">
                  {{ branch.is_active == 1 ? 'Active' : 'Inactive' }}
                </span>
              </div>
            </div>
          </div>
        </transition>
      </div>
    </div>
    <div v-else class="text-center">
      <p>No branches found.</p>
      <button class="btn btn-primary" @click="openAddModal">
        Add Branch
      </button>
    </div>

    <!-- Add/Edit Modal -->
    <Modal :isOpen="isOpenModal" :title="'Branch'" @close="closeModal" size="lg">
      <AddBranch ref="childComponentRef" :hospital="props.hospital" @close="closeModal" />
    </Modal>
  </AuthLayout>
</template>

<style scoped>
.pointer {
  cursor: pointer;
}

.transition {
  transition: transform 0.3s ease;
}

.rotate-180 {
  transform: rotate(180deg);
}

.accordion-enter-active,
.accordion-leave-active {
  transition: all 0.25s ease;
}

.accordion-enter-from,
.accordion-leave-to {
  opacity: 0;
  max-height: 0;
}

.badge-success {
  background-color: #28a745;
  color: #fff;
}

.badge-danger {
  background-color: #dc3545;
  color: #fff;
}
</style>
