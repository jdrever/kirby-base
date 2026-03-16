<?php
/**
 * Colour mode script — inlined in <head> so it runs immediately.
 *
 * Handles everything in one place:
 * - Reads the stored cookie and applies data-bs-theme before the page renders
 *   (preventing flash of the wrong theme)
 * - Responds to OS-level preference changes when mode is 'auto'
 * - Hydrates the selector UI and wires up click handlers once the DOM is ready
 *
 * No external JS file or addScript() call is needed in consuming sites.
 */
?>
<script>
;(function () {
  var COOKIE_NAME = 'colourMode'
  var COOKIE_DAYS = 365
  var html = document.documentElement

  function getCookie(name) {
    var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'))
    return match ? decodeURIComponent(match[2]) : null
  }

  function setCookie(name, value, days) {
    var date = new Date()
    date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000)
    document.cookie = name + '=' + encodeURIComponent(value) + ';expires=' + date.toUTCString() + ';path=/;SameSite=Lax'
  }

  function getStoredMode() {
    return getCookie(COOKIE_NAME) || 'auto'
  }

  function applyTheme(mode) {
    if (mode === 'auto') {
      mode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
    }
    html.setAttribute('data-bs-theme', mode)
  }

  function updateSelectorUI(mode) {
    document.querySelectorAll('.colour-mode-btn').forEach(function (btn) {
      var active = btn.dataset.colourMode === mode
      btn.style.fontWeight = active ? 'bold' : 'normal'
      btn.disabled = active
    })
  }

  function setColourMode(mode) {
    setCookie(COOKIE_NAME, mode, COOKIE_DAYS)
    applyTheme(mode)
    updateSelectorUI(mode)
  }

  // Apply immediately to prevent flash
  var storedMode = getStoredMode()
  applyTheme(storedMode)

  // Re-apply if OS preference changes while in auto mode
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
    if (getStoredMode() === 'auto') applyTheme('auto')
  })

  // Wire up selector buttons once DOM is ready
  function init() {
    updateSelectorUI(storedMode)
    document.querySelectorAll('.colour-mode-btn').forEach(function (btn) {
      btn.addEventListener('click', function () { setColourMode(btn.dataset.colourMode) })
    })
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init)
  } else {
    init()
  }
})()
</script>
