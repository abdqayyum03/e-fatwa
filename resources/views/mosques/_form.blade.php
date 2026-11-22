{{-- Shared by mosques.create and mosques.edit. Click the map to set the coordinates. --}}
@php($mosque = $mosque ?? null)

@if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="row g-4">
    <div class="col-lg-5">
        <div class="mb-3">
            <label for="name" class="form-label">Nama masjid *</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $mosque?->name) }}" required>
        </div>
        <div class="mb-3">
            <label for="location" class="form-label">Lokasi (bandar / negeri) *</label>
            <input type="text" id="location" name="location" class="form-control" value="{{ old('location', $mosque?->location) }}" required>
        </div>
        <div class="mb-3">
            <label for="contact_info" class="form-label">No. telefon</label>
            <input type="text" id="contact_info" name="contact_info" class="form-control" value="{{ old('contact_info', $mosque?->contact_info) }}">
        </div>
        <div class="row g-2">
            <div class="col-6">
                <label for="latitude" class="form-label">Latitud *</label>
                <input type="text" id="latitude" name="latitude" class="form-control" placeholder="3.1390" value="{{ old('latitude', $mosque?->latitude) }}" required>
            </div>
            <div class="col-6">
                <label for="longitude" class="form-label">Longitud *</label>
                <input type="text" id="longitude" name="longitude" class="form-control" placeholder="101.6869" value="{{ old('longitude', $mosque?->longitude) }}" required>
            </div>
        </div>
        <p class="form-text"><i class="bi bi-hand-index"></i> Klik pada peta untuk mengisi koordinat secara automatik.</p>
    </div>
    <div class="col-lg-7">
        <div id="pickMap" style="height: 360px; border-radius: var(--ef-radius); border: 1px solid var(--ef-border)"></div>
    </div>
</div>

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush
@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (function () {
            const lat = document.getElementById('latitude');
            const lng = document.getElementById('longitude');
            const start = (lat.value && lng.value) ? [parseFloat(lat.value), parseFloat(lng.value)] : [4.2, 102.0];
            const map = L.map('pickMap').setView(start, lat.value ? 14 : 6);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
            let marker = lat.value ? L.marker(start).addTo(map) : null;
            const place = (latlng) => {
                lat.value = latlng.lat.toFixed(6);
                lng.value = latlng.lng.toFixed(6);
                marker ? marker.setLatLng(latlng) : (marker = L.marker(latlng).addTo(map));
            };
            map.on('click', (e) => place(e.latlng));
            [lat, lng].forEach((el) => el.addEventListener('change', () => {
                const p = L.latLng(parseFloat(lat.value), parseFloat(lng.value));
                if (!isNaN(p.lat) && !isNaN(p.lng)) { place(p); map.setView(p, 14); }
            }));
        })();
    </script>
@endpush
