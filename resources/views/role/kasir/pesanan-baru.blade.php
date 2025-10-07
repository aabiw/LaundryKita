@extends('layouts.app')

@section('title', 'Pesanan Baru - LaundryKita')

@section('content')
<div class="d-flex">
    {{-- Sidebar (shared) --}}
    @include('role.kasir.partials.sidebar')

    <!-- Konten -->
    <div class="flex-grow-1 p-4" style="background: #f1f5f4ff;">
        <h2 class="fw-bold">Pesanan Baru</h2>
        <p class="text-muted mb-4">Masukkan detail pesanan pelanggan</p>

        <!-- Alert jika ada pesan sukses/error -->
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="row g-4">
            <!-- Form Pesanan -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-3 p-4">
                    <h5 class="fw-bold mb-4">Formulir Pesanan</h5>
                    <form id="form-pesanan-baru" method="POST" action="{{ route('kasir.pesanan.store') }}" target="WA_TAB">
                        @csrf
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <input type="text" name="customer" id="nama" placeholder="Nama Pelanggan" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="telepon" id="telepon" placeholder="Nomor Telepon" class="form-control" required>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                {{-- Pilih jenis layanan dipindah ke posisi email --}}
                                <select name="layanan" id="layanan" class="form-select" required>
                                    <option value="">Pilih jenis layanan</option>
                                    @forelse($layanans ?? [] as $svc)
                                        <option value="{{ $svc->nama }}"
                                                data-harga="{{ $svc->harga }}"
                                                data-durasi="{{ $svc->durasi_jam }}">
                                            {{ $svc->nama }}
                                        </option>
                                    @empty
                                        <option value="" disabled>Belum ada layanan aktif — minta pemilik menambahkannya</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="col-md-6">
                                <!-- Izinkan desimal & locale Indonesia -->
                                <input type="text" name="berat_kg" id="berat" placeholder="Berat (kg)"
                                       class="form-control" min="0" step="0.01" inputmode="decimal" lang="id" required>
                            </div>
                        </div>

                        <!-- Status Pembayaran -->
                        <div class="mb-3">
                            <label class="form-label">Status Pembayaran</label>
                            <select name="status_pembayaran" id="status_pembayaran" class="form-select" required>
                                <option value="lunas">Lunas</option>
                                <option value="belum_lunas" selected>Belum Lunas</option>
                            </select>
                        </div>

                        <!-- Metode Pembayaran (baru) -->
                        <div class="mb-3">
                            <label for="metode_pembayaran" class="form-label">Metode Pembayaran</label>
                            <select name="metode_pembayaran" id="metode_pembayaran" class="form-select">
                                <option value="">Pilih Metode</option>
                                <option value="Tunai">Tunai</option>
                                <option value="Transfer Bank">Transfer Bank</option>
                                <option value="QRIS">QRIS</option> {{-- ✅ ganti dari "Kartu Kredit" ke "QRIS" --}}
                            </select>
                        </div>

                        <div class="mb-3">
                            <textarea id="catatan" name="catatan" placeholder="Catatan tambahan" class="form-control" rows="3"></textarea>
                        </div>

                        <!-- hidden input untuk total -->
                        <input type="hidden" name="total" id="total" value="0">

                        <button type="submit" class="btn btn-accent">Buat Pesanan</button>
                    </form>
                </div>
            </div>

            <!-- Detail Pesanan -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 rounded-3 p-4">
                    <h5 class="fw-bold mb-3">Detail Pesanan</h5>
                    <ul class="list-unstyled" id="detailPesanan">
                        <li><i class="bi bi-person me-2"></i>Nama: <span class="fw-bold">-</span></li>
                        <li><i class="bi bi-telephone me-2"></i>Telepon: <span class="fw-bold">-</span></li>
                        {{-- Email dihapus --}}
                        <li><i class="bi bi-basket me-2"></i>Layanan: <span class="fw-bold">-</span></li>
                        <li><i class="bi bi-box me-2"></i>Berat: <span class="fw-bold">-</span></li>
                        <li><i class="bi bi-cash-coin me-2"></i>Estimasi Harga: <span class="fw-bold">Rp0</span></li>
                        <li><i class="bi bi-sticky me-2"></i>Catatan: <span class="fw-bold">-</span></li>
                        <li><i class="bi bi-credit-card-2-front me-2"></i>Status Pembayaran: <span class="fw-bold">Belum Lunas</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-3 shadow">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold text-success">
            <i class="bi bi-check-circle-fill me-2"></i> Pesanan Berhasil
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <p>Pesanan sudah berhasil dibuat dan masuk ke sistem.</p>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-accent" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
    function getHargaPerKg() {
        const opt = document.querySelector('#layanan option:checked');
        const h = opt ? opt.getAttribute('data-harga') : null;
        return h ? parseInt(h, 10) : 0;
    }

    function updateDetail() {
        const nama = document.getElementById('nama').value || "-";
        const telepon = document.getElementById('telepon').value || "-";
        const layananSel = document.getElementById('layanan');
        const layanan = layananSel.value || "-";

        // Normalisasi "3,5" -> "3.5" agar bisa dihitung
        theBerat = (document.getElementById('berat').value || "").toString().replace(',', '.');
        const berat = parseFloat(theBerat) || 0;

        const statusVal = document.getElementById('status_pembayaran').value;
        const metodeVal  = (document.getElementById('metode_pembayaran')?.value || '').trim();
        // tampilkan metode saat LUNAS jika dipilih (QRIS/Tunai/Transfer Bank)
        const statusPembayaran = (statusVal === 'lunas')
            ? 'Lunas' + (metodeVal ? ` (${metodeVal})` : '')
            : 'Belum Lunas';

        const catatan = document.getElementById('catatan').value || "-";
        const hargaPerKg = getHargaPerKg();
        const total = Math.round(berat * hargaPerKg);

        // set hidden input total
        document.getElementById('total').value = total;

        document.getElementById('detailPesanan').innerHTML = `
            <li><i class="bi bi-person me-2"></i>Nama: <span class="fw-bold">${nama}</span></li>
            <li><i class="bi bi-telephone me-2"></i>Telepon: <span class="fw-bold">${telepon}</span></li>
            <li><i class="bi bi-basket me-2"></i>Layanan: <span class="fw-bold">${layanan}</span></li>
            <li><i class="bi bi-box me-2"></i>Berat: <span class="fw-bold">${berat} kg</span></li>
            <li><i class="bi bi-cash-coin me-2"></i>Estimasi Harga: <span class="fw-bold">Rp${total.toLocaleString('id-ID')}</span></li>
            <li><i class="bi bi-sticky me-2"></i>Catatan: <span class="fw-bold">${catatan}</span></li>
            <li><i class="bi bi-credit-card-2-front me-2"></i>Status Pembayaran: <span class="fw-bold">${statusPembayaran}</span></li>
        `;
    }

    // === [Tambahan] Sinkronisasi enable/disable metode pembayaran ===
    function syncMetodePembayaran() {
        const statusEl = document.getElementById('status_pembayaran');
        const metodeEl = document.getElementById('metode_pembayaran');
        const isBelumLunas = statusEl && statusEl.value === 'belum_lunas';

        if (metodeEl) {
            metodeEl.disabled = isBelumLunas;   // nonaktifkan jika belum lunas
            if (isBelumLunas) metodeEl.value = ''; // kosongkan pilihan
        }
    }
    // =================================================================

    document.querySelectorAll("#nama,#telepon,#layanan,#berat,#catatan,#status_pembayaran,#metode_pembayaran")
        .forEach(el => el.addEventListener("input", function(){
            updateDetail();
            if (this.id === 'status_pembayaran') syncMetodePembayaran(); // jaga sinkron
        }));
    document.getElementById('layanan').addEventListener('change', updateDetail);
    document.getElementById('status_pembayaran').addEventListener('change', syncMetodePembayaran);

    // Inisialisasi pertama
    updateDetail();
    syncMetodePembayaran();

    // Normalisasi berat ke titik sebelum submit agar backend aman
    document.querySelector('form[action="{{ route('kasir.pesanan.store') }}"]')
      .addEventListener('submit', function(){
          const beratEl = document.getElementById('berat');
          if (beratEl && beratEl.value) {
              beratEl.value = beratEl.value.replace(',', '.');
          }
      });

    // (Dihapus) Pembuka about:blank WA_TAB — tidak diperlukan lagi

    // Tampilkan modal jika ada session success
    @if(session('success'))
        document.addEventListener("DOMContentLoaded", () => {
            var modal = new bootstrap.Modal(document.getElementById('successModal'));
            modal.show();
        });
    @endif
</script>

<style>
    .text-accent { color: #35C8B4 !important; }
    .bg-accent { background-color: #35C8B4 !important; }
    .btn-accent {
        background: #35C8B4;
        color: #f1f5f4ff;
        border-radius: 25px;
        padding: 8px 20px;
        font-weight: 500;
        border: none;
    }
    .btn-accent:hover {
        background: #2ba497;
        color: #f1f5f4ff;
    }
</style>
@endsection
