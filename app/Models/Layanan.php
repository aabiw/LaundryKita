<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    // Kalau tabelmu singular: aktifkan baris ini
    // protected $table = 'layanan';

    protected $fillable = [
        'kode','nama','harga','durasi_jam','is_active',
        'created_by','created_role'
    ];

    protected $casts = [
        'is_active'=>'boolean','harga'=>'integer','durasi_jam'=>'integer'
    ];

    public function logs() {
        return $this->hasMany(LayananLog::class, 'layanan_id');
    }
}
