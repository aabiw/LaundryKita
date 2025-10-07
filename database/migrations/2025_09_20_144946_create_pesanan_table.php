<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->unique();
            $table->unsignedBigInteger('pelanggan_id')->nullable();
            $table->string('customer', 150);
            $table->string('telepon', 50)->nullable();
            #$table->string('email', 150)->nullable();
            $table->string('layanan')->nullable();
            $table->decimal('berat_kg', 8, 2)->nullable();
            $table->unsignedBigInteger('total')->default(0);
            $table->enum('status', ['Baru','Siap Ambil','Selesai'])->default('Baru');
            $table->boolean('is_paid')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};
