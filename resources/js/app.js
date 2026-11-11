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

// Dismissible flash/validation alerts: hide themselves after a timeout and can
// be closed manually. Wired through the shared <x-ui.alert> component.
Alpine.data('badigAlert', (timeout = 5000) => ({
    show: true,
    timer: null,

    init() {
        const delay = Number(timeout);

        if (delay > 0) {
            this.timer = window.setTimeout(() => {
                this.show = false;
            }, delay);
        }
    },

    destroy() {
        window.clearTimeout(this.timer);
    },

    close() {
        window.clearTimeout(this.timer);
        this.show = false;
    },
}));

// Live search: every GET filter/search form refreshes its results in place with
// fetch, so the page never reloads while the user types. The form declares the
// region to refresh with `data-live-target="<css selector>"`; the fallback is the
// whole content area (`#badig-content`). Opt a form out with `data-no-live-search`.
const LIVE_SEARCH_DELAY = 800;
const LIVE_SEARCH_FALLBACK_TARGET = '#badig-content';

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

function liveSearchTarget(form) {
    return form.dataset.liveTarget || LIVE_SEARCH_FALLBACK_TARGET;
}

function scheduleLiveSearch(target) {
    const form = target.form;

    if (!form || (form.getAttribute('method') || 'get').toLowerCase() !== 'get' || form.hasAttribute('data-no-live-search')) {
        return;
    }

    window.clearTimeout(form.__badigLiveSearchTimer);
    form.__badigLiveSearchTimer = window.setTimeout(() => {
        runLiveSearch(form);
    }, Number(form.dataset.liveSearchDelay || LIVE_SEARCH_DELAY));
}

function buildLiveSearchUrl(form) {
    const url = new URL(form.getAttribute('action') || window.location.href, window.location.href);
    const params = new URLSearchParams();

    new FormData(form).forEach((value, key) => {
        if (typeof value === 'string' && value !== '') {
            params.append(key, value);
        }
    });

    url.search = params.toString();

    return url;
}

// Replace a live region with the same region from a freshly fetched page.
function swapLiveRegion(selector, url) {
    const current = document.querySelector(selector);

    if (!current) {
        window.location.assign(url.toString());

        return;
    }

    fetch(url.toString(), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'text/html',
        },
        credentials: 'same-origin',
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            return response.text();
        })
        .then((html) => {
            const next = new DOMParser().parseFromString(html, 'text/html').querySelector(selector);

            if (!next) {
                throw new Error('Live region tidak ditemukan pada respons.');
            }

            try {
                window.Alpine?.destroyTree?.(current);
            } catch (error) {
                // Ignore cleanup failures.
            }

            current.replaceWith(next);

            try {
                window.history.replaceState({}, '', url.toString());
            } catch (error) {
                // Ignore history failures.
            }

            try {
                window.Alpine?.initTree?.(next);
            } catch (error) {
                // Ignore re-init failures; the swapped markup is still visible.
            }

            document.dispatchEvent(new CustomEvent('badig:live-region', { detail: { region: next } }));
        })
        .catch(() => {
            // Fall back to a real navigation so the user still gets results.
            window.location.assign(url.toString());
        });
}

function runLiveSearch(form) {
    swapLiveRegion(liveSearchTarget(form), buildLiveSearchUrl(form));
}

document.addEventListener('input', (event) => {
    if (isLiveSearchField(event.target)) {
        scheduleLiveSearch(event.target);
    }
});

document.addEventListener('change', (event) => {
    const target = event.target;

    if (!target) {
        return;
    }

    // "Pilih semua" checkbox used by the bulk-export tables.
    if (target.id === 'selectAllRows') {
        const scope = target.closest('[data-live-region]') || document;

        scope.querySelectorAll('.rowCheckbox').forEach((checkbox) => {
            checkbox.checked = target.checked;
        });

        return;
    }

    if (target.tagName === 'SELECT' && isLiveSearchField(target)) {
        scheduleLiveSearch(target);
    }
});

// Submitting a filter/search form (the "Filter" button or pressing Enter) also
// refreshes in place instead of reloading the page.
document.addEventListener('submit', (event) => {
    const form = event.target;

    if (event.defaultPrevented || !form || (form.getAttribute('method') || 'get').toLowerCase() !== 'get' || form.hasAttribute('data-no-live-search')) {
        return;
    }

    window.clearTimeout(form.__badigLiveSearchTimer);
    event.preventDefault();
    runLiveSearch(form);
});

// Keep pagination inside a live region in-place as well.
document.addEventListener('click', (event) => {
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
        return;
    }

    const link = event.target.closest('a[href]');

    if (!link || link.target === '_blank' || link.hasAttribute('download')) {
        return;
    }

    const pagination = link.closest('nav[role="navigation"], .pagination');

    if (!pagination) {
        return;
    }

    const region = pagination.closest('[data-live-region]');

    if (!region || !region.id) {
        return;
    }

    let url;

    try {
        url = new URL(link.href, window.location.href);
    } catch (error) {
        return;
    }

    if (url.origin !== window.location.origin) {
        return;
    }

    event.preventDefault();
    swapLiveRegion('#' + region.id, url);
});

Alpine.start();
