{{-- Prayer-time widget. Filled in by public/js/app.js from route('prayer-times'). --}}
<div class="surface p-4 prayer-widget" data-prayer-widget data-endpoint="{{ route('prayer-times') }}" data-default-zone="{{ \App\Services\PrayerTimes::DEFAULT_ZONE }}">
    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
        <div>
            <div class="small text-muted text-uppercase fw-bold" style="letter-spacing: .08em">{{ __('Waktu Solat Hari Ini') }}</div>
            <div class="small text-muted" data-prayer-date>{{ __('Memuatkan…') }}</div>
        </div>
        <i class="bi bi-moon-stars fs-4 text-gold"></i>
    </div>

    <div class="next-prayer mb-3" data-prayer-next hidden>
        <div class="small">{{ __('Seterusnya:') }} <strong data-prayer-next-name></strong> <span data-prayer-next-time></span></div>
        <div class="countdown" data-prayer-countdown aria-live="off">--:--:--</div>
    </div>

    <ul class="prayer-list" data-prayer-list>
        @foreach(\App\Services\PrayerTimes::PRAYERS as $key => $label)
            <li data-prayer="{{ $key }}"><span>{{ $label }}</span><b>--:--</b></li>
        @endforeach
    </ul>

    <label class="form-label small text-muted mt-3 mb-1" for="prayerZone">{{ __('Zon') }}</label>
    <select class="form-select form-select-sm" id="prayerZone" data-prayer-zone>
        @foreach(\App\Services\PrayerTimes::zones() as $state => $areas)
            <optgroup label="{{ $state }}">
                @foreach($areas as $code => $name)
                    <option value="{{ $code }}">{{ $code }} · {{ \Illuminate\Support\Str::limit($name, 48) }}</option>
                @endforeach
            </optgroup>
        @endforeach
    </select>
    <p class="small text-muted mt-2 mb-0" data-prayer-error hidden></p>
    <p class="small text-muted mt-2 mb-0">{{ __('Sumber:') }} <a href="https://www.e-solat.gov.my" target="_blank" rel="noopener">{{ __('e-Solat JAKIM') }}</a></p>
</div>
