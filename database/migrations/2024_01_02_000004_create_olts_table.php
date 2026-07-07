<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('olts', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('ip_address');
            $table->string('model')->nullable();
            $table->string('lokasi')->nullable();
            $table->integer('total_port')->default(16);
            $table->enum('status', ['online', 'offline', 'maintenance'])->default('online');
            $table->string('uptime')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('olts');
    }
};
