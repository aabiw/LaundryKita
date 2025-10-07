@extends('layouts.app')

@section('title', 'Status Pembayaran - LaundryKita')

@section('content')
<div class="d-flex">
    {{-- Sidebar (shared) --}}
    @include('role.kasir.partials.sidebar')

    <!-- Konten -->
    <div class="flex-grow-1 p-4" style="background:#f1f5f4ff;">
        <h2 class="fw-bold">Status Pembayaran</h2>
        <p class="text-muted mb-4">Pilih pesanan untuk mengubah status pembayarannya</p>

        {{-- Flash message --}}
        @if(session('ok'))        <div class="alert alert-success">{{ session('ok') }}</div>@endif
        @if(session('success'))   <div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))     <div class="alert alert-danger">{{ session('error') }}</div>@endif

        <div class="row">
            <!-- Daftar Pesanan -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 rounded-3 p-4">
                    <h5 class="fw-bold mb-3">Daftar Pesanan</h5>

                    <form class="mb-3" method="GET" action="{{ route('kasir.pembayaran.index') }}">
                        <div class="input-group">
                            <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="Cari nama/kode...">
                            <button class="btn btn-accent" type="submit">Cari</button>
                        </div>
                    </form>

                    @forelse($orders ?? [] as $p)
                        @php
                            // Sumber kebenaran: kolom status_pembayaran (lunas / belum_lunas)
                            // fallback ke boolean is_paid bila kolom di DB lama belum ada.
                            $isLunas    = isset($p->status_pembayaran)
                                            ? ($p->status_pembayaran === 'lunas')
                                            : (bool) $p->is_paid;
                            $badgeClass = $isLunas ? 'bg-success' : 'bg-dark-teal';
                            $badgeText  = $isLunas ? 'Lunas' : 'Belum Lunas';
                        @endphp

                        <div class="card border-0 shadow-sm p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-bold">{{ $p->kode }} - {{ $p->customer }}</div>
                                    <div class="text-muted">
                                        {{ $p->layanan }}
                                        @if(!is_null($p->berat_kg))
                                            - {{ number_format((float)$p->berat_kg, 2) }}kg
                                        @endif
                                    </div>
                                    {{-- Tambahan: tampilkan nama kasir pembuat --}}
                                    <div class="text-muted" style="font-size: .9rem;">
                                        Kasir: {{ $p->creator->name ?? '-' }}
                                    </div>
                                    <div class="mt-1">
                                        <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
                                    </div>
                                </div>
                                <div class="text-end fw-bold">Rp{{ number_format($p->total, 0, ',', '.') }}</div>
                            </div>

                            <div class="mt-2 d-flex gap-2">
                                <a href="{{ route('kasir.pembayaran.index', ['id' => $p->id, 'q' => $q ?? null]) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    Pilih
                                </a>

                                @unless($isLunas)
                                    {{-- Tombol tandai lunas daftar kiri --}}
                                    <form class="formTandaiLunasKiri" method="POST" action="{{ route('kasir.pembayaran.markPaid', $p->id) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-success">Tandai Lunas</button>
                                    </form>
                                @endunless
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Belum ada pesanan.</p>
                    @endforelse
                </div>
            </div>

            <!-- Struk / Preview -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 rounded-3 p-4">
                    <h5 class="fw-bold mb-3">Struk Pembayaran</h5>

                    @if(!empty($selected))
                        @php
                            $isLunasSel    = isset($selected->status_pembayaran)
                                                ? ($selected->status_pembayaran === 'lunas')
                                                : (bool) $selected->is_paid;
                            $badgeClassSel = $isLunasSel ? 'bg-success' : 'bg-dark-teal';
                            $badgeTextSel  = $isLunasSel ? 'Lunas' : 'Belum Lunas';
                        @endphp

                        <div id="receipt-content">
                            <h6 class="fw-bold mb-1 text-accent">LaundryKita</h6>
                            <div class="small text-muted">Dicetak: {{ now()->format('d M Y, H:i') }}</div>
                            <hr>
                            <p class="mb-1"><b>Order ID:</b> {{ $selected->kode }}</p>
                            <p class="mb-1"><b>Customer:</b> {{ $selected->customer }}</p>
                            {{-- Tambahan: tampilkan nama kasir pembuat --}}
                            <p class="mb-1"><b>Kasir:</b> {{ $selected->creator->name ?? '-' }}</p>
                            <p class="mb-1">
                                <b>Service:</b> {{ $selected->layanan }}
                                @if(!is_null($selected->berat_kg))
                                    - {{ number_format((float)$selected->berat_kg, 2) }}kg
                                @endif
                            </p>
                            <p class="mb-1"><b>Total:</b> Rp{{ number_format($selected->total, 0, ',', '.') }}</p>
                            <p class="mb-1">
                                <b>Status Pembayaran:</b>
                                <span class="badge {{ $badgeClassSel }}">{{ $badgeTextSel }}</span>
                            </p>
                            @if(!empty($selected->paid_at))
                                <p class="mb-1"><b>Waktu Pelunasan:</b> {{ \Carbon\Carbon::parse($selected->paid_at)->format('d M Y, H:i') }}</p>
                            @endif
                            <hr>
                            <p class="mt-2 text-muted small">Terima kasih telah menggunakan LaundryKita!</p>
                        </div>

                        <div class="d-flex flex-column gap-2 mt-3">
                            @unless($isLunasSel)
                                <form id="formTandaiLunas" method="POST" action="{{ route('kasir.pembayaran.markPaid', $selected->id) }}">
                                    @csrf @method('PATCH')
                                    <div class="mb-2">
                                        <label for="metode_pembayaran" class="form-label fw-semibold">Metode Pembayaran</label>
                                        <select class="form-select" id="metode_pembayaran" name="metode_pembayaran">
                                            <option value="">-- Pilih Metode Pembayaran --</option>
                                            <option value="Tunai">Tunai</option>
                                            <option value="Transfer">Transfer</option>
                                            <option value="QRIS">QRIS</option>
                                        </select>
                                    </div>
                                    <button id="btnTandaiLunas" class="btn btn-success">Tandai Lunas</button>
                                </form>
                            @endunless
                            <button id="btnCetakStruk" onclick="printReceipt()" class="btn btn-accent">Cetak Struk</button>
                        </div>
                    @else
                        <div class="text-muted">
                            Pilih pesanan dari daftar untuk memproses pembayaran.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .text-accent { color:#35C8B4 !important; }
    .bg-accent { background-color:#35C8B4 !important; }
    .btn-accent {
        background:#35C8B4; color:#fff; border-radius:25px;
        padding:6px 18px; font-weight:500; border:none;
    }
    .btn-accent:hover { background:#2ca697; color:#fff; }
    .hover-bg:hover { background-color:#C2F2E4; }
</style>

<script>
    // Validasi metode pembayaran sebelum submit (panel kanan)
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('formTandaiLunas');
        if (form) {
            form.addEventListener('submit', function (e) {
                const metode = document.getElementById('metode_pembayaran').value;
                if (metode === '') {
                    e.preventDefault();
                    alert('⚠ Silakan pilih metode pembayaran terlebih dahulu!');
                }
            });
        }

        // Validasi tombol cetak struk
        const btnCetak = document.getElementById('btnCetakStruk');
        if (btnCetak) {
            btnCetak.addEventListener('click', function (e) {
                const statusBadge = document.querySelector('#receipt-content .badge');
                if (statusBadge && statusBadge.textContent.trim() !== 'Lunas') {
                    e.preventDefault();
                    alert('⚠ Tidak bisa mencetak struk sebelum pembayaran ditandai lunas!');
                }
            });
        }

        // Validasi tombol tandai lunas di daftar kiri
        const formsKiri = document.querySelectorAll('.formTandaiLunasKiri');
        formsKiri.forEach(f => {
            f.addEventListener('submit', function (e) {
                e.preventDefault();
                alert('⚠ Silakan pilih metode pembayaran terlebih dahulu di panel kanan sebelum menandai lunas!');
            });
        });
    });

    // Fungsi cetak struk (hanya berjalan bila lunas)
    function printReceipt() {
        const statusBadge = document.querySelector('#receipt-content .badge');
        if (!statusBadge || statusBadge.textContent.trim() !== 'Lunas') {
            alert('⚠ Tidak bisa mencetak struk sebelum pembayaran ditandai lunas!');
            return;
        }

        const receiptContent = document.getElementById('receipt-content').innerHTML;
        const now = new Date();
        const waktuCetak = now.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });

        const win = window.open('', '', 'width=420,height=640');
        win.document.write(`
            <html>
            <head>
                <title>Print Receipt</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    h2 { color: #35C8B4; text-align: center; margin: 0 0 4px; }
                    .ts { text-align:center; font-size:12px; color:#666; margin-bottom:8px; }
                    hr { border: 0; border-top: 1px dashed #ccc; margin: 10px 0; }
                    p { margin: 4px 0; font-size: 14px; }
                    .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
                    .badge { display:inline-block; padding:2px 8px; border-radius:8px; font-size:12px; }
                    .bg-success { background:#A4CF4A; color:#fff; }
                    .bg-secondary { background:#6c757d; color:#fff; }
                </style>
            </head>
            <body>
                <h2>LaundryKita</h2>
                <div class="ts">Dicetak: ${waktuCetak}</div>
                <hr>
                ${receiptContent}
                <div class="footer">LaundryKita - Struk Resmi</div>
            </body>
            </html>
        `);
        win.document.close();
        win.focus();
        win.print();
    }
</script>
@endsection
