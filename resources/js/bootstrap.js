import axios from 'axios';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Fix Leaflet's default icon path issues
delete L.Icon.Default.prototype._getIconUrl;

L.Icon.Default.mergeOptions({
    iconUrl: markerIcon,
    iconRetinaUrl: markerIcon2x,
    shadowUrl: markerShadow,
});

// Leaflet Map Initialization
document.addEventListener('DOMContentLoaded', () => {
    const mapElement = document.getElementById('housing-map');

    if (mapElement) {
        // Initialize map centered on configured location
        const config = window.housingConfig || { center: [-6.3016, 106.8266], zoom: 17 };
        const map = L.map('housing-map').setView(config.center, config.zoom); // Dynamic center

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Add dummy housing blocks (Polygons)

        // Block A (Occupied - Green)
        const blockA = [
            [-6.3010, 106.8260],
            [-6.3010, 106.8270],
            [-6.3015, 106.8270],
            [-6.3015, 106.8260]
        ];
        L.polygon(blockA, { color: 'green', fillColor: '#4ade80', fillOpacity: 0.5 }).addTo(map)
            .bindPopup("<b>Blok A</b><br>Status: Full Penghuni");

        // Block B (Empty/Under Construction - Yellow)
        const blockB = [
            [-6.3016, 106.8260],
            [-6.3016, 106.8270],
            [-6.3021, 106.8270],
            [-6.3021, 106.8260]
        ];
        L.polygon(blockB, { color: 'orange', fillColor: '#fbbf24', fillOpacity: 0.5 }).addTo(map)
            .bindPopup("<b>Blok B</b><br>Status: Tahap Pembangunan");

        // Facility (Park - Blue)
        const park = [
            [-6.3010, 106.8272],
            [-6.3010, 106.8280],
            [-6.3021, 106.8280],
            [-6.3021, 106.8272]
        ];
        L.polygon(park, { color: 'blue', fillColor: '#60a5fa', fillOpacity: 0.5 }).addTo(map)
            .bindPopup("<b>Taman Warga</b><br>Fasilitas Umum");

        // Markers for specific important spots - using dynamic center
        L.marker(config.center).addTo(map)
            .bindPopup("<b>Lokasi Utama</b><br>Titik Pusat Perumahan").openPopup();

        // Render Houses from Database
        if (config.houses && config.houses.length > 0) {
            config.houses.forEach(house => {
                if (house.latitude && house.longitude) {
                    let color = '#9ca3af'; // gray default
                    let fillColor = '#d1d5db';
                    let statusLabel = 'Tidak Diketahui';

                    if (house.status === 'occupied') {
                        color = '#16a34a'; // green-600
                        fillColor = '#4ade80'; // green-400
                        statusLabel = 'Terbangun';
                    } else if (house.status === 'vacant_land') {
                        color = '#ca8a04'; // yellow-600
                        fillColor = '#facc15'; // yellow-400
                        statusLabel = 'Kavling';
                    }

                    L.circleMarker([house.latitude, house.longitude], {
                        radius: 8,
                        color: color,
                        weight: 2,
                        fillColor: fillColor,
                        fillOpacity: 0.7
                    }).addTo(map)
                        .bindPopup(`<b>Blok ${house.block} No. ${house.number}</b><br>${statusLabel}`);
                }
            });
        }
    }
});
