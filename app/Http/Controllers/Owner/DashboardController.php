<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Layanan;
use App\Models\Pesanan;
use App\Models\Pembayaran;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // waktu real-time
        $now        = Carbon::now();
        $todayStart = $now->copy()->startOfDay();
        $weekStart  = $now->copy()->startOfWeek();   // Senin 00:00
        $monthStart = $now->copy()->startOfMonth();  // Tgl 1 00:00

        // Scope: pesanan yang sudah LUNAS
        $paidScope = function ($q) {
            $q->where('is_paid', 1)
              ->orWhere('status_pembayaran', 'lunas');
        };

        // Helper: jumlahkan total pesanan LUNAS berdasarkan rentang waktu paid_at (fallback updated_at jika paid_at null)
        $sumPaid = function ($from, $to) use ($paidScope) {
            return (int) Pesanan::where($paidScope)
                ->where(function ($q) use ($from, $to) {
                    $q->whereBetween('paid_at', [$from, $to])
                      ->orWhere(function ($qq) use ($from, $to) {
                          $qq->whereNull('paid_at')
                             ->whereBetween('updated_at', [$from, $to]);
                      });
                })
                ->sum('total');
        };

        // --- KPI utama (real-time & konsisten dengan History) ---
        $pendapatanHariIni = $sumPaid($todayStart, $now);

        // Pesanan yang dibuat hari ini (apa pun statusnya)
        $pesananHariIni = (int) Pesanan::whereDate('created_at', $now->toDateString())->count();

        // Pesanan aktif (belum selesai/dibatalkan)
        $pesananAktif = (int) Pesanan::whereIn('status', ['Baru', 'Dalam Proses', 'Siap Ambil'])->count();

        // Karyawan aktif (role kasir)
        $karyawanAktif = (int) User::where('role', 'kasir')->count();

        // --- Ringkasan pendapatan (real-time) ---
        $ringkasan = [
            'hari'   => $pendapatanHariIni,
            'minggu' => $sumPaid($weekStart, $now),
            'bulan'  => $sumPaid($monthStart, $now),
        ];

        // --- Layanan terpopuler (30 hari terakhir) ---
        $layananTerpopuler = Pesanan::select('layanan', DB::raw('COUNT(*) as pesanan'))
            ->whereDate('created_at', '>=', $now->copy()->subDays(30)->toDateString())
            ->groupBy('layanan')
            ->orderByDesc('pesanan')
            ->limit(5)
            ->get()
            ->map(function ($row, $i) {
                return [
                    'rank'    => $i + 1,
                    'nama'    => $row->layanan,
                    'pesanan' => (int) $row->pesanan,
                ];
            });

        // Persentase growth: diset null (silakan di-hide di Blade)
        $growthPendapatan = null;
        $growthPesanan    = null;

        return view('role.pemilik.pemilik', [
            'pendapatanHariIni' => $pendapatanHariIni,
            'pesananHariIni'    => $pesananHariIni,
            'pesananAktif'      => $pesananAktif,
            'karyawanAktif'     => $karyawanAktif,
            'ringkasan'         => $ringkasan,
            'layananTop'        => $layananTerpopuler,
            'growthPendapatan'  => $growthPendapatan,
            'growthPesanan'     => $growthPesanan,
        ]);
    }
}
