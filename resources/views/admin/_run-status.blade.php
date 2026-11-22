{{-- Status badge for an ImportRun ($run may be null: never imported). --}}
@php
    $styles = [
        'queued' => ['text-bg-secondary', 'hourglass-split', __('Dalam baris gilir')],
        'running' => ['text-bg-info', 'arrow-repeat', __('Sedang berjalan')],
        'done' => ['text-bg-success', 'check-circle', __('Selesai')],
        'failed' => ['text-bg-danger', 'x-circle', __('Gagal')],
    ];
@endphp
@if($run)
    @php([$class, $icon, $label] = $styles[$run->status] ?? ['text-bg-light', 'question-circle', $run->status])
    <span class="badge {{ $class }}" @if($run->error) title="{{ $run->error }}" @endif><i class="bi bi-{{ $icon }}"></i> {{ $label }}</span>
@else
    <span class="badge text-bg-light border">{{ __('Belum pernah') }}</span>
@endif
