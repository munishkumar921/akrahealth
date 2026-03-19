<script setup>
const props = defineProps({
  actionButtons: {
    type: Array,
    default: () => [],
  },
  extraButtons: {
    type: Array,
    default: () => [],
  },
});
</script>

<template>
  <div class="d-flex ms-2">
    <!-- Main buttons -->
    <button
      v-for="(button, index) in actionButtons"
      :key="index"
      type="button"
      class="btn btn-primary d-flex align-items-center gap-2 ms-2"
      @click="button.function()"
    >
      <i :class="button.icon"></i>
          {{ button.label }}
    </button>
 
    <!-- Dropdown for extra buttons -->
    <div class="ms-2 dropdown" v-if="extraButtons.length">
        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
             <i class="fa-solid fa-square-plus pointer mr-1 "></i>{{ extraButtons[0].title }}
        </button>

      <ul class="dropdown-menu dropdown-menu-end mt-2" aria-labelledby="dropdownMenuButton1">
         <li
          v-for="(btn, i) in extraButtons"
          :key="'extra-' + i"
        >
          <button
            class="dropdown-item pointer"
            type="button"
            @click="btn.function()"
          >
            {{ btn.label }}
          </button>
        </li>
      </ul>
    </div>
  </div>
</template>
