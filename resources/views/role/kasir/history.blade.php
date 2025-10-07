@extends('layouts.app')
@section('title','History Pesanan - LaundryKita')

@section('content')
<div class="d-flex">
  {{-- Sidebar (shared) --}}
  <div class="sidebar-fixed">
    @include('role.kasir.partials.sidebar')
  </div>

  <div class="flex-grow-1 p-4" style="background:#f1f5f4ff; margin-left:260px;">
    <h2 class="fw-bold mb-3">History Pesanan</h2>
    <p class="text-muted">Semua pesanan (read-only)</p>

    {{-- statistik ringkas --}}
    <div class="row g-3 mb-3">
      <div class="col-md-4"><div class="card shadow-sm border-0 p-3">
        <div class="text-muted">Total Pesanan</div>
        <div class="fs-4 fw-bold">{{ number_format($stats['total']) }}</div>
      </div></div>
      <div class="col-md-4"><div class="card shadow-sm border-0 p-3">
        <div class="text-muted">Lunas</div>
        <div class="fs-4 fw-bold text-success">{{ number_format($stats['lunas']) }}</div>
      </div></div>
      <div class="col-md-4"><div class="card shadow-sm border-0 p-3">
        <div class="text-muted">Belum Lunas</div>
        <div class="fs-4 fw-bold text-danger">{{ number_format($stats['belum_lunas']) }}</div>
      </div></div>
    </div>

    {{-- filter --}}
    <form class="card shadow-sm border-0 p-3 mb-4" method="get" action="{{ route('kasir.history') }}">
      <div class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label mb-0">Status</label>
          <select name="status" class="form-select">
            <option value="">Semua</option>
            @foreach(['Baru','Siap Ambil','Selesai'] as $st)
              <option value="{{ $st }}" {{ request('status')===$st?'selected':'' }}>{{ $st }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label mb-0">Pembayaran</label>
          <select name="paid" class="form-select">
            <option value="">Semua</option>
            <option value="1" {{ request('paid')==='1'?'selected':'' }}>Lunas</option>
            <option value="0" {{ request('paid')==='0'?'selected':'' }}>Belum</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label mb-0">Dari</label>
          <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>
        <div class="col-md-2">
          <label class="form-label mb-0">Sampai</label>
          <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>
        <div class="col-md-3">
          <label class="form-label mb-0">Cari (kode/nama/telepon)</label>
          <div class="d-flex gap-2">
            <input type="text" name="q" class="form-control" value="{{ request('q') }}">
            <button class="btn btn-accent btn-sm">Filter</button>
          </div>
        </div>
      </div>
    </form>

    {{-- list pesanan --}}
    @forelse($orders as $order)
      <div class="card shadow-sm border-0 p-3 mb-3">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h5 class="fw-bold mb-1">{{ $order->kode }} - {{ $order->customer }}</h5>
            <div class="text-muted">
              {{ $order->layanan }} • {{ number_format((float)$order->berat_kg, 2, ',', '.') }} kg
              • {{ optional($order->created_at)->format('d M Y H:i') }}
            </div>
            <div class="mt-1">
              <span class="badge {{ $order->is_paid ? 'bg-success' : 'bg-secondary' }}">
                {{ $order->is_paid ? 'Lunas' : 'Belum Lunas' }}
              </span>
              <span class="badge bg-info text-dark">{{ $order->status }}</span>
              <span class="badge bg-light text-dark">Kasir: {{ $order->creator->name ?? '-' }}</span>
            </div>
          </div>

          {{-- kanan: total + aksi --}}
          <div class="text-end">
            <div class="fw-bold fs-5">Rp{{ number_format((int)$order->total, 0, ',', '.') }}</div>
            <div class="mt-2 d-flex gap-2 justify-content-end">
              <button class="btn btn-sm btn-outline-accent"
                      data-id="{{ $order->id }}"
                      onclick="openDetail(this)">Detail</button>
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="alert alert-light border">Belum ada data pesanan.</div>
    @endforelse

    <div class="mt-3">
      {{ $orders->links() }}
    </div>
  </div>
</div>

{{-- Modal Detail --}}
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content rounded-3 border-0 shadow">
      <div class="modal-header bg-accent text-white">
        <h5 class="modal-title">Detail Pesanan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="background:#f7fffd;">
        <div id="detailBody" class="p-2 text-center text-muted">Memuat...</div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
async function openDetail(btn){
  const id = btn.getAttribute('data-id');
  try {
    const res = await fetch(`{{ url('/kasir/history') }}/${id}`, {
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    });
    if (!res.ok) throw new Error('Gagal memuat data');
    const data = await res.json();
    const order = data.order;
    const logs  = data.logs || [];

    const htmlLogs = logs.length > 0
      ? `<ul class="list-group list-group-flush">
          ${logs.map(l => `
            <li class="list-group-item">
              <strong>${l.by}</strong> • ${l.from} → ${l.to}
              <div class="text-muted small">${l.at}</div>
              ${l.note ? `<div class="small fst-italic">Catatan: ${l.note}</div>` : ''}
            </li>
          `).join('')}
         </ul>`
      : '<div class="text-muted">Belum ada log perubahan.</div>';

    document.getElementById('detailBody').innerHTML = `
      <div class="card border-0 shadow-sm mb-3" style="background:#E8FCF4;">
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <p><strong>Kode:</strong> ${order.kode}</p>
              <p><strong>Pelanggan:</strong> ${order.customer}</p>
              <p><strong>Telepon:</strong> ${order.telepon || '-'}</p>
            </div>
            <div class="col-md-6">
              <p><strong>Layanan:</strong> ${order.layanan}</p>
              <p><strong>Berat:</strong> ${Number(order.berat_kg || 0).toLocaleString('id-ID')} kg</p>
              <p><strong>Total:</strong> Rp${Number(order.total || 0).toLocaleString('id-ID')}</p>
              <p><strong>Status:</strong> ${order.status} • ${order.is_paid ? 'Lunas' : 'Belum Lunas'}</p>
              <p><strong>Dibuat:</strong> ${order.created_at || '-'}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm" style="background:#F1FFF9;">
        <div class="card-body">
          <h6 class="fw-bold mb-2">Log Perubahan</h6>
          ${htmlLogs}
        </div>
      </div>
    `;

    new bootstrap.Modal(document.getElementById('detailModal')).show();
  } catch (err) {
    console.error(err);
    alert('Terjadi kesalahan saat memuat detail.');
  }
}
</script>
@endpush

<style>
  /* --- Styling utama --- */
  .sidebar-fixed {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    background: #fff;
    z-index: 1000;
    width: 260px;
    border-right: none !important; /* hilangkan garis pemisah */
  }

  .text-accent { color:#35C8B4!important; }
  .bg-accent   { background-color:#35C8B4!important; }
  .btn-accent  { background:#35C8B4;color:#fff;border-radius:25px;padding:8px 20px;font-weight:500;border:none; }
  .btn-accent:hover { background:#2ba497;color:#fff; }
  .btn-outline-accent { border:1px solid #35C8B4; color:#35C8B4; }
  .btn-outline-accent:hover { background:#35C8B4; color:#fff; }
</style>
@endsection