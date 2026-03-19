<script setup>
 import { onMounted } from 'vue';

onMounted(() => {
  // Ensure Calendly script is loaded
  const calendlyScriptUrl = 'https://assets.calendly.com/assets/external/widget.js';

  if (!document.querySelector(`script[src="${calendlyScriptUrl}"]`)) {
    const script = document.createElement('script');
    script.src = calendlyScriptUrl;
    script.async = true;
    document.body.appendChild(script);

    script.onload = () => {
      initCalendly();
    };
  } else {
    initCalendly();
  }

  function initCalendly() {
    if (window.Calendly) {
      window.Calendly.initInlineWidget({
        url: 'https://calendly.com/akrahealth/new-meeting',
        parentElement: document.getElementById('calendly-embed'),
      });
    } else {
      console.error('Calendly script failed to load.');
    }
  }
});
</script>

<template>
    <div id="calendly-embed" style="min-width: 320px; height: 535px;" ></div>
</template>
