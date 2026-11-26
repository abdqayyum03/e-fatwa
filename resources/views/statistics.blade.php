@extends('layouts.app')

@section('title', __('Statistik Fatwa'))

@section('content')
@include('partials.page-hero', [
    'title' => __('Statistik Fatwa'),
    'subtitle' => __('Gambaran keseluruhan fatwa dalam pangkalan data mengikut negeri, tahun dan kategori.'),
    'icon' => 'bar-chart',
    'crumbs' => [__('Statistik') => route('statistics')],
])

<div class="container-xl page-body">
    {{-- Key figures --}}
    <div class="surface mb-4">
        <div class="row g-0">
            <div class="col-6 col-md-3 stat"><div class="num">{{ number_format($totals['fatwas']) }}</div><div class="lbl">{{ __('Jumlah fatwa') }}</div></div>
            <div class="col-6 col-md-3 stat">
                <div class="num">{{ $totals['fatwas'] ? round($totals['with_text'] / $totals['fatwas'] * 100) : 0 }}%</div>
                <div class="lbl">{{ __('Boleh dicari teks penuh') }}</div>
            </div>
            <div class="col-6 col-md-3 stat"><div class="num">{{ number_format($totals['gazetted']) }}</div><div class="lbl">{{ __('Diwartakan') }}</div></div>
            <div class="col-6 col-md-3 stat">
                <div class="num">{{ $totals['from'] ?? '–' }}–{{ $totals['to'] ?? '–' }}</div>
                <div class="lbl">{{ __('Tempoh liputan') }}</div>
            </div>
        </div>
    </div>

    @if($totals['fatwas'] === 0)
        <div class="surface empty"><i class="bi bi-bar-chart"></i>{{ __('Belum ada data. Jalankan') }} <code>{{ __('php artisan fatwa:scrape') }}</code>.</div>
    @else
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="surface p-4 h-100">
                    <h2 class="h5">{{ __('Fatwa mengikut negeri') }}</h2>
                    <p class="small text-muted">{{ __('Klik bar untuk membuka fatwa negeri itu.') }}</p>
                    <div class="chart-box"><canvas id="chartStates" aria-label="{{ __('Carta fatwa mengikut negeri') }}" role="img"></canvas></div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="surface p-4 h-100">
                    <h2 class="h5">{{ __('Kategori utama') }}</h2>
                    <p class="small text-muted">{{ __(':count fatwa belum berkategori.', ['count' => number_format($totals['uncategorised'])]) }}</p>
                    @php($maxCategory = max(1, $categories->max('total') ?? 1))
                    @foreach($categories as $category)
                        <div class="bar-row">
                            <span class="bar-label">{{ $category->name }}</span>
                            <div class="bar-track"><div class="bar-fill" style="width: {{ max(1, round($category->total / $maxCategory * 100)) }}%"></div></div>
                            <span class="count">{{ number_format($category->total) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-12">
                <div class="surface p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <h2 class="h5 mb-0">{{ __('Fatwa mengikut tahun') }}</h2>
                        <div class="reading-tools" role="group" aria-label="{{ __('Jenis carta') }}">
                            <button type="button" class="active" data-year-mode="total" aria-pressed="true">{{ __('Jumlah') }}</button>
                            <button type="button" data-year-mode="stacked" aria-pressed="false">{{ __('Bertindan') }}</button>
                            <button type="button" data-year-mode="line" aria-pressed="false">{{ __('Garis') }}</button>
                        </div>
                    </div>
                    <p class="small text-muted" data-year-hint>{{ __('Semua negeri digabungkan. Pilih Bertindan atau Garis untuk melihat setiap negeri.') }}</p>
                    <div class="chart-box tall"><canvas id="chartYears" aria-label="{{ __('Carta fatwa mengikut tahun') }}" role="img"></canvas></div>
                </div>
            </div>
            <div class="col-12">
                <div class="surface overflow-hidden">
                    <table class="table mb-0 align-middle stats-table">
                        <thead>
                            <tr><th>{{ __('Negeri') }}</th><th class="text-end">{{ __('Fatwa') }}</th><th class="text-end">{{ __('Teks penuh') }}</th><th class="text-end">{{ __('Diwartakan') }}</th><th class="d-none d-sm-table-cell"></th></tr>
                        </thead>
                        <tbody>
                            @foreach($perState as $row)
                                <tr>
                                    <td><a href="{{ route('fatwas.index', ['state' => $row['state']]) }}" class="state-link">{{ $row['state'] }}</a></td>
                                    <td class="text-end num">{{ number_format($row['total']) }}</td>
                                    <td class="text-end num">{{ number_format($row['with_text']) }} <span class="text-muted d-none d-sm-inline">({{ round($row['with_text'] / max(1, $row['total']) * 100) }}%)</span></td>
                                    <td class="text-end num">{{ number_format($row['gazetted']) }}</td>
                                    <td class="text-end d-none d-sm-table-cell"><a href="{{ route('fatwas.index', ['state' => $row['state']]) }}" class="small">{{ __('Lihat') }} <i class="bi bi-arrow-right"></i></a></td>
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
            const fatwasUrl = @json(route('fatwas.index'));
            const hints = {
                total: @json(__('Semua negeri digabungkan. Pilih Bertindan atau Garis untuk melihat setiap negeri.')),
                stacked: @json(__('Klik nama negeri pada legenda untuk menyembunyikan atau menunjukkannya.')),
                line: @json(__('Klik nama negeri pada legenda untuk menyembunyikan atau menunjukkannya.')),
            };
            const charts = [];
            let yearMode = 'total';

            const css = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();
            const fade = (hsl, a) => hsl.replace(')', ' / ' + a + ')');

            function build() {
                charts.splice(0).forEach((c) => c.destroy());
                const accent = css('--ef-primary');
                const mono = css('--ef-mono') || 'monospace';
                Chart.defaults.font.family = css('--ef-font') || 'sans-serif';
                Chart.defaults.color = css('--ef-muted');
                Chart.defaults.borderColor = css('--ef-border');
                const ticks = { font: { family: mono, size: 11 } };
                const animation = false;

                // States: one series in the accent; click a bar to open that state's fatwas.
                charts.push(new Chart(document.getElementById('chartStates'), {
                    type: 'bar',
                    data: {
                        labels: perState.map((s) => s.state),
                        datasets: [{ label: @json(__('Fatwa')), data: perState.map((s) => s.total), backgroundColor: accent, borderRadius: 3, barPercentage: .7 }],
                    },
                    options: {
                        animation, maintainAspectRatio: false, indexAxis: 'y',
                        scales: { x: { grid: { color: css('--ef-border') }, border: { display: false }, ticks }, y: { grid: { display: false }, border: { display: false } } },
                        plugins: { legend: { display: false } },
                        onClick: (e, items) => { if (items.length) window.location = fatwasUrl + '?state=' + encodeURIComponent(perState[items[0].index].state); },
                        onHover: (e, items) => { e.native.target.style.cursor = items.length ? 'pointer' : 'default'; },
                    },
                }));

                // Years: all states together (default), or each state stacked or as lines.
                const datasets = yearMode === 'total'
                    ? [{ label: @json(__('Jumlah')), data: perYear.labels.map((_, i) => perYear.series.reduce((sum, s) => sum + (s.data[i] || 0), 0)), backgroundColor: accent, borderRadius: 2 }]
                    : perYear.series.map((s) => ({
                        label: s.state, data: s.data,
                        backgroundColor: yearMode === 'stacked' ? s.colour : fade(s.colour, .12), borderColor: s.colour,
                        borderWidth: yearMode === 'stacked' ? 0 : 1.5, tension: .3, pointRadius: 0, fill: yearMode === 'line', borderRadius: 2,
                    }));
                const stacked = yearMode !== 'line';
                charts.push(new Chart(document.getElementById('chartYears'), {
                    type: yearMode === 'line' ? 'line' : 'bar',
                    data: { labels: perYear.labels, datasets },
                    options: {
                        animation, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
                        scales: {
                            x: { stacked, grid: { display: false }, border: { display: false }, ticks: { ...ticks, maxRotation: 0, autoSkipPadding: 12 } },
                            y: { stacked, beginAtZero: true, border: { display: false }, grid: { color: css('--ef-border') }, ticks: { ...ticks, precision: 0 } },
                        },
                        plugins: { legend: { display: yearMode !== 'total', position: 'bottom', labels: { boxWidth: 10, boxHeight: 10 } } },
                    },
                }));
            }

            build();

            document.querySelectorAll('[data-year-mode]').forEach((btn) => btn.addEventListener('click', () => {
                yearMode = btn.dataset.yearMode;
                document.querySelectorAll('[data-year-mode]').forEach((b) => {
                    b.classList.toggle('active', b === btn);
                    b.setAttribute('aria-pressed', b === btn ? 'true' : 'false');
                });
                document.querySelector('[data-year-hint]').textContent = hints[yearMode];
                build();
            }));

            // Re-draw with the new colours when the light/dark theme changes.
            new MutationObserver(build).observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
        })();
    </script>
@endpush
