{{-- resources/views/role/pemilik/print/tag.blade.php --}}
@php
    $tgl  = optional($pesanan->created_at)->format('d M Y');
    $berat= (float) $pesanan->berat_kg;
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tag {{ $pesanan->kode }}</title>
    <style>
        *{ font-family: Arial, Helvetica, sans-serif; }
        body{ margin:0; padding:16px; }
        .tag{
            width: 90mm; border:1px dashed #333; padding:10px; margin:0 auto;
        }
        .kode{ font-size:22px; font-weight:bold; letter-spacing:1px; }
        .row{ display:flex; justify-content:space-between; margin-top:6px; font-size:14px; }
        .muted{ color:#666; font-size:12px; }
        @media print{
            .no-print{ display:none !important; }
            body{ padding:0; }
        }
    </style>
</head>
<body>
<div class="tag">
    <div class="kode">{{ $pesanan->kode }}</div>
    <div class="muted">{{ $tgl }}</div>

    <div class="row"><div>Nama</div><div>{{ $pesanan->customer }}</div></div>
    @if($pesanan->telepon)
        <div class="row"><div>Telp</div><div>{{ $pesanan->telepon }}</div></div>
    @endif
    <div class="row"><div>Layanan</div><div>{{ $pesanan->layanan }}</div></div>
    <div class="row"><div>Berat</div><div>{{ number_format($berat,2,',','.') }} kg</div></div>

    <div class="no-print" style="margin-top:10px;text-align:center">
        <button onclick="window.print()">Cetak Tag</button>
    </div>
</div>
</body>
</html>
