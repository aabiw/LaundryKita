@extends('layouts.app')

@section('title', 'Profil - LaundryKita')

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
                <a href="{{ url('/user/riwayat') }}" class="nav-link text-dark px-3 py-2">
                    <i class="bi bi-bag-check me-2"></i> Riwayat Pesanan
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="{{ url('/user/profile') }}" class="nav-link active fw-bold text-white rounded px-3 py-2 bg-accent">
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
        <h2 class="fw-bold">Pengaturan Profil</h2>
        <p class="text-muted mb-4">Atur informasi akun dan preferensi Anda</p>

        <div class="row">
            <!-- Informasi Pribadi -->
            <div class="col-md-6 mb-3">
                <div class="card border-0 shadow-sm rounded-3 p-3">
                    <h5 class="fw-bold mb-3"><i class="bi bi-person me-2"></i>Informasi Pribadi</h5>
                    <form id="profileForm">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" value="John Doe">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat Email</label>
                            <input type="email" class="form-control" value="john@example.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor Telepon</label>
                            <input type="text" class="form-control" value="+6281234567890">
                        </div>
                    </form>
                </div>
            </div>

            <!-- Keamanan -->
            <div class="col-md-6 mb-3">
                <div class="card border-0 shadow-sm rounded-3 p-3">
                    <h5 class="fw-bold mb-3"><i class="bi bi-shield-lock me-2"></i>Pengaturan Keamanan</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Kata Sandi</label>
                        <p class="text-muted">Kata sandi terakhir diperbarui pada 1 Januari 2024</p>
                        <button class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#passwordModal">
                            Ubah Kata Sandi
                        </button>
                    </div>

                    <hr>

                    <div>
                        <h6 class="fw-bold">Keamanan Akun</h6>
                        <p class="text-accent mb-0"><i class="bi bi-check-circle-fill me-1"></i> Aman</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol Simpan/Edit -->
        <div class="text-end mt-3">
            <button class="btn btn-orange px-4" onclick="saveProfile()">Simpan Perubahan</button>
        </div>
    </div>
</div>

<!-- Modal Ubah Password -->
<div class="modal fade" id="passwordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Ubah Kata Sandi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="passwordForm">
                    <div class="mb-3">
                        <label class="form-label">Kata Sandi Lama</label>
                        <input type="password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kata Sandi Baru</label>
                        <input type="password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" class="form-control">
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-orange" onclick="changePassword()">Simpan Kata Sandi</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notifikasi -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055">
    <div id="successToast" class="toast align-items-center text-white bg-accent border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body fw-bold">
                🎉 Profil Anda berhasil diperbarui!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
    <div id="passwordToast" class="toast align-items-center text-white bg-accent border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body fw-bold">
                🔑 Kata sandi Anda berhasil diubah!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
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

<script>
    function saveProfile() {
        var toast = new bootstrap.Toast(document.getElementById('successToast'));
        toast.show();
    }

    function changePassword() {
        var modal = bootstrap.Modal.getInstance(document.getElementById('passwordModal'));
        modal.hide();

        var toast = new bootstrap.Toast(document.getElementById('passwordToast'));
        toast.show();
    }
</script>
@endsection