@extends('layouts.app')

@section('content')
<div class="p-5 rounded shadow-lg text-white position-relative overflow-hidden" style="
    background: url('{{ asset('images/fatwa-bg.jpg') }}') no-repeat center center;
    background-size: cover;
">
  <div class="dashboard py-5" style="background-color: rgba(248, 249, 250, 0.6);">
    <div class="container">
      <div class="text-center mb-5 px-3 px-md-5">
        <h2 class="display-5 fw-bold">Selamat Datang ke Sistem e-Fatwa</h2>
        <p class="text-muted">Cari, baca dan rujuk fatwa terkini dari pelbagai negeri dan agensi berautoriti Islam di Malaysia.</p>
      </div>

      <!-- Hero Section -->
      <div class="row align-items-center mb-5">
        <div class="col-lg-6 mb-4 mb-lg-0">
          <div class="position-relative rounded overflow-hidden w-100">
            <div class="ratio ratio-16x9">
              <video class="w-100 h-100 rounded" controls>
                <source src="{{ asset('videos/fatwa-hero.mp4') }}" type="video/mp4">
                Your browser does not support the video tag.
              </video>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <h3 class="fw-bold">Pusat Rujukan Fatwa Malaysia</h3>
          <p class="text-muted">
            Fatwa merupakan pandangan hukum atau keputusan rasmi yang dikeluarkan oleh pihak berautoriti Islam sebagai panduan kepada masyarakat dalam menangani isu-isu yang berkaitan dengan akidah, ibadah, muamalat dan kehidupan seharian. 
            Di Malaysia, fatwa hanya boleh diisytiharkan oleh agensi agama yang sah seperti Jabatan Mufti Negeri dan Jabatan Mufti Wilayah Persekutuan. Setiap negeri mempunyai kuasa tersendiri dalam hal ehwal agama Islam, dan oleh itu, 
            fatwa yang diwartakan adalah hasil penelitian mendalam oleh para mufti dan ahli Jawatankuasa Fatwa negeri masing-masing.
          </p>
          <a href="{{ route('fatwas.index') }}" class="btn btn-primary btn-lg mt-3">
            Lihat Senarai Fatwa
          </a>
        </div>
      </div>

      <!-- Quick Access Sections -->
      @php
        $cards = [
            ['title' => 'Fatwa', 'desc' => 'Senarai fatwa terkini dan terdahulu dari seluruh Malaysia.', 'icon' => 'journal-text text-primary', 'route' => 'fatwas.index', 'label' => 'Teroka Fatwa'],
            ['title' => 'Masjid', 'desc' => 'Cari lokasi masjid dan surau berhampiran anda.', 'icon' => 'geo-alt text-success', 'route' => 'mosques.index', 'label' => 'Lihat Peta'],
            ['title' => 'Agensi Penguatkuasa Agama', 'desc' => 'Maklumat agensi penguatkuasa fatwa di seluruh negara.', 'icon' => 'building text-warning', 'route' => 'authorities.index', 'label' => 'Lihat Agensi'],
            ['title' => 'Maklum Balas', 'desc' => 'Hantar sebarang pertanyaan, cadangan atau masalah anda.', 'icon' => 'chat-dots text-danger', 'route' => 'feedbacks.index', 'label' => 'Berikan Maklum Balas'],
        ];
      @endphp

      @foreach ($cards as $card)
      <div class="row mb-4">
        <div class="col-md-12">
          <div class="card h-100 shadow-sm hover-shadow transition">
            <div class="card-body d-flex align-items-center">
              <i class="bi bi-{{ $card['icon'] }} display-4 me-4"></i>
              <div>
                <h5 class="card-title">{{ $card['title'] }}</h5>
                <p class="card-text text-muted">{{ $card['desc'] }}</p>
              </div>
            </div>
            <div class="card-footer bg-transparent border-0 text-end">
              <a href="{{ route($card['route']) }}" class="btn btn-outline-primary">
                {{ $card['label'] }}
              </a>
            </div>
          </div>
        </div>
      </div>
      @endforeach

    </div>
  </div>
</div>

<style>
.hover-shadow:hover {
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
  transform: translateY(-3px);
  transition: 0.3s ease;
}
</style>
@endsection
