// Admin Prospects map — Leaflet is dynamically imported so public pages never load it.
export default (config) => ({
    L: null,
    map: null,
    prospects: [],
    areas: [],
    markers: new Map(),       // prospect id -> L.Marker
    areaLayers: [],
    categories: [],

    selected: null,           // full prospect object (with notes once loaded)
    loadingDetail: false,
    noteBody: '',
    savingNote: false,
    scanning: false,

    filters: {
        // Default to only "new" prospects; a ?status= deep-link overrides this in init().
        statuses: ['new'],
        category: '',
        band: '',
        q: '',
    },
    listLimit: 200,

    searchMode: false,
    pendingCenter: null,      // {lat, lng}
    radiusMiles: 3,
    previewCircle: null,
    searching: false,
    searchingMessage: '',
    searchingTimer: null,

    toast: null,              // {type: 'success'|'error', text}
    toastTimer: null,

    statusLabels: {
        new: 'New',
        shortlisted: 'Shortlisted',
        contacted: 'Contacted',
        ruled_out: 'Ruled Out',
        won: 'Won',
    },

    async init() {
        // Deep-link a status filter, e.g. /admin/prospects?status=shortlisted
        const requested = new URLSearchParams(window.location.search).get('status');
        const wanted = (requested ? requested.split(',') : [])
            .map(s => s.trim())
            .filter(s => config.statuses.includes(s));
        if (wanted.length) this.filters.statuses = wanted;

        const leaflet = await import('leaflet');
        await import('leaflet/dist/leaflet.css');
        this.L = leaflet.default ?? leaflet;

        this.map = this.L.map(this.$refs.map, { zoomControl: false })
            .setView(config.center, 13);

        this.L.control.zoom({ position: 'bottomright' }).addTo(this.map);

        this.L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
            maxZoom: 20,
        }).addTo(this.map);

        this.map.on('click', (e) => this.onMapClick(e));

        // Sidebar collapse animates the container width — keep tiles in sync
        new ResizeObserver(() => this.map && this.map.invalidateSize()).observe(this.$refs.map);

        this.$watch('radiusMiles', () => this.drawPreviewCircle());
        this.$watch('filters', () => this.applyFilters());

        await this.refresh();
    },

    /* ── Data loading ──────────────────────────────────────────── */

    async refresh() {
        const data = await this.api('GET', '/admin/prospects/data');
        this.prospects = data.prospects;
        this.areas = data.areas;
        this.categories = [...new Set(this.prospects.map(p => p.category).filter(Boolean))].sort();
        this.rebuildMarkers();
        this.rebuildAreas();
    },

    rebuildMarkers() {
        this.markers.forEach(m => m.remove());
        this.markers.clear();
        this.visible().forEach(p => this.addMarker(p));
    },

    rebuildAreas() {
        this.areaLayers.forEach(l => l.remove());
        this.areaLayers = this.areas.map(a =>
            this.L.circle([a.lat, a.lng], {
                radius: a.radius_m,
                color: 'rgba(109,190,46,0.3)',
                weight: 1,
                fillColor: '#6DBE2E',
                fillOpacity: 0.04,
                interactive: false,
            }).addTo(this.map)
        );
    },

    addMarker(p) {
        const marker = this.L.marker([p.lat, p.lng], {
            icon: this.makeIcon(p),
            riseOnHover: true,
        }).addTo(this.map);
        marker.on('click', () => this.select(p.id));
        this.markers.set(p.id, marker);
    },

    makeIcon(p) {
        const sel = this.selected?.id === p.id ? ' pin-selected' : '';
        return this.L.divIcon({
            className: '',
            html: `<div class="prospect-pin pin-${p.status}${sel}" title="${this.escapeHtml(p.name)}"></div>`,
            iconSize: [22, 22],
            iconAnchor: [11, 11],
        });
    },

    /* ── Filtering ─────────────────────────────────────────────── */

    visible() {
        const q = this.filters.q.trim().toLowerCase();
        return this.prospects.filter(p => {
            if (!this.filters.statuses.includes(p.status)) return false;
            if (this.filters.category && p.category !== this.filters.category) return false;
            if (this.filters.band && this.band(p) !== this.filters.band) return false;
            if (q && !p.name.toLowerCase().includes(q)) return false;
            return true;
        }).sort((a, b) => a.presence_score - b.presence_score || a.name.localeCompare(b.name));
    },

    listItems() {
        return this.visible().slice(0, this.listLimit);
    },

    applyFilters() {
        this.listLimit = 200;
        this.rebuildMarkers();
    },

    toggleStatusFilter(status) {
        const i = this.filters.statuses.indexOf(status);
        if (i === -1) this.filters.statuses.push(status);
        else if (this.filters.statuses.length > 1) this.filters.statuses.splice(i, 1);
    },

    band(p) {
        return p.presence_score < 30 ? 'low' : p.presence_score < 60 ? 'medium' : 'high';
    },

    bandLabel(p) {
        return { low: 'Low', medium: 'Medium', high: 'High' }[this.band(p)];
    },

    exportUrl() {
        const params = new URLSearchParams();
        if (this.filters.statuses.length < config.statuses.length) params.set('status', this.filters.statuses.join(','));
        if (this.filters.category) params.set('category', this.filters.category);
        if (this.filters.band) params.set('band', this.filters.band);
        if (this.filters.q.trim()) params.set('q', this.filters.q.trim());
        const qs = params.toString();
        return '/admin/prospects/export' + (qs ? `?${qs}` : '');
    },

    /* ── Selection / detail drawer ─────────────────────────────── */

    async select(id) {
        const prev = this.selected?.id;
        this.selected = this.prospects.find(p => p.id === id) ?? null;
        if (!this.selected) return;

        // refresh pin highlight rings
        [prev, id].forEach(pid => {
            const p = this.prospects.find(x => x.id === pid);
            const m = pid && this.markers.get(pid);
            if (p && m) m.setIcon(this.makeIcon(p));
        });

        this.map.panTo([this.selected.lat, this.selected.lng]);

        this.loadingDetail = true;
        try {
            const full = await this.api('GET', `/admin/prospects/${id}`);
            if (this.selected?.id === id) this.selected = { ...this.selected, ...full };
        } finally {
            this.loadingDetail = false;
        }

        // Auto-scan the website the first time a prospect is opened
        if (this.selected?.id === id && this.selected.website && !this.selected.scanned_at) {
            this.scanWebsite();
        }
    },

    async scanWebsite() {
        if (!this.selected?.website || this.scanning) return;
        const id = this.selected.id;
        this.scanning = true;
        try {
            const result = await this.api('POST', `/admin/prospects/${id}/scan`);
            if (this.selected?.id === id) {
                this.selected.scan_data = result.scan_data;
                this.selected.scanned_at = result.scanned_at;
            }
            // keep the in-memory list copy in sync so re-opening doesn't rescan
            const listed = this.prospects.find(p => p.id === id);
            if (listed) {
                listed.scan_data = result.scan_data;
                listed.scanned_at = result.scanned_at;
            }
        } catch (err) {
            if (this.selected?.id === id) this.showToast('error', err.userMessage ?? 'Website scan failed.');
        } finally {
            this.scanning = false;
        }
    },

    scanHasResults(scan) {
        if (!scan) return false;
        return (scan.emails?.length || scan.phones?.length || scan.names?.length
            || (scan.social && Object.keys(scan.social).length)) > 0;
    },

    closeDetail() {
        const prev = this.selected?.id;
        this.selected = null;
        const p = this.prospects.find(x => x.id === prev);
        const m = prev && this.markers.get(prev);
        if (p && m) m.setIcon(this.makeIcon(p));
    },

    async setStatus(status) {
        if (!this.selected || this.selected.status === status) return;
        const id = this.selected.id;

        // optimistic update
        this.selected.status = status;
        const listed = this.prospects.find(p => p.id === id);
        if (listed) listed.status = status;
        const m = this.markers.get(id);
        if (m && listed) m.setIcon(this.makeIcon(listed));

        try {
            await this.api('PATCH', `/admin/prospects/${id}`, { status });
        } catch {
            this.showToast('error', 'Could not save status — try again.');
            await this.refresh();
        }
    },

    async addNote() {
        const body = this.noteBody.trim();
        if (!body || !this.selected || this.savingNote) return;
        this.savingNote = true;
        try {
            const note = await this.api('POST', `/admin/prospects/${this.selected.id}/notes`, { body });
            this.selected.notes = [note, ...(this.selected.notes ?? [])];
            const listed = this.prospects.find(p => p.id === this.selected.id);
            if (listed) listed.notes_count = (listed.notes_count ?? 0) + 1;
            this.noteBody = '';
        } catch {
            this.showToast('error', 'Could not save note — try again.');
        } finally {
            this.savingNote = false;
        }
    },

    async removeProspect() {
        if (!this.selected) return;
        if (!window.confirm(`Delete "${this.selected.name}" and its notes?`)) return;
        const id = this.selected.id;
        try {
            await this.api('DELETE', `/admin/prospects/${id}`);
            this.selected = null;
            this.prospects = this.prospects.filter(p => p.id !== id);
            this.markers.get(id)?.remove();
            this.markers.delete(id);
        } catch {
            this.showToast('error', 'Could not delete — try again.');
        }
    },

    /* ── Search mode ───────────────────────────────────────────── */

    enterSearchMode() {
        this.searchMode = true;
        this.pendingCenter = null;
        this.closeDetail();
    },

    cancelSearch() {
        this.searchMode = false;
        this.pendingCenter = null;
        this.removePreviewCircle();
    },

    onMapClick(e) {
        if (!this.searchMode || this.searching) return;
        this.pendingCenter = { lat: e.latlng.lat, lng: e.latlng.lng };
        this.drawPreviewCircle();
    },

    drawPreviewCircle() {
        if (!this.pendingCenter) return;
        this.removePreviewCircle();
        this.previewCircle = this.L.circle([this.pendingCenter.lat, this.pendingCenter.lng], {
            radius: this.radiusMiles * 1609.34,
            color: '#6DBE2E',
            weight: 2,
            dashArray: '8 6',
            fillColor: '#6DBE2E',
            fillOpacity: 0.08,
            interactive: false,
        }).addTo(this.map);
    },

    removePreviewCircle() {
        this.previewCircle?.remove();
        this.previewCircle = null;
    },

    async runSearch() {
        if (!this.pendingCenter || this.searching) return;
        this.searching = true;
        this.startSearchMessages();

        try {
            const result = await this.api('POST', '/admin/prospects/search', {
                lat: this.pendingCenter.lat,
                lng: this.pendingCenter.lng,
                radius_m: Math.round(this.radiusMiles * 1609.34),
            });
            this.showToast('success', `Imported ${result.imported} new business${result.imported === 1 ? '' : 'es'} (${result.updated} refreshed).`);
            this.cancelSearch();
            await this.refresh();
        } catch (err) {
            this.showToast('error', err.userMessage ?? 'Search failed — Overpass may be busy. Try again in a minute or shrink the radius.');
        } finally {
            this.searching = false;
            this.stopSearchMessages();
        }
    },

    startSearchMessages() {
        const messages = [
            'Querying OpenStreetMap… this can take up to 30 seconds.',
            'Scanning for independent businesses…',
            'Filtering out chains and franchises…',
            'Scoring online presence…',
        ];
        let i = 0;
        this.searchingMessage = messages[0];
        this.searchingTimer = setInterval(() => {
            i = (i + 1) % messages.length;
            this.searchingMessage = messages[i];
        }, 6000);
    },

    stopSearchMessages() {
        clearInterval(this.searchingTimer);
        this.searchingTimer = null;
    },

    /* ── Helpers ───────────────────────────────────────────────── */

    async api(method, url, body = null) {
        const response = await fetch(url, {
            method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: body ? JSON.stringify(body) : null,
        });

        if (response.status === 419) {
            this.showToast('error', 'Session expired — reload the page.');
            throw new Error('CSRF expired');
        }

        if (!response.ok) {
            const error = new Error(`HTTP ${response.status}`);
            try {
                error.userMessage = (await response.json()).message;
            } catch { /* non-JSON error body */ }
            throw error;
        }

        return response.json();
    },

    showToast(type, text) {
        clearTimeout(this.toastTimer);
        this.toast = { type, text };
        this.toastTimer = setTimeout(() => { this.toast = null; }, 6000);
    },

    escapeHtml(str) {
        return String(str).replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        }[c]));
    },

    formatDate(iso) {
        return new Date(iso).toLocaleString(undefined, {
            month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit',
        });
    },
});
