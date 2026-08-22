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

    /**
     * The frame of an event that's current as of `this.moment` — the
     * latest one whose own moment has already arrived. Events without
     * frames (the common case) have nothing here and just use their own
     * fixed lat/lng.
     */
    currentFrame(event) {
        if (!event.frames || event.frames.length === 0) return null;

        const eligible = event.frames.filter((frame) => frame.moment <= this.moment);
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
        this.renderMarkers();
        this.renderBoundary();
    },

    selectEvent(event) {
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
        this.selectedEvent = null;
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
