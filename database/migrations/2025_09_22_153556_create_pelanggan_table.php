<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel master pelanggan (tanpa timestamps)
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->bigIncrements('ID_pelanggan');      // PK sesuai model
            $table->string('nama_pelanggan', 150);
            $table->string('telepon', 30)->nullable()->unique();
            #$table->string('email', 150)->nullable()->unique();
            #$table->date('tanggal_lahir')->nullable();
            #$table->string('password')->nullable();      // biarkan nullable jika tidak dipakai login
        });

        // (opsional, tapi disarankan) tambahkan FK ke tabel pesanan
        if (Schema::hasTable('pesanan')) {
            Schema::table('pesanan', function (Blueprint $table) {
                if (!Schema::hasColumn('pesanan', 'pelanggan_id')) {
                    $table->unsignedBigInteger('pelanggan_id')->nullable();
                }
                $table->foreign('pelanggan_id')
                    ->references('ID_pelanggan')->on('pelanggan')
                    ->nullOnDelete()->cascadeOnUpdate();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pesanan')) {
            Schema::table('pesanan', function (Blueprint $table) {
                // jika FK pernah dibuat, lepaskan dulu
                if (Schema::hasColumn('pesanan', 'pelanggan_id')) {
                    $table->dropForeign(['pelanggan_id']);
                }
            });
        }

        Schema::dropIfExists('pelanggan');
    }
};
