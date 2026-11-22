@extends('admin.layout', [
    'heading' => __('Agensi'),
    'subheading' => __('Nama, logo, alamat dan laman web jabatan mufti yang dipaparkan di laman awam.'),
    'icon' => 'bank',
])

@section('title', __('Agensi'))

@section('admin')
<div class="surface overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr><th style="width: 56px"></th><th>{{ __('Agensi') }}</th><th>{{ __('Negeri') }}</th><th class="text-end">{{ __('Fatwa') }}</th><th>{{ __('Laman web') }}</th><th></th></tr>
            </thead>
            <tbody>
                @foreach($authorities as $authority)
                    <tr>
                        <td>
                            @if($authority->logo)
                                <img src="{{ asset('images/authorities/'.$authority->logo) }}" alt="" style="width: 40px; height: 40px; object-fit: contain">
                            @else
                                <div class="admin-mono" style="--h: {{ crc32($authority->state ?? $authority->authority_name) % 360 }}">
                                    {{ \Illuminate\Support\Str::of($authority->state ?? $authority->authority_name)->explode(' ')->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->join('') }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('authorities.show', $authority) }}" target="_blank" class="fw-semibold text-decoration-none">{{ $authority->authority_name }}</a>
                            <div class="small text-muted">{{ collect([$authority->street, $authority->city])->filter()->join(', ') ?: __('Tiada alamat') }}</div>
                        </td>
                        <td><x-state-pill :state="$authority->state" /></td>
                        <td class="text-end">{{ number_format($authority->fatwas_count) }}</td>
                        <td class="small">
                            @if($authority->website)
                                <a href="{{ $authority->website }}" target="_blank" rel="noopener">{{ parse_url($authority->website, PHP_URL_HOST) }}</a>
                            @else
                                <span class="text-muted">–</span>
                            @endif
                        </td>
                        <td class="text-end"><a href="{{ route('admin.authorities.edit', $authority) }}" class="btn btn-sm btn-outline-primary text-nowrap"><i class="bi bi-pencil"></i> {{ __('Edit') }}</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
