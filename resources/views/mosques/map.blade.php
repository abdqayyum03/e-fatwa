@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Masjid Locator Map</h2>

    <div id="map" style="height: 500px; margin-top: 20px;"></div>
</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    var map = L.map('map').setView([3.1390, 101.6869], 7); // Default: Malaysia center

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
    }).addTo(map);

    const mosques = @json($mosques);

    mosques.forEach(function(mosque) {
        if (mosque.latitude && mosque.longitude) {
            L.marker([mosque.latitude, mosque.longitude])
                .addTo(map)
                .bindPopup("<strong>" + mosque.name + "</strong><br>" + mosque.location);
        }
    });
</script>
@endsection
