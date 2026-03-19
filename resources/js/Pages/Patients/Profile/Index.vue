<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import BaseFileInput from '@/Components/Common/Input/BaseFileInput.vue';
import BaseDatePicker from '@/Components/Common/Input/BaseDatePicker.vue';
import PatientTimeline from '@/Components/PatientTimeline.vue';
import { usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from "vue";
import { useForm } from '@inertiajs/vue3';

const showEditModal = ref(false);

const props = defineProps({
    patient: Object,
    messages: Array,
    documents: Array,
    orders: Array,
    familyHistory: Array,
    financialData: Array,
    encounters: Array,
    history: Array,
    medicalRecords: Array,
});

const form = useForm({
    id: props.patient.id || '',
    first_name: props.patient.first_name || '',
    last_name: props.patient.last_name || '',
    email: props.patient?.email || '',
    mobile: props.patient?.mobile || '',
    address_1: props.patient?.address_1 || '',
    address_2: props.patient?.address_2 || '',
    city: props.patient?.city || '',
    state: props.patient?.state || '',
    zip: props.patient?.zip || '',
    country: props.patient?.country || '',
    sex: props.patient?.sex || '',
    dob: props.patient?.dob || '',
    marital_status: props.patient?.marital_status || '',
    profile_photo: props.patient?.photo || '',
});

const fileError = ref('');

const previewImage = ref(null);

const expandedMessages = ref({});

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

const openEditModal = () => {
    form.reset();
    form.first_name = props.patient.first_name || '';
    form.last_name = props.patient.last_name || '';
    form.email = props.patient.email || '';
    form.mobile = props.patient.mobile || '';
    form.sex = props.patient.sex || '';
    form.dob = props.patient.dob || '';
    form.address_1 = props.patient.address_1 || '';
    form.address_2 = props.patient.address_2 || '';
    form.city = props.patient.city || '';
    form.state = props.patient.state || '';
    form.zip = props.patient.zip || '';
    form.country = props.patient.country || '';
    form.marital_status = props.patient.marital_status || '';
    form.profile_photo = props.patient.photo || '';
    previewImage.value = props.patient.photo ? `/storage/${props.patient.photo}` : null;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    form.reset();
    previewImage.value = null;
    fileError.value = '';
};

const submitProfileUpdate = () => {
    form.post(route('patient.profile.update'), {
        onSuccess: () => {
            closeEditModal();
        },
        onError: () => {
            // Handle errors if needed
        }
    });
};
const profileImage = computed(() => {
    if (props.patient?.photo) return `/storage/${props.patient.photo}`;
    return '/images/user_default_avtar.svg';
});

const getDoctorProfileImage = (doctor) => {
    if (doctor?.user?.profile_photo_url) return doctor.user.profile_photo_url;
    if (doctor?.user?.profile_photo_path) return `/storage/${doctor.user.profile_photo_path}`;
    if (doctor?.user?.sex === 'Male') return '/images/doctor_m_avtar.svg';
    if (doctor?.user?.sex === 'Female') return '/images/doctor_f_avtar.svg';
    return '/images/user_default_avtar.svg';
};

const onChangeFileUpload = (event) => {
    form.profile_photo = event.target.files[0];
    previewImage.value = URL.createObjectURL(event.target.files[0]);
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount || 0);
};

const toggleMessageExpand = (id) => {
    expandedMessages.value[id] = !expandedMessages.value[id];
};

watch(() => form.profile_photo, (newVal) => {
    if (newVal instanceof File) {
        previewImage.value = URL.createObjectURL(newVal);
    }
});

const getOrderType = (order) => {
    if (order.labs) return 'Laboratory'
    if (order.radiology) return 'Imaging'
    if (order.cp) return 'Cardiopulmonary'
    if (order.referrals) return 'Referral'
    return 'Order'
};

const getTruncatedOrderText = (order) => {
    const text =
        order.labs ||
        order.radiology ||
        order.cp ||
        order.referrals ||
        ''

    if (typeof text !== 'string') return ''

    return text.length > 50 ? text.substring(0, 50) + '...' : text
};

const getDoctorName = (order) => {
    return (
        order?.doctor?.name ||
        order?.doctor?.user?.name ||
        ''
    )
};

const getFormattedDate = (date) => {
    return date
        ? new Date(date).toLocaleDateString()
        : 'Recent'
};

const hasOrders = computed(() => {
    return Array.isArray(props.orders) && props.orders.length > 0
});

const latestOrders = computed(() => {
    if (!Array.isArray(props.orders)) return [];
    return [...props.orders].sort((a, b) => {
        const dateA = new Date(a.created_at || 0).getTime();
        const dateB = new Date(b.created_at || 0).getTime();
        return dateB - dateA;
    }).slice(0, 5);
});

const viewOrder = (order) => {
    window.location.href = route('patient.order.detail', order.id);
};

const latestDocuments = computed(() => {
    if (!Array.isArray(props.documents)) return [];
    return [...props.documents].sort((a, b) => {
        const dateA = new Date(a.created_at || 0).getTime();
        const dateB = new Date(b.created_at || 0).getTime();
        return dateB - dateA;
    }).slice(0, 5);
});

const viewDocument = (doc) => {
    if (doc.file_path || doc.url) {
        window.open(doc.file_path || doc.url, '_blank');
    }
};

const latestFamilyHistory = computed(() => {
    if (!Array.isArray(props.familyHistory)) return [];
    return [...props.familyHistory].sort((a, b) => {
        const dateA = new Date(a.created_at || 0).getTime();
        const dateB = new Date(b.created_at || 0).getTime();
        return dateB - dateA;
    }).slice(0, 5);
});

const viewFamilyHistory = (entry) => {
    if (entry && entry.parent_id) {
        window.location.href = route('patient.family-history.detail', entry.parent_id);
    }
};

const resolveFamilyHistoryTitle = (history) => {
    if (!history) return 'Family History';
    // If oh_fh is a string
    if (typeof history.oh_fh === 'string' && history.oh_fh.trim() !== '') return history.oh_fh;
    // If oh_fh is an object with name
    if (history.oh_fh && typeof history.oh_fh.name === 'string' && history.oh_fh.name.trim() !== '') return history.oh_fh.name;
    // Common fallback fields
    if (history.name && typeof history.name === 'string' && history.name.trim() !== '') return history.name;
    if (history.family_history && typeof history.family_history === 'string' && history.family_history.trim() !== '') return history.family_history;
    if (history.condition && typeof history.condition === 'string' && history.condition.trim() !== '') return history.condition;
    if (history.disease && typeof history.disease === 'string' && history.disease.trim() !== '') return history.disease;
    return 'Family History';
};

const expandedFamilyHistory = ref({});
const toggleFamilyHistory = (id) => {
    expandedFamilyHistory.value[id] = !expandedFamilyHistory.value[id];
};

const familyHistoryNormalized = computed(() => {
    let src = props.familyHistory;
    if (!src) return [];
    if (typeof src === 'string') {
        try {
            src = JSON.parse(src);
        } catch (e) {
            return [];
        }
    }
    if (!Array.isArray(src)) return [];
    
    return src.map((h, idx) => {
        // Normalize dob: may be timestamp in seconds or date string
        let dob = null;
        if (h.dob) {
            // If numeric and looks like seconds, convert
            if (typeof h.dob === 'number') {
                const ts = h.dob;
                dob = ts > 1e12 ? new Date(ts) : new Date(ts * 1000);
            } else if (typeof h.dob === 'string') {
                // Try to parse as date string
                const date = new Date(h.dob);
                if (!isNaN(date.getTime())) {
                    dob = date;
                }
            }
        }
        
        // Format medical_history for display
        let medicalHistoryText = '';
        if (h.medical_history) {
            if (Array.isArray(h.medical_history)) {
                medicalHistoryText = h.medical_history.join(', ');
            } else if (typeof h.medical_history === 'string') {
                medicalHistoryText = h.medical_history;
            }
        }
        
        return {
            id: h.id ?? h._id ?? `fh-${idx}`,
            parent_id: h.parent_id || h.id,
            title: h.name || 'Family History',
            relationship: h.relationship || h.relation || h.relationship_type || '',
            living_status: h.living_status || h.status || '',
            gender: h.gender || '',
            dob: dob,
            marital_status: h.marital_status || '',
            mother: h.mother || '',
            father: h.father || '',
            medical_history: medicalHistoryText,
            created_at: h.created_at,
            raw: h,
        };
    });
});

const fullAddress = computed(() => {
    const p = props.patient || {};
    const parts = [];
    if (p.address_1) parts.push(p.address_1);
    if (p.address_2) parts.push(p.address_2);
    if (p.city) parts.push(p.city);
    if (p.state) parts.push(p.state);
    if (p.zip) parts.push(p.zip);
    if (p.country) parts.push(p.country);
    return parts.length ? parts.join(', ') : 'Not specified';
});

const googleMapsUrl = computed(() => {
    const addr = fullAddress.value;
    if (!addr || addr === 'Not specified') return null;

    const base = 'https://www.google.com/maps?q=';
    return `${base}${encodeURIComponent(addr)}&output=embed`;
});


</script>
<template>
<AuthLayout title="Patient Profile" description="View and manage your profile" heading="Patient Profile">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12">
                    <div class="iq-card">
                        <div class="iq-card-body profile-page p-0">
                            <div class="profile-header">
                                <div class="cover-container overlay">
 
                                     
                                </div>
                                <ul class="header-nav d-flex flex-wrap justify-content-end p-0 m-0">
                                    <li>
                                        <div class="profile-edit cursor-pointer" @click="openEditModal()"><i
                                                class="ri-pencil-line"></i>
                                        </div>
                                    </li>
                                </ul>
                                <div class="profile-info p-4">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-5">
                                            <div class="user-detail ps-5">
                                                <div class="d-flex flex-wrap align-items-center">
                                                    <div class="profile-img pe-4">
                                                        <img :src="profileImage" alt="profile-img"
                                                            class="img-fluid avatar-130" />
                                                    </div>
                                                    <div class="profile-detail d-flex align-items-center patient-detail-line">
                                                        <h4 class="mb-0">{{ patient?.name }}, {{ calculateAge(patient?.dob) }} Years
                                                            Old, {{ patient?.sex }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-7">
                                            <ul
                                                class="nav nav-pills d-flex align-items-end float-end profile-feed-items p-0 m-0">
                                                <li>
                                                    <a class="nav-link active" data-toggle="pill"
                                                        href="#profile-encounter">Encounter</a>
                                                </li>
                                                <li>
                                                    <a class="nav-link" data-toggle="pill"
                                                        href="#profile-history">History</a>
                                                </li>
                                                <li>
                                                    <a class="nav-link" data-toggle="pill"
                                                        href="#profile-medical-records">Medical records</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="row">
                        <div class="col-lg-3 profile-left">
                            <div class="iq-card">
                                <div class="iq-card-header d-flex justify-content-between">
                                    <div class="iq-header-title">
                                        <h4 class="card-title">Messages</h4>
                                    </div>
                                    <div class="iq-card-header-toolbar d-flex align-items-center">
                                        <a :href="route('patient.messages')" class="text-primary">View All</a>
                                    </div>
                                </div>
                                <div class="iq-card-body p-0">
                                    <ul class="list-inline m-0 p-3" v-if="messages && messages.length > 0">
                                        <li class="d-flex align-items-start mb-3" v-for="message in messages"
                                            :key="message.id">
                                            <div class="news-icon me-2 mt-1"><i class="ri-chat-4-fill"></i></div>
                                            <div class="news-detail  w-100">
                                                <template v-if="message.message && message.message.length > 50">
                                                    <span class="mb-1 text-muted">
                                                        <span v-if="expandedMessages[message.id]">{{ message.message
                                                            }}</span>
                                                        <span v-else>{{ message.message.substring(0, 50) }}...</span>
                                                        <a href="javascript:void(0);" class=" ms-1"
                                                            @click="toggleMessageExpand(message.id)">
                                                            {{ expandedMessages[message.id] ? 'Show less' : 'See full text' }}
                                                        </a>
                                                    </span>
                                                </template>
                                                <template v-else>
                                                    <p class="mb-1 text-muted">{{ message.message || 'Message content not available.' }}</p>
                                                </template>
                                                <a href="#" v-if="message.link"
                                                    class="text-primary text-decoration-none small">see details</a>
                                            </div>
                                        </li>
                                    </ul>
                                    <p v-else class="text-muted p-3 text-center">No messages available.</p>
                                </div>
                            </div>
                            <div class="iq-card">
                                <div class="iq-card-header d-flex justify-content-between">
                                    <div class="iq-header-title">
                                        <h4 class="card-title">Documents</h4>
                                    </div>
                                    <div class="iq-card-header-toolbar d-flex align-items-center gap-2">
                                        <a :href="route('patient.documents')" class="text-primary small">View All</a>
                                    </div>
                                </div>
                                <div class="iq-card-body p-0">
                                    <ul class="list-unstyled m-0 p-3"
                                        v-if="latestDocuments && latestDocuments.length > 0">
                                        <li class="d-flex align-items-start mb-3 pb-3 border-bottom"
                                            v-for="(doc, index) in latestDocuments" :key="doc.id" v-show="index < 5">
                                            <div class="news-icon me-2 mt-1">
                                                <i class="ri-file-text-line text-primary"></i>
                                            </div>
                                            <div class="news-detail w-100">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">{{ doc?.title || 'Document' }}</h6>
                                                        <p class="mb-1 text-muted small">{{ doc?.description || '' }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-secondary small">{{ doc.created_at ? new
                                                        Date(doc.created_at).toLocaleDateString() : 'Recent' }}</span>

                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                    <p v-else class="text-muted p-3 text-center">No documents available.</p>
                                </div>
                            </div>
                            <div class="iq-card">
                                <div class="iq-card-header d-flex justify-content-between">
                                    <div class="iq-header-title">
                                        <h4 class="card-title">Financial data</h4>
                                    </div>
                                </div>
                                <div class="iq-card-body">
                                    <ul class="pages-lists m-0 p-0" v-if="financialData && financialData.length > 0">
                                        <li class="d-flex mb-4 align-items-center justify-content-between"
                                            v-for="billing in financialData" :key="billing.id">
                                            <div class="media-support-info">
                                                <h6>{{ billing.description || 'Billing #' + billing.id }}</h6>
                                                <p class="mb-0">{{ formatCurrency(billing.amount) }} - {{
                                                    billing.status|| 'Pending' }}</p>
                                                <div class="add-suggestion">
                                                    <span class="text-secondary">{{ billing.created_at ? new
                                                        Date(billing.created_at).toLocaleDateString() : 'Recent'
                                                    }}</span>
                                                </div>
                                            </div>

                                        </li>
                                    </ul>
                                    <p v-else class="text-muted p-3">No financial data available.</p>
                                </div>
                            </div>
                            <div class="iq-card">
                                <div class="iq-card-header d-flex justify-content-between">
                                    <div class="iq-header-title">
                                        <h4 class="card-title">Family history</h4>
                                    </div>
                                    <div class="iq-card-header-toolbar d-flex align-items-center gap-2">
                                        <a href="#" class="text-primary small">View All</a>
                                    </div>
                                </div>
                                <div class="iq-card-body p-0">
                                    <ul class="list-unstyled m-0 p-3"
                                        v-if="familyHistoryNormalized && familyHistoryNormalized.length > 0">
                                        <li class="d-flex align-items-start mb-3 pb-3 border-bottom"
                                            v-for="entry in familyHistoryNormalized" :key="entry.id">
                                            <div class="news-icon me-2 mt-1">
                                                <i class="ri-parent-line text-primary"></i>
                                            </div>
                                            <div class="news-detail w-100">
                                                <p class="mb-1 fw-semibold">{{ entry.title }}</p>

                                                <p class="mb-1 small text-muted">
                                                    <span v-if="entry.relationship">{{ entry.relationship }}</span>
                                                    <span v-if="entry.relationship && entry.living_status"> • </span>
                                                    <span v-if="entry.living_status">{{ entry.living_status }}</span>
                                                    <span v-if="entry.gender"> • </span>
                                                    <span v-if="entry.gender">{{ entry.gender }}</span>
                                                </p>

                                                <p class="mb-2 text-muted small">
                                                    <span v-if="entry.medical_history && entry.medical_history.length > 0">
                                                        <span v-if="expandedFamilyHistory[entry.id]">{{ entry.medical_history }}</span>
                                                        <span v-else>{{ entry.medical_history.length > 120 ? entry.medical_history.substring(0, 120) + '...' : entry.medical_history }}</span>
                                                        <a href="javascript:void(0)" class="ms-2" @click="toggleFamilyHistory(entry.id)" v-if="entry.medical_history.length > 120">{{ expandedFamilyHistory[entry.id] ? 'Show less' : 'Read more' }}</a>
                                                    </span>
                                                    <span v-else class="text-secondary">No additional details</span>
                                                </p>

                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-secondary small">{{ entry.dob ? entry.dob.toLocaleDateString() : (entry.created_at ? new Date(entry.created_at).toLocaleDateString() : 'Recent') }}</span>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>

                                    <p v-else class="text-muted p-3 text-center">
                                        No family history available.
                                    </p>
                                </div>

                            </div>
                        </div>
                        <div class="col-lg-6 profile-center">
                            <div class="tab-content">
                                <div class="tab-pane fade active show" id="profile-encounter" role="tabpanel">
                                    <div class="iq-card">
                                        <div class="iq-card-header d-flex justify-content-between">
                                            <div class="iq-header-title">
                                                <h4 class="card-title">Encounters</h4>
                                            </div>
                                        </div>
                                        <div class="iq-card-body">
                                            <ul class="list-unstyled m-0 p-0"
                                                v-if="encounters && encounters.length > 0">
                                                <li class="mb-4 border-bottom pb-3" v-for="encounter in encounters"
                                                    :key="encounter.id">
                                                    <div class="d-flex align-items-start cursor-pointer"
                                                        @click="$inertia.visit(`/patient/encounters/${encounter.id}`)">
                                                        <div class="media-support-user-img me-3">
                                                            <img class="rounded-circle img-fluid avatar-50"
                                                                :src="getDoctorProfileImage(encounter.doctor)"
                                                                alt="Doctor">
                                                        </div>
                                                        <div class="media-support-info flex-grow-1">
                                                            <h6 class="mb-1">{{
                                                                encounter.doctor?.name || encounter.doctor?.user?.name || 'Doctor'
                                                                }}</h6>
                                                            <p class="mb-1 text-primary">{{ encounter.encounter_type ||
                                                                'Medical Encounter' }}</p>
                                                            <p class="mb-1 text-muted small">{{ encounter.chief_complaint || 'Encounter details not available.' }}</p>
                                                            <small class="text-secondary">{{ encounter.created_at ? new
                                                                Date(encounter.created_at).toLocaleDateString() :
                                                                'Recent' }}</small>
                                                        </div>
                                                        <div class="ms-2">
                                                            <i class="ri-arrow-right-s-line text-primary"></i>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                            <p v-else class="text-muted p-3">No encounters available.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="profile-history" role="tabpanel">
                                    <div class="iq-card">
                                        <div class="iq-card-header d-flex justify-content-between">
                                            <div class="iq-header-title">
                                                <h4 class="card-title">History</h4>
                                            </div>
                                            <div class="iq-card-header-toolbar d-flex align-items-center">
                                                <a :href="route('patient.history')" class="text-primary">
                                                    View All
                                                </a>
                                            </div>
                                        </div>
                                        <div class=" overflow-auto" style="height: 790px;">
                                            <PatientTimeline v-if="history && history.length > 0" :patient="patient"
                                                :timeline="history" />
                                            <div v-else class="p-4 text-center text-muted">
                                                <i class="ri-history-line" style="font-size: 3rem;"></i>
                                                <p class="mt-2">No history available</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="profile-medical-records" role="tabpanel">
                                    <div class="iq-card">
                                        <div class="iq-card-header d-flex justify-content-between">
                                            <div class="iq-header-title">
                                                <h4 class="card-title">Medical records</h4>
                                            </div>
                                        </div>
                                        <div class="iq-card-body">
                                            <ul class="suggestions-lists m-0 p-0"
                                                v-if="medicalRecords && medicalRecords.length > 0">
                                                <li class="d-flex mb-4 align-items-center"
                                                    v-for="record in medicalRecords" :key="record.id">
                                                    <div class="user-img img-fluid">
                                                        <img src="/images/medical-record-icon.png" alt="record-img"
                                                            class="rounded-circle avatar-40">
                                                    </div>
                                                    <div class="media-support-info ms-3">
                                                        <h6>{{ record.issue || 'Medical Issue #' + record.id }}</h6>
                                                        <p class="mb-0">{{ record.type || 'Issue' }} - {{ record.status
                                                            || 'Active' }}</p>
                                                    </div>
                                                    <div class="iq-card-header-toolbar d-flex align-items-center">
                                                        <span class="text-secondary">{{ record.created_at ? new
                                                            Date(record.created_at).toLocaleDateString() : 'Recent'
                                                        }}</span>
                                                    </div>
                                                </li>
                                            </ul>
                                            <p v-else class="text-muted p-3">No medical records available.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 profile-right">
                            <div class="iq-card">
                                <div class="iq-card-header d-flex justify-content-between">
                                    <div class="iq-header-title">
                                        <h4 class="card-title">About</h4>
                                    </div>
                                </div>
                                <div class="iq-card-body">
                                    <div class="about-info">
                                         <div class="row">
                                            <div class="col-4 fw-semibold text-end">Email</div>
                                            <div class="col-8 d-flex align-items-center">
                                                <a v-if="patient.email" class="text-break" :href="'mailto:' + patient.email">{{ patient.email }}</a>
                                                <span v-else class="text-secondary">Not provided</span>
                                            </div>

                                            <div class="col-4 fw-semibold text-end">Phone</div>
                                            <div class="col-8">
                                                <a v-if="patient.mobile" :href="'tel:' + patient.mobile">{{ patient.mobile }}</a>
                                                <span v-else class="text-secondary">Not provided</span>
                                            </div>
                                            <div class="col-4 fw-semibold text-end">Gender</div>
                                            <div class="col-8">{{ patient.sex || 'Not specified' }}</div>

                                            <div class="col-4 fw-semibold text-end">Age</div>
                                            <div class="col-8">{{ patient.dob ? calculateAge(patient.dob) + ' years' : 'Not specified' }}</div>

                                            <div class="col-4 fw-semibold text-end">Joined</div>
                                            <div class="col-8">{{ patient.created_at ? new Date(patient.created_at).toLocaleDateString() : 'Not specified' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="iq-card">
                                <div class="iq-card-header d-flex justify-content-between">
                                    <div class="iq-header-title">
                                        <h4 class="card-title">Location</h4>
                                    </div>
                                </div>
                                <div class="iq-card-body">
                                    <ul class="m-0 p-0">
                                        <li class="d-flex mb-2">
                                            <div class="news-icon me-2">
                                                <i class="fas fa-map-marker-alt text-info"></i>
                                            </div>
                                            <span v-if="fullAddress !== 'Not specified'">{{ fullAddress }}</span>
                                            <span v-else class="text-secondary">Not specified</span>
                                        </li>
                                        <li v-if="googleMapsUrl">
                                            <iframe
                                                class="w-100"
                                                :src="googleMapsUrl"
                                                height="200"
                                                style="border:0;"
                                                allowfullscreen=""
                                                loading="lazy"
                                                referrerpolicy="no-referrer-when-downgrade"
                                            ></iframe>
                                        </li>
                                        <li v-else class="text-secondary small">
                                            Location map not available.
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="iq-card">
                                <div class="iq-card-header d-flex justify-content-between">
                                    <div class="iq-header-title">
                                        <h4 class="card-title">Orders</h4>
                                    </div>
                                    <div class="iq-card-header-toolbar d-flex align-items-center gap-2">
                                        <a :href="route('patient.orders')" class="text-primary small">View All</a>
                                    </div>
                                </div>
                                <div class="iq-card-body p-0">
                                    <ul class="list-unstyled m-0 p-3" v-if="latestOrders && latestOrders.length > 0">
                                        <li class="d-flex align-items-start mb-3 pb-3 border-bottom"
                                            v-for="order in latestOrders" :key="order.id">
                                            <div class="news-icon me-2 mt-1">
                                                <i class="ri-file-list-3-line" :class="{
                                                    'text-success': order.labs,
                                                    'text-warning': order.radiology,
                                                    'text-primary': order.cp,
                                                    'text-secondary': order.referrals
                                                }"></i>
                                            </div>
                                            <div class="news-detail w-100">
                                                <div
                                                    class="d-flex flex-wrap justify-content-between align-items-start mb-1">
                                                    <h6 class="mb-1">{{ getOrderType(order) }}</h6>
                                                    <span class="badge"
                                                        :class="order.is_completed ? 'bg-success' : 'bg-warning'">
                                                        {{ order.is_completed ? 'Completed' : 'Pending' }}
                                                    </span>
                                                </div>

                                                <p class="mb-2 text-muted small">{{ getTruncatedOrderText(order) }}</p>

                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="small">
                                                        <i class="ri-user-fill me-1 text-primary"></i>
                                                        <span>Dr. {{ getDoctorName(order) }}</span>
                                                    </div>
                                                </div>

                                                <span class="text-secondary small d-block mt-1">{{
                                                    getFormattedDate(order.created_at) }}</span>
                                            </div>
                                        </li>
                                    </ul>
                                    <p v-else class="text-muted p-3 text-center">No orders available.</p>
                                </div>
                            </div>
                          
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Profile Modal -->
        <div class="modal fade" :class="{ 'show d-block': showEditModal }" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg overflow-hidden" role="document">
                <div class="modal-content overflow-hidden">
                    <div class="modal-header">
                        <h5 class="modal-title text-white">Edit Profile</h5>
                        <button type="button" class="close" @click="closeEditModal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="submitProfileUpdate">
                        <div class="modal-body overflow-auto" style="max-height: 70vh; overflow-x: hidden;">
                            <div class="row">
                                <!-- Profile Photo -->
                                <div class="col-md-12">
                                    <div v-if="previewImage" class="mb-3 text-center">
                                        <img :src="previewImage" alt="Profile Preview"
                                            class="img-fluid rounded-circle avatar-100"
                                            style="width: 100px; height: 100px; object-fit: cover;">
                                    </div>
                                    <BaseFileInput id="profile_photo" label="Profile Photo" accept="image/*"
                                        v-model="form.profile_photo" :error="form.errors.profile_photo" />
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">First Name</label>
                                        <input type="text" class="form-control" id="name" v-model="form.first_name"
                                            required>
                                        <div v-if="form.errors.first_name" class="text-danger small">{{
                                            form.errors.first_name }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="last_name">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" v-model="form.last_name"
                                            required>
                                        <div v-if="form.errors.last_name" class="text-danger small">{{
                                            form.errors.last_name }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" id="email" v-model="form.email"
                                            required>
                                        <div v-if="form.errors.email" class="text-danger small">{{ form.errors.email }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                         <BaseDatePicker v-model="form.dob" type="date" label="Date of Birth" placeholder="Select date" />
                                        <div v-if="form.errors.dob" class="text-danger small">{{ form.errors.dob }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="mobile">Mobile</label>
                                        <input type="tel" class="form-control" id="mobile" v-model="form.mobile">
                                        <div v-if="form.errors.mobile" class="text-danger small">{{ form.errors.mobile
                                        }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="sex">Gender</label>
                                        <select class="form-control" id="sex" v-model="form.sex">
                                            <option value="">Select Gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Other">Other</option>
                                        </select>
                                        <div v-if="form.errors.sex" class="text-danger small">{{ form.errors.sex }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="marital_status">Marital Status</label>
                                        <select class="form-control" id="marital_status" v-model="form.marital_status">
                                            <option value="">Select Marital Status</option>
                                            <option value="Single">Single</option>
                                            <option value="Married">Married</option>
                                            <option value="Divorced">Divorced</option>
                                            <option value="Widowed">Widowed</option>
                                        </select>
                                        <div v-if="form.errors.marital_status" class="text-danger small">{{
                                            form.errors.marital_status }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="address_1">Address 1</label>
                                        <input type="text" class="form-control" id="address_1" v-model="form.address_1">
                                        <div v-if="form.errors.address_1" class="text-danger small">{{
                                            form.errors.address_1 }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="address_2">Address 2</label>
                                        <input type="text" class="form-control" id="address_2" v-model="form.address_2">
                                        <div v-if="form.errors.address_2" class="text-danger small">{{
                                            form.errors.address_2 }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="city">City</label>
                                        <input type="text" class="form-control" id="city" v-model="form.city">
                                        <div v-if="form.errors.city" class="text-danger small">{{ form.errors.city }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="country">Country</label>
                                        <input type="text" class="form-control" id="country" v-model="form.country">
                                        <div v-if="form.errors.country" class="text-danger small">{{ form.errors.country
                                        }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="state">State</label>
                                        <input type="text" class="form-control" id="state" v-model="form.state">
                                        <div v-if="form.errors.state" class="text-danger small">{{ form.errors.state }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="zip">ZIP</label>
                                        <input type="text" class="form-control" id="zip" v-model="form.zip">
                                        <div v-if="form.errors.zip" class="text-danger small">{{ form.errors.zip }}
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" @click="closeEditModal">Cancel</button>
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">Save
                                Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Backdrop -->
        <div v-if="showEditModal" class="modal-backdrop fade show" @click="closeEditModal"
            style="background-color: rgba(0, 0, 0, 0.5);"></div>
    </AuthLayout>
</template>
<style scoped>
.header-nav {
    position: absolute;
    top: 20px;
    right: 20px;
}

.news-icon {
    font-size: 20px;
    margin-right: 5px !important;
}

.header-nav li {
    list-style: none;
    margin-left: 10px;
}

.header-nav div {
    background: rgba(255, 255, 255, 0.9);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #333;
    text-decoration: none;
    transition: all 0.3s ease;
}

.header-nav div:hover {
    background: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
.iq-card .iq-card-header{
    padding: 1rem 0.5rem!important;
}
.cover-container {
    background: #09acff2b;
    height: 250px;
}
.overlay:after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background:#09acff2b;
}
.profile-img {
    margin-top: -60px;
    position: relative;
    z-index: 9;
}
.profile-img img {
    border: 5px solid #fff;
    background: #fff;
}

.patient-detail-line {
    white-space: nowrap;
}
</style>