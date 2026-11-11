import Alpine from 'alpinejs';

window.Alpine = Alpine;

const THEME_KEY = 'theme';

function resolveTheme() {
    // Dark is permanent default. Only an explicit saved "light" overrides it.
    return localStorage.getItem(THEME_KEY) === 'light' ? 'light' : 'dark';
}

function applyTheme(theme, persist = true) {
    const normalized = theme === 'light' ? 'light' : 'dark';

    document.documentElement.classList.toggle('dark', normalized === 'dark');
    document.documentElement.dataset.theme = normalized;
    document.documentElement.style.colorScheme = normalized;

    if (persist) {
        localStorage.setItem(THEME_KEY, normalized);
    }

    window.dispatchEvent(new CustomEvent('badig-theme-changed', {
        detail: { theme: normalized },
    }));

    return normalized;
}

// Re-assert the pre-paint theme immediately when the module is evaluated.
applyTheme(resolveTheme(), false);

window.BADIGTheme = {
    get: resolveTheme,
    set: (theme) => applyTheme(theme, true),
    toggle: () => applyTheme(resolveTheme() === 'dark' ? 'light' : 'dark', true),
};

Alpine.data('dashboardShell', () => ({
    mobileOpen: false,
    sidebarMini: false,

    init() {
        this.sidebarMini = window.innerWidth >= 1024 && localStorage.getItem('sidebar-mini') === 'true';

        this.$watch('sidebarMini', (value) => {
            localStorage.setItem('sidebar-mini', value ? 'true' : 'false');
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                this.mobileOpen = false;
            } else {
                this.sidebarMini = false;
            }
        });
    },

    toggleSidebar() {
        if (window.innerWidth >= 1024) {
            this.sidebarMini = !this.sidebarMini;
            return;
        }

        this.mobileOpen = !this.mobileOpen;
    },
}));

Alpine.data('themeSwitcher', () => ({
    theme: resolveTheme(),

    init() {
        this.theme = applyTheme(resolveTheme(), false);

        window.addEventListener('storage', (event) => {
            if (event.key === THEME_KEY) {
                this.theme = applyTheme(event.newValue === 'light' ? 'light' : 'dark', false);
            }
        });
    },

    toggle() {
        this.theme = applyTheme(this.theme === 'dark' ? 'light' : 'dark', true);
    },
}));

// Live search: every GET filter/search form auto-submits while the user types.
// A global delegated handler is used so all current and future search bars work
// without per-form wiring. Opt a form out with the `data-no-live-search` attribute.
const LIVE_SEARCH_DELAY = 500;
const LIVE_SEARCH_FOCUS_KEY = 'badig-live-search-focus';

function isLiveSearchField(target) {
    if (!target || !target.form) {
        return false;
    }

    if (!['INPUT', 'SELECT', 'TEXTAREA'].includes(target.tagName)) {
        return false;
    }

    const excluded = ['file', 'submit', 'button', 'hidden', 'checkbox', 'radio', 'image', 'reset', 'range', 'color'];

    return !excluded.includes(target.type);
}

function scheduleLiveSearch(target) {
    const form = target.form;

    if (!form || (form.getAttribute('method') || 'get').toLowerCase() !== 'get' || form.hasAttribute('data-no-live-search')) {
        return;
    }

    window.clearTimeout(form.__badigLiveSearchTimer);
    form.__badigLiveSearchTimer = window.setTimeout(() => {
        try {
            sessionStorage.setItem(LIVE_SEARCH_FOCUS_KEY, target.name || '');
        } catch (error) {
            // Ignore storage errors.
        }

        try {
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();

                return;
            }
        } catch (error) {
            // Fall back to a plain submit below.
        }

        form.submit();
    }, Number(form.dataset.liveSearchDelay || LIVE_SEARCH_DELAY));
}

document.addEventListener('input', (event) => {
    if (isLiveSearchField(event.target)) {
        scheduleLiveSearch(event.target);
    }
});

document.addEventListener('change', (event) => {
    const target = event.target;

    if (target && target.tagName === 'SELECT' && isLiveSearchField(target)) {
        scheduleLiveSearch(target);
    }
});

// Restore focus to the search field after a live-search reload.
document.addEventListener('DOMContentLoaded', () => {
    let field = null;

    try {
        field = sessionStorage.getItem(LIVE_SEARCH_FOCUS_KEY);
    } catch (error) {
        field = null;
    }

    if (!field) {
        return;
    }

    try {
        sessionStorage.removeItem(LIVE_SEARCH_FOCUS_KEY);
    } catch (error) {
        // Ignore storage errors.
    }

    const escaped = field.replace(/\\/g, '\\\\').replace(/"/g, '\\"');
    const candidate = Array.from(document.querySelectorAll(`[name="${escaped}"]`)).find((element) => {
        const form = element.form;

        return form && (form.getAttribute('method') || 'get').toLowerCase() === 'get';
    });

    if (candidate && document.activeElement === document.body) {
        candidate.focus();

        if (typeof candidate.setSelectionRange === 'function' && (candidate.type === 'text' || candidate.type === 'search')) {
            candidate.setSelectionRange(candidate.value.length, candidate.value.length);
        }
    }
});

Alpine.start();
