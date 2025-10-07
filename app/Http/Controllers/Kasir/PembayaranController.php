<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pesanan;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\Auth;

class PembayaranController extends Controller
{
    /**
     * Halaman Status Pembayaran.
     * - Bisa cari pesanan (nama/kode)
     * - Kirim 'selected' jika ada ?id=xxx untuk panel struk kanan
     */
    public function index(Request $request)
    {
        $q = $request->get('q');

        $orders = Pesanan::with('creator')
                    ->when($q, function ($x) use ($q) {
                        $x->where('customer', 'like', "%{$q}%")
                          ->orWhere('kode', 'like', "%{$q}%");
                    })
                    ->orderBy('created_at', 'desc')
                    ->get();

        $selected = $request->get('id')
                    ? Pesanan::with('creator')->find($request->get('id'))
                    : null;

        return view('role.kasir.pembayaran', [
            'orders'   => $orders,
            'selected' => $selected,
            'q'        => $q,
        ]);
    }

    /**
     * Proses pembayaran (input nominal & metode).
     * Jika jumlah >= total -> tandai LUNAS.
     */
    public function proses(Request $request, $id)
    {
        $request->validate([
            'metode' => 'required|string|max:50',
            'jumlah' => 'required|integer|min:0'
        ]);

        $order = Pesanan::findOrFail($id);

        Pembayaran::create([
            'pesanan_id' => $order->id,
            'metode'     => $request->input('metode'),
            'jumlah'     => $request->input('jumlah'),
            'user_id'    => Auth::id() ?? null,
        ]);

        // Jika nominal mencukupi -> set Lunas
        if ((int) $request->input('jumlah') >= (int) $order->total) {
            $order->is_paid            = true;         // kompatibel dengan skema lama
            $order->status_pembayaran  = 'lunas';      // jika kolom ini ada di tabel
            $order->paid_at            = now();        // timestamp pelunasan
            // opsional: ubah status kerja jadi Selesai (ikuti logika awalmu)
            if ($order->status !== 'Selesai') {
                $order->status = 'Selesai';
            }
            $order->save();
        }

        return back()->with('success', 'Pembayaran berhasil diproses.');
    }

    /**
     * Tandai LUNAS tanpa input nominal (misal dari tombol "Tandai Lunas").
     * (Terintegrasi dengan pilihan metode pembayaran dari panel kanan)
     */
    public function markPaid(Request $request, $id)
    {
        $order = Pesanan::findOrFail($id);

        // Set status lunas di pesanan
        $order->is_paid           = true;
        $order->status_pembayaran = 'lunas'; // jika kolom tersedia
        $order->paid_at           = now();
        $order->save();

        // Ambil metode dari form panel kanan (fallback ke 'tunai' jika tidak ada)
        $metode = $request->input('metode_pembayaran');
        if (is_string($metode) && $metode !== '') {
            // normalisasi capitalisasi ringan (opsional, tanpa mengubah maksud)
            $metode = ucfirst(strtolower($metode));
        } else {
            $metode = 'tunai';
        }

        // Catat transaksi ke tabel 'pembayarans' agar pendapatan harian masuk
        // Hindari duplikasi jika sebelumnya sudah ada record untuk pesanan yang sama
        if (!Pembayaran::where('pesanan_id', $order->id)->exists()) {
            Pembayaran::create([
                'pesanan_id' => $order->id,
                'metode'     => $metode,
                'jumlah'     => (int) ($order->total ?? 0),
                'user_id'    => Auth::id() ?? null,
            ]);
        }

        return back()->with('ok', 'Status pembayaran diupdate menjadi Lunas.');
    }
}
