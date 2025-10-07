<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    // pakai nama tabel yang memang ada di DB kamu
    protected $table = 'pesanan';

    /**
     * Kolom yang bisa di-mass assign saat create/update.
     * (Tambahkan status_pembayaran & paid_at agar nilai dari form tersimpan)
     */
    protected $fillable = [
        'kode',
        'pelanggan_id',
        'customer',
        'telepon',
        'email',
        'layanan',
        'berat_kg',
        'total',
        'status',

        // pembayaran
        'status_pembayaran',   // 'lunas' | 'belum_lunas'
        'is_paid',             // boolean legacy
        'paid_at',             // timestamp saat dilunasi

        // meta
        'created_by',
    ];

    /**
     * Casting tipe data.
     */
    protected $casts = [
        'is_paid'  => 'boolean',
        'paid_at'  => 'datetime',
        'berat_kg' => 'decimal:2',
        'total'    => 'integer',
    ];

    // ===== RELASI =====

    // banyak pembayaran untuk satu pesanan
    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'pesanan_id');
    }

    // pesanan milik satu pelanggan (FK: pelanggan_id → pelanggan.ID_pelanggan)
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    // user/pegawai yang membuat pesanan
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    // log perubahan/status pesanan
    public function logs()
    {
        return $this->hasMany(PesananLog::class, 'pesanan_id');
    }

    /**
     * Accessor sederhana: $pesanan->kasir_name
     * Mengembalikan nama user pembuat pesanan (jika ada).
     */
    public function getKasirNameAttribute(): ?string
    {
        return $this->creator->name ?? null;
    }
}