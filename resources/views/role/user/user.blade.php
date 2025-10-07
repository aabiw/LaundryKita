@extends('layouts.app')

@section('title', 'Dashboard Pengguna - LaundryKita')

@section('content')
<div class="d-flex">
    <!-- Sidebar User -->
    <div class="sidebar bg-white border-end" style="width: 250px; min-height: 100vh;">
        <div class="p-4">
            <h4 class="fw-bold text-accent">LaundryKita</h4>
            <p class="text-muted">Portal Pelanggan</p>
        </div>
        <ul class="nav flex-column px-3">
            <li class="nav-item mb-2">
                <a href="{{ url('/user') }}" class="nav-link active fw-bold text-white rounded px-3 py-2" style="background: #FF9800;">
                    <i class="bi bi-grid me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="{{ url('/user/riwayat') }}" class="nav-link text-dark px-3 py-2">
                    <i class="bi bi-bag-check me-2"></i> Riwayat Pesanan
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="{{ url('/user/profile') }}" class="nav-link text-dark px-3 py-2">
                    <i class="bi bi-person me-2"></i> Profil
                </a>
            </li>
        </ul>
        <div class="mt-auto px-3 pb-4">
            <a href="{{ url('/login') }}" class="btn btn-outline-danger w-100">
                <i class="bi bi-box-arrow-right me-2"></i> Keluar
            </a>
        </div>
    </div>

    <!-- Konten -->
    <div class="flex-grow-1 p-4" style="background: #f8f9fa;">
        <h2 class="fw-bold">Selamat datang kembali, John Doe!</h2>
        <p class="text-muted mb-4">Berikut adalah ringkasan aktivitas laundry Anda</p>

        <!-- Statistik -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-3 p-3 text-center">
                    <h6 class="text-muted">Pesanan Aktif</h6>
                    <h3 class="fw-bold text-accent">1</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-3 p-3 text-center">
                    <h6 class="text-muted">Pesanan Selesai</h6>
                    <h3 class="fw-bold text-success">1</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-3 p-3 text-center">
                    <h6 class="text-muted">Total Pengeluaran</h6>
                    <h3 class="fw-bold text-dark">Rp150.000</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-3 p-3 text-center">
                    <h6 class="text-muted">Rata-rata Waktu Proses</h6>
                    <h3 class="fw-bold text-dark">24 Jam</h3>
                </div>
            </div>
        </div>

        <!-- Pesanan Terbaru -->
        <div class="card shadow-sm border-0 rounded-3 p-3">
            <h5 class="fw-bold mb-3">Pesanan Terbaru</h5>
            <div class="d-flex justify-content-between align-items-center border rounded p-3 mb-2">
                <div>
                    <h6 class="fw-bold mb-0">ORD001</h6>
                    <small class="text-muted">Wash & Fold • 5.5 kg • 14/01/2024</small>
                </div>
                <div class="text-end">
                    <span class="badge" style="background:#FF9800;">Sedang Diproses</span>
                    <p class="fw-bold mb-0">Rp27.500</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .text-accent {
        color: #FF9800 !important;
    }
    .bg-accent {
        background-color: #FF9800 !important;
    }
    .btn-orange {
        background: #FF9800;
        color: #fff;
        border-radius: 25px;
        padding: 8px 20px;
        font-weight: 500;
        border: none;
    }
    .btn-orange:hover {
        background: #e68900;
        color: #fff;
    }
</style>
@endsection