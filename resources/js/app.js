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

/* ─── Sortable grid (admin drag-and-drop reorder) ───────────────────────── */
import Sortable from 'sortablejs';
Alpine.data('sortable', (reorderUrl) => ({
    saving: false,

    init() {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        Sortable.create(this.$el, {
            handle: '.drag-handle',
            draggable: '[data-sortable-item]',
            animation: 180,
            ghostClass: 'opacity-40',
            onEnd: () => {
                const order = [...this.$el.querySelectorAll('[data-sortable-item]')]
                    .map((el) => el.dataset.id);
                this.saving = true;
                fetch(reorderUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ order }),
                })
                    .catch(() => {})
                    .finally(() => { this.saving = false; });
            },
        });
    },
}));

/* ─── Prospects map (admin) — Leaflet lazy-loaded inside init() ─────────── */
import prospectsMap from './prospectsMap';
Alpine.data('prospectsMap', prospectsMap);

window.Alpine = Alpine;
Alpine.start();
