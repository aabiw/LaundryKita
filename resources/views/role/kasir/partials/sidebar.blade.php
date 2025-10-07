{{-- resources/views/role/kasir/partials/sidebar.blade.php --}}
<div class="sidebar bg-white border-end d-flex flex-column" style="width:250px; min-height:100vh;">
  <div class="p-4 border-bottom">
    <h4 class="fw-bold text-accent m-0">LaundryKita</h4>
    <p class="text-muted mb-0">Portal Karyawan</p>
  </div>

  <ul class="nav flex-column px-3 py-3">
    <li class="nav-item mb-2">
      <a href="{{ route('kasir.dashboard') }}"
         class="nav-link px-3 py-2 {{ request()->routeIs('kasir.dashboard') ? 'active fw-bold text-white rounded bg-accent' : 'text-dark' }}">
        <i class="bi bi-grid me-2"></i><span>Dashboard</span>
      </a>
    </li>

    <li class="nav-item mb-2">
      <a href="{{ route('kasir.pesanan.create') }}"
         class="nav-link px-3 py-2 {{ request()->routeIs('kasir.pesanan.create') ? 'active fw-bold text-white rounded bg-accent' : 'text-dark' }}">
        <i class="bi bi-plus-lg me-2"></i><span>Pesanan Baru</span>
      </a>
    </li>

    <li class="nav-item mb-2">
      <a href="{{ route('kasir.pesanan.index') }}"
         class="nav-link px-3 py-2 {{ request()->routeIs('kasir.pesanan.index') ? 'active fw-bold text-white rounded bg-accent' : 'text-dark' }}">
        <i class="bi bi-file-text me-2"></i><span>Pesanan Aktif</span>
      </a>
    </li>

    <li class="nav-item mb-2">
      <a href="{{ route('kasir.pembayaran.index') }}"
         class="nav-link px-3 py-2 {{ request()->routeIs('kasir.pembayaran.*') ? 'active fw-bold text-white rounded bg-accent' : 'text-dark' }}">
        <i class="bi bi-credit-card me-2"></i><span>Status Pembayaran</span>
      </a>
    </li>

    <li class="nav-item mb-2">
  <a href="{{ route('kasir.history') }}"
     class="nav-link px-3 py-2 {{ request()->routeIs('kasir.history') ? 'active fw-bold text-white rounded bg-accent' : 'text-dark' }}">
    <i class="bi bi-clock-history me-2"></i><span>History Pesanan</span>
  </a>
</li>

    <li class="nav-item mb-2">
      <a href="{{ route('kasir.profil.edit') }}"
         class="nav-link px-3 py-2 {{ request()->routeIs('kasir.profil.*') ? 'active fw-bold text-white rounded bg-accent' : 'text-dark' }}">
        <i class="bi bi-person me-2"></i><span>Profil</span>
      </a>
    </li>
    


    {{-- Tombol keluar tepat di bawah Profil --}}
    <li class="nav-item">
      <a href="{{ route('logout') }}"
         onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
         class="btn btn-outline-danger w-100">
        <i class="bi bi-box-arrow-right me-2"></i> Keluar
      </a>
      <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
      </form>
    </li>
  </ul>
</div>

@push('styles')
<style>
  .text-accent{color:#FF9800!important;}
  .bg-accent{background-color:#FF9800!important;}
  /* Biar label menu tidak turun baris */
  .sidebar .nav-link{white-space:nowrap; display:flex; align-items:center;}
  .sidebar .nav-link i{flex:0 0 auto;}
  .sidebar .nav-link span{flex:1 1 auto;}
</style>
@endpush