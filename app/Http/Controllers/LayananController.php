<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\LayananLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LayananController extends Controller
{
    public function index()
    {
        $items     = Layanan::orderBy('created_at','asc')->get();
        $total     = $items->count();
        $avgPrice  = $total ? (int) round($items->avg('harga')) : 0;
        $avgDurasi = $total ? (int) round($items->avg('durasi_jam')) : 0;

        return view('role.pemilik.layanan', compact('items','total','avgPrice','avgDurasi'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'nama'       => 'required|string|max:100',
            'harga'      => 'required|integer|min:0',
            'durasi_jam' => 'required|integer|min:1',
        ]);

        // kode LYN001, LYN002, ...
        $last = Layanan::where('kode','like','LYN%')->orderBy('kode','desc')->first();
        $num  = $last ? (int) Str::after($last->kode,'LYN') + 1 : 1;

        $user = auth()->user();
        $layanan = Layanan::create([
            'kode'       => 'LYN'.str_pad($num, 3, '0', STR_PAD_LEFT),
            'nama'       => $data['nama'],
            'harga'      => $data['harga'],
            'durasi_jam' => $data['durasi_jam'],
            'is_active'  => true,
            'created_by' => $user?->id,
            'created_role' => $user?->role, // 'kasir' atau 'pemilik'
        ]);

        // Log CREATE
        LayananLog::create([
            'layanan_id' => $layanan->id,
            'action'     => 'create',
            'nama'       => $layanan->nama,
            'harga'      => $layanan->harga,
            'durasi_jam' => $layanan->durasi_jam,
            'is_active'  => $layanan->is_active,
            'user_id'    => $user?->id,
            'user_role'  => $user?->role,
        ]);

        return back()->with('ok','Layanan baru berhasil ditambahkan.');
    }

    public function update(Request $r, $id)
    {
        $svc = Layanan::findOrFail($id);

        $data = $r->validate([
            'nama'       => 'required|string|max:100',
            'harga'      => 'required|integer|min:0',
            'durasi_jam' => 'required|integer|min:1',
            'is_active'  => 'nullable|boolean',
        ]);

        $svc->update([
            'nama'       => $data['nama'],
            'harga'      => $data['harga'],
            'durasi_jam' => $data['durasi_jam'],
            'is_active'  => $r->boolean('is_active', true),
        ]);

        // Log UPDATE
        $user = auth()->user();
        LayananLog::create([
            'layanan_id' => $svc->id,
            'action'     => 'update',
            'nama'       => $svc->nama,
            'harga'      => $svc->harga,
            'durasi_jam' => $svc->durasi_jam,
            'is_active'  => $svc->is_active,
            'user_id'    => $user?->id,
            'user_role'  => $user?->role,
        ]);

        return back()->with('ok','Layanan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $svc = Layanan::findOrFail($id);

        // Log DELETE (snapshot sebelum dihapus)
        $user = auth()->user();
        LayananLog::create([
            'layanan_id' => $svc->id,
            'action'     => 'delete',
            'nama'       => $svc->nama,
            'harga'      => $svc->harga,
            'durasi_jam' => $svc->durasi_jam,
            'is_active'  => $svc->is_active,
            'user_id'    => $user?->id,
            'user_role'  => $user?->role,
        ]);

        $svc->delete();
        return back()->with('ok','Layanan dihapus.');
    }

    // ====== HALAMAN HISTORY untuk PEMILIK ======
    public function history()
    {
        $logs = LayananLog::with(['layanan','user'])
            ->latest()
            ->paginate(15);

        return view('role.pemilik.layanan_history', compact('logs'));
    }
}
