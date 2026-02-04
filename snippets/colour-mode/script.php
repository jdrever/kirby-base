<script>
    ;(function () {
        const COOKIE_NAME = 'colourMode'
        const COOKIE_DAYS = 365
        const htmlElement = document.documentElement

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
         * Get the stored colour mode preference, defaulting to 'auto'
         * @returns {string}
         */
        function getStoredMode() {
            return getCookie(COOKIE_NAME) || 'auto'
        }

        /**
         * Apply the theme to the document
         * @param {string} mode - 'light', 'dark', or 'auto'
         */
        function applyTheme(mode) {
            if (mode === 'auto') {
                const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
                htmlElement.setAttribute('data-bs-theme', systemPrefersDark ? 'dark' : 'light')
            } else {
                htmlElement.setAttribute('data-bs-theme', mode)
            }
        }

        /**
         * Update the selector UI to highlight the active mode
         * @param {string} mode
         */
        function updateSelectorUI(mode) {
            document.querySelectorAll('.colour-mode-btn').forEach(btn => {
                const isActive = btn.dataset.colourMode === mode
                btn.style.fontWeight = isActive ? 'bold' : 'normal'
                btn.disabled = isActive
            })
        }

        /**
         * Set the colour mode preference
         * @param {string} mode
         */
        function setColourMode(mode) {
            setCookie(COOKIE_NAME, mode, COOKIE_DAYS)
            applyTheme(mode)
            updateSelectorUI(mode)
        }

        // Apply theme immediately on load
        const storedMode = getStoredMode()
        applyTheme(storedMode)

        // Listen for system preference changes when in auto mode
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            if (getStoredMode() === 'auto') {
                applyTheme('auto')
            }
        })

        // Hydrate the selector when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init)
        } else {
            init()
        }

        function init() {
            updateSelectorUI(storedMode)

            // Attach click handlers to selector buttons
            document.querySelectorAll('.colour-mode-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    setColourMode(btn.dataset.colourMode)
                })
            })
        }
    })()
</script>