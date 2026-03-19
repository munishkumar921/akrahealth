<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
</script>
<template>
<AuthLayout title="Schedule Setup" description="Configure your practice schedule and operating hours" heading="Schedule Setup">
    
  <div class="container-box">
    <h5 class="mb-2 card-heading">Schedule Setup</h5>
    <form>
      <div class="mb-3">
        <label for="includeWeekends">Include Weekends in the Schedule</label>
        <select class="form-select" id="includeWeekends">
          <option>Yes</option>
          <option>No</option>
        </select>
      </div>

      <div class="row">
        <div class="col-md-6">
          <label for="firstHour">First hour/time that will be displayed on the schedule</label>
          <input type="text" class="form-control" id="firstHour" value="09:00 AM">
        </div>
        <div class="col-md-6">
          <label for="lastHour">Last hour/time that will be displayed on the schedule</label>
          <input type="text" class="form-control" id="lastHour" value="10:30 PM">
        </div>
      </div>

      <div class="mt-3">
        <label for="timezone">Timezone</label>
        <input type="text" class="form-control" id="timezone" value="Asia/Kolkata">
      </div>

      <!-- Loop through days -->
      <div class="row mt-4">
        <div class="col-md-6">
          <label>Monday open at</label>
          <input type="text" class="form-control" value="09:00 AM">
        </div>
        <div class="col-md-6">
          <label>Monday close at</label>
          <input type="text" class="form-control" value="09:00 PM">
        </div>

        <div class="col-md-6">
          <label>Tuesday open at</label>
          <input type="text" class="form-control" value="09:00 AM">
        </div>
        <div class="col-md-6">
          <label>Tuesday close at</label>
          <input type="text" class="form-control" value="09:00 PM">
        </div>

        <div class="col-md-6">
          <label>Wednesday open at</label>
          <input type="text" class="form-control" value="10:00 AM">
        </div>
        <div class="col-md-6">
          <label>Wednesday close at</label>
          <input type="text" class="form-control" value="10:00 PM">
        </div>

        <div class="col-md-6">
          <label>Thursday open at</label>
          <input type="text" class="form-control" value="01:00 AM">
        </div>
        <div class="col-md-6">
          <label>Thursday close at</label>
          <input type="text" class="form-control" value="09:00 PM">
        </div>

        <div class="col-md-6">
          <label>Friday open at</label>
          <input type="text" class="form-control" value="10:00 AM">
        </div>
        <div class="col-md-6">
          <label>Friday close at</label>
          <input type="text" class="form-control" value="10:00 PM">
        </div>

        <div class="col-md-6">
          <label>Saturday open at</label>
          <input type="text" class="form-control" value="10:00 AM">
        </div>
        <div class="col-md-6">
          <label>Saturday close at</label>
          <input type="text" class="form-control" value="10:00 PM">
        </div>

        <div class="col-md-6">
          <label>Sunday open at</label>
          <input type="text" class="form-control" value="09:00 AM">
        </div>
        <div class="col-md-6">
          <label>Sunday close at</label>
          <input type="text" class="form-control" value="09:00 PM">
        </div>
      </div>

      <div class="mt-4">
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="reset" class="btn btn-danger ms-2">Cancel</button>
      </div>
    </form>
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
    label {
      font-weight: 500;
      margin-top: 15px;
    }
</style>
