<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const dashboardData = page.props.dashboardData || {}
</script>

<template>
    <AuthLayout title="Virtual Assistant Dashboard" description="Virtual Assistant Dashboard">
        <div class="row">
            <div class="col-md-6 col-lg-3">
                <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Messages</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter">{{ dashboardData.messages || 0 }}</span></h2>
                            </div>
                            <div class="iq-iconbox alert-info">
                                <i class="fa fa-envelope"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Encounters</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter">{{ dashboardData.encounters?.total || 0 }}</span></h2>
                            </div>
                            <div class="iq-iconbox iq-bg-warning">
                                <i class="fa fa-file-text"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Patients</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter">{{ dashboardData.patients?.total || 0 }}</span></h2>
                            </div>
                            <div class="iq-iconbox iq-bg-success">
                                <i class="fa fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Appointments</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter">{{ dashboardData.calendars?.total || 0 }}</span></h2>
                            </div>
                            <div class="iq-iconbox iq-spring-green ">
                                <i class="fa fa-calendar"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12 col-lg-12">
                <div class="iq-card">
                    <div class="iq-card-header d-flex justify-content-between bg-white rounded">
                        <div class="iq-header-title">
                            <h4 class="card-title">Virtual Assistant Dashboard</h4>
                        </div>
                    </div>
                    <div class="iq-card-body">
                        <div class="alert alert-info" role="alert">
                            <div class="iq-alert-text">Welcome to the Virtual Assistant Dashboard. This is where you can manage virtual assistant operations.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<style scoped>
.h-330 { height: 330px; }
.card { background: #fff; }

/* Notifications styling */
.notification-card {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  border-radius: 6px !important;
  overflow: hidden;
}

.notification-header {
  background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
  color: #fff;
  padding: 1.5rem;
  border: none;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.notification-header .card-title {
  color: #fff;
  margin: 0;
  font-weight: 600;
  font-size: 1.1rem;
}

.vertical-timeline {
  position: relative;
  padding: 1rem 0;
}

.vertical-timeline::before {
  content: '';
  position: absolute;
  left: 25px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: linear-gradient(to bottom, #0ea5e9, transparent);
}

.vertical-timeline-item {
  position: relative;
  padding-left: 70px;
  padding-bottom: 1.5rem;
}

.vertical-timeline-item:last-child {
  padding-bottom: 0;
}

.notification-badge {
  position: absolute;
  left: 10px;
  top: 5px;
  width: 36px;
  height: 36px;
  background: #f0f9ff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 3px solid #0ea5e9;
  font-size: 16px;
}

.notification-content {
  background: #f8fafc;
  padding: 1rem;
  border-radius: 8px;
  border-left: 4px solid #0ea5e9;
  transition: all 0.3s ease;
}

.notification-content:hover {
  background: #f1f5f9;
  border-left-color: #06b6d4;
  box-shadow: 0 2px 8px rgba(14, 165, 233, 0.1);
}

.notification-title {
  font-size: 0.95rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

.notification-title .badge {
  font-size: 0.7rem;
  padding: 0.35rem 0.6rem;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.notification-user {
  font-size: 0.9rem;
  color: #64748b;
  margin: 0.5rem 0;
}

.notification-user span.fw-500 {
  color: #1e293b;
  font-weight: 500;
}

.notification-time {
  font-size: 0.85rem;
  display: inline-flex;
  align-items: center;
}
</style>
