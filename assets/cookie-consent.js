;(function() {
  const COOKIE_NAME = 'cookieConsent'
  const LEGACY_COOKIE_NAME = 'cookieConsentGiven'
  const COOKIE_DAYS = 365

  /**
   * Get a cookie value by name
   * @param {string} name
   * @returns {string|null}
   */
  function getCookie(name) {
    const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'))
    return match ? decodeURIComponent(match[2]) : null
  }

  /**
   * Set a cookie with expiry
   * @param {string} name
   * @param {string} value
   * @param {number} days
   */
  function setCookie(name, value, days) {
    const date = new Date()
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000))
    const expires = 'expires=' + date.toUTCString()
    document.cookie = name + '=' + encodeURIComponent(value) + ';' + expires + ';path=/;SameSite=Lax'
  }

  /**
   * Delete a cookie by name
   * @param {string} name
   */
  function deleteCookie(name) {
    document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/;SameSite=Lax'
  }

  /**
   * Get consent status from cookie, including legacy cookie name and values.
   * Recognises legacy cookie 'cookieConsentGiven' with values 'true', 'yes', '1'
   * (mapped to 'accepted') and 'no' (mapped to 'rejected').
   * @returns {'accepted'|'rejected'|null}
   */
  function getConsentStatus() {
    var value = getCookie(COOKIE_NAME)
    if (!value) {
      var legacy = getCookie(LEGACY_COOKIE_NAME)
      if (legacy) {
        if (['true', 'yes', '1'].indexOf(legacy) !== -1) {
          value = 'accepted'
        } else if (legacy === 'no') {
          value = 'rejected'
        }
      }
    }
    return value
  }

  /**
   * Set consent status in cookie
   * @param {'accepted'|'rejected'} status
   */
  function setConsentStatus(status) {
    setCookie(COOKIE_NAME, status, COOKIE_DAYS)
    deleteCookie(LEGACY_COOKIE_NAME)
    applyConsentState(status)
  }

  /**
   * Check if consent has been given (accepted)
   * @returns {boolean}
   */
  function hasConsent() {
    return getConsentStatus() === 'accepted'
  }

  /**
   * Check if consent has been rejected
   * @returns {boolean}
   */
  function hasRejected() {
    return getConsentStatus() === 'rejected'
  }

  /**
   * Apply the consent state to the page
   * CSS defaults: banner hidden, content shown, placeholder hidden
   * JS shows banner if no choice made, swaps content/placeholder if not accepted
   * @param {string|null} status
   */
  function applyConsentState(status) {
    // Show banner if no choice made, hide it once a choice is recorded
    document.querySelectorAll('[data-cookie-consent-banner]').forEach(el => {
      el.style.display = status ? 'none' : 'block'
    })

    // Handle consent-required content blocks
    // If not accepted, show placeholder and hide content
    document.querySelectorAll('[data-requires-consent]').forEach(container => {
      const placeholder = container.querySelector('[data-consent-placeholder]')
      const content = container.querySelector('[data-consent-content]')

      if (placeholder && content) {
        if (status === 'accepted') {
          // CSS defaults handle this, but be explicit
          placeholder.style.display = 'none'
          content.style.display = ''
        } else {
          // No consent or rejected - show placeholder, hide content
          placeholder.style.display = ''
          content.style.display = 'none'
        }
      }
    })

    // Hide reject button if already rejected (can only accept now)
    if (status === 'rejected') {
      document.querySelectorAll('[data-consent-reject-btn]').forEach(btn => {
        btn.style.display = 'none'
      })
    }
  }

  /**
   * Initialize consent handling
   */
  function init() {
    const status = getConsentStatus()
    applyConsentState(status)

    // Attach click handlers to consent buttons
    document.querySelectorAll('[data-consent-accept]').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault()
        setConsentStatus('accepted')
      })
    })

    document.querySelectorAll('[data-consent-reject]').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault()
        setConsentStatus('rejected')
      })
    })
  }

  // Apply immediately for elements already in DOM
  applyConsentState(getConsentStatus())

  // Re-apply when DOM is ready for dynamically added elements
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init)
  } else {
    init()
  }

  // Expose for external use if needed
  window.cookieConsent = {
    hasConsent,
    hasRejected,
    getStatus: getConsentStatus,
    setStatus: setConsentStatus
  }
})()
