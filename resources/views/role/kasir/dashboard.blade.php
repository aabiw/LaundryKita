@extends('layouts.app')

@section('title', 'Dashboard Karyawan - LaundryKita')

@section('content')
<div class="d-flex">
    {{-- Sidebar (shared) --}}
    @include('role.kasir.partials.sidebar')

    <!-- Konten -->
    <div class="flex-grow-1 p-4" style="background: #f1f5f4ff;">
        <h2 class="fw-bold">Dashboard Karyawan</h2>
        <p class="text-muted mb-4">Ringkasan aktivitas operasional hari ini</p>

        <!-- Statistik -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-3 p-3 text-center">
                    <h6 class="text-muted">Pesanan Hari Ini</h6>
                    <h3 class="fw-bold text-accent">{{ $pesananHariIni }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-3 p-3 text-center">
                    <h6 class="text-muted">Pesanan Aktif</h6>
                    <h3 class="fw-bold text-accent">{{ $pesananAktif }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-3 p-3 text-center">
                    <h6 class="text-muted">Siap Diambil</h6>
                    <h3 class="fw-bold text-accent">{{ $siapAmbil   }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-3 p-3 text-center">
                    <h6 class="text-muted">Pendapatan Hari Ini</h6>
                    <h3 class="fw-bold text-accent">Rp{{ number_format($pendapatanHariIni, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>

        <!-- Pesanan Terbaru -->
        <div class="card shadow-sm border-0 rounded-3 p-3">
            <h5 class="fw-bold mb-3">Pesanan Terbaru</h5>
            @forelse($orders as $order)
                @php
                    $statusMap = [
                        'Baru'          => 'bg-secondary',
                        'Dalam Proses'  => 'bg-warning text-dark',
                        'Siap Ambil'    => 'bg-info text-dark',
                        'Selesai'       => 'bg-success',
                        'Dibatalkan'    => 'bg-danger',
                    ];
                    $badgeClass = $statusMap[$order['status']] ?? 'bg-secondary';
                @endphp
                <div class="d-flex justify-content-between align-items-center border rounded p-3 mb-2">
                    <div>
                        <h6 class="fw-bold mb-0">{{ $order['id'] }} - {{ $order['customer'] }}</h6>
                        <small class="text-muted">Total: Rp{{ number_format($order['total'],0,',','.') }}</small>
                    </div>
                    <div class="text-end">
                        <span class="badge {{ $badgeClass }}">
                            {{ $order['status'] }}
                        </span>
                    </div>
                </div>
            @empty
                <p class="text-muted">Belum ada pesanan terbaru.</p>
            @endforelse
        </div>
    </div>
</div>

@push('styles')
<style>
    .text-accent { color: #35C8B4 !important; }
    .bg-accent { background-color: #35C8B4 !important; }
</style>
@endpush
@endsection