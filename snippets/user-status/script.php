<?php
/**
 * User status JavaScript - fetches login state and basket count via API.
 * Handles hydration of [data-user-logged-in] and [data-basket-count] elements.
 * Include this script once in the page (typically in header).
 */
?>
<script>
;(function() {
    let statusFetched = false;

    /**
     * Fetch user status and update UI elements
     */
    function fetchUserStatus() {
        if (statusFetched) return;
        statusFetched = true;

        fetch('/user-status', { credentials: 'include' })
            .then(response => response.json())
            .then(data => {
                // Update basket count
                const count = data.basketCount || 0;
                const text = count + ' item' + (count !== 1 ? 's' : '');
                document.querySelectorAll('[data-basket-count]').forEach(el => {
                    el.textContent = text;
                });

                // Show/hide logged-in elements
                if (data.isLoggedIn) {
                    document.querySelectorAll('[data-user-logged-in]').forEach(el => {
                        el.style.display = '';
                    });
                }
            })
            .catch(err => {
                console.error('User status fetch error:', err);
                // Set fallback for basket count
                document.querySelectorAll('[data-basket-count]').forEach(el => {
                    el.textContent = '0 items';
                });
            });
    }

    // Fetch when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fetchUserStatus);
    } else {
        fetchUserStatus();
    }

    // Expose for external use
    window.refreshUserStatus = function() {
        statusFetched = false;
        fetchUserStatus();
    };

    // Backwards compatibility alias
    window.updateBasketCount = window.refreshUserStatus;
})();
</script>
