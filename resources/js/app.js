import Alpine from 'alpinejs';
import persist from '@alpinejs/persist';

Alpine.plugin(persist);

/* ─── Login + Register modals ───────────────────────────────────────────── */
Alpine.data('loginModal', () => ({
    open: false,
    registerOpen: false,
    email: Alpine.$persist('').as('rid_email'),
    password: '',
    remember: false,
    loading: false,
    registerLoading: false,

    show() { this.registerOpen = false; this.open = true; this.$nextTick(() => this.$refs.emailInput?.focus()); },
    hide() { this.open = false; this.password = ''; },

    showRegister() { this.open = false; this.registerOpen = true; this.$nextTick(() => this.$refs.registerEmail?.focus()); },
    hideRegister() { this.registerOpen = false; },

    submit() {
        this.loading = true;
        this.$refs.loginForm.submit();
    },

    registerSubmit() {
        this.registerLoading = true;
        this.$refs.registerForm.submit();
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

/* ─── Agreement review: scroll-gate + signature (drawn or typed) ────────── */
Alpine.data('agreementReview', (defaultName = '') => ({
    reachedBottom: false,
    agreed: false,
    method: 'drawn',     // 'drawn' | 'typed'
    name: defaultName,   // legal name (prefilled from the customer's account)
    chosenFont: '',      // cursive family for typed signatures
    // canvas state
    ctx: null,
    drawing: false,
    hasInk: false,
    last: { x: 0, y: 0 },

    init() {
        const canvas = this.$refs.canvas;
        if (canvas) {
            this.ctx = canvas.getContext('2d');
            this.ctx.lineWidth = 2.5;
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';
            this.ctx.strokeStyle = '#111827';
        }
        // Short documents that don't scroll should still enable signing.
        this.$nextTick(() => this.checkBottom(this.$refs.doc));
    },

    onScroll(e) { this.checkBottom(e.target); },
    checkBottom(el) {
        if (!el) return;
        if (el.scrollHeight - el.scrollTop - el.clientHeight < 8) this.reachedBottom = true;
    },

    // pointer → canvas-buffer coordinates (buffer is fixed 500x180, CSS-scaled)
    pos(e) {
        const canvas = this.$refs.canvas;
        const rect = canvas.getBoundingClientRect();
        const t = (e.touches && e.touches[0]) ? e.touches[0] : e;
        return {
            x: (t.clientX - rect.left) * (canvas.width / rect.width),
            y: (t.clientY - rect.top) * (canvas.height / rect.height),
        };
    },
    startDraw(e) { e.preventDefault(); this.drawing = true; this.last = this.pos(e); },
    draw(e) {
        if (!this.drawing) return;
        e.preventDefault();
        const p = this.pos(e);
        this.ctx.beginPath();
        this.ctx.moveTo(this.last.x, this.last.y);
        this.ctx.lineTo(p.x, p.y);
        this.ctx.stroke();
        this.last = p;
        this.hasInk = true;
    },
    stopDraw() { this.drawing = false; },
    clearPad() {
        const c = this.$refs.canvas;
        this.ctx.clearRect(0, 0, c.width, c.height);
        this.hasInk = false;
    },

    signatureReady() {
        // Drawn signatures don't need a typed name; the typed style does.
        return this.method === 'drawn'
            ? this.hasInk
            : (this.name.trim().length > 0 && !!this.chosenFont);
    },
    canSign() { return this.reachedBottom && this.agreed && this.signatureReady(); },

    submitSignature() {
        if (!this.canSign()) return;
        this.$refs.fMethod.value = this.method;
        this.$refs.fFont.value = this.method === 'typed' ? this.chosenFont : '';
        this.$refs.fData.value = this.method === 'drawn'
            ? this.$refs.canvas.toDataURL('image/png')
            : this.name;
        this.$refs.signForm.submit();
    },
}));

/* ─── Prospects map (admin) — Leaflet lazy-loaded inside init() ─────────── */
import prospectsMap from './prospectsMap';
Alpine.data('prospectsMap', prospectsMap);

/* ─── Hero "energy orb" — tiny custom WebGL shader (homepage only) ───────── */
import heroOrb from './heroOrb';
Alpine.data('heroOrb', heroOrb);

window.Alpine = Alpine;
Alpine.start();
