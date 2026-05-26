import Alpine from 'alpinejs';

window.Alpine = Alpine;

// ── Frontend error boundary ───────────────────────────────────────────────────
// Catches unhandled JS errors and uncaught promise rejections so the user sees a
// friendly fallback instead of a silently broken page.

function showErrorOverlay() {
    try {
        if (document.getElementById('__crash-overlay')) return;

        const el = document.createElement('div');
        el.id = '__crash-overlay';
        el.style.cssText = [
            'position:fixed', 'inset:0', 'z-index:99999',
            'display:flex', 'flex-direction:column',
            'align-items:center', 'justify-content:center',
            'background:#efebe9', 'padding:2rem',
            "font-family:Roboto,system-ui,sans-serif",
        ].join(';');

        el.innerHTML = `
            <div style="background:#fff;border-radius:16px;padding:3rem 2.5rem;max-width:440px;width:100%;text-align:center;box-shadow:0 4px 24px rgba(0,0,0,.1);border:1px solid #e2ddd8;">
                <div style="width:60px;height:60px;border-radius:50%;background:#0a3255;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;">
                    <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                </div>
                <h1 style="font-family:Oswald,system-ui,sans-serif;font-size:1.4rem;font-weight:700;color:#0a3255;text-transform:uppercase;letter-spacing:.06em;margin:0 0 .75rem;">Something Went Wrong</h1>
                <p style="color:#6b7280;font-size:.95rem;line-height:1.7;margin:0 0 2rem;">An unexpected error occurred. Your session and data are safe — please reload the page to continue.</p>
                <button onclick="location.reload()" style="background:#B41100;color:#fff;border:none;padding:.7rem 2.5rem;border-radius:8px;font-family:Oswald,system-ui,sans-serif;font-weight:700;font-size:.9rem;letter-spacing:.08em;text-transform:uppercase;cursor:pointer;">
                    Reload Page
                </button>
            </div>
        `;

        document.body?.appendChild(el);
    } catch {
        // If DOM manipulation itself fails there is nothing more we can do.
    }
}

// Synchronous JS errors (script errors only; resource load failures have no event.message).
window.addEventListener('error', (event) => {
    if (event.message) showErrorOverlay();
});

// Uncaught promise rejections (e.g. a fetch that was never awaited and throws).
window.addEventListener('unhandledrejection', () => showErrorOverlay());

Alpine.start();
