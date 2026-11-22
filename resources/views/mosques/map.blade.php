@extends('layouts.app')

@section('title', 'Peta Masjid')

@section('hero-actions')
    <a href="{{ route('mosques.index') }}" class="btn btn-light"><i class="bi bi-list-ul"></i> Senarai</a>
    @can('admin')
        <a href="{{ route('mosques.create') }}" class="btn btn-gold"><i class="bi bi-plus-lg"></i> Tambah</a>
    @endcan
@endsection

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
@include('partials.page-hero', [
    'title' => 'Peta Masjid',
    'subtitle' => 'Pilih masjid dalam senarai untuk melihatnya di peta, atau gunakan lokasi anda untuk mencari yang terdekat.',
    'icon' => 'geo-alt',
    'crumbs' => ['Masjid' => route('mosques.index'), 'Peta' => null],
])

<div class="container-xl page-body">
    <div class="map-layout">
        <div class="surface d-flex flex-column overflow-hidden">
            <div class="p-3 border-bottom d-flex gap-2">
                <input type="search" id="mosqueFilter" class="form-control form-control-sm" placeholder="Tapis masjid..." aria-label="Tapis masjid">
                <button type="button" id="locateMe" class="btn btn-outline-primary btn-sm text-nowrap" title="Cari masjid terdekat">
                    <i class="bi bi-crosshair"></i>
                </button>
            </div>
            <div class="map-list flex-grow-1" id="mosqueList">
                @forelse($mosques as $mosque)
                    <button type="button" data-id="{{ $mosque->id }}" data-name="{{ \Illuminate\Support\Str::lower($mosque->name . ' ' . $mosque->location) }}">
                        <div class="fw-semibold">{{ $mosque->name }}</div>
                        <div class="small text-muted"><i class="bi bi-geo-alt"></i> {{ $mosque->location }} <span class="distance"></span></div>
                    </button>
                @empty
                    <div class="empty py-5">
                        <i class="bi bi-geo-alt"></i>
                        Belum ada masjid dengan koordinat.
                        @can('admin') <div class="mt-2"><a href="{{ route('mosques.create') }}">Tambah masjid</a></div> @endcan
                    </div>
                @endforelse
            </div>
            <div class="px-3 py-2 border-top small text-muted">{{ $mosques->count() }} masjid</div>
        </div>
        <div id="map" role="region" aria-label="Peta masjid"></div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-md-6 col-lg-4">@include('partials.prayer-times')</div>
        <div class="col-md-6 col-lg-8">
            <div class="surface p-4 h-100">
                <h2 class="h5">Kuliah di masjid</h2>
                <p class="text-muted small">Lihat jadual kuliah dan ceramah yang akan datang di masjid-masjid dalam direktori.</p>
                <a href="{{ route('lectures.index') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-calendar-event"></i> Jadual kuliah</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (function () {
            const mosques = @json($mosques);
            const map = L.map('map', { scrollWheelZoom: false }).setView([4.2, 102.0], 6);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
            map.on('focus', () => map.scrollWheelZoom.enable());

            const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const icon = L.divIcon({
                className: '',
                html: '<div style="width:30px;height:30px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);background:var(--ef-primary);border:3px solid #fff;box-shadow:0 4px 10px rgba(0,0,0,.3)"></div>',
                iconSize: [30, 30], iconAnchor: [15, 30], popupAnchor: [0, -28],
            });

            const markers = {};
            mosques.forEach((m) => {
                const directions = 'https://www.google.com/maps/dir/?api=1&destination=' + m.latitude + ',' + m.longitude;
                markers[m.id] = L.marker([m.latitude, m.longitude], { icon }).addTo(map).bindPopup(
                    '<strong>' + esc(m.name) + '</strong><br><span class="text-muted">' + esc(m.location) + '</span>' +
                    (m.contact_info ? '<br><i class="bi bi-telephone"></i> ' + esc(m.contact_info) : '') +
                    (m.upcoming_lectures ? '<br><a href="{{ route('lectures.index') }}?mosque=' + m.id + '"><i class="bi bi-calendar-event"></i> ' + m.upcoming_lectures + ' kuliah akan datang</a>' : '') +
                    '<br><a href="' + directions + '" target="_blank" rel="noopener">Dapatkan arah →</a>'
                );
            });
            if (mosques.length) map.fitBounds(L.featureGroup(Object.values(markers)).getBounds().pad(0.2), { maxZoom: 13 });

            const list = document.getElementById('mosqueList');
            const select = (id) => {
                list.querySelectorAll('button').forEach((b) => b.classList.toggle('active', b.dataset.id == id));
                const mk = markers[id];
                if (mk) { map.flyTo(mk.getLatLng(), 15, { duration: .8 }); mk.openPopup(); }
            };
            list.addEventListener('click', (e) => { const b = e.target.closest('button[data-id]'); if (b) select(b.dataset.id); });
            Object.entries(markers).forEach(([id, mk]) => mk.on('click', () => {
                const b = list.querySelector('button[data-id="' + id + '"]');
                list.querySelectorAll('button').forEach((x) => x.classList.toggle('active', x === b));
                b?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }));

            document.getElementById('mosqueFilter').addEventListener('input', (e) => {
                const q = e.target.value.trim().toLowerCase();
                list.querySelectorAll('button[data-id]').forEach((b) => { b.hidden = q && !b.dataset.name.includes(q); });
            });

            // Nearest mosques: sort the list by distance from the visitor (location stays in the browser).
            document.getElementById('locateMe').addEventListener('click', () => {
                if (!navigator.geolocation) return alert('Pelayar anda tidak menyokong lokasi.');
                navigator.geolocation.getCurrentPosition((pos) => {
                    const me = L.latLng(pos.coords.latitude, pos.coords.longitude);
                    L.circleMarker(me, { radius: 8, color: '#fff', weight: 3, fillColor: '#3884c7', fillOpacity: 1 }).addTo(map).bindPopup('Lokasi anda');
                    const buttons = [...list.querySelectorAll('button[data-id]')];
                    buttons.forEach((b) => {
                        const d = markers[b.dataset.id] ? me.distanceTo(markers[b.dataset.id].getLatLng()) / 1000 : Infinity;
                        b.dataset.distance = d;
                        b.querySelector('.distance').textContent = isFinite(d) ? '· ' + d.toFixed(1) + ' km' : '';
                    });
                    buttons.sort((a, b) => a.dataset.distance - b.dataset.distance).forEach((b) => list.appendChild(b));
                    map.flyTo(me, 12);
                }, () => alert('Lokasi tidak dapat diperoleh. Sila benarkan akses lokasi.'));
            });
        })();
    </script>
@endpush
