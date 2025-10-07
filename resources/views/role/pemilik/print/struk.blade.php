{{-- resources/views/role/pemilik/print/struk.blade.php --}}
@php
    $tgl  = optional($pesanan->created_at)->format('d M Y H:i');
    $berat= (float) $pesanan->berat_kg;
    $total= (int) $pesanan->total;
    $hargaPerKg = $berat > 0 ? floor($total / $berat) : null;
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Struk {{ $pesanan->kode }}</title>
    <style>
        *{ font-family: Arial, Helvetica, sans-serif; }
        body{ margin:0; padding:16px; }
        .receipt{ width: 80mm; margin:0 auto; }
        h2{ margin:0 0 4px 0; }
        .muted{ color:#666; font-size:12px; }
        .line{ border-top:1px dashed #999; margin:8px 0; }
        .row{ display:flex; justify-content:space-between; font-size:14px; margin:4px 0; }
        .total{ font-weight:bold; font-size:16px; }
        @media print{
            .no-print{ display:none !important; }
            body{ padding:0; }
        }
    </style>
</head>
<body>
<div class="receipt">
    <h2>LaundryKita</h2>
    <div class="muted">Portal Pemilik</div>
    <div class="line"></div>

    <div class="row"><div>Kode</div><div>{{ $pesanan->kode }}</div></div>
    <div class="row"><div>Tanggal</div><div>{{ $tgl }}</div></div>
    <div class="row"><div>Customer</div><div>{{ $pesanan->customer }}</div></div>
    @if($pesanan->telepon)
        <div class="row"><div>Telepon</div><div>{{ $pesanan->telepon }}</div></div>
    @endif

    <div class="line"></div>

    <div class="row"><div>Layanan</div><div>{{ $pesanan->layanan }}</div></div>
    <div class="row"><div>Berat</div><div>{{ number_format($berat,2,',','.') }} kg</div></div>
    @if(!is_null($hargaPerKg))
        <div class="row"><div>Harga/kg</div><div>Rp{{ number_format($hargaPerKg,0,',','.') }}</div></div>
    @endif
    <div class="row total"><div>Total</div><div>Rp{{ number_format($total,0,',','.') }}</div></div>

    <div class="line"></div>

    <div class="row"><div>Status</div>
        <div>{{ $pesanan->is_paid ? 'Lunas' : 'Belum Lunas' }} • {{ $pesanan->status }}</div>
    </div>

    <p class="muted" style="margin-top:12px">Terima kasih sudah menggunakan layanan kami.</p>

    <div class="no-print" style="margin-top:12px;text-align:center">
        <button onclick="window.print()">Cetak</button>
    </div>
</div>
</body>
</html>
