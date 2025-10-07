<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesananLog extends Model
{
    protected $table = 'pesanan_logs';

    protected $fillable = [
        'pesanan_id',
        'user_id',
        'action',       // ex: update_status, create, cancel, pay
        'from_status',
        'to_status',
        'note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }
}
