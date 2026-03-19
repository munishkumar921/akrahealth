<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { computed } from "vue";  

const props = defineProps({
    familyHistory: Object,
    keyword: String,    
     
});
 

// Table Columns
const columns = [
  { label: "Name", key: "name" },
  { label: "Relationship", key: "relationship" },
  { label: "Gender", key: "gender" },
  { label: "DOB", key: "dob" },
  { label: "Marital Status", key: "marital_status" },
  { label: "Medical History", key: "medical_history", type: "slot", slot: "medical_history" },
];

// Normalize keys (convert all to lowercase & consistent naming)
function normalize(item) {
  const map = {
    Name: "name",
    Relationship: "relationship",
    Status: "living_status",
    Gender: "gender",
    "Date of Birth": "dob",
    "Marital Status": "marital_status",
    Mother: "mother",
    Father: "father",
    Medical: "medical_history",
  };

  const normalized = {};
  for (const [key, value] of Object.entries(item)) {
    normalized[map[key] || key] = value;
  }
  return normalized;
}

// Flatten and normalize
const flatData = computed(() => {
  const items = props.familyHistory?.data || [];
  return items.flatMap(item =>
    (item.oh_fh || []).map(fh => ({
      id: item.id,
      ...normalize(fh),
    }))
  );
});

</script>

<template>
    <AuthLayout
        title="Immunizations"
        description="Immunizations"
        heading="Immunizations">
        <div class="d-flex align-items-center justify-content-between">
            <h3 class="d-flex align-items-center">Family History</h3>
             
         </div>
         <Table :columns="columns" :data="{ data: flatData }":search="keyword">
           <template #medical_history="{ row }">
            <div v-if="row.medical_history">
                <span v-for="(item, index) in (Array.isArray(row.medical_history) ? row.medical_history : String(row.medical_history).split(','))" 
                      :key="index" 
                      class="badge bg-primary me-1">
                    {{ item.trim() }}
                </span>
            </div>
           </template>
          </Table>

     </AuthLayout>
</template>
