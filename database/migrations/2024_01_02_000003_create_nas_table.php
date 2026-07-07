<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nas', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique(); // MK-CORE-01
            $table->string('nama');
            $table->string('ip_address');
            $table->string('model')->nullable();
            $table->string('lokasi')->nullable();
            $table->enum('status', ['online', 'offline', 'maintenance'])->default('online');
            $table->string('uptime')->nullable();
            $table->integer('cpu_pct')->nullable();
            $table->integer('mem_pct')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas');
    }
};
