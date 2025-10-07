<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pesanan;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard kasir.
     */

    public function index()
    {
        // Ambil 6 pesanan terbaru
        $orders = Pesanan::orderBy('created_at', 'desc')
                    ->limit(6)
                    ->get(['id','kode','customer','total','status']);

        // Hitung jumlah pesanan masuk hari ini
        $pesananHariIni = Pesanan::whereDate('created_at', now())->count();

        // Hitung pesanan aktif (Baru + Dalam Proses)
        $pesananAktif = Pesanan::whereIn('status', ['Baru', 'Dalam Proses'])->count();

        // Hitung pesanan yang siap diambil
        $siapAmbil = Pesanan::where('status', 'Siap Ambil')->count();

        // Hitung pendapatan dari pesanan yang sudah selesai hari ini
        $pendapatanHariIni = Pesanan::where('status_pembayaran', 'lunas')
                                    ->whereDate('updated_at', now())
                                    ->sum('total');

        return view('role.kasir.dashboard', compact(
            'orders',
            'pesananHariIni',
            'pesananAktif',
            'siapAmbil',
            'pendapatanHariIni'
        ));
}


    
}
