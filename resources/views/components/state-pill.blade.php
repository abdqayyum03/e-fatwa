@props(['state'])
{{-- Coloured pill per state; the hue is derived from the state name so each state keeps its colour everywhere. --}}
@if($state)
    <span {{ $attributes->merge(['class' => 'state-pill']) }} style="--h: {{ crc32($state) % 360 }}">{{ $state }}</span>
@endif
