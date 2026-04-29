/**
 * session.js — Hoki POS Session Guard
 * Runs after page load on every page (except index.html).
 * - Syncs logout across browser tabs via storage events
 * - Guards against session being cleared externally
 */

(function () {
    const LOGIN_PAGE = 'index.html';

    function isLoginPage() {
        return window.location.pathname.endsWith(LOGIN_PAGE) ||
               window.location.pathname.endsWith('/');
    }

    if (isLoginPage()) return;

    /* Redirect to login if session is gone */
    function guardSession() {
        const u = localStorage.getItem('currentUser');
        if (!u) window.location.href = LOGIN_PAGE;
    }

    /* Sync logout across tabs — if another tab clears currentUser, redirect here too */
    window.addEventListener('storage', function (e) {
        if (e.key === 'currentUser' && !e.newValue) {
            window.location.href = LOGIN_PAGE;
        }
    });

    /* Run guard once on load */
    guardSession();
})();
