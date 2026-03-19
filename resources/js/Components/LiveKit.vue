<template>
  <div>
    <h2>LiveKit Video Room</h2>
    
    <button @click="connectRoom">Join Room</button>
    <div ref="videoContainer" style="width:100%;height:400px;background:#000;margin-top:10px;"></div>
  </div>
</template>

<script>
import { connect, Room } from "livekit-client";

export default {
  data() {
    return {
      room: null,
    };
  },
  methods: {
    async connectRoom() {
      try {
        const { data } = await axios.get("conference.call"); // Token from server

        this.room = await connect(data.url, data.token);

        this.room.on("trackSubscribed", (track, publication, participant) => {
          const videoContainer = this.$refs.videoContainer;
          if (track.kind === "video") {
            const element = track.attach();
            videoContainer.appendChild(element);
          }
        });

        const localTracks = await Room.createLocalTracks({
          audio: true,
          video: true,
        });

        localTracks.forEach(track => this.room.localParticipant.publishTrack(track));

        console.log("Connected to LiveKit Room");
      } catch (error) {
        console.log("Connection error:", error);
      }
    }
  }
};
</script>
