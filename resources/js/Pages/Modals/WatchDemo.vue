<script setup>
import { onMounted, onUnmounted, ref } from 'vue'

const videoUrl = 'https://www.youtube.com/embed/M-kVxZXYn7g'
const iframeRef = ref(null)
const demoModalRef = ref(null)

onMounted(() => {
    demoModalRef.value = document.getElementById('watch-demo-modal')
    if (demoModalRef.value) {
        demoModalRef.value.addEventListener('hidden.bs.modal', stopVideo)
    }
})

onUnmounted(() => {
    if (demoModalRef.value) {
        demoModalRef.value.removeEventListener('hidden.bs.modal', stopVideo)
    }
})

function stopVideo() {
    if (iframeRef.value) {
        // Better approach: Reset to original URL instead of empty string
        iframeRef.value.src = videoUrl
    }
}
</script>

<template>
    <section>
        <div class="modal fade" id="watch-demo-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content bg-dark">
                    <div class="modal-header border-0 p-2">
                        <button 
                            type="button" 
                            class="btn-close btn-close-white" 
                            @click="stopVideo" 
                            data-dismiss="modal" 
                            aria-label="Close"
                            style="margin-left: auto;"
                        >
                        </button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="ratio ratio-16x9">
                            <iframe 
                                ref="iframeRef" 
                                :src="videoUrl" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen
                            ></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>