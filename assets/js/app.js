// assets/js/app.js — Global init, AJAX helpers, toast

document.addEventListener('DOMContentLoaded', () => {
    // Auto-hide flash messages
    const flash = document.getElementById('flash-msg');
    if (flash) {
        setTimeout(() => {
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 300);
        }, 4000);
    }

    // Initialize any global tooltips or dropdowns
    initDropdowns();
    initConfirmLinks();
});

/**
 * AJAX helper using fetch
 */
function apiRequest(url, options = {}) {
    const defaults = {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    };

    // Auto-include CSRF token for state-changing methods
    if (options.method && options.method !== 'GET') {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (csrfToken) {
            defaults.headers['X-CSRF-Token'] = csrfToken;
        }
        if (!(options.body instanceof FormData)) {
            defaults.headers['Content-Type'] = 'application/json';
        }
    }

    return fetch(url, { ...defaults, ...options })
        .then(r => {
            if (!r.ok) throw new Error(`HTTP ${r.status}: ${r.statusText}`);
            return r.json();
        });
}

/**
 * Show toast notification
 */
function showToast(message, type = 'success') {
    const existing = document.querySelector('.toast-notification');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed; top: 1rem; right: 1rem;
        padding: 1rem 1.5rem; border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999; font-weight: 500;
        animation: slideIn 0.3s ease;
        ${type === 'success' ? 'background:#d1fae5;color:#065f46;' : 'background:#fee2e2;color:#991b1b;'}
    `;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

function initDropdowns() {
    document.querySelectorAll('[data-dropdown]').forEach(el => {
        el.addEventListener('click', (e) => {
            const menu = el.nextElementSibling;
            if (menu) menu.classList.toggle('show');
        });
    });
    document.addEventListener('click', (e) => {
        if (!e.target.closest('[data-dropdown]')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
        }
    });
}

function initConfirmLinks() {
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', (e) => {
            if (!confirm(el.dataset.confirm)) e.preventDefault();
        });
    });
}
