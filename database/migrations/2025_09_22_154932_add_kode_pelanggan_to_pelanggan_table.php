<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            // kolom kode unik, kita buat nullable dulu agar bisa dibackfill
            $table->string('kode_pelanggan', 20)->unique()->nullable()->after('ID_pelanggan');
        });

        // Backfill untuk record yang sudah ada
        $rows = DB::table('pelanggan')->select('ID_pelanggan','kode_pelanggan')->get();
        foreach ($rows as $r) {
            if (!$r->kode_pelanggan) {
                // format: PLG- + 8 char acak (huruf besar & angka)
                $kode = 'PLG-' . strtoupper(Str::random(8));
                // pastikan unik (loop kecil jika bentrok—jarang terjadi)
                while (DB::table('pelanggan')->where('kode_pelanggan', $kode)->exists()) {
                    $kode = 'PLG-' . strtoupper(Str::random(8));
                }
                DB::table('pelanggan')
                    ->where('ID_pelanggan', $r->ID_pelanggan)
                    ->update(['kode_pelanggan' => $kode]);
            }
        }

        // (opsional) kalau kamu sudah pasang doctrine/dbal, boleh ubah ke not null:
        // Schema::table('pelanggan', function (Blueprint $table) {
        //     $table->string('kode_pelanggan', 20)->nullable(false)->change();
        // });
    }

    public function down(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropUnique(['kode_pelanggan']);
            $table->dropColumn('kode_pelanggan');
        });
    }
};
