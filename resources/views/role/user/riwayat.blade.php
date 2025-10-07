@extends('layouts.app')

@section('title', 'Riwayat Pesanan - LaundryKita')

@section('content')
<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar bg-white border-end" style="width: 250px; min-height: 100vh;">
        <div class="p-4">
            <h4 class="fw-bold text-accent">LaundryKita</h4>
            <p class="text-muted">Portal Pelanggan</p>
        </div>
        <ul class="nav flex-column px-3">
            <li class="nav-item mb-2">
                <a href="{{ url('/user') }}" class="nav-link text-dark px-3 py-2">
                    <i class="bi bi-grid me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="{{ url('/user/riwayat') }}" class="nav-link active fw-bold text-white rounded px-3 py-2" style="background: #FF9800;">
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
        <h2 class="fw-bold">Riwayat Pesanan</h2>
        <p class="text-muted mb-4">Lacak dan kelola pesanan laundry Anda</p>

        <!-- Filter Tanggal -->
        <div class="card shadow-sm border-0 rounded-3 p-3 mb-3">
            <form class="row g-2">
                <div class="col-md-4">
                    <input type="date" class="form-control" name="tanggal">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-orange w-100">
                        <i class="bi bi-search me-1"></i> Cari
                    </button>
                </div>
            </form>
        </div>

        <!-- Daftar Pesanan -->
        <div class="card shadow-sm border-0 rounded-3 p-3">
            <h5 class="fw-bold mb-3">Daftar Pesanan</h5>

            <!-- Item Pesanan -->
            <div class="d-flex justify-content-between align-items-center border rounded p-3 mb-2">
                <div>
                    <h6 class="fw-bold mb-0">ORD001</h6>
                    <small class="text-muted">Wash & Fold • 5.5 kg • 14/01/2024</small>
                </div>
                <div class="text-end">
                    <span class="badge" style="background:#FF9800;">Diproses</span>
                    <button class="btn btn-sm btn-outline-primary ms-2" data-bs-toggle="modal" data-bs-target="#orderModal">
                        <i class="bi bi-eye me-1"></i> Detail
                    </button>
                    <p class="fw-bold mb-0">Rp27.500</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="orderModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content p-4">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">Detail Pesanan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">

        <div class="row mb-3">
          <div class="col-md-6"><strong>Order ID:</strong> ORD001</div>
          <div class="col-md-6"><strong>Status:</strong> <span class="badge bg-warning text-dark">Diproses</span></div>
          <div class="col-md-6"><strong>Layanan:</strong> Wash & Fold</div>
          <div class="col-md-6"><strong>Berat:</strong> 5.5 kg</div>
          <div class="col-md-6"><strong>Total Biaya:</strong> Rp27.500</div>
          <div class="col-md-6"><strong>Status Pembayaran:</strong> <span class="text-success fw-bold">Lunas</span></div>
        </div>

        <!-- Progres dengan garis oranye -->
        <div class="mb-3">
            <h6 class="fw-bold">Progres</h6>
            <div class="progress mb-2" style="height: 8px;">
                <div class="progress-bar" role="progressbar" style="width: 50%; background:#FF9800;"></div>
            </div>
            <div class="d-flex justify-content-between">
                <span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Diterima</span>
                <span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Diproses</span>
                <span class="text-muted"><i class="bi bi-circle me-1"></i> Siap</span>
                <span class="text-muted"><i class="bi bi-circle me-1"></i> Selesai</span>
            </div>
        </div>

        <hr>
        <p><strong>Tanggal Pesanan:</strong> 14/01/2024, 16:00</p>
        <p><strong>Terakhir Diperbarui:</strong> 15/01/2024, 09:00</p>
      </div>
    </div>
  </div>
</div>

<style>
    .text-accent { color: #FF9800 !important; }
    .btn-orange {
        background: #FF9800; color: #fff;
        border-radius: 25px; padding: 8px 20px;
        font-weight: 500; border: none;
    }
    .btn-orange:hover { background: #e68900; color: #fff; }
</style>
@endsection