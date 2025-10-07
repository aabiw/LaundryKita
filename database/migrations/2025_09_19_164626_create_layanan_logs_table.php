<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('layanan_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('layanan_id')->nullable()->constrained('layanans')->nullOnDelete();
            $t->enum('action', ['create','update','delete']);
            // snapshot data saat kejadian
            $t->string('nama',100);
            $t->unsignedInteger('harga');
            $t->unsignedInteger('durasi_jam');
            $t->boolean('is_active')->default(true);
            // siapa yang melakukan
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->enum('user_role', ['pemilik','kasir'])->nullable();
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('layanan_logs');
    }
};

