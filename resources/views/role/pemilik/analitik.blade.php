@extends('layouts.app')

@section('title', 'Analitik - LaundryKita')

@section('content')
<div class="d-flex">
  {{-- Sidebar Pemilik --}}
  <!-- Sidebar -->
  <div class="sidebar bg-pale-lime border-end" style="width: 250px; min-height: 100vh; background-color: #fff;">
    <div class="p-4 border-bottom">
      <h4 class="fw-bold text-orange">LaundryKita</h4>
      <p class="text-muted">Portal Pemilik</p>
    </div>

    <ul class="nav flex-column px-3 mt-3">
      <li class="nav-item mb-2">
        <a href="{{ url('/pemilik') }}"
           class="nav-link text-dark px-3 py-2">
          <i class="bi bi-grid me-2"></i> Dashboard
        </a>
      </li>

      <li class="nav-item mb-2">
        <a href="{{ url('/pemilik/karyawan') }}"
           class="nav-link text-dark px-3 py-2">
          <i class="bi bi-people me-2"></i> Karyawan
        </a>
      </li>

      <li class="nav-item mb-2">
        <a href="{{ url('/pemilik/layanan') }}"
           class="nav-link text-dark px-3 py-2">
          <i class="bi bi-gear me-2"></i> Layanan
        </a>
      </li>

      {{-- NEW: History Pesanan --}}
      <li class="nav-item mb-2">
        <a href="{{ route('pemilik.history') }}"
           class="nav-link {{ request()->routeIs('pemilik.history') ? 'active fw-bold text-white rounded px-3 py-2 bg-orange' : 'text-dark px-3 py-2' }}">
          <i class="bi bi-clock-history me-2"></i> History
        </a>
      </li>

      {{-- Menu Analitik (aktif di halaman ini) --}}
      <li class="nav-item mb-2">
        <a href="{{ route('pemilik.analitik') }}"
           class="nav-link active fw-bold text-white rounded px-3 py-2 bg-orange">
          <i class="bi bi-bar-chart-line me-2"></i> Analitik
        </a>
      </li>
    </ul>

    <div class="mt-auto px-3 pb-4">
      <a href="{{ url('/login') }}" class="btn btn-outline-danger w-100">
        <i class="bi bi-box-arrow-right me-2"></i> Keluar
      </a>
    </div>
  </div>

  {{-- Konten Analitik --}}
  <div class="flex-grow-1 p-4" style="background:#f8f9fa;">
    <h2 class="fw-bold mb-3 text-orange">Analitik Bisnis</h2>
    <p class="text-muted mb-4">Pantau kinerja bisnis laundry kamu berdasarkan data transaksi</p>

    {{-- Filter Periode --}}
    <div class="d-flex justify-content-end mb-4">
      <select id="filterPeriode" class="form-select w-auto">
        <option value="7">7 Hari</option>
        <option value="30" selected>30 Hari</option>
        <option value="90">90 Hari</option>
      </select>
    </div>

    {{-- Statistik --}}
    <div class="row g-4 mb-4">
      <div class="col-md-3">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h6 class="text-muted">Total Pendapatan</h6>
            <h4 class="fw-bold text-orange" id="totalRevenue">Rp 0</h4>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h6 class="text-muted">Total Pesanan</h6>
            <h4 class="fw-bold" id="totalOrders">0</h4>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h6 class="text-muted">Rata-rata Order</h6>
            <h4 class="fw-bold" id="avgOrder">Rp 0</h4>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h6 class="text-muted">Pertumbuhan</h6>
            <h4 class="fw-bold text-success" id="growthRate">0%</h4>
          </div>
        </div>
      </div>
    </div>

    {{-- Grafik --}}
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <h5 class="fw-bold mb-3">Tren Pendapatan & Pesanan</h5>
        <canvas id="analyticsChart" height="100"></canvas>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('analyticsChart').getContext('2d');
  let analyticsChart;

  async function loadAndRender(days) {
    const url = "{{ route('pemilik.analitik.data') }}" + "?days=" + days;
    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
    const d = await res.json();

    // isi kartu ringkasan
    document.getElementById('totalRevenue').innerText = "Rp " + (d.totalRevenue || 0).toLocaleString("id-ID");
    document.getElementById('totalOrders').innerText  = (d.totalOrders  || 0).toLocaleString("id-ID");
    document.getElementById('avgOrder').innerText     = "Rp " + (d.avgOrder || 0).toLocaleString("id-ID");
    const gr = (d.growthRate ?? 0);
    document.getElementById('growthRate').innerText   = (gr >= 0 ? "+" : "") + Number(gr).toFixed(1) + "%";

    if (analyticsChart) analyticsChart.destroy();

    analyticsChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: d.labels,
        datasets: [
          {
            label: 'Pendapatan',
            data: d.revenue,
            borderColor: '#ff7f50',
            backgroundColor: 'rgba(255,127,80,0.2)',
            fill: true,
            tension: 0.3
          },
          {
            label: 'Pesanan',
            data: d.orders,
            borderColor: '#2c7be5',
            backgroundColor: 'rgba(44,123,229,0.15)',
            fill: true,
            tension: 0.3
          }
        ]
      },
      options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          tooltip: {
            callbacks: {
              label: function(context) {
                if (context.dataset.label === 'Pendapatan') {
                  return "Rp " + (context.parsed.y || 0).toLocaleString("id-ID");
                } else {
                  return (context.parsed.y || 0) + " Pesanan";
                }
              }
            }
          }
        },
        scales: { y: { beginAtZero: true } }
      }
    });
  }

  const periode = document.getElementById('filterPeriode');
  if (periode) {
    periode.addEventListener('change', function() {
      loadAndRender(this.value || 30);
    });
  }

  // default 30 hari
  loadAndRender(30);
</script>
@endpush
