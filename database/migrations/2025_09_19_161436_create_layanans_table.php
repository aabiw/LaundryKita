<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('layanans', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique();  // LYN001
            $table->string('nama', 100);
            $table->unsignedInteger('harga');      // per kg
            $table->unsignedInteger('durasi_jam'); // 24, 48, dst
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('layanans');
    }
};

