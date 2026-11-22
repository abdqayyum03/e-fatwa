{{-- One AI answer with its numbered sources. Also rendered client-side from the same JSON fields. --}}
<div class="surface p-4 ask-answer">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="small fw-bold text-uppercase text-muted" style="letter-spacing: .08em"><i class="bi bi-stars text-gold"></i> {{ __('Jawapan') }}</span>
        <span class="tag">{{ __('Ringkasan AI, bukan fatwa') }}</span>
    </div>
    <div class="answer-body">{!! $result['html'] !!}</div>

    @if(! empty($result['sources']))
        <h3 class="h6 mt-4 mb-2">{{ __('Rujukan') }}</h3>
        <ol class="list-unstyled d-grid gap-2 mb-0">
            @foreach($result['sources'] as $source)
                <li class="d-flex gap-2">
                    <span class="badge rounded-pill text-bg-light border align-self-start">{{ $source['n'] }}</span>
                    <div>
                        <a href="{{ $source['url'] }}" class="fw-semibold">{{ $source['title'] }}</a>
                        <div class="small text-muted">{{ $source['state'] }}@if($source['year']) · {{ $source['year'] }}@endif</div>
                    </div>
                </li>
            @endforeach
        </ol>
    @endif
</div>
