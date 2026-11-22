@extends('admin.layout', [
    'heading' => __('Kategori'),
    'subheading' => __('Namakan semula atau gabungkan kategori, dan urus peraturan kata kunci untuk fatwa tanpa kategori.'),
    'icon' => 'tags',
])

@section('title', __('Kategori'))

@section('admin')
{{-- ===== Keyword rules ===== --}}
<div class="surface p-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
        <div>
            <h2 class="h5 mb-1">{{ __('Peraturan kata kunci') }}</h2>
            <p class="small text-muted mb-0">
                {{ __('Fatwa tanpa kategori diberi kategori jika tajuknya mengandungi salah satu kata kunci (perkataan penuh, huruf besar/kecil diabaikan). Keutamaan lebih rendah menang; jika dua kategori sama keutamaan, fatwa itu dibiarkan.') }}
            </p>
        </div>
        <span class="badge text-bg-warning fs-6">{{ number_format($uncategorised) }} {{ __('tanpa kategori') }}</span>
    </div>

    <div class="d-flex gap-2 my-3">
        <form method="POST" action="{{ route('admin.rules.apply') }}">
            @csrf
            <button class="btn btn-outline-primary"><i class="bi bi-eye"></i> {{ __('Pratonton') }}</button>
        </form>
        <form method="POST" action="{{ route('admin.rules.apply') }}" data-confirm="{{ __('Beri kategori kepada fatwa yang sepadan dengan peraturan?') }}">
            @csrf
            <input type="hidden" name="apply" value="1">
            <button class="btn btn-primary"><i class="bi bi-magic"></i> {{ __('Guna peraturan') }}</button>
        </form>
    </div>

    @if($preview)
        <div class="rule-preview mb-3">
            <p class="mb-2">
                <strong>{{ __('Pratonton:') }}</strong>
                {{ __(':total fatwa akan diberi kategori, :ambiguous dibiarkan kerana sepadan dengan dua kategori, :unmatched tidak sepadan dengan mana-mana peraturan.', [
                    'total' => $preview['total'], 'ambiguous' => $preview['ambiguous'], 'unmatched' => $preview['unmatched'],
                ]) }}
            </p>
            @foreach($preview['assigned'] as $name => $items)
                <details class="mb-1">
                    <summary><strong>{{ $name }}</strong> <span class="badge text-bg-light border">{{ count($items) }}</span></summary>
                    <ul class="small mb-2 mt-1">
                        @foreach(array_slice($items, 0, 40) as $item)
                            <li><a href="{{ route('fatwas.show', $item['id']) }}" target="_blank">{{ $item['title'] }}</a></li>
                        @endforeach
                        @if(count($items) > 40)<li class="text-muted">{{ __('… dan :count lagi', ['count' => count($items) - 40]) }}</li>@endif
                    </ul>
                </details>
            @endforeach
        </div>
    @endif

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr><th style="width: 22%">{{ __('Kategori') }}</th><th>{{ __('Kata kunci (dipisahkan koma)') }}</th><th style="width: 90px">{{ __('Keutamaan') }}</th><th style="width: 100px"></th></tr>
            </thead>
            <tbody>
                @foreach($rules as $rule)
                    <tr>
                        <td>
                            <select name="category_id" form="rule-{{ $rule->id }}" class="form-select form-select-sm" aria-label="{{ __('Kategori') }}">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected($rule->category_id === $category->id)>{{ $category->category_name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><textarea name="keywords" form="rule-{{ $rule->id }}" rows="2" class="form-control form-control-sm" aria-label="{{ __('Kata kunci') }}">{{ $rule->keywords }}</textarea></td>
                        <td><input type="number" name="priority" form="rule-{{ $rule->id }}" value="{{ $rule->priority }}" min="1" max="999" class="form-control form-control-sm" aria-label="{{ __('Keutamaan') }}"></td>
                        <td class="text-nowrap">
                            <form id="rule-{{ $rule->id }}" method="POST" action="{{ route('admin.rules.update', $rule) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-outline-primary" title="{{ __('Simpan') }}" aria-label="{{ __('Simpan') }}"><i class="bi bi-save"></i></button>
                            </form>
                            <form method="POST" action="{{ route('admin.rules.destroy', $rule) }}" class="d-inline" data-confirm="{{ __('Padam peraturan ini?') }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="{{ __('Padam') }}" aria-label="{{ __('Padam') }}"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                {{-- New rule --}}
                <tr class="table-light-row">
                    <td>
                        <select name="category_id" form="rule-new" class="form-select form-select-sm" aria-label="{{ __('Kategori') }}" required>
                            <option value="">{{ __('Pilih kategori') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><textarea name="keywords" form="rule-new" rows="2" class="form-control form-control-sm" placeholder="{{ __('cth. zakat, fitrah, nisab') }}" aria-label="{{ __('Kata kunci') }}" required></textarea></td>
                    <td><input type="number" name="priority" form="rule-new" value="50" min="1" max="999" class="form-control form-control-sm" aria-label="{{ __('Keutamaan') }}"></td>
                    <td>
                        <form id="rule-new" method="POST" action="{{ route('admin.rules.store') }}">
                            @csrf
                            <button class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> {{ __('Tambah') }}</button>
                        </form>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- ===== Categories ===== --}}
<div class="surface overflow-hidden">
    <div class="p-4 pb-2">
        <h2 class="h5 mb-1">{{ __('Senarai kategori') }}</h2>
        <p class="small text-muted mb-0">{{ __('Menggabungkan memindahkan semua fatwa (dan peraturan) ke kategori lain, kemudian memadam kategori asal.') }}</p>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr><th>{{ __('Nama') }}</th><th class="text-end" style="width: 80px">{{ __('Fatwa') }}</th><th style="width: 38%">{{ __('Gabung ke dalam') }}</th><th style="width: 60px"></th></tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                    <tr>
                        <td>
                            <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="d-flex gap-1">
                                @csrf @method('PATCH')
                                <input name="category_name" value="{{ $category->category_name }}" class="form-control form-control-sm" aria-label="{{ __('Nama kategori') }}" required maxlength="100">
                                <button class="btn btn-sm btn-outline-primary" title="{{ __('Namakan semula') }}" aria-label="{{ __('Namakan semula') }}"><i class="bi bi-save"></i></button>
                            </form>
                        </td>
                        <td class="text-end"><a href="{{ route('fatwas.index', ['category' => $category->id]) }}">{{ number_format($category->fatwas_count) }}</a></td>
                        <td>
                            <form method="POST" action="{{ route('admin.categories.merge', $category) }}" class="d-flex gap-1"
                                  data-confirm="{{ __('Gabungkan ":name" ke dalam kategori yang dipilih? ":name" akan dipadam.', ['name' => $category->category_name]) }}">
                                @csrf
                                <select name="target_id" class="form-select form-select-sm" aria-label="{{ __('Gabung ke dalam') }}" required>
                                    <option value="">{{ __('Pilih…') }}</option>
                                    @foreach($categories as $other)
                                        @continue($other->id === $category->id)
                                        <option value="{{ $other->id }}">{{ $other->category_name }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-sm btn-outline-secondary text-nowrap"><i class="bi bi-arrow-left-right"></i> {{ __('Gabung') }}</button>
                            </form>
                        </td>
                        <td>
                            @if($category->fatwas_count === 0)
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" data-confirm="{{ __('Padam kategori ":name"?', ['name' => $category->category_name]) }}">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="{{ __('Padam') }}" aria-label="{{ __('Padam') }}"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
