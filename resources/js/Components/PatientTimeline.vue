<script setup>
import { defineProps, ref } from "vue";
import { Link } from "@inertiajs/vue3";
import Dropdown from "./Dropdown.vue";

const props = defineProps({
  patient: {
    type: Object,
    required: false,
  },
  lastVisit: {
    type: String,

  },
  timeline: {
    type: Array,
  },
  readMore: {
    type: Function,
    required: false,
  },
});

const items = ref([
  { label: 'New Encounter', href: route('doctor.encounters.create'), icon: 'fa fa-plus fa-fw' },
  { label: 'New Telephone Message', href: '#', icon: 'fa fa-phone fa-fw' },
  { label: 'New Letter', href: '#', icon: 'fa fa-envelope fa-fw' },
  { label: 'New Test Results', href: '#', icon: 'fa fa-flask fa-fw' },
  { label: 'New Alert', href: route('doctor.alerts.index'), icon: 'fa fa-exclamation-triangle fa-fw' },
  { label: 'New Document', href: route('doctor.documents.index'), icon: 'fa fa-file fa-fw' },
  { divider: true },
  { label: 'New Message to Patient', href: '#', icon: 'fa fa-comment fa-fw' },
  { label: 'New Coordination of Care Transaction', href: '#', icon: 'fa fa-handshake fa-fw' },
]);

const calculateAge = (dob) => {
    if (!dob) return null;
    const birthDate = new Date(dob);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
};
</script>
 <template>
  <div class="bg-white rounded-xl shadow p-4">
    <!-- Patient Info (optional) -->
    <div v-if="patient" class="pb-4 mb-4">
      <div class="bg-primary border border-primary  rounded-lg p-3 d-flex justify-content-between center-flex">
        <h3 class="text-white m-0">
          {{ patient?.name }}, {{calculateAge(patient?.dob)}} Years Old, {{ patient?.sex }}
        </h3>
        <div class="relative" v-if="$page.props.auth.user.role === 'doctor'">
          <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown"
            aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-bars"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton" style="min-width: 200px;">
            <li v-for="(item, index) in items" :key="index">
              <a :href="item.href" class="dropdown-item">
                <i :class="item.icon + ' fa-fw mr-1'"></i> {{ item.label }}
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Last Visit (optional) -->
    <div v-if="lastVisit" class="alert alert-success border border-green-300 text-green-800 rounded-md item-center">
      <i class="fa-regular fa-calendar mr-2"></i>
      <span>Last Visit with Your Practice: <strong>{{ lastVisit }}</strong></span>
    </div>

    <!-- Timeline -->
    <div class="relative pt-6  ms-4 me-4">
      <!-- Center vertical line -->
      <div class="timeline-line"></div>

      <div v-for="(item, index) in timeline" :key="index" class="timeline-item">
        <!-- Date label -->
        <div class="timeline-date"
          :class="{ 'timeline-date-left': index % 2 === 0, 'timeline-date-right': index % 2 !== 0 }">
          {{ item.date }}
        </div>


        <!-- Center icon -->
        <div class="timeline-icon">
          <div :class="item.iconColor">
            <i :class="item.icon"></i>

          </div>
        </div>
        <!-- Horizontal connector -->
        


        <!-- Content -->
        <div class="timeline-content" :class="{ 'right': index % 2 === 0, 'left': index % 2 !== 0 }">
          <h3 class="bold">{{ item.title }}</h3>
          <p v-if="item.description" class="preserve-whitespace">{{ item.description }}</p>

          <Link :href="item.url" class="btn btn-primary" v-if="item?.url">Read more</Link>
          <div class="timeline-connector"></div>
        </div>
      </div>
    </div>
  </div>

</template>
<style scoped>
/* Timeline vertical line */
.timeline-line {
  position: absolute;
  left: 50%;
  top: 0;
  bottom: 0;
  width: 1px;
  background-color: #d1d5db;
  transform: translateX(-50%);
}

/* Timeline item container */
.timeline-item {
  position: relative;
  margin-bottom: 4.5rem;
  display: flex;
  align-items: flex-start;
  width: 100%;
}

/* Horizontal connector lines */
.timeline-connector {
  margin-top: 20px;
  height: 3px;
  background-color: #d1d5db;
  width: 100%;
  transform: translateX(1%);
}
 
 
/* Icon container */
.timeline-icon {
  position: absolute;
  left: 50%;
  top: 0;
  transform: translateX(-50%);
  z-index: 10;
}

.timeline-icon i {
  font-size: 1.25rem;
}

/* Content positioning */
.timeline-content {
  width: 45%;
  margin-top: 1rem;

  padding: 0 1rem;
  position: relative;
}

.timeline-content.left {
  padding-right: 1.5rem;
  padding-left: 0;
  margin-right: 55%;
}

.timeline-content.right {
  text-align: left;
  padding-left: 1.5rem;
  padding-right: 0;
  margin-left: 55%;
}

/* Date positioning */
.timeline-date {
  position: absolute;
  top: 25px !important;
  ;
  transform: translateY(-50%);
  width: fit-content;

  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 500;
  white-space: nowrap;

}

.timeline-date-left {
  right: 55% !important;
  margin-right: 0.75rem;
}

.timeline-date-right {
  left: 55% !important;
  margin-left: 0.75rem;
}

/* Clean button styling */
.timeline-content button {
  margin-top: 1rem;
  padding: 0.5rem 1rem;
  background-color: #0ea5e9;
  color: white;
  border: none;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.timeline-content button:hover {
  background-color: #0284c7;
}

/* Clean typography */
.timeline-content h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.timeline-content p {
  font-size: 0.875rem;
  color: #6b7280;
  line-height:1.2rem;
  margin:1rem 0 1rem 0!important;
}

.timeline-content .preserve-whitespace {
  white-space: pre-line;
 
}

/* Ensure FontAwesome icons are visible */
.timeline-icon .fa-solid,
.timeline-icon .fa-regular,
.timeline-icon .fa {
  display: inline-block;
  font-style: normal;
  font-variant: normal;
  text-rendering: auto;
  line-height: 1;
  font-family: "Font Awesome 6 Free";
  font-weight: 900;
}

/* Specific icon styling */
.timeline-icon .fa-stethoscope:before {
  content: "\f0f1";
}

.timeline-icon .fa-ban:before {
  content: "\f05e";
}

.timeline-icon .fa-syringe:before {
  content: "\f48e";
}


/* Icon circle styling to match image */
.timeline-icon>div {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 4px solid white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Green circle for encounters */
.timeline-icon .bg-green-500 {
  background-color: #10b981;
}

/* Yellow circle for medication events */
.timeline-icon .bg-yellow-500 {
  background-color: #f59e0b;
}
.timeline-icon .bg-primary{
   background-color:#0ea5e9;
}
.timeline-icon .bg-orange-500{
  background-color: #f59e0b;
}
.timeline-icon .bg-red-500 {
  background-color:#c03b44;
}
.timeline-icon .bg-indigo-500 {
  background-color: #6366f1;
}

.timeline-container {
  position: relative;
  padding: 20px 0;
  margin: 0 auto;
  max-width: 900px;
}

.timeline-container::after {
  content: '';
  position: absolute;
  width: 2px;
  background-color: #ccc;
  top: 0;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
}

@media (max-width: 768px) {
  .timeline-line {
    left: 24px;
    transform: translateX(0);
  }

  .timeline-icon {
    left: 24px;
  }

  .timeline-item {
    margin-bottom: 2.5rem;
  }

  .timeline-content.left,
  .timeline-content.right {
    width: auto;
    margin-left: 70px !important;
    margin-right: 0 !important;
    padding-left: 1rem !important;
    padding-right: 1rem !important;
    text-align: left !important;
  }

  .timeline-date-left,
  .timeline-date-right {
    left: 70px !important;
    right: auto !important;
    text-align: left !important;
    margin-left: 0 !important;
  }
 
}
</style>

