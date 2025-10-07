@extends('layouts.app')
@section('title','Riwayat Layanan - LaundryKita')

@section('content')
<div class="d-flex">
  {{-- Sidebar pemilik --}}
  <!-- Sidebar -->
  <div class="sidebar bg-pale-lime border-end" style="width: 250px; min-height: 100vh; background-color: #fff;">
    <div class="p-4 border-bottom">
      <h4 class="fw-bold text-orange">LaundryKita</h4>
      <p class="text-muted">Portal Pemilik</p>
    </div>

    <ul class="nav flex-column px-3 mt-3">
      <li class="nav-item mb-2">
        <a href="{{ url('/pemilik') }}" class="nav-link text-dark px-3 py-2">
          <i class="bi bi-grid me-2"></i> Dashboard
        </a>
      </li>
      <li class="nav-item mb-2">
        <a href="{{ url('/pemilik/karyawan') }}" class="nav-link text-dark px-3 py-2">
          <i class="bi bi-people me-2"></i> Karyawan
        </a>
      </li>
      <li class="nav-item mb-2">
        <a href="{{ url('/pemilik/layanan') }}" class="nav-link text-dark px-3 py-2">
          <i class="bi bi-gear me-2"></i> Layanan
        </a>
      </li>

      {{-- NEW: History Pesanan (riwayat semua pesanan dari kasir) --}}
      <li class="nav-item mb-2">
        <a href="{{ route('pemilik.history') }}"
           class="nav-link text-dark px-3 py-2">
          <i class="bi bi-clock-history me-2"></i> History
        </a>
      </li>

      {{-- Halaman ini: Riwayat Layanan (aktif) --}}
      <li class="nav-item mb-2">
        <a href="{{ route('pemilik.layanan.history') }}"
           class="nav-link active fw-bold text-white rounded px-3 py-2 bg-orange">
          <i class="bi bi-journal-text me-2"></i> Riwayat Layanan
        </a>
      </li>
    </ul>

    <div class="mt-auto px-3 pb-4">
      <a href="{{ url('/login') }}" class="btn btn-outline-danger w-100">Keluar</a>
    </div>
  </div>

  <div class="flex-grow-1 p-4" style="background:#f8f9fa;">
    <h2 class="fw-bold mb-3">Riwayat Perubahan Layanan</h2>

    @if(session('ok'))
      <div class="alert alert-success">{{ session('ok') }}</div>
    @endif

    @forelse($logs as $log)
      <div class="card shadow-sm border-0 rounded-3 p-3 mb-2">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fw-bold text-capitalize">{{ $log->action }}</div>
            <small class="text-muted">
              {{ $log->nama }}
              • Rp {{ number_format((int)($log->harga ?? 0), 0, ',', '.') }}/kg
              • {{ (int)($log->durasi_jam ?? 0) }} jam
              • oleh {{ $log->user->name ?? 'System' }} ({{ $log->user_role ?? '-' }})
              • {{ optional($log->created_at)->format('d M Y H:i') }}
            </small>
          </div>
          <div>
            @if(!empty($log->layanan_id))
              <span class="badge bg-light text-dark">ID: {{ $log->layanan_id }}</span>
            @endif
          </div>
        </div>
      </div>
    @empty
      <p class="text-muted">Belum ada aktivitas.</p>
    @endforelse

    <div class="mt-3">
      {{ $logs->links() }}
    </div>
  </div>
</div>

<style>
  .text-orange { color:#FF9800!important; }
  .bg-orange   { background-color:#FF9800!important; }
</style>
@endsection