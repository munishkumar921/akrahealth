<script setup>
import AuthLayout2 from '@/Layouts/AuthLayout2.vue';
import { ref, onMounted, onBeforeUnmount, nextTick, computed } from 'vue';
import { Room, RoomEvent} from 'livekit-client';
import { useForm,router } from '@inertiajs/vue3';
import axios from 'axios';
const props = defineProps({
    appointments: Object
});
const room = ref(null)
const token = ref(null)
const identity = ref(null)
const url = ref(null)
const isConnected = ref(false);
const connectingAppointments = ref(new Map());
const localContainer = ref(null);
const remoteContainer = ref(null);
const micEnabled = ref(true);
const cameraEnabled = ref(true);
const showChat = ref(false);
const chatMessages = ref([]);
const participants = ref([]);
const showParticipants = ref(false);
const isScreenSharing = ref(false);
const isRecording = ref(false);
const egressId = ref(null);
const chatInput = ref("");
const errorMessage = ref("");
const successMessage = ref("");
const notificationsEnabled = ref(false);
const transcriptionEnabled = ref(false);
const selectedPatient = ref(null);
const showSoapNotes = ref(false);
const activeTab = ref('present');
const soapNotesActiveTab = ref('subject');
const isDragging = ref(false);
const dragOffset = ref({ x: 0, y: 0 });
const soapNotes = useForm({
        id:'',
        patient_id:'',
        doctor_id:'',
        chief_complaint:'',
        hpi: '',
        ros: '',
        pe: '',
        weight: '',
        height: '',
        bmi: '',
        temperature: '',
        temperature_method: '',
        bp_systolic: '',
        bp_diastolic: '',
        bp_position: '',
        pulse: '',
        respirations: '',
        o2_saturation: '',
        assessment_other: '',
        differential_diagnoses: '',
        assessment_discussion: '',
        plan: '',
        followup: '',
        duration: '',
        encounter_type:'',
        encounter_location:'',
        encounter_date_of_service:'',
});

const currentTime = ref(new Date());
let timeInterval = null;

onMounted(() => {
    timeInterval = setInterval(() => {
        currentTime.value = new Date();
    }, 1000); // update every second
});
 

const filteredAppointments = computed(() => {
    const apps = Array.isArray(props.appointments) ? props.appointments : [];
    const now = currentTime.value;

     return apps.filter(app => {
    // Ensure we have valid date and time
    if (!app.appointment_date || !app.appointment_time) return false;
    
    // Combine date and time into a single datetime object
    const dateStr = app.appointment_date.split('T')[0]; // Get YYYY-MM-DD part
    const timeStr = app.appointment_time.includes('T') 
        ? app.appointment_time.split('T')[1].substring(0, 8) // Get HH:MM:SS part
        : app.appointment_time;
    
    const apptDateTime = new Date(`${dateStr}T${timeStr}`);
     
    // For debugging
     
    // Normalize to date-only for date comparisons
    const apptDateOnly = new Date(apptDateTime);
    apptDateOnly.setHours(0, 0, 0, 0);
    
    const nowDateOnly = new Date(now);
    nowDateOnly.setHours(0, 0, 0, 0);
    
    const isSameDate = apptDateOnly.getTime() === nowDateOnly.getTime();
    
    if (activeTab.value === 'past') {
        // Past: Any appointment that has already completed (date/time is in the past)
        return apptDateOnly < nowDateOnly;
    }
    else if (activeTab.value === 'present') {
        // Present: TODAY's appointments that haven't happened yet
        return isSameDate
    }
    else if (activeTab.value === 'future') {
        // Future: Any appointment from TOMORROW onward (ignoring time of day)
        return apptDateOnly > nowDateOnly;
    }
    
    return false;
});
});



// initialize form fields
const form = useForm({
    patient_id: '',
    doctor_id: '',
    appointment_id: '',
    encounter_type: '',
    encounter_location: '',
    chief_complaint: '',
    encounter_role: "Primary Care Provider",
    encounter_date_of_service: '',
    encounter_condition_work: "No",
    encounter_condition_auto: "No",
 });

const roomCall = (appointment) => {
    connectingAppointments.value.set(appointment.id, true);
    selectedPatient.value = appointment;

    // Populate form with appointment data
    form.patient_id = appointment.patient_id ?? '';
    form.doctor_id = appointment.doctor_id ?? '';
    form.appointment_id = appointment.id ?? '';
    form.encounter_type = appointment.appointment_type ?? '';
    form.encounter_location = appointment?.doctor?.hospital?.default_pos_id ?? '';
    form.chief_complaint = appointment.reason ?? '';
    form.encounter_date_of_service = appointment.appointment_date ?? '';

// Make the POST request
axios.post(route('doctor.call.encounter.store'), form)
    .then(response => {
        // Store encounter data for SOAP notes
        if (response.data?.data) {
            // If response is wrapped in data property (Laravel default)
            soapNotes.id = response.data.data.id;
            soapNotes.chief_complaint = response.data.data.chief_complaint;
            soapNotes.patient_id = response.data.data.patient_id;
            soapNotes.doctor_id = response.data.data.doctor_id;
            soapNotes.encounter_id = response.data.data.id; // Consider adding this
            soapNotes.encounter_type=response.data.data.encounter_type;
            soapNotes.encounter_location=response.data.data.encounter_location;
            soapNotes.encounter_date_of_service=response.data.data.encounter_date_of_service;
            } else {
            // Direct response
            soapNotes.id = response.data?.id;
            soapNotes.chief_complaint = response.data?.chief_complaint;
            soapNotes.patient_id = response.data?.patient_id;
            soapNotes.doctor_id = response.data?.doctor_id;
            soapNotes.encounter_type=response.data?.encounter_type;
            soapNotes.encounter_location=response.data?.encounter_location;
            soapNotes.encounter_date_of_service=response.data?.encounter_date_of_service;
        }
        
        // After storing encounter, generate token
        return axios.get(route('doctor.generateToken', { id: appointment.id }));
    })
    .then(tokenResponse => {
        const { room: roomData, token: tokenData, identity: identityData, url: urlData } = tokenResponse.data;
        
        if (!roomData || !tokenData || !identityData || !urlData) {
            throw new Error('Incomplete response data from server');
        }

        room.value = roomData;
        token.value = tokenData;
        identity.value = identityData;
        url.value = urlData;
        
        // Notify patient to join the call via email and notification
        axios.post(route('doctor.call.notify'), {
            appointment_id: appointment.id
        }).catch(err => {
            console.error('Failed to notify patient:', err);
        });

        // Call connectToRoom with the actual values
        return connectToRoom(urlData, tokenData);
    })
    .catch(err => {
        console.error('Error in roomCall:', err);
        
        if (err.response) {
            // Server responded with error status
            if (err.response.status === 422) {
                // Optionally show validation errors to user
                alert('Validation errors: ' + JSON.stringify(err.response.data.errors));
            } else if (err.response.status === 500) {
                console.error('Server error:', err.response.data);
                alert('Server error occurred. Please try again.');
            }
        } else if (err.request) {
            // Request was made but no response received
            console.error('No response received:', err.request);
            alert('No response from server. Please check your connection.');
        } else {
            // Something else happened
            console.error('Error:', err.message);
            alert('Error: ' + err.message);
        }

        connectingAppointments.value.delete(appointment.id);
    });
};
const connectToRoom = async (url, token) => {
       if (!url || !token) {
        console.error('Missing URL or token');
         return;
    }
     try {
        const lkRoom = new Room();
        room.value = lkRoom;

        lkRoom.on(RoomEvent.Connected, async () => {
            console.log("✅ Connected to LiveKit");
            isConnected.value = true;
            await lkRoom.localParticipant.setMicrophoneEnabled(true);
            await lkRoom.localParticipant.setCameraEnabled(true);
            attachLocalVideo(lkRoom);
        });

        lkRoom.on(RoomEvent.TrackPublished, () => attachLocalVideo(lkRoom));

        lkRoom.on(RoomEvent.TrackSubscribed, (track, publication, participant) => {
            console.log("🎧 Subscribed to:", track.kind, "from", participant.identity);
            const el = track.attach();
            el.autoplay = true;
            el.playsInline = true;

            if (track.kind === "video") {
                el.style.width = "100%";
                el.style.height = "100%";
                el.style.objectFit = "cover";
                el.dataset.participant = participant.identity;

                const existing = remoteContainer.value.querySelector(
                    `[data-participant="${participant.identity}"]`
                );
                if (existing) existing.remove();
                remoteContainer.value.appendChild(el);
            } else if (track.kind === "audio") {
                el.muted = false;
                el.dataset.participant = participant.identity;
                el.style.display = "none";
                document.body.appendChild(el);
                el.play().catch((err) =>
                    console.warn("⚠️ Audio autoplay blocked:", err.message)
                );
            }
        });

        lkRoom.on(RoomEvent.TrackUnsubscribed, (track, publication, participant) => {
            document
                .querySelectorAll(`[data-participant="${participant.identity}"]`)
                .forEach((el) => el.remove());
        });

        lkRoom.on(RoomEvent.ParticipantConnected, (participant) => {
            console.log('👋 Participant connected:', participant.identity);
            updateParticipants(lkRoom);
        });

        lkRoom.on(RoomEvent.ParticipantDisconnected, (participant) => {
            console.log('👋 Participant disconnected:', participant.identity);
            updateParticipants(lkRoom);
        });

        lkRoom.on(RoomEvent.LocalTrackUnpublished, () => updateParticipants(lkRoom));

        lkRoom.on(RoomEvent.Disconnected, resetUI);

        lkRoom.on(RoomEvent.DataReceived, (payload, participant) => {
            const text = new TextDecoder().decode(payload);
            const name = participant?.identity?.split("_").pop() || "User";
            if (!showChat.value) showChat.value = true;
            chatMessages.value.push({
                from: name,
                text,
                time: getFormattedTime(),
            });
            nextTick(() => {
                const box = document.getElementById("chat-box");
                if (box) box.scrollTop = box.scrollHeight;
            });
        });
        await lkRoom.connect(url, token);
        connectingAppointments.value.delete(selectedPatient.value.id);

         updateParticipants(lkRoom);
    } catch (e) {
        console.error(e);
        errorMessage.value = e.message;
        connectingAppointments.value.delete(selectedPatient.value.id);

     }
};
const getFormattedTime = () => {
    return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const attachLocalVideo = async (lkRoom) => {
    await nextTick();
    if (!localContainer.value) return;
    localContainer.value.innerHTML = "";
    const pub = Array.from(lkRoom.localParticipant.videoTrackPublications.values())[0];
    if (pub?.track) {
        const el = pub.track.attach();
        el.muted = true;
        el.autoplay = true;
        el.playsInline = true;
        el.style.width = "100%";
        el.style.height = "100%";
        el.style.objectFit = "cover";
        localContainer.value.appendChild(el);
    }
};

const toggleMic = async () => {
    if (!room.value) return;
    micEnabled.value = !micEnabled.value;
    await room.value.localParticipant.setMicrophoneEnabled(micEnabled.value);
};

const toggleCamera = async () => {
    if (!room.value) return;
    cameraEnabled.value = !cameraEnabled.value;
    await room.value.localParticipant.setCameraEnabled(cameraEnabled.value);
    if (cameraEnabled.value) attachLocalVideo(room.value);
    else if (localContainer.value) localContainer.value.innerHTML = `<img src="/images/camera-off.svg" class="camera-off-icon" />`;
};

const toggleRecording = async () => {
    if (!room.value) return;

    try {
        if (isRecording.value) {
            await axios.post(route('livekit.record.stop'), {
                egressId: egressId.value
            });
            isRecording.value = false;
            egressId.value = null;
        } else {
            const response = await axios.post(route('livekit.record.start'), {
                roomName: room.value.name
            });
            egressId.value = response.data.egressId;
            isRecording.value = true;
        }
    } catch (error) {
        console.error('Recording error:', error);
        errorMessage.value = 'Failed to toggle recording. Please try again.';
    }
};

const disconnectRoom = async () => {
    if (room.value) {
        await room.value.disconnect();
        resetUI();
    }
};

const resetUI = () => {
    isConnected.value = false;
    micEnabled.value = true;
    cameraEnabled.value = true;
    showChat.value = false;
    showParticipants.value = false;
    isScreenSharing.value = false;
    isRecording.value = false;
    egressId.value = null;
    if (localContainer.value) localContainer.value.innerHTML = "";
    if (remoteContainer.value) remoteContainer.value.innerHTML = "";
    document.querySelectorAll("audio[data-participant]").forEach((el) => el.remove());
};

const toggleChat = () => (showChat.value = !showChat.value);
 const toggleNotifications = () => (notificationsEnabled.value = !notificationsEnabled.value);
const toggleTranscription = () => (transcriptionEnabled.value = !transcriptionEnabled.value);
const toggleSoapNotes = () => (showSoapNotes.value = !showSoapNotes.value);

const saveSoapNotes = async () => {
    if (!selectedPatient.value) return;

    // Clear previous messages
    successMessage.value = "";
    errorMessage.value = "";

    axios.post(route('doctor.call.encounter.store'), soapNotes)
        .then(response => {
            successMessage.value = "SOAP notes saved successfully!";
            console.log('SOAP notes saved:', response.data);
        })
        .catch(error => {
            console.error('Error saving SOAP notes:', error);

            if (error.response) {
                // Server responded with error status
                console.error('Response data:', error.response.data);
                console.error('Response status:', error.response.status);

                // Handle specific error cases
                if (error.response.status === 422) {
                    // Validation errors
                    console.error('Validation errors:', error.response.data.errors);
                    errorMessage.value = "Validation failed. Please check your input.";
                } else if (error.response.status === 403) {
                    errorMessage.value = "You don't have permission to save these notes.";
                } else {
                    errorMessage.value = "Failed to save SOAP notes. Please try again.";
                }
            } else {
                errorMessage.value = "Network error. Please check your connection.";
            }
        });
};

 const updateParticipants = (lkRoom) => {
    const newParticipants = [lkRoom.localParticipant];
    lkRoom.remoteParticipants.forEach(p => newParticipants.push(p));
    participants.value = newParticipants;
};

const sendChat = async () => {
    if (!chatInput.value.trim() || !room.value) return;
    const msg = chatInput.value.trim();
    await room.value.localParticipant.publishData(new TextEncoder().encode(msg), {
        reliable: true,
    });
    chatMessages.value.push({
        from: "You",
        text: msg,
        time: getFormattedTime(),
    });
    chatInput.value = "";
    nextTick(() => {
        const box = document.getElementById("chat-box");
        if (box) box.scrollTop = box.scrollHeight;
    });
};

const isAppointmentReady = (appointment) => {
    if (!appointment.appointment_date || !appointment.appointment_time) return false;

    const datePart = appointment.appointment_date.split('T')[0];
    const timePart = appointment.appointment_time.includes('T')
        ? appointment.appointment_time.split('T')[1].substring(0, 8)
        : appointment.appointment_time;

    // Create appointment datetime in local timezone
    const appt = new Date(`${datePart}T${timePart}`);

    // Get current time - ensure it's a Date object
    const now = new Date(currentTime.value); // Clone to avoid reference issues

    // Calculate difference in minutes (positive = past, negative = future)
    const diffMs = now.getTime() - appt.getTime();
    const diffMinutes = diffMs / (1000 * 60);

    // Enable button 5 minutes before appointment time until 2 hours after
    // diffMinutes <= 5 means appointment is within 5 minutes in the future
    // diffMinutes >= -120 means appointment is not more than 2 hours in the past
    return diffMinutes <= 5 && diffMinutes >= -120;
};

const startDrag = (event) => {
    if (!localContainer.value) return;
    isDragging.value = true;
    const rect = localContainer.value.getBoundingClientRect();
    dragOffset.value = {
        x: event.clientX - rect.left,
        y: event.clientY - rect.top
    };
    document.addEventListener('mousemove', drag);
    document.addEventListener('mouseup', stopDrag);
};

const drag = (event) => {
    if (!isDragging.value || !localContainer.value) return;
    const videoSection = localContainer.value.parentElement;
    const videoRect = videoSection.getBoundingClientRect();
    const containerRect = localContainer.value.getBoundingClientRect();

    let newLeft = event.clientX - dragOffset.value.x - videoRect.left;
    let newTop = event.clientY - dragOffset.value.y - videoRect.top;

    // Constrain to video section boundaries with some padding
    const padding = 10;
    newLeft = Math.max(padding, Math.min(newLeft, videoRect.width - containerRect.width - padding));
    newTop = Math.max(padding, Math.min(newTop, videoRect.height - containerRect.height - padding));

    localContainer.value.style.left = `${newLeft}px`;
    localContainer.value.style.top = `${newTop}px`;
    localContainer.value.style.right = 'auto';
    localContainer.value.style.bottom = 'auto';
};

const stopDrag = () => {
    isDragging.value = false;
    document.removeEventListener('mousemove', drag);
    document.removeEventListener('mouseup', stopDrag);
};
onBeforeUnmount(() => {
    if (timeInterval) clearInterval(timeInterval);
    disconnectRoom();
});
</script>
<template>
<AuthLayout2 title="Doctor Calls" description="View and manage your video calls" heading="Doctor Calls">
        <div class="row">

            <div class="col-lg-12">
                <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
                    <div class="iq-card-header d-flex justify-content-between">
                        <div class="iq-header-title">
                            <h4 class="card-title fs-22">Calls</h4>
                        </div>
                    </div>
                    <div class="iq-card-body">
                        <ul class="nav nav-pills mb-3" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link" :class="{ active: activeTab === 'past' }" @click.prevent="activeTab = 'past'" href="#">Past</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" :class="{ active: activeTab === 'present' }" @click.prevent="activeTab = 'present'" href="#">Present</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" :class="{ active: activeTab === 'future' }" @click.prevent="activeTab = 'future'" href="#">Future</a>
                            </li>
                        </ul>
                        <div class="table-responsive">
                            <table class="table mb-0 table-borderless">
                                <thead>
                                    <tr>
                                        <th scope="col">Patient Name</th>
                                        <th scope="col">Appointment Date</th>
                                        <th scope="col">Appointment Time</th>
                                         <th scope="col">Reason</th>
                                        <th scope="col">Action</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-for="appointment in filteredAppointments" :key="appointment.id">
                                        <tr>
                                            <td>
                                                {{ appointment?.patient?.name }}
                                            </td>
                                            <td>
                                                {{ appointment?.appointment_date }}
                                            </td>
                                            <td>
                                                {{ appointment?.appointment_time }}
                                            </td>
                                            <td>
                                                {{ appointment?.reason }}
                                            </td>
                                            <td>
                                           <button class="btn btn-primary" @click="roomCall(appointment)" :disabled="connectingAppointments.get(appointment.id) || !isAppointmentReady(appointment)">
                                                            <i class="ri-vidicon-line mr-2"></i>
                                                            {{ connectingAppointments.get(appointment.id) ? 'Connecting...' : 'Start Video Call' }}
                                            </button>
                                             </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            </div>
         <!---livekit--->
          <div v-if="isConnected" class="fullscreen-video-container">
            <!-- Header Bar -->
            <div class="video-header">
                <div class="session-info">
                    <h5 class="mb-0">Virtual Session - {{ selectedPatient?.patient?.name||selectedPatient?.patient?.user?.name || 'Patient' }}</h5>
                    <span class="session-badge"><i class="ri-user-line"></i> 1 in session</span>
                </div>
                <div class="header-actions">
                    <button class="header-btn" @click="toggleNotifications" :class="{ active: notificationsEnabled }">
                        <i class="ri-notification-line"></i> Enable Notifications
                    </button>
                    <button class="header-btn" @click="toggleTranscription" :class="{ active: transcriptionEnabled }">
                        <i class="ri-mic-line"></i> Start Transcription
                    </button>
                    <button class="header-btn" @click="toggleSoapNotes" :class="{ active: showSoapNotes }">
                        <i class="ri-file-text-line"></i> SOAP Notes
                    </button>
                    <button class="header-btn btn-end-call" @click="disconnectRoom">
                        <i class="ri-phone-line"></i> End Call
                    </button>
                </div>
            </div>

            <!-- Video Area -->
            <div class="video-main-area" :class="{ 'split-layout': showSoapNotes }">
                <!-- Video Section -->
                <div class="video-section">
                    <!-- Remote video -->
                    <div ref="remoteContainer" class="remote-video">
                     </div>

                    <!-- Local preview - Removed from top-right, will be in bottom controls area if needed -->
                    <div ref="localContainer" class="local-video" @mousedown.prevent="startDrag"></div>
                </div>

                <!-- SOAP Notes Section -->
                <transition name="slide-left">
                    <div v-if="showSoapNotes" class="soap-notes-section">
                        <div class="soap-notes-header">
                            <h6><i class="ri-file-text-line"></i> SOAP Notes</h6>
                            <button class="btn-close-sidebar" @click="toggleSoapNotes">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>
                        <div class="soap-notes-content">
                            <!-- Tab Navigation -->
                            <ul class="nav nav-tabs mb-3 flex-nowrap " role="tablist" style="flex-wrap: nowrap;">
                                <li class="nav-item flex-shrink-0">
                                    <a class="nav-link" :class="{ active: soapNotesActiveTab === 'subject' }" @click.prevent="soapNotesActiveTab = 'subject'" href="#" style="white-space: nowrap;">Subject</a>
                                </li>
                                <li class="nav-item flex-shrink-0">
                                    <a class="nav-link" :class="{ active: soapNotesActiveTab === 'objective' }" @click.prevent="soapNotesActiveTab = 'objective'" href="#" style="white-space: nowrap;">Objective</a>
                                </li>
                                <li class="nav-item flex-shrink-0">
                                    <a class="nav-link" :class="{ active: soapNotesActiveTab === 'assessment' }" @click.prevent="soapNotesActiveTab = 'assessment'" href="#" style="white-space: nowrap;">Assessment</a>
                                </li>
                                <li class="nav-item flex-shrink-0">
                                    <a class="nav-link" :class="{ active: soapNotesActiveTab === 'plan' }" @click.prevent="soapNotesActiveTab = 'plan'" href="#" style="white-space: nowrap;">Plan</a>
                                </li>
                            </ul>

                            <!-- Tab Content -->
                            <div class="tab-content flex-grow-1">
                                <!-- Subject Tab -->
                                <div v-if="soapNotesActiveTab === 'subject'" class="tab-pane active">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Chief Complaint</label>
                                        <textarea
                                            v-model="soapNotes.chief_complaint"
                                            class="form-control"
                                            rows="2"
                                            placeholder="Patient's main complaint..."
                                        ></textarea>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">History of Present Illness</label>
                                        <textarea
                                            v-model="soapNotes.hpi"
                                            class="form-control"
                                            rows="3"
                                            placeholder="Detailed history of the current illness..."
                                        ></textarea>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Review of Systems</label>
                                        <textarea
                                            v-model="soapNotes.ros"
                                            class="form-control"
                                            rows="3"
                                            placeholder="Review of other systems..."
                                        ></textarea>
                                    </div>
                                </div>

                                <!-- Objective Tab -->
                                <div v-if="soapNotesActiveTab === 'objective'" class="tab-pane active objective-tab">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Physical Exam</label>
                                        <textarea
                                            v-model="soapNotes.pe"
                                            class="form-control"
                                            rows="3"
                                            placeholder="Physical examination findings..."
                                        ></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">Weight (kg)</label>
                                                <input
                                                    v-model="soapNotes.weight"
                                                    type="number"
                                                    class="form-control"
                                                    placeholder="Weight in kg"
                                                />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">Height (cm)</label>
                                                <input
                                                    v-model="soapNotes.height"
                                                    type="number"
                                                    class="form-control"
                                                    placeholder="Height in cm"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">BMI (kg/m²)</label>
                                                <input
                                                    v-model="soapNotes.bmi"
                                                    type="number"
                                                    step="0.1"
                                                    class="form-control"
                                                    placeholder="BMI"
                                                />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">Temperature</label>
                                                <input
                                                    v-model="soapNotes.temperature"
                                                    type="number"
                                                    step="0.1"
                                                    class="form-control"
                                                    placeholder="Temp"
                                                />
                                            </div>
                                        </div>
                                      
                                    </div>
                                    <div class="row">
                                          <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">Temperature Method</label>
                                                <select v-model="soapNotes.temperature_method" class="form-control">
                                                    <option value="">Select</option>
                                                    <option value="oral">Oral</option>
                                                    <option value="axillary">Axillary</option>
                                                    <option value="rectal">Rectal</option>
                                                    <option value="tympanic">Tympanic</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">Systolic BP (mmHg)</label>
                                                <input
                                                    v-model="soapNotes.bp_systolic"
                                                    type="number"
                                                    class="form-control"
                                                    placeholder="Systolic"
                                                />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">Diastolic BP (mmHg)</label>
                                                <input
                                                    v-model="soapNotes.bp_diastolic"
                                                    type="number"
                                                    class="form-control"
                                                    placeholder="Diastolic"
                                                />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">BP Position</label>
                                                <select v-model="soapNotes.bp_position" class="form-control">
                                                    <option value="">Select</option>
                                                    <option value="sitting">Sitting</option>
                                                    <option value="standing">Standing</option>
                                                    <option value="lying">Lying</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">Pulse (bpm)</label>
                                                <input
                                                    v-model="soapNotes.pulse"
                                                    type="number"
                                                    class="form-control"
                                                    placeholder="Pulse rate"
                                                />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">Respirations (bpm)</label>
                                                <input
                                                    v-model="soapNotes.respirations"
                                                    type="number"
                                                    class="form-control"
                                                    placeholder="Respiratory rate"
                                                />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label fw-bold">O2 Saturation (%)</label>
                                                <input
                                                    v-model="soapNotes.o2_saturation"
                                                    type="number"
                                                    min="0"
                                                    max="100"
                                                    class="form-control"
                                                    placeholder="O2 Sat"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Assessment Tab -->
                                <div v-if="soapNotesActiveTab === 'assessment'" class="tab-pane active">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Additional Diagnoses</label>
                                        <textarea
                                            v-model="soapNotes.assessment_other"
                                            class="form-control"
                                            rows="3"
                                            placeholder="Additional diagnoses..."
                                        ></textarea>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Differential Diagnoses</label>
                                        <textarea
                                            v-model="soapNotes.differential_diagnoses"
                                            class="form-control"
                                            rows="3"
                                            placeholder="Differential diagnoses..."
                                        ></textarea>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Assessment Discussion</label>
                                        <textarea
                                            v-model="soapNotes.assessment_discussion"
                                            class="form-control"
                                            rows="3"
                                            placeholder="Discussion of assessment..."
                                        ></textarea>
                                    </div>
                                </div>

                                <!-- Plan Tab -->
                                <div v-if="soapNotesActiveTab === 'plan'" class="tab-pane active">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Recommendations</label>
                                        <textarea
                                            v-model="soapNotes.plan"
                                            class="form-control"
                                            rows="3"
                                            placeholder="Treatment recommendations..."
                                        ></textarea>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Follow up</label>
                                        <textarea
                                            v-model="soapNotes.followUp"
                                            class="form-control"
                                            rows="3"
                                            placeholder="Follow-up instructions..."
                                        ></textarea>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Total face-to-face time (min)</label>
                                        <input
                                            v-model="soapNotes.duration"
                                            type="number"
                                            class="form-control"
                                            placeholder="Total time in minutes"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Success/Error Messages -->
                            <div v-if="successMessage" class="alert alert-success mt-3">
                                <i class="ri-check-circle-line"></i>
                                {{ successMessage }}
                            </div>
                            <div v-if="errorMessage" class="alert alert-danger mt-3">
                                <i class="ri-error-warning-line"></i>
                                {{ errorMessage }}
                            </div>

                            <button class="btn btn-primary w-100 mt-3" @click="saveSoapNotes">
                                <i class="ri-save-line"></i> Save SOAP Notes
                            </button>
                        </div>
                    </div>
                </transition>
            </div>

            <!-- Bottom Control Bar -->
            <div class="control-bar">
                <div class="control-group">
                    <!-- Microphone -->
                    <div class="control-item">
                        <button class="control-btn" @click="toggleMic">
                            <i :class="micEnabled ? 'ri-mic-line' : 'ri-mic-off-line'"></i>
                        </button>
                        <span class="control-label">Microphone</span>
                     </div>

                    <!-- Camera -->
                    <div class="control-item">
                        <button class="control-btn" @click="toggleCamera">
                            <i :class="cameraEnabled ? 'ri-camera-line' : 'ri-camera-off-line'"></i>
                        </button>
                        <span class="control-label">Camera</span>
                     </div>

                    <!-- Chat -->
                    <div class="control-item">
                        <button class="control-btn" @click="toggleChat" :class="{ active: showChat }">
                            <i class="ri-chat-3-line"></i>
                        </button>
                        <span class="control-label">Chat</span>
                    </div>
                    <!-- Recording -->
                    <div class="control-item">
                        <button class="control-btn" @click="toggleRecording" :class="{ active: isRecording }">
                            <i :class="isRecording ? 'ri-stop-circle-line' : 'ri-record-circle-line'"></i>
                        </button>
                        <span class="control-label">{{ isRecording ? 'Stop Rec' : 'Record' }}</span>
                    </div>
                    <!-- Leave -->
                    <div class="control-item">
                        <button class="control-btn btn-leave" @click="disconnectRoom">
                            <i class="ri-logout-box-line"></i>
                        </button>
                        <span class="control-label">Leave</span>
                    </div>
                </div>
            </div>

            <!-- Chat Sidebar -->
            <transition name="slide">
                <div v-if="showChat && isConnected" class="sidebar-overlay chat-sidebar">
                    <div class="sidebar-box">
                        <div class="sidebar-header">
                            <h6><i class="ri-chat-3-line"></i> Chat</h6>
                            <button class="btn-close-sidebar" @click="toggleChat">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>
                        <div id="chat-box" class="chat-messages">
                            <div v-for="(m, i) in chatMessages" :key="i" class="chat-message">
                                <div class="message-header">
                                    <strong>{{ m.from }}</strong>
                                    <small>{{ m.time }}</small>
                                </div>
                                <p>{{ m.text }}</p>
                            </div>
                        </div>
                        <div class="chat-input-area">
                            <input 
                                v-model="chatInput" 
                                @keyup.enter="sendChat" 
                                placeholder="Type a message..." 
                                class="chat-input" 
                            />
                            <button class="btn-send" @click="sendChat">
                                <i class="ri-send-plane-fill"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </transition>
        </div>
    </AuthLayout2>
</template>

<style scoped>
/* Fullscreen Video Container */
.fullscreen-video-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: #1a1a1a;
    z-index: 9999;
    display: flex;
    flex-direction: column;
}

/* Header Bar */
.video-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 24px;
    background: #fff;
    border-bottom: 1px solid #e0e0e0;
    z-index: 10;
}

.session-info {
    display: flex;
    align-items: center;
    gap: 16px;
}

.session-info h5 {
    margin: 0;
    color: #1a1a1a;
    font-size: 16px;
    font-weight: 600;
}

.session-badge {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    background: #f5f5f5;
    border-radius: 4px;
    font-size: 13px;
    color: #666;
}

.header-actions {
    display: flex;
    gap: 12px;
}

.header-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    color: #333;
    cursor: pointer;
    transition: all 0.2s;
}

.header-btn:hover {
    background: #f5f5f5;
}

.header-btn.active {
    background: #e3f2fd;
    border-color: #2196f3;
    color: #2196f3;
}

.header-btn.btn-end-call {
    background: #ef5350;
    color: #fff;
    border-color: #ef5350;
}

.header-btn.btn-end-call:hover {
    background: #e53935;
}

/* Video Area */
.video-main-area {
    flex: 1;
    position: relative;
    background: #000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.video-main-area.split-layout {
    display: flex;
    flex-direction: row;
}

.video-section {
    flex: 1;
    position: relative;
    background: #000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.soap-notes-section {
    width: 400px;
    background: #fff;
    display: flex;
    flex-direction: column;
    border-left: 1px solid #e0e0e0;
    height:550px;
}

.soap-notes-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #e0e0e0;
    background: #f8f9fa;
}

.soap-notes-header h6 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.soap-notes-content {
    flex: 1;
    padding: 10px;
    overflow-y: auto;
}

.soap-notes-content .form-group {
    margin-bottom: 1rem;
}

.soap-notes-content .form-label {
    color: #1a1a1a;
    margin-bottom: 0.5rem;
}

.soap-notes-content .form-control {
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
}

.soap-notes-content .form-control:focus {
    border-color: #2196f3;
    box-shadow: 0 0 0 0.2rem rgba(33, 150, 243, 0.25);
}

.soap-notes-content .alert {
    padding: 12px 16px;
    border-radius: 6px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.soap-notes-content .alert-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}

.soap-notes-content .alert-danger {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}

.remote-video {
    width: 100%;
    height: 100%;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.remote-video video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.local-video {
    position: absolute;
    top: 20px;
    left: calc(100% - 260px); /* 240px width + 20px margin */
    width: 240px;
    height: 135px;
    background: #000;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    overflow: hidden;
    z-index: 20;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    cursor: grab;
    user-select: none;
}

.local-video:active {
    cursor: grabbing;
}

.clinic-label {
    position: absolute;
    bottom: 20px;
    left: 20px;
    padding: 8px 16px;
    background: rgba(0, 0, 0, 0.6);
    color: #fff;
    border-radius: 6px;
    font-size: 14px;
    backdrop-filter: blur(10px);
}

/* Bottom Control Bar */
.control-bar {
    padding: 20px;
    background: #2b2b2b;
    display: flex;
    justify-content: center;
}

.control-group {
    display: flex;
    gap: 24px;
    align-items: flex-start;
}

.control-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    position: relative;
}

.control-item.dropdown {
    flex-direction: row;
    gap: 0;
}

.control-btn {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #3c4043;
    border: none;
    color: #fff;
    font-size: 20px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.control-btn:hover {
    background: #5f6368;
}

.control-btn.active {
    background: #1976d2;
}

.control-btn.btn-leave {
    background: #ea4335;
}

.control-btn.btn-leave:hover {
    background: #d33b2c;
}

.dropdown-toggle {
    width: 32px;
    height: 48px;
    background: #3c4043;
    border: none;
    border-left: 1px solid #5f6368;
    color: #fff;
    cursor: pointer;
    border-radius: 0 24px 24px 0;
    margin-left: -8px;
}

.control-item.dropdown .control-btn {
    border-radius: 24px 0 0 24px;
}

.control-label {
    font-size: 13px;
    color: #e8eaed;
    white-space: nowrap;
}

.control-item.dropdown .control-label {
    position: absolute;
    bottom: -24px;
    left: 0;
    right: 0;
    text-align: center;
}

/* Chat Sidebar */
.sidebar-overlay {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    width: 360px;
    z-index: 10001;
    background: rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(4px);
}

.sidebar-box {
    width: 100%;
    height: 100%;
    background: #fff;
    display: flex;
    flex-direction: column;
    box-shadow: -2px 0 8px rgba(0, 0, 0, 0.1);
}

.sidebar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #e0e0e0;
}

.sidebar-header h6 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-close-sidebar {
    background: none;
    border: none;
    font-size: 20px;
    color: #666;
    cursor: pointer;
    padding: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
}

.chat-message {
    margin-bottom: 16px;
}

.message-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 4px;
}

.message-header strong {
    font-size: 14px;
    color: #1a1a1a;
}

.message-header small {
    font-size: 12px;
    color: #999;
}

.chat-message p {
    margin: 0;
    padding: 8px 12px;
    background: #f5f5f5;
    border-radius: 8px;
    font-size: 14px;
    color: #333;
}

.chat-input-area {
    padding: 16px;
    border-top: 1px solid #e0e0e0;
    display: flex;
    gap: 8px;
}

.chat-input {
    flex: 1;
    padding: 10px 14px;
    border: 1px solid #e0e0e0;
    border-radius: 24px;
    font-size: 14px;
    outline: none;
}

.chat-input:focus {
    border-color: #2196f3;
}

.btn-send {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #2196f3;
    border: none;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.btn-send:hover {
    background: #1976d2;
}

/* Slide Animation */
.slide-enter-active,
.slide-leave-active {
    transition: transform 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
    transform: translateX(100%);
}

/* Slide Left Animation for SOAP Notes */
.slide-left-enter-active,
.slide-left-leave-active {
    transition: transform 0.3s ease;
}

.slide-left-enter-from,
.slide-left-leave-to {
    transform: translateX(100%);
}

/* Camera Off Placeholder */
.camera-off-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #333;
    color: #999;
    font-size: 48px;
}

/* Objective Tab Scrolling */
.objective-tab {
    max-height: 400px;
    overflow-y: auto;
    padding-right: 10px;
}

.objective-tab::-webkit-scrollbar {
    width: 6px;
}

.objective-tab::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.objective-tab::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.objective-tab::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}


</style>