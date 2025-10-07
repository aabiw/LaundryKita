<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index()
    {
        // View analitik tetap sama; angka & chart diisi via fetch JSON
        return view('role.pemilik.analitik');
    }

    public function data(Request $request)
    {
        $days = max(1, (int) $request->query('days', 30));
        $start = Carbon::now()->subDays($days - 1)->startOfDay();
        $end   = Carbon::now()->endOfDay();

        // Siapkan label H-*
        $labels  = [];
        $revenue = [];
        $orders  = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $labels[]  = 'H-'.($i+1);
            $revenue[] = 0;
            $orders[]  = 0;
        }

        // Deteksi tabel transaksi yang ada
        [$table, $totalCol, $createdCol] = $this->detectSource();

        if ($table) {
            $rows = DB::table($table)
                ->selectRaw('DATE('.$createdCol.') as d, COUNT(*) as cnt, COALESCE(SUM('.($totalCol ?? '0').'),0) as sumtotal')
                ->whereBetween($createdCol, [$start, $end])
                ->groupBy('d')
                ->orderBy('d')
                ->get();

            // Map tanggal → index label
            $indexByDate = [];
            foreach ($labels as $idx => $lab) {
                $h = (int) substr($lab, 2); // contoh: 'H-30' → 30
                $date = Carbon::now()->subDays($h - 1)->toDateString();
                $indexByDate[$date] = $idx;
            }

            foreach ($rows as $r) {
                if (isset($indexByDate[$r->d])) {
                    $idx = $indexByDate[$r->d];
                    $revenue[$idx] = (int) $r->sumtotal;
                    $orders[$idx]  = (int) $r->cnt;
                }
            }
        }

        $totalRevenue = array_sum($revenue);
        $totalOrders  = array_sum($orders);
        $avgOrder     = $totalOrders ? (int) round($totalRevenue / max(1,$totalOrders)) : 0;

        // Growth vs periode sebelumnya (panjang sama dgn $days)
        $prevStart = (clone $start)->subDays($days);
        $prevEnd   = (clone $end)->subDays($days);
        $prevRevenueSum = 0;

        if ($table && $totalCol) {
            $prevRevenueSum = (int) (DB::table($table)
                ->whereBetween($createdCol, [$prevStart, $prevEnd])
                ->selectRaw('COALESCE(SUM('.$totalCol.'),0) as s')
                ->value('s') ?? 0);
        }

        $growthRate = $prevRevenueSum > 0
            ? round((($totalRevenue - $prevRevenueSum) / $prevRevenueSum) * 100, 1)
            : 0.0;

        return response()->json([
            'labels'       => $labels,
            'revenue'      => $revenue,
            'orders'       => $orders,
            'totalRevenue' => $totalRevenue,
            'totalOrders'  => $totalOrders,
            'avgOrder'     => $avgOrder,
            'growthRate'   => $growthRate,
        ]);
    }

    /**
     * Cari sumber data transaksi yang tersedia di DB.
     * Return: [table, total_col|null, created_at_col]
     */
    private function detectSource(): array
    {
        // Susunan kandidat umum (ubah jika skema-mu berbeda)
        $candidates = [
            'transaksis'   => ['total','grand_total','total_harga','amount','nominal'],
            'orders'       => ['total','grand_total','total_harga','amount','nominal'],
            'pesanans'     => ['total','grand_total','total_harga','amount','nominal'],
            'transactions' => ['total','grand_total','total_harga','amount','nominal'],
        ];

        foreach ($candidates as $table => $totalCols) {
            if (Schema::hasTable($table)) {
                $cols = Schema::getColumnListing($table);

                // created_at / tanggal / date
                $createdCol = in_array('created_at', $cols) ? 'created_at'
                             : (in_array('tanggal', $cols) ? 'tanggal'
                             : (in_array('date', $cols) ? 'date' : 'created_at'));

                // cari kolom total
                $totalCol = null;
                foreach ($totalCols as $c) {
                    if (in_array($c, $cols)) { $totalCol = $c; break; }
                }

                return [$table, $totalCol, $createdCol];
            }
        }

        return [null, null, 'created_at'];
    }
}
