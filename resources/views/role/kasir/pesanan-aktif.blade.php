@extends('layouts.app')

@section('title', 'Pesanan Aktif - LaundryKita')

@section('content')
<div class="d-flex">
    {{-- Sidebar (shared) --}}
    @include('role.kasir.partials.sidebar')

    <!-- Konten -->
    <div class="flex-grow-1 p-4" style="background: #f1f5f4ff;">
        <h2 class="fw-bold">Pesanan Aktif</h2>
        <p class="text-muted mb-4">Daftar pesanan yang sedang dikerjakan</p>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Search Bar: kode / nama pelanggan --}}
        <form method="GET" action="{{ request()->url() }}" class="mb-3">
            <div class="input-group">
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    class="form-control form-control-lg"
                    placeholder="Cari kode / nama pelanggan..."
                    aria-label="Cari kode / nama pelanggan">
                <button class="btn btn-success px-4 fw-semibold" type="submit">CARI</button>
            </div>
        </form>

        <!-- Daftar Pesanan -->
        <div class="card shadow-sm border-0 rounded-3 p-3">
            @forelse($orders as $order)
                @php
                    $statusMap = [
                        'Baru'          => 'bg-secondary',
                        'Dalam Proses'  => 'bg-warning text-dark',
                        'Siap Ambil'    => 'bg-info text-dark',
                        'Selesai'       => 'bg-success',
                        'Dibatalkan'    => 'bg-danger',
                    ];
                    $isPaid = isset($order->status_pembayaran)
                                ? $order->status_pembayaran === 'lunas'
                                : (bool) ($order->is_paid ?? false);
                @endphp

                <div class="d-flex justify-content-between align-items-center border rounded p-3 mb-2">
                    <div>
                        <h6 class="fw-bold mb-1">{{ $order->kode }} - {{ $order->customer }}</h6>
                        <small class="text-muted d-block mb-1">
                            {{ $order->layanan }} • {{ number_format($order->berat_kg ?? 0, 2, ',', '.') }} kg
                        </small>
                        {{-- Tambahan: tampilkan nama kasir pembuat pesanan --}}
                        <small class="text-muted d-block mb-1">
                            Kasir: {{ $order->creator->name ?? '-' }}
                        </small>
                        <div class="mb-1">
                            <span class="badge {{ $statusMap[$order->status] ?? 'bg-secondary' }}">{{ $order->status }}</span>
                            <span class="badge {{ $isPaid ? 'bg-success' : 'bg-secondary' }}">
                                {{ $isPaid ? 'Lunas' : 'Belum Lunas' }}
                            </span>
                        </div>
                        <p class="fw-bold mb-0">Rp{{ number_format($order->total ?? 0, 0, ',', '.') }}</p>
                    </div>

                    <div class="text-end">
                        @if($order->status !== 'Selesai' && $order->status !== 'Dibatalkan')

                            @if($order->status !== 'Siap Ambil')
                                {{-- Ubah ke Siap Ambil (AJAX ala v5) --}}
                                <form method="POST"
                                      action="{{ route('kasir.pesanan.updateStatus', $order->id) }}"
                                      class="d-inline js-status-form"
                                      data-status="Siap Ambil"
                                      onsubmit="return confirm('Kirim pesan ke pelanggan via WhatsApp dan ubah status ke Siap Ambil?')">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-info btn-sm">
                                        Siap Ambil
                                    </button>
                                </form>
                            @endif

                            {{-- Ubah ke Selesai (AJAX ala v5) --}}
                            <form method="POST"
                                  action="{{ route('kasir.pesanan.updateStatus', $order->id) }}"
                                  class="d-inline js-status-form"
                                  data-status="Selesai"
                                  onsubmit="return confirm('Konfirmasi: tandai pesanan Selesai & kirim pesan WhatsApp ke pelanggan?')">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-success btn-sm">
                                    Selesai
                                </button>
                            </form>

                        @endif

                        {{-- Tambahan: tombol Cetak Tag --}}
                        <a href="{{ route('kasir.pesanan.cetakTag', $order->id) }}"
                           target="_blank"
                           class="btn btn-outline-secondary btn-sm ms-1">
                            <i class="bi bi-printer"></i> Cetak Tag
                        </a>
                    </div>
                </div>
            @empty
                <p class="text-muted text-center mb-0">Belum ada pesanan aktif</p>
            @endforelse
        </div>
    </div>
</div>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@if(session('success'))
<script>
document.addEventListener("DOMContentLoaded", () => {
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: @json(session('success')),
        confirmButtonColor: '#35C8B4'
    });
});
</script>
@endif

{{-- AJAX ala versi 5: update status -> buka WA (hanya wa_url dari controller) -> auto-reload --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
  const token = document.querySelector('meta[name="csrf-token"]')
               ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
               : '{{ csrf_token() }}';

  document.querySelectorAll('form.js-status-form').forEach(form => {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const url    = form.getAttribute('action');
      const status = form.getAttribute('data-status') || 'Baru';
      const btn    = form.querySelector('button[type="submit"]');

      if (btn) {
        btn.disabled = true;
        btn.dataset._text = btn.innerHTML;
        btn.innerHTML = 'Memproses...';
      }

      try {
        const res = await fetch(url, {
          method: 'PATCH',
          headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ status })
        });

        if (!res.ok) {
          let msg = 'Tidak dapat mengubah status. Coba lagi.';
          try {
            const err = await res.json();
            msg = err?.message || err?.error || msg;
          } catch(_) {}
          if (window.Swal) Swal.fire({icon:'error', title:'Gagal', text:msg});
          else alert(msg);
          return;
        }

        const data = await res.json();

        // === BUKA WA ===
        // Controller HARUS mengirim wa_url = https://wa.me/{phone}?text=...
        if (data.wa_url) {
          window.open(data.wa_url, '_blank');
        }

        // Notifikasi singkat & reload
        if (window.Swal && data.message) {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: data.message,
            timer: 900,
            showConfirmButton: false
          });
        }
        setTimeout(() => window.location.reload(), 600);

      } catch (err) {
        console.error(err);
        if (window.Swal) {
          Swal.fire({icon:'error', title:'Gagal', text:'Tidak dapat mengubah status. Coba lagi.'});
        } else {
          alert('Tidak dapat mengubah status. Coba lagi.');
        }
      } finally {
        if (btn) {
          btn.disabled = false;
          btn.innerHTML = btn.dataset._text || 'Kirim';
        }
      }
    });
  });
});
</script>

{{-- Legacy support: jika ada session wa_url dari redirect lama --}}
@if(session('wa_url'))
<script>
  window.open(@json(session('wa_url')), "_blank");
</script>
@endif

<style>
    .text-accent { color: #35C8B4 !important; }
    .bg-accent  { background-color: #35C8B4 !important; }
</style>
@endsection