<script setup>
import AuthLayout2 from '@/Layouts/AuthLayout2.vue';
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Room, RoomEvent } from 'livekit-client';
import { useForm, router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    appointment: Object
});

const room = ref(null);
const token = ref(null);
const identity = ref(null);
const url = ref(null);
const isConnected = ref(false);
const connecting = ref(false);
const localContainer = ref(null);
const remoteContainer = ref(null);
const micEnabled = ref(true);
const cameraEnabled = ref(true);
const showChat = ref(false);
const chatMessages = ref([]);
const participants = ref([]);
const errorMessage = ref("");
const successMessage = ref("");
const chatInput = ref("");

onMounted(async () => {
    await joinCall();
});

onBeforeUnmount(() => {
    disconnectRoom();
});

const joinCall = async () => {
    if (connecting.value) return;

    connecting.value = true;
    errorMessage.value = "";

    try {
        // Generate token for patient
        const tokenResponse = await axios.get(route('doctor.generateToken', {
            id: props.appointment.id
        }));

        const { room: roomData, token: tokenData, identity: identityData, url: urlData } = tokenResponse.data;

        if (!roomData || !tokenData || !identityData || !urlData) {
            throw new Error('Incomplete response data from server');
        }

        room.value = roomData;
        token.value = tokenData;
        identity.value = identityData;
        url.value = urlData;

        console.log('Connecting to room...', { url: urlData });

        await connectToRoom(urlData, tokenData);
    } catch (error) {
        console.error('Error joining call:', error);
        errorMessage.value = error.response?.data?.message || error.message || 'Failed to join the call. Please try again.';
        connecting.value = false;
    }
};

const connectToRoom = async (url, token) => {
    try {
        const lkRoom = new Room();
        room.value = lkRoom;

        lkRoom.on(RoomEvent.Connected, async () => {
            console.log("✅ Connected to LiveKit");
            isConnected.value = true;
            connecting.value = false;
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
        updateParticipants(lkRoom);
    } catch (e) {
        console.error(e);
        errorMessage.value = e.message;
        connecting.value = false;
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

const disconnectRoom = async () => {
    if (room.value) {
        await room.value.disconnect();
        resetUI();
    }
    router.visit(route('patient.dashboard'));
};

const resetUI = () => {
    isConnected.value = false;
    micEnabled.value = true;
    cameraEnabled.value = true;
    showChat.value = false;
    if (localContainer.value) localContainer.value.innerHTML = "";
    if (remoteContainer.value) remoteContainer.value.innerHTML = "";
    document.querySelectorAll("audio[data-participant]").forEach((el) => el.remove());
};

const toggleChat = () => (showChat.value = !showChat.value);

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
</script>

<template>
    <AuthLayout2 title="Live Consultation" description="Join your video consultation" heading="">
        <div class="container-fluid">
            <!-- Connection Status -->
            <div v-if="!isConnected && !errorMessage" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Connecting...</span>
                </div>
                <h4 class="mt-3">Connecting to your consultation...</h4>
                <p class="text-muted">Please wait while we connect you to {{ appointment?.doctor?.name || appointment?.doctor?.user?.name }}</p>
            </div>

            <!-- Error Message -->
            <div v-if="errorMessage && !isConnected" class="alert alert-danger text-center py-4">
                <i class="ri-error-warning-line fs-1"></i>
                <h5 class="mt-3">Connection Failed</h5>
                <p>{{ errorMessage }}</p>
                <button @click="joinCall" class="btn btn-primary">
                    <i class="ri-refresh-line"></i> Try Again
                </button>
                <button @click="router.visit(route('patient.dashboard'))" class="btn btn-danger ms-2">
                    <i class="ri-home-line"></i> Go to Dashboard
                </button>
            </div>

            <!-- Video Call Interface -->
            <div v-if="isConnected" class="fullscreen-video-container">
                <!-- Header Bar -->
                <div class="video-header">
                    <div class="session-info">
                        <h5 class="mb-0">Live Consultation with Dr. {{ appointment?.doctor?.name ||appointment?.doctor?.user?.name }}</h5>
                        <span class="session-badge">
                            <i class="ri-user-line"></i>
                            {{ participants.length }} in session
                        </span>
                    </div>
                    <div class="header-actions">
                        <button class="header-btn" @click="toggleChat" :class="{ active: showChat }">
                            <i class="ri-chat-3-line"></i> Chat
                        </button>
                        <button class="header-btn btn-end-call" @click="disconnectRoom">
                            <i class="ri-phone-line"></i> End Call
                        </button>
                    </div>
                </div>

                <!-- Video Area -->
                <div class="video-main-area">
                    <!-- Remote video (Doctor) -->
                    <div ref="remoteContainer" class="remote-video">
                        <div v-if="participants.length <= 1" class="waiting-message">
                            <i class="ri-time-line fs-1 text-muted"></i>
                            <h5 class="mt-3">Waiting for doctor...</h5>
                            <p class="text-muted">Please wait while your doctor joins the call.</p>
                        </div>
                    </div>

                    <!-- Local video (Patient) -->
                    <div ref="localContainer" class="local-video"></div>
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

.video-section {
    flex: 1;
    position: relative;
    background: #000;
    display: flex;
    align-items: center;
    justify-content: center;
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

.waiting-message {
    text-align: center;
    color: #999;
}

.local-video {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 240px;
    height: 135px;
    background: #000;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    overflow: hidden;
    z-index: 20;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
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

.control-label {
    font-size: 13px;
    color: #e8eaed;
    white-space: nowrap;
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

/* Alert Styles */
.alert {
    margin: 20px;
    border-radius: 8px;
}

.alert-danger {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
}
</style>
