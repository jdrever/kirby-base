<?php
/**
 * Cookie consent JavaScript - handles consent state client-side via document.cookie.
 * Uses cookies for reliable storage across all browser contexts including private browsing.
 * Include this script in the page head or early in body for immediate hydration.
 */
?>
<script>
;(function() {
    const COOKIE_NAME = 'cookieConsent'
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
     * Get consent status from cookie
     * @returns {'accepted'|'rejected'|null}
     */
    function getConsentStatus() {
        return getCookie(COOKIE_NAME)
    }

    /**
     * Set consent status in cookie
     * @param {'accepted'|'rejected'} status
     */
    function setConsentStatus(status) {
        setCookie(COOKIE_NAME, status, COOKIE_DAYS)
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
     * @param {string|null} status
     */
    function applyConsentState(status) {
        // Hide main consent banner if any choice has been made
        if (status === 'accepted' || status === 'rejected') {
            document.querySelectorAll('[data-cookie-consent-banner]').forEach(el => {
                el.style.display = 'none'
            })
        }

        // Handle consent-required content blocks
        document.querySelectorAll('[data-requires-consent]').forEach(container => {
            const placeholder = container.querySelector('[data-consent-placeholder]')
            const content = container.querySelector('[data-consent-content]')

            if (placeholder && content) {
                if (status === 'accepted') {
                    placeholder.style.display = 'none'
                    content.style.display = ''
                } else {
                    placeholder.style.display = ''
                    content.style.display = 'none'
                }
            }
        })

        // Update reject button visibility in inline consent forms
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
</script>
