@extends('admin.layout', [
    'heading' => __('Import'),
    'subheading' => __('Jalankan import fatwa bagi satu negeri, dan lihat sejarah setiap import.'),
    'icon' => 'cloud-download',
])

@section('title', __('Import'))

@section('admin')
@if($stuck)
    <div class="alert alert-warning" role="alert">
        <i class="bi bi-exclamation-triangle me-1"></i>
        {{ __('Ada import yang masih menunggu dalam baris gilir. Import dijalankan oleh pekerja baris gilir; mulakan satu dalam terminal:') }}
        <code class="d-block mt-1">php artisan queue:work --timeout=3600</code>
    </div>
@elseif($active)
    <div class="alert alert-info d-flex align-items-center gap-2" role="status">
        <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
        {{ __('Import sedang berjalan. Halaman ini dimuat semula setiap 15 saat.') }}
    </div>
@endif

<div class="surface overflow-hidden mb-4">
    <div class="p-4 pb-2">
        <h2 class="h5 mb-1">{{ __('Sumber') }}</h2>
        <p class="small text-muted mb-0">{{ __('Import mengemas kini fatwa sedia ada dan menambah yang baharu. PDF yang sudah dibaca tidak dimuat turun semula; imbasan baharu dibaca dengan OCR.') }}</p>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr><th>{{ __('Negeri') }}</th><th>{{ __('Import terakhir') }}</th><th class="text-end">{{ __('Baharu') }}</th><th class="text-end">{{ __('Dikemas kini') }}</th><th class="text-end">{{ __('Gagal') }}</th><th></th></tr>
            </thead>
            <tbody>
                @foreach($sources as $key => $state)
                    @php($run = $latest[$key] ?? null)
                    <tr>
                        <td><x-state-pill :state="$state" /> <code class="small text-muted ms-1">{{ $key }}</code></td>
                        <td>
                            @include('admin._run-status', ['run' => $run])
                            @if($run?->started_at)<div class="small text-muted">{{ ($run->finished_at ?? $run->started_at)->diffForHumans() }}</div>@endif
                        </td>
                        <td class="text-end">{{ $run?->status === 'done' ? $run->created_count : '–' }}</td>
                        <td class="text-end">{{ $run?->status === 'done' ? $run->updated_count : '–' }}</td>
                        <td class="text-end">{{ $run?->status === 'done' ? $run->failed_count : '–' }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.imports.store', $key) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-primary text-nowrap" @disabled($run?->isActive())>
                                    <i class="bi bi-play-fill"></i> {{ __('Jalankan') }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="surface overflow-hidden">
    <div class="p-4 pb-2"><h2 class="h5 mb-0">{{ __('Sejarah import') }}</h2></div>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr><th>{{ __('Mula') }}</th><th>{{ __('Sumber') }}</th><th>{{ __('Status') }}</th><th>{{ __('Oleh') }}</th><th class="text-end">{{ __('Baharu') }}</th><th class="text-end">{{ __('Dikemas kini') }}</th><th class="text-end">{{ __('Dengan teks') }}</th><th class="text-end">{{ __('Gagal') }}</th><th class="text-end">{{ __('Masa') }}</th></tr>
            </thead>
            <tbody>
                @forelse($runs as $run)
                    <tr>
                        <td class="small text-nowrap">{{ ($run->started_at ?? $run->created_at)->format('d/m/Y H:i') }}</td>
                        <td><code class="small">{{ $run->source }}</code></td>
                        <td>@include('admin._run-status', ['run' => $run])</td>
                        <td class="small">
                            @switch($run->trigger)
                                @case('admin') {{ $run->user->name ?? __('Admin') }} @break
                                @case('schedule') {{ __('Jadual mingguan') }} @break
                                @default {{ __('Terminal') }}
                            @endswitch
                        </td>
                        <td class="text-end">{{ $run->created_count }}</td>
                        <td class="text-end">{{ $run->updated_count }}</td>
                        <td class="text-end">{{ $run->with_text }}</td>
                        <td class="text-end">{{ $run->failed_count }}</td>
                        <td class="text-end small text-nowrap">{{ $run->duration() ?? '–' }}</td>
                    </tr>
                    @if($run->error)
                        <tr><td colspan="9" class="small text-danger border-top-0 pt-0"><i class="bi bi-exclamation-circle"></i> {{ \Illuminate\Support\Str::limit($run->error, 300) }}</td></tr>
                    @endif
                @empty
                    <tr><td colspan="9" class="text-muted text-center py-4">{{ __('Belum ada import direkodkan. Import dari terminal juga direkodkan di sini.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $runs->links() }}</div>
@endsection

@if($active)
    @push('scripts')
        <script>setTimeout(function () { location.reload(); }, 15000);</script>
    @endpush
@endif
