import { Map as MaplibreMap, Marker, NavigationControl, setWorkerUrl, setRTLTextPlugin } from 'maplibre-gl';
import maplibreWorkerUrl from 'maplibre-gl/dist/maplibre-gl-worker.mjs?worker&url';
import rtlTextPluginUrl from '../vendor/mapbox-gl-rtl-text.js?url';
import 'maplibre-gl/dist/maplibre-gl.css';

const MAP_STYLE = 'https://tiles.openfreemap.org/styles/liberty';
const EGYPT_CENTER = [30.0, 26.8];

function registerMapWorkerOnce() {
    if (window.__atlasMapWorkerRegistered) return;
    setWorkerUrl(maplibreWorkerUrl);
    setRTLTextPlugin(rtlTextPluginUrl, false);
    window.__atlasMapWorkerRegistered = true;
}

function fieldEl(selector) {
    return document.querySelector(selector);
}

function readValue(selector) {
    return fieldEl(selector)?.value ?? null;
}

function dispatchValue(selector, value, eventName = 'input') {
    const el = fieldEl(selector);
    if (!el) return;
    el.value = value;
    el.dispatchEvent(new Event(eventName, { bubbles: true }));
}

// Filament renders these sibling inputs with their real value up-front, but
// on a fresh page load Alpine can start (and run this component's init())
// a tick before Livewire finishes hydrating the surrounding form — so a
// single synchronous read can catch the field before it has its value.
// Poll briefly instead of assuming the first read is authoritative.
function waitForFieldValue(selector, { attempts = 20, delayMs = 25 } = {}) {
    return new Promise((resolve) => {
        const attempt = (remaining) => {
            const value = readValue(selector);
            if (value !== null && value !== '') {
                resolve(value);

                return;
            }
            if (remaining <= 0) {
                resolve(value);

                return;
            }
            setTimeout(() => attempt(remaining - 1), delayMs);
        };
        attempt(attempts);
    });
}

function ringCentroid(ring) {
    const [sumLng, sumLat] = ring.reduce(([lng, lat], [pLng, pLat]) => [lng + pLng, lat + pLat], [0, 0]);

    return [sumLng / ring.length, sumLat / ring.length];
}

document.addEventListener('alpine:init', () => {
    // A single lat/lng point picker — used for an Event's own location.
    window.Alpine.data('mapPointPicker', () => ({
        map: null,
        marker: null,

        async init() {
            registerMapWorkerOnce();

            const [latRaw, lngRaw] = await Promise.all([
                waitForFieldValue('[data-map-field="latitude"]'),
                waitForFieldValue('[data-map-field="longitude"]'),
            ]);
            const lat = parseFloat(latRaw);
            const lng = parseFloat(lngRaw);
            const hasPoint = !Number.isNaN(lat) && !Number.isNaN(lng);

            this.map = new MaplibreMap({
                container: this.$refs.map,
                style: MAP_STYLE,
                center: hasPoint ? [lng, lat] : EGYPT_CENTER,
                zoom: hasPoint ? 7 : 5,
            });
            this.map.addControl(new NavigationControl(), 'top-left');

            if (hasPoint) this.placeMarker({ lng, lat });

            this.map.on('click', (e) => this.placeMarker(e.lngLat));
        },

        placeMarker(lngLat) {
            if (this.marker) {
                this.marker.setLngLat(lngLat);
            } else {
                this.marker = new Marker({ color: '#f59e0b', draggable: true }).setLngLat(lngLat).addTo(this.map);
                this.marker.on('dragend', () => this.updateFields(this.marker.getLngLat()));
            }
            this.updateFields(lngLat);
        },

        updateFields(lngLat) {
            dispatchValue('[data-map-field="latitude"]', lngLat.lat.toFixed(7));
            dispatchValue('[data-map-field="longitude"]', lngLat.lng.toFixed(7));
        },
    }));

    // A point-or-polygon geometry picker — used for an EventFrame's shape.
    window.Alpine.data('mapGeometryPicker', () => ({
        map: null,
        markers: [],
        mode: 'point',

        async init() {
            registerMapWorkerOnce();

            const [geometryTypeRaw, geometryRaw] = await Promise.all([
                waitForFieldValue('[data-map-field="geometry_type"]'),
                waitForFieldValue('[data-map-field="geometry"]'),
            ]);

            this.mode = geometryTypeRaw || 'point';

            let initialGeometry = null;
            try {
                initialGeometry = JSON.parse(geometryRaw);
            } catch (e) {
                initialGeometry = null;
            }

            const center = initialGeometry
                ? (initialGeometry.type === 'Point' ? initialGeometry.coordinates : ringCentroid(initialGeometry.coordinates[0]))
                : EGYPT_CENTER;

            this.map = new MaplibreMap({
                container: this.$refs.map,
                style: MAP_STYLE,
                center,
                zoom: initialGeometry ? 7 : 5,
            });
            this.map.addControl(new NavigationControl(), 'top-left');

            this.map.on('load', () => {
                this.map.addSource('picker-polygon', {
                    type: 'geojson',
                    data: { type: 'FeatureCollection', features: [] },
                });
                this.map.addLayer({
                    id: 'picker-polygon-fill',
                    type: 'fill',
                    source: 'picker-polygon',
                    paint: { 'fill-color': '#dc2626', 'fill-opacity': 0.25 },
                });
                this.map.addLayer({
                    id: 'picker-polygon-line',
                    type: 'line',
                    source: 'picker-polygon',
                    paint: { 'line-color': '#dc2626', 'line-width': 2 },
                });

                if (initialGeometry?.type === 'Point') {
                    this.addVertex(initialGeometry.coordinates, { silent: true });
                } else if (initialGeometry?.type === 'Polygon') {
                    initialGeometry.coordinates[0].slice(0, -1).forEach((coord) => this.addVertex(coord, { silent: true }));
                    this.renderPolygon();
                }
            });

            this.map.on('click', (e) => {
                if (this.mode === 'point') {
                    this.markers.forEach((m) => m.remove());
                    this.markers = [];
                }
                this.addVertex([e.lngLat.lng, e.lngLat.lat]);
                if (this.mode === 'polygon') this.renderPolygon();
            });
        },

        setMode(mode) {
            if (this.mode === mode) return;
            this.mode = mode;
            this.clear();
            dispatchValue('[data-map-field="geometry_type"]', mode, 'change');
        },

        addVertex(coord, { silent = false } = {}) {
            const marker = new Marker({ color: '#f59e0b', draggable: this.mode === 'point' }).setLngLat(coord).addTo(this.map);
            if (this.mode === 'point') {
                marker.on('dragend', () => this.commitPoint(marker.getLngLat()));
            }
            this.markers.push(marker);

            if (!silent && this.mode === 'point') this.commitPoint({ lng: coord[0], lat: coord[1] });
        },

        commitPoint(lngLat) {
            dispatchValue('[data-map-field="geometry"]', JSON.stringify({ type: 'Point', coordinates: [lngLat.lng, lngLat.lat] }));
        },

        renderPolygon() {
            const coords = this.markers.map((m) => {
                const ll = m.getLngLat();

                return [ll.lng, ll.lat];
            });

            if (coords.length < 3) {
                this.map.getSource('picker-polygon')?.setData({ type: 'FeatureCollection', features: [] });

                return;
            }

            const ring = [...coords, coords[0]];
            this.map.getSource('picker-polygon')?.setData({
                type: 'Feature',
                geometry: { type: 'Polygon', coordinates: [ring] },
                properties: {},
            });
            dispatchValue('[data-map-field="geometry"]', JSON.stringify({ type: 'Polygon', coordinates: [ring] }));
        },

        undoLast() {
            this.markers.pop()?.remove();
            this.renderPolygon();
        },

        clear() {
            this.markers.forEach((m) => m.remove());
            this.markers = [];
            this.map.getSource('picker-polygon')?.setData({ type: 'FeatureCollection', features: [] });
            dispatchValue('[data-map-field="geometry"]', '');
        },
    }));
});
