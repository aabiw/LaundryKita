<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            // status_pembayaran & paid_at ditempatkan setelah kolom total
            $table->enum('status_pembayaran', ['belum_lunas','lunas'])
                  ->default('belum_lunas')
                  ->after('total');

            $table->timestamp('paid_at')->nullable()->after('status_pembayaran');
        });

        // ---- BACKFILL DATA LAMA ----
        // Jika sebelumnya sudah ada kolom is_paid, sinkronkan ke kolom baru
        // is_paid = 1  -> status_pembayaran = 'lunas' dan isi paid_at bila masih null
        DB::table('pesanan')
            ->where('is_paid', 1)
            ->update([
                'status_pembayaran' => 'lunas',
                'paid_at'           => DB::raw('COALESCE(paid_at, NOW())'),
            ]);

        // is_paid = 0/null -> status_pembayaran = 'belum_lunas'
        DB::table('pesanan')
            ->where(function ($q) {
                $q->whereNull('is_paid')->orWhere('is_paid', 0);
            })
            ->update([
                'status_pembayaran' => 'belum_lunas',
            ]);
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropColumn(['status_pembayaran', 'paid_at']);
        });
    }
};
