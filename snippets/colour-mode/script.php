<script>
    ;(function () {
        const STORAGE_KEY = 'colourMode'
        const htmlElement = document.documentElement

        /**
         * Get the stored colour mode preference, defaulting to 'auto'
         * @returns {string}
         */
        function getStoredMode() {
            return localStorage.getItem(STORAGE_KEY) || 'auto'
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
            localStorage.setItem(STORAGE_KEY, mode)
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