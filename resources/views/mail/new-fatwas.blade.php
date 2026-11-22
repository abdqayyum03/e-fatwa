<x-mail::message>
# {{ __('Assalamualaikum, :name', ['name' => $user->name]) }}

{{ __('Berikut ialah fatwa baharu yang ditambah ke e-Fatwa sejak makluman terakhir anda.') }}

@foreach($listed as $fatwa)
**[{{ $fatwa->title }}]({{ route('fatwas.show', $fatwa) }})**
{{ $fatwa->authority->state ?? '' }}@if($fatwa->issued_year) · {{ $fatwa->issued_year }}@endif @if($fatwa->category) · {{ $fatwa->category->category_name }}@endif

@endforeach
@if($more > 0)
{{ __('…dan :count lagi.', ['count' => $more]) }}
@endif

<x-mail::button :url="route('fatwas.index')">
{{ __('Lihat semua fatwa') }}
</x-mail::button>

{{ __('Anda menerima e-mel ini kerana anda melanggan makluman di e-Fatwa.') }}
[{{ __('Tukar tetapan makluman') }}]({{ route('account.edit') }}) · [{{ __('Berhenti melanggan') }}]({{ $unsubscribeUrl }})

{{ config('app.name') }}
</x-mail::message>
