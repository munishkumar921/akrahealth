<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue'
import { ref, onMounted, onBeforeUnmount } from "vue";
import AgoraRTC from "agora-rtc-sdk-ng";
import axios from "axios";
import { usePage } from "@inertiajs/vue3";

const client = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });
const localTracks = ref({ video: null, audio: null });
const remoteUsers = ref([]);
const channel_name = ref('');

const props = defineProps({
  appointment: Object
});

const defaultAvatar = '/avtar.jpg';

const role = usePage().props?.auth?.user?.roles?.[0]?.name;

// ======================
// JOIN CHANNEL
// ======================
const joinChannel = async () => {
  toast('Video call will start in a moment!', 'success');

  const res = await axios.post(`/agora/token/${props.appointment.doctor.id}/${props.appointment.id}`);
  const { appId, token, channel, uid } = res.data;
  channel_name.value = channel;

  await client.join(appId, channel, token, uid);

  // Create and publish local tracks
  localTracks.value.audio = await AgoraRTC.createMicrophoneAudioTrack();
  localTracks.value.video = await AgoraRTC.createCameraVideoTrack();
  localTracks.value.video.play("local-player");
  await client.publish(Object.values(localTracks.value));

  // console.log("Local stream published:", uid);

  registerAgoraEvents();

  // 🧩 NEW FIX:
  // If there are already remote users in the channel (joined before you), subscribe to them manually
  client.remoteUsers.forEach(async (user) => {
    // 🧩 Skip self
    if (user.uid === uid) return;

    console.log('uid ============', uid);
    console.log('user ============', user);

    // console.log("Subscribing to existing remote user:", user.uid);
    await client.subscribe(user, "video").catch(console.error);
    await client.subscribe(user, "audio").catch(console.error);

    createRemotePlayer(user.uid);
    user.videoTrack?.play(`remote-player-${user.uid}`);
    user.audioTrack?.play();

    if (!remoteUsers.value.find(u => u.uid === user.uid)) {
      remoteUsers.value.push(user);
    }
  });
};


// ======================
// AGORA EVENT HANDLERS
// ======================
const registerAgoraEvents = () => {
  // When a remote user publishes (audio/video)
  client.on("user-published", async (user, mediaType) => {
    if (user.uid === client.uid) return;
    // console.log("User published:", user.uid);
    await client.subscribe(user, mediaType);

    if (mediaType === "video") {
      createRemotePlayer(user.uid);
      user.videoTrack.play(`remote-player-${user.uid}`);
    }
    if (mediaType === "audio") {
      user.audioTrack.play();
    }

    // prevent duplicates
    if (!remoteUsers.value.find(u => u.uid === user.uid)) {
      remoteUsers.value.push(user);
    }
  });

  // When a user leaves or unpublishes
  client.on("user-unpublished", (user) => {
    // console.log("User unpublished:", user.uid);
    removeRemotePlayer(user.uid);
    remoteUsers.value = remoteUsers.value.filter(u => u.uid !== user.uid);
  });

  client.on("user-left", (user) => {
    // console.log("User left:", user.uid);
    removeRemotePlayer(user.uid);
    remoteUsers.value = remoteUsers.value.filter(u => u.uid !== user.uid);
  });
};

// dynamically create remote div
const createRemotePlayer = (uid) => {
  let remoteDiv = document.getElementById(`remote-player-${uid}`);
  if (!remoteDiv) {
    remoteDiv = document.createElement("div");
    remoteDiv.id = `remote-player-${uid}`;
    remoteDiv.style.width = "500px";
    remoteDiv.style.height = "500px";
    remoteDiv.style.background = `#000 url(${defaultAvatar}) center / cover no-repeat`;
    document.getElementById("remote-container").appendChild(remoteDiv);
  }
};

const removeRemotePlayer = (uid) => {
  const remoteDiv = document.getElementById(`remote-player-${uid}`);
  if (remoteDiv) remoteDiv.remove();
};

// ======================
// LEAVE CHANNEL
// ======================
const leaveChannel = async () => {
  for (const track of Object.values(localTracks.value)) {
    track?.stop();
    track?.close();
  }
  await client.leave();
  remoteUsers.value = [];
  // console.log("Left channel");
};

onBeforeUnmount(() => {
  leaveChannel();
});
</script>

<template>
  <AuthLayout title="Video Call" description="Doctor–Patient Consultation">
    <div class="container-fluid">
      <h3 class="mb-3">
        Call with
        <template v-if="role === 'Patient'">
          {{ appointment.doctor.user.name }}
        </template>
        <template v-else>
          {{ appointment.patient.user.name }}
        </template>
        <!-- -- {{ channel_name }} -->
      </h3>

      <div class="d-flex mb-4" style="gap: 16px;">
        <!-- Local video (big) -->
        <div style="flex: 0 0 50%; max-width: 50%;">
          <div id="local-player" class="w-100 rounded shadow overflow-hidden bg-black"
            style="height: 500px; background: #000 url(/avtar.jpg) center / cover no-repeat;">
          </div>
        </div>

        <!-- Remote video (small) -->
        <div id="remote-container" class="rounded shadow overflow-hidden"
          style="flex: 0 0 50%; max-width: 50%; display: flex; flex-wrap: wrap; gap: 10px; background: #000 url(/avtar.jpg) center / cover no-repeat;">

          <template v-for="(user, index) in remoteUsers" :key="user.uid">

            <div v-if="index > 0" class="w-100 rounded shadow overflow-hidden bg-black"
              :id="`remote-player-${user.uid}`" style="width: 500px; height: 500px; background: #333;"></div>
          </template>
        </div>
      </div>

      <div class="mt-4 d-flex justify-content-center gap-2">
        <button @click="joinChannel" class="btn btn-primary px-4">Join</button>
        <button @click="leaveChannel" class="btn btn-danger px-4">Leave</button>
      </div>
    </div>
  </AuthLayout>
</template>
