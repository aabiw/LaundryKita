<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pelanggan extends Model
{
    protected $table      = 'pelanggan';
    protected $primaryKey = 'ID_pelanggan';
    public $timestamps    = false;

    protected $fillable = [
        'kode_pelanggan',          // <-- business key unik
        'nama_pelanggan',
        'email',
        'telp_pelanggan',
        'tanggal_lahir',
        'password',
    ];

    protected $hidden = ['password'];

    // Auto-generate kode pelanggan unik saat record dibuat
    protected static function booted()
    {
        static::creating(function (self $pel) {
            if (empty($pel->kode_pelanggan)) {
                $pel->kode_pelanggan = 'PLG-' . strtoupper(Str::random(8));
            }
        });
    }

    // Hash otomatis kalau password diisi dalam bentuk plain text
    public function setPasswordAttribute($value): void
    {
        if (is_null($value) || $value === '') {
            $this->attributes['password'] = null;
            return;
        }
        $this->attributes['password'] = Str::startsWith($value, '$2y$')
            ? $value
            : bcrypt($value);
    }

    // Relasi: satu pelanggan punya banyak pesanan
    // FK di pesanan: pelanggan_id, owner key di pelanggan: ID_pelanggan
    public function pesanans()
    {
        return $this->hasMany(Pesanan::class, 'pelanggan_id', 'ID_pelanggan');
    }
}
