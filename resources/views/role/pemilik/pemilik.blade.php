@extends('layouts.app')

@section('title', 'Dashboard Pemilik - LaundryKita')

@section('content')
<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar bg-pale-lime border-end" style="width: 250px; min-height: 100vh; background-color: #fff;">
        <div class="p-4 border-bottom">
            <h4 class="fw-bold text-accent">LaundryKita</h4>
            <p class="text-muted">Portal Pemilik</p>
        </div>
        <ul class="nav flex-column px-3 mt-3">
            <li class="nav-item mb-2">
                <a href="{{ route('pemilik.dashboard') }}"
                   class="nav-link px-3 py-2 {{ request()->routeIs('pemilik.dashboard') ? 'active fw-bold text-white rounded bg-accent' : 'text-dark' }}">
                    <i class="bi bi-grid me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="{{ route('pemilik.karyawan') }}"
                   class="nav-link px-3 py-2 {{ request()->routeIs('pemilik.karyawan*') ? 'active fw-bold text-white rounded bg-accent' : 'text-dark' }}">
                    <i class="bi bi-people me-2"></i> Karyawan
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="{{ route('pemilik.layanan.index') }}"
                   class="nav-link px-3 py-2 {{ request()->routeIs('pemilik.layanan.*') ? 'active fw-bold text-white rounded bg-accent' : 'text-dark' }}">
                    <i class="bi bi-gear me-2"></i> Layanan
                </a>
            </li>

            {{-- NEW: History --}}
            <li class="nav-item mb-2">
                <a href="{{ route('pemilik.history') }}"
                   class="nav-link px-3 py-2 {{ request()->routeIs('pemilik.history') ? 'active fw-bold text-white rounded bg-accent' : 'text-dark' }}">
                    <i class="bi bi-clock-history me-2"></i> History
                </a>
            </li>

            {{-- [UTS HOLD] Sembunyikan menu Analitik sementara (jangan hapus, cukup un-comment setelah UTS)
            <li class="nav-item mb-2">
                <a href="{{ route('pemilik.analitik') }}"
                   class="nav-link px-3 py-2 {{ request()->routeIs('pemilik.analitik*') ? 'active fw-bold text-white rounded bg-accent' : 'text-dark' }}">
                    <i class="bi bi-bar-chart-line me-2"></i> Analitik
                </a>
            </li>
            --}}
        </ul>

        <div class="mt-auto px-3 pb-4">
            <a href="{{ url('/login') }}" class="btn btn-outline-danger w-100">Keluar</a>
        </div>
    </div>

    <!-- Konten Utama -->
    <div class="flex-grow-1 p-4" style="background: #f1f5f4ff;">
        <h2 class="fw-bold mb-3">Ringkasan Bisnis</h2>
        <p class="text-muted">Selamat datang kembali, Pemilik! Berikut performa bisnis laundry kamu.</p>

        <!-- Kartu Statistik -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body">
                        <h6 class="text-muted">Pendapatan Hari Ini</h6>
                        <h4 class="fw-bold text-accent">Rp{{ number_format($pendapatanHariIni ?? 0, 0, ',', '.') }}</h4>
                        {{-- <small class="text-success">+{{ $growthPendapatan ?? 0 }}% dari kemarin</small> --}}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body">
                        <h6 class="text-muted">Pesanan Hari Ini</h6>
                        <h4 class="fw-bold text-brand">{{ $pesananHariIni ?? 0 }}</h4>
                        {{-- <small class="text-success">+{{ $growthPesanan ?? 0 }}% dari kemarin</small> --}}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body">
                        <h6 class="text-muted">Pesanan Aktif</h6>
                        <h4 class="fw-bold text-accent">{{ $pesananAktif ?? 0 }}</h4>
                        <small class="text-muted">Sedang diproses</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body">
                        <h6 class="text-muted">Karyawan Aktif</h6>
                        <h4 class="fw-bold text-brand">{{ $karyawanAktif ?? 0 }}</h4>
                        <small class="text-muted">Sedang bekerja</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ringkasan & Layanan -->
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3 text-accent">Ringkasan Pendapatan</h6>
                        <p class="text-muted">Detail pendapatan berdasarkan periode waktu</p>
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="fw-bold text-brand">Rp{{ number_format(($ringkasan['hari'] ?? 0), 0, ',', '.') }}</h5>
                                <small>Hari Ini</small>
                            </div>
                            <div>
                                <h5 class="fw-bold text-accent">Rp{{ number_format(($ringkasan['minggu'] ?? 0), 0, ',', '.') }}</h5>
                                <small>Minggu Ini</small>
                            </div>
                            <div>
                                <h5 class="fw-bold text-brand">Rp{{ number_format(($ringkasan['bulan'] ?? 0), 0, ',', '.') }}</h5>
                                <small>Bulan Ini</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Layanan Terpopuler -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3 text-accent">Layanan Terpopuler</h6>
                        @php $rank = 1; @endphp
                        @forelse(($layananTop ?? []) as $row)
                            <p class="mb-1">{{ $rank++ }}. {{ $row['nama'] }}
                                <span class="{{ $rank==3 ? 'text-accent' : 'text-brand' }}">
                                    {{ $row['pesanan'] }} pesanan
                                </span>
                            </p>
                        @empty
                            <p class="mb-1">1. <span class="text-muted">Belum ada layanan.</span></p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .text-brand { color: #0a3d62; }
    .text-accent { color: #35C8B4; }
    .bg-accent { background-color: #35C8B4 !important; }
</style>
@endpush
