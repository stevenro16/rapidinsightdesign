import Alpine from 'alpinejs';
import persist from '@alpinejs/persist';

Alpine.plugin(persist);

/* ─── Login modal ───────────────────────────────────────────────────────── */
Alpine.data('loginModal', () => ({
    open: false,
    email: Alpine.$persist('').as('rid_email'),
    password: '',
    remember: false,
    loading: false,

    show() { this.open = true; this.$nextTick(() => this.$refs.emailInput?.focus()); },
    hide() { this.open = false; this.password = ''; },

    submit() {
        this.loading = true;
        this.$refs.loginForm.submit();
    },
}));

/* ─── Portal shell ──────────────────────────────────────────────────────── */
Alpine.data('portalShell', () => ({
    sidebarOpen: Alpine.$persist(true).as('rid_sidebar'),

    toggleSidebar() { this.sidebarOpen = !this.sidebarOpen; },

    isActive(path) {
        return window.location.pathname.startsWith(path);
    },
}));

/* ─── Scroll reveal ─────────────────────────────────────────────────────── */
Alpine.data('scrollReveal', (delay = 0) => ({
    visible: false,

    init() {
        const obs = new IntersectionObserver(([entry]) => {
            if (entry.isIntersecting) {
                setTimeout(() => { this.visible = true; }, delay);
                obs.disconnect();
            }
        }, { threshold: 0.15 });
        obs.observe(this.$el);
    },
}));

/* ─── Iframe embed (showroom) ───────────────────────────────────────────── */
Alpine.data('iframeEmbed', (url) => ({
    loaded: false,
    url,

    onLoad() { this.loaded = true; },
}));

/* ─── Confirm delete ────────────────────────────────────────────────────── */
Alpine.data('confirmDelete', (message = 'Are you sure you want to delete this?') => ({
    confirm(formEl) {
        if (window.confirm(message)) formEl.submit();
    },
}));

/* ─── Flash dismiss ─────────────────────────────────────────────────────── */
Alpine.data('flash', () => ({
    show: true,
    init() { setTimeout(() => { this.show = false; }, 5000); },
}));

/* ─── Tabs ──────────────────────────────────────────────────────────────── */
Alpine.data('tabs', (defaultTab) => ({
    active: defaultTab,
    setTab(tab) { this.active = tab; },
    isActive(tab) { return this.active === tab; },
}));

window.Alpine = Alpine;
Alpine.start();
