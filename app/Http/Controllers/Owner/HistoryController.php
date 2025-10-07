<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pesanan;
use App\Models\PesananLog; // <- bila tabel log sudah dibuat

class HistoryController extends Controller
{
    /**
     * List history pesanan (read-only) + filter.
     */
    public function index(Request $request)
    {
        // --- 1) Base query: TERAPKAN semua filter KECUALI 'paid' ---
        $base = Pesanan::query()->with('creator');

        if ($request->filled('status')) {
            $base->where('status', $request->string('status'));
        }
        if ($request->filled('date_from')) {
            $base->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $base->whereDate('created_at', '<=', $request->date_to);
        }
        if ($kw = $request->get('q')) {
            $base->where(function ($x) use ($kw) {
                $x->where('kode', 'like', "%{$kw}%")
                  ->orWhere('customer', 'like', "%{$kw}%")
                  ->orWhere('telepon', 'like', "%{$kw}%")
                  ->orWhere('email', 'like', "%{$kw}%");
            });
        }

        // --- 2) Query untuk LIST: duplikasi base lalu terapkan filter 'paid' bila ada ---
        $listQuery = clone $base;
        $paid = $request->input('paid', '');
        if ($paid !== '' && in_array($paid, ['0','1'], true)) {
            $listQuery->where('is_paid', (int) $paid); // '1' => 1 (lunas), '0' => 0 (belum)
        }

        // Ambil list dengan paginate
        $orders = $listQuery
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        // --- 3) Statistik: dihitung dari BASE (tanpa 'paid') agar tidak terkunci ---
        $stats = [
            'total'       => (clone $base)->count(),
            'belum_lunas' => (clone $base)->where('is_paid', 0)->count(),
            'lunas'       => (clone $base)->where('is_paid', 1)->count(),
        ];

        return view('role.pemilik.history', compact('orders', 'stats'));
    }

    /**
     * Detail untuk modal (JSON) + log perubahan status (jika ada).
     */
    public function show(Pesanan $pesanan)
    {
        // muat relasi ringan bila perlu
        $pesanan->loadMissing(['pelanggan', 'pembayaran', 'creator']);

        // Ambil log (kalau tabel ada)
        $logs = class_exists(PesananLog::class)
            ? PesananLog::with('user:id,name')
                ->where('pesanan_id', $pesanan->id)
                ->orderByDesc('created_at')
                ->get()
            : collect();

        return response()->json([
            'order' => [
                'id'         => $pesanan->id,
                'kode'       => $pesanan->kode,
                'customer'   => $pesanan->customer,
                'telepon'    => $pesanan->telepon,
                'email'      => $pesanan->email,
                'layanan'    => $pesanan->layanan,
                'berat_kg'   => (float) $pesanan->berat_kg,
                'total'      => (int) $pesanan->total,
                'status'     => $pesanan->status,
                'is_paid'    => (bool) $pesanan->is_paid,
                'created_at' => optional($pesanan->created_at)->format('d M Y H:i'),
                'kasir'      => optional($pesanan->creator)->name,
            ],
            'logs' => $logs->map(fn ($l) => [
                'by'   => $l->user->name ?? 'System',
                'from' => $l->from_status,
                'to'   => $l->to_status,
                'note' => $l->note,
                'at'   => optional($l->created_at)->format('d M Y H:i'),
            ]),
        ]);
    }

    /**
     * Export CSV sesuai filter aktif.
     */
    public function exportCsv(Request $request)
    {
        // gunakan builder yang sama seperti index(): base tanpa 'paid'
        $base = Pesanan::query();

        if ($request->filled('status'))    $base->where('status', $request->string('status'));
        if ($request->filled('date_from')) $base->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $base->whereDate('created_at', '<=', $request->date_to);
        if ($kw = $request->get('q')) {
            $base->where(function ($x) use ($kw) {
                $x->where('kode', 'like', "%{$kw}%")
                  ->orWhere('customer', 'like', "%{$kw}%")
                  ->orWhere('telepon', 'like', "%{$kw}%")
                  ->orWhere('email', 'like', "%{$kw}%");
            });
        }

        // terapkan filter 'paid' hanya jika ada
        $paid = $request->input('paid', '');
        if ($paid !== '' && in_array($paid, ['0','1'], true)) {
            $base->where('is_paid', (int) $paid);
        }

        $rows = $base->orderByDesc('created_at')->get();

        $filename = 'history_pesanan_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Kode','Customer','Telepon','Email',
                'Layanan','Berat(kg)','Total','Status','Lunas','Created At'
            ]);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->kode,
                    $r->customer,
                    $r->telepon,
                    $r->email,
                    $r->layanan,
                    (string) $r->berat_kg,
                    (int) $r->total,
                    $r->status,
                    $r->is_paid ? 'Ya' : 'Belum',
                    optional($r->created_at)->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /** Cetak struk. */
    public function printReceipt(Pesanan $pesanan)
    {
        return view('role.pemilik.print.struk', compact('pesanan'));
    }

    /** Cetak tag/claim. */
    public function printTag(Pesanan $pesanan)
    {
        return view('role.pemilik.print.tag', compact('pesanan'));
    }
}