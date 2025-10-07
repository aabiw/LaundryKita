<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pesanan;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\PesananLog;
use App\Models\Pembayaran;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class PesananController extends Controller
{
    public function index(Request $request)
    {
        $orders = Pesanan::with('creator')
            ->where('status', '!=', 'Selesai');

        // --- Search: hanya kode & nama pelanggan ---
        if (($kw = trim($request->get('q', ''))) !== '') {
            $terms = preg_split('/\s+/', $kw);

            $orders->where(function ($outer) use ($terms) {
                foreach ($terms as $t) {
                    $t = trim($t);
                    if ($t === '') continue;

                    // nama pelanggan: cocok sebagai KATA UTUH (hindari 'Nabila' saat cari 'abil')
                    $tLower = mb_strtolower($t, 'UTF-8');
                    $customerExpr = "LOWER(CONCAT(' ', TRIM(REPLACE(REPLACE(customer, '  ', ' '), '\t', ' ')), ' '))";

                    $outer->where(function ($q) use ($t, $tLower, $customerExpr) {
                        $q->where('kode', 'like', "%{$t}%")                           // kode: substring
                          ->orWhereRaw("$customerExpr LIKE ?", ['% ' . $tLower . ' %']); // nama: kata utuh
                    });
                }
            });
        }

        $orders = $orders->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        return view('role.kasir.pesanan-aktif', compact('orders'));
    }

    public function create(Request $request)
    {
        $layanans = Layanan::where('is_active', true)
            ->orderBy('nama', 'asc')
            ->get(['id', 'kode', 'nama', 'harga', 'durasi_jam']);

        $view = view('role.kasir.pesanan-baru', compact('layanans'));
        if ($request->boolean('fresh')) {
            return response($view)
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        }
        return $view;
    }

    public function store(Request $request)
    {
        if ($request->filled('berat_kg')) {
            $request->merge([
                'berat_kg' => str_replace(',', '.', $request->input('berat_kg')),
            ]);
        }

        $data = $request->validate([
            'customer'           => ['required', 'string', 'max:191'],
            'telepon'            => ['nullable', 'string', 'max:50'],
            'layanan'            => [
                'required', 'string', 'max:255',
                Rule::exists('layanans', 'nama')->where('is_active', 1),
            ],
            'berat_kg'           => ['nullable', 'numeric', 'min:0'],
            'total'              => ['required', 'integer', 'min:0'],
            'catatan'            => ['nullable', 'string', 'max:500'],
            'status_pembayaran'  => ['required', Rule::in(['lunas', 'belum_lunas'])],
            'metode_pembayaran'  => ['nullable', 'string', 'max:50', 'required_if:status_pembayaran,lunas'],
        ]);

        $normalizedTel = !empty($data['telepon']) ? $this->normalizeWaNumber($data['telepon']) : null;
        $data['telepon'] = (!empty($data['telepon']) && !$normalizedTel) ? null : $normalizedTel;

        $pelanggan = null;
        if (!empty($data['telepon'])) {
            $pelanggan = Pelanggan::query()->where('telepon', $data['telepon'])->first();
            if ($pelanggan) {
                if (empty($pelanggan->nama_pelanggan)) $pelanggan->nama_pelanggan = $data['customer'];
                if (!empty($data['telepon']) && empty($pelanggan->telepon)) $pelanggan->telepon = $data['telepon'];
                $pelanggan->save();
            } else {
                $pelanggan = Pelanggan::create([
                    'nama_pelanggan' => $data['customer'],
                    'telepon'        => $data['telepon'] ?? null,
                ]);
            }
            $data['pelanggan_id'] = $pelanggan->ID_pelanggan;
        }

        if (empty($data['telepon']) && $pelanggan && !empty($pelanggan->telepon)) {
            $data['telepon'] = $pelanggan->telepon;
        }

        $data['kode']       = 'ORD' . strtoupper(Str::random(6));
        $data['status']     = 'Baru';
        $data['is_paid']    = ($data['status_pembayaran'] === 'lunas');
        $data['paid_at']    = $data['is_paid'] ? now() : null;
        $data['created_by'] = Auth::id();

        $order = Pesanan::create($data);

        PesananLog::create([
            'pesanan_id'  => $order->id,
            'user_id'     => Auth::id(),
            'action'      => 'create',
            'from_status' => null,
            'to_status'   => 'Baru',
            'note'        => $order->is_paid ? 'Dibuat (Lunas)' : 'Dibuat (Belum Lunas)',
        ]);

        if ($order->is_paid) {
            Pembayaran::create([
                'pesanan_id' => $order->id,
                'metode'     => $request->input('metode_pembayaran') ?? 'tunai',
                'jumlah'     => (int) ($order->total ?? 0),
                'user_id'    => Auth::id(),
            ]);
        }

        $telepon = $order->telepon ?? ($order->pelanggan->telepon ?? null);
        if (!empty($telepon)) {
            $wa = $this->normalizeWaNumber($telepon);
            if ($wa) {
                $msg     = $this->composeWaMessage($order, 'Baru');
                $targets = $this->buildWaTargets($wa, $msg, $request);

                $backFresh = route('kasir.pesanan.create', ['fresh' => 1, 't' => now()->timestamp]);

                return redirect()
                    ->to($backFresh)
                    ->with('success', 'Pesanan berhasil dibuat. (Kode: ' . $order->kode . ')')
                    ->with('just_created', true)
                    ->with('wa_app',    $targets['app'])
                    ->with('wa_me',     $targets['wame'])
                    ->with('wa_api',    $targets['api'])
                    ->with('wa_api2',   $targets['api2'])   // ⬅ penting untuk nomor tak tersimpan
                    ->with('wa_intent', $targets['intent'])
                    ->with('wa_intent_jid', $targets['intent_jid']) // ⬅ intent JID (Android)
                    ->with('wa_web',    $targets['web'])
                    ->with('wa_rawtxt', $targets['rawtxt']);        // ⬅ untuk clipboard fallback
            }
        }

        return redirect()
            ->route('kasir.pesanan.create', ['fresh' => 1, 't' => now()->timestamp])
            ->with('success', 'Pesanan berhasil dibuat. (Kode: ' . $order->kode . ')');
    }

    public function updateStatus(Request $request, Pesanan $pesanan)
    {
        $request->validate([
            'status' => 'required|in:Baru,Dalam Proses,Siap Ambil,Selesai,Dibatalkan',
        ]);

        $from = $pesanan->status;
        $to   = (string) $request->input('status');

        if ($to === 'Selesai' && $from !== 'Siap Ambil') {
            $msg = 'Tidak dapat mengubah status karena pesanan belum siap ambil. Coba lagi.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['ok'=>false,'message'=>$msg], 422);
            }
            return back()->with('error', $msg);
        }
        $isPaid = ($pesanan->is_paid ?? false) || (($pesanan->status_pembayaran ?? null) === 'lunas');
        if ($to === 'Selesai' && !$isPaid) {
            $msg = 'Tidak dapat mengubah status karena pesanan belum lunas. Coba lagi.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['ok'=>false,'message'=>$msg], 422);
            }
            return back()->with('error', $msg);
        }

        $pesanan->update(['status' => $to]);

        PesananLog::create([
            'pesanan_id'  => $pesanan->id,
            'user_id'     => Auth::id(),
            'action'      => 'update_status',
            'from_status' => $from,
            'to_status'   => $to,
            'note'        => null,
        ]);

        $telepon = $pesanan->telepon ?? ($pesanan->pelanggan->telepon ?? null);
        if (in_array($to, ['Siap Ambil', 'Selesai'], true) && !empty($telepon)) {
            $wa = $this->normalizeWaNumber($telepon);
            if ($wa) {
                $msg     = $this->composeWaMessage($pesanan, $to);
                $targets = $this->buildWaTargets($wa, $msg, $request);

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'ok'        => true,
                        'message'   => "Status pesanan diperbarui dari {$from} ke {$to}.",
                        'status'    => $to,
                        'wa_url'    => $targets['app'],
                        'wa_app'    => $targets['app'],
                        'wa_me'     => $targets['wame'],
                        'order_id'  => $pesanan->id,
                    ]);
                }

                return $this->bridgeOpenWhatsApp($targets, url()->previous());
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok'       => true,
                'message'  => "Status pesanan diperbarui dari {$from} ke {$to}.",
                'status'   => $to,
                'wa_url'   => null,
                'order_id' => $pesanan->id,
            ]);
        }

        return back()->with('success', 'Status pesanan diperbarui dari ' . $from . ' ke ' . $to . '.');
    }

    public function destroy(Pesanan $pesanan)
    {
        $pesanan->delete();
        return back()->with('success', 'Pesanan berhasil dihapus.');
    }

    private function normalizeWaNumber(?string $raw): ?string
    {
        $d = preg_replace('/\D+/', '', $raw ?? '');
        if ($d === '') return null;

        if (str_starts_with($d, '620')) {
            $d = '62' . substr($d, 3);
        } elseif ($d[0] === '0') {
            $d = '62' . substr($d, 1);
        } elseif ($d[0] === '8') {
            $d = '62' . $d;
        }

        if (str_starts_with($d, '62')) {
            $rest = ltrim(substr($d, 2), '0');
            if ($rest === '') return null;
            $d = '62' . $rest;
        }

        $len = strlen($d);
        if ($len < 10 || $len > 15) return null;

        return $d;
    }

    /** Intent (JID + phone) + API khusus nomor tak tersimpan + fallback lengkap */
    private function buildWaTargets(string $phone, string $text, Request $request): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $enc  = rawurlencode($text);

        $jid = $phone . '@s.whatsapp.net';

        $intent_jid   = "intent://send/?jid={$jid}&text={$enc}#Intent;scheme=smsto;package=com.whatsapp;end";
        $intent_phone = "intent://send/?phone={$phone}&text={$enc}#Intent;scheme=smsto;package=com.whatsapp;end";

        $api_std = "https://api.whatsapp.com/send?phone={$phone}&text={$enc}";
        $api_nu  = "https://api.whatsapp.com/send/?phone={$phone}&text={$enc}&type=phone_number&app_absent=0";

        return [
            'app'        => "whatsapp://send?phone={$phone}&text={$enc}",
            'wame'       => "https://wa.me/{$phone}?text={$enc}",
            'api'        => $api_std,
            'api2'       => $api_nu,
            'web'        => "https://web.whatsapp.com/send?phone={$phone}&text={$enc}",
            'intent'     => $intent_phone,
            'intent_jid' => $intent_jid,
            'rawtxt'     => $text,
        ];
    }

    private function bridgeOpenWhatsApp(array $targets, string $backUrl)
    {
        $html = '<!doctype html><html lang="id"><head><meta charset="utf-8">'
              . '<meta name="viewport" content="width=device-width, initial-scale=1">'
              . '<title>Membuka WhatsApp…</title></head><body>'
              . '<script>(function(){'
              . 'var app=' . json_encode($targets['app']) . ';'
              . 'var wame=' . json_encode($targets['wame']) . ';'
              . 'var back=' . json_encode($backUrl) . ';'
              . 'try{window.location.href=app;}catch(e){}'
              . 'setTimeout(function(){try{location.replace(wame);}catch(e){location.href=wame;}},900);'
              . 'document.addEventListener("visibilitychange",function(){if(document.visibilityState==="hidden"){setTimeout(function(){try{location.replace(back);}catch(e){location.href=back;}},1800);}});'
              . 'setTimeout(function(){try{location.replace(back);}catch(e){location.href=back;}},4500);'
              . '})();</script>'
              . '</body></html>';

        return response($html, 200)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    private function composeWaMessage(Pesanan $p, string $statusBaru): string
    {
        $kasir     = Auth::user()->name ?? 'Kasir';
        $tanggal   = now()->format('d/m/Y - H:i');
        $beratView = isset($p->berat_kg) ? number_format((float) $p->berat_kg, 2, ',', '.') : null;
        $subtotal  = 'Rp. ' . number_format((int) ($p->total ?? 0), 0, ',', '.');
        $statusBayar = $p->status_pembayaran
            ? ucfirst($p->status_pembayaran)
            : (($p->is_paid ?? false) ? 'Lunas' : 'Belum Lunas');

        $lines = [
            "LaundryKita",
            "====================",
            "Tanggal : {$tanggal}",
            "No Nota : " . ($p->kode ?? '-'),
            "Kasir : {$kasir}",
            "Nama : " . ($p->customer ?? '-'),
            "====================",
            "Tipe Layanan : " . ($p->layanan ?? '-'),
        ];
        if ($beratView) $lines[] = "Berat (kg) = {$beratView}";
        $lines[] = "Subtotal = {$subtotal}";
        $lines[] = "Status Pembayaran : {$statusBayar}";
        $lines[] = "====================";
        $lines[] = "Status : {$statusBaru}";
        $lines[] = "====================";
        $lines[] = "Terima kasih telah menggunakan layanan kami.";

        return implode("\n", $lines);
    }

    public function cetakTag(Pesanan $pesanan)
    {
        $pesanan->load('creator');
        return view('role.kasir.print.tag', compact('pesanan'));
    }
}