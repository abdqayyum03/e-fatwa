@extends('admin.layout', [
    'heading' => __('Admin'),
    'subheading' => __('Gambaran keseluruhan pangkalan data, import dan pengguna.'),
    'icon' => 'speedometer2',
])

@section('title', __('Admin'))

@section('admin')
{{-- Totals --}}
<div class="surface mb-4">
    <div class="row g-0">
        @foreach([
            [$totals['fatwas'], __('Fatwa')],
            [$totals['with_text'], __('Dengan teks penuh')],
            [$totals['ocr'], __('Dibaca dengan OCR')],
            [$totals['with_decision'], __('Keputusan dikesan')],
            [$totals['users'], __('Pengguna')],
            [$totals['admins'], __('Pentadbir')],
        ] as [$num, $label])
            <div class="col-6 col-md-4 col-xl-2 stat">
                <div class="num" style="font-size: 1.7rem">{{ number_format($num) }}</div>
                <div class="lbl">{{ $label }}</div>
            </div>
        @endforeach
    </div>
</div>

<div class="row g-4">
    {{-- Quality --}}
    <div class="col-md-6">
        <div class="surface p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">{{ __('Perlu perhatian') }}</h2>
                <a href="{{ route('admin.quality.index') }}" class="small">{{ __('Buka baris gilir') }} <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="list-group list-group-flush">
                @foreach(\App\Services\FatwaQuality::ISSUES as $key => [$label, $icon])
                    <a href="{{ route('admin.quality.index', ['isu' => $key]) }}" class="list-group-item list-group-item-action d-flex align-items-center px-0">
                        <i class="bi bi-{{ $icon }} me-2 text-muted"></i>{{ __($label) }}
                        <span class="badge rounded-pill {{ $quality[$key] ? 'text-bg-warning' : 'text-bg-light border' }} ms-auto">{{ number_format($quality[$key]) }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Imports --}}
    <div class="col-md-6">
        <div class="surface p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">{{ __('Import terkini') }}</h2>
                <a href="{{ route('admin.imports.index') }}" class="small">{{ __('Semua import') }} <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <tbody>
                        @foreach($sources as $key => $state)
                            @php($run = $latestRuns[$key] ?? null)
                            <tr>
                                <td><x-state-pill :state="$state" /></td>
                                <td>@include('admin._run-status', ['run' => $run])</td>
                                <td class="small text-muted text-end">{{ $run?->finished_at?->diffForHumans() ?? ($run?->started_at?->diffForHumans() ?? '–') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- New fatwas --}}
    <div class="col-12">
        <div class="surface overflow-hidden">
            <div class="d-flex justify-content-between align-items-center p-4 pb-2">
                <h2 class="h5 mb-0">{{ __('Fatwa baharu minggu ini') }} <span class="badge text-bg-light border">{{ $newFatwaCount }}</span></h2>
            </div>
            @forelse($newFatwas as $fatwa)
                <a href="{{ route('fatwas.show', $fatwa) }}" class="fatwa-item">
                    <div class="flex-grow-1" style="min-width: 0">
                        <div class="title text-truncate">{{ $fatwa->title }}</div>
                        <div class="meta-row">
                            <x-state-pill :state="$fatwa->authority->state ?? null" />
                            <span class="tag">{{ $fatwa->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </a>
            @empty
                <p class="text-muted px-4 pb-4 mb-0">{{ __('Tiada fatwa baharu dalam 7 hari lepas.') }}</p>
            @endforelse
        </div>
    </div>

    {{-- Feedback --}}
    <div class="col-md-6">
        <div class="surface p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">{{ __('Maklum balas baharu') }} <span class="badge {{ $unreadFeedbackCount ? 'text-bg-warning' : 'text-bg-light border' }}">{{ $unreadFeedbackCount }}</span></h2>
                <a href="{{ route('admin.feedback.index') }}" class="small">{{ __('Peti masuk') }} <i class="bi bi-arrow-right"></i></a>
            </div>
            @forelse($unreadFeedback as $item)
                <div class="py-2 @if(! $loop->last) border-bottom @endif">
                    <div class="d-flex justify-content-between small"><strong>{{ $item->name }}</strong><span class="text-muted">{{ $item->created_at->diffForHumans() }}</span></div>
                    <div class="small text-muted text-truncate">{{ $item->content }}</div>
                </div>
            @empty
                <p class="text-muted mb-0">{{ __('Tiada maklum balas yang belum dibaca.') }}</p>
            @endforelse
        </div>
    </div>

    {{-- Users --}}
    <div class="col-md-6">
        <div class="surface p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">{{ __('Pengguna baharu') }}</h2>
                <a href="{{ route('admin.users.index') }}" class="small">{{ __('Semua pengguna') }} <i class="bi bi-arrow-right"></i></a>
            </div>
            @forelse($newUsers as $user)
                <div class="d-flex justify-content-between py-2 small @if(! $loop->last) border-bottom @endif">
                    <span class="text-truncate"><strong>{{ $user->name }}</strong> <span class="text-muted">{{ $user->email }}</span></span>
                    <span class="text-muted ms-2 text-nowrap">{{ $user->created_at->diffForHumans() }}</span>
                </div>
            @empty
                <p class="text-muted mb-0">{{ __('Tiada pendaftaran baharu minggu ini.') }}</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
