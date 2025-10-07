<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pesanan;
use App\Models\PesananLog;

class KasirHistoryController extends Controller
{
    /**
     * List history pesanan (read-only) + filter (portal kasir).
     */
    public function index(Request $request)
    {
        // 1) Base query: seluruh filter KECUALI 'paid'
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

        // 2) Keyword: HANYA kode (substring) + customer (KATA UTUH).
        //    Email & telepon TIDAK disertakan, agar 'abil' tidak memicu 'Nabila' / email.
        if (($kw = trim($request->get('q', ''))) !== '') {
            $terms = preg_split('/\s+/', $kw);

            $base->where(function ($outer) use ($terms) {
                foreach ($terms as $t) {
                    $t = trim($t);
                    if ($t === '') continue;

                    $tLower = mb_strtolower($t, 'UTF-8');
                    // Normalisasi nama + padding spasi, lalu lower() untuk LIKE case-insensitive
                    $customerExpr = "LOWER(CONCAT(' ', TRIM(REPLACE(REPLACE(customer, '  ', ' '), '\t', ' ')), ' '))";

                    $outer->where(function ($q) use ($t, $tLower, $customerExpr) {
                        $q->where('kode', 'like', "%{$t}%")                                 // kode: substring
                          ->orWhereRaw("$customerExpr LIKE ?", ['% ' . $tLower . ' %']);     // nama: kata utuh
                    });
                }
            });
        }

        // 3) List query: terapkan filter 'paid' hanya di daftar
        $listQuery = clone $base;
        $paid = $request->input('paid', '');
        if ($paid !== '' && in_array($paid, ['0','1'], true)) {
            $listQuery->where('is_paid', (int) $paid);
        }

        $orders = $listQuery->orderByDesc('created_at')
                            ->paginate(12)
                            ->withQueryString();

        // 4) Statistik: dari BASE (tanpa 'paid') agar tidak ikut berubah saat ganti Pembayaran
        $stats = [
            'total'       => (clone $base)->count(),
            'lunas'       => (clone $base)->where('is_paid', 1)->count(),
            'belum_lunas' => (clone $base)->where('is_paid', 0)->count(),
        ];

        return view('role.kasir.history', compact('orders', 'stats'));
    }

    /**
     * Detail untuk modal (opsional).
     */
    public function show(Pesanan $pesanan)
    {
        $pesanan->loadMissing(['creator']);
        $logs = class_exists(PesananLog::class)
            ? PesananLog::with('user:id,name')->where('pesanan_id', $pesanan->id)->latest()->get()
            : collect();

        return response()->json([
            'order' => [
                'id'         => $pesanan->id,
                'kode'       => $pesanan->kode,
                'customer'   => $pesanan->customer,
                'telepon'    => $pesanan->telepon,
                'layanan'    => $pesanan->layanan,
                'berat_kg'   => (float) ($pesanan->berat_kg ?? 0),
                'total'      => (int) ($pesanan->total ?? 0),
                'status'     => $pesanan->status,
                'is_paid'    => (bool) $pesanan->is_paid,
                'created_at' => optional($pesanan->created_at)->format('d M Y H:i'),
                'kasir'      => optional($pesanan->creator)->name,
            ],
            'logs' => $logs->map(fn ($l) => [
                'by' => $l->user->name ?? 'System',
                'from' => $l->from_status,
                'to' => $l->to_status,
                'note' => $l->note,
                'at' => optional($l->created_at)->format('d M Y H:i'),
            ]),
        ]);
    }

    /**
     * Export CSV sesuai filter aktif.
     */
    public function exportCsv(Request $request)
    {
        $base = Pesanan::query();

        if ($request->filled('status'))    $base->where('status', $request->string('status'));
        if ($request->filled('date_from')) $base->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $base->whereDate('created_at', '<=', $request->date_to);

        // keyword sama seperti index(): hanya kode + customer (kata utuh)
        if (($kw = trim($request->get('q', ''))) !== '') {
            $terms = preg_split('/\s+/', $kw);

            $base->where(function ($outer) use ($terms) {
                foreach ($terms as $t) {
                    $t = trim($t);
                    if ($t === '') continue;

                    $tLower = mb_strtolower($t, 'UTF-8');
                    $customerExpr = "LOWER(CONCAT(' ', TRIM(REPLACE(REPLACE(customer, '  ', ' '), '\t', ' ')), ' '))";

                    $outer->where(function ($q) use ($t, $tLower, $customerExpr) {
                        $q->where('kode', 'like', "%{$t}%")
                          ->orWhereRaw("$customerExpr LIKE ?", ['% ' . $tLower . ' %']);
                    });
                }
            });
        }

        $paid = $request->input('paid', '');
        if ($paid !== '' && in_array($paid, ['0','1'], true)) {
            $base->where('is_paid', (int) $paid);
        }

        $rows = $base->orderByDesc('created_at')->get();

        $filename = 'history_pesanan_kasir_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Kode','Customer','Telepon','Layanan','Berat(kg)','Total','Status','Lunas','Created At']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->kode,
                    $r->customer,
                    $r->telepon,
                    $r->layanan,
                    (string) ($r->berat_kg ?? ''),
                    (int) ($r->total ?? 0),
                    $r->status,
                    $r->is_paid ? 'Ya' : 'Belum',
                    optional($r->created_at)->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}