<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Tag Pesanan - {{ $pesanan->kode }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
  /* Ukuran kertas kecil (mis. 80mm roll / A7) */
  @page { size: 80mm auto; margin: 6mm; }
  @media print {
    .no-print { display: none !important; }
    /* Saat cetak, isi memenuhi lebar kertas */
    .tag { width: 100%; margin: 0; }
  }

  body {
    font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
    margin: 0;
    background: #f7f8f9;
  }

  /* >>> Ubah lebar tag jadi 80mm agar ramping (seperti tiket/label) */
  .tag {
    width: 80mm;              /* sebelumnya 100% */
    margin: 12px auto;        /* center saat preview */
    border: 1px dashed #999;
    border-radius: 8px;
    padding: 10px 12px;
    background: #fff;
    box-sizing: border-box;
  }
  /* <<< */

  h1 { font-size: 16px; margin: 0 0 6px; }
  .muted { color:#666; font-size: 11px; }
  .row { display:flex; justify-content:space-between; font-size: 12px; margin: 2px 0; }
  .big { font-size: 18px; font-weight: 700; letter-spacing: 1px; }
  .hr { border-top:1px dashed #bbb; margin:8px 0; }
  .badge {
    display:inline-block; font-size: 11px; padding:2px 6px; border-radius:6px;
    background:#e8f5e9; color:#2e7d32; font-weight:600;
  }
</style>
</head>
<body onload="window.print()">

<div class="tag">
  <h1>LaundryKita</h1>
  <div class="muted">Tag Pesanan</div>

  <div class="row"><div>Kode</div><div class="big">{{ $pesanan->kode }}</div></div>
  <div class="row"><div>Nama</div><div>{{ $pesanan->customer ?? '-' }}</div></div>
  <div class="row"><div>Layanan</div><div>{{ $pesanan->layanan ?? '-' }}</div></div>
  @if(!is_null($pesanan->berat_kg))
  <div class="row"><div>Berat</div><div>{{ number_format((float)$pesanan->berat_kg,2,',','.') }} kg</div></div>
  @endif
  <div class="row"><div>Total</div><div>Rp{{ number_format((int)($pesanan->total ?? 0),0,',','.') }}</div></div>
  <div class="row"><div>Status</div><div>{{ $pesanan->status ?? '-' }}</div></div>

  @php
    $statusBayar = $pesanan->status_pembayaran ? ucfirst($pesanan->status_pembayaran) : (($pesanan->is_paid ?? false) ? 'Lunas' : 'Belum Lunas');
  @endphp
  <div class="row"><div>Pembayaran</div><div>
    <span class="badge">{{ $statusBayar }}</span>
  </div></div>

  <div class="hr"></div>
  <div class="row"><div>Tanggal</div><div>{{ optional($pesanan->created_at)->format('d/m/Y H:i') }}</div></div>
  @if(!empty($pesanan->telepon))
  <div class="row"><div>Telepon</div><div>{{ $pesanan->telepon }}</div></div>
  @endif
</div>

<!-- Tombol untuk preview non-print -->
<div class="no-print" style="margin-top:12px; text-align:center">
  <button onclick="window.print()">Cetak</button>
</div>

</body>
</html>