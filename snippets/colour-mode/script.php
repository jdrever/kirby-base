<?php
/**
 * Colour mode early-apply script.
 * Inlines a minimal script in <head> to read the colourMode cookie and set
 * data-bs-theme on <html> immediately, preventing flash of unstyled content.
 * The full colour-mode.js (loaded at end of body) handles selector hydration
 * and click events.
 */
?>
<script>
(function () {
  var match = document.cookie.match(/(^| )colourMode=([^;]+)/)
  var mode = match ? decodeURIComponent(match[2]) : 'auto'
  if (mode === 'auto') {
    mode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
  }
  document.documentElement.setAttribute('data-bs-theme', mode)
})()
</script>
