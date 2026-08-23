import Alpine from 'alpinejs';
import { Map as MaplibreMap, Marker, NavigationControl, setWorkerUrl, setRTLTextPlugin } from 'maplibre-gl';
import maplibreWorkerUrl from 'maplibre-gl/dist/maplibre-gl-worker.mjs?worker&url';
import rtlTextPluginUrl from '../vendor/mapbox-gl-rtl-text.js?url';
import 'maplibre-gl/dist/maplibre-gl.css';

window.Alpine = Alpine;

setWorkerUrl(maplibreWorkerUrl);

// Arabic and Hebrew glyphs need this plugin to shape/join letters
// correctly — without it, labels render as disconnected characters.
setRTLTextPlugin(rtlTextPluginUrl, false);

const MAP_STYLE = 'https://tiles.openfreemap.org/styles/liberty';
const EGYPT_CENTER = [30.0, 26.8];
const EGYPT_ZOOM = 5.3;

// The base style's country/state labels default to "name:latin +
// name:nonlatin", which for some entities pulls in the wrong local script
// (e.g. Hebrew for the Palestinian territories instead of Arabic) or plain
// English. Since this app is Arabic-first, show the Arabic name everywhere,
// and use the term used in Egypt ("فلسطين") rather than OSM's literal
// "Palestinian Territories" translation.
const COUNTRY_LABEL_LAYERS = ['label_state', 'label_country_1', 'label_country_2', 'label_country_3'];
const ARABIC_LABEL_TEXT_FIELD = [
    'case',
    ['==', ['get', 'iso_a2'], 'PS'], 'فلسطين',
    ['has', 'name:ar'], ['get', 'name:ar'],
    ['coalesce', ['get', 'name_en'], ['get', 'name']],
];

// Must mirror App\Support\Moment (app/Support/Moment.php) exactly — both
// sides pack/compare the same "moment" integers.
const YEAR_OFFSET = 10_000;

function packMoment(year, month, day, hour, minute) {
    return (year + YEAR_OFFSET) * 100_000_000
        + (month ?? 1) * 1_000_000
        + (day ?? 1) * 10_000
        + (hour ?? 0) * 100
        + (minute ?? 0);
}

function unpackMoment(moment) {
    const year = Math.floor(moment / 100_000_000) - YEAR_OFFSET;
    let rest = moment % 100_000_000;
    const month = Math.floor(rest / 1_000_000);
    rest %= 1_000_000;
    const day = Math.floor(rest / 10_000);
    rest %= 10_000;
    const hour = Math.floor(rest / 100);
    const minute = rest % 100;

    return { year, month, day, hour, minute };
}

// Must mirror FramesRelationManager::ICONS keys (app/Filament/Resources/Events/RelationManagers/FramesRelationManager.php).
const ICONS = {
    position: '📍',
    troops: '🎖️',
    naval: '⚓',
    air: '✈️',
    battle: '⚔️',
    flag: '🚩',
};

Alpine.data('atlas', () => ({
    loading: true,
    error: null,
    eras: [],
    events: [],
    searchQuery: '',
    searchOpen: false,
    // The timeline only ever stops at real, already-computed moments
    // (era bounds, event starts/ends, frame times) instead of scrubbing
    // continuously — a continuous integer between two packed dates isn't
    // itself a valid date (months don't all have the same number of days),
    // so stepping through raw integers produces nonsense like "month 42".
    timelineMoments: [],
    momentIndex: 0,
    selectedEvent: null,
    map: null,
    markers: [],
    mapReady: false,

    async init() {
        try {
            const [erasRes, eventsRes] = await Promise.all([
                fetch('/api/eras').then((r) => r.json()),
                fetch('/api/events').then((r) => r.json()),
            ]);

            this.eras = erasRes.data;
            this.events = eventsRes.data;
            this.timelineMoments = this.buildTimelineMoments();
            this.momentIndex = 0;

            this.initMap();
        } catch (e) {
            this.error = 'تعذّر تحميل بيانات الخريطة. تأكد إن السيرفر شغال.';
            this.loading = false;
        }
    },

    buildTimelineMoments() {
        const moments = new Set();

        this.eras.forEach((era) => {
            moments.add(packMoment(era.start_year, 1, 1, 0, 0));
            moments.add(packMoment(era.end_year, 12, 31, 23, 59));
        });

        this.events.forEach((event) => {
            moments.add(event.start_moment);
            moments.add(event.end_moment);
            (event.frames ?? []).forEach((frame) => moments.add(frame.moment));
        });

        return Array.from(moments).sort((a, b) => a - b);
    },

    get moment() {
        return this.timelineMoments[this.momentIndex] ?? null;
    },

    // Eras can be centuries or millennia apart (the timeline only stops at
    // real moments, so it just jumps the gap), so "the current era" has to
    // be computed per moment rather than assumed to be a single fixed era.
    get currentEra() {
        if (this.moment === null) return null;

        const { year } = unpackMoment(this.moment);

        return this.eras.find((era) => era.start_year <= year && year <= era.end_year) ?? null;
    },

    stepTime(delta) {
        const next = this.momentIndex + delta;
        if (next < 0 || next >= this.timelineMoments.length) return;

        this.momentIndex = next;
        this.onTimeChange();
    },

    initMap() {
        this.map = new MaplibreMap({
            container: this.$refs.map,
            style: MAP_STYLE,
            center: EGYPT_CENTER,
            zoom: EGYPT_ZOOM,
        });

        this.map.addControl(new NavigationControl(), 'top-left');

        // Point markers are plain DOM overlays positioned via the map's
        // projection, so they don't need to wait on style/source loading.
        this.renderMarkers();

        // Polygon frames, the boundary layer, and label overrides need
        // addSource/addLayer/setLayoutProperty, which require a loaded
        // style. 'styledata' fires repeatedly while the style resolves,
        // so retry against it until it succeeds.
        const addContentWhenReady = () => {
            if (this.mapReady || !this.map.isStyleLoaded()) return;
            this.mapReady = true;
            this.initBoundaryLayer();
            this.arabizeCountryLabels();
            this.initPolygonLayer();
            this.initSideZoneLayer();
            this.renderMarkers();
        };

        this.map.on('load', addContentWhenReady);
        this.map.on('idle', addContentWhenReady);
        this.map.on('styledata', addContentWhenReady);

        // The spinner only needs to cover the time until something is
        // actually on screen — MapLibre fades tiles in progressively after
        // that, so it doesn't need to wait for every source to finish.
        this.map.once('render', () => {
            this.loading = false;
        });
        setTimeout(() => {
            this.loading = false;
        }, 3000);

        this.map.on('error', (e) => {
            console.error('[atlas] map error', e && e.error);
        });
    },

    initBoundaryLayer() {
        this.map.addSource('era-boundary', {
            type: 'geojson',
            data: { type: 'FeatureCollection', features: [] },
        });

        this.map.addLayer({
            id: 'era-boundary-fill',
            type: 'fill',
            source: 'era-boundary',
            paint: { 'fill-color': '#b45309', 'fill-opacity': 0.12 },
        });

        this.map.addLayer({
            id: 'era-boundary-line',
            type: 'line',
            source: 'era-boundary',
            paint: { 'line-color': '#b45309', 'line-width': 1.5 },
        });

        this.renderBoundary();
    },

    // Which era is "current" changes as the timeline moves, so the
    // boundary shown has to be re-synced on every time change, not drawn
    // once at map load.
    renderBoundary() {
        const source = this.map.getSource('era-boundary');
        if (!source) return;

        source.setData(this.currentEra?.boundary_geojson ?? { type: 'FeatureCollection', features: [] });
    },

    arabizeCountryLabels() {
        COUNTRY_LABEL_LAYERS.forEach((id) => {
            if (this.map.getLayer(id)) {
                this.map.setLayoutProperty(id, 'text-field', ARABIC_LABEL_TEXT_FIELD);
            }
        });
    },

    initPolygonLayer() {
        this.map.addSource('event-frames-polygons', {
            type: 'geojson',
            data: { type: 'FeatureCollection', features: [] },
        });

        this.map.addLayer({
            id: 'event-frames-polygons-fill',
            type: 'fill',
            source: 'event-frames-polygons',
            paint: { 'fill-color': '#dc2626', 'fill-opacity': 0.2 },
        });

        this.map.addLayer({
            id: 'event-frames-polygons-line',
            type: 'line',
            source: 'event-frames-polygons',
            paint: { 'line-color': '#dc2626', 'line-width': 1.5 },
        });

        this.map.on('click', 'event-frames-polygons-fill', (e) => {
            const eventId = e.features?.[0]?.properties?.eventId;
            const event = this.events.find((ev) => ev.id === eventId);

            if (event) this.selectEvent(event);
        });
    },

    // Control-zone polygons (side-tagged 'polygon' frames) rendered as a
    // colored area per side during playback — separate from the era
    // boundary / static event-polygon layers above.
    initSideZoneLayer() {
        this.map.addSource('atlas-side-zones', {
            type: 'geojson',
            data: { type: 'FeatureCollection', features: [] },
        });

        this.map.addLayer({
            id: 'atlas-side-zones-fill',
            type: 'fill',
            source: 'atlas-side-zones',
            paint: {
                'fill-color': ['match', ['get', 'side'], 'a', '#dc2626', 'b', '#2563eb', '#999999'],
                'fill-opacity': 0.22,
            },
        });

        this.map.addLayer({
            id: 'atlas-side-zones-line',
            type: 'line',
            source: 'atlas-side-zones',
            paint: {
                'line-color': ['match', ['get', 'side'], 'a', '#dc2626', 'b', '#2563eb', '#999999'],
                'line-width': 2,
            },
        });
    },

    // --- Two-sided animated troop-movement playback ---
    // Distinct from the discrete timeline: this animates smoothly (via
    // requestAnimationFrame) through an event's own side-tagged frames,
    // independent of the era/event timeline's step-by-step navigation.

    playbackActive: false,
    playbackRaf: null,
    playbackVirtualMoment: null,
    playbackDurationMs: 9000,
    playbackDisplay: { a: null, b: null, momentLabel: '' },
    sideMarkers: { a: null, b: null },

    framesForSide(event, side, geometryType = null) {
        return (event.frames ?? [])
            .filter((frame) => frame.side === side && (!geometryType || frame.geometry_type === geometryType))
            .sort((a, b) => a.moment - b.moment);
    },

    // The latest control-zone polygon whose moment has already arrived —
    // zones snap rather than morph, same reasoning as polygon frames
    // elsewhere (interpolating arbitrary polygon shapes isn't meaningful).
    currentZoneForSide(zoneFrames, moment) {
        if (zoneFrames.length === 0) return null;

        let current = zoneFrames[0];
        zoneFrames.forEach((frame) => {
            if (frame.moment <= moment) current = frame;
        });

        return current;
    },

    hasSideAnimation(event) {
        if (!event) return false;

        return this.framesForSide(event, 'a').length > 0 || this.framesForSide(event, 'b').length > 0;
    },

    frameCenter(frame) {
        return frame.geometry_type === 'point' ? frame.geometry.coordinates : this.polygonCentroid(frame.geometry);
    },

    /**
     * The state of one side at an arbitrary (possibly fractional) point in
     * time, linearly interpolated between the two frames it falls between.
     * Position only interpolates smoothly when both bracketing frames are
     * points — polygon shapes just snap, since morphing between arbitrary
     * polygons isn't meaningful.
     */
    interpolatedSideState(frames, moment) {
        if (frames.length === 0) return null;
        if (moment <= frames[0].moment) return { ...frames[0], coords: this.frameCenter(frames[0]) };

        const last = frames[frames.length - 1];
        if (moment >= last.moment) return { ...last, coords: this.frameCenter(last) };

        for (let i = 0; i < frames.length - 1; i++) {
            const prev = frames[i];
            const next = frames[i + 1];
            if (moment < prev.moment || moment > next.moment) continue;

            const t = next.moment === prev.moment ? 1 : (moment - prev.moment) / (next.moment - prev.moment);
            const lerp = (a, b) => a + (b - a) * t;
            const lerpMaybe = (a, b) => (a != null && b != null ? Math.round(lerp(a, b)) : a ?? b);

            const coords = prev.geometry_type === 'point' && next.geometry_type === 'point'
                ? [lerp(prev.geometry.coordinates[0], next.geometry.coordinates[0]), lerp(prev.geometry.coordinates[1], next.geometry.coordinates[1])]
                : this.frameCenter(t < 0.5 ? prev : next);

            return {
                coords,
                icon: prev.icon,
                troop_count: lerpMaybe(prev.troop_count, next.troop_count),
                casualties: lerpMaybe(prev.casualties, next.casualties),
                nearestRealMoment: t < 0.5 ? prev.moment : next.moment,
            };
        }

        return { ...last, coords: this.frameCenter(last) };
    },

    startPlayback(event) {
        const pointFramesA = this.framesForSide(event, 'a', 'point');
        const pointFramesB = this.framesForSide(event, 'b', 'point');
        const zoneFramesA = this.framesForSide(event, 'a', 'polygon');
        const zoneFramesB = this.framesForSide(event, 'b', 'polygon');
        const all = [...pointFramesA, ...pointFramesB, ...zoneFramesA, ...zoneFramesB];
        if (all.length === 0) return;

        this.stopPlayback();

        const minMoment = Math.min(...all.map((f) => f.moment));
        const maxMoment = Math.max(...all.map((f) => f.moment));
        const startedAt = performance.now();

        this.playbackActive = true;
        this.renderMarkers();

        const tick = (now) => {
            if (!this.playbackActive) return;

            const progress = Math.min(1, (now - startedAt) / this.playbackDurationMs);
            this.playbackVirtualMoment = minMoment + (maxMoment - minMoment) * progress;
            this.renderPlaybackFrame(pointFramesA, pointFramesB, zoneFramesA, zoneFramesB);

            if (progress < 1) {
                this.playbackRaf = requestAnimationFrame(tick);
            } else {
                this.playbackActive = false;
            }
        };

        this.playbackRaf = requestAnimationFrame(tick);
    },

    stopPlayback() {
        this.playbackActive = false;
        if (this.playbackRaf) cancelAnimationFrame(this.playbackRaf);
        this.playbackRaf = null;

        ['a', 'b'].forEach((side) => {
            this.sideMarkers[side]?.remove();
            this.sideMarkers[side] = null;
        });
        this.playbackDisplay = { a: null, b: null, momentLabel: '' };

        this.map?.getSource('atlas-side-zones')?.setData({ type: 'FeatureCollection', features: [] });
        this.renderMarkers();
    },

    renderPlaybackFrame(pointFramesA, pointFramesB, zoneFramesA, zoneFramesB) {
        const stateA = this.interpolatedSideState(pointFramesA, this.playbackVirtualMoment);
        const stateB = this.interpolatedSideState(pointFramesB, this.playbackVirtualMoment);

        this.updateSideMarker('a', stateA);
        this.updateSideMarker('b', stateB);
        this.updateSideZones(zoneFramesA, zoneFramesB, this.playbackVirtualMoment);

        const anchorMoment = stateA?.nearestRealMoment ?? stateB?.nearestRealMoment ?? Math.round(this.playbackVirtualMoment);

        this.playbackDisplay = {
            a: stateA ? { troop_count: stateA.troop_count, casualties: stateA.casualties } : null,
            b: stateB ? { troop_count: stateB.troop_count, casualties: stateB.casualties } : null,
            momentLabel: this.formatMoment(anchorMoment),
        };
    },

    updateSideZones(zoneFramesA, zoneFramesB, moment) {
        const source = this.map.getSource('atlas-side-zones');
        if (!source) return;

        const zoneA = this.currentZoneForSide(zoneFramesA, moment);
        const zoneB = this.currentZoneForSide(zoneFramesB, moment);
        const features = [];

        if (zoneA) features.push({ type: 'Feature', geometry: zoneA.geometry, properties: { side: 'a' } });
        if (zoneB) features.push({ type: 'Feature', geometry: zoneB.geometry, properties: { side: 'b' } });

        source.setData({ type: 'FeatureCollection', features });
    },

    updateSideMarker(side, state) {
        if (!state) {
            this.sideMarkers[side]?.remove();
            this.sideMarkers[side] = null;

            return;
        }

        if (!this.sideMarkers[side]) {
            const el = document.createElement('div');
            el.className = `atlas-side-marker atlas-side-marker-${side}`;
            this.sideMarkers[side] = new Marker({ element: el }).setLngLat(state.coords).addTo(this.map);
        } else {
            this.sideMarkers[side].setLngLat(state.coords);
        }

        const icon = state.icon ? (ICONS[state.icon] ?? '') : '';
        const count = state.troop_count != null ? state.troop_count.toLocaleString('ar-EG') : '';
        this.sideMarkers[side].getElement().innerHTML = `
            <span class="atlas-side-marker-dot"></span>
            <span class="atlas-side-marker-label">${icon} ${count}</span>
        `;
    },

    /**
     * The frame of an event that's current as of `this.moment` — the
     * latest one whose own moment has already arrived. Events without
     * frames (the common case) have nothing here and just use their own
     * fixed lat/lng. Side-tagged frames (the two-army animated mode) are
     * excluded — those only render during playback, not as a static pin.
     */
    currentFrame(event) {
        const plain = (event.frames ?? []).filter((frame) => !frame.side);
        if (plain.length === 0) return null;

        const eligible = plain.filter((frame) => frame.moment <= this.moment);
        if (eligible.length === 0) return null;

        return eligible.reduce((latest, frame) => (frame.moment > latest.moment ? frame : latest));
    },

    get visibleEvents() {
        if (this.moment === null) return [];

        return this.events.filter((event) => event.start_moment <= this.moment && this.moment < event.end_moment);
    },

    renderMarkers() {
        this.markers.forEach((marker) => marker.remove());
        this.markers = [];

        const polygonFeatures = [];

        this.visibleEvents.forEach((event) => {
            // While an event's own two-sided animation is playing, its plain
            // marker/polygon would just sit on top of the moving side
            // markers and zones — hide it for the duration of playback.
            if (this.playbackActive && this.selectedEvent?.id === event.id) return;

            const frame = this.currentFrame(event);

            if (frame?.geometry_type === 'polygon') {
                polygonFeatures.push({
                    type: 'Feature',
                    geometry: frame.geometry,
                    properties: { eventId: event.id },
                });
                this.markers.push(this.createMarker(event, this.polygonCentroid(frame.geometry), frame.icon));

                return;
            }

            if (frame?.geometry_type === 'point') {
                this.markers.push(this.createMarker(event, frame.geometry.coordinates, frame.icon));

                return;
            }

            this.markers.push(this.createMarker(event, [event.longitude, event.latitude], null));
        });

        if (this.map.getSource('event-frames-polygons')) {
            this.map.getSource('event-frames-polygons').setData({
                type: 'FeatureCollection',
                features: polygonFeatures,
            });
        }
    },

    createMarker(event, lngLat, icon) {
        const el = document.createElement('button');
        el.type = 'button';
        el.className = icon ? 'atlas-marker atlas-marker-icon' : 'atlas-marker';
        el.textContent = icon ? (ICONS[icon] ?? '') : '';
        el.setAttribute('aria-label', event.title_ar);
        el.addEventListener('click', () => this.selectEvent(event));

        return new Marker({ element: el }).setLngLat(lngLat).addTo(this.map);
    },

    polygonCentroid(geometry) {
        const ring = geometry.coordinates[0];
        const [sumLng, sumLat] = ring.reduce(([lng, lat], [pointLng, pointLat]) => [lng + pointLng, lat + pointLat], [0, 0]);

        return [sumLng / ring.length, sumLat / ring.length];
    },

    onTimeChange() {
        this.stopPlayback();
        this.renderBoundary();
    },

    selectEvent(event) {
        this.stopPlayback();
        this.selectedEvent = event;
        const frame = this.currentFrame(event);
        const center = frame?.geometry_type === 'polygon'
            ? this.polygonCentroid(frame.geometry)
            : (frame?.geometry.coordinates ?? [event.longitude, event.latitude]);

        this.map.flyTo({ center, zoom: 8, duration: 800 });
    },

    get searchResults() {
        const query = this.searchQuery.trim();
        if (!query) return [];

        const needle = query.toLowerCase();

        return this.events
            .filter((event) => (
                event.title_ar.includes(query)
                || event.title_en?.toLowerCase().includes(needle)
                || event.description?.includes(query)
                || event.location_name?.includes(query)
            ))
            .slice(0, 8);
    },

    // Jumps straight to an event regardless of where the timeline is
    // currently scrubbed to — search bypasses browsing the timeline.
    goToEvent(event) {
        const index = this.timelineMoments.indexOf(event.start_moment);
        if (index !== -1) {
            this.momentIndex = index;
            this.onTimeChange();
        }

        this.selectEvent(event);
        this.searchQuery = '';
        this.searchOpen = false;
    },

    closePanel() {
        this.stopPlayback();
        this.selectedEvent = null;
    },

    youtubeEmbedUrl(url) {
        if (!url) return null;

        const match = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([\w-]{11})/);

        return match ? `https://www.youtube.com/embed/${match[1]}` : null;
    },

    formatYear(year) {
        if (year === null || year === undefined) return '';

        return year < 0 ? `${Math.abs(year)} ق.م` : `${year} م`;
    },

    formatMoment(moment) {
        if (moment === null || moment === undefined) return '';

        const { year, month, day, hour, minute } = unpackMoment(moment);

        if (month === 1 && day === 1 && hour === 0 && minute === 0) {
            return this.formatYear(year);
        }

        const datePart = `${day}/${month}/${Math.abs(year)}${year < 0 ? ' ق.م' : ''}`;

        if (hour === 0 && minute === 0) return datePart;

        return `${datePart} ${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;
    },

    formatEventDate(event) {
        return this.formatMoment(event.start_moment);
    },
}));

Alpine.start();
