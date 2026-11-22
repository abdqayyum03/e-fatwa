@extends('admin.layout', [
    'heading' => __('Pengguna'),
    'subheading' => __('Akaun berdaftar, hak pentadbir dan akaun yang dinyahaktifkan.'),
    'icon' => 'people',
])

@section('title', __('Pengguna'))

@section('admin')
<form method="GET" class="d-flex gap-2 mb-3" role="search">
    <input type="search" name="cari" value="{{ $search }}" class="form-control" placeholder="{{ __('Cari nama atau e-mel') }}" aria-label="{{ __('Cari nama atau e-mel') }}">
    <button class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
</form>

<div class="surface overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr><th>{{ __('Akaun') }}</th><th>{{ __('Peranan') }}</th><th>{{ __('Makluman') }}</th><th class="text-end">{{ __('Simpanan') }}</th><th>{{ __('Daftar') }}</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    @php($isMe = $user->is(auth()->user()))
                    <tr @if($user->isDisabled()) class="opacity-50" @endif>
                        <td>
                            <div class="fw-semibold">{{ $user->name }} @if($isMe)<span class="small text-muted">({{ __('anda') }})</span>@endif</div>
                            <div class="small text-muted">{{ $user->email }}</div>
                        </td>
                        <td>
                            @if($user->is_admin)<span class="badge text-bg-primary">{{ __('Peranan: pentadbir') }}</span>@else<span class="badge text-bg-light border">{{ __('Peranan: pengguna') }}</span>@endif
                            @if($user->isDisabled())<span class="badge text-bg-danger">{{ __('Dinyahaktifkan') }}</span>@endif
                        </td>
                        <td>@if($user->alerts_enabled)<i class="bi bi-bell-fill text-success" title="{{ __('Makluman e-mel aktif') }}"></i>@else<i class="bi bi-bell-slash text-muted" title="{{ __('Tiada makluman') }}"></i>@endif</td>
                        <td class="text-end">{{ $user->bookmarks_count }}</td>
                        <td class="small text-nowrap" title="{{ $user->created_at->format('d/m/Y H:i') }}">{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="text-end text-nowrap">
                            @unless($isMe)
                                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="d-inline"
                                      data-confirm="{{ $user->is_admin ? __('Buang hak pentadbir :name?', ['name' => $user->name]) : __('Jadikan :name pentadbir? Pentadbir boleh mengubah dan memadam semua data.', ['name' => $user->name]) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="action" value="admin">
                                    <button class="btn btn-sm btn-outline-primary">{{ $user->is_admin ? __('Buang admin') : __('Jadikan admin') }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="d-inline"
                                      @unless($user->isDisabled()) data-confirm="{{ __('Nyahaktifkan akaun :name? Mereka tidak boleh log masuk sehingga diaktifkan semula.', ['name' => $user->name]) }}" @endunless>
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="action" value="disable">
                                    <button class="btn btn-sm {{ $user->isDisabled() ? 'btn-outline-success' : 'btn-outline-danger' }}">{{ $user->isDisabled() ? __('Aktifkan') : __('Nyahaktifkan') }}</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">{{ __('Tiada pengguna dijumpai.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $users->links() }}</div>
@endsection
