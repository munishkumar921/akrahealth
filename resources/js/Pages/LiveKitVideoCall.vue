<script setup>
import { ref, onBeforeUnmount, nextTick } from "vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { Room, RoomEvent } from "livekit-client";
import { Inertia } from "@inertiajs/inertia";

const props = defineProps({
    token: String,
    url: String,
});

const room = ref(null);
const isConnected = ref(false);
const isConnecting = ref(false);
const localContainer = ref(null);
const remoteContainer = ref(null);
const micEnabled = ref(true);
const cameraEnabled = ref(true);
const showChat = ref(false);
const chatMessages = ref([]);
const chatInput = ref("");
const errorMessage = ref("");

/* ==== Connect ==== */
const connectToRoom = async () => {
    isConnecting.value = true;
    try {
        const lkRoom = new Room();

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
                // Attach video inside Vue container
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
                // Attach audio directly to body (like your working HTML)
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

        lkRoom.on(RoomEvent.Disconnected, resetUI);

        // === Chat Listener ===
        lkRoom.on(RoomEvent.DataReceived, (payload, participant) => {
            const text = new TextDecoder().decode(payload);
            const name = participant?.identity?.split("_").pop() || "User";
            if (!showChat.value) showChat.value = true;
            chatMessages.value.push({
                from: name,
                text,
                time: new Date().toLocaleTimeString(),
            });
            nextTick(() => {
                const box = document.getElementById("chat-box");
                if (box) box.scrollTop = box.scrollHeight;
            });
        });

        await lkRoom.connect(props.url, props.token);
        room.value = lkRoom;
        isConnecting.value = false;
    } catch (e) {
        console.error(e);
        errorMessage.value = e.message;
        isConnecting.value = false;
    }
};

/* === Attach Local Video === */
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

/* === Toggle Mic === */
const toggleMic = async () => {
    if (!room.value) return;
    micEnabled.value = !micEnabled.value;
    await room.value.localParticipant.setMicrophoneEnabled(micEnabled.value);
};

/* === Toggle Camera === */
const toggleCamera = async () => {
    if (!room.value) return;
    cameraEnabled.value = !cameraEnabled.value;
    await room.value.localParticipant.setCameraEnabled(cameraEnabled.value);
    if (cameraEnabled.value) attachLocalVideo(room.value);
    else if (localContainer.value) localContainer.value.innerHTML = `<img src="/images/camera-off.svg" class="camera-off-icon" />`;
};

/* === Disconnect === */
const disconnectRoom = async () => {
    if (room.value) {
        await room.value.disconnect();
        resetUI();
    }
    Inertia.reload();
};

const resetUI = () => {
    isConnected.value = false;
    micEnabled.value = true;
    cameraEnabled.value = true;
    if (localContainer.value) localContainer.value.innerHTML = "";
    if (remoteContainer.value) remoteContainer.value.innerHTML = "";
    document.querySelectorAll("audio[data-participant]").forEach((el) => el.remove());
};

/* === Chat === */
const toggleChat = () => (showChat.value = !showChat.value);
const sendChat = async () => {
    if (!chatInput.value.trim() || !room.value) return;
    const msg = chatInput.value.trim();
    await room.value.localParticipant.publishData(new TextEncoder().encode(msg), {
        reliable: true,
    });
    chatMessages.value.push({
        from: "You",
        text: msg,
        time: new Date().toLocaleTimeString(),
    });
    chatInput.value = "";
    nextTick(() => {
        const box = document.getElementById("chat-box");
        if (box) box.scrollTop = box.scrollHeight;
    });
};

onBeforeUnmount(disconnectRoom);
</script>


<template>
    <AuthLayout title="Video Call" description="LiveKit Chat" heading="Video Call">
        <div class="position-relative">
            <div class="row">
                <div class="col-12 position-relative remote-container p-0">
                    <!-- Remote video -->
                    <div ref="remoteContainer" class="remote-video"></div>

                    <!-- Local preview -->
                    <div ref="localContainer" class="local-container"></div>

                    <!-- Controls -->
                    <div class="call-controls" v-if="!isConnecting">
                        <template v-if="!isConnected">
                            <img @click="connectToRoom" src="/images/call-on.svg" />
                        </template>
                        <template v-else>
                            <img @click="disconnectRoom" src="/images/call-off.svg" />
                            <img @click="toggleMic" :src="micEnabled ? '/images/mic-on.svg' : '/images/mic-off.svg'" />
                            <img @click="toggleCamera" :src="cameraEnabled
                                ? '/images/camera-on.svg'
                                : '/images/camera-off.svg'
                                " />
                            <img @click="toggleChat" src="/images/chat.svg" />
                        </template>
                    </div>
                </div>
            </div>

            <!-- Chat Sidebar -->
            <transition name="slide">
                <div v-if="showChat" class="chat-box position-absolute bg-white border rounded p-3 shadow">
                    <div class="row align-items-center mb-2">
                        <div class="col">
                            <h6 class="mb-0">💬 Chat</h6>
                        </div>
                        <div class="col-auto text-end">
                            <button type="button" class="btn btn-sm btn-light" @click="toggleChat">
                                X
                            </button>
                        </div>
                    </div>
                    <div id="chat-box" class="border rounded p-2 mb-2" style="height: 340px; overflow-y: auto">
                        <p v-for="(m, i) in chatMessages" :key="i" class="mb-1">
                            <strong>{{ m.from }}:</strong> {{ m.text }}
                            <small class="text-muted d-block" style="font-size: 10px">{{
                                m.time
                            }}</small>
                        </p>
                    </div>
                    <div class="d-flex">
                        <input v-model="chatInput" @keyup.enter="sendChat" placeholder="Type a message..."
                            class="form-control me-2" />
                        <button class="btn btn-primary" @click="sendChat">Send</button>
                    </div>
                </div>
            </transition>

            <div v-if="errorMessage" style="display: none">{{ errorMessage }}</div>
        </div>
    </AuthLayout>
</template>

<style scoped>
.remote-container {
    position: relative;
    background: #f8f6fc;
    border-radius: 12px;
    overflow: hidden;
    width: 100%;
    height: calc(100vh - 230px);
    margin: 0;
    padding: 0;
}

.remote-video video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.local-container {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 240px;
    height: 135px;
    border-radius: 8px;
    overflow: hidden;
    background: rgba(0, 0, 0, 0.7);
    border: 2px solid #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.call-controls {
    position: absolute;
    bottom: 12px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 14px;
    background: rgba(0, 0, 0, 0.5);
    padding: 8px 16px;
    border-radius: 30px;
    z-index: 10;
}

.call-controls img {
    width: 30px;
    height: 30px;
    cursor: pointer;
    filter: invert(1);
    transition: transform 0.2s;
}

.call-controls img:hover {
    transform: scale(1.1);
}

.chat-box {
    top: 150px;
    right: -3px;
    width: 300px;
    height: 450px;
    overflow: hidden;
    z-index: 20;
}

.camera-off-bg {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(8px);
}

.camera-off-icon {
    width: 64px;
    opacity: 0.8;
}
</style>
