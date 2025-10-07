@extends('layouts.app')

@section('title', 'Kelola Karyawan - LaundryKita')

@section('content')
<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar bg-pale-lime border-end" style="width: 250px; min-height: 100vh; background-color: #fff;">
        <div class="p-4 border-bottom">
            <h4 class="fw-bold text-accent">LaundryKita</h4>
            <p class="text-muted">Portal Pemilik</p>
        </div>
        <ul class="nav flex-column px-3">
            <li class="nav-item mb-2">
                <a href="{{ url('/pemilik') }}" class="nav-link text-dark px-3 py-2">
                    <i class="bi bi-grid me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="{{ url('/pemilik/karyawan') }}" class="nav-link active fw-bold text-white rounded px-3 py-2 bg-accent">
                    <i class="bi bi-people me-2"></i> Karyawan
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="{{ url('/pemilik/layanan') }}" class="nav-link text-dark px-3 py-2">
                    <i class="bi bi-gear me-2"></i> Layanan
                </a>
            </li>

            {{-- NEW: History pesanan untuk pemilik --}}
            <li class="nav-item mb-2">
                <a href="{{ route('pemilik.history') }}"
                   class="nav-link text-dark px-3 py-2">
                    <i class="bi bi-clock-history me-2"></i> History
                </a>
            </li>

            {{-- [UTS HOLD] Sembunyikan menu Analitik sementara (jangan hapus; un-comment setelah UTS)
            <li class="nav-item mb-2">
                <a href="{{ url('/pemilik/analitik') }}" class="nav-link text-dark px-3 py-2">
                    <i class="bi bi-bar-chart-line me-2"></i> Analitik
                </a>
            </li>
            --}}
        </ul>
        <div class="mt-auto px-3 pb-4">
            <a href="{{ url('/login') }}" class="btn btn-outline-danger w-100">Keluar</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-grow-1 p-4" style="background: #f1f5f4ff;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-3">Manajemen Karyawan</h2>
                <p class="text-muted mb-0">Atur dan kelola karyawan di LaundryKita</p>
            </div>
            <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#tambahKaryawanModal">
                + Tambah Karyawan
            </button>
        </div>

        {{-- Flash --}}
        @if(session('ok'))
            <div class="alert alert-success">{{ session('ok') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        {{-- === Credensial kasir baru / reset (tampil sekali) === --}}
        @if(session('temp_pass') && session('temp_email'))
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <div>
                    <strong>Informasi Karyawan Baru / Reset:</strong><br>
                    Email: <code>{{ session('temp_email') }}</code><br>
                    Password sementara: <code>{{ session('temp_pass') }}</code>
                    <div class="small text-muted mt-1">
                        Berikan ke karyawan & minta segera ganti password setelah login.
                    </div>
                </div>
            </div>
        @endif

        <!-- Daftar Karyawan -->
        <h5 class="fw-bold mb-3">Daftar Karyawan</h5>

        {{-- LOOP DINAMIS KASIR --}}
        @forelse($kasir as $k)
            <div class="card shadow-sm border-0 rounded-3 p-3 mb-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0">{{ $k->name }}</h6>
                        <small class="text-muted">Karyawan • {{ $k->email }}</small>
                    </div>
                    <div>
                        {{-- Edit --}}
                        <button class="btn btn-sm btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#editKaryawanModal-{{ $k->id }}"
                                data-name="{{ $k->name }}"
                                data-email="{{ $k->email }}">Edit</button>

                        {{-- Reset Password (tambahan) --}}
                        <form action="{{ route('pemilik.karyawan.reset', $k->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary"
                                onclick="return confirm('Reset password karyawan ini?')">
                                Reset Password
                            </button>
                        </form>

                        {{-- Hapus --}}
                        <form class="d-inline" method="POST" action="{{ route('pemilik.karyawan.destroy', $k->id) }}">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"
                                    onclick="return confirm('Karyawan ini akan dihapus. Lanjutkan?')">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Modal Edit (per kasir) --}}
            <div class="modal fade" id="editKaryawanModal-{{ $k->id }}" tabindex="-1" aria-labelledby="editKaryawanLabel-{{ $k->id }}" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content rounded-3 border-0 shadow">
                  <div class="modal-header bg-accent text-white">
                    <h5 class="modal-title" id="editKaryawanLabel-{{ $k->id }}">Edit Data Karyawan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                  </div>
                  <form method="POST" action="{{ route('pemilik.karyawan.update', $k->id) }}">
                    @csrf @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                          <label class="form-label">Nama Lengkap</label>
                          <input type="text" name="name" class="form-control" value="{{ $k->name }}" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Email</label>
                          <input type="email" name="email" class="form-control" value="{{ $k->email }}" required>
                        </div>
                        {{-- Password opsional: tidak ditampilkan agar tulisan/layout tetap sama --}}
                        {{-- <input type="password" name="password" class="form-control" placeholder="Password baru (opsional)"> --}}
                    </div>
                    <div class="modal-footer">
                      <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Batalkan</button>
                      <button class="btn btn-accent" type="submit">Simpan Perubahan</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
        @empty
            <p class="text-muted">Belum ada karyawan.</p>
        @endforelse
    </div>
</div>

<!-- Modal Tambah Karyawan -->
<div class="modal fade" id="tambahKaryawanModal" tabindex="-1" aria-labelledby="tambahKaryawanLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content rounded-3 border-0 shadow">
      <div class="modal-header bg-accent text-white">
        <h5 class="modal-title" id="tambahKaryawanLabel">Tambah Karyawan Baru</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="formTambahKaryawan" method="POST" action="{{ route('pemilik.karyawan.store') }}">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" name="name" class="form-control" placeholder="Masukkan nama lengkap" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" placeholder="Masukkan email" required>
          </div>
          {{-- Agar layout/tulisan tidak berubah, password diset default di controller jika field ini kosong --}}
          {{-- <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control"></div> --}}
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Batalkan</button>
          <button class="btn btn-accent" id="btnTambahKaryawan" type="submit">Tambahkan</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Jika ingin tetap pakai alert setelah response
    @if(session('ok'))
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: @json(session('ok')), confirmButtonColor: '#FF9800' });
    @endif
    @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Gagal!', text: @json(session('error')), confirmButtonColor: '#FF9800' });
    @endif
</script>
@endpush

<style>
    .text-accent { color: #35C8B4 !important; }
    .bg-accent  { background-color: #35C8B4 !important; }
    .btn-accent {
        background: #35C8B4; color: #fff; border-radius: 25px;
        padding: 8px 20px; font-weight: 500; border: none;
    }
    .btn-accent:hover { background: #2ba497; color: #fff; }
    .btn-accent-square {
        background: #35C8B4;
        color: #fff;
        font-weight: 500;
        border: none;
    }
</style>
@endsection
