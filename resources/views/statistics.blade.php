@extends('layouts.app')

@section('title', 'Statistik Fatwa')

@section('content')
@include('partials.page-hero', [
    'title' => 'Statistik Fatwa',
    'subtitle' => 'Gambaran keseluruhan fatwa dalam pangkalan data mengikut negeri, tahun dan kategori.',
    'icon' => 'bar-chart',
    'crumbs' => ['Statistik' => route('statistics')],
])

<div class="container-xl page-body">
    {{-- KPI tiles --}}
    <div class="surface mb-4 reveal">
        <div class="row g-0">
            <div class="col-6 col-md-3 stat"><div class="num" data-count="{{ $totals['fatwas'] }}">0</div><div class="lbl">Jumlah fatwa</div></div>
            <div class="col-6 col-md-3 stat">
                <div class="num">{{ $totals['fatwas'] ? round($totals['with_text'] / $totals['fatwas'] * 100) : 0 }}%</div>
                <div class="lbl">Boleh dicari teks penuh</div>
            </div>
            <div class="col-6 col-md-3 stat"><div class="num" data-count="{{ $totals['gazetted'] }}">0</div><div class="lbl">Diwartakan</div></div>
            <div class="col-6 col-md-3 stat">
                <div class="num" style="font-size: 1.7rem">{{ $totals['from'] ?? '–' }}–{{ $totals['to'] ?? '–' }}</div>
                <div class="lbl">Tempoh liputan</div>
            </div>
        </div>
    </div>

    @if($totals['fatwas'] === 0)
        <div class="surface empty"><i class="bi bi-bar-chart"></i>Belum ada data. Jalankan <code>php artisan fatwa:scrape</code>.</div>
    @else
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="surface p-4 h-100 reveal">
                    <h2 class="h5">Fatwa mengikut negeri</h2>
                    <p class="small text-muted">Bahagian gelap: fatwa dengan teks penuh yang boleh dicari.</p>
                    <div class="chart-box"><canvas id="chartStates" aria-label="Carta fatwa mengikut negeri" role="img"></canvas></div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="surface p-4 h-100 reveal" style="--d:.08s">
                    <h2 class="h5">Kategori utama</h2>
                    <p class="small text-muted">{{ $totals['uncategorised'] }} fatwa belum dikategorikan oleh sumbernya.</p>
                    <div class="chart-box"><canvas id="chartCategories" aria-label="Carta kategori fatwa" role="img"></canvas></div>
                </div>
            </div>
            <div class="col-12">
                <div class="surface p-4 reveal">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <h2 class="h5 mb-0">Fatwa mengikut tahun</h2>
                        <div class="btn-group btn-group-sm" role="group" aria-label="Jenis carta">
                            <button type="button" class="btn btn-outline-primary active" data-year-mode="stacked">Bertindan</button>
                            <button type="button" class="btn btn-outline-primary" data-year-mode="line">Garis</button>
                        </div>
                    </div>
                    <p class="small text-muted">Klik nama negeri pada legenda untuk menyembunyikan atau menunjukkannya.</p>
                    <div class="chart-box tall"><canvas id="chartYears" aria-label="Carta fatwa mengikut tahun" role="img"></canvas></div>
                </div>
            </div>
            <div class="col-12">
                <div class="surface overflow-hidden reveal">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr><th>Negeri</th><th class="text-end">Fatwa</th><th class="text-end">Teks penuh</th><th class="text-end">Diwartakan</th><th></th></tr>
                        </thead>
                        <tbody>
                            @foreach($perState as $row)
                                <tr>
                                    <td><x-state-pill :state="$row['state']" /></td>
                                    <td class="text-end fw-bold">{{ $row['total'] }}</td>
                                    <td class="text-end">{{ $row['with_text'] }} <span class="text-muted small">({{ round($row['with_text'] / max(1, $row['total']) * 100) }}%)</span></td>
                                    <td class="text-end">{{ $row['gazetted'] }}</td>
                                    <td class="text-end"><a href="{{ route('fatwas.index', ['state' => $row['state']]) }}" class="small">Lihat <i class="bi bi-arrow-right"></i></a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <script>
        (function () {
            if (!window.Chart || !document.getElementById('chartStates')) return;

            const perState = @json($perState);
            const perYear = @json($perYear);
            const categories = @json($categories);
            const fatwasUrl = @json(route('fatwas.index'));
            const charts = [];
            let yearMode = 'stacked';

            const css = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();
            const fade = (hsl, a) => hsl.replace(')', ' / ' + a + ')');

            function build() {
                charts.splice(0).forEach((c) => c.destroy());
                Chart.defaults.font.family = css('--ef-font') || 'sans-serif';
                Chart.defaults.color = css('--ef-muted');
                Chart.defaults.borderColor = css('--ef-border');
                const animation = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? false : undefined;

                // States: total vs with text, click a bar to open that state's fatwas.
                charts.push(new Chart(document.getElementById('chartStates'), {
                    type: 'bar',
                    data: {
                        labels: perState.map((s) => s.state),
                        datasets: [
                            { label: 'Teks penuh', data: perState.map((s) => s.with_text), backgroundColor: perState.map((s) => s.colour), borderRadius: 6, stack: 's' },
                            { label: 'Tajuk sahaja', data: perState.map((s) => s.total - s.with_text), backgroundColor: perState.map((s) => fade(s.colour, .3)), borderRadius: 6, stack: 's' },
                        ],
                    },
                    options: {
                        animation, maintainAspectRatio: false, indexAxis: 'y',
                        scales: { x: { stacked: true, grid: { display: false } }, y: { stacked: true, grid: { display: false } } },
                        plugins: { legend: { position: 'bottom' } },
                        onClick: (e, items) => { if (items.length) window.location = fatwasUrl + '?state=' + encodeURIComponent(perState[items[0].index].state); },
                        onHover: (e, items) => { e.native.target.style.cursor = items.length ? 'pointer' : 'default'; },
                    },
                }));

                // Categories doughnut.
                const palette = ['#0f5c4d', '#c4952b', '#3884c7', '#c85064', '#7a5bc4', '#2a9a80', '#d07a2c', '#5c6c66', '#9c3d8a', '#4f7d2c', '#b0841f', '#2f6db0'];
                charts.push(new Chart(document.getElementById('chartCategories'), {
                    type: 'doughnut',
                    data: { labels: categories.map((c) => c.name), datasets: [{ data: categories.map((c) => c.total), backgroundColor: palette, borderColor: css('--ef-surface'), borderWidth: 2 }] },
                    options: { animation, maintainAspectRatio: false, cutout: '58%', plugins: { legend: { position: 'right', labels: { boxWidth: 12 } } } },
                }));

                // Years: stacked bars or lines per state.
                const stacked = yearMode === 'stacked';
                charts.push(new Chart(document.getElementById('chartYears'), {
                    type: stacked ? 'bar' : 'line',
                    data: {
                        labels: perYear.labels,
                        datasets: perYear.series.map((s) => ({
                            label: s.state, data: s.data,
                            backgroundColor: stacked ? s.colour : fade(s.colour, .15), borderColor: s.colour,
                            borderWidth: stacked ? 0 : 2, tension: .3, pointRadius: 0, fill: !stacked, borderRadius: 3,
                        })),
                    },
                    options: {
                        animation, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
                        scales: { x: { stacked, grid: { display: false } }, y: { stacked, beginAtZero: true, ticks: { precision: 0 } } },
                        plugins: { legend: { position: 'bottom' } },
                    },
                }));
            }

            build();

            document.querySelectorAll('[data-year-mode]').forEach((btn) => btn.addEventListener('click', () => {
                yearMode = btn.dataset.yearMode;
                document.querySelectorAll('[data-year-mode]').forEach((b) => b.classList.toggle('active', b === btn));
                build();
            }));

            // Re-draw with the new colours when the light/dark theme changes.
            new MutationObserver(build).observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
        })();
    </script>
@endpush
