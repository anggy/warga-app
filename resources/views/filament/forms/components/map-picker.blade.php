<div
    x-data="{
        state: $wire.$entangle('{{ $getStatePath() }}'),
        lat: $wire.$entangle('data.latitude'),
        lng: $wire.$entangle('data.longitude'),
        map: null,
        marker: null,
        initMap() {
            // Load Leaflet CSS
            if (!document.getElementById('leaflet-css')) {
                const link = document.createElement('link');
                link.id = 'leaflet-css';
                link.rel = 'stylesheet';
                link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                document.head.appendChild(link);
            }

            // Load Leaflet JS
            if (!window.L) {
                const script = document.createElement('script');
                script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                script.onload = () => this.setupMap();
                document.head.appendChild(script);
            } else {
                this.setupMap();
            }
        },
        setupMap() {
            // Default to system settings or Jakarta
            let center = [-6.200000, 106.816666];
            let zoom = 13;

            // If we have existing coordinates, use them
            if (this.lat && this.lng) {
                center = [parseFloat(this.lat), parseFloat(this.lng)];
                zoom = 18;
            }

            this.map = L.map(this.$refs.map).setView(center, zoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(this.map);

            // Fix missing marker icon by using CDN paths
            delete L.Icon.Default.prototype._getIconUrl;
            L.Icon.Default.mergeOptions({
                iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
                iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
            });

            // Create marker
            this.marker = L.marker(center, {
                draggable: true
            }).addTo(this.map);

            // Update state on drag
            this.marker.on('dragend', (event) => {
                const position = event.target.getLatLng();
                this.lat = position.lat.toFixed(8);
                this.lng = position.lng.toFixed(8);
            });

            // Update marker on map click
            this.map.on('click', (event) => {
                const position = event.latlng;
                this.marker.setLatLng(position);
                this.lat = position.lat.toFixed(8);
                this.lng = position.lng.toFixed(8);
            });
            
            // Watch for manual input changes to update map
            this.$watch('lat', (value) => {
                if (value && this.lng) {
                    const newLatLng = new L.LatLng(value, this.lng);
                    this.marker.setLatLng(newLatLng);
                    this.map.panTo(newLatLng);
                }
            });
            
             this.$watch('lng', (value) => {
                if (value && this.lat) {
                    const newLatLng = new L.LatLng(this.lat, value);
                    this.marker.setLatLng(newLatLng);
                    this.map.panTo(newLatLng);
                }
            });
        }
    }"
    x-init="initMap()"
    wire:ignore
    class="w-full"
>
    <div x-ref="map" style="height: 400px; width: 100%; border-radius: 0.5rem; z-index: 0;"></div>
    <div class="text-xs text-gray-500 mt-2">Klik peta atau geser marker untuk mengubah lokasi.</div>
</div>
