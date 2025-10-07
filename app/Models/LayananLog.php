<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LayananLog extends Model
{
    protected $fillable = [
        'layanan_id','action','nama','harga','durasi_jam','is_active',
        'user_id','user_role'
    ];

    protected $casts = [
        'is_active'=>'boolean','harga'=>'integer','durasi_jam'=>'integer'
    ];

    public function layanan() { return $this->belongsTo(Layanan::class); }
    public function user()    { return $this->belongsTo(\App\Models\User::class); }
}
