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

Alpine.data('atlas', () => ({
    loading: true,
    error: null,
    era: null,
    events: [],
    year: null,
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

            this.era = erasRes.data[0] ?? null;
            this.events = eventsRes.data;
            this.year = this.era ? this.era.start_year : null;

            this.initMap();
        } catch (e) {
            this.error = 'تعذّر تحميل بيانات الخريطة. تأكد إن السيرفر شغال.';
            this.loading = false;
        }
    },

    initMap() {
        this.map = new MaplibreMap({
            container: this.$refs.map,
            style: MAP_STYLE,
            center: EGYPT_CENTER,
            zoom: EGYPT_ZOOM,
        });

        this.map.addControl(new NavigationControl(), 'top-left');

        // Markers are plain DOM overlays positioned via the map's
        // projection, so they don't need to wait on style/source loading.
        this.renderMarkers();

        // The boundary layer and label overrides need addSource/addLayer/
        // setLayoutProperty, which require a loaded style. 'styledata'
        // fires repeatedly while the style resolves, so retry against it
        // until it succeeds.
        const addContentWhenReady = () => {
            if (this.mapReady || !this.map.isStyleLoaded()) return;
            this.mapReady = true;
            this.renderBoundary();
            this.arabizeCountryLabels();
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

    renderBoundary() {
        if (!this.era?.boundary_geojson) return;

        this.map.addSource('era-boundary', {
            type: 'geojson',
            data: this.era.boundary_geojson,
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
    },

    arabizeCountryLabels() {
        COUNTRY_LABEL_LAYERS.forEach((id) => {
            if (this.map.getLayer(id)) {
                this.map.setLayoutProperty(id, 'text-field', ARABIC_LABEL_TEXT_FIELD);
            }
        });
    },

    get visibleEvents() {
        if (this.year === null) return [];

        return this.events.filter((event) => event.event_year <= this.year);
    },

    renderMarkers() {
        this.markers.forEach((marker) => marker.remove());
        this.markers = this.visibleEvents.map((event) => {
            const el = document.createElement('button');
            el.type = 'button';
            el.className = 'atlas-marker';
            el.setAttribute('aria-label', event.title_ar);
            el.addEventListener('click', () => this.selectEvent(event));

            return new Marker({ element: el })
                .setLngLat([event.longitude, event.latitude])
                .addTo(this.map);
        });
    },

    onYearChange() {
        this.renderMarkers();
    },

    selectEvent(event) {
        this.selectedEvent = event;
        this.map.flyTo({ center: [event.longitude, event.latitude], zoom: 8, duration: 800 });
    },

    closePanel() {
        this.selectedEvent = null;
    },

    formatYear(year) {
        if (year === null || year === undefined) return '';

        return year < 0 ? `${Math.abs(year)} ق.م` : `${year} م`;
    },

    formatEventDate(event) {
        const year = this.formatYear(event.event_year);

        if (event.date_precision === 'exact' && event.event_month && event.event_day) {
            return `${event.event_day}/${event.event_month}/${Math.abs(event.event_year)}`;
        }

        return year;
    },
}));

Alpine.start();
