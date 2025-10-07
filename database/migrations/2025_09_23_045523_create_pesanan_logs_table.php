<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan_logs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('pesanan_id');
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('action', 50)->default('update_status');
            $t->string('from_status', 50)->nullable();
            $t->string('to_status', 50)->nullable();
            $t->text('note')->nullable();
            $t->timestamps();

            $t->foreign('pesanan_id')->references('id')->on('pesanan')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan_logs');
    }
};
